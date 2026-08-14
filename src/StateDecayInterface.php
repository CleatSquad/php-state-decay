<?php

declare(strict_types=1);

namespace CleatSquad\StateDecay;

/**
 * A time-based decay curve. Elapsed time is supplied by the caller, so the
 * implementation stays free of any clock and is trivially testable.
 */
interface StateDecayInterface
{
    /**
     * The multiplier for a given age, in the 0.0..1.0 range.
     */
    public function factor(int $elapsedSeconds): float;

    /**
     * Applies the decay factor to a continuous value.
     */
    public function applyToValue(float $value, int $elapsedSeconds): float;

    /**
     * Applies the decay factor to integer counters, keyed by name.
     *
     * @param array<string, int> $counters
     * @return array<string, int>
     */
    public function applyToCounters(array $counters, int $elapsedSeconds): array;
}
