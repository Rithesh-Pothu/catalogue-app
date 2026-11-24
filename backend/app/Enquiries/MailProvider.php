<?php

namespace App\Enquiries;

/**
 * The seam between enquiries and whatever delivers email (ADR 0002).
 * To change the email provider, add one implementation and rebind it.
 */
interface MailProvider
{
    /**
     * Send one plain-text email.
     *
     * @param  string|null  $replyTo  where a reply should go, when that is not the sender
     *
     * @throws MailDeliveryFailed when the email could not be delivered
     */
    public function send(string $to, string $subject, string $body, ?string $replyTo = null): void;
}
