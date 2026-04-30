<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Date shifter for tool_courseshift.
 *
 * @package    tool_courseshift
 * @copyright  2026 Tessa Demel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_courseshift\local;

/**
 * Class shifter.
 */
class shifter {
    /**
     * Compute a preview of what the shift would do (no DB writes).
     */
    public static function preview(
        array $courseids,
        string $mode,
        int $anchordate,
        int $deltadays,
        bool $includecontent,
        ?array $percoursedates = null
    ): array {
        global $DB;
        $rows = [];
        foreach ($courseids as $courseid) {
            $courseid = (int)$courseid;
            $course = $DB->get_record('course', ['id' => $courseid]);
            if (!$course) {
                continue;
            }
            if ($mode === 'percourse' && is_array($percoursedates)) {
                $target = (int)($percoursedates[$courseid] ?? 0);
                $delta = ($target > 0 && (int)$course->startdate > 0) ? ($target - (int)$course->startdate) : 0;
            } else {
                $delta = self::compute_delta($course, $mode, $anchordate, $deltadays);
            }
            if ($delta === 0) {
                continue;
            }
            $entry = [
                'courseid'   => $courseid,
                'coursename' => format_string($course->fullname),
                'delta'      => $delta,
                'deltadays'  => round($delta / DAYSECS, 1),
                'startdate'  => self::pair($course->startdate, $delta),
                'enddate'    => self::pair($course->enddate, $delta),
                'cms'        => [],
                'events'     => [],
            ];
            if ($includecontent) {
                $modinfo = get_fast_modinfo($courseid);
                foreach ($modinfo->cms as $cm) {
                    $fields = self::get_date_fields_for_module($cm->modname, (int)$cm->instance);
                    $changes = [];
                    foreach ($fields as $f => $v) {
                        if ($v > 0) {
                            $changes[$f] = self::pair($v, $delta);
                        }
                    }
                    if (!empty($changes)) {
                        $entry['cms'][] = [
                            'name'    => format_string($cm->name),
                            'modname' => $cm->modname,
                            'changes' => $changes,
                        ];
                    }
                }
                $events = $DB->get_records_select(
                    'event',
                    'courseid = :cid AND eventtype = :etype AND timestart > 0',
                    ['cid' => $courseid, 'etype' => 'course'],
                    'timestart ASC',
                    'id, name, timestart'
                );
                foreach ($events as $ev) {
                    $entry['events'][] = [
                        'name' => format_string($ev->name),
                        'pair' => self::pair((int)$ev->timestart, $delta),
                    ];
                }
            }
            $rows[] = $entry;
        }
        return $rows;
    }

    /**
     * Apply the shift.
     *
     * @return array ['courses'=>int, 'cms'=>int]
     */
    public static function apply(
        array $courseids,
        string $mode,
        int $anchordate,
        int $deltadays,
        bool $includecontent,
        ?array $percoursedates = null
    ): array {
        global $DB, $USER;
        $coursecount = 0;
        $cmcount = 0;
        $eventcount = 0;
        $touchedcourseids = [];
        $snapshot = ['courses' => []];

        foreach ($courseids as $courseid) {
            $courseid = (int)$courseid;
            $course = $DB->get_record('course', ['id' => $courseid]);
            if (!$course) {
                continue;
            }
            if ($mode === 'percourse' && is_array($percoursedates)) {
                $target = (int)($percoursedates[$courseid] ?? 0);
                $delta = ($target > 0 && (int)$course->startdate > 0) ? ($target - (int)$course->startdate) : 0;
            } else {
                $delta = self::compute_delta($course, $mode, $anchordate, $deltadays);
            }
            if ($delta === 0) {
                continue;
            }

            // Per-course snapshot for undo.
            $entry = [
                'courseid'  => $courseid,
                'startdate' => (int)$course->startdate,
                'enddate'   => (int)$course->enddate,
                'cms'       => [],
                'events'    => [],
            ];

            $update = (object)['id' => $courseid];
            if ((int)$course->startdate > 0) {
                $update->startdate = (int)$course->startdate + $delta;
            }
            if ((int)$course->enddate > 0) {
                $update->enddate = (int)$course->enddate + $delta;
            }
            $DB->update_record('course', $update);
            $coursecount++;
            $touchedcourseids[] = $courseid;

            if ($includecontent) {
                $entry['cms'] = self::shift_activity_dates_with_snapshot($courseid, $delta);
                $cmcount += count($entry['cms']);
                $entry['events'] = self::shift_course_events_with_snapshot($courseid, $delta);
                $eventcount += count($entry['events']);
            }
            $snapshot['courses'][] = $entry;
            rebuild_course_cache($courseid, true);
        }

        $undoid = 0;
        if ($coursecount > 0 && !empty($snapshot['courses'])) {
            $undoid = \tool_courseshift\local\undo_store::record((int)$USER->id, $snapshot);
        }

        // Audit log entry — emit one event covering the whole batch.
        if ($coursecount > 0) {
            $event = \tool_courseshift\event\dates_shifted::create([
                'context' => \context_system::instance(),
                'other' => [
                    'courseids'      => $touchedcourseids,
                    'mode'           => $mode,
                    'anchordate'     => $anchordate,
                    'deltadays'      => $deltadays,
                    'includecontent' => $includecontent ? 1 : 0,
                    'courses'        => $coursecount,
                    'cms'            => $cmcount,
                    'events'         => $eventcount,
                    'undoid'         => $undoid,
                ],
            ]);
            $event->trigger();
        }

        return [
            'courses' => $coursecount,
            'cms'     => $cmcount,
            'events'  => $eventcount,
            'undoid'  => $undoid,
        ];
    }

