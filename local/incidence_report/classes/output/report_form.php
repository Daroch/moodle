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

require_once(dirname(__FILE__) . '/../../../../config.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/componentlib.class.php');
require_once(__DIR__ . '/../../lib.php');

class local_report_form extends moodleform {

    function definition() {
        global $DB;

        $mform = $this->_form;

        // The form can be called from a URL that includes all the desired filter params.
        $get_status = optional_param('status', -1, PARAM_INT);
        $get_type = optional_param('type', -1, PARAM_INT);
        $get_course = optional_param('course', -1, PARAM_INT);
        $get_management = optional_param('management', -1, PARAM_INT);
        $get_manager = optional_param('manager', -1, PARAM_INT);
        $get_start_date = optional_param('start_date', -1, PARAM_INT);
        $get_final_date = optional_param('final_date', -1, PARAM_INT);
        $get_show_category = optional_param('show_category', -1, PARAM_INT);
        $get_show_course = optional_param('show_course', -1, PARAM_INT);
        $get_show_manager = optional_param('show_manager', -1, PARAM_INT);

        $mform->addElement('header', '', get_string('generalform', 'local_incidence_report'));

        // This defines the links for the last five submitted reports
        // NOTE: This is not PER USER, but GLOBAL.
        $history = get_config('local_incidence_report', 'report_history');
        $history = (array) json_decode($history);

        $enlaces = [];
        $fechas_consultas = [];

        $baseurl = '/local/incidence_report/report.php';
        foreach ($history as $time => $filter) {
            $params = [];
            if (isset($filter->status)) {
                $params['status'] =  $filter->status;
            }
            if (isset($filter->type)) {
                $params['type'] = $filter->type;
            }
            if (isset($filter->course)) {
                $params['course'] = $filter->course;
            }
            if (isset($filter->gerencia)) {
                $params['management'] = $filter->gerencia;
            }
            if (isset($filter->gestor)) {
                $params['manager'] = $filter->gestor;
            }
            if (isset($filter->fecha_ini)) {
                $params['start_date'] = $filter->fecha_ini;
            }
            if (isset($filter->fecha_fin)) {
                $params['final_date'] = $filter->fecha_fin;
            }
            if (isset($filter->mostrar_categoria)) {
                $params['show_category'] = $filter->mostrar_categoria;
            }
            if (isset($filter->mostrar_curso)) {
                $params['show_course'] = $filter->mostrar_curso;
            }
            if (isset($filter->mostrar_gestor)) {
                $params['show_manager'] = $filter->mostrar_gestor;
            }

            $link = new moodle_url($baseurl, $params);
            $enlaces[] = $link->out(false);
            $fechas_consultas[] = date("d-m-Y H:i:s", $time);
        }

        $history_array_buttons = null;
        for ($i = 0; $i < count($enlaces); $i++) {
            $params = [
                'onclick' => 'window.location.href=\''.$enlaces[$i].'\'',
            ];
            $history_array_buttons[] = $mform->createElement('button', 'intro-'.$i, $fechas_consultas[$i], $params);
        }

        if ($history_array_buttons != null) {
            arsort($history_array_buttons);
        }
        $mform->addGroup($history_array_buttons, 'history_array_buttons', 'Ultimas 5 consultas', null, false);

        // Estados
        $array_status = array(
            '' => 'Todos',
            '0' => 'Enviada',
            '1' => 'Asignada',
            '2' => 'Procesando',
            '3' => 'Cerrada',
            '4' => 'Caducada',
        );
        $select = $mform->addElement('select', 'status', get_string('status', 'local_incidence_report'), $array_status);
        if ($get_status != -1) {
            $select->setSelected($get_status);
        }

        // Tipo
        $array_type = array(
            '' => 'Todos',
            '0' => 'Login',
            '1' => 'Secretaría',
            '2' => 'Plataforma',
            '3' => 'Cursos',
            '4' => 'Otros',
        );
        $select = $mform->addElement('select', 'type', get_string('type', 'local_incidence_report'), $array_type);
        if ($get_type != -1) {
            $select->setSelected($get_type);
        }

        // Gerencias
        $get_gerencia = $DB->get_records('course_categories');
        $array_gerencia_id[0] = 'Todas las categorías';
        foreach ($get_gerencia as $gerencia) {
            $array_gerencia_id[$gerencia->id] = $gerencia->name;
        }

        $select = $mform->addElement('select', 'gerencia', 'Categoría', $array_gerencia_id);
        if ($get_management != -1) {
            $select->setSelected($get_management);
        }

        //Curso
        $select = $mform->addElement('select', 'courses', get_string('course', 'local_incidence_report'), array('Selecciona primero una categoría'));

        //Gestor
        $select = $mform->addElement('select', 'gestors', get_string('manager', 'local_incidence_report'), array('Selecciona primero una categoría'));

        // Rango fechas
        $sql =  'SELECT MIN(timestamp) AS timestamp FROM {local_incidence_report_msgs}';
        $first_incidence = $DB->get_record_sql($sql);

        $mform->addElement('date_selector', 'fecha_ini', get_string('start_date', 'local_incidence_report'));
        if ($get_start_date != -1) {
            $mform->setDefault('fecha_ini', $get_start_date);
        } else {
            $mform->setDefault('fecha_ini', $first_incidence->timestamp);
        }

        $mform->addElement('date_selector', 'fecha_fin', get_string('final_date', 'local_incidence_report'));
        if ($get_final_date != -1) {
            $mform->setDefault('fecha_fin', $get_final_date);
        }

        //$checkboxarray = array();
        //$checkboxarray[] = &$mform->createElement('checkbox', 'mostrar_curso', get_string('show_courses', 'local_incidence_report'));
        //$checkboxarray[] = &$mform->createElement('checkbox', 'mostrar_gestor', get_string('show_managers', 'local_incidence_report'));
        //$mform->addGroup($checkboxarray, 'checkarray', get_string('filters_show', 'local_incidence_report'), array(''), false);
        //if ($get_show_course != -1) {
        //    $mform->setDefault('mostrar_curso', $get_show_course);
        //}
        //if ($get_show_manager != -1) {
        //    $mform->setDefault('mostrar_gestor', $get_show_manager);
        //}

        // Meto esto porque JL ha metido un CSS sobre el último elemento de la lista y lo he comentado.
        $mform->addElement('html', '<div></div>');

        $mform->addElement('hidden', 'course');
        $mform->setType('course', PARAM_INT);

        $mform->addElement('hidden', 'gestor');
        $mform->setType('gestor', PARAM_INT);

        //$buttonarray = array();
        //$buttonarray[] = &$mform->createElement('submit', 'submitbutton', 'Aplicar');
        //$buttonarray[] = &$mform->createElement('submit', 'cancel', get_string('cancel'));
        //$mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);

        $this->add_action_buttons(false, 'Generar Informe');
    }
}
