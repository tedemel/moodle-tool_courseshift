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
 * Library functions for tool_courseshift.
 *
 * @package    tool_courseshift
 * @copyright  2026 Tessa Demel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Add the link to category settings navigation.
 */
function tool_courseshift_extend_navigation_category_settings($navigation, $context) {
    if (!has_capability('tool/courseshift:use', context_system::instance())) {
        return;
    }
    $url = new moodle_url('/admin/tool/courseshift/index.php', [
        'categoryid' => $context->instanceid,
    ]);
    $navigation->add(
        get_string('pluginname', 'tool_courseshift'),
        $url,
        navigation_node::TYPE_SETTING,
        null,
        'tool_courseshift_link',
        new pix_icon('i/calendar', '')
    );
}

/**
 * Add the link to a course's settings navigation.
 */
function tool_courseshift_extend_navigation_course($navigation, $course, $context) {
    if (!has_capability('tool/courseshift:use', context_system::instance())) {
        return;
    }
    $url = new moodle_url('/admin/tool/courseshift/index.php', [
        'courseid' => $course->id,
    ]);
    $navigation->add(
        get_string('pluginname', 'tool_courseshift'),
        $url,
        navigation_node::TYPE_SETTING,
        null,
        'tool_courseshift_link',
        new pix_icon('i/calendar', '')
    );
}
