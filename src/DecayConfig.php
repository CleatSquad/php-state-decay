<?php

declare(strict_types=1);

namespace CleatSquad\StateDecay;

use InvalidArgumentException;

/**
 * Half-life setting for a decay curve, held in seconds so that sub-day
 * half-lives — a 30-minute abuse counter, a 6-hour health score — are expressible.
 */
final readonly class DecayConfig
{
    public const DEFAULT_HALF_LIFE_DAYS = 90;

    private const SECONDS_PER_DAY = 86400;

    /** Half-life in seconds; 0.0 disables decay. */
    public float $halfLifeSeconds;

    /**
     * Days are the common unit, so they stay the constructor's argument.
     * Use fromSeconds() for anything shorter than a day.
     */
    public function __construct(float $halfLifeDays = self::DEFAULT_HALF_LIFE_DAYS)
    {
        self::assertUsable($halfLifeDays, 'Half life days');

        $this->halfLifeSeconds = $halfLifeDays * self::SECONDS_PER_DAY;
    }

    public static function fromSeconds(float $halfLifeSeconds): self
    {
        self::assertUsable($halfLifeSeconds, 'Half life seconds');

        return new self($halfLifeSeconds / self::SECONDS_PER_DAY);
    }

    public static function fromDays(float $halfLifeDays): self
    {
        return new self($halfLifeDays);
    }

    /** Never decays: factor() stays at 1.0 whatever the elapsed time. */
    public static function never(): self
    {
        return new self(0.0);
    }

    public function halfLifeDays(): float
    {
        return $this->halfLifeSeconds / self::SECONDS_PER_DAY;
    }

    /**
     * NAN and INF are rejected rather than clamped: both would silently poison
     * every later factor() with a meaningless curve.
     */
    private static function assertUsable(float $value, string $label): void
    {
        if (is_nan($value) || is_infinite($value)) {
            throw new InvalidArgumentException($label . ' must be a finite number.');
        }

        if ($value < 0.0) {
            throw new InvalidArgumentException($label . ' cannot be negative.');
        }
    }
}
