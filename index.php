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
 * Multi-course date shift admin page.
 *
 * @package    tool_courseshift
 * @copyright  2026 Tessa Demel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();
$context = context_system::instance();
require_capability('tool/courseshift:use', $context);

$categoryid = optional_param('categoryid', 0, PARAM_INT);
$courseid   = optional_param('courseid', 0, PARAM_INT);
$action     = optional_param('action', '', PARAM_ALPHA);

$pageurl = new moodle_url('/admin/tool/courseshift/index.php');
if ($categoryid) {
    $pageurl->param('categoryid', $categoryid);
}
if ($courseid) {
    $pageurl->param('courseid', $courseid);
}

admin_externalpage_setup('tool_courseshift', '', null, $pageurl);
$PAGE->set_title(get_string('pagetitle', 'tool_courseshift'));
$PAGE->set_heading(get_string('pagetitle', 'tool_courseshift'));

$backurl = $categoryid
    ? new moodle_url('/course/management.php', ['categoryid' => $categoryid])
    : new moodle_url('/course/management.php');
$backbutton = function () use ($backurl) {
    return \html_writer::link(
        $backurl,
        get_string('back_management', 'tool_courseshift'),
        ['class' => 'btn btn-secondary mt-3']
    );
};

// Apply step.
if ($action === 'apply') {
    require_sesskey();
    $courseids  = optional_param_array('courseids', [], PARAM_INT);
    $mode       = optional_param('mode', 'anchor', PARAM_ALPHA);
    $anchordate = optional_param('anchordate', 0, PARAM_INT);
    $deltadays  = optional_param('deltadays', 0, PARAM_INT);
    $includecontent = (bool)optional_param('includecontent', 0, PARAM_BOOL);

    $result = \tool_courseshift\local\shifter::apply(
        $courseids, $mode, $anchordate, $deltadays, $includecontent
    );

    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('pagetitle', 'tool_courseshift'));
    echo $OUTPUT->notification(
        get_string('result', 'tool_courseshift', (object)$result),
        \core\output\notification::NOTIFY_SUCCESS
    );
    echo $backbutton();
    echo $OUTPUT->footer();
    return;
}

$form = new \tool_courseshift\form\shift_form();

if ($form->is_cancelled()) {
    redirect($backurl);
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pagetitle', 'tool_courseshift'));

// Preview step.
if ($data = $form->get_data()) {
    $courseids = (array)$data->courseids;
    $mode      = $data->mode;
    $anchordate = (int)($data->anchordate ?? 0);
    $deltadays  = (int)($data->deltadays ?? 0);
    $includecontent = !empty($data->includecontent);

    $rows = \tool_courseshift\local\shifter::preview(
        $courseids, $mode, $anchordate, $deltadays, $includecontent
    );

    if (empty($rows)) {
        echo $OUTPUT->notification(get_string('nothingtoshift', 'tool_courseshift'), 'warning');
        $form->display();
        echo $backbutton();
        echo $OUTPUT->footer();
        return;
    }

    echo \html_writer::tag('h4', get_string('preview_heading', 'tool_courseshift'));
    echo \html_writer::tag(
        'p',
        get_string('preview_intro', 'tool_courseshift'),
        ['class' => 'text-muted']
    );

    foreach ($rows as $r) {
        echo \html_writer::start_div('card mb-3');
        echo \html_writer::start_div('card-body');
        echo \html_writer::tag(
            'h5',
            $r['coursename'] . ' — ' . get_string('preview_deltadays', 'tool_courseshift', $r['deltadays'])
        );

        $tablerows = [];
        if ($r['startdate']['from']) {
            $tablerows[] = [
                get_string('preview_coursestart', 'tool_courseshift'),
                $r['startdate']['fromfmt'],
                $r['startdate']['tofmt'],
            ];
        }
        if ($r['enddate']['from']) {
            $tablerows[] = [
                get_string('preview_courseend', 'tool_courseshift'),
                $r['enddate']['fromfmt'],
                $r['enddate']['tofmt'],
            ];
        }
        foreach ($r['cms'] as $cm) {
            foreach ($cm['changes'] as $field => $pair) {
                $tablerows[] = [
                    $cm['name'] . ' (' . $cm['modname'] . ')',
                    $pair['fromfmt'],
                    $pair['tofmt'] . ' [' . $field . ']',
                ];
            }
        }

        $table = new \html_table();
        $table->head = [
            get_string('preview_th_element', 'tool_courseshift'),
            get_string('preview_th_before', 'tool_courseshift'),
            get_string('preview_th_after', 'tool_courseshift'),
        ];
        $table->data = $tablerows;
        $table->attributes['class'] = 'table table-sm';
        echo \html_writer::table($table);

        echo \html_writer::end_div();
        echo \html_writer::end_div();
    }

    echo \html_writer::start_tag('form', ['method' => 'post', 'action' => $pageurl]);
    echo \html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
    echo \html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'action', 'value' => 'apply']);
    foreach ($courseids as $cid) {
        echo \html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'courseids[]', 'value' => (int)$cid]);
    }
    echo \html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'mode', 'value' => $mode]);
    echo \html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'anchordate', 'value' => $anchordate]);
    echo \html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'deltadays', 'value' => $deltadays]);
    echo \html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'includecontent', 'value' => $includecontent ? 1 : 0]);
    echo \html_writer::tag('div', \html_writer::empty_tag('input', [
        'type'  => 'submit',
        'class' => 'btn btn-primary me-2',
        'value' => get_string('confirm', 'tool_courseshift'),
    ]) . \html_writer::link($pageurl, get_string('confirm_back', 'tool_courseshift'), [
        'class' => 'btn btn-secondary',
    ]), ['class' => 'mt-3']);
    echo \html_writer::end_tag('form');

    echo $backbutton();
    echo $OUTPUT->footer();
    return;
}

// Default: form.
$form->display();
echo $backbutton();
echo $OUTPUT->footer();
