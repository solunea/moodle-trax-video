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

// Plugin strings.
$string['modulename'] = 'Trax Video';
$string['modulename_help'] = "This plugin let's you add xAPI videos into your Moodle courses.";
$string['modulenameplural'] = 'Trax Videos';
$string['pluginadministration'] = 'Trax Video administration';
$string['pluginname'] = 'Trax Video';
$string['page-mod-traxvideo-x'] = 'Any Trax Video page';
$string['page-mod-traxvideo-view'] = 'Trax Video main page';

// Permissions.
$string['traxvideo:addinstance'] = 'Add a new Trax Video';
$string['traxvideo:view'] = 'View Trax Video';

// Mod Form.
$string['poster'] = 'Poster (PNG,JPG)';
$string['poster_help'] = 'Enter the URL of a video poster which will be displayed before the video is loaded.';
$string['sourcemp4'] = 'Video (URL of source video)';
$string['sourcemp4_help'] = 'Enter the URL of the video with an mp4 extension.';

// Privacy metadata.
$string['privacy:metadata'] = 'This plugin does not store any personal data.
    However, some events are sent to the Trax Logs plugin.
    Refer to Trax Logs data privacy policy.';

$string['configdisplayoptions'] = 'Select all options that should be available, existing settings are not modified. Hold CTRL key to select multiple fields.';
$string['configframesize'] = 'When a web page or a video is displayed within a frame, this value is the height (in pixels) of the top frame (which contains the navigation).';
$string['configparametersettings'] = 'This sets the default value for the Parameter settings pane in the form when adding some new videos. After the first time, this becomes an individual user preference.';
$string['configpopup'] = 'When adding a new video which is able to be shown in a popup window, should this option be enabled by default?';
$string['configpopupdirectories'] = 'Should popup windows show directory links by default?';
$string['configpopupheight'] = 'What height should be the default height for new popup windows?';
$string['configpopuplocation'] = 'Should popup windows show the location bar by default?';
$string['configpopupmenubar'] = 'Should popup windows show the menu bar by default?';
$string['configpopupresizable'] = 'Should popup windows be resizable by default?';
$string['configpopupscrollbars'] = 'Should popup windows be scrollable by default?';
$string['configpopupstatus'] = 'Should popup windows show the status bar by default?';
$string['configpopuptoolbar'] = 'Should popup windows show the tool bar by default?';
$string['configpopupwidth'] = 'What width should be the default width for new popup windows?';
$string['contentheader'] = 'Content';
$string['displayoptions'] = 'Available display options';
$string['displayselect'] = 'Display';
$string['displayselect_help'] = 'This setting, together with the file type and whether the browser allows embedding, determines how the video is displayed. Options may include:

* Automatic - The best display option for the video is selected automatically
* Embed - The video is displayed within the page below the navigation bar
* In pop-up - The video is displayed in a new browser window, with specific dimensions, without menus or an address bar
* Open - The video is displayed in the current page, without menus or an address bar
* New window - The video is displayed in a new browser window with menus and an address bar';
$string['displayselect_link'] = 'mod/file/mod';
$string['displayselectexplain'] = 'Choose display type.';
$string['popupheight'] = 'Pop-up height (in pixels)';
$string['popupheightexplain'] = 'Specifies default height of popup windows.';
$string['popupwidth'] = 'Pop-up width (in pixels)';
$string['popupwidthexplain'] = 'Specifies default width of popup windows.';

$string['click'] = 'Click ';
$string['videolink'] = 'link to view the video.';

