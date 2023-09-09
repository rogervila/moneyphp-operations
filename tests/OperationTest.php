<?php

namespace Tests\MoneyOperation;

use Money\Currencies\ISOCurrencies;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Money;
use Money\Parser\DecimalMoneyParser;
use MoneyOperation\Exceptions\InvalidOperationException;
use MoneyOperation\Operation;
use PHPUnit\Framework\TestCase;

/** @psalm-suppress UnusedClass */
class OperationTest extends TestCase
{
    public function test_constructors(): void
    {
        $amount = random_int(100, 1000);

        $this->assertInstanceOf(Operation::class, $operationA = Operation::of(Money::EUR($amount)));
        $this->assertInstanceOf(Operation::class, $operationB = Operation::ofValues($amount, 'EUR'));

        $this->assertTrue($operationA->percentageIncrease((string) $amount)->equals($operationB->percentageIncrease((string) $amount)));
    }

    /**
     * @dataProvider percentageIncreaseProvider
     *
     * @psalm-param numeric-string $percentage
     */
    public function test_percentage_increase(Money $originalMoney, string $percentage, Money $expectedMoney): void
    {
        $resultMoney = Operation::of($originalMoney)->percentageIncrease($percentage);

        $this->assertTrue(
            $resultMoney->equals($expectedMoney),
            sprintf(
                '"%s" does not match expected "%s"',
                $resultMoney->getAmount(),
                $expectedMoney->getAmount(),
            )
        );
    }

    /**
     * @dataProvider percentageDecreaseProvider
     *
     * @psalm-param numeric-string $percentage
     */
    public function test_percentage_decrease(Money $originalMoney, string $percentage, Money $expectedMoney): void
    {
        $resultMoney = Operation::of($originalMoney)->percentageDecrease($percentage);

        $this->assertTrue(
            $resultMoney->equals($expectedMoney),
            sprintf(
                '"%s" does not match expected "%s"',
                $resultMoney->getAmount(),
                $expectedMoney->getAmount(),
            )
        );
    }

    /**
     * @dataProvider percentageDifferenceProvider
     */
    public function test_percentage_difference(
        Money $originalMoney,
        Money $comparedMoney,
        string $expectedPercentage,
        int $decimals = 2
    ): void {
        $resultPercentage = number_format(
            Operation::of($originalMoney)->percentageDifference($comparedMoney),
            $decimals
        );

        $this->assertEquals(
            $expectedPercentage,
            $resultPercentage,
            sprintf(
                'percentage difference between "%s" and "%s" does not match expected "%s"',
                $originalMoney->getAmount(),
                $comparedMoney->getAmount(),
                $resultPercentage
            )
        );
    }

    /**
     * @dataProvider splitProvider
     *
     * @param Money[] $expectedParts
     */
    public function test_split(Money $originalMoney, array $expectedParts): void
    {
        /** @psalm-var int<1,max> $times */
        $times = count($expectedParts);

        $this->assertCount($times, $result = Operation::of($originalMoney)->split($times));

        foreach ($result as $key => $part) {
            $this->assertTrue(
                $part->equals($expectedParts[$key]),
                sprintf(
                    'Money with value "%s" split %d times has value "%s" instead of expected "%s" for key %d',
                    $originalMoney->getAmount(),
                    $times,
                    $part->getAmount(),
                    $expectedParts[$key]->getAmount(),
                    $key
                )
            );
        }
    }

    public function test_split_exception_wrong_times(): void
    {
        $this->expectException(InvalidOperationException::class);

        /** @phpstan-ignore-next-line */
        Operation::of(Money::EUR('123'))->split(random_int(-1, 0));
    }

    /**
     * @dataProvider splitProvider
     *
     * @param Money[] $parts
     */
    public function test_join(Money $originalMoney, array $parts): void
    {
        $this->assertTrue(
            $originalMoney->equals($currentMoney = Operation::join($parts)),
            sprintf(
                'Amount "%s" does not match expected "%s"',
                $currentMoney->getAmount(),
                $originalMoney->getAmount(),
            )
        );
    }

    public function test_join_exception_empty_parts(): void
    {
        $this->expectException(InvalidOperationException::class);

        Operation::join([]);
    }

    public function test_split_exception_indivisible(): void
    {
        $this->expectException(InvalidOperationException::class);
        $this->test_split(
            Money::EUR('288'),
            [Money::EUR('58'),Money::EUR('58'),Money::EUR('58'),Money::EUR('58'),Money::EUR('58')]
        );
    }

