<?php

declare(strict_types=1);

namespace App\Services;

class PriceModifierBase implements PriceModifierInterface {
    public function calculate(int $price): int {
        return (int) ceil($price);
    }
}
