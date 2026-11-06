<?php
namespace Wooden;

final class CourierDelivery
{
    private ?int $tripKm = null;

    public function quote(int $distanceKm): int
    {
        $this->tripKm = $distanceKm;
        try {
            return 60000 + $this->distanceCharge();
        } finally {
            $this->tripKm = null;
        }
    }

    private function distanceCharge(): int
    {
        // Первые 10 км входят в базовую стоимость; доплата считается только за превышение.
        return max(0, $this->tripKm - 10) * 2500;
    }
}

final class FreightDelivery
{
    public function estimate(array $shipment): array
    {
        // Стоимость перевозки возвращается в копейках.
        return ['rubles' => 900 + (int) ceil($shipment['weight_kg'] / 10) * 120];
    }
}
