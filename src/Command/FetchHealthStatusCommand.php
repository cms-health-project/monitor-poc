<?php

declare(strict_types=1);

namespace App\Command;

use App\Business\Alerting\AlertChannelFactory;
use App\Business\Retriever\FileRetriever;
use App\Business\Storage\FileStorage;
use App\Health\HealthStatus;
use GuzzleHttp\Client;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

#[AsCommand(
    name: 'app:health:fetch',
    description: 'Fetch all the health data',
    hidden: false
)]
class FetchHealthStatusCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $configFile = __DIR__ . '/../../config/health.config.yaml';

        $retriever = new FileRetriever(__DIR__ . '/../../config/endpoints.csv');

        $storage = new FileStorage(__DIR__ . '/../../_storage');

        $alertingChannels = $this->getAlertingChannels($configFile);

        $client = new Client();

        foreach ($retriever->getEndpoints() as $endpoint) {
            $response = $client->get($endpoint);

            $array = json_decode((string)$response->getBody(), true);

            $array['_internal'] = [
                'fetched' => time()
            ];

            if ($array['status'] !== HealthStatus::SUCCESS) {
                foreach ($alertingChannels as $alertingChannel) {
                    $alertingChannel->sendAlert($alertingChannels);
                }
            }

            $storage->storeHealthCheckResult($endpoint, $array);
        }

        return Command::SUCCESS;
    }

    /**
     * @return \App\Business\Alerting\AlertChannel[]
     */
    protected function getAlertingChannels(string $configFile): array
    {
        $config = Yaml::parse(file_get_contents($configFile));

        $channels = [];

        foreach ($config['alerting']['channels'] as $channel) {
            $channels[] = AlertChannelFactory::getAlertingChannel($channel['type'], $channel['options']);
        }

        return $channels;
    }
}
