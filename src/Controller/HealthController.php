<?php

namespace App\Controller;

use App\Business\Retriever\YamlRetriever;
use App\Business\Storage\FileStorage;
use App\Health\HealthStatus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class HealthController extends AbstractController
{
    #[Route('api/v1/user/{userId}', methods: ['GET'])]
    public function getHealthStatusForUser(string $userId = ""): JsonResponse
    {
        // $retriever = new FileRetriever(__DIR__ . '/../../config/endpoints.csv');
        $retriever = new YamlRetriever(__DIR__ . '/../../config/endpoints.yaml');
        $storage = new FileStorage(__DIR__ . '/../../_storage');


        foreach ($retriever->getEndpoints() as $key => $endpoint) {
            $healthStatuses = [];
            $status = $storage->getHealthCheckResult($key);

            foreach ($status['checks'] as $checkName => $checkList) {
                foreach ($checkList as $check) {
                    if ($check['status'] !== HealthStatus::SUCCESS) {
                        $errorMessages[] = $checkName . ': ' . $check['output'];
                    }
                }
            }

            $healthStatuses[$key] = ['status' => $status, 'errors' => $errorMessages, 'name' => $key];
        }

        return new JsonResponse(['status' => 'success', 'message' => 'Health status fetched for user ' . $userId, 'data' => $healthStatuses]);
    }
}
