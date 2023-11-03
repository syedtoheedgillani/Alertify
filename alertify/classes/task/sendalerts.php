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
 * Send alert to the users.
 *
 * @package    block_Alertify
 * @copyright  2023 Syed Toheed Gillani
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_alertify\task;

use block_alertify\helper;

class sendalerts extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('sendalertstask', 'block_alertify');
    }

    /**
     * Executes alerts by processing pending alert information. This function retrieves pending alerts,
     * preloads course information for those alerts, and processes each alert by invoking the
     * 'process_activity_alert' function for further handling.
     *
     */
    public function execute() {
        global $CFG, $DB;
        require_once("{$CFG->libdir}/completionlib.php");
        $pending = helper::get_pending_alert_info();

        // Preload course info.
        $courseids = array_map(function($alert) {
            return $alert->course;
        }, $pending);

        $courses = $DB->get_records_list('course', 'id', $courseids, '', 'id, shortname, fullname, visible');
        $subject = get_string('subject', 'block_alertify');

        foreach ($pending as $alert) {
            $this->process_activity_alert($alert, $courses, $subject);
        }
    }

    /**
     * Processes an activity alert, generating and sending notifications to users who haven't completed the activity.
     *
     * @param mixed $alert The alert to be processed.
     * @param mixed $courses An array of courses where the activity is located.
     * @param mixed $subject The subject for the notification email.
     *
     */
    protected function process_activity_alert($alert, $courses, $subject) {
        global $DB;
        mtrace("Processing alertify for cmid: {$alert->cmid}");

        if (!$alert->cmid || !$DB->record_exists('course_modules', array('id' => $alert->cmid, 'course' => $alert->course))) {
        mtrace("- Error: could not load an invalid cmid for alert id: {$alert->id}");
        return false;
        }

        $context = \context_module::instance($alert->cmid);
        $cm = get_coursemodule_from_id(null, $alert->cmid, $alert->course);
        if (!$cm) {
            mtrace('- Error: could not load cm record');
            return false;
        }
        $activity = (object)[
            'url' => new \moodle_url("/mod/$cm->modname/view.php", ['id' => $alert->cmid]),
            'name' => $cm->name,
        ];

        // Get users who haven't completed the activity.
        list($esql, $params) = get_enrolled_sql($context, '', 0, true);

        $sql = "SELECT u.*
                FROM {user} u
                JOIN ($esql) eu ON eu.id = u.id
                WHERE u.id NOT IN (
                    SELECT userid
                    FROM {course_modules_completion}
                    WHERE coursemoduleid = :cmid AND completionstate = 1
                )";
        $params['cmid'] = $alert->cmid;
        $users = $DB->get_records_sql($sql, $params);

        $users = helper::filter_by_students($users, $alert, $context);

        foreach ($users as $user) {
            // Course vis check.
            if (!$courses[$alert->course]->visible && !has_capability('moodle/course:viewhiddencourses', $context, $user)) {
                continue;
            }
            // CM vis check.
            if (empty($cm->visible) && !has_capability('moodle/course:viewhiddenactivities', $context, $user)) {
                continue;
            }
            list($text, $html) = helper::parse_template($alert->template, $courses[$alert->course], $user, $activity);

            email_to_user($user, \core_user::get_support_user(), $subject, $text, $html);
        }

        $alert->set_sent_and_save();
        return true;
    }
}
