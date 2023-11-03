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
 * Block rendrer
 *
 * @package    block_Alertify
 * @copyright  2023 Syed Toheed Gillani
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

class block_alertify_renderer extends plugin_renderer_base {
    /**
     * Render the main block display.
     *
     * @param \block_alertify\output\overview\renderable $overview
     * @return string
     */
    public function alerts(array $alerts) {
        $o = '';
        foreach ($alerts as $alert) {
            if (!empty($o)) {
                $o .= html_writer::empty_tag('br');
            }
            $o .= $this->render_alert($alert);
        }

        if (empty($alerts)) {
            $o .= html_writer::tag('p', get_string('noalerts', 'block_alertify'));
        }
        return $o;
    }

    protected function alert_list($list, $selected, $modinfo) {
        $o = html_writer::start_tag('ul');
        foreach ($list as $alert) {
            $url = $this->page->url;
            $url->param('alertid', $alert->id);
            $name = get_string('typelogin', 'block_alertify');
            if ($alert->alerttype == 'activity') {
                $name = $modinfo->cms[$alert->cmid]->name;
            }
            $link = html_writer::link($url, $name);
            $o .= html_writer::tag('li', $link);
        }
        $o .= html_writer::end_tag('ul');
        return $o;
    }

    public function render_alert(\block_alertify\output\alert\renderable $overview) {
        $state = $overview->enabled ? 'yes' : 'no';
        $enabledstr = html_writer::span(get_string($state), "enabled-$state");
        $enabled = html_writer::span(get_string('enabled', 'block_alertify').": $enabledstr", 'alerthead');

        $name = html_writer::span($overview->name, 'bold');
        $brtag = html_writer::empty_tag('br');
        $o = html_writer::span($name.$brtag.$enabled);
        $o .= $brtag;

        $alertstr = get_string('alert', 'block_alertify');
        $format = get_string('strftimedate', 'langconfig');
        $alerts = array();
        $num = 1;
        foreach ($overview->alerts as $alert) {
            $alerttime = userdate($alert[0], $format);
            $alertstat = '';
            if ($alert[0] <= time()) {
                $alertstat = '('.get_string('numbersent', 'block_alertify', $alert[1]).')';
            }
            $head = html_writer::span("{$alertstr} {$num}:", 'alerthead');
            if ($alert[0] > 0) {
                $alerts[] = "$head $alerttime $alertstat";
            }
            $num++;
        }
        $o .= html_writer::tag('span', implode($brtag, $alerts));
        $o .= $brtag;
        return $o;
    }

}
