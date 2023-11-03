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

require_once(__DIR__ . '/../../config.php');

$courseid = required_param('course', PARAM_INT);
$alertid = optional_param('id', 0, PARAM_INT);
$delete = optional_param('delete', false, PARAM_BOOL);
$confirm = optional_param('confirm', false, PARAM_BOOL);

$context = context_course::instance($courseid);
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

require_login($course);
require_capability('block/alertify:addinstance', $context);

$PAGE->set_context($context);
$pagename = get_string('addalert', 'block_alertify');
$title = format_string($course->fullname) . ": $pagename";

$pageurl = new moodle_url('/blocks/alertify/alert.php', array('course' => $courseid));
$PAGE->navbar->add(get_string('pluginname', 'block_alertify'));
$PAGE->navbar->add($pagename, $pageurl);
$PAGE->set_pagelayout('report');
$PAGE->set_url($pageurl);
$PAGE->set_title(get_string('course') . ': ' . $course->fullname);
$PAGE->set_heading($title);

$activities = [];
$modinfo = get_fast_modinfo($course);
$mods = $modinfo->get_cms();

foreach ($mods as $mod) {
    if ($mod->completion) {
        $activities[$mod->id] = $mod->name;
    }
}
asort($activities);

if (empty($activities)) {
    print_error('errornoactivitieswithcompletion', 'block_alertify');
}

$returnurl = new moodle_url('/course/view.php', ['id' => $courseid]);
if ($biid = $DB->get_field('block_instances', 'id', ['blockname' => 'alertify', 'parentcontextid' => $context->id])) {
    $returnurl->param('bui_editid', $biid);
    $returnurl->param('sesskey', sesskey());
}

$alert = new \block_alertify\alert();
$alert->course = $courseid;
$alert->template = get_config('block_alertify', 'defaulttemplateactivity');
if (!empty($alertid)) {
    $alert = \block_alertify\alert::instance($alertid);
}

// Ensure no one is trying to edit an alert that belongs to another course.
if ($courseid != $alert->course) {
    print_error('errorcoursemismatch', 'block_alertify');
}

if ($delete && !empty($alert->id)) {
    if ($confirm) {
        $alert->delete();
        redirect($returnurl);
    }
    echo $OUTPUT->header();
    $url = new moodle_url('/blocks/alertify/alert.php', ['course' => $courseid, 'delete' => 1, 'id' => $alertid, 'confirm' => 1]);

    if (!$DB->record_exists('course_modules', array('course' => $alert->course, 'id' => $alert->cmid))) {
        echo $OUTPUT->confirm(get_string('deleteconfirminvalidalert', 'block_alertify'), $url, $returnurl);
    } else {
        echo $OUTPUT->confirm(get_string('deleteconfirm', 'block_alertify', $activities[$alert->cmid]), $url, $returnurl);
    }

    echo $OUTPUT->footer();
    exit;
}

$data = $alert->data_for_form();
$data->course = $courseid;

$actoptions = $activities;
if (empty($alertid)) {
    $actoptions = \block_alertify\helper::get_cms_without_alert($modinfo, $courseid);
}

$mform = new \block_alertify\form\alert(null,
    array(
        'course' => $course, 'activities' => $actoptions, 'alertid' => $alertid, 'alert' => $alert));
$mform->set_data($data);

if ($mform->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $mform->get_data()) {
    foreach ($data as $key => $value) {
        if (substr($key, 0, 5) == 'alert') {
            $alert->set_alert_time(substr($key, 5), $value);
            continue;
        }
        $alert->$key = $value;
    }
    $alert->save();
    redirect($returnurl);
}

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();
