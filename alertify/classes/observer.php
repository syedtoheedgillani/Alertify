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

defined('MOODLE_INTERNAL') || die();

class block_alertify_observer {

    /**
     * Delete the alerts and notifications sent
     *
     * @param \core\event\base $event
     */
    public static function delete(\core\event\base $event) {
        global $DB;

        if ($event->eventname == '\core\event\course_module_deleted') {
            if ($DB->get_records('block_alertify', array('cmid' => $event->objectid))){

                $DB->delete_records('block_alertify', array('cmid' => $event->objectid));
            }
        }
    }
}
