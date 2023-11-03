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

namespace block_alertify;

class helper {

    /**
     * Loads alert data with tracking information for a specific alert ID.
     *
     * This static function is used to load alert data along with its tracking information for a given alert ID. It
     * initializes an empty array to store the alert data and creates an instance of the 'alert' class for the specified
     * alert ID. The function should be extended to retrieve and populate the 'alertdata' array with relevant data.
     *
     * @param int $alertid The unique identifier of the alert for which data is to be loaded.
     *
     * @return array An array containing alert data and tracking information.
     */
    public static function load_alerts_with_track($alertid) {
        global $DB;

        $alertdata = [];
        $alert = alert::instance($alertid);

        return $alertdata;
    }

    /**
     * Retrieves alerts associated with a specific course, optionally filtered by enabled status.
     *
     * This static function is used to retrieve alerts associated with a particular course, and it can optionally filter
     * the results based on whether the alerts are enabled or not. It queries the 'block_alertify' table in the database,
     * retrieves the records, and constructs 'alert' objects for each retrieved record, organizing them into an array.
     *
     * @param int $courseid The unique identifier of the course for which alerts are to be retrieved.
     * @param bool $enabledonly (optional) Whether to filter alerts to include only enabled ones (default is false).
     *
     * @return array An array of 'alert' objects keyed by their unique identifiers.
     */
    public static function get_alerts($courseid, $enabledonly = false) {
        global $DB;

        $params = ['course' => $courseid];
        if ($enabledonly) {
            $params['enabled'] = 1;
        }
        $records = $DB->get_records('block_alertify', $params);
        $alerts = [];
        foreach ($records as $record) {
            $alerts[$record->id] = new alert($record);
        }
        return $alerts;
    }

    /**
     * Retrieves pending alert information for today's date.
     *
     * This static function is used to retrieve pending alerts that are enabled and scheduled for the current date.
     * It queries the 'block_alertify' table in the database to find records that match the criteria, constructs 'alert'
     * objects for each retrieved record, and organizes them into an array.
     *
     * @return array An array of 'alert' objects representing pending alerts scheduled for the current date.
     */
    public static function get_pending_alert_info() {
        global $DB;

        $sql = "SELECT *
                FROM {block_alertify}
                WHERE enabled = 1
                      AND DATE(FROM_UNIXTIME(alertcreated)) = CURDATE()"; // Check if the records are of today.
        $params = [];

        $records = $DB->get_records_sql($sql, $params);

        $alerts = [];
        foreach ($records as $record) {
            $alerts[$record->id] = new alert($record);
        }
        return $alerts;
    }

    /**
     * Parses a template, replacing placeholders with actual values and generating text and HTML versions.
     *
     * This static function is used to parse a template, replacing placeholders with actual values, and generating both
     * text and HTML versions of the content. Placeholders within the template are replaced with course and activity
     * information, as well as user details, resulting in text and HTML content for notifications.

    * @param string $template The template with placeholders to be parsed.
    * @param object $course The course object containing course information.
    * @param object $user The user object containing user information.
    * @param object|null $activity (optional) The activity object containing activity information.
    *
    * @return array An array containing the parsed text and HTML versions of the template.
    */
    public static function parse_template($template, $course, $user, $activity = null) {
        $activityurl = isset($activity->url) ? $activity->url : '';
        $activityname = isset($activity->name) ? $activity->name : '';
        $courseurl = new \moodle_url('/course/view.php', array('id' => $course->id));

        $html = str_replace('{clink}', \html_writer::link($courseurl, $course->fullname), $template);
        $html = str_replace('{alink}', \html_writer::link($activityurl, $activityname), $html);
        $html = str_replace('{cfull}', format_string($course->fullname), $html);
        $html = str_replace('{cshort}', format_string($course->shortname), $html);
        $html = str_replace('{userfullname}', fullname($user), $html);
        $html = nl2br($html);

        $text = html_to_text($html);

        return array($text, $html);
    }

    /**
     * Retrieves course modules without associated alerts.
     *
     * This static function is used to retrieve course modules (CMs) from a course that do not have associated alerts
     * in the 'block_alertify' table. It compares the course modules from the provided 'modinfo' with the existing
     * alerts for the specified course and returns an array of CMs that have completion tracking enabled but do not
     * have an associated alert.
     *
     * @param object $modinfo The course module information.
     * @param int $courseid The unique identifier of the course.
     *
     * @return array An array of course modules without associated alerts, where keys are CM IDs and values are CM names.
     */
    public static function get_cms_without_alert($modinfo, $courseid) {
        global $DB;
        $existing = $DB->get_records('block_alertify', ['course' => $courseid], '', 'cmid');

        $options = [];
        foreach ($modinfo->cms as $cmid => $data) {
            if ($data->completion && !isset($existing[$cmid])) {
                $options[$cmid] = $data->name;
            }
        }
        return $options;
    }

    /**
     * Filters and excludes certain users based on capabilities and context.
     *
     * This static function is used to filter a list of users, removing those who have a specific capability
     * in a given context. In this case, it filters users based on their capability to view hidden courses in the
     * specified context. Users with this capability are excluded from the list of users.
     *
     * @param array $users An array of users to be filtered.
     * @param object $alert The alert object for context information.
     * @param object $context The context in which the filtering is applied.
     *
     * @return array An array of users with specific capabilities filtered out.
     */
    public static function filter_by_students($users, $alert, $context) {
        if (!empty($users)) {
            $userids = get_users_by_capability($context, 'moodle/course:viewhiddencourses', 'u.id');

            $users = array_udiff($users, $userids,
                function ($a, $b) {
                    return $a->id - $b->id;
                }
            );
        }
        return $users;
    }
}
