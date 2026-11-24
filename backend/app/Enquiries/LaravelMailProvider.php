<?php

namespace App\Enquiries;

use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Exception\ExceptionInterface as MimeException;

/**
 * Sends through whichever mailer Laravel is configured with (MAIL_MAILER).
 * Locally that is SMTP to Mailpit; moving to a real provider is a change of
 * configuration or one new MailProvider implementation (ADR 0002).
 */
final class LaravelMailProvider implements MailProvider
{
    public function send(string $to, string $subject, string $body, ?string $replyTo = null): void
    {
        try {
            Mail::raw($body, function (Message $message) use ($to, $subject, $replyTo): void {
                $message->to($to)->subject($subject);
                if ($replyTo !== null) {
                    $message->replyTo($replyTo);
                }
            });
        } catch (TransportExceptionInterface|MimeException $exception) {
            throw new MailDeliveryFailed('The email could not be delivered.', 0, $exception);
        }
    }
}
