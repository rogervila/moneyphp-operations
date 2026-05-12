<p align="center"><img width="200" src="https://i.ibb.co/YpVGRZP/money-management.png" alt="MoneyPHP Operations" /></p>

[![Build Status](https://github.com/rogervila/moneyphp-operations/workflows/build/badge.svg)](https://github.com/rogervila/moneyphp-operations/actions)
[![StyleCI](https://github.styleci.io/repos/588556534/shield?branch=main)](https://github.styleci.io/repos/588556534)
[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=rogervila_moneyphp-operations&metric=alert_status)](https://sonarcloud.io/dashboard?id=rogervila_moneyphp-operations)

[![Latest Stable Version](https://poser.pugx.org/rogervila/moneyphp-operations/v/stable)](https://packagist.org/packages/rogervila/moneyphp-operations)
[![Total Downloads](https://poser.pugx.org/rogervila/moneyphp-operations/downloads)](https://packagist.org/packages/rogervila/moneyphp-operations)
[![License](https://poser.pugx.org/rogervila/moneyphp-operations/license)](https://packagist.org/packages/rogervila/moneyphp-operations)

# MoneyPHP Operations

MoneyPHP Operations provides a set of powerful helpers to manipulate and format money using [MoneyPHP](https://www.moneyphp.org).

## Installation

```bash
composer require rogervila/moneyphp-operations
```

## Usage

### Initialization

You can initialize an `Operation` instance using an existing `Money` object or directly from values.

```php
use Money\Money;
use MoneyOperation\Operation;

// From a Money object
$operation = Operation::of(Money::EUR(1000));

// From values (amount and currency)
$operation = Operation::ofValues(1000, 'EUR');
```

---

### Percentage Operations

#### Increase
Increase the amount by a given percentage.

```php
$money = Money::EUR('100'); // 1.00€
$increased = Operation::of($money)->percentageIncrease('20'); // 1.20€

// Custom rounding mode
$increased = Operation::of($money)->percentageIncrease('20', Money::ROUND_HALF_DOWN);
```

#### Decrease
Decrease the amount by a given percentage. Supports both positive and negative percentage strings.

```php
$money = Money::EUR('288'); // 2.88€
$decreased = Operation::of($money)->percentageDecrease('2.99'); // 2.79€
```

#### Difference
Calculate the percentage difference between two `Money` objects.

```php
$moneyA = Money::EUR('100');
$moneyB = Money::EUR('120');

$diff = Operation::of($moneyA)->percentageDifference($moneyB); // 20.0
```

---

### Collection Operations

#### Split
Split a `Money` object into multiple parts. It ensures the sum of parts equals the original amount by adding the remainder to the first part.

```php
$money = Money::EUR('1000'); // 10.00€
$parts = Operation::of($money)->split(3); 
// [Money::EUR('334'), Money::EUR('333'), Money::EUR('333')]
```

#### Join
Combine an array of `Money` objects back into a single `Money` object.

```php
$parts = [Money::EUR('334'), Money::EUR('333'), Money::EUR('333')];
$sum = Operation::join($parts); // 10.00€
```

#### Assert Split
Verify if an array of `Money` objects correctly totals the original instance.

```php
$parts = [Money::EUR('334'), Money::EUR('333'), Money::EUR('333')];
$isValid = Operation::of(Money::EUR(1000))->assertSplit($parts); // true
```

#### Average
Calculate the average value of a collection of `Money` objects.

```php
$parts = [Money::EUR('100'), Money::EUR('200'), Money::EUR('300'), Money::EUR('400')];
$avg = Operation::average($parts); // 2.50€
```

---

### Formatting and Parsing

#### Formatting
Format a `Money` object to a localized string. Requires the `intl` extension.

```php
$money = Money::USD('100');
$formatted = Operation::of($money)->format('en_US'); // "$1.00"

// Custom currencies implementation
$formatted = Operation::of($money)->format('en_US', new MyCustomCurrencies());
```

#### Parsing
Parse a localized currency string into a `Money` object.

```php
$money = Operation::parse('$1.00', 'en_US'); // Money::USD('100')
```

#### To Decimal
Convert a `Money` object to a float representation.

```php
$decimal = Operation::of(Money::EUR(54321))->toDecimal(); // 543.21
```

---

### Factory

Create `Money` instances easily.

```php
$money = Operation::factory(100, 'EUR'); // Money::EUR('100')
$money = Operation::factory('500', new \Money\Currency('USD')); // Money::USD('500')
```

## Rounding Modes

Most operations accept an optional `$roundingMode` parameter. By default, it uses `Money::ROUND_HALF_UP`.

Available modes (from MoneyPHP):
- `Money::ROUND_HALF_UP`
- `Money::ROUND_HALF_DOWN`
- `Money::ROUND_HALF_EVEN`
- `Money::ROUND_HALF_ODD`
- `Money::ROUND_UP`
- `Money::ROUND_DOWN`
- `Money::ROUND_CEILING`
- `Money::ROUND_FLOOR`

## Exceptions

Methods may throw `\MoneyOperation\Exceptions\InvalidOperationException` in cases like:
- Invalid number of parts for `split`.
- Indivisible amounts.
- Missing `intl` extension for formatting/parsing.
- Empty arrays for `join` or `average`.

## Author

Created by [Roger Vilà](https://rogervila.es)

## License

MoneyPHP Operations is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

Icons made by <a href="https://www.flaticon.es/autores/prosymbols-premium" title="Freepik">Prosymbols Premium</a> from <a href="https://www.flaticon.es/iconos-gratis/administracion-del-dinero" title="Flaticon">www.flaticon.es</a>
