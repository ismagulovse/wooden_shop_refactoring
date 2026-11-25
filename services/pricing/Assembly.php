<?php
namespace Wooden;

final class WorkshopAssembly
{
    private const MINUTES_PER_UNIT = 45;
    private const MIN_CHARGE_KOPECKS = 60000;
    private const MINUTE_RATE_KOPECKS = 2000;

    public function calculate(int $units): int
    {
        // На сборку каждого предмета мебели закладывается 45 минут.
        return $this->workCharge($units * self::MINUTES_PER_UNIT);
    }

    private function workCharge(int $minutesForOrder): int
    {
        return max(self::MIN_CHARGE_KOPECKS, $minutesForOrder * self::MINUTE_RATE_KOPECKS);
    }
}

final class OnSiteAssembly
{
    private const BASE_VISIT_RUBLES = 1000;
    private const ITEM_RATE_RUBLES = 700;
    private const INCLUDED_KM = 10;
    private const EXTRA_KM_RATE_RUBLES = 20;

    public function visitPrice(int $itemCount, int $distanceKm): array
    {
        return ['rubles' => self::BASE_VISIT_RUBLES + $itemCount * self::ITEM_RATE_RUBLES + max(0, $distanceKm - self::INCLUDED_KM) * self::EXTRA_KM_RATE_RUBLES];
    }
}
