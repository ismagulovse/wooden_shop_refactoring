<?php
namespace Wooden;

final class ExtendedWarranty
{
    private const ANNUAL_PERCENT = 2;
    private const MONTHS_PER_YEAR = 12;
    private const PERCENT_DIVISOR = 100;

    public function premium(int $subtotalKopecks, int $months): int
    {
        // Любая часть года оплачивается как полный год дополнительной гарантии.
        return $this->annualPremium($subtotalKopecks) * (int) ceil($months / self::MONTHS_PER_YEAR);
    }

    private function annualPremium(int $coveredValue): int
    {
        return intdiv($coveredValue * self::ANNUAL_PERCENT, self::PERCENT_DIVISOR);
    }
}

final class FixedWarranty
{
    private const UNIT_PRICE_RUBLES = 500;
    private const MONTHS_PER_YEAR = 12;

    public function planCost(array $contract): array
    {
        return ['rubles' => $contract['units'] * self::UNIT_PRICE_RUBLES * (int) ceil($contract['months'] / self::MONTHS_PER_YEAR)];
    }
}
