# Changelog

All notable changes to `cleatsquad/php-state-decay` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-08-14

Initial release.

### Added

- `HalfLifeDecay`, an exponential half-life decay engine with no runtime
  dependencies.
- `StateDecayInterface`, the contract implemented by decay curves.
- `DecayConfig`, holding the half-life in seconds, with `fromSeconds()`,
  `fromDays()` and `never()` named constructors so that sub-day half-lives are
  expressible.
- `factor()`, returning the `0.0..1.0` multiplier for a given age.
- `applyToValue()`, ageing a continuous value without rounding or clamping.
- `applyToCounters()`, ageing integer counters with round-half-up, which keeps a
  frequently run pass stable where flooring would erase barely-aged evidence.

### Security

- Clock skew cannot resurrect aged state: a negative or zero elapsed time yields
  a factor of `1.0`.
- `NAN`, `INF` and negative half-lives are rejected with
  `InvalidArgumentException` rather than silently producing a meaningless curve.

[1.0.0]: https://github.com/CleatSquad/php-state-decay/releases/tag/v1.0.0
