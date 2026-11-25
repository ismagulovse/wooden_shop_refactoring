<?php
namespace Wooden;

final class WeightRecycling
{
    private const INCLUDED_KG = 20;
    private const BASE_FEE_KOPECKS = 50000;
    private const KG_RATE_KOPECKS = 1500;

    public function fee(int $weightKg): int
    {
        $billableKg = max(0, $weightKg - self::INCLUDED_KG);
        return self::BASE_FEE_KOPECKS + $this->disposalCharge($billableKg);
    }

    private function disposalCharge(int $billableKg): int
    {
        return $billableKg * self::KG_RATE_KOPECKS;
    }
}

final class ItemRecycling
{
    private const BASE_FEE_KOPECKS = 30000;
    private const ITEM_RATE_KOPECKS = 40000;

    public function removalCost(int $pieces): array
    {
        return ['amount_kopecks' => self::BASE_FEE_KOPECKS + $pieces * self::ITEM_RATE_KOPECKS];
    }
}
