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

define('AJAX_SCRIPT', true);

require_once('../../config.php');
require_once('lib.php');

require_login();
//require_sesskey();
$action = required_param('action', PARAM_ACTION);

global $USER, $DB;

switch ($action) {

    case 'getCurso':

        $gerencia = required_param('gerencia', PARAM_INT);
        $get_course = $DB->get_records('course', array('category' => $gerencia));

        $array_course = array();
        foreach ($get_course as $course) {
            $array_course[] = array("id" => $course->id, 'name' => $course->fullname);
        }

        echo json_encode($array_course);

        break;

    case 'getGestor':
        // Opción 1 - No recibimos ningun parámetro.
        // Opción 2 - Recibimos los parámetros vía URL (carga desde historial).
        // Opción 3 - Recibimos parámetors a partir de la petición AJAX.
        $categoryid = optional_param('gerencia', 0, PARAM_INT);
        $courseid = optional_param('courseid', 0, PARAM_INT);

        $managers = [];

        // Dependientes de curso:
        // LOCAL_INCIDENCE_REPORT_PROFILE_ROLES_ALUMNO <-- NO gestiona
        // LOCAL_INCIDENCE_REPORT_PROFILE_ROLES_PROFESOR <-- NO gestiona
        // LOCAL_INCIDENCE_REPORT_PROFILE_ROLES_COORDINADOR <-- NO gestiona
        // LOCAL_INCIDENCE_REPORT_PROFILE_ROLES_TUTOR"

        if ($categoryid != 0) {
            if ($courseid != 0) {
                $tutor = local_incidence_report_profile_get(LOCAL_INCIDENCE_REPORT_PROFILE_TUTOR, CONTEXT_COURSE, $courseid);
                foreach ($tutor as $user) {
                    $managers[] = ['id' => $user->id, 'firstname' => '[TUTOR] ' . $user->firstname . ' ' . $user->lastname];
                }
            } else {
                $courses = $DB->get_records('course', ['category' => $categoryid]);
                foreach ($courses as $course) {
                    $tutor = local_incidence_report_profile_get(LOCAL_INCIDENCE_REPORT_PROFILE_TUTOR, CONTEXT_COURSE, $course->id);
                    foreach ($tutor as $user) {
                        $managers[] = ['id' => $user->id, 'firstname' => '[TUTOR] ' . $user->firstname . ' ' . $user->lastname];
                    }
                }
            }
        }

        // Independientes de curso:
        //LOCAL_INCIDENCE_REPORT_PROFILE_ROLES_JEFATURA
        $jefatura = local_incidence_report_profile_get(LOCAL_INCIDENCE_REPORT_PROFILE_SECRETARIA);
        foreach ($jefatura as $user) {
            $managers[] = ['id' => $user->id, 'firstname' => '[JEFATURA] ' . $user->firstname . ' ' . $user->lastname];
        }
        //LOCAL_INCIDENCE_REPORT_PROFILE_ROLES_SECRETARIA
        $secretaria = local_incidence_report_profile_get(LOCAL_INCIDENCE_REPORT_PROFILE_JEFATURA);
        foreach ($secretaria as $user) {
            $managers[] = ['id' => $user->id, 'firstname' => '[SECRETARIA] ' . $user->firstname . ' ' . $user->lastname];
        }
        // LOCAL_INCIDENCE_REPORT_PROFILE_ROLES_FEDERACION <-- NO gestiona

        // Administradores
        // LOCAL_INCIDENCE_REPORT_PROFILE_ROLES_MOODLE <-- LO SOLAPAMOS CON ADMINISTADORES
        $admins = local_incidence_report_get_admins();
        foreach ($admins as $admin) {
            $managers[] = ['id' => $admin->id, 'firstname' => '[MOODLE] ' . $admin->firstname . ' ' . $admin->firstname];
        }

        echo json_encode($managers);

        break;

        $gerencia = required_param('gerencia', PARAM_INT);

        $sql = 'SELECT u.id, u.firstname, u.lastname
                  FROM {role_assignments} as ra
                       JOIN {context} as ctx ON (ra.contextid = ctx.id
                                                 AND ctx.contextlevel=:context)
                       JOIN {user} as u ON (ra.userid = u.id)
                 WHERE ra.roleid=:manager
                       AND ctx.instanceid=:category';

        $get_gestor = $DB->get_records_sql($sql, array('category' => $gerencia, 'context' => 40, 'manager' => 1));

        $array_gestor = array();
        foreach ($get_gestor as $gestor) {
            $array_gestor[] = array("id" => $gestor->id, 'firstname' => $gestor->firstname, 'lastname' => $gestor->lastname);
        }

        echo json_encode($array_gestor);

        break;

        /*
     * Throw error if AJAX isnt handeled
     */
    default:
        throw new coding_exception('Unhandled action');
        break;
}
