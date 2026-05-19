# Changelog

All notable changes to `tool_courseshift` are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] — 2026-05-19

### Changed
- Promoted maturity from `MATURITY_ALPHA` to `MATURITY_STABLE`.
- Compatibility verified for Moodle 5.0–5.2 (`$plugin->supported = [500, 502]`).
- CI matrix overhauled: `MOODLE_500_STABLE/pgsql`, `MOODLE_501_STABLE/mariadb`,
  `MOODLE_502_STABLE/pgsql`, `MOODLE_502_STABLE` + PHP 8.4/mariadb.
- CI now also covers MariaDB (mariadb:10.11 service added).
- Bump version 2026050300 → 2026051902, release 0.1.0 → 1.0.0.

## [0.1.0] — 2026-05-03

### Added
- Initial alpha release.
- Multi-select course autocomplete + preview/apply workflow.
- Two shift modes: per-course anchor date OR fixed delta in days.
- Optional activity-date shifting across `assign`, `quiz`, `forum`, `lesson`,
  `choice`, `workshop`, `feedback`, `data`, `scorm`, `chat`.
- Audit event `\tool_courseshift\event\dates_shifted`.
- Navigation entry points: site admin, course-category sidebar, course sidebar.
