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

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/report_lib.php');
require_once(__DIR__ . '/classes/output/report_render.php');

global $USER;
global $OUTPUT;
global $PAGE;

$title = get_string('pluginname', 'local_incidence_report');
$pagetitle = $title;
$url = new moodle_url('/local/incidence_report/report.php');

$PAGE->set_context(context_system::instance());
$PAGE->set_url($url);
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_pagelayout('standard');

$PAGE->requires->jquery();
$PAGE->requires->js_call_amd('local_incidence_report/funciones', 'init');

echo $OUTPUT->header();


if (!isloggedin()) {
    echo local_incidence_report_notice('Debes iniciar sesión', LOCAL_INCIDENCE_REPORT_NOTICE_ERROR);
} else {
    $renderer = new local_report_renderer($PAGE, RENDERER_TARGET_GENERAL);

    if (is_siteadmin($USER->id)) {
        echo $renderer->render_admin();
    } else {
        echo local_incidence_report_notice('Debes estar logueado como administrador', LOCAL_INCIDENCE_REPORT_NOTICE_ERROR);
    }
}

echo $OUTPUT->footer();
