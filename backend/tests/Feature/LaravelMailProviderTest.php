<?php

namespace Tests\Feature;

use App\Enquiries\MailDeliveryFailed;
use App\Enquiries\MailProvider;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Tests\TestCase;

/**
 * The real mail provider, run against Laravel's in-memory transport (tests use
 * MAIL_MAILER=array), so nothing leaves the process. Locally the same adapter
 * delivers to Mailpit over SMTP.
 */
class LaravelMailProviderTest extends TestCase
{
    public function test_the_default_mail_provider_delivers_a_plain_text_email_with_a_reply_to(): void
    {
        $this->app->make(MailProvider::class)->send(
            'sales@shop.test',
            'Enquiry about WD 2TB Hard Drive',
            "Name: Priya Shah\n\nIs this drive in stock?",
            'priya@example.com',
        );

        $messages = Mail::mailer()->getSymfonyTransport()->messages();
        $this->assertCount(1, $messages);

        $email = $messages[0]->getOriginalMessage();
        $this->assertSame('sales@shop.test', $email->getTo()[0]->getAddress());
        $this->assertSame('Enquiry about WD 2TB Hard Drive', $email->getSubject());
        $this->assertSame("Name: Priya Shah\n\nIs this drive in stock?", $email->getTextBody());
        $this->assertSame('priya@example.com', $email->getReplyTo()[0]->getAddress());
    }

    public function test_a_transport_failure_becomes_a_mail_delivery_failure(): void
    {
        Mail::extend('exploding', fn () => new class extends AbstractTransport
        {
            protected function doSend(SentMessage $message): void
            {
                throw new TransportException('Connection to the mail server refused.');
            }

            public function __toString(): string
            {
                return 'exploding://';
            }
        });
        config(['mail.mailers.exploding' => ['transport' => 'exploding'], 'mail.default' => 'exploding']);

        $this->expectException(MailDeliveryFailed::class);

        $this->app->make(MailProvider::class)->send('sales@shop.test', 'Subject', 'Body');
    }
}
