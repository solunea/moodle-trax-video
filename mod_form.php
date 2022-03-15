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

require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once('./lib.php');
require_once($CFG->dirroot . '/mod/traxvideo/locallib.php');

class mod_traxvideo_mod_form extends moodleform_mod
{

    function definition()
    {
        $config = get_config('traxvideo');
        $mform = $this->_form;

        // General settings.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Name.
        $mform->addElement('text', 'name', get_string('name'), ['size' => '100']);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        // Summary.
        $this->standard_intro_elements();

        // Video.
        $mform->addElement('text', 'sourcemp4', get_string('sourcemp4', 'traxvideo'), ['size' => '100']);
        $mform->setType('sourcemp4', PARAM_TEXT);
        $mform->addRule('sourcemp4', null, 'required', null, 'client');
        $mform->addRule('sourcemp4', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('sourcemp4', 'sourcemp4', 'traxvideo');
        $mform->setDefault('sourcemp4', 'http://vjs.zencdn.net/v/oceans.mp4');

        // Poster.
        $mform->addElement('text', 'poster', get_string('poster', 'traxvideo'), ['size' => '100']);
        $mform->setType('poster', PARAM_TEXT);
        $mform->addRule('poster', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('poster', 'poster', 'traxvideo');
        $mform->setDefault('poster', 'http://vjs.zencdn.net/v/oceans.png');

        // Terminated strategy
        $mform->addElement('select', 'terminate', get_string('terminateSelect', 'traxvideo'), $this->get_terminating_options());
        $mform->setType('terminate', PARAM_INT);
        $mform->setDefault('terminate', 0);
        $mform->addHelpButton('terminate', 'terminateSelect', 'traxvideo');

        //-------------------------------------------------------
        $mform->addElement('header', 'optionssection', get_string('appearance'));

        if ($this->current->instance) {
            $options = $this->get_displayoptions(explode(',', $config->displayoptions), $this->current->display);
        } else {
            $options = $this->get_displayoptions(explode(',', $config->displayoptions));
        }

        $mform->addElement('select', 'display', get_string('displayselect', 'traxvideo'), $options);
        $mform->setDefault('display', $config->display);
        $mform->addHelpButton('display', 'displayselect', 'traxvideo');

        if (array_key_exists(TraxVideoConfig::TRAXLIB_DISPLAY_POPUP, $options)) {
            $mform->addElement('text', 'popupwidth', get_string('popupwidth', 'traxvideo'), array('size' => 3));
            if (count($options) > 1) {
                $mform->hideIf('popupwidth', 'display', 'noteq', TraxVideoConfig::TRAXLIB_DISPLAY_POPUP);
            }
            $mform->setType('popupwidth', PARAM_INT);
            $mform->setDefault('popupwidth', $config->popupwidth);
            $mform->setAdvanced('popupwidth', true);

            $mform->addElement('text', 'popupheight', get_string('popupheight', 'traxvideo'), array('size' => 3));
            if (count($options) > 1) {
                $mform->hideIf('popupheight', 'display', 'noteq', TraxVideoConfig::TRAXLIB_DISPLAY_POPUP);
            }
            $mform->setType('popupheight', PARAM_INT);
            $mform->setDefault('popupheight', $config->popupheight);
            $mform->setAdvanced('popupheight', true);
        }

        //-------------------------------------------------------
        // Common settings.
        $this->standard_coursemodule_elements();

        // Submit buttons.
        $this->add_action_buttons();
    }

    function data_preprocessing(&$default_values)
    {
        if (!empty($default_values['displayoptions'])) {
            $displayoptions = unserialize($default_values['displayoptions']);
            if (!empty($displayoptions['popupwidth'])) {
                $default_values['popupwidth'] = $displayoptions['popupwidth'];
            }
            if (!empty($displayoptions['popupheight'])) {
                $default_values['popupheight'] = $displayoptions['popupheight'];
            }
        }
    }

    /**
     * Returns list of available options for terminating video.
     * @return array of key=>name pairs
     */
    function get_terminating_options()
    {
        return array(TraxTerminatedVideoConfig::TRAXLIB_TERMINATED_ONACTION => get_string('terminateOnAction', 'traxvideo'),
            TraxTerminatedVideoConfig::TRAXLIB_TERMINATED_ONCOMPLETE_ANDSTOP => get_string('terminateOnCompleteStop', 'traxvideo'),
            TraxTerminatedVideoConfig::TRAXLIB_TERMINATED_ONCOMPLETE_ANDCONTINUE => get_string('terminateOnCompleteContinue', 'traxvideo'));
    }

    /**
     * Returns list of available display options
     * @param array $enabled list of options enabled in module configuration
     * @param int $current current display options for existing instances
     * @return array of key=>name pairs
     */
    function get_displayoptions(array $enabled, $current = null)
    {
        if (is_number($current)) {
            $enabled[] = $current;
        }

        $options = array(TraxVideoConfig::TRAXLIB_DISPLAY_AUTO => get_string('resourcedisplayauto'),
            TraxVideoConfig::TRAXLIB_DISPLAY_EMBED => get_string('resourcedisplayembed'),
            TraxVideoConfig::TRAXLIB_DISPLAY_FRAME => get_string('resourcedisplayframe'),
            TraxVideoConfig::TRAXLIB_DISPLAY_NEW => get_string('resourcedisplaynew'),
            TraxVideoConfig::TRAXLIB_DISPLAY_DOWNLOAD => get_string('resourcedisplaydownload'),
            TraxVideoConfig::TRAXLIB_DISPLAY_OPEN => get_string('resourcedisplayopen'),
            TraxVideoConfig::TRAXLIB_DISPLAY_POPUP => get_string('resourcedisplaypopup'));

        $result = array();

        foreach ($options as $key => $value) {
            if (in_array($key, $enabled)) {
                $result[$key] = $value;
            }
        }

        if (empty($result)) {
            // there should be always something in case admin misconfigures module
            $result[TraxVideoConfig::TRAXLIB_DISPLAY_OPEN] = $options[TraxVideoConfig::TRAXLIB_DISPLAY_OPEN];
        }

        return $result;
    }
}
