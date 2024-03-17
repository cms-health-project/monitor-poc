<?php

namespace App\Business\Alerting;

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

class EmailAlertChannel extends AbstractAlertChannel
{
    const ALERT_CHANNEL_IDENTIFIER = 'email';

    private array $emailAddresses;

    private Mailer $mailer;

    public function __construct(array $options)
    {
        if ( array_key_exists( 'dsn', $options ) ) {
            $dsn = $options['dsn'];
        } else {
            $this->checkOptions($options, ['emailAddresses', 'smtpHost', 'smtpUser', 'smtpPassword']);

            $dsn = 'smtp://' . $options['smtpUser'] . ':' . urlencode($options['smtpPassword']) . '@' . $options['smtpHost'] . ':587';
        }

        $this->emailAddresses = $options['emailAddresses'];

        $this->mailer = new Mailer(Transport::fromDsn($dsn));
    }

    public function sendAlert(array $healthCheckResult): void
    {
        $email = (new Email())
            ->from('me@zeshanahmed.com')
            ->to('premiumsrapidshares@gmail.com')
            ->bcc($this->emailAddresses[0])
            ->subject('Alert')
            ->text('This is a test email sent via Symfony Mailer.');

        $this->mailer->send($email);
    }
}
