<?php

declare(strict_types=1);

namespace CleatSquad\StateDecay;

/**
 * Halves accumulated state as it ages, following an exponential half-life curve.
 */
final readonly class HalfLifeDecay implements StateDecayInterface
{
    private DecayConfig $config;

    public function __construct(?DecayConfig $config = null)
    {
        $this->config = $config ?? new DecayConfig();
    }

    /**
     * 1.0 for no elapsed time, 0.5 after one half-life. Time never runs
     * backwards: clock skew will not resurrect aged state.
     */
    public function factor(int $elapsedSeconds): float
    {
        if ($elapsedSeconds <= 0 || $this->config->halfLifeSeconds <= 0.0) {
            return 1.0;
        }

        return 0.5 ** ($elapsedSeconds / $this->config->halfLifeSeconds);
    }

    /**
     * Ages a continuous value — a score, a weight, a rate. Nothing is rounded
     * and nothing is clamped: the caller owns the value's own bounds.
     */
    public function applyToValue(float $value, int $elapsedSeconds): float
    {
        return $value * $this->factor($elapsedSeconds);
    }

    /**
     * Counts after ageing, rounded rather than floored: rounding keeps a
     * frequently run pass stable, where flooring would erase evidence that has
     * barely aged. Negative counts are treated as 0 — a count cannot be owed.
     *
     * @param array<string, int> $counters
     * @return array<string, int>
     */
    public function applyToCounters(array $counters, int $elapsedSeconds): array
    {
        $factor = $this->factor($elapsedSeconds);
        $decayed = [];

        foreach ($counters as $key => $count) {
            $decayed[$key] = (int) round(max(0, $count) * $factor);
        }

        return $decayed;
    }
}
