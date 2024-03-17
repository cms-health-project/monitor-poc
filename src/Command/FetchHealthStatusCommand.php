<?php

declare(strict_types=1);

namespace App\Command;

use App\Business\Alerting\AlertChannel;
use App\Business\Alerting\AlertChannelFactory;
use App\Business\Retriever\FileRetriever;
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
            $response = $client->get($endpoint);

            $array = json_decode((string)$response->getBody(), true);

            $array['_internal'] = [
                'fetched' => time()
            ];

            $this->sendAlerts($alertingChannels, $key, $array, $storage->getHealthCheckResult($endpoint));

            $storage->storeHealthCheckResult($endpoint, $array);
        }

        return Command::SUCCESS;
    }

    protected function sendAlerts(array $alertingChannels, string $endpoint, array $currentStatus, array $previousStatus): void
    {
        /** @var AlertChannel[] $channels */
        $channels = [];

        if ($currentStatus['status'] !== $previousStatus['status']) {
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

        foreach ($config['alerting']['channels'] as $channel) {
            $channels[$channel['constraint']][] = AlertChannelFactory::getAlertingChannel($channel['type'], $channel['options'], $this->twig);
        }

        return $channels;
    }
}
