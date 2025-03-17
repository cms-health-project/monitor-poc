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
        $endpoints = include __DIR__ . '/../../config/endpoints.php';
        $storage = new FileStorage(__DIR__ . '/../../_storage');

        $healthStatuses = [];

        foreach ($endpoints as $label => $endpoint) {
            $errorMessages = [];
            $status = $storage->getHealthCheckResult($label);
            if (!is_array($status) || $status === []) {
                continue;
            }

            foreach ($status['checks'] as $checkName => $checkList) {
                foreach ($checkList as $check) {
                    if ($check['status'] !== HealthStatus::SUCCESS) {
                        $errorMessages[] = $checkName . ': ' . ($check['output'] ?? '');
                    }
                }
            }

            $healthStatuses[$endpoint['url']] = ['status' => $status, 'errors' => $errorMessages];
        }

        return new JsonResponse(['status' => 'success', 'message' => 'Health status fetched for user ' . $userId, 'data' => $healthStatuses]);
    }
}
