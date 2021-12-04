# Travel Payouts PHP SDK

## Installation

### Composer

```
composer require abame/travelpayouts
```

## Usage

 First create a main Travel object and pass your token in it 
```php
use TravelPayouts\Travel; 

$travel = new Travel('YOUR TOKEN HERE');
```
Then you can use it get different services

### Tickets service
```php
$ticketService = $travel->getTicketsService();
//Get flights found by our users in the last 48 hours from PAR to FRA. Return array consists of TravelPayouts\Ticket objects.
$flights = $ticketService->getLatestPrices('PAR', 'FRA', false, 'eur', 'year', 1, 10);
```

See [documentation](https://github.com/abame/travelpayouts/wiki/TicketService)

### Flight service
```php
$flightService = $travel->getFlightService();
$flightService
       ->setIp('127.0.0.1')
       ->setHost('aviasales.ru')
       ->setMarker('123')
       ->addPassenger('adults', 2)
       ->addSegment('PAR', 'FRA', '2021-12-20');
$searchData    = $flightService->search('ru', 'Y');
$searchResults = $flightService->getSearchResults($searchData['search_id']);
```

### Partner service
```php
$partnerService = $travel->getPartnerService();
//get user balance and currency of the balance
list($balance, $currency) = $partnerService->getBalance();
```

### Data service
```php
$dataService = $travel->getDataService();
//get all airports in the system
$airports    = $dataService->getAirports(); 
```

