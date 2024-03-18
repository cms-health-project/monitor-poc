<?php

declare(strict_types=1);

namespace App\Command;

use App\Business\Alerting\AlertChannel;
use App\Business\Alerting\AlertChannelFactory;
use App\Business\Exception\ConfigurationException;
use App\Business\Retriever\YamlRetriever;
use App\Business\Storage\FileStorage;
use App\Health\HealthStatus;
use GuzzleHttp\Client;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use Twig\Environment;

#[AsCommand(
    name: 'app:health:fetch',
    description: 'Fetch all the health data',
    hidden: false
)]
class FetchHealthStatusCommand extends Command
{
    protected Environment $twig;
    private string $dashboardUrl;

    public function __construct(Environment $environment)
    {
        parent::__construct();
        $this->twig = $environment;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $configFile = __DIR__ . '/../../config/health.config.yaml';

        $retriever = new YamlRetriever(__DIR__ . '/../../config/endpoints.yaml');

        $storage = new FileStorage(__DIR__ . '/../../_storage');

        $alertingChannels = $this->getAlertingChannels($configFile);

        $client = new Client();

        foreach ($retriever->getEndpoints() as $key => $endpoint) {
            $response = $client->send($endpoint->toRequest());

            $array = json_decode((string)$response->getBody(), true);

            $array['_internal'] = [
                'fetched' => time()
            ];

            $this->sendAlerts($alertingChannels, $key, $array, $storage->getHealthCheckResult($key));

            $storage->storeHealthCheckResult($key, $array);
        }

        return Command::SUCCESS;
    }

    protected function sendAlerts(array $alertingChannels, string $endpoint, array $currentStatus, array|false $previousStatus): void
    {
        /** @var AlertChannel[] $channels */
        $channels = [];

        if ($previousStatus && $currentStatus['status'] !== $previousStatus['status']) {
            $channels = array_merge($channels, $alertingChannels[AlertChannel::CONSTRAINT_ON_CHANGE]);
        }

        if ($currentStatus['status'] !== HealthStatus::SUCCESS) {
            $channels = array_merge($channels, $alertingChannels[AlertChannel::CONSTRAINT_FAILED]);
        }

        $channels = array_merge($channels, $alertingChannels[AlertChannel::CONSTRAINT_ALL]);

        foreach ($channels as $alertingChannel) {
            $alertingChannel->sendAlert($endpoint, $currentStatus);
        }
    }

    /**
     * @return \App\Business\Alerting\AlertChannel[]
     */
    protected function getAlertingChannels(string $configFile): array
    {
        $config = Yaml::parse(file_get_contents($configFile));

        $channels = [
            AlertChannel::CONSTRAINT_FAILED => [],
            AlertChannel::CONSTRAINT_ALL => [],
            AlertChannel::CONSTRAINT_ON_CHANGE => [],
        ];

        if (!array_key_exists('alerting', $config) || !array_key_exists('channels', $config['alerting'])) {
            return $channels;
        }

        if (!array_key_exists('dashboardUrl', $config['alerting'])) {
            throw new ConfigurationException('When alerting channels are defined then the "dashboardUrl" field is mandatory');
        }

        $dashboardUrl = $config['alerting']['dashboardUrl'];

        foreach ($config['alerting']['channels'] as $channel) {
            $options = $channel['options'];

            if (!array_key_exists('dashboardUrl', $options)) {
            }

            $channels[$channel['constraint']][] = AlertChannelFactory::getAlertingChannel($channel['type'], $channel['options'], $this->twig);
        }

        return $channels;
    }
}
