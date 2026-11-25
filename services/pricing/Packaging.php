<?php
namespace Wooden;

final class ProtectivePackaging
{
    private const MATERIALS_PER_UNIT_KOPECKS = 18000;
    private const LABOUR_PER_UNIT_KOPECKS = 7000;

    public function price(int $units): int
    {
        return $this->materials($units) + $this->labour($units);
    }

    private function materials(int $units): int { return $units * self::MATERIALS_PER_UNIT_KOPECKS; }
    private function labour(int $units): int { return $units * self::LABOUR_PER_UNIT_KOPECKS; }
}

final class CratePackaging
{
    private const UNITS_PER_CRATE = 2;
    private const CRATE_PRICE_KOPECKS = 90000;

    public function calculateCost(array $goods): array
    {
        // Короб вмещает два предмета; неполный короб оплачивается целиком.
        return ['amount_kopecks' => (int) ceil($goods['units'] / self::UNITS_PER_CRATE) * self::CRATE_PRICE_KOPECKS];
    }
}
