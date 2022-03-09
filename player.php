<?php

require_once('../../config.php');
require_once($CFG->dirroot . '/mod/traxvideo/locallib.php');

use logstore_trax\src\controller as trax_controller;


// Params.
$id = required_param('id', PARAM_INT);
$videourl = required_param('url', PARAM_URL);
$poster = required_param('poster', PARAM_URL);

// Objects.
$cm = get_coursemodule_from_id('traxvideo', $id, 0, false, MUST_EXIST);
$course = $DB->get_record("course", array('id' => $cm->course), '*', MUST_EXIST);
$activity = $DB->get_record("traxvideo", array('id' => $cm->instance), '*', MUST_EXIST);

// Permissions.
require_course_login($course, false, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/traxvideo:view', $context);

// Events.
traxvideo_tigger_module_event('course_module_viewed', $activity, $course, $cm, $context);

// Page setup.
$pageurl = new moodle_url('/mod/traxvideo/player.php', array('url' => $videourl));
$PAGE->set_url($pageurl);

?>
<head>
<link rel="stylesheet" type="text/css" href="/mod/traxvideo/players/xapi-videojs/video-js-7.17.0/video-js.css">
<script type="text/javascript" charset="utf-8" src="/mod/traxvideo/players/xapi-videojs/video-js-7.17.0/video.js"></script>
<script type="text/javascript" charset="utf-8" src="/mod/traxvideo/players/xapi-videojs/videojs-youtube-2.6.1/Youtube.min.js"></script>
<script type="text/javascript" charset="utf-8" src="/mod/traxvideo/players/xapi-videojs/xAPIWrapper-1.11.0/dist/xapiwrapper.min.js"></script>
<script type="text/javascript" charset="utf-8" src="/mod/traxvideo/players/xapi-videojs/xAPIWrapper-1.11.0/dist/xapiwrapper.min.js.map"></script>
<script type="text/javascript" charset="utf-8" src="/mod/traxvideo/players/xapi-videojs/xAPIWrapper-1.11.0/lib/cryptojs_v3.1.2.js"></script>
<script type="text/javascript" charset="utf-8" src="/mod/traxvideo/players/xapi-videojs/xAPIWrapper-1.11.0/lib/utf8-text-encoding.js"></script>
<script type="text/javascript" charset="utf-8" src="/mod/traxvideo/players/xapi-videojs/xapi-videojs.js"></script>
</head>
<?php

// Content header.
$title = format_string($activity->name);
$PAGE->set_title($title);

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

<div class="wrapper">
    <div class="videocontent">
        <?php
        video_tag($videourl,$poster);
        ?>
    </div>
</div>

<script type="text/javascript">

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
