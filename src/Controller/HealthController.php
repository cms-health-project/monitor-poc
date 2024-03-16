<?php

namespace App\Controller;

use App\Business\Retriever\FileRetriever;
use App\Business\Storage\FileStorage;
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

        $healthStatus = [];

        foreach ($retriever->getEndpoints() as $endpoint) {
            $healthStatus[$endpoint] = $storage->getHealthCheckResult($endpoint);
        }

        return new JsonResponse(['status' => 'success', 'message' => 'Health status fetched for user ' . $userId, 'data' => $healthStatus]);
    }
}
