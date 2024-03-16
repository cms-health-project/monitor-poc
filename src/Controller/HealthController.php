<?php

namespace App\Controller;

use App\Business\Retriever\FileRetriever;
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
        $retriever = new FileRetriever(__DIR__ . '/../../config/endpoints.csv');
        $storage = new FileStorage(__DIR__ . '/../../_storage');

        $healthStatuses = [];

        foreach ($retriever->getEndpoints() as $endpoint) {
            $status = $storage->getHealthCheckResult($endpoint);

            foreach ($status['checks'] as $checkName => $checkList) {
                foreach ($checkList as $check) {
                    if ($check['status'] !== HealthStatus::SUCCESS) {
                        $errorMessages[] = $checkName . ': ' . $check['output'];
                    }
                }
            }

            $healthStatuses[$endpoint] = ['status' => $status, 'errors' => $errorMessages];
        }

        return new JsonResponse(['status' => 'success', 'message' => 'Health status fetched for user ' . $userId, 'data' => $healthStatuses]);
    }
}
