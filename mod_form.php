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
 * Trax Video for Moodle.
 *
 * @package    mod_traxvideo
 * @copyright  2019 Sébastien Fraysse {@link http://fraysse.eu}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/course/moodleform_mod.php');

class mod_traxvideo_mod_form extends moodleform_mod {

    function definition() {
		$config = get_config('traxvideo');
		$mform = $this->_form;

		// General settings.
		$mform->addElement('header', 'general', get_string('general', 'form'));

		// Name.
		$mform->addElement('text', 'name', get_string('name'));
		$mform->setType('name', PARAM_TEXT);
		$mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

		// Summary.
        $this->standard_intro_elements();

		// Common settings.
		$this->standard_coursemodule_elements();

		// Submit buttons.
		$this->add_action_buttons();
	}

}

