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
 * English language strings for tool_courseshift.
 *
 * @package    tool_courseshift
 * @copyright  2026 Tessa Demel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Shift course dates';
$string['pagetitle'] = 'Shift dates across multiple courses';
$string['intro'] = 'Shifts course start, course end, and all date-bearing activities of selected courses by the same amount.';
$string['courses'] = 'Courses';
$string['courses_help'] = 'Pick the courses whose dates should be shifted.';
$string['mode'] = 'Mode';
$string['mode_anchor'] = 'Set to new start date (per course)';
$string['mode_delta'] = 'Shift by fixed amount';
$string['anchordate'] = 'New course start date';
$string['anchordate_help'] = 'Each course is shifted so its start date lands on the chosen date. Content is shifted by the same delta.';
$string['deltadays'] = 'Shift by (days)';
$string['deltadays_help'] = 'Positive value = forward, negative = backward.';
$string['includecontent'] = 'Also shift activity dates';
$string['submit'] = 'Show preview';
$string['confirm'] = 'Apply';
$string['confirm_back'] = 'Back to form';
$string['back_management'] = 'Back to course and category management';
$string['result'] = 'Successfully shifted: {$a->courses} courses, {$a->cms} activity dates.';
$string['nothingtoshift'] = 'Nothing to shift.';
$string['preview_heading'] = 'Preview';
$string['preview_intro'] = 'The following changes will be applied on confirmation:';
$string['preview_th_element'] = 'Element';
$string['preview_th_before'] = 'Before';
$string['preview_th_after'] = 'After';
$string['preview_coursestart'] = 'Course start';
$string['preview_courseend'] = 'Course end';
$string['preview_deltadays'] = 'Δ {$a} days';
$string['error_dateshift_zero'] = 'Shift amount must not be 0.';

$string['courseshift:use'] = 'Shift dates across multiple courses';

$string['event_dates_shifted'] = 'Course dates shifted';

$string['privacy:metadata'] = 'The Shift course dates plugin does not store any personal data.';
