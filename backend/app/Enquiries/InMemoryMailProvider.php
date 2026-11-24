<?php

namespace App\Enquiries;

/**
 * A mail provider that keeps what it was asked to send in memory. Used by
 * tests, where nothing should leave the process.
 */
final class InMemoryMailProvider implements MailProvider
{
    /** @var list<array{to: string, subject: string, body: string, replyTo: string|null}> */
    private array $sent = [];

    public function send(string $to, string $subject, string $body, ?string $replyTo = null): void
    {
        $this->sent[] = ['to' => $to, 'subject' => $subject, 'body' => $body, 'replyTo' => $replyTo];
    }

    /**
     * @return list<array{to: string, subject: string, body: string, replyTo: string|null}>
     */
    public function sent(): array
    {
        return $this->sent;
    }
}
