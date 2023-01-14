<?php

namespace MoneyOperation;

use Money\Currencies\ISOCurrencies;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Money;
use MoneyOperation\Exceptions\InvalidOperationException;


class Operation
{
    public function __construct(
        protected Money $money,
    ) {
    }

    public static function of(Money $money): self
    {
        return new self($money);
    }

    /**
     * @psalm-param numeric-string $percentage
     * @psalm-param int<1,8> $roundingMode
     */
    public function percentageIncrease(string $percentage, int $roundingMode = Money::ROUND_HALF_UP): Money
    {
        return $this->money->add($this->money->multiply($percentage, $roundingMode)->divide(100, $roundingMode));
    }

    /**
     * Supports negative numeric $percentage values
     *
     * @psalm-param numeric-string $percentage
     * @psalm-param int<1,8> $roundingMode
     */
    public function percentageDecrease(string $percentage, int $roundingMode = Money::ROUND_HALF_UP): Money
    {
        $percentage = ltrim($percentage, '-');
        /** @psalm-var numeric-string $percentage */
        return $this->money->subtract($this->money->multiply($percentage, $roundingMode)->divide(100, $roundingMode));
    }

    public function percentageDifference(Money $money): float {
        $a = floatval($this->money->getAmount());
        $b = floatval($money->getAmount());

        return ($b - $a) / $a * 100.0;
    }

    /**
     * @psalm-param int<1,max> $times
     * @psalm-param int<1,8> $roundingMode
     * @psalm-param int<1,max> $tries
     * @return Money[]
     * @throws InvalidOperationException
     */
    public function split(int $times, int $roundingMode = Money::ROUND_HALF_UP, int $tries = 10): array
    {
        /** @phpstan-ignore-next-line */
        if ($times < 1) {
            /** @psalm-suppress NoValue */
            throw new InvalidOperationException(sprintf('$times must be >= 1, %d given', $times));
        }

        /** @psalm-var Money[] $parts */
        $parts = array_fill(0, $times, $part = $this->money->divide($times, $roundingMode));

        while (!$this->assertSplit($parts)) {
            if ($tries === 0) {
                 throw new InvalidOperationException(
                     sprintf('Could not split %s value to %d parts', $this->money->getAmount(), $times)
                 );
            }

            $operationMoney = new Money('1', $part->getCurrency());

            $parts[0] = $this->join($parts)->lessThan($this->money)
                ? $part->add($operationMoney)
                : $part->subtract($operationMoney);

            $tries--;
        }

        return $parts;
    }

    /**
     * @param Money[] $parts
     * @throws InvalidOperationException
     */
    public function join(array $parts): Money
    {
        if (empty($parts)) {
            throw new InvalidOperationException('$parts array cannot be empty');
        }

        $money = $parts[0];

        foreach ($parts as $key => $part) {
            if ($key === 0) {
                continue;
            }

            $money = $money->add($part);
        }

        return $money;
    }

    /**
     * @param Money[] $parts
     * @throws InvalidOperationException
     */
    public function assertSplit(array $parts): bool
    {
        return $this->join($parts)->equals($this->money);
    }
}
