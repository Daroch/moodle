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
require_once(__DIR__ . '/lib.php');
require_once(__DIR__ . '/classes/output/incidence_report_renderer.php');

global $USER;
global $OUTPUT;
global $CFG;
global $DB;
global $PAGE;

$title = get_string('pluginname', 'local_incidence_report');
$pagetitle = $title;
$url = new moodle_url("/local/incidence_report/user_debug.php");

$PAGE->set_context(context_system::instance());
$PAGE->set_url($url);
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_pagelayout('standard');

echo $OUTPUT->header();

//$PAGE->requires->js_call_amd('local_incidence_report/incidence_report_javascript', 'init');

if (!local_incidence_report_is_user_allowed($USER)) {
    echo local_incidence_report_notice(get_string('forbidden_access', 'local_incidence_report'), LOCAL_INCIDENCE_REPORT_NOTICE_ERROR);
} else {
    if (!isloggedin()) {
        echo local_incidence_report_notice(get_string('forbidden_access', 'local_incidence_report'), LOCAL_INCIDENCE_REPORT_NOTICE_ERROR);
    } else {

        //$DB->set_debug(true);
        $isalumno = local_incidence_report_profile_check(LOCAL_INCIDENCE_REPORT_PROFILE_ALUMNO);
        $istutor = local_incidence_report_profile_check(LOCAL_INCIDENCE_REPORT_PROFILE_TUTOR);
        $isprofesor = local_incidence_report_profile_check(LOCAL_INCIDENCE_REPORT_PROFILE_PROFESOR);
        $iscoordinador = local_incidence_report_profile_check(LOCAL_INCIDENCE_REPORT_PROFILE_COORDINADOR);
        $isjefatura = local_incidence_report_profile_check(LOCAL_INCIDENCE_REPORT_PROFILE_JEFATURA);
        $issecretaria = local_incidence_report_profile_check(LOCAL_INCIDENCE_REPORT_PROFILE_SECRETARIA);
        $ismoodle = local_incidence_report_profile_check(LOCAL_INCIDENCE_REPORT_PROFILE_MOODLE);
        if (is_siteadmin()) {
            $ismoodle = true;
        }
        $isfederacion = local_incidence_report_profile_check(LOCAL_INCIDENCE_REPORT_PROFILE_FEDERACION);

        $courseid = optional_param('courseid', 0, PARAM_INT);
        if ($courseid == 0) {
            $courseid = optional_param('fcourse', 0, PARAM_INT);
        }

        $isobserver = local_incidence_report_allow_observer($USER->id, $courseid);
        //$DB->set_debug(false);

        $course_options = null;
        $global_options = null;

        $options = [];
        // ALUMNO
        if ($isalumno) {
            //$options[LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_TAREAS] = get_string('submit_type_string_' . LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_TAREAS, 'local_incidence_report');
            //$options[LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_ACCESO] = get_string('submit_type_string_' . LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_ACCESO, 'local_incidence_report');
            //$options[LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_ENTREGAS] = get_string('submit_type_string_' . LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_ENTREGAS, 'local_incidence_report');
            //$options[LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_CALIFICACIONES] = get_string('submit_type_string_' . LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_CALIFICACIONES, 'local_incidence_report');
            $options[LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_SECCION] = get_string('submit_type_string_' . LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_SECCION, 'local_incidence_report');
            $options[LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_ENTREGA] = get_string('submit_type_string_' . LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_ENTREGA, 'local_incidence_report');
        }
        // TUTOR
        if ($istutor) {
            //$options[LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_PLAGIO] = get_string('submit_type_string_' . LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_PLAGIO, 'local_incidence_report');
            //$options[LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_EXAMEN_LUGAR] = get_string('submit_type_string_' . LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_EXAMEN_LUGAR, 'local_incidence_report');
            //$options[LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_ACTAS] = get_string('submit_type_string_' . LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_ACTAS, 'local_incidence_report');
            $options[LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_CORRECCION] = get_string('submit_type_string_' . LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_CORRECCION, 'local_incidence_report');
            $options[LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_CALIFICADOR] = get_string('submit_type_string_' . LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_CALIFICADOR, 'local_incidence_report');
        }
        // Solicitudes asignadas a múltiples perfiles
        if ($istutor || $iscoordinador) {
            //$options[LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_CONVOCATORIA_JE] = get_string('submit_type_string_' . LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_CONVOCATORIA_JE, 'local_incidence_report');
            //$options[LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_EXAMEN_RECOGIDA] = get_string('submit_type_string_' . LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_EXAMEN_RECOGIDA, 'local_incidence_report');
            $options[LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_FALLO] = get_string('submit_type_string_' . LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_FALLO, 'local_incidence_report');
            $options[LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_MATRICULA] = get_string('submit_type_string_' . LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_MATRICULA, 'local_incidence_report');
            $options[LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_RECURSO] = get_string('submit_type_string_' . LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_RECURSO, 'local_incidence_report');
            $options[LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_CONTENIDOS] = get_string('submit_type_string_' . LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_CONTENIDOS, 'local_incidence_report');
        }
        if ($isalumno || $istutor || $iscoordinador) {
            //$options[LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_CALIFICACION] = get_string('submit_type_string_' . LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_CALIFICACION, 'local_incidence_report');
            //$options[LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_EXAMEN_FECHA] = get_string('submit_type_string_' . LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_EXAMEN_FECHA, 'local_incidence_report');
        }
        ksort($options);
        $course_options = $options;

        $options = [];
        // ALUMNO
        if ($isalumno) {
            $options[LOCAL_INCIDENCE_REPORT_TYPE_SECRETARIA_EXPEDIENTE] = get_string('submit_type_string_' . LOCAL_INCIDENCE_REPORT_TYPE_SECRETARIA_EXPEDIENTE, 'local_incidence_report');
            $options[LOCAL_INCIDENCE_REPORT_TYPE_SECRETARIA_CONVOCATORIA] = get_string('submit_type_string_' . LOCAL_INCIDENCE_REPORT_TYPE_SECRETARIA_CONVOCATORIA, 'local_incidence_report');
            $options[LOCAL_INCIDENCE_REPORT_TYPE_SECRETARIA_BAJA] = get_string('submit_type_string_' . LOCAL_INCIDENCE_REPORT_TYPE_SECRETARIA_BAJA, 'local_incidence_report');
            $options[LOCAL_INCIDENCE_REPORT_TYPE_SECRETARIA_CERTIFICADOS] = get_string('submit_type_string_' . LOCAL_INCIDENCE_REPORT_TYPE_SECRETARIA_CERTIFICADOS, 'local_incidence_report');
            $options[LOCAL_INCIDENCE_REPORT_TYPE_PLATAFORMA_CURSO] = get_string('submit_type_string_' . LOCAL_INCIDENCE_REPORT_TYPE_PLATAFORMA_CURSO, 'local_incidence_report');
            //$options[LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_SECCION] = get_string('submit_type_string_' . LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_SECCION, 'local_incidence_report');
            //$options[LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_ENTREGA] = get_string('submit_type_string_' . LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_ENTREGA, 'local_incidence_report');
        }
        // COORDINADOR
        if ($iscoordinador) {
            $options[LOCAL_INCIDENCE_REPORT_TYPE_SECRETARIA_COMISIONES] = get_string('submit_type_string_' . LOCAL_INCIDENCE_REPORT_TYPE_SECRETARIA_COMISIONES, 'local_incidence_report');
        }
        // FEDERACION
        if ($isfederacion) {
            // $options[LOCAL_INCIDENCE_REPORT_TYPE_SECRETARIA_MATRICULA] = get_string('submit_type_string_' . LOCAL_INCIDENCE_REPORT_TYPE_SECRETARIA_MATRICULA, 'local_incidence_report');
        }
        // TUTOR
        if ($istutor) {
            //$options[LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_CORRECCION] = get_string('submit_type_string_' . LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_CORRECCION, 'local_incidence_report');
            //$options[LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_CALIFICADOR] = get_string('submit_type_string_' . LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_CALIFICADOR, 'local_incidence_report');
        }
        // Solicitudes asignadas a múltiples perfiles
        if ($istutor || $isprofesor) {
            $options[LOCAL_INCIDENCE_REPORT_TYPE_SECRETARIA_ACTAS] = get_string('submit_type_string_' . LOCAL_INCIDENCE_REPORT_TYPE_SECRETARIA_ACTAS, 'local_incidence_report');
        }
        if ($istutor || $iscoordinador) {
            //$options[LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_FALLO] = get_string('submit_type_string_' . LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_FALLO, 'local_incidence_report');
            //$options[LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_MATRICULA] = get_string('submit_type_string_' . LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_MATRICULA, 'local_incidence_report');
            //$options[LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_RECURSO] = get_string('submit_type_string_' . LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_RECURSO, 'local_incidence_report');
            //$options[LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_CONTENIDOS] = get_string('submit_type_string_' . LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_CONTENIDOS, 'local_incidence_report');
        }
        ksort($options);
        $global_options = $options;
    }

    echo '<h1>Comprobando usuario conectado...</h1>';
    echo '<h2>Perfiles</h2>';
    echo 'Perfil "alumno": ' . (($isalumno) ? 'si' : 'no') . '<br>';
    echo 'Perfil "tutor": ' . (($istutor) ? 'si' : 'no') . '<br>';
    echo 'Perfil "profesor": ' . (($isprofesor) ? 'si' : 'no') . '<br>';
    echo 'Perfil "coordinador": ' . (($iscoordinador) ? 'si' : 'no') . '<br>';
    echo 'Perfil "jefatura": ' . (($isjefatura) ? 'si' : 'no') . '<br>';
    echo 'Perfil "secretaria": ' . (($issecretaria) ? 'si' : 'no') . '<br>';
    echo 'Perfil "moodle": ' . (($ismoodle) ? 'si' : 'no') . '<br>';
    echo 'Perfil "federacion": ' . (($isfederacion) ? 'si' : 'no') . '<br>';
    echo 'Perfil "observador" en ' . $courseid . ': ' . (($isobserver) ? 'si' : 'no') . '<br>';
    echo 'Puede gestionar: ' . (local_incidence_report_allow_management($USER->id) ? 'si' : 'no') . '<br>';
    echo '<h2>Incidencias GLOBALES reportables:</h2>';
    echo '<ul>';
    foreach ($global_options as $option) {
        echo '<li>' . $option;
    }
    echo '</ul>';
    echo '<h2>Incidencias DE CURSO reportables:</h2>';
    echo '<ul>';
    foreach ($course_options as $option) {
        echo '<li>' . $option;
    }
    echo '</ul>';

    echo '<h2>Gestion del curso indicado:</h2>';
    echo '<pre>';
    //$DB->set_debug(true);
    var_dump(local_incidence_report_allow_management($USER->id));
    //$DB->set_debug(false);
    echo '</pre>';

    echo '<h2>Detalle asignaciones:</h2>';
    echo '<pre>';
    $sql = 'SELECT ra.id AS id,
                   ra.roleid AS ra_roleid,
                   ra.contextid AS ra_contextid,
                   ra.userid AS ra_userid,
                   ra.timemodified AS ra_timemodified,
                   ra.modifierid AS ra_modifierid,
                   ra.component AS ra_component,
                   ra.itemid AS ra_itemid,
                   ra.sortorder AS ra_sortorder,
                   ctx.id AS ctx_id,
                   ctx.contextlevel AS ctx_contextlevel,
                   ctx.instanceid AS ctx_instanceid,
                   ctx.path AS ctx_path,
                   ctx.depth AS ctx_depth,
                   ctx.locked AS ctx_locked
              FROM mdl_role_assignments AS ra
              JOIN mdl_context AS ctx ON (ctx.id = ra.contextid)
             WHERE userid=:userid';
    $params = ['userid' => $USER->id];
    $result = $DB->get_records_sql($sql, $params);
    var_dump($result);
    echo '</pre>';
}

$incidenceid = optional_param('incidenceid', 0, PARAM_RAW);
$profile = optional_param('profile', 0, PARAM_RAW);
//$context = optional_param('context', 0, PARAM_RAW);
//$instanceid = optional_param('instanceid', 0, PARAM_RAW);
$incidence = $DB->get_record('local_incidence_report_msgs', array('id' => $incidenceid));
if ($profile) {
    echo '<h1>Avisar al cerrar...</h1>';
    local_incidence_report_email_get_additional_users_for_incidence($incidence, [$profile], true);
    //local_incidence_report_profile_get($profile, $context, $instanceid, true);
}

echo $OUTPUT->footer();
