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

require_once(dirname(__FILE__).'/../../config.php');

$courseid = required_param('course', PARAM_INT);
$alertid = optional_param('alertid', null, PARAM_INT);
$context = context_course::instance($courseid);
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

require_login($courseid);
require_capability('block/alertify:addinstance', $context);

$PAGE->set_context($context);
$reportname = get_string('reportname', 'block_alertify');
$title = format_string($course->fullname) . ": $reportname";

$pageurl = new moodle_url('/blocks/alertify/index.php', array('course' => $courseid));
if ($alertid) {
    $pageurl->param('alertid', $alertid);
}
$PAGE->navbar->add($reportname, $pageurl);
$PAGE->set_pagelayout('report');
$PAGE->set_url($pageurl);
$PAGE->set_title(get_string('course') . ': ' . $course->fullname);
$PAGE->set_heading($title);

echo $OUTPUT->header();

$renderer = $PAGE->get_renderer('block_alertify');
$report = new \block_alertify\report();
$alertlist = \block_alertify\helper::get_alerts($courseid);
$selected = $alertid;
$modinfo = get_fast_modinfo($courseid);

foreach ($alertlist as $id => $alert) {
    if (!isset($modinfo->cms[$alert->cmid]) && $alert->cmid > 0) {
        unset($alertlist[$id]);
    }
}

if ($alertid && !isset($alertlist[$alertid])) {
    print_error('errorcoursemismatch', 'block_alertify');
}

if (empty($alertid) || $alertlist[$alertid]->alerttype == 'login') {
    list($userdata, $trackdata, $count) = $report->load_login_data();
    $name = get_string('typelogin', 'block_alertify');
    foreach ($alertlist as $alert) {
        if ($alert->alerttype == 'login') {
            $selected = $alert->id;
            $alertid = $selected;
            break;
        }
    }
} else {
    list($userdata, $trackdata, $count) = $report->load_activity_data($alertlist[$alertid]);
    $name = $modinfo->cms[$alertlist[$alertid]->cmid]->name;
}

$sentcount = !empty($alertid) ? \block_alertify\helper::load_alerts_with_track($alertid) : [];
$renderable = new \block_alertify\output\report\renderable($name, $userdata, $count, $report->page,
    $pageurl, $trackdata, $alertlist, $selected, $modinfo, $sentcount);
echo $renderer->render_report($renderable);

echo $OUTPUT->footer();
