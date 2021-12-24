<?php

declare(strict_types=1);

namespace Tests\TravelPayouts\Services;

use DateTime;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use RuntimeException;
use TravelPayouts\Services\PartnerService;
use TravelPayouts\Travel;

class PartnerServiceTest extends TestCase
{
    use BaseServiceTrait;
    use ProphecyTrait;

    protected PartnerService $service;

    public function testGetBalance(): void
    {
        $client = $this->getClient('partner/balance', true);
        $this->service->setClient($client->reveal());
        $response = $this->service->getBalance();
        $this->assertIsArray($response);
        $this->assertArrayHasKey('balance', $response);
        $this->assertArrayHasKey('currency', $response);
    }

    public function testGetPayments(): void
    {
        $client = $this->getClient('partner/payments', true);
        $this->service->setClient($client->reveal());
        /** no sales therefore runtime exception thrown */
        $payments = $this->service->getPayments();

        foreach ($payments as $payment) {
            self::assertGreaterThanOrEqual(0, $payment['amount']);
        }
    }

    public function testGetSales(): void
    {
        $client = $this->getClient('partner/sales', true);
        $this->service->setClient($client->reveal());

        $today = new DateTime('now');
        $date = new DateTime($today->format('Y-m'));

        $sales = $this->service->getSales('date', $date->format('Y-m'));

        $period = [
            $date->getTimestamp(),
            $date->modify('first day of next month')->getTimestamp(),
        ];

        foreach ($sales as $sale) {
            $this->assertNotEmpty($sale['key']);
            self::assertLessThanOrEqual($period[0], strtotime($sale['key']));
            self::assertLessThanOrEqual($period[1], strtotime($sale['key']));

            $saleCount = 0;

            $saleCount += array_sum($sale['flights']) + array_sum($sale['hotels']);

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