    /**
     * compute_delta.
     */
    private static function compute_delta($course, string $mode, int $anchordate, int $deltadays): int {
        if ($mode === 'anchor') {
            if ((int)$course->startdate > 0 && $anchordate > 0) {
                return $anchordate - (int)$course->startdate;
            }
            return 0;
        }
        return (int)$deltadays * DAYSECS;
    }

    /**
     * pair.
     */
    private static function pair(int $value, int $delta): array {
        if ($value <= 0) {
            return ['from' => 0, 'to' => 0, 'fromfmt' => '—', 'tofmt' => '—'];
        }
        $newvalue = $value + $delta;
        return [
            'from'    => $value,
            'to'      => $newvalue,
            'fromfmt' => userdate($value, '%Y-%m-%d %H:%M'),
            'tofmt'   => userdate($newvalue, '%Y-%m-%d %H:%M'),
        ];
    }

    /**
     * shift_activity_dates.
     */
    private static function shift_activity_dates(int $courseid, int $delta): int {
        return count(self::shift_activity_dates_with_snapshot($courseid, $delta));
    }

    /**
     * Shift activity dates and return per-cm snapshot of pre-change values.
     *
     * @return array list of ['modname', 'instance', 'fields' => [name=>oldvalue]]
     */
    private static function shift_activity_dates_with_snapshot(int $courseid, int $delta): array {
        global $DB;
        $modinfo = get_fast_modinfo($courseid);
        $records = [];
        foreach ($modinfo->cms as $cmid => $cm) {
            $fields = self::get_date_fields_for_module($cm->modname, (int)$cm->instance);
            if (empty($fields)) {
                continue;
            }
            $update = (object)['id' => (int)$cm->instance];
            $oldvalues = [];
            $hasupdate = false;
            foreach ($fields as $field => $value) {
                if ($value > 0) {
                    $oldvalues[$field] = (int)$value;
                    $update->$field = $value + $delta;
                    $hasupdate = true;
                }
            }
            if ($hasupdate) {
                $update->timemodified = time();
                $DB->update_record($cm->modname, $update);
                $records[] = [
                    'modname'  => $cm->modname,
                    'instance' => (int)$cm->instance,
                    'fields'   => $oldvalues,
                ];
            }
        }
        return $records;
    }

    /**
     * Shift "course"-typed calendar events for one course; return pre-change snapshot.
     *
     * @return array list of ['eventid', 'oldtimestart']
     */
    private static function shift_course_events_with_snapshot(int $courseid, int $delta): array {
        global $DB;
        $events = $DB->get_records_select(
            'event',
            'courseid = :cid AND eventtype = :etype AND timestart > 0',
            ['cid' => $courseid, 'etype' => 'course']
        );
        $records = [];
        foreach ($events as $e) {
            $records[] = ['eventid' => (int)$e->id, 'oldtimestart' => (int)$e->timestart];
            $DB->set_field('event', 'timestart', (int)$e->timestart + $delta, ['id' => $e->id]);
            $DB->set_field('event', 'timemodified', time(), ['id' => $e->id]);
        }
        return $records;
    }

    /**
     * get_date_fields_for_module.
     */
    private static function get_date_fields_for_module(string $modname, int $instanceid): array {
        global $DB;
        $known = [
            'assign'   => ['allowsubmissionsfromdate', 'duedate', 'cutoffdate', 'gradingduedate'],
            'quiz'     => ['timeopen', 'timeclose'],
            'forum'    => ['duedate', 'cutoffdate'],
            'lesson'   => ['available', 'deadline'],
            'choice'   => ['timeopen', 'timeclose'],
            'workshop' => ['submissionstart', 'submissionend', 'assessmentstart', 'assessmentend'],
            'feedback' => ['timeopen', 'timeclose'],
            'data'     => [
                'timeavailablefrom', 'timeavailableto', 'timeviewfrom',
                'timeviewto', 'assesstimestart', 'assesstimefinish',
            ],
            'scorm'    => ['timeopen', 'timeclose'],
            'chat'     => ['chattime'],
        ];
        $fields = $known[$modname] ?? [];
        if (empty($fields)) {
            return [];
        }
        $select = 'id, ' . implode(', ', $fields);
        $record = $DB->get_record($modname, ['id' => $instanceid], $select, IGNORE_MISSING);
        if (!$record) {
            return [];
        }
        $out = [];
        foreach ($fields as $f) {
            $out[$f] = (int)($record->$f ?? 0);
        }
        return $out;
    }
}
