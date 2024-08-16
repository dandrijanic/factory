<?php

declare(strict_types=1);

namespace App\Services;

class PriceModifierVAT implements PriceModifierInterface {
    public function __construct(
        protected PriceModifierInterface $calculator,
        protected int $vatRate,
    ) {}

    public function calculate(int $price): int {
        return (int) ceil($this->calculator->calculate($price) * (1 + $this->vatRate/100));
    }
}
