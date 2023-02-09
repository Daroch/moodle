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
 

function local_report_get_incidences($data) {
    global $DB;
    global $USER;

    $sql = "SELECT lr.*, ct.id as category_id, ct.name as category_name, u.id as manager_id, u.firstname as manager_firstname, u.lastname as manager_lastname, c.fullname as curso_name       
          FROM {local_incidence_report_msgs} lr
            LEFT JOIN {course} c 
			ON lr.courseid = c.id
            LEFT JOIN {course_categories} ct 
			ON c.category = ct.id
            LEFT JOIN {user} u 
			ON lr.manager = u.id
          WHERE timestamp > :fecha_ini AND timestamp < :fecha_fin
            AND answers IS NULL";
    $array_filtros = array();

    if ($data->status != "") {
        $sql .= " AND status =:status";
        $array_filtros['status'] = $data->status;
    }

    if ($data->type != "") {
        //$sql .= " AND type =:type";
        $sql .= ' instr(type,:type) = 1 AND ';
        $array_filtros['type'] = $data->type;
    }

    if ($data->course != 0) {
        $sql .= " AND lr.courseid =:course";
        $array_filtros['course'] = $data->course;
    }

    if ($data->gerencia != 0) {
        $sql .= " AND ct.id =:gerencia";
        $array_filtros['gerencia'] = $data->gerencia;
    }

    if ($data->gestor != 0) {
        $sql .= " AND u.id=:gestor";
        $array_filtros['gestor'] = $data->gestor;
    }

    $array_filtros['fecha_ini'] = $data->fecha_ini;
    $array_filtros['fecha_fin'] = $data->fecha_fin;

    //$DB->set_debug(true);
    $records = $DB->get_records_sql($sql, $array_filtros);
    //$DB->set_debug(false);

    return $records;
}

function local_report_get_gerencias($data, $id_gerencia) {
    global $DB;
    global $USER;

    $sql = "SELECT lr.*, ct.id as id_category, ct.name as name_category
            FROM {local_incidence_report_msgs} lr 
                LEFT JOIN {course} c 
			ON lr.courseid = c.id
                LEFT JOIN {course_categories} ct ON c.category = ct.id
            WHERE timestamp > :fecha_ini AND timestamp < :fecha_fin
                AND answers IS NULL";

    if ($id_gerencia == 0) {
        $sql .= " AND ct.id IS NULL";
    } else {
        $sql .= " AND ct.id = :id_gerencia";
    }

    $records = $DB->get_records_sql($sql, array('fecha_ini' => $data->fecha_ini, 'fecha_fin' => $data->fecha_fin, 'id_gerencia' => $id_gerencia));

    return $records;
}

function local_report_get_manager_resolved($data, $id_gerencia, $id_manager) {
    global $DB;
    global $USER;



    $sql = "SELECT lr.*
            FROM {local_incidence_report_msgs} lr 
                LEFT JOIN {course} c 
			ON lr.courseid = c.id
                LEFT JOIN {course_categories} ct ON c.category = ct.id
            WHERE timestamp > :fecha_ini AND timestamp < :fecha_fin
                AND answers IS NULL";


    if ($id_manager == null) {
        $sql .= " AND manager IS NULL";
    } else {
        $sql .= " AND manager = :id_manager
                  AND ct.id = :id_gerencia
                AND status =:status";
    }
    /*
      if ($id_gerencia == null) {
      $sql .= " AND ct.id IS NULL";
      } else {
      $sql .= " AND ct.id = :id_gerencia";
      } */

    $records = $DB->get_records_sql($sql, array(
        'fecha_ini' => $data->fecha_ini,
        'fecha_fin' => $data->fecha_fin,
        'id_gerencia' => $id_gerencia,
        'id_manager' => $id_manager,
        'status' => 3));

    return $records;
}

function local_report_get_course_reports($data, $id_gerencia, $id_course) {
    global $DB;
    global $USER;



    $sql = "SELECT lr.*
            FROM {local_incidence_report_msgs} lr 
                LEFT JOIN {course} c 
			ON lr.courseid = c.id
                LEFT JOIN {course_categories} ct ON c.category = ct.id
            WHERE timestamp > :fecha_ini AND timestamp < :fecha_fin
                AND answers IS NULL
                AND lr.courseid = :id_course";

    if ($id_gerencia == null) {
        $sql .= " AND ct.id IS NULL";
    } else {
        $sql .= " AND ct.id = :id_gerencia";
    }

    $records = $DB->get_records_sql($sql, array(
        'fecha_ini' => $data->fecha_ini,
        'fecha_fin' => $data->fecha_fin,
        'id_gerencia' => $id_gerencia,
        'id_course' => $id_course));

    return $records;
}

function local_report_get_name_course_id($id_course) {
    global $DB;

    if ($id_course == 0) {
        $name_course = (object) array('fullname' => 'Cualquier curso');
    } else {
        $sql = "SELECT fullname
                FROM  {course}
                WHERE id=:id_course";
        $name_course = $DB->get_record_sql($sql, array('id_course' => $id_course));
    }

    return $name_course;
}

function local_report_get_name_gerencia_id($id_course_categories) {
    global $DB;

    if ($id_course_categories == 0) {
        $name_gerencia = (object) array('name' => 'Cualquier gerencia');
    } else {
        $sql = "SELECT name
                FROM  {course_categories}
                WHERE id=:id_gerencia";

        $name_gerencia = $DB->get_record_sql($sql, array('id_gerencia' => $id_course_categories));
    }

    return $name_gerencia;
}

function local_report_get_name_gestor_id($id_user) {
    global $DB;

    if ($id_user == 0) {
        $name_gestor = (object) array('username' => 'General');
    } else {
        $sql = "SELECT username
                FROM {user}
                WHERE id=:id_user";

        $name_gestor = $DB->get_record_sql($sql, array('id_user' => $id_user));
    }

    return $name_gestor;
}

function local_report_get_last_incidence($id_incidence) {
    global $DB;
    // [i] VALORAR TIEMPO SOLUCION
    // Buscar el tiempo de creación de la ULTIMA incidencia con ANSWERS == ID de esta incidencia
    // Evaluar tiempo total
    $sql = "SELECT *
        FROM {local_incidence_report_msgs}
        WHERE answers =:incidence_id
            AND timestamp = (SELECT MAX(timestamp)
            FROM {local_incidence_report_msgs}
            WHERE answers =:incidence_id2)";
    $t_ultima_incidencia = $DB->get_record_sql($sql, array('incidence_id' => $id_incidence, 'incidence_id2' => $id_incidence));

    return $t_ultima_incidencia;
}

function local_report_get_first_incidence($id_incidence) {
    global $DB;
    // [i] VALORAR TIEMPO RESPUESTA
    // Buscar el tiempo de creación de la PRIMERA incidencia con ANSWERS == ID de esta incidencia
    // Evaluar tiempo total
    $sql = "SELECT *
        FROM {local_incidence_report_msgs}
        WHERE answers =:incidence_id
            AND timestamp = (SELECT MIN(timestamp)
            FROM {local_incidence_report_msgs}
            WHERE answers =:incidence_id2)";

    $t_primera_incidencia = $DB->get_record_sql($sql, array('incidence_id' => $id_incidence, 'incidence_id2' => $id_incidence));

    return $t_primera_incidencia;
}
