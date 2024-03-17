<?php

namespace App\Business\Alerting;

use App\Health\HealthStatus;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class EmailAlertChannel extends AbstractAlertChannel
{
    const ALERT_CHANNEL_IDENTIFIER = 'email';

    private array $emailAddresses;

    private string $theme = 'default';

    private Mailer $mailer;

    private array $defaultOptions = [
        'subject' => [
            'failed' => "Ohh no!!"
        ]
    ];

    private Environment $twig;

    public function __construct(array $options, Environment $twig)
    {
        $options = array_merge_recursive($this->defaultOptions, $options);

        $this->twig = $twig;

        if (array_key_exists('dsn', $options)) {
            $dsn = $options['dsn'];
        } else {
            $this->checkOptions($options, ['emailAddresses', 'smtpHost', 'smtpUser', 'smtpPassword']);

            $dsn = 'smtp://' . $options['smtpUser'] . ':' . urlencode($options['smtpPassword']) . '@' . $options['smtpHost'] . ':587';
        }

        if (array_key_exists('theme', $options)) {
            $this->theme = $options['theme'];
        }

        $this->emailAddresses = $options['emailAddresses'];

        $this->mailer = new Mailer(Transport::fromDsn($dsn));
    }

    public function sendAlert(string $endpoint, array $healthCheckResult): void
    {
        if ($healthCheckResult['status'] === HealthStatus::SUCCESS) {
            $content = $this->twig->render('alerts/emails/' . $this->theme . '/success.html.twig', [$healthCheckResult]);
        } else {
            $content = $this->twig->render('alerts/emails/' . $this->theme . '/failed.html.twig', [$healthCheckResult]);
        }

        $email = (new Email())
            ->from('me@zeshanahmed.com')
            ->to('premiumsrapidshares@gmail.com')
            ->bcc($this->emailAddresses[0])
            ->subject('Alert')
            ->text('This is a test email sent via Symfony Mailer.');

        $this->mailer->send($email);
    }
}
