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

$string['activity'] = 'Activity';
$string['alerttemplate'] = 'Email template';
$string['alerttemplate_help'] = 'The following list of keywords can be used in your template:

{alink} : link to the activity (displays as the activity name)<br />
{clink} : link to the course (displays as the course fullname)<br />
{cfull} : the fullname of the course<br />
{cshort} : the shortname of the course<br />
{userfullname} : the user\'s fullname';

$string['addalert'] = 'Add alert';
$string['alert'] = 'Alert';
$string['configure'] = 'Configure';
$string['configtemplateactivity'] = 'The default template used for new activty alerts.';
$string['configtemplateactivitydefault'] ='Hello {userfullname},

Our records indicate that you haven\'t completed the activity \'{alink}\' in {clink}.

Can you please action this at your earliest.

Thank you.
Security Officer Team';
$string['deleteconfirm'] = 'Are you sure you want to delete the alert for activity "{$a}"?';
$string['deleteconfirminvalidalert'] = 'Are you sure you want to delete this invalid alert?';
$string['enabled'] = 'Enabled';
$string['errorcoursecompletiondisabled'] = 'Course completion is currently disabled in this course, so no activity alerts will be triggered.';
$string['errorcoursemismatch'] = 'The alert you\'re trying to alter doesn\'t belong to this course.';
$string['errornoactivitieswithcompletion'] = 'Your course doesn\'t have any activities with completion criteria setup yet.';
$string['noalerts'] = 'No alerts have been configured.';
$string['nomoreactivities'] = 'No more activities can be configured with alerts. Either no activities have completion enabled, or all the ones that do have alerts setup already.';
$string['alertify:addinstance'] = 'Add a new inactive user alert block';

$string['pluginname'] = 'Alertify';
$string['sendalertstask'] = 'Send Alertify Email';
$string['subject'] = 'Course not completed yet';
$string['templateactivity'] = 'Default activity alert';
$string['invalidalert'] = 'Invalid alert';
$string['typeactivity'] = 'Activity alert';