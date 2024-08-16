<?php

declare(strict_types=1);

namespace App\Services;

interface PriceModifierInterface {
    public function calculate(int $price): int;
}
