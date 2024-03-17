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

    private string $from = '';
    private array $emailAddresses;

    private string $theme = 'default';

    private Mailer $mailer;
    private array $defaultOptions = [
        'from' => 'me@zeshanahmed.com',
        'subject' => [
            'failed' => "There is a critical error on your {endpointName}",
            'success' => "Your endpoint ({endpointName}) is up and running again"
        ]
    ];
    private array $subjects = [];
    private Environment $twig;

    public function __construct(array $options, Environment $twig)
    {
        $options = $this->array_merge_recursive_distinct($this->defaultOptions, $options);

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

        if (array_key_exists('from', $options)) {
            $this->from = $options['from'];
        }

        if (array_key_exists('subject', $options)) {
            foreach ($options['subject'] as $subjectKey => $subject) {
                $this->subjects[$subjectKey] = $subject;
            }
        }

        $this->emailAddresses = $options['emailAddresses'];

        $this->mailer = new Mailer(Transport::fromDsn($dsn));
    }

    public function sendAlert(string $endpoint, array $healthCheckResult): void
    {
        $errors = array();

        foreach ( $healthCheckResult['checks'] as $checkResults ) {
            foreach ( $checkResults as $checkResult ) {
                if ( $checkResult['status'] !== HealthStatus::SUCCESS && $checkResult['output'] ) {
                    $errors[] = $checkResult['output'];
                }
            }
        }

        if ($healthCheckResult['status'] === HealthStatus::SUCCESS) {
            $content = $this->twig->render('alerts/emails/' . $this->theme . '/success.html.twig', ['healthcheck' => $healthCheckResult, 'endpoint' => $endpoint]);
            $subject = $this->subjects['success'];
        } else {
            $content = $this->twig->render('alerts/emails/' . $this->theme . '/failed.html.twig', ['healthcheck' => $healthCheckResult, 'endpoint' => $endpoint, 'errors' => $errors]);
            $subject = $this->subjects['failed'];
        }

        foreach ($this->emailAddresses as $emailAddress) {
            $email = (new Email())
                ->from($this->from)
                ->to($emailAddress)
                ->subject($subject)
                ->html($content);

            $this->mailer->send($email);
        }

    }

    public function array_merge_recursive_distinct( array &$array1, array &$array2 )
    {
      $merged = $array1;

      foreach ( $array2 as $key => &$value )
      {
        if ( is_array ( $value ) && isset ( $merged [$key] ) && is_array ( $merged [$key] ) )
        {
          $merged [$key] = $this->array_merge_recursive_distinct ( $merged [$key], $value );
        }
        else
        {
          $merged [$key] = $value;
        }
      }

      return $merged;
    }
}
