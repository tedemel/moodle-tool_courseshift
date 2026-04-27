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
 * Unit tests for shifter.
 *
 * @package    tool_courseshift
 * @copyright  2026 Tessa Demel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_courseshift\local;

/**
 * @covers \tool_courseshift\local\shifter
 */
final class shifter_test extends \advanced_testcase {

    /**
     * test_apply_anchor_shifts_course_and_content.
     */
    public function test_apply_anchor_shifts_course_and_content(): void {
        global $DB;
        $this->resetAfterTest();

        $start = strtotime('2026-06-01 00:00');
        $end   = strtotime('2026-08-01 00:00');
        $course = $this->getDataGenerator()->create_course([
            'startdate' => $start,
            'enddate'   => $end,
        ]);

        $duedate = strtotime('2026-06-15 12:00');
        $assign = $this->getDataGenerator()->create_module('assign', [
            'course'  => $course->id,
            'duedate' => $duedate,
        ]);

        $newstart = strtotime('2026-09-01 00:00'); // +3 months delta.

        $result = shifter::apply([$course->id], 'anchor', $newstart, 0, true);

        $this->assertEquals(1, $result['courses']);
        $this->assertEquals(1, $result['cms']);

        $delta = $newstart - $start;
        $updated = $DB->get_record('course', ['id' => $course->id]);
        $this->assertEquals($newstart, (int)$updated->startdate);
        $this->assertEquals($end + $delta, (int)$updated->enddate);
        $this->assertEquals(
            $duedate + $delta,
            (int)$DB->get_field('assign', 'duedate', ['id' => $assign->id])
        );
    }

    /**
     * test_apply_delta_shifts_only_when_includecontent_true.
     */
    public function test_apply_delta_shifts_only_when_includecontent_true(): void {
        global $DB;
        $this->resetAfterTest();

        $start = strtotime('2026-06-01 00:00');
        $course = $this->getDataGenerator()->create_course(['startdate' => $start]);
        $duedate = strtotime('2026-06-15 12:00');
        $assign = $this->getDataGenerator()->create_module('assign', [
            'course'  => $course->id,
            'duedate' => $duedate,
        ]);

        // Shift by 7 days, do NOT include content.
        $result = shifter::apply([$course->id], 'delta', 0, 7, false);

        $this->assertEquals(1, $result['courses']);
        $this->assertEquals(0, $result['cms']);

        $newstart = (int)$DB->get_field('course', 'startdate', ['id' => $course->id]);
        $this->assertEquals($start + 7 * DAYSECS, $newstart);
        // Activity untouched.
        $this->assertEquals(
            $duedate,
            (int)$DB->get_field('assign', 'duedate', ['id' => $assign->id])
        );
    }

    /**
     * test_preview_does_not_modify_db.
     */
    public function test_preview_does_not_modify_db(): void {
        global $DB;
        $this->resetAfterTest();

        $start = strtotime('2026-06-01 00:00');
        $course = $this->getDataGenerator()->create_course(['startdate' => $start]);

        $rows = shifter::preview([$course->id], 'delta', 0, 7, false);

        $this->assertNotEmpty($rows);
        $this->assertEquals($start, (int)$DB->get_field('course', 'startdate', ['id' => $course->id]));
    }
}
