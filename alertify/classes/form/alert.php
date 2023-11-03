<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Form for editing the block instances.
 *
 * @package   block_Alertify
 * @copyright 2023 Syed Toheed Gillani
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_alertify\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class alert extends \moodleform {
    protected function definition() {
        $mform = $this->_form;

        $mform->addElement('header', 'addalert', get_string('addalert', 'block_alertify'));

        $mform->addElement('selectyesno', 'enabled', get_string('enabled', 'block_alertify'));
        $mform->setDefault('enabled', 0);

        $mform->addElement('select', 'cmid', get_string('activity', 'block_alertify'), $this->_customdata['activities']);
        $mform->addRule('cmid', get_string('required'), 'required');
        if (!empty($this->_customdata['alertid'])) {
            $mform->hardFreeze('cmid');
        }

        $mform->addElement('textarea', 'template', get_string('alerttemplate', 'block_alertify'), array('cols' => 40, 'rows' => 5));
        $mform->addHelpButton('template', 'alerttemplate', 'block_alertify');
        $mform->addRule('template', get_string('required'), 'required');

        $mform->addElement('hidden', 'course');
        $mform->setType('course', PARAM_INT);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $this->add_action_buttons();
    }
}
