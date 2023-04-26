<?php

namespace App\DTO;

class CurrencyRateDTO
{

    public function __construct(
        public readonly float $rate,
        public readonly string $code,
    ) {}

}