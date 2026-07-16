# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.2] - 2026-07-16

### Added

- Human-in-the-loop workflow review steps: `reviewWorkflowStep()` on the workflows API, the `review` step type, and the `waiting_review` run status. Approving resumes the run, rejecting cancels it.
- Custom credit top-up: the checkout endpoint accepts `amountEur` (€10–€2500) in payment mode.

## [1.0.1] - 2026-07-14

### Changed

- Documentation and package metadata improvements; no functional changes.

## [1.0.0] - 2026-07-14

### Added

- Initial public release.
