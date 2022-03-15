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

defined('MOODLE_INTERNAL') || die();

class TraxVideoConfig {
    const TRAXLIB_DISPLAY_AUTO = 0;
    const TRAXLIB_DISPLAY_EMBED = 1;
    const TRAXLIB_DISPLAY_FRAME = 2;
    const TRAXLIB_DISPLAY_NEW = 3;
    const TRAXLIB_DISPLAY_DOWNLOAD = 4;
    const TRAXLIB_DISPLAY_OPEN = 5;
    const TRAXLIB_DISPLAY_POPUP = 6;
}

class TraxTerminatedVideoConfig {
    const TRAXLIB_TERMINATED_ONACTION = 0;
    const TRAXLIB_TERMINATED_ONCOMPLETE_ANDSTOP = 1;
    const TRAXLIB_TERMINATED_ONCOMPLETE_ANDCONTINUE = 2;
}

/**
 * Mark the activity completed (if required) and trigger the course_module_viewed event.
 *
 * @param string $eventname event name
 * @param stdClass $traxvideo traxvideo object
 * @param stdClass $course course object
 * @param stdClass $cm course module object
 * @param stdClass $context context object
 */
function traxvideo_tigger_module_event($eventname, $traxvideo, $course, $cm, $context)
{

    // Trigger course_module_viewed event.
    $params = array(
        'context' => $context,
        'objectid' => $traxvideo->id
    );
    $eventclass = '\mod_traxvideo\event\\' . $eventname;
    $event = $eventclass::create($params);
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('traxvideo', $traxvideo);
    $event->trigger();

    // Completion.
    if ($eventname == 'course_module_viewed') {
        $completion = new completion_info($course);
        $completion->set_module_viewed($cm);
    }
}

function video_tag($videourl, $posterurl, $type = 'video/mp4')
{
    if (startsWith($videourl, 'https://youtu.be') || startsWith($videourl, 'https://youtube.com')) {
        echo '<video id="xapi-videojs" class="video-js vjs-default-skin vjs-big-play-centered" controls preload="auto" poster="' . $posterurl . '" data-setup=\'{ "fluid": true, "techOrder": ["youtube", "html5"], "sources": [{ "type": "video/youtube", "src": "' . $videourl . '"}] }\'></video>';
    } else if (startsWith($videourl, 'https://mediacenter.univ-reims.fr')) {
        $pattern = '~https://mediacenter\.univ-reims\.fr/videos/\?video=(\w*).*~';
        $videoUri = '';
        if (preg_match($pattern, $videourl, $matches)) {
            $videoUri = 'https://mediacenter.univ-reims.fr/videos/' . $matches[1] . '/multimedia/' . $matches[1] . '.mp4';
        }
        if ($posterurl === "") {
            $posterUri = 'https://mediacenter.univ-reims.fr/videos/' . $matches[1] . '/preview.jpg';
        } else {
            $posterUri = $posterurl;
        }
        echo '<video id="xapi-videojs" class="video-js vjs-default-skin vjs-big-play-centered" controls preload="auto" poster="' . $posterUri . '" data-setup=\'{"fluid": true}\'><source src="' . $videoUri . '" type="' . $type . '"></video>';
    } else {
        echo '<video id="xapi-videojs" class="video-js vjs-default-skin vjs-big-play-centered" controls preload="auto" poster="' .$posterurl . '" data-setup=\'{"fluid": true}\'><source src="' . $videourl . '" type="' . $type . '"></video>';
    }

}