    /**
     * @dataProvider splitProvider
     *
     * @param Money[] $expectedParts
     */
    public function test_assert_split(Money $originalMoney, array $expectedParts): void
    {
        $this->assertTrue(Operation::of($originalMoney)->assertSplit($expectedParts));
    }

    /**
     * @dataProvider averageProvider
     *
     * @param Money[] $parts
     */
    public function test_assert_average(array $parts, Money $expectedMoney): void
    {
        $this->assertTrue(
            $expectedMoney->equals($currentMoney = Operation::average($parts)),
            sprintf(
                'Amount "%s" does not match expected "%s"',
                $currentMoney->getAmount(),
                $expectedMoney->getAmount(),
            )
        );
    }

    /**
     * @dataProvider formatterParserProvider
     */
    public function test_assert_intl_format_and_parse(Money $expectedMoney, string $expectedFormat, string $locale): void
    {
        $this->assertEquals(
            $expectedFormat,
            $format = Operation::of($expectedMoney)->format($locale),
            sprintf(
                'Format "%s" does not match expected "%s"',
                $format,
                $expectedFormat,
            )
        );

        $this->assertTrue(
            $expectedMoney->equals($parsed = Operation::parse($expectedFormat, $locale)),
            sprintf(
                'Parsed "%s" does not match expected "%s"',
                $parsed->getAmount(),
                $expectedMoney->getAmount(),
            )
        );
    }

    /**
     * @dataProvider toDecimalMethodProvider
     */
    public function test_to_integer_method(Money $money, float $result): void
    {
        $this->assertTrue(Operation::of($money)->toDecimal() === $result);
    }

    public function test_average_exception_empty_parts(): void
    {
        $this->expectException(InvalidOperationException::class);

        Operation::average([]);
    }

    /**
     * @psalm-return array<array{Money,numeric-string,Money}>
     */
    public static function percentageIncreaseProvider(): array
    {
        return [
            [Money::EUR('100'), '20', Money::EUR('120')],
            [Money::EUR('100'), '1.99', Money::EUR('102')],
        ];
    }

    /**
     * @psalm-return array<array{Money,numeric-string,Money}>
     */
    public static function percentageDecreaseProvider(): array
    {
        return [
            [Money::EUR('120'), '20', Money::EUR('96')],
            [Money::EUR('120'), '-20', Money::EUR('96')],
            [Money::EUR('288'), '2.99', Money::EUR('279')],
            [Money::EUR('288'), '-2.99', Money::EUR('279')],
        ];
    }

    /**
     * @psalm-return array<array{Money,Money,numeric-string}>
     */
    public static function percentageDifferenceProvider(): array
    {
        return [
            [Money::EUR('100'), Money::EUR('120'), '20.00'],
            [Money::EUR('101'), Money::EUR('120'), '18.81'],
            [Money::EUR('288'), Money::EUR('42'), '-85.42'],
        ];
    }

    /**
     * @psalm-return array<array{Money, Money[]}>
     */
    public static function splitProvider(): array
    {
        return [
            [Money::EUR('100'), [Money::EUR('25'),Money::EUR('25'),Money::EUR('25'),Money::EUR('25')]],
            [Money::EUR('999'), [Money::EUR('333'),Money::EUR('333'),Money::EUR('333')]],
            [Money::EUR('1000'), [Money::EUR('334'),Money::EUR('333'),Money::EUR('333')]],
            [Money::EUR('290'), [Money::EUR('58'),Money::EUR('58'),Money::EUR('58'),Money::EUR('58'),Money::EUR('58')]],
            [Money::EUR('1234'), [Money::EUR('1234')]],
        ];
    }

    /**
     * @psalm-return array<array{Money[], Money}>
     */
    public static function averageProvider(): array
    {
        return [
            [
                [Money::EUR('100'), Money::EUR('200'), Money::EUR('300'), Money::EUR('400')], Money::EUR('250'),
                [Money::EUR('288'), Money::EUR('422'), Money::EUR('1714')], Money::EUR('808'),
            ],
        ];
    }

    /**
     * @psalm-return array<array{Money,string}>
     */
    public static function formatterParserProvider(): array
    {
        return [
            [Money::USD('100'), '$1.00', 'en_US'],
            [Money::EUR('288'), '2,88 €', 'es_ES'],
        ];
    }

    /**
     * @psalm-return array<array{Money,float}>
     */
    public static function toDecimalMethodProvider(): array
    {
        return [
            [Money::USD(1234), 12.34],
            [Money::USD(4321), 43.21],
            [Money::USD(12345), 123.45],
            [Money::USD(54321), 543.21],
            [Money::USD(999999), 9999.99],
        ];
    }
}
