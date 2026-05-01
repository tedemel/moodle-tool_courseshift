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
 * Restore tool_courseshift snapshots.
 *
 * @package    tool_courseshift
 * @copyright  2026 Tessa Demel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_courseshift\local;

/**
 * Class undo_runner.
 */
class undo_runner {
    /**
     * Restore the snapshot identified by $undoid.
     *
     * @param int $undoid
     * @return array
     */
    public static function restore(int $undoid): array {
        global $DB, $USER;

        $rec = undo_store::get($undoid);
        if (!$rec) {
            throw new \moodle_exception('error_undo_missing', 'tool_courseshift');
        }
        if ((int)$rec->userid !== (int)$USER->id && !is_siteadmin()) {
            throw new \moodle_exception('error_undo_permission', 'tool_courseshift');
        }
        if ((time() - (int)$rec->timecreated) > undo_store::TTL_SECONDS) {
            undo_store::delete($undoid);
            throw new \moodle_exception('error_undo_expired', 'tool_courseshift');
        }

        $snapshot = json_decode($rec->snapshot, true) ?: [];
        $courses = 0;
        $cms = 0;
        $events = 0;

        foreach ($snapshot['courses'] ?? [] as $entry) {
            $courseid = (int)$entry['courseid'];
            $update = (object)['id' => $courseid];
            if ((int)$entry['startdate'] > 0) {
                $update->startdate = (int)$entry['startdate'];
            }
            if ((int)$entry['enddate'] > 0) {
                $update->enddate = (int)$entry['enddate'];
            }
            $DB->update_record('course', $update);
            $courses++;

            foreach ($entry['cms'] ?? [] as $row) {
                try {
                    $modname = $row['modname'];
                    $upd = (object)['id' => (int)$row['instance']];
                    foreach ($row['fields'] as $f => $v) {
                        $upd->$f = (int)$v;
                    }
                    $upd->timemodified = time();
                    $DB->update_record($modname, $upd);
                    $cms++;
                } catch (\Throwable $e) {
                    debugging('undo cm restore: ' . $e->getMessage(), DEBUG_DEVELOPER);
                }
            }

            foreach ($entry['events'] ?? [] as $row) {
                try {
                    $DB->set_field('event', 'timestart', (int)$row['oldtimestart'], ['id' => (int)$row['eventid']]);
                    $DB->set_field('event', 'timemodified', time(), ['id' => (int)$row['eventid']]);
                    $events++;
                } catch (\Throwable $e) {
                    debugging('undo event restore: ' . $e->getMessage(), DEBUG_DEVELOPER);
                }
            }

            rebuild_course_cache($courseid, true);
        }

        undo_store::delete($undoid);
        return ['courses' => $courses, 'cms' => $cms, 'events' => $events];
    }
}
