<?php

declare(strict_types=1);

namespace Tests\TravelPayouts\Services;

use DateTime;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use TravelPayouts\Services\PartnerService;
use TravelPayouts\Travel;

class PartnerServiceTest extends TestCase
{
    protected PartnerService $service;

    public function testGetBalance(): void
    {
        $balance = $this->service->getBalance();

        self::assertGreaterThanOrEqual(0, $balance['balance']);
        self::assertContains($balance['currency'], ['rub', 'usd', 'eur']);
    }

    public function testGetPayments(): void
    {
        /** no sales therefore runtime exception thrown */
        $this->expectException(RuntimeException::class);
        $payments = $this->service->getPayments();

        foreach ($payments as $payment) {
            self::assertGreaterThanOrEqual(0, $payment['amount']);
        }
    }

    public function testGetSales(): void
    {
        $today = new DateTime('now');
        $date = new DateTime($today->format('Y-m'));

        $sales = $this->service->getSales('date', $date->format('Y-m'));

        $period = [
            $date->getTimestamp(),
            $date->modify('first day of next month')->getTimestamp(),
        ];

        foreach ($sales as $sale) {
            $this->assertEmpty($sale['key']);
            self::assertLessThanOrEqual($period[0], strtotime($sale['key']));
            self::assertLessThanOrEqual($period[1], strtotime($sale['key']));

            $saleCount = 0;

            $saleCount += array_sum($sale['flights']) + array_sum($sale['hotels']);

            self::assertGreaterThan(0, $saleCount);
        }
    }

    public function testGetDetailedSales(): void
    {
        $today = new DateTime('now');
        $date = new DateTime($today->format('Y-m'));

        $sales = $this->service->getDetailedSales($date->format('Y-m'));

        $period = [
            $date->getTimestamp(),
            $date->modify('first day of next month')->getTimestamp(),
        ];

        foreach ($sales as $key => $sale) {
            self::assertGreaterThanOrEqual($period[0], strtotime($key));
            self::assertLessThanOrEqual($period[1], strtotime($key));

            $saleCount = 0;

            foreach ($sale as $types) {
                foreach ($types as $type) {
                    $saleCount += array_sum($type);
                }
            }

            self::assertGreaterThan(0, $saleCount);
        }
    }

    protected function setUp(): void
    {
        $travel = new Travel('DUMMY_TOKEN');
        $this->service = $travel->getPartnerService();

        date_default_timezone_set('UTC');
    }
}
