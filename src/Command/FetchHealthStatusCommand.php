<?php

declare(strict_types=1);

namespace App\Command;

use App\Business\Alerting\EmailAlertChannel;
use App\Business\Retriever\FileRetriever;
use App\Business\Storage\FileStorage;
use App\Business\Storage\NullStore;
use App\Health\HealthStatus;
use GuzzleHttp\Client;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:health:fetch',
    description: 'Fetch all the health data',
    hidden: false
)]
class FetchHealthStatusCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $retriever = new FileRetriever(__DIR__ . '/../../config/endpoints.csv');

        $storage = new FileStorage(__DIR__. '/../../_storage');
        $alertChannel = new EmailAlertChannel('nils.langner@startwind.io');

        $client = new Client();

        foreach ($retriever->getEndpoints() as $endpoint) {
            $response = $client->get($endpoint);

            $array = json_decode((string)$response->getBody(), true);

            $array['_internal'] = [
              'fetched' => time()
            ];

            if ($array['status'] !== HealthStatus::SUCCESS) {
                $alertChannel->sendAlert($array);
            }

            $storage->storeHealthCheckResult($endpoint, $array);
        }

        return Command::SUCCESS;
    }
}
