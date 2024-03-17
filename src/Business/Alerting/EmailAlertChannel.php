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
        $this->checkOptions($options, ['emailAddresses', 'smtpHost', 'smtpUser', 'smtpPassword']);

        $this->emailAddresses = $options['emailAddresses'];

        $dsn = 'smtp://' . $options['smtpUser'] . ':' . urlencode($options['smtpPassword']) . '@' . $options['smtpHost'] . ':587';

        $this->mailer = new Mailer(Transport::fromDsn($dsn));
    }

    public function sendAlert(array $healthCheckResult): void
    {
        $email = (new Email())
            ->from('nils.langner@startwind.io')
            ->to('nils.langner@startwind.io')
            ->bcc($this->emailAddresses[0])
            ->subject('Alert')
            ->text('This is a test email sent via Symfony Mailer.');

        $this->mailer->send($email);
    }
}
