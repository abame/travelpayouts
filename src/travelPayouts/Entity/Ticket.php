<?php

declare(strict_types=1);

namespace TravelPayouts\Entity;

use DateTime;
use InvalidArgumentException;
use TravelPayouts\Services\TicketsServiceInterface;

class Ticket
{
    public const AVIASALES = 'aviasales';

    public const JETRADAR = 'jetradar';

    /**
     * @var City|Airport
     */
    private $origin;

    /**
     * @var City|Airport
     */
    private $destination;

    private DateTime $departDate;

    private DateTime $returnDate;

    private int $value;

    private string $currency;

    private int $distance;

    private bool $actual;

    private DateTime $foundAt;

    private int $tripClass;

    private bool $showToAffiliates;

    private int $numberOfChanges;

    private string $airline;

    private ?DateTime $expires;

    private int $flightNumber;

    public function getValue(): int
    {
        return $this->value;
    }

    public function setValue(int $value): Ticket
    {
        $this->value = $value;

        return $this;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): Ticket
    {
        $this->currency = $currency;

        return $this;
    }

    public function getDistance(): int
    {
        return $this->distance;
    }

    public function setDistance(int $distance): Ticket
    {
        $this->distance = $distance;

        return $this;
    }

    public function isActual(): bool
    {
        return $this->actual;
    }

    public function setActual(bool $actual): Ticket
    {
        $this->actual = $actual;

        return $this;
    }

    public function getFoundAt(): DateTime
    {
        return $this->foundAt;
    }

    public function setFoundAt(DateTime $foundAt): Ticket
    {
        $this->foundAt = $foundAt;

        return $this;
    }

    public function isShowToAffiliates(): bool
    {
        return $this->showToAffiliates;
    }

    public function setShowToAffiliates(bool $showToAffiliates): Ticket
    {
        $this->showToAffiliates = $showToAffiliates;

        return $this;
    }

    public function getNumberOfChanges(): int
    {
        return $this->numberOfChanges;
    }

    public function setNumberOfChanges(int $numberOfChanges): Ticket
    {
        $this->numberOfChanges = $numberOfChanges;

        return $this;
    }

    public function getAirline(): string
    {
        return $this->airline;
    }

    public function setAirline(string $airline): Ticket
    {
        $this->airline = $airline;

        return $this;
    }

    public function getExpires(): ?DateTime
    {
        return $this->expires;
    }

    public function setExpires(?DateTime $expires): Ticket
    {
        $this->expires = $expires;

        return $this;
    }

    public function getFlightNumber(): int
    {
        return $this->flightNumber;
    }

    public function setFlightNumber(int $flightNumber): Ticket
    {
        $this->flightNumber = $flightNumber;

        return $this;
    }

    public function getUrl(string $type = 'aviasales'): string
    {
        $url = '';

        $getTripClass = function (int $class): string {
            $ticketClass = 'Y';

            switch ($class) {
                case TicketsServiceInterface::BUSINESS_CLASS:
                    $ticketClass = 'C';
                    break;
                case TicketsServiceInterface::FIRST_CLASS:
                    $ticketClass = 'F';
                    break;
                case TicketsServiceInterface::ECONOMY_CLASS:
                    $ticketClass = 'Y';
                    break;
            }

            return $ticketClass;
        };

        switch ($type) {
            case self::AVIASALES:
                $url .= 'https://search.aviasales.ru/';
                $url .= mb_strtoupper($this->getOrigin()->getIata()) . $this->getDepartDate()->format('dm');
                $url .= mb_strtolower($this->getDestination()->getIata()) . $this->getReturnDate()->format('dm');
                $url .= '1';
                break;
            case self::JETRADAR:
                $url .= 'https://www.jetradar.com/searches/';
                $origin = $this->getOrigin();
                $dest = $this->getDestination();

                $url .= $origin instanceof Airport ? 'A' : 'C';
                $url .= mb_strtoupper($origin->getIata()) . $this->getDepartDate()->format('dm');
                $url .= $dest instanceof Airport ? 'A' : 'C';
                $url .= mb_strtolower($dest->getIata()) . $this->getReturnDate()->format('dm');
                $url .= $getTripClass($this->getTripClass()) . '1';
                break;
            default:
                throw new InvalidArgumentException('Type of website not found');
        }

        return $url;
    }

    /** @return City|Airport */
    public function getOrigin()
    {
        return $this->origin;
    }

    /** @param City|Airport $origin */
    public function setOrigin($origin): Ticket
    {
        $this->origin = $origin;

        return $this;
    }

    public function getDepartDate(): DateTime
    {
        return $this->departDate;
    }

    public function setDepartDate(DateTime $departDate): Ticket
    {
        $this->departDate = $departDate;

        return $this;
    }

    /** @return City|Airport */
    public function getDestination()
    {
        return $this->destination;
    }

    /** @param City|Airport $destination */
    public function setDestination($destination): Ticket
    {
        $this->destination = $destination;

        return $this;
    }

    public function getReturnDate(): DateTime
    {
        return $this->returnDate;
    }

    public function setReturnDate(DateTime $returnDate): Ticket
    {
        $this->returnDate = $returnDate;

        return $this;
    }

    public function getTripClass(): int
    {
        return $this->tripClass;
    }

    public function setTripClass(int $tripClass): Ticket
    {
        $this->tripClass = $tripClass;

        return $this;
    }
}
