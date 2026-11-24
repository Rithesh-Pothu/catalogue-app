<?php

namespace App\Enquiries;

use App\Catalogue\Catalogue;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Turns an enquirer's request about one product into one email to the sales
 * inbox. Controllers hand input here and apply no enquiry rules themselves.
 */
final class Enquiries
{
    public function __construct(
        private readonly Catalogue $catalogue,
        private readonly MailProvider $mail,
        private readonly string $salesInbox,
    ) {}

    /**
     * @param  array<string, mixed>  $input  the enquirer's name, email and message
     *
     * @throws \App\Catalogue\ProductNotFound when there is no such product
     * @throws ValidationException when the input is not a usable enquiry; nothing is sent
     * @throws MailDeliveryFailed when the email could not be delivered
     */
    public function submit(int $productId, array $input): void
    {
        $product = $this->catalogue->product($productId);

        $enquiry = Validator::make($input, [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email:rfc', 'max:254'],
            'message' => ['required', 'string', 'max:2000'],
        ])->validate();

        $this->mail->send(
            $this->salesInbox,
            'Enquiry about '.$this->singleLine($product->title),
            implode("\n", [
                "Product: {$product->title} (id {$product->id})",
                "Name: {$enquiry['name']}",
                "Email: {$enquiry['email']}",
                '',
                $enquiry['message'],
            ]),
            $enquiry['email'],
        );
    }

    /** A subject must stay on one line, whatever the product title contains. */
    private function singleLine(string $text): string
    {
        return trim(preg_replace('/\s+/', ' ', $text) ?? $text);
    }
}
