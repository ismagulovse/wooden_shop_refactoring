<?php
namespace Wooden;

final class CourierDelivery
{
    // Стоимость указана в копейках.
    private const BASE_FEE = 60000;
    private const KM_RATE = 2500;

    private ?int $tripKm = null;

    public function quote(int $distanceKm): int
    {
        $this->tripKm = $distanceKm;
        try {
            return self::BASE_FEE + $this->distanceCharge();
        } finally {
            $this->tripKm = null;
        }
    }

    private function distanceCharge(): int
    {
        // Первые 10 км входят в базовую стоимость; доплата считается только за превышение.
        return max(0, $this->tripKm - 10) * self::KM_RATE;
    }
}

final class FreightDelivery
{
    public function estimate(array $shipment): array
    {
        return ['rubles' => 900 + (int) ceil($shipment['weight_kg'] / 10) * 120];
    }
}
