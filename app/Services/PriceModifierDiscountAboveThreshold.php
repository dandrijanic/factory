<?php

declare(strict_types=1);

namespace App\Services;

class PriceModifierDiscountAboveThreshold implements PriceModifierInterface {
    public function __construct(
        protected PriceModifierInterface $calculator,
        protected int $threshold,
        protected int $discountPercentage,
    ) {}

    public function calculate(int $price): int {
        if ($price < $this->threshold) {
            return (int) ceil($this->calculator->calculate($price));
        }

        return (int) ceil($this->calculator->calculate($price) * (1 - $this->discountPercentage/100));
    }
}
