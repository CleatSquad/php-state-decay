<?php

declare(strict_types=1);

namespace CleatSquad\StateDecay\Tests;

use CleatSquad\StateDecay\DecayConfig;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class DecayConfigTest extends TestCase
{
    public function testDaysAreStoredAsSeconds(): void
    {
        self::assertSame(90.0 * 86400, (new DecayConfig(90))->halfLifeSeconds);
    }

    public function testTheDefaultIsNinetyDays(): void
    {
        self::assertSame(90, DecayConfig::DEFAULT_HALF_LIFE_DAYS);
        self::assertSame(90.0, (new DecayConfig())->halfLifeDays());
    }

    public function testASubDayHalfLifeIsExpressible(): void
    {
        $config = DecayConfig::fromSeconds(1800);

        self::assertSame(1800.0, $config->halfLifeSeconds);
        self::assertSame(1800.0 / 86400, $config->halfLifeDays());
    }

    public function testFromDaysMatchesTheConstructor(): void
    {
        self::assertSame(
            (new DecayConfig(7))->halfLifeSeconds,
            DecayConfig::fromDays(7)->halfLifeSeconds
        );
    }

    public function testNeverDisablesDecay(): void
    {
        self::assertSame(0.0, DecayConfig::never()->halfLifeSeconds);
    }

    public function testFractionalDaysSurviveTheRoundTrip(): void
    {
        self::assertSame(0.5, (new DecayConfig(0.5))->halfLifeDays());
    }

    public function testANegativeHalfLifeIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new DecayConfig(-1);
    }

    public function testNegativeSecondsAreRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        DecayConfig::fromSeconds(-1);
    }

    public function testNotANumberIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new DecayConfig(NAN);
    }

    public function testInfinityIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new DecayConfig(INF);
    }
}
