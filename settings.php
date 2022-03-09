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

if ($hassiteconfig) {
    require_once($CFG->dirroot . '/mod/traxvideo/locallib.php');
    $displayoptions = array(TraxVideoConfig::TRAXLIB_DISPLAY_AUTO => get_string('resourcedisplayauto'),
        TraxVideoConfig::TRAXLIB_DISPLAY_EMBED => get_string('resourcedisplayembed'),
        TraxVideoConfig::TRAXLIB_DISPLAY_NEW => get_string('resourcedisplaynew'),
        TraxVideoConfig::TRAXLIB_DISPLAY_OPEN => get_string('resourcedisplayopen'),
        TraxVideoConfig::TRAXLIB_DISPLAY_POPUP => get_string('resourcedisplaypopup'));
    $defaultdisplayoptions = array(TraxVideoConfig::TRAXLIB_DISPLAY_AUTO,
        TraxVideoConfig::TRAXLIB_DISPLAY_EMBED,
        TraxVideoConfig::TRAXLIB_DISPLAY_NEW,
        TraxVideoConfig::TRAXLIB_DISPLAY_OPEN,
        TraxVideoConfig::TRAXLIB_DISPLAY_POPUP,
    );

//--- general settings -----------------------------------------------------------------------------------
    $settings->add(new admin_setting_configmultiselect('traxvideo/displayoptions',
        get_string('displayoptions', 'traxvideo'), get_string('configdisplayoptions', 'traxvideo'),
        $defaultdisplayoptions, $displayoptions));

    //--- modedit defaults -----------------------------------------------------------------------------------
    $settings->add(new admin_setting_configselect('traxvideo/display',
        get_string('displayselect', 'traxvideo'), get_string('displayselectexplain', 'traxvideo'), TraxVideoConfig::TRAXLIB_DISPLAY_AUTO,
        $displayoptions));
    $settings->add(new admin_setting_configtext('traxvideo/popupwidth',
        get_string('popupwidth', 'traxvideo'), get_string('popupwidthexplain', 'traxvideo'), 620, PARAM_INT, 7));
    $settings->add(new admin_setting_configtext('traxvideo/popupheight',
        get_string('popupheight', 'traxvideo'), get_string('popupheightexplain', 'traxvideo'), 450, PARAM_INT, 7));

}



