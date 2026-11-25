<?php
namespace Wooden;

final class CourierDelivery
{
    // Стоимость указана в копейках.
    private const BASE_FEE = 60000;
    private const KM_RATE = 2500;
    private const INCLUDED_KM = 10;

    public function quote(int $distanceKm): int
    {
        return self::BASE_FEE + $this->distanceCharge($distanceKm);
    }

    private function distanceCharge(int $tripKm): int
    {
        // Первые 10 км входят в базовую стоимость; доплата считается только за превышение.
        return max(0, $tripKm - self::INCLUDED_KM) * self::KM_RATE;
    }
}

final class FreightDelivery
{
    private const BASE_FEE_RUBLES = 900;
    private const KG_PER_STEP = 10;
    private const STEP_RATE_RUBLES = 120;

    public function estimate(array $shipment): array
    {
        return ['rubles' => self::BASE_FEE_RUBLES + (int) ceil($shipment['weight_kg'] / self::KG_PER_STEP) * self::STEP_RATE_RUBLES];
    }
}
