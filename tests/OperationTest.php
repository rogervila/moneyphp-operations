<?php

namespace Tests\MoneyOperation;

use Money\Money;
use MoneyOperation\Exceptions\InvalidOperationException;
use MoneyOperation\Operation;
use PHPUnit\Framework\TestCase;

class OperationTest extends TestCase
{
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
     * @psalm-return array<array{Money,numeric-string,Money}>
     */
    protected function percentageIncreaseProvider(): array
    {
        return [
            [Money::EUR('100'), '20', Money::EUR('120')],
            [Money::EUR('100'), '1.99', Money::EUR('102')],
        ];
    }

    /**
     * @psalm-return array<array{Money,numeric-string,Money}>
     */
    protected function percentageDecreaseProvider(): array
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
    protected function percentageDifferenceProvider(): array
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
    protected function splitProvider(): array
    {
        return [
            [Money::EUR('100'), [Money::EUR('25'),Money::EUR('25'),Money::EUR('25'),Money::EUR('25')]],
            [Money::EUR('999'), [Money::EUR('333'),Money::EUR('333'),Money::EUR('333')]],
            [Money::EUR('1000'), [Money::EUR('334'),Money::EUR('333'),Money::EUR('333')]],
            [Money::EUR('290'), [Money::EUR('58'),Money::EUR('58'),Money::EUR('58'),Money::EUR('58'),Money::EUR('58')]],
            [Money::EUR('1234'), [Money::EUR('1234')]],
        ];
    }
}
