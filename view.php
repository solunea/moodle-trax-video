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

require_once('../../config.php');
require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once('./lib.php');
require_once($CFG->dirroot . '/mod/traxvideo/locallib.php');

use logstore_trax\src\controller as trax_controller;

// Params.
$id = required_param('id', PARAM_INT);

// Objects.
$cm = get_coursemodule_from_id('traxvideo', $id, 0, false, MUST_EXIST);
$course = $DB->get_record("course", array('id' => $cm->course), '*', MUST_EXIST);
$activity = $DB->get_record("traxvideo", array('id' => $cm->instance), '*', MUST_EXIST);

// Permissions.
require_course_login($course, false, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/traxvideo:view', $context);

// Events.
if ($activity->display != TraxVideoConfig::TRAXLIB_DISPLAY_POPUP && $activity->display != TraxVideoConfig::TRAXLIB_DISPLAY_NEW && $activity->display != TraxVideoConfig::TRAXLIB_DISPLAY_OPEN) {
    traxvideo_tigger_module_event('course_module_viewed', $activity, $course, $cm, $context);
}

// Page setup.
$url = new moodle_url('/mod/traxvideo/view.php', array('id' => $id));
$PAGE->set_url($url);

// External file.
$PAGE->requires->css(new moodle_url($CFG->wwwroot . '/mod/traxvideo/players/xapi-videojs/video-js-7.17.0/video-js.css'));
$PAGE->requires->js(new moodle_url($CFG->wwwroot . '/mod/traxvideo/players/xapi-videojs/video-js-7.17.0/video.js'), true);
$PAGE->requires->js(new moodle_url($CFG->wwwroot . '/mod/traxvideo/players/xapi-videojs/videojs-youtube-2.6.1/Youtube.min.js'), true);
$PAGE->requires->js(new moodle_url($CFG->wwwroot . '/mod/traxvideo/players/xapi-videojs/xAPIWrapper-1.11.0/dist/xapiwrapper.min.js'), true);
$PAGE->requires->js(new moodle_url($CFG->wwwroot . '/mod/traxvideo/players/xapi-videojs/xAPIWrapper-1.11.0/dist/xapiwrapper.min.js.map'), true);
$PAGE->requires->js(new moodle_url($CFG->wwwroot . '/mod/traxvideo/players/xapi-videojs/xAPIWrapper-1.11.0/lib/cryptojs_v3.1.2.js'), true);
$PAGE->requires->js(new moodle_url($CFG->wwwroot . '/mod/traxvideo/players/xapi-videojs/xAPIWrapper-1.11.0/lib/utf8-text-encoding.js'), true);
$PAGE->requires->js(new moodle_url($CFG->wwwroot . '/mod/traxvideo/players/xapi-videojs/xapi-videojs.js'), true);

// Content header.
$title = format_string($activity->name);
$PAGE->set_title($title);
$PAGE->set_heading($course->fullname);
echo $OUTPUT->header();
echo $OUTPUT->heading($title);

// Front data.
$controller = new trax_controller();
$parentactivity = $controller->activities->get('traxvideo', $activity->id, false);
$activityid = $parentactivity['id'] . '/video';

$front = (object)[
    'endpoint' => $CFG->wwwroot . '/admin/tool/log/store/trax/proxy/',
    'username' => '',
    'password' => '',
    'actor' => '{"mbox": "mailto:' . $activity->id . '@traxvideo.mod"}',
    'activityid' => $activityid,
    'activityname' => $title,
    'video' => [
        'video/mp4' => $activity->sourcemp4,
    ],
    'poster' => $activity->poster,
];
?>

<?php
function startsWith($haystack, $needle)
{
    $length = strlen($needle);
    return substr($haystack, 0, $length) === $needle;
}

?>
    <div id="terminate_video_form" class="form-group clearfix fitem femptylabel" style="visibility: hidden">
        <div class="float-right form-inline align-items-start felement" data-fieldtype="submit">
            <input type="submit" class="btn btn-primary " name="all" id="id_all" onclick="terminateVideo();"
                   value="<?php echo get_string('terminateVideo', 'traxvideo'); ?>">
        </div>
    </div>

    <div class="wrapper">
        <div class="videocontent">
            <?php
            $playerurl = "./player.php?id=" . $id . "&url=" . urlencode($activity->sourcemp4) . "&poster=" . urlencode($activity->poster);
            if ($activity->display == TraxVideoConfig::TRAXLIB_DISPLAY_POPUP) {
                $options = empty($resource->displayoptions) ? array() : unserialize($resource->displayoptions);
                $width = empty($options['popupwidth']) ? 620 : $options['popupwidth'];
                $height = empty($options['popupheight']) ? 450 : $options['popupheight']; ?>
                <div><?= get_string('click', 'traxvideo') ?> <a href="<?php echo $playerurl ?>"
                                                                onclick="window.open('<?= $playerurl ?>', '', '<?= "width=" . $width . ",height=" . $height ?>,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes'); return false;"><?= $title ?></a> <?= get_string('videolink', 'traxvideo') ?>
                </div>
                <?php
            } else if ($activity->display == TraxVideoConfig::TRAXLIB_DISPLAY_OPEN) {
                ?>
                <div><?= get_string('click', 'traxvideo') ?> <a
                            href="<?= $playerurl ?>"><?= $title ?></a> <?= get_string('videolink', 'traxvideo') ?></div>
                <?php
            } else if ($activity->display == TraxVideoConfig::TRAXLIB_DISPLAY_NEW) {
                ?>
                <div><?= get_string('click', 'traxvideo') ?> <a href="<?= $playerurl ?>"
                                                                target="_blank"><?= $title ?></a> <?= get_string('videolink', 'traxvideo') ?>
                </div>
                <?php
            } else {
                video_tag($activity->sourcemp4, $activity->poster);
            }
            ?>
        </div>
        <div class="videoMessage" id="videoMessage"></div>
    </div>

    <script type="text/javascript">
        const videoIsTerminated = "<?php echo get_string('videoIsTerminated', 'traxvideo'); ?>";

        ADL.XAPIWrapper.log.debug = false;
        if (ADL.XAPIWrapper.lrs.actor === undefined) {
            var conf = {
                "endpoint": "<?php echo $front->endpoint ?>",
                "auth": "Basic " + toBase64('<?php echo $front->username ?>:<?php echo $front->password ?>'),
                "actor": '<?php echo $front->actor ?>'
            };
            ADL.XAPIWrapper.changeConfig(conf);
        }
        var activityIri = "<?php echo $front->activityid ?>";
        var activityTitle = "<?php echo $front->activityname ?>";
        var activityDesc = '';
        ADL.XAPIVideoJS("xapi-videojs");

    </script>

<?php
// Content close.
echo $OUTPUT->footer();

