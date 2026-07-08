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

$string['anchordate'] = 'New course start date';
$string['anchordate_help'] = 'Each course is shifted so its start date lands on the chosen date. Content is shifted by the same delta.';
$string['back_management'] = 'Back to course and category management';
$string['confirm'] = 'Apply';
$string['confirm_back'] = 'Back to form';
$string['courses'] = 'Courses';
$string['courses_help'] = 'Pick the courses whose dates should be shifted.';
$string['courseshift:use'] = 'Shift dates across multiple courses';
$string['deltadays'] = 'Shift by (days)';
$string['deltadays_help'] = 'Positive value = forward, negative = backward.';
$string['error_dateshift_zero'] = 'Shift amount must not be 0.';
$string['error_undo_expired'] = 'This undo entry has expired.';
$string['error_undo_missing'] = 'Undo entry not found or already consumed.';
$string['error_undo_permission'] = 'No permission to use this undo entry.';
$string['event_dates_shifted'] = 'Course dates shifted';
$string['includecontent'] = 'Also shift activity dates';
$string['intro'] = 'Shifts course start, course end, and all date-bearing activities of selected courses by the same amount.';
$string['mode'] = 'Mode';
$string['mode_anchor'] = 'Set to new start date (per course)';
$string['mode_delta'] = 'Shift by fixed amount';
$string['mode_percourse'] = 'Per-course individual start date';
$string['monitor_pending_mode'] = 'Mode';
$string['monitor_pending_runtime'] = 'Scheduled for';
$string['monitor_pending_summary'] = 'Courses';
$string['monitor_pending_user'] = 'Queued by';
$string['monitor_recent_cms'] = 'Activity dates';
$string['monitor_recent_courses'] = 'Courses';
$string['monitor_recent_events'] = 'Events';
$string['monitor_recent_heading'] = 'Recently executed';
$string['monitor_recent_more'] = 'View in full log';
$string['monitor_recent_none'] = 'No shifts executed yet.';
$string['monitor_recent_user'] = 'By';
$string['monitor_recent_when'] = 'When';
$string['nothingtoshift'] = 'Nothing to shift.';
$string['pagetitle'] = 'Shift dates across multiple courses';
$string['percourse_continue'] = 'Show preview';
$string['percourse_heading'] = 'Per-course start dates';
$string['percourse_intro'] = 'Pick a new start date for each course. Activity dates and calendar events shift by the resulting per-course delta.';
$string['percourse_th_course'] = 'Course';
$string['percourse_th_current'] = 'Current start date';
$string['percourse_th_new'] = 'New start date';
$string['pluginname'] = 'Shift course dates';
$string['preview_courseend'] = 'Course end';
$string['preview_coursestart'] = 'Course start';
$string['preview_deltadays'] = 'Δ {$a} days';
$string['preview_heading'] = 'Preview';
$string['preview_intro'] = 'The following changes will be applied on confirmation:';
$string['preview_th_after'] = 'After';
$string['preview_th_before'] = 'Before';
$string['preview_th_element'] = 'Element';
$string['privacy:metadata'] = 'The Shift course dates plugin does not store any personal data.';
$string['result'] = 'Successfully shifted: {$a->courses} courses, {$a->cms} activity dates, {$a->events} calendar events.';
$string['scheduled'] = 'Schedule to run at';
$string['scheduled_cancel'] = 'Cancel scheduled shift';
$string['scheduled_cancelled'] = 'Scheduled shift cancelled.';
$string['scheduled_help'] = 'Optional. When set, the shift is NOT applied immediately but executed via cron at the chosen time.';
$string['scheduled_no_pending'] = 'No scheduled shifts pending.';
$string['scheduled_pending_heading'] = 'Pending scheduled shifts';
$string['scheduled_queued'] = 'Shift queued for {$a}. It will run automatically via cron.';
$string['select_all_in_category'] = 'Select all courses in this category ({$a})';
$string['submit'] = 'Show preview';
$string['task_cleanup_undo'] = 'Expire stale tool_courseshift undo snapshots';
$string['undo_button'] = 'Undo';
$string['undo_result'] = 'Restored: {$a->courses} courses, {$a->cms} activity dates, {$a->events} calendar events.';
