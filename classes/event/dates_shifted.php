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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <https://www.gnu.org/licenses/>.

/**
 * Event: dates_shifted.
 *
 * @package    tool_courseshift
 * @copyright  2026 Tessa Demel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_courseshift\event;

defined('MOODLE_INTERNAL') || die();

class dates_shifted extends \core\event\base {

    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    public static function get_name() {
        return get_string('event_dates_shifted', 'tool_courseshift');
    }

    public function get_description() {
        $courseids = implode(',', (array)($this->other['courseids'] ?? []));
        $cms = (int)($this->other['cms'] ?? 0);
        $mode = $this->other['mode'] ?? '?';
        return "User {$this->userid} shifted dates of courses [{$courseids}] (mode={$mode}); {$cms} activity dates updated.";
    }
}
