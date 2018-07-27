# Change log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [[*next-version*]] - YYYY-MM-DD

## [0.2-alpha1] - 2018-07-27
### Changed
- Removed all previous classes.
- Introduced `BookingTransitioner` which is a transitioner of `StateAwareInterface` instances.
- `BookingTransitioner` now uses a state machine factory to create disposable state machines.

## [0.1-alpha1] - 2018-05-15
Initial version.
