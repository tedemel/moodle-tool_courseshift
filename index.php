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

// Render the preview cards + apply form (or schedule-queue form).
$renderpreview = function (
    array $courseids,
    string $mode,
    int $anchordate,
    int $deltadays,
    bool $includecontent,
    int $scheduled,
    ?array $percoursedates,
    moodle_url $pageurl,
    callable $backbutton
) {
    global $OUTPUT;
    $rows = \tool_courseshift\local\shifter::preview(
        $courseids,
        $mode,
        $anchordate,
        $deltadays,
        $includecontent,
        $percoursedates
    );

    if (empty($rows)) {
        echo $OUTPUT->notification(get_string('nothingtoshift', 'tool_courseshift'), 'warning');
        echo \html_writer::link($pageurl, get_string('confirm_back', 'tool_courseshift'), ['class' => 'btn btn-secondary']);
        echo $backbutton();
        return;
    }

    echo \html_writer::tag('h4', get_string('preview_heading', 'tool_courseshift'));
    echo \html_writer::tag('p', get_string('preview_intro', 'tool_courseshift'), ['class' => 'text-muted']);

    foreach ($rows as $r) {
        echo \html_writer::start_div('card mb-3');
        echo \html_writer::start_div('card-body');
        echo \html_writer::tag('h5', $r['coursename'] . ' — '
            . get_string('preview_deltadays', 'tool_courseshift', $r['deltadays']));

        $tablerows = [];
        if ($r['startdate']['from']) {
            $tablerows[] = [get_string('preview_coursestart', 'tool_courseshift'),
                            $r['startdate']['fromfmt'], $r['startdate']['tofmt']];
        }
        if ($r['enddate']['from']) {
            $tablerows[] = [get_string('preview_courseend', 'tool_courseshift'),
                            $r['enddate']['fromfmt'], $r['enddate']['tofmt']];
        }
        foreach ($r['cms'] as $cm) {
            foreach ($cm['changes'] as $field => $pair) {
                $tablerows[] = [$cm['name'] . ' (' . $cm['modname'] . ')',
                                $pair['fromfmt'], $pair['tofmt'] . ' [' . $field . ']'];
            }
        }
        foreach ($r['events'] ?? [] as $ev) {
            $tablerows[] = ['📅 ' . $ev['name'], $ev['pair']['fromfmt'], $ev['pair']['tofmt']];
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

    // Apply form (with all params + optional schedule).
    $hidden = function (string $name, $value): string {
        return \html_writer::empty_tag('input', [
            'type'  => 'hidden',
            'name'  => $name,
            'value' => $value,
        ]);
    };
    echo \html_writer::start_tag('form', ['method' => 'post', 'action' => $pageurl]);
    echo $hidden('sesskey', sesskey());
    echo $hidden('action', $scheduled > 0 ? 'schedule' : 'apply');
    foreach ($courseids as $cid) {
        echo $hidden('courseids[]', (int)$cid);
    }
    echo $hidden('mode', $mode);
    echo $hidden('anchordate', $anchordate);
    echo $hidden('deltadays', $deltadays);
    echo $hidden('includecontent', $includecontent ? 1 : 0);
    echo $hidden('scheduled', $scheduled);
    if (is_array($percoursedates)) {
        foreach ($percoursedates as $cid => $ts) {
            echo $hidden("percoursedates[{$cid}]", (int)$ts);
        }
    }

    if ($scheduled > 0) {
        $btnlabel = get_string('confirm', 'tool_courseshift') . ' (' . userdate($scheduled) . ')';
    } else {
        $btnlabel = get_string('confirm', 'tool_courseshift');
    }
    $submitbtn = \html_writer::empty_tag('input', [
        'type'  => 'submit',
        'class' => 'btn btn-primary me-2',
        'value' => $btnlabel,
    ]);
    $cancellink = \html_writer::link(
        $pageurl,
        get_string('confirm_back', 'tool_courseshift'),
        ['class' => 'btn btn-secondary']
    );
    echo \html_writer::tag('div', $submitbtn . $cancellink, ['class' => 'mt-3']);
    echo \html_writer::end_tag('form');
    echo $backbutton();
};

// Action — cancel a pending scheduled shift.
if ($action === 'cancelschedule') {
    require_sesskey();
    $taskid = required_param('taskid', PARAM_INT);
    \tool_courseshift\local\monitor::cancel($taskid);
    redirect(
        $pageurl,
        get_string('scheduled_cancelled', 'tool_courseshift'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

// Action — undo.
if ($action === 'undo') {
    require_sesskey();
    $undoid = required_param('undoid', PARAM_INT);
    try {
        $result = \tool_courseshift\local\undo_runner::restore($undoid);
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('pagetitle', 'tool_courseshift'));
        echo $OUTPUT->notification(
            get_string('undo_result', 'tool_courseshift', (object)$result),
            \core\output\notification::NOTIFY_SUCCESS
        );
    } catch (\moodle_exception $e) {
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('pagetitle', 'tool_courseshift'));
        echo $OUTPUT->notification($e->getMessage(), \core\output\notification::NOTIFY_ERROR);
    }
    echo $backbutton();
    echo $OUTPUT->footer();
    return;
}

// Action — schedule (queue adhoc task).
if ($action === 'schedule') {
    require_sesskey();
    $courseids = optional_param_array('courseids', [], PARAM_INT);
    $mode      = optional_param('mode', 'anchor', PARAM_ALPHA);
    $anchordate = optional_param('anchordate', 0, PARAM_INT);
    $deltadays  = optional_param('deltadays', 0, PARAM_INT);
    $includecontent = (bool)optional_param('includecontent', 0, PARAM_BOOL);
    $scheduled = (int)optional_param('scheduled', 0, PARAM_INT);
    $percoursedates = optional_param_array('percoursedates', [], PARAM_INT);

    $task = new \tool_courseshift\task\scheduled_shift();
    $task->set_custom_data([
        'courseids'      => array_map('intval', $courseids),
        'mode'           => $mode,
        'anchordate'     => $anchordate,
        'deltadays'      => $deltadays,
        'includecontent' => $includecontent,
        'percoursedates' => $percoursedates ?: null,
    ]);
    $task->set_next_run_time($scheduled);
    \core\task\manager::queue_adhoc_task($task);

    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('pagetitle', 'tool_courseshift'));
    echo $OUTPUT->notification(
        get_string('scheduled_queued', 'tool_courseshift', userdate($scheduled)),
        \core\output\notification::NOTIFY_SUCCESS
    );
    echo $backbutton();
    echo $OUTPUT->footer();
    return;
}

// Action — apply.
if ($action === 'apply') {
    require_sesskey();
    $courseids = optional_param_array('courseids', [], PARAM_INT);
    $mode      = optional_param('mode', 'anchor', PARAM_ALPHA);
    $anchordate = optional_param('anchordate', 0, PARAM_INT);
    $deltadays  = optional_param('deltadays', 0, PARAM_INT);
    $includecontent = (bool)optional_param('includecontent', 0, PARAM_BOOL);
    $percoursedates = optional_param_array('percoursedates', [], PARAM_INT);

    $result = \tool_courseshift\local\shifter::apply(
        $courseids,
        $mode,
        $anchordate,
        $deltadays,
        $includecontent,
        $percoursedates ?: null
    );

    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('pagetitle', 'tool_courseshift'));
    echo $OUTPUT->notification(
        get_string('result', 'tool_courseshift', (object)$result),
        \core\output\notification::NOTIFY_SUCCESS
    );

    if (!empty($result['undoid'])) {
        $formattrs = [
            'method' => 'post',
            'action' => $pageurl,
            'class'  => 'd-inline-block me-2',
        ];
        $hiddeninput = function (string $name, $value): string {
            return \html_writer::empty_tag('input', [
                'type'  => 'hidden',
                'name'  => $name,
                'value' => $value,
            ]);
        };
        echo \html_writer::start_tag('form', $formattrs);
        echo $hiddeninput('sesskey', sesskey());
        echo $hiddeninput('action', 'undo');
        echo $hiddeninput('undoid', (int)$result['undoid']);
        echo \html_writer::empty_tag('input', [
            'type'  => 'submit',
            'class' => 'btn btn-warning',
            'value' => get_string('undo_button', 'tool_courseshift'),
        ]);
        echo \html_writer::end_tag('form');
    }
    echo $backbutton();
    echo $OUTPUT->footer();
    return;
}

// Action — preview (POSTed from percourse-input form).
if ($action === 'preview') {
    require_sesskey();
    $courseids = optional_param_array('courseids', [], PARAM_INT);
    $mode      = optional_param('mode', 'percourse', PARAM_ALPHA);
    $includecontent = (bool)optional_param('includecontent', 0, PARAM_BOOL);
    $scheduled = (int)optional_param('scheduled', 0, PARAM_INT);
    $percoursedates = optional_param_array('percoursedates', [], PARAM_INT);

    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('pagetitle', 'tool_courseshift'));
    $renderpreview(
        $courseids,
        $mode,
        0,
        0,
        $includecontent,
        $scheduled,
        $percoursedates ?: null,
        $pageurl,
        $backbutton
    );
    echo $OUTPUT->footer();
    return;
}

