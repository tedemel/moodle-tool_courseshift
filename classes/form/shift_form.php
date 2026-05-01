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
 * Course shift form.
 *
 * @package    tool_courseshift
 * @copyright  2026 Tessa Demel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_courseshift\form;

defined('MOODLE_INTERNAL') || die();

require_once($GLOBALS['CFG']->libdir . '/formslib.php');

/**
 * Class shift_form.
 */
class shift_form extends \moodleform {
    /**
     * definition.
     */
    protected function definition(): void {
        $mform = $this->_form;

        $mform->addElement('static', 'intro', '', get_string('intro', 'tool_courseshift'));

        $mform->addElement('course', 'courseids', get_string('courses', 'tool_courseshift'), [
            'multiple' => true,
            'requiredcapabilities' => ['moodle/course:update'],
        ]);
        $mform->addHelpButton('courseids', 'courses', 'tool_courseshift');
        $mform->addRule('courseids', null, 'required', null, 'client');

        $mform->addElement('select', 'mode', get_string('mode', 'tool_courseshift'), [
            'anchor'    => get_string('mode_anchor', 'tool_courseshift'),
            'delta'     => get_string('mode_delta', 'tool_courseshift'),
            'percourse' => get_string('mode_percourse', 'tool_courseshift'),
        ]);
        $mform->setDefault('mode', 'anchor');

        $mform->addElement('date_time_selector', 'anchordate', get_string('anchordate', 'tool_courseshift'));
        $mform->addHelpButton('anchordate', 'anchordate', 'tool_courseshift');
        $mform->hideIf('anchordate', 'mode', 'noteq', 'anchor');

        $mform->addElement('text', 'deltadays', get_string('deltadays', 'tool_courseshift'));
        $mform->setType('deltadays', PARAM_INT);
        $mform->setDefault('deltadays', 0);
        $mform->addHelpButton('deltadays', 'deltadays', 'tool_courseshift');
        $mform->hideIf('deltadays', 'mode', 'noteq', 'delta');

        $mform->addElement('advcheckbox', 'includecontent', get_string('includecontent', 'tool_courseshift'));
        $mform->setDefault('includecontent', 1);

        // Optional scheduling.
        $mform->addElement('date_time_selector', 'scheduled', get_string('scheduled', 'tool_courseshift'), [
            'optional' => true,
        ]);
        $mform->addHelpButton('scheduled', 'scheduled', 'tool_courseshift');

        $this->add_action_buttons(true, get_string('submit', 'tool_courseshift'));
    }

    /**
     * validation.
     *
     * @param mixed $data
     * @param mixed $files
     * @return array
     */
    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);
        if (($data['mode'] ?? '') === 'delta' && (int)($data['deltadays'] ?? 0) === 0) {
            $errors['deltadays'] = get_string('error_dateshift_zero', 'tool_courseshift');
        }
        return $errors;
    }
}
