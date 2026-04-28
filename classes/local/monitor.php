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
 * Monitor helper: lists pending scheduled shifts and recent history.
 *
 * @package    tool_courseshift
 * @copyright  2026 Tessa Demel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_courseshift\local;

/**
 * Class monitor.
 */
class monitor {
    /**
     * Pending scheduled_shift adhoc tasks.
     *
     * @return \stdClass[] list of {id, nextruntime, userid, customdata}
     */
    public static function pending(): array {
        global $DB;
        $records = $DB->get_records('task_adhoc', [
            'classname' => '\\tool_courseshift\\task\\scheduled_shift',
            'component' => 'tool_courseshift',
        ], 'nextruntime ASC');
        return array_values($records);
    }

    /**
     * Cancel (delete) a pending scheduled_shift task by id.
     */
    public static function cancel(int $taskid): bool {
        global $DB;
        $rec = $DB->get_record('task_adhoc', [
            'id' => $taskid,
            'classname' => '\\tool_courseshift\\task\\scheduled_shift',
            'component' => 'tool_courseshift',
        ]);
        if (!$rec) {
            return false;
        }
        // Don't kill a task that's actively running.
        if (!empty($rec->timestarted)) {
            return false;
        }
        $DB->delete_records('task_adhoc', ['id' => $taskid]);
        return true;
    }

    /**
     * Last N dates_shifted events from logstore_standard_log.
     *
     * @param int $limit
     * @return \stdClass[] list of log records {timecreated, userid, other (json)}
     */
    public static function recent(int $limit = 10): array {
        global $DB;
        if (!$DB->get_manager()->table_exists('logstore_standard_log')) {
            return [];
        }
        $records = $DB->get_records(
            'logstore_standard_log',
            [
                'component' => 'tool_courseshift',
                'eventname' => '\\tool_courseshift\\event\\dates_shifted',
            ],
            'timecreated DESC',
            'id, timecreated, userid, other',
            0,
            $limit
        );
        return array_values($records);
    }
}
