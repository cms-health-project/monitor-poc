<?php

namespace App\Controller;

use App\Business\Retriever\FileRetriever;
use App\Business\Storage\FileStorage;
use App\Health\HealthStatus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WebController extends AbstractController
{
    #[Route('user/{userId}', methods: ['GET'])]
    public function getHealthStatusForUser(string $userId = ""): Response
    {
        $retriever = new FileRetriever(__DIR__ . '/../../config/endpoints.csv');
        $storage = new FileStorage(__DIR__ . '/../../_storage');

        $healthStatuses = [];

        foreach ($retriever->getEndpoints() as $endpoint) {
            $status = $storage->getHealthCheckResult($endpoint);

            $errorMessages = [];

            foreach ($status['checks'] as $checkName => $checkList) {
                foreach ($checkList as $check) {
                    if ($check['status'] !== HealthStatus::SUCCESS) {
                        $errorMessages[] = $checkName . ': ' . $check['output'];
                    }
                }
            }

            $healthStatuses[$endpoint] = ['status' => $status, 'errors' => $errorMessages];
        }

        return $this->render('results.html.twig', ['healthStatuses' => $healthStatuses]);
    }
}
