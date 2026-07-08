# Changelog

All notable changes to `tool_courseshift` are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.1] — 2026-07-08

### Fixed
- Security: the apply/schedule/preview POST handlers accepted arbitrary course
  ids; every course is now checked against `moodle/course:update` for the
  acting user before being previewed or shifted.
- Scheduled (adhoc) shifts now run as the scheduling user (`set_userid`), so
  the capability check, audit event and undo snapshot are attributed correctly
  and the monitor panel no longer shows an unknown user.
- Replaced hard-coded German table headings on the per-course date form with
  language strings (new strings `percourse_th_course/current/new`).

### Removed
- Dead private helper `shifter::shift_activity_dates()`.

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
