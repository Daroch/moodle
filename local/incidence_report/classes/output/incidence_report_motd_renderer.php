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
//


/**
 * @package   local_incidence_report
 * @copyright 2020, PLANIFICACIÓN DE ENTORNOS TECNOLÓGICOS, S.L. <admon@pentec.es>
 */
 
defined('MOODLE_INTERNAL') || die;

require_once(__DIR__ . '/../../lib.php');
require_once(__DIR__ . '/incidence_report_form.php');

class local_incidence_report_motd_renderer extends \plugin_renderer_base {

    public function render_motd_editor () {
        $html = '';
        if (LOCAL_INCIDENCE_REPORT_ENABLE_MOTD === false) {
            return $html;
        }

        global $DB;
        ob_start();

        $motd = new stdClass();
        $motd->motdcontent = get_config('local_incidence_report', 'motdcontent');
        $motd->motdtype = get_config('local_incidence_report', 'motdtype');

        $mform = new local_incidence_report_motd_editor_form();

        if ($mform->is_cancelled()) {
            // No hacemos nada
        } else if ($fromform = $mform->get_data()) {
            $mform->save_data();
            $mform->display();
        } else {
            $mform->display();
        }

        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

    public function render_motd_viewer () {
        $html = '';
        if (LOCAL_INCIDENCE_REPORT_ENABLE_MOTD === false) {
            return $html;
        }

        global $DB;
        ob_start();

        $motd = new stdClass();
        $motd->motdcontent = get_config('local_incidence_report', 'motdcontent');
        $motd->motdtype = get_config('local_incidence_report', 'motdtype');

        if ($motd->motdtype != null) {
            echo local_incidence_report_notice($motd->motdcontent, $motd->motdtype);
        }

        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

}
