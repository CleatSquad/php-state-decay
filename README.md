# PHP State Decay

[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.2-777bb4.svg)](composer.json)

Exponential half-life decay for counters and scores, in dependency-free PHP.

Evidence gathered a year ago should not weigh as much as evidence gathered
yesterday. This library computes that discount: after one half-life a value is
worth half of what it was, after two it is worth a quarter, and so on.

## Installation

```bash
composer require cleatsquad/php-state-decay
```

Requires PHP 8.2 or later. No runtime dependencies.

## Usage

### Decay a score

```php
use CleatSquad\StateDecay\DecayConfig;
use CleatSquad\StateDecay\HalfLifeDecay;

$decay = new HalfLifeDecay(new DecayConfig(halfLifeDays: 30));

$decay->applyToValue(100.0, 30 * 86400); // 50.0
$decay->applyToValue(100.0, 60 * 86400); // 25.0
```

### Decay integer counters

```php
$counters = ['successes' => 40, 'failures' => 10];

$decay->applyToCounters($counters, 30 * 86400);
// ['successes' => 20, 'failures' => 5]
```

Counters are rounded, not floored. A pass that runs every hour would otherwise
floor barely-aged evidence down to nothing, one unit at a time.

### Read the multiplier directly

```php
$decay->factor(0);             // 1.0
$decay->factor(30 * 86400);    // 0.5
$decay->factor(-3600);         // 1.0 — clock skew never resurrects state
```

### Short half-lives

Days are the usual unit, but anything shorter is expressible:

```php
DecayConfig::fromSeconds(1800);  // 30 minutes
DecayConfig::fromDays(0.25);     // 6 hours
DecayConfig::never();            // decay disabled; factor() stays 1.0
```

## Design notes

**Elapsed time is the caller's business.** Nothing here reads a clock. You pass
the number of seconds that went by, which keeps the library free of any time
dependency and makes every test deterministic. If you use a PSR-20 clock, take
the difference yourself and hand it over.

**Time never runs backwards.** A negative or zero elapsed time yields a factor
of `1.0`. Clock skew between machines cannot make aged state young again.

**Counters cannot be owed.** A negative count is treated as `0` rather than
propagated.

**Invalid configuration fails loudly.** A negative, `NAN` or `INF` half-life
throws `InvalidArgumentException` instead of silently poisoning every later
computation.

## When to use it

Good fits: recency ranking, reputation and trust scores, abuse and rate
heuristics, health scores, recommendation weights — anywhere accumulated
evidence should fade rather than be dropped by a cutoff.

Poor fits: you need a hard expiry (use a TTL), or a fixed-size sliding window
(count within the window instead).

## Testing

```bash
composer install
composer test      # PHPUnit
composer analyse   # PHPStan, max level
```

## License

MIT. See [LICENSE](LICENSE).
