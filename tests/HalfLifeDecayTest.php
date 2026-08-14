<?php

declare(strict_types=1);

namespace CleatSquad\StateDecay\Tests;

use CleatSquad\StateDecay\DecayConfig;
use CleatSquad\StateDecay\HalfLifeDecay;
use PHPUnit\Framework\TestCase;

final class HalfLifeDecayTest extends TestCase
{
    private const DAY = 86400;

    public function testOneHalfLifeHalvesTheFactor(): void
    {
        self::assertSame(0.5, (new HalfLifeDecay(new DecayConfig(90)))->factor(90 * self::DAY));
    }

    public function testEachHalfLifeHalvesTheFactorAgain(): void
    {
        $decay = new HalfLifeDecay(new DecayConfig(90));

        self::assertSame(1.0, $decay->factor(0));
        self::assertSame(0.25, $decay->factor(180 * self::DAY));
        self::assertSame(0.125, $decay->factor(270 * self::DAY));
    }

    public function testClockSkewNeverResurrectsState(): void
    {
        self::assertSame(1.0, (new HalfLifeDecay(new DecayConfig(90)))->factor(-10 * self::DAY));
    }

    public function testAZeroHalfLifeDisablesDecay(): void
    {
        $decay = new HalfLifeDecay(DecayConfig::never());

        self::assertSame(1.0, $decay->factor(3650 * self::DAY));
        self::assertSame(40, $decay->applyToCounters(['hits' => 40], 3650 * self::DAY)['hits']);
    }

    public function testTheFactorStaysWithinItsBounds(): void
    {
        $decay = new HalfLifeDecay(new DecayConfig(90));

        foreach ([0, 1, 3600, 90 * self::DAY, 36500 * self::DAY, PHP_INT_MAX] as $elapsed) {
            $factor = $decay->factor($elapsed);
            self::assertGreaterThanOrEqual(0.0, $factor);
            self::assertLessThanOrEqual(1.0, $factor);
        }
    }

    public function testASubDayHalfLifeDecaysWithinTheHour(): void
    {
        $decay = new HalfLifeDecay(DecayConfig::fromSeconds(1800));

        self::assertSame(0.5, $decay->factor(1800));
        self::assertSame(0.25, $decay->factor(3600));
    }

    public function testAContinuousValueIsAgedWithoutRounding(): void
    {
        $decay = new HalfLifeDecay(new DecayConfig(90));

        self::assertSame(0.5, $decay->applyToValue(1.0, 90 * self::DAY));
        self::assertSame(2.5, $decay->applyToValue(10.0, 180 * self::DAY));
    }

    public function testANegativeValueKeepsItsSign(): void
    {
        self::assertSame(-0.5, (new HalfLifeDecay(new DecayConfig(90)))->applyToValue(-1.0, 90 * self::DAY));
    }

    public function testOneHalfLifeHalvesTheEvidence(): void
    {
        $decay = new HalfLifeDecay(new DecayConfig(90));
        $aged = $decay->applyToCounters(['successes' => 40, 'failures' => 10], 90 * self::DAY);

        self::assertSame(20, $aged['successes']);
        self::assertSame(5, $aged['failures']);
    }

    public function testTwoHalfLivesQuarterIt(): void
    {
        $decay = new HalfLifeDecay(new DecayConfig(90));
        $aged = $decay->applyToCounters(['successes' => 40, 'failures' => 8], 180 * self::DAY);

        self::assertSame(10, $aged['successes']);
        self::assertSame(2, $aged['failures']);
    }

    public function testNoElapsedTimeChangesNothing(): void
    {
        $decay = new HalfLifeDecay(new DecayConfig(90));
        $aged = $decay->applyToCounters(['successes' => 7, 'failures' => 3], 0);

        self::assertSame(7, $aged['successes']);
        self::assertSame(3, $aged['failures']);
    }

    public function testNegativeElapsedTimeChangesNothing(): void
    {
        $decay = new HalfLifeDecay(new DecayConfig(90));
        $aged = $decay->applyToCounters(['successes' => 7, 'failures' => 3], -10 * self::DAY);

        self::assertSame(7, $aged['successes']);
        self::assertSame(3, $aged['failures']);
    }

    public function testNegativeCountersAreTreatedAsZero(): void
    {
        $decay = new HalfLifeDecay(new DecayConfig(90));

        self::assertSame(0, $decay->applyToCounters(['broken' => -5], 0)['broken']);
    }

    public function testAnEmptySetOfCountersStaysEmpty(): void
    {
        self::assertSame([], (new HalfLifeDecay())->applyToCounters([], 90 * self::DAY));
    }

    public function testCounterKeysArePreserved(): void
    {
        $aged = (new HalfLifeDecay())->applyToCounters(['a' => 1, 'b' => 2], 0);

        self::assertSame(['a', 'b'], array_keys($aged));
    }

    public function testAFrequentPassIsStable(): void
    {
        $decay = new HalfLifeDecay(new DecayConfig(90));
        $useCount = 40;

        for ($hour = 0; $hour < 24; $hour++) {
            $counters = $decay->applyToCounters(['successes' => $useCount], 3600);
            $useCount = $counters['successes'];
        }

        self::assertSame(40, $useCount);
    }

    public function testEvidenceEventuallyReachesZero(): void
    {
        $decay = new HalfLifeDecay(new DecayConfig(90));
        $aged = $decay->applyToCounters(['successes' => 40, 'failures' => 40], 3650 * self::DAY);

        self::assertSame(0, $aged['successes']);
        self::assertSame(0, $aged['failures']);
    }

    public function testTheDefaultConfigurationIsUsedWhenNoneIsGiven(): void
    {
        self::assertSame(0.5, (new HalfLifeDecay())->factor(90 * self::DAY));
    }
}
