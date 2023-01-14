<p align="center"><img width="200" src="https://i.ibb.co/YpVGRZP/money-management.png" alt="MoneyPHP Operations" /></p>

[![Build Status](https://github.com/rogervila/moneyphp-operations/workflows/build/badge.svg)](https://github.com/rogervila/moneyphp-operations/actions)
[![StyleCI](https://github.styleci.io/repos/588556534/shield?branch=main)](https://github.styleci.io/repos/588556534)
[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=rogervila_moneyphp-operations&metric=alert_status)](https://sonarcloud.io/dashboard?id=rogervila_moneyphp-operations)
<!--[![Coverage](https://sonarcloud.io/api/project_badges/measure?project=rogervila_moneyphp-operations&metric=coverage)](https://sonarcloud.io/dashboard?id=rogervila_moneyphp-operations)-->

[![Latest Stable Version](https://poser.pugx.org/rogervila/moneyphp-operations/v/stable)](https://packagist.org/packages/rogervila/moneyphp-operations)
[![Total Downloads](https://poser.pugx.org/rogervila/moneyphp-operations/downloads)](https://packagist.org/packages/rogervila/moneyphp-operations)
[![License](https://poser.pugx.org/rogervila/moneyphp-operations/license)](https://packagist.org/packages/rogervila/moneyphp-operations)

# MoneyPHP Operations

## About

MoneyPHP Operations brings a set of helpers to manipulate money with [MoneyPHP](https://www.moneyphp.org).

## Install

```
composer require rogervila/moneyphp-operations
```

## Usage

> Note: Pull requests with new helpers are welcome!

### Percentage increase

```php
use Money\Money; 
use MoneyOperation\Operation;

$money = Money::EUR('100'); // 1€

$increasedMoney = Operation::of($money)->percentageIncrease('20') // 1.20€
```

### Percentage decrease

```php
use Money\Money; 
use MoneyOperation\Operation;

$money = Money::EUR('288'); // 2.88€

// percentageDecrease accepts positive and negative numeric strings
$decreasedMoney = Operation::of($money)->percentageDecrease('2.99') // 2.79€
$decreasedMoney = Operation::of($money)->percentageDecrease('-2.99') // 2.79€
```

### Percentage difference

```php
use Money\Money; 
use MoneyOperation\Operation;

$moneyA = Money::EUR('100'); // 1€
$moneyB = Money::EUR('120'); // 1.20€

// Returns a float. Use number_format to format the result 
$percentage = Operation::of($moneyA)->percentageDifference($moneyB) // 20.0
```

### Split

```php
use Money\Money; 
use MoneyOperation\Operation;

$money = Money::EUR('1000'); // 10€
 
/**
 * Will try to increase the first part when cannot be split equally
 * Throws \MoneyOperation\Exceptions\InvalidOperationException when cannot be split at all (for very low values mainly)
 */
$parts = Operation::of($money)->split(3) // [Money::EUR('334'), Money::EUR('333'), Money::EUR('333')]
```

### Join

```php
use Money\Money; 
use MoneyOperation\Operation;

$parts = [Money::EUR('334'), Money::EUR('333'), Money::EUR('333')];
 
$money = Operation::of($money)->join($parts) // 10€
```

## Author

Created by [Roger Vilà](https://rogervila.es)

## License

MoneyPHP Operations is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

Icons made by <a href="https://www.flaticon.es/autores/prosymbols-premium" title="Freepik">Prosymbols Premium</a> from <a href="https://www.flaticon.es/iconos-gratis/administracion-del-dinero" title="Flaticon">www.flaticon.es</a>
