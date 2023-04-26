<?php

namespace App\Controller;

use App\Enums\CurrencyEnum;
use App\Service\CurrencyRateService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ConverterController extends AbstractController
{

    public function __construct(
        private CurrencyRateService $currencyRateService
    ) {}

    #[Route('/converter/{from}/{to}', name: 'converter', methods: ['POST'])]
    public function getRates(
        string $from,
        string $to,
        Request $request
    ): JsonResponse
    {
        try {

            $amount = $request->toArray()['amount'];

            $data = $this->currencyRateService->convert(
                $from,
                $to,
                $amount
            );

            return new JsonResponse([
                'message' => $data,
                'status' => true,
            ]);
        } catch (\Throwable $throwable) {
            return new JsonResponse([
                'message' => 'Error calculating: ' . $throwable->getMessage(),
                'status' => false
            ]);
        }

    }
}