// Default — form.
$form = new \tool_courseshift\form\shift_form();
if ($form->is_cancelled()) {
    redirect($backurl);
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pagetitle', 'tool_courseshift'));

if ($data = $form->get_data()) {
    $courseids = (array)$data->courseids;
    $mode = $data->mode;
    $anchordate = (int)($data->anchordate ?? 0);
    $deltadays = (int)($data->deltadays ?? 0);
    $includecontent = !empty($data->includecontent);
    $scheduled = (int)($data->scheduled ?? 0);

    if ($mode === 'percourse') {
        // Render per-course date input form (HTML, not moodleform).
        $hiddeninput = function (string $name, $value): string {
            return \html_writer::empty_tag('input', [
                'type'  => 'hidden',
                'name'  => $name,
                'value' => $value,
            ]);
        };
        echo \html_writer::tag('h4', get_string('percourse_heading', 'tool_courseshift'));
        echo \html_writer::tag(
            'p',
            get_string('percourse_intro', 'tool_courseshift'),
            ['class' => 'text-muted']
        );

        echo \html_writer::start_tag('form', ['method' => 'post', 'action' => $pageurl]);
        echo $hiddeninput('sesskey', sesskey());
        echo $hiddeninput('action', 'preview');
        echo $hiddeninput('mode', 'percourse');
        echo $hiddeninput('includecontent', $includecontent ? 1 : 0);
        echo $hiddeninput('scheduled', $scheduled);

        $table = new \html_table();
        $table->head = ['Kurs', 'Aktuelles Startdatum', 'Neues Startdatum'];
        $table->attributes['class'] = 'table table-sm';
        foreach ($courseids as $cid) {
            $cid = (int)$cid;
            $course = $DB->get_record('course', ['id' => $cid]);
            if (!$course) {
                continue;
            }
            $cur = $course->startdate > 0 ? userdate($course->startdate, '%Y-%m-%d %H:%M') : '—';
            $defaultval = $course->startdate > 0
                ? date('Y-m-d\TH:i', $course->startdate)
                : '';
            $input = \html_writer::empty_tag('input', [
                'type' => 'datetime-local',
                'class' => 'form-control form-control-sm',
                'name' => "percourseinput[{$cid}]",
                'value' => $defaultval,
            ]);
            // Hidden mirror the courseid into courseids[] so apply step has the list.
            $input .= \html_writer::empty_tag('input', [
                'type' => 'hidden',
                'name' => 'courseids[]',
                'value' => $cid,
            ]);
            $table->data[] = [format_string($course->fullname), $cur, $input];
        }
        echo \html_writer::table($table);
        $submitbtn = \html_writer::empty_tag('input', [
            'type'  => 'submit',
            'class' => 'btn btn-primary me-2',
            'value' => get_string('percourse_continue', 'tool_courseshift'),
        ]);
        $cancellink = \html_writer::link(
            $pageurl,
            get_string('confirm_back', 'tool_courseshift'),
            ['class' => 'btn btn-secondary']
        );
        echo \html_writer::tag('div', $submitbtn . $cancellink, ['class' => 'mt-3']);
        echo \html_writer::end_tag('form');
        // JS: convert datetime-local strings into Unix timestamps in percoursedates[] before submit.
        $PAGE->requires->js_amd_inline(<<<'JS'
require([], function() {
    document.addEventListener('submit', function(e) {
        const form = e.target;
        if (!form || form.querySelector('input[name="action"][value="preview"]') === null) return;
        form.querySelectorAll('input[type="datetime-local"][name^="percourseinput["]').forEach(function(el) {
            const cid = el.name.match(/\[(\d+)\]/)[1];
            const ts = el.value ? Math.floor(new Date(el.value).getTime() / 1000) : 0;
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'percoursedates[' + cid + ']';
            hidden.value = ts;
            form.appendChild(hidden);
        });
    }, true);
});
JS);

        echo $backbutton();
        echo $OUTPUT->footer();
        return;
    }

    // Anchor/delta: directly preview.
    $renderpreview(
        $courseids,
        $mode,
        $anchordate,
        $deltadays,
        $includecontent,
        $scheduled,
        null,
        $pageurl,
        $backbutton
    );
    echo $OUTPUT->footer();
    return;
}

// Monitor panel — pending scheduled shifts + recent history.
$pending = \tool_courseshift\local\monitor::pending();
$recent  = \tool_courseshift\local\monitor::recent(8);

echo \html_writer::start_div('card mb-3');
echo \html_writer::start_div('card-body');
echo \html_writer::tag(
    'h4',
    get_string('scheduled_pending_heading', 'tool_courseshift'),
    ['class' => 'h5 mb-3']
);
if (empty($pending)) {
    echo \html_writer::tag(
        'p',
        get_string('scheduled_no_pending', 'tool_courseshift'),
        ['class' => 'text-muted mb-0']
    );
} else {
    $ptable = new \html_table();
    $ptable->head = [
        get_string('monitor_pending_runtime', 'tool_courseshift'),
        get_string('monitor_pending_user', 'tool_courseshift'),
        get_string('monitor_pending_summary', 'tool_courseshift'),
        get_string('monitor_pending_mode', 'tool_courseshift'),
        '',
    ];
    $ptable->attributes['class'] = 'table table-sm';
    foreach ($pending as $task) {
        $cd = json_decode($task->customdata) ?: new \stdClass();
        $cids = (array)($cd->courseids ?? []);
        $modeval = (string)($cd->mode ?? 'anchor');
        $user = $DB->get_record('user', ['id' => $task->userid], 'id, firstname, lastname');
        $hiddencancel = function (string $name, $value): string {
            return \html_writer::empty_tag('input', [
                'type'  => 'hidden',
                'name'  => $name,
                'value' => $value,
            ]);
        };
        $cancelform  = \html_writer::start_tag('form', [
            'method' => 'post',
            'action' => $pageurl,
            'class'  => 'd-inline',
        ]);
        $cancelform .= $hiddencancel('sesskey', sesskey());
        $cancelform .= $hiddencancel('action', 'cancelschedule');
        $cancelform .= $hiddencancel('taskid', (int)$task->id);
        $cancelform .= \html_writer::empty_tag('input', [
            'type'  => 'submit',
            'class' => 'btn btn-sm btn-outline-danger',
            'value' => get_string('scheduled_cancel', 'tool_courseshift'),
        ]);
        $cancelform .= \html_writer::end_tag('form');
        $ptable->data[] = [
            userdate((int)$task->nextruntime),
            $user ? fullname($user) : '#' . (int)$task->userid,
            count($cids),
            $modeval,
            $cancelform,
        ];
    }
    echo \html_writer::table($ptable);
}
echo \html_writer::end_div();
echo \html_writer::end_div();

if (!empty($recent)) {
    echo \html_writer::start_div('card mb-3');
    echo \html_writer::start_div('card-body');
    echo \html_writer::tag(
        'h4',
        get_string('monitor_recent_heading', 'tool_courseshift'),
        ['class' => 'h5 mb-3']
    );
    $rtable = new \html_table();
    $rtable->head = [
        get_string('monitor_recent_when', 'tool_courseshift'),
        get_string('monitor_recent_user', 'tool_courseshift'),
        get_string('monitor_recent_courses', 'tool_courseshift'),
        get_string('monitor_recent_cms', 'tool_courseshift'),
        get_string('monitor_recent_events', 'tool_courseshift'),
    ];
    $rtable->attributes['class'] = 'table table-sm';
    foreach ($recent as $log) {
        $other = json_decode($log->other) ?: new \stdClass();
        $user = $DB->get_record('user', ['id' => $log->userid], 'id, firstname, lastname');
        $rtable->data[] = [
            userdate((int)$log->timecreated),
            $user ? fullname($user) : '#' . (int)$log->userid,
            (int)($other->courses ?? 0),
            (int)($other->cms ?? 0),
            (int)($other->events ?? 0),
        ];
    }
    echo \html_writer::table($rtable);
    $loglink = \html_writer::link(
        new moodle_url('/report/log/index.php', ['id' => 1]),
        get_string('monitor_recent_more', 'tool_courseshift'),
        ['class' => 'small']
    );
    echo \html_writer::tag('p', $loglink, ['class' => 'mb-0']);
    echo \html_writer::end_div();
    echo \html_writer::end_div();
}

// Select-all-in-category shortcut button if categoryid is set.
if ($categoryid > 0) {
    $cat = core_course_category::get($categoryid, IGNORE_MISSING);
    if ($cat) {
        $courseinfo = $cat->get_courses(['recursive' => true]);
        $count = count($courseinfo);
        if ($count > 0) {
            $ids = array_keys($courseinfo);
            $idsjson = htmlspecialchars(json_encode(array_map('intval', $ids)), ENT_QUOTES);
            $btnhtml = \html_writer::tag(
                'button',
                get_string('select_all_in_category', 'tool_courseshift', $count),
                [
                    'type'                        => 'button',
                    'class'                       => 'btn btn-outline-primary mb-2',
                    'data-courseshift-select-ids' => $idsjson,
                ]
            );
            echo \html_writer::tag('div', $btnhtml, ['class' => 'mb-3']);
            $PAGE->requires->js_amd_inline(<<<'JS'
require(['core/form-autocomplete'], function() {
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('[data-courseshift-select-ids]');
        if (!btn) return;
        const ids = JSON.parse(btn.dataset.courseshiftSelectIds);
        const select = document.querySelector('select[name="courseids[]"]');
        if (!select) return;
        ids.forEach(function(id) {
            let opt = select.querySelector('option[value="' + id + '"]');
            if (!opt) {
                opt = document.createElement('option');
                opt.value = id;
                opt.text = 'Course ' + id;
                select.appendChild(opt);
            }
            opt.selected = true;
        });
        select.dispatchEvent(new Event('change', {bubbles: true}));
    });
});
JS);
        }
    }
}

$form->display();
echo $backbutton();
echo $OUTPUT->footer();
