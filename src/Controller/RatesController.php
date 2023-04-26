<?php

namespace App\Controller;

use App\Enums\CurrencyEnum;
use App\Service\CurrencyRateService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class RatesController extends AbstractController
{

    public function __construct(
        private CurrencyRateService $currencyRateService
    ) {}

    #[Route('/rates/{currency}', name: 'get_rates')]
    public function getRates(
        string $currency
    ): JsonResponse
    {
        try {

            $data = $this->currencyRateService->getCachedRates($currency);

            return new JsonResponse([
                'message' => $data,
                'status' => true
            ]);
        } catch (\Throwable $throwable) {
            return new JsonResponse([
                'message' => 'Error fetching cached rates: ' . $throwable->getMessage(),
                'status' => false
            ]);
        }
    }

    #[Route('/rates', name: 'get_rates_usd')]
    public function getRatesForUSD(): JsonResponse
    {

        try {
            $data = $this->currencyRateService->getCachedRates('usd');

            return new JsonResponse([
                'message' => $data,
                'status' => true
            ]);
        } catch (\Throwable $throwable) {
            return new JsonResponse([
                'message' => 'Error fetching cached rates: ' . $throwable->getMessage(),
                'status' => false
            ]);
        }


    }

}