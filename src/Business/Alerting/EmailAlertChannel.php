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
    /**
     * @var \Twig\Environment
     */
    private Environment $twig;

    public function __construct(array $options, Environment $twig)
    {
        $this->twig = $twig;

        $this->checkOptions($options, ['emailAddresses', 'smtpHost', 'smtpUser', 'smtpPassword']);

        if (array_key_exists('theme', $options)) {
            $this->theme = $options['theme'];
        }

        $this->emailAddresses = $options['emailAddresses'];

        $dsn = 'smtp://' . $options['smtpUser'] . ':' . urlencode($options['smtpPassword']) . '@' . $options['smtpHost'] . ':587';

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
            ->from('nils.langner@startwind.io')
            ->to('nils.langner@startwind.io')
            ->bcc($this->emailAddresses[0])
            ->subject('Alert')
            ->text('This is a test email sent via Symfony Mailer.');

        $this->mailer->send($email);
    }
}
