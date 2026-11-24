<?php

namespace Tests\Feature;

use App\Catalogue\InMemoryProductSource;
use App\Catalogue\Product;
use App\Catalogue\ProductSource;
use App\Enquiries\InMemoryMailProvider;
use App\Enquiries\MailDeliveryFailed;
use App\Enquiries\MailProvider;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class EnquiryTest extends TestCase
{
    private InMemoryMailProvider $mail;

    protected function setUp(): void
    {
        parent::setUp();

        config(['catalogue.sales_inbox' => 'sales@shop.test']);

        $this->mail = new InMemoryMailProvider;
        $this->app->instance(MailProvider::class, $this->mail);
        $this->app->instance(ProductSource::class, new InMemoryProductSource([
            new Product(3, 'WD 2TB Hard Drive', 64.0, 'A portable drive', 'electronics', 'https://img.test/3.png', 3.3),
        ]));
    }

    public function test_a_valid_enquiry_emails_the_sales_inbox_and_says_thank_you(): void
    {
        $response = $this->postJson('/api/products/3/enquiries', [
            'name' => 'Priya Shah',
            'email' => 'priya@example.com',
            'message' => 'Is this drive in stock?',
        ]);

        $response->assertCreated()->assertExactJson(['message' => 'Thank you for your enquiry.']);

        $this->assertCount(1, $this->mail->sent());
        $email = $this->mail->sent()[0];
        $this->assertSame('sales@shop.test', $email['to']);
        $this->assertStringContainsString('WD 2TB Hard Drive', $email['subject']);
        $this->assertStringContainsString('WD 2TB Hard Drive', $email['body']);
        $this->assertStringContainsString('Priya Shah', $email['body']);
        $this->assertStringContainsString('priya@example.com', $email['body']);
        $this->assertStringContainsString('Is this drive in stock?', $email['body']);
        $this->assertSame('priya@example.com', $email['replyTo']);
    }

    public function test_a_line_break_in_a_product_title_cannot_add_email_headers(): void
    {
        $this->app->instance(ProductSource::class, new InMemoryProductSource([
            new Product(4, "Sneaky\r\nBcc: attacker@example.com", 9.0, 'x', 'electronics', 'https://img.test/4.png', null),
        ]));

        $this->postJson('/api/products/4/enquiries', [
            'name' => 'Priya Shah',
            'email' => 'priya@example.com',
            'message' => 'Hello',
        ])->assertCreated();

        $subject = $this->mail->sent()[0]['subject'];
        $this->assertStringNotContainsString("\n", $subject);
        $this->assertStringNotContainsString("\r", $subject);
    }

    public function test_a_failed_send_answers_502_and_never_says_thank_you(): void
    {
        $this->app->instance(MailProvider::class, new class implements MailProvider
        {
            public function send(string $to, string $subject, string $body, ?string $replyTo = null): void
            {
                throw new MailDeliveryFailed('The mail server is down.');
            }
        });

        $response = $this->postJson('/api/products/3/enquiries', [
            'name' => 'Priya Shah',
            'email' => 'priya@example.com',
            'message' => 'Is this drive in stock?',
        ]);

        $response->assertStatus(502)->assertExactJson([
            'message' => "We couldn't send your enquiry. Please try again.",
        ]);
        $this->assertStringNotContainsString('Thank you', $response->getContent());
    }

    public function test_an_enquiry_about_an_unknown_product_is_not_found_and_sends_nothing(): void
    {
        $response = $this->postJson('/api/products/99/enquiries', [
            'name' => 'Priya Shah',
            'email' => 'priya@example.com',
            'message' => 'Is this drive in stock?',
        ]);

        $response->assertNotFound()->assertExactJson(['message' => 'Product not found.']);
        $this->assertSame([], $this->mail->sent());
    }

    /**
     * @param  array<string, string>  $input
     */
    #[DataProvider('invalidEnquiries')]
    public function test_an_invalid_enquiry_names_the_bad_field_and_sends_nothing(array $input, string $badField): void
    {
        $response = $this->postJson('/api/products/3/enquiries', $input);

        $response->assertUnprocessable()->assertJsonValidationErrors([$badField]);
        $this->assertSame([], $this->mail->sent());
    }

    /**
     * @return array<string, array{array<string, string>, string}>
     */
    public static function invalidEnquiries(): array
    {
        $valid = ['name' => 'Priya Shah', 'email' => 'priya@example.com', 'message' => 'Is this in stock?'];

        return [
            'blank name' => [[...$valid, 'name' => '   '], 'name'],
            'missing name' => [['email' => $valid['email'], 'message' => $valid['message']], 'name'],
            'name too long' => [[...$valid, 'name' => str_repeat('a', 101)], 'name'],
            'blank email' => [[...$valid, 'email' => ''], 'email'],
            'not an email address' => [[...$valid, 'email' => 'priya-at-example'], 'email'],
            'blank message' => [[...$valid, 'message' => ''], 'message'],
            'message too long' => [[...$valid, 'message' => str_repeat('a', 2001)], 'message'],
        ];
    }
}
