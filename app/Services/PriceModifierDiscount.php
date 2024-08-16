<?php

declare(strict_types=1);

namespace App\Services;

class PriceModifierDiscount implements PriceModifierInterface {
    public function __construct(
        protected PriceModifierInterface $calculator,
        protected float $discountPercentage,
    ) {}

    public function calculate(int $price): int {
        return (int) ceil($this->calculator->calculate($price) * (1 - $this->discountPercentage/100));
    }
}
