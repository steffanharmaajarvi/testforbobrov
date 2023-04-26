<?php

namespace App\DTO;

class CurrencyConversionRateDTO
{

    public function __construct(
        public readonly float $amount,
        public readonly CurrencyRateDTO $from,
        public readonly CurrencyRateDTO $to,
    ) {}

}