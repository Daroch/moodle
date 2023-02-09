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
require_once(__DIR__ . '/report_form.php');
require_once(__DIR__ . '/incidence_report_motd_renderer.php');
require_once(__DIR__ . '/horario_final.php');

class local_report_renderer extends \plugin_renderer_base {

    public function render_admin() {
        global $OUTPUT;
        global $DB;

        $mform = new local_report_form();

        $fromform = $mform->get_data();

        if ($mform->is_cancelled()) {
            // No hay botón de cancelar.
        } else if ($fromform = $mform->get_data()) {
            $this->update_history($fromform);

            $data = self::process_query();

            echo $OUTPUT->render_from_template('local_incidence_report/report_info_block', (array)$data);

            echo self::render_pie_chart(
                'Incidencias por tipo',
                'Cantidad',
                ['Login', 'Secretaría', 'Plataforma', 'Cursos', 'Otros'],
                $data->totales_por_tipos,
                ['#031d42', '#f2e8c4', '#f5a11e', '#f27514', '#db2a06']
            );

            echo self::render_pie_chart(
                'Incidencias por estado',
                'Cantidad',
                ['Enviadas', 'Asignadas', 'Procesando', 'Cerradas', 'Caducadas'],
                $data->totales_por_estado,
                ['#031d42', '#f2e8c4', '#f5a11e', '#f27514', '#db2a06']
            );

            if ($data->total_incidencias_evaluadas > 0) {
                echo self::render_pie_chart(
                    'Incidencias por puntuación',
                    'Cantidad',
                    ['0 puntos', '1 punto', '2 puntos', '3 puntos', '4 puntos'],
                    $data->totales_por_puntuaciones,
                    ['#031d42', '#f2e8c4', '#f5a11e', '#f27514', '#db2a06']
                );
            }
            echo $this->boton_exportar($fromform, LOCAL_INCIDENCE_REPORT_EXPORT_FORMAT_CSV);
            echo ' ';
            echo $this->boton_exportar($fromform, LOCAL_INCIDENCE_REPORT_EXPORT_FORMAT_XLS);
            echo ' ';
            $url = new moodle_url('/local/incidence_report/report.php');
            echo html_writer::tag('button', 'Volver', [
                'type' => 'button',
                'class' => 'btn btn-primary',
                'onclick' => 'window.location.href="' . $url->out(false) . '"',
            ]);
        } else {
            $mform->display();
        }

        return;
    }

    // Estructura del informe 1
    public function informe1(
        $data,
        $conjunto_de_incidencias,
        $conjunto_de_incidencias_de_categoria,
        $conjunto_de_incidencias_de_categoria_por_gestores,
        $conjunto_de_incidencias_de_categoria_por_curso
    ) {
        echo '<div class=summary-content>';

        echo '<div class="tab-button-block">
            <button class="tab-button btn active" id="show-review-tab">Resumen</button>
            <button class="tab-button btn" id="show-filter-tab">Filtros</button>
        </div>';

        echo '<div class="tab-content tab-hidden" id="review-tab">';
        echo $this->render_view_records($conjunto_de_incidencias, 'resumen', 'Resumen');
        echo '</div>';

        echo '<div class="tab-content tab-hidden" style="display: none" id="filter-tab">';
        echo $this->filter_for($data);
        echo '</div>';

        echo '</div>';

        echo '<div class="graficas">';
        if (isset($conjunto_de_incidencias_de_categoria)) {

            $temp = count($conjunto_de_incidencias_de_categoria);
            if (($temp > 1) && ($temp < 10)) {
                echo charts_visualizacion_todas($conjunto_de_incidencias_de_categoria, 'Total incidencias', true, 'Gerencias');
                echo charts_visualizacion_todas($conjunto_de_incidencias_de_categoria, 'Total incidencias', false, 'Gerencias');
            }

            echo charts_visualizacion_todas($conjunto_de_incidencias_de_categoria, 'Total incidencias', true, 'Tipos');
            echo charts_visualizacion_todas($conjunto_de_incidencias_de_categoria, 'Total incidencias', false, 'Tipos');

            echo charts_visualizacion_todas($conjunto_de_incidencias_de_categoria, 'Total incidencias', true, 'Estados');
            echo charts_visualizacion_todas($conjunto_de_incidencias_de_categoria, 'Total incidencias', false, 'Estados');

            echo charts_visualizacion_tiempos($conjunto_de_incidencias_de_categoria, 'Total incidencias', 'Respuesta');
            echo charts_visualizacion_tiempos($conjunto_de_incidencias_de_categoria, 'Total incidencias', 'Resolucion');

            echo charts_visualizacion_puntuacion($conjunto_de_incidencias_de_categoria, 'Total incidencias');
        }
        echo '</div>';

        echo '<div class=bloque2>';
        foreach ($conjunto_de_incidencias_de_categoria as $id_categoria => $value) {
            echo '<div class=bloque3>';
            // Categorias(genencias)
            if (isset($data->mostrar_categoria)) {
                $title_name = 'Categoria ' . $conjunto_de_incidencias_de_categoria[$id_categoria]->category_name;

                echo '<div class=summary-content>';

                echo '<div class="tab-button-block">
                        <button class="tab-button btn active">' . $title_name . '</button>
                      </div>';
                echo '<div class="tab-content">';
                echo $this->render_view_records($conjunto_de_incidencias_de_categoria[$id_categoria], 'categoria', $title_name);
                echo '</div>';

                echo '</div>';

                if ($data->gerencia != 0) {
                    echo '<div class="graficas">';

                    echo charts_visualizacion_gerencia($conjunto_de_incidencias_de_categoria, 'Total incidencias', true, 'Tipos');
                    echo charts_visualizacion_gerencia($conjunto_de_incidencias_de_categoria, 'Total incidencias', true, 'Estados');

                    echo charts_visualizacion_gerencia($conjunto_de_incidencias_de_categoria, 'Total incidencias', false, 'Tipos');
                    echo charts_visualizacion_gerencia($conjunto_de_incidencias_de_categoria, 'Total incidencias', false, 'Estados');

                    echo '</div>';
                }
            }

            // Gestor
            // Curso
            if (isset($data->mostrar_curso)) {
                foreach ($conjunto_de_incidencias_de_categoria_por_curso[$id_categoria] as $id_curso => $value) {
                    if ($id_curso == 0) {
                        $title_name = 'Incidencias no asignadas a ningun curso';
                        continue;
                    } else {
                        $title_name = 'Curso: ' . $conjunto_de_incidencias_de_categoria_por_curso[$id_categoria][$id_curso]->curso_name;
                    }


                    echo '<div class=summary-content>';
                    echo '<div class="tab-button-block">
                        <button class="tab-button btn active">' . $title_name . '</button>
                      </div>';

                    echo '<div class="tab-content">';
                    echo $this->render_view_records(
                        $conjunto_de_incidencias_de_categoria_por_curso[$id_categoria][$id_curso],
                        'curso',
                        $title_name
                    );
                    echo '</div>';

                    echo '</div>';

                    echo '<div class="graficas">';

                    echo render_incidence_chart($value->incidencia);

                    echo "</div>";
                }
                //echo '<div class="graficas">';
                //echo charts_visualizacion_gerencia($conjunto_de_incidencias_de_categoria_por_curso, 'Total incidencias', true, 'Cursos');
                //echo charts_visualizacion_gerencia($conjunto_de_incidencias_de_categoria_por_curso, 'Total incidencias', false, 'Cursos');
                //echo '</div>';
            }
            echo '</div>';
        }

        if (isset($data->mostrar_gestor)) {
            if ($id_categoria == "") {
                $id_categoria_2 = 0;
            } else {
                $id_categoria_2 = $id_categoria;
            }

            $id_categoria_2 = 0;

            foreach ($conjunto_de_incidencias_de_categoria_por_gestores[$id_categoria_2] as $id_gestor => $value) {
                if (!$id_gestor) {
                    continue;
                }
                $title_name = '';
                $first_name = $conjunto_de_incidencias_de_categoria_por_gestores[$id_categoria_2][$id_gestor]->manager_firstname;
                $last_name = $conjunto_de_incidencias_de_categoria_por_gestores[$id_categoria_2][$id_gestor]->manager_lastname;

                if (isset($first_name)) $title_name .= 'Gestor: ' . $first_name;

                if (isset($last_name)) $title_name .= ', ' . $last_name;

                if ($title_name == '') {
                    $title_name = 'Gestor Sin asignar ';
                }

                echo '<div class=summary-content>';
                echo '<div class="tab-button-block">
                        <button class="tab-button btn active">' . $title_name . '</button>
                      </div>';

                echo '<div class="tab-content">';
                echo $this->render_view_records(
                    $conjunto_de_incidencias_de_categoria_por_gestores[$id_categoria_2][$id_gestor],
                    'gestor',
                    $title_name
                );
                echo '</div>';

                echo '</div>';

                echo '<div class="graficas">';
                echo render_incidence_chart($value->incidencia);
                echo '</div>';

                if (($data->gestor != 0) && (1 == 2)) {
                    echo '<div class="graficas">';
                    echo charts_visualizacion_todas(
                        $conjunto_de_incidencias_de_categoria_por_gestores,
                        'Total incidencias',
                        true,
                        'Gerente'
                    );
                    echo charts_visualizacion_todas(
                        $conjunto_de_incidencias_de_categoria_por_gestores,
                        'Total incidencias',
                        false,
                        'Gerente'
                    );

                    echo charts_visualizacion_gestor(
                        $conjunto_de_incidencias_de_categoria_por_gestores,
                        'Total incidencias',
                        true,
                        'Cursos'
                    );
                    echo charts_visualizacion_gestor(
                        $conjunto_de_incidencias_de_categoria_por_gestores,
                        'Total incidencias',
                        true,
                        'Tipos'
                    );
                    echo charts_visualizacion_gestor(
                        $conjunto_de_incidencias_de_categoria_por_gestores,
                        'Total incidencias',
                        true,
                        'Estados'
                    );

                    echo charts_visualizacion_gestor(
                        $conjunto_de_incidencias_de_categoria_por_gestores,
                        'Total incidencias',
                        false,
                        'Cursos'
                    );
                    echo charts_visualizacion_gestor(
                        $conjunto_de_incidencias_de_categoria_por_gestores,
                        'Total incidencias',
                        false,
                        'Tipos'
                    );
                    echo charts_visualizacion_gestor(
                        $conjunto_de_incidencias_de_categoria_por_gestores,
                        'Total incidencias',
                        false,
                        'Estados'
                    );
                    echo '</div>';
                }
            }
        }




        echo '</div>';
    }

    // Visualizar la estructura del informe 
    public function render_view_records($conjunto_de_incidencias, $class = null, $title_name = null) {

        ob_start();

        $total = count($conjunto_de_incidencias->incidencia);
        $conjunto_de_incidencias->calcular_tiempo_medio_respuesta();
        $conjunto_de_incidencias->calcular_tiempo_medio_resolucion();
        $conjunto_de_incidencias->calcular_puntuacion_media();

        $total_login = 0;
        $total_tecnica = 0;
        $total_funcional = 0;
        $total_otra = 0;

        $total_enviadas = 0;
        $total_asignadas = 0;
        $total_procesadas = 0;
        $total_cerradas = 0;
        $total_caducadas = 0;


        foreach ($conjunto_de_incidencias->incidencia as $incidencia => $valor) {

            switch ($valor->tipo) {
                case 0:
                    $total_login++;
                    break;
                case 1:
                    $total_tecnica++;
                    break;
                case 2:
                    $total_funcional++;
                    break;
                case 3:
                    $total_otra++;
                    break;
            }

            switch ($valor->estado) {
                case 0:
                    $total_enviadas++;
                    break;
                case 1:
                    $total_asignadas++;
                    break;
                case 2:
                    $total_procesadas++;
                    break;
                case 3:
                    $total_cerradas++;
                    break;
                case 4:
                    $total_caducadas++;
                    break;
            }
        }

        $tiempo_medio_resolucion = segundos_a_DiasHorasMin($conjunto_de_incidencias->tiempo_medio_resolucion);
        $tiempo_medio_respuesta = segundos_a_DiasHorasMin($conjunto_de_incidencias->tiempo_medio_respuesta);

        echo '<div class="row px-3">
                <div class="col-12 col-md-6 col-lg-4 pb-3">
                    <table class="' . $class . ' w-100 bg-grey">
                        <tr>
                            <th colspan="2"><b>Resumen</b></th>
                        </tr>
                        <tr>
                            <td><b class="ml-2">Total incidencias del periodo</b></td>
                            <td><span>' . $total . '</span></td>
                        </tr>
                        <tr>
                            <td><b class="ml-2">Tiempo medio resolucion</b></td>
                            <td><span>' . $tiempo_medio_resolucion . '</span></td>
                        </tr>
                        <tr>
                            <td><b class="ml-2">Tiempo medio respuesta</b> </td>
                            <td><span>' . $tiempo_medio_respuesta . '</span></td>
                        </tr>
                        <tr>
                            <td><b class="ml-2">Puntuación media obtenida</b></span></td>
                            <td><span>' . sprintf("%1\$.2f", $conjunto_de_incidencias->puntuacion_media) . '</td>
                        </tr>
                    </table>
                </div>
                <div class="col-12 col-md-6 col-lg-4 pb-3">
                    <table class="' . $class . ' w-100 bg-grey">
                        <tr>
                            <th colspan="2"><b>Tipos</b></th>
                        </tr>
                        <tr>
                            <td><b class="ml-2">Login</b></td>
                            <td><span>' . $total_login . '</span></td>
                        </tr>
                        <tr>
                            <td><b class="ml-2">Técnica</b></td>
                            <td><span>' . $total_tecnica . '</span></td>
                        </tr>
                        <tr>
                            <td><b class="ml-2">Funcional</b></td>
                            <td><span>' . $total_funcional . '</span></td>
                        </tr>
                        <tr>
                            <td><b class="ml-2">Otra</b></td>
                            <td><span>' . $total_otra . '</span></td>
                        </tr>
                    </table>
                </div>
                <div class=" col-12 col-md-6 col-lg-4 pb-3">
                    <table class="' . $class . ' w-100 bg-grey">
                        <tr>
                            <th colspan="2"><b>Estado</b></th>
                        </tr>
                        <tr>
                            <td><b class="ml-2">Enviadas</b></td>
                            <td><span>' . $total_enviadas . '</span></td>
                        </tr>
                        <tr>
                            <td><b class="ml-2">Asignadas</b></td>
                            <td><span>' . $total_asignadas . '</span></td>
                        </tr>
                        <tr>
                            <td><b class="ml-2">Procesadas</b></td>
                            <td><span>' . $total_procesadas . '</span></td>
                        </tr>
                        <tr>
                            <td><b class="ml-2">Cerradas</b></td>
                            <td><span>' . $total_cerradas . '</span></td>
                        </tr>
                        <tr>
                            <td><b class="ml-2">Caducas:</b></td>
                            <td><span>' . $total_caducadas . '</span></td>
                        </tr>  
                    </table>
                </div>
            </div>';

        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

    public function filter_for($data) {

        $show = '';
        $name_course = local_report_get_name_course_id($data->course);
        $name_gerencia = local_report_get_name_gerencia_id($data->gerencia);
        $name_gestor = local_report_get_name_gestor_id($data->gestor);

        if ($data->status == null) {

            $estado = "Todos los estados";
        } else {
            $estado = local_incidence_report_status_literals($data->status);
        }

        if ($data->type == null) {

            $type = "Todos los tipos";
        } else {
            $type = local_incidence_report_type_literals($data->type);
        }

        if (isset($data->mostrar_categoria)) {

            $show .= '<li><b>Mostrado por categorias</b></li>';
        }
        if (isset($data->mostrar_gestor)) {

            $show .= '<li><b>Mostrado por gestor</b></li>';
        }
        if (isset($data->mostrar_curso)) {

            $show .= '<li><b>Mostrado por curso</b></li>';
        }
        echo '<div class="row px-3">
                <div class="col-12 col-md-6 col-lg-4 pb-3">
                    <table class="filtro left w-100 bg-grey">
                        <tr>
                            <th colspan="2"><b>Filtros</b></th>
                        </tr>
                        <tr>
                            <td><b class="ml-2">Estado</b></td>
                            <td><span>' . $estado . '</span></td>
                        </tr>
                        <tr>
                            <td><b class="ml-2">Tipo</b></td>
                            <td><span>' . $type . '</span></td>
                        </tr>
                        <tr>
                            <td><b class="ml-2">Curso</b></td>
                            <td><span>' . $name_course->fullname . '</span></td>
                        </tr>
                        <tr>
                            <td><b class="ml-2">Gerencia</b></td>
                            <td><span>' . $name_gerencia->name . '</span></td>
                        </tr>
                        <tr>
                            <td><b class="ml-2">Gestor</b></td>
                            <td><span>' . $name_gestor->username . '</span></td>
                        </tr>
                        <tr>
                            <td><b class="ml-2">Fecha inicio</b></td>
                            <td><span>' . date('d/m/Y', $data->fecha_ini) . '</span></td>
                        </tr>
                        <tr>
                            <td><b class="ml-2">Fecha final</b></td>
                            <td><span>' . date('d/m/Y', $data->fecha_fin) . '</span></td>
                        </tr>
                        <tr>
                            <td colspan="2">' . $show . '</td>
                        </tr>
                        </table>
                    </div>
                </div>';
    }

    private function update_history($data) {
        // Añadimos la ultima consulta al histórico.
        $history = get_config('local_incidence_report', 'report_history');
        $history = (array) json_decode($history);
        // Guardamos sólo 5
        if (count($history) > 4) {
            array_shift($history);
        }
        // Metemos el nuevo.
        $ahora = time();
        $history[$ahora] = $data;
        //Guardamos el historial
        $history = json_encode($history);
        set_config('report_history', $history, 'local_incidence_report');
    }

    static function get_all_incidences($filter) {
        // PARCHE
        $courses = optional_param('courses', null, PARAM_RAW);
        if ($courses !== null) {
            $filter->courses = $courses;
        }

        global $DB;
        $sql = 'SELECT id,
                       FLOOR(type/100) AS type,
                       type AS subtype,
                       courseid,
                       manager,
                       points,
                       timestamp,
                       (SELECT timestamp FROM {local_incidence_report_msgs} WHERE answers=i.id ORDER BY id ASC LIMIT 1) AS first_answer,
                       (SELECT timestamp FROM mdl_local_incidence_report_msgs WHERE answers=i.id ORDER BY id DESC LIMIT 1) AS last_answer,
                       status
                  FROM {local_incidence_report_msgs} AS i
                 WHERE answers IS NULL
                       AND timestamp >= :fecha_ini
                       AND timestamp <= :fecha_fin';
        $params = [
            'fecha_ini' => $filter->fecha_ini,
            'fecha_fin' => $filter->fecha_fin,
        ];

        if ($filter->status > 0) {
            $sql .= ' AND status=:status ';
            $params['status'] = $filter->status;
        }

        if ($filter->type > 0) {
            $sql .= ' AND instr(type, :type, 0) = 1 ';
            $params['type'] = $filter->type;
        }

        if ($filter->courses > 0) {
            $sql .= ' AND courseid=:courseid ';
            $params['courseid'] = $filter->courses;
        } else {
            if ($filter->gerencia > 0) {
                //$DB->set_debug(true);
                $courseids = $DB->get_records('course', ['category' => $filter->gerencia]);
                //$DB->set_debug(false);
                $courseids = array_keys($courseids);
                if (count($courseids) > 0) {
                    $sql .= ' AND courseid IN (' . implode(',', $courseids) . ') ';
                }
            }
        }

        if ($filter->gestors > 0) {
            $sql .= ' AND manager=:manager ';
            $params['manager'] = $filter->gestors;
        }

        return $DB->get_records_sql($sql, $params);
    }

    static function seconds_to_literal($seconds) {
        $ss = $seconds;
        $s = $ss % 60;
        $m = floor(($ss % 3600) / 60);
        $h = floor(($ss % 86400) / 3600);
        $d = floor(($ss % 2592000) / 86400);
        $M = floor($ss / 2592000);

        $temp = [];
        array_unshift($temp, $s . 's');
        if ($m > 0) {
            array_unshift($temp, $m . 'm');
        }
        if ($h > 0) {
            array_unshift($temp, $h . 'h');
        }
        if ($d > 0) {
            array_unshift($temp, $d . 'd');
        }
        if ($M > 0) {
            array_unshift($temp, $M . 'M');
        }

        return implode(', ', $temp);
    }

    static function get_type_literal($key) {
        switch ($key) {
            case LOCAL_INCIDENCE_REPORT_FILTER_TYPE_LOGIN:
                $code = 'Login';
                break;
            case LOCAL_INCIDENCE_REPORT_FILTER_TYPE_SECRETARY:
                $code = 'Secretaria';
                break;
            case LOCAL_INCIDENCE_REPORT_FILTER_TYPE_PLATFORM:
                $code = 'Plataforma';
                break;
            case LOCAL_INCIDENCE_REPORT_FILTER_TYPE_COURSE:
                $code = 'Cursos';
                break;
            case LOCAL_INCIDENCE_REPORT_FILTER_TYPE_OTHERS:
            default:
                $code = 'Otros';
                break;
        }
        return $code;
    }

    static function get_status_literal($key) {
        switch ($key) {
            case LOCAL_INCIDENCE_REPORT_STATUS_SENT:
                $code = 'Enviadas';
                break;
            case LOCAL_INCIDENCE_REPORT_STATUS_ASSIGNED:
                $code = 'Asignadas';
                break;
            case LOCAL_INCIDENCE_REPORT_STATUS_ONGOING:
                $code = 'Procesando';
                break;
            case LOCAL_INCIDENCE_REPORT_STATUS_CLOSED:
                $code = 'Cerradas';
                break;
            case LOCAL_INCIDENCE_REPORT_STATUS_TIMEDOUT:
                $code = 'Caducadas';
                break;
            default:
                $code = $key;
                break;
        }
        return $code;
    }

    static function render_pie_chart(
        $title = 'Sin título',
        $serie_name = 'Sin nombre',
        $serie_data_names = ['name_1', 'name_2', 'name_3'],
        $serie_data_values = [1, 2, 3],
        $serie_data_colors = ['#FF0000', '#00FF00', '#0000FF']
    ) {
        global $OUTPUT;
        ob_start();

        $chart = new \core\chart_pie();

        $chart->set_title($title);

        $serie = new \core\chart_series($serie_name, $serie_data_values);
        $serie->set_colors($serie_data_colors);

        $chart->add_series($serie);
        $chart->set_labels($serie_data_names);

        echo '<div class="charts">';
        echo $OUTPUT->render_chart($chart, false);
        echo '</div>';

        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

    function boton_exportar($data, $format) {
        ob_start();
        switch ($format) {
            case LOCAL_INCIDENCE_REPORT_EXPORT_FORMAT_CSV:
                $label = 'Exportar CSV';
                break;
            case LOCAL_INCIDENCE_REPORT_EXPORT_FORMAT_XLS:
                $label = 'Exportar XLS';
                break;
            default:
                $label = 'Exportar';
        }

        $baseurl = '/local/incidence_report/download.php';

        $params = [];
        if (isset($data->status)) $params['status'] = $data->status;
        if (isset($data->type)) $params['type'] = $data->type;
        if (isset($data->course)) $params['couse'] = $data->course;
        if (isset($data->gerencia)) $params['management'] = $data->gerencia;
        if (isset($data->gestor)) $params['manager'] = $data->gestor;
        if (isset($data->fecha_ini)) $params['start_date'] = $data->fecha_ini;
        if (isset($data->fecha_fin)) $params['final_date'] = $data->fecha_fin;
        if (isset($data->mostrar_categoria)) $params['show_category'] = $data->mostrar_categoria;
        if (isset($data->mostrar_curso)) $params['show_course'] = $data->mostrar_curso;
        if (isset($data->mostrar_gestor)) $params['show_manager'] = $data->mostrar_gestor;
        $params['format'] = $format;

        $enlace_download = new moodle_url($baseurl, $params);

        echo html_writer::tag('button', $label, [
            'type' => 'button',
            'class' => 'btn btn-primary',
            'onclick' => 'window.location.href="' . $enlace_download->out(false) . '"',
        ]);

        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

    static function process_query() {
        global $DB;

        $mform = new local_report_form();
        $fromform = $mform->get_data();

        if ($fromform == null) {
            // Does not come form a FORM query but a URL request, probably for download.
            // Make a new $fromform
            $fromform = new stdClass();
            $fromform->status = optional_param('status', null, PARAM_INT);
            $fromform->type = optional_param('type', null, PARAM_INT);
            $fromform->courses = optional_param('couse', null, PARAM_INT);
            $fromform->gerencia = optional_param('management', null, PARAM_INT);
            $fromform->gestors = optional_param('manager', null, PARAM_INT);
            $fromform->fecha_ini = optional_param('start_date', null, PARAM_INT);
            $fromform->fecha_fin = optional_param('final_date', null, PARAM_INT);
        }

        // Adecuar los valores recibidos
        // La fecha fin será la indicada + 1 día.
        // TODO: Esto mejor con strtotime porque si no no tiene en cuenta los cambios horarios.
        $fromform->fecha_fin = $fromform->fecha_fin + 86400; //+1 day

        $incidences = self::get_all_incidences($fromform);

        // KPIs (Key Performance Indicators)
        $data = new stdClass();
        $data->total_incidencias_periodo = count($incidences);
        $data->total_incidencias_resueltas = 0;
        $data->tiempo_medio_de_resolucion = null;
        $data->total_incidencias_atendidas = 0;
        $data->tiempo_medio_sla = null;
        $data->total_incidencias_evaluadas = 0;
        $data->puntuacion_media = null;
        $data->totales_por_tipos = [];
        $data->totales_por_subtipos = [];
        $data->totales_por_estado = [];
        $data->totales_por_puntuaciones = [];

        for ($i = 0; $i < 5; $i++) {
            $data->totales_por_tipos[$i] = 0;
            $data->totales_por_estado[$i] = 0;
            $data->totales_por_puntuaciones[$i] = 0;
        }

        $tiempos_de_sla = [];
        $tiempos_de_resolucion = [];
        $puntuaciones = [];
        foreach ($incidences as $incidence) {
            // Clasificar por tipos
            if ($incidence->subtype == 201) {
                if (!isset($data->totales_por_tipos[LOCAL_INCIDENCE_REPORT_FILTER_TYPE_LOGIN])) {
                    $data->totales_por_tipos[LOCAL_INCIDENCE_REPORT_FILTER_TYPE_LOGIN] = 1;
                } else {
                    $data->totales_por_tipos[LOCAL_INCIDENCE_REPORT_FILTER_TYPE_LOGIN]++;
                }
            } else {
                // TODO Generar el subtipo sólo si existe cadena que lo represente
                //      ver más abajo el código que genera $data->subtipos
                if ($incidence->subtype == null) {
                    if (!isset($data->totales_por_tipos[LOCAL_INCIDENCE_REPORT_FILTER_TYPE_OTHERS])) {
                        $data->totales_por_tipos[LOCAL_INCIDENCE_REPORT_FILTER_TYPE_OTHERS] = 1;
                    } else {
                        $data->totales_por_tipos[LOCAL_INCIDENCE_REPORT_FILTER_TYPE_OTHERS]++;
                    }
                } else {
                    if (!isset($data->totales_por_tipos[$incidence->type])) {
                        $data->totales_por_tipos[$incidence->type] = 1;
                    } else {
                        $data->totales_por_tipos[$incidence->type]++;
                    }
                }
            }
            // Clasificar por subtipos
            if (!isset($data->totales_por_subtipos[$incidence->subtype])) {
                $data->totales_por_subtipos[$incidence->subtype] = 1;
            } else {
                $data->totales_por_subtipos[$incidence->subtype]++;
            }
            // Clasificar por estados
            if (!isset($data->totales_por_estado[$incidence->status])) {
                $data->totales_por_estado[$incidence->status] = 1;
            } else {
                $data->totales_por_estado[$incidence->status]++;
            }
            // Tiempos SLA
            if ($incidence->first_answer != null) {
                $tiempos_de_sla[] = tiempo_empleado(
                    date('Y-m-d H:i', $incidence->timestamp),
                    date('Y-m-d H:i', $incidence->first_answer)
                );
                $data->total_incidencias_atendidas++;
            }
            // Tiempos resolución
            switch ($incidence->status) {
                case LOCAL_INCIDENCE_REPORT_STATUS_CLOSED:
                    if ($incidence->last_answer != null) {
                        $tiempos_de_resolucion[] = tiempo_empleado(
                            date('Y-m-d H:i', $incidence->timestamp),
                            date('Y-m-d H:i', $incidence->last_answer)
                        );
                        $data->total_incidencias_resueltas++;
                    }
                    break;
            }
            // Puntuaciones
            if ($incidence->points != null and $incidence->points != -1) {
                $data->total_incidencias_evaluadas++;
                $puntuaciones[] = $incidence->points;
                $data->totales_por_puntuaciones[$incidence->points]++;
            }
        }
        if ($data->total_incidencias_atendidas > 0) {
            $data->tiempo_medio_sla = array_sum($tiempos_de_sla) / $data->total_incidencias_atendidas;
        }
        if ($data->total_incidencias_resueltas > 0) {
            $data->tiempo_medio_de_resolucion = array_sum($tiempos_de_resolucion) / $data->total_incidencias_resueltas;
        }
        if ($data->total_incidencias_evaluadas > 0) {
            $data->puntuacion_media = array_sum($puntuaciones) / $data->total_incidencias_evaluadas;
        }

        $data->tipos = [];
        foreach ($data->totales_por_tipos as $key => $value) {
            $data->tipos[] = ['tipo' => self::get_type_literal($key), 'total' => $value];
        }

        //$data->subtipos = [];
        $temporal = [];
        foreach ($data->totales_por_subtipos as $key => $value) {
            $subtipo = (get_string_manager()->string_exists('submit_type_string_' . $key, 'local_incidence_report')) ? get_string('submit_type_string_' . $key, 'local_incidence_report') : $key;
            $subtipo = ($key == '' || $key == 0) ? '--' : $subtipo;
            //$data->subtipos[] = ['subtipo' => $subtipo, 'total' => $value];
            $temporal[$subtipo] = $value;
        }
        ksort($temporal);
        foreach ($temporal as $key => $value) {
            $data->subtipos[] = ['subtipo' => $key, 'total' => $value];
        }

        $data->estados = [];
        foreach ($data->totales_por_estado as $key => $value) {

            $data->estados[] = ['estado' => self::get_status_literal($key), 'total' => $value];
        }

        if ($data->tiempo_medio_de_resolucion > 0) {
            $data->tiempo_medio_de_resolucion = self::seconds_to_literal($data->tiempo_medio_de_resolucion);
        }

        if ($data->tiempo_medio_sla > 0) {
            $data->tiempo_medio_sla = self::seconds_to_literal($data->tiempo_medio_sla);
        }

        if ($data->puntuacion_media == null) {
            $data->puntuacion_media = ' -- Sin evaluaciones --';
        } else {
            $data->puntuacion_media = sprintf("%01.2f", $data->puntuacion_media) . ' (' . $data->total_incidencias_evaluadas . ')';
        }

        $data->filter = [];

        $data->filter[] = ['key' => 'Estado', 'value' => self::get_status_literal($fromform->status)];
        $data->filter[] = ['key' => 'Tipo', 'value' => self::get_type_literal($fromform->type)];

        $categoria = $DB->get_record('course_categories', ['id' => $fromform->gerencia]);
        $temp = ($categoria) ? $categoria->name : 'Todas';
        $data->filter[] = ['key' => 'Categoría', 'value' => $temp];

        $course = $DB->get_record('course', ['id' => $fromform->courses]);
        $temp = ($course) ? $course->fullname : 'Todos';
        $data->filter[] = ['key' => 'Curso', 'value' => $temp];

        $gestor = $DB->get_record('user', ['id' => $fromform->gestors]);
        $temp = ($gestor) ? $gestor->firstname . ' ' . $gestor->lastname : 'Todos';
        $data->filter[] = ['key' => 'Gestor', 'value' => $temp];

        $data->filter[] = ['key' => 'Desde', 'value' => date('d/m/Y', $fromform->fecha_ini)];
        $data->filter[] = ['key' => 'Hasta', 'value' => date('d/m/Y', $fromform->fecha_fin)];
        //$data->filter[] = ['key' => 'Detalle por cursos', 'value' => $fromform->course];
        //$data->filter[] = ['key' => 'Detalle por gestores', 'value' => $fromform->gestor];

        return $data;
    }
}



class incidencia {

    public $tipo;
    public $estado;
    public $sla;
    public $tiempo_resolucion;
    public $curso;
    public $categoria;
    public $gestor;
    public $points;

    function __construct() {
        $this->tipo = null;
        $this->estado = null;
        $this->sla = null;
        $this->tiempo_resolucion = null;
        $this->curso = null;
        $this->categoria = null;
        $this->gestor = null;
        $this->points = null;
    }
}

class conjuntoIncidencias {

    public $incidencia;
    public $tiempo_medio_respuesta;
    public $tiempo_medio_resolucion;
    public $puntuacion_media;

    function __construct() {


        $this->incidencia = array();
        $this->tiempo_medio_respuesta = null;
        $this->tiempo_medio_resolucion = null;
        $this->puntuacion_media = null;
    }

    function calcular_tiempo_medio_respuesta() {

        $suma = 0;
        $media = 0;

        foreach ($this->incidencia as $incidence) {
            $suma += $incidence->sla;
        }
        if ($suma != 0) $media = $suma / count($this->incidencia);

        $this->tiempo_medio_respuesta = $media;
    }

    function calcular_tiempo_medio_resolucion() {

        $suma = 0;
        $media = 0;

        foreach ($this->incidencia as $incidence) {

            $suma += $incidence->tiempo_resolucion;
        }

        if ($suma != 0) $media = $suma / count($this->incidencia);

        $this->tiempo_medio_resolucion = $media;
    }

    function calcular_puntuacion_media() {

        $suma = 0;
        $cuenta = 0;
        $media = 0;

        foreach ($this->incidencia as $incidence) {
            if (is_numeric($incidence->points)) {
                if ($incidence->points > 0) {
                    $suma += $incidence->points;
                    $cuenta++;
                }
            }
        }

        if ($cuenta != 0) {
            $media = $suma / $cuenta;
        } else {
            $media = "- sin datos suficientes -";
        }

        $this->puntuacion_media = $media;
    }
}

function segundos_a_DiasHorasMin($tiempo_segundos) {

    // Calculamos las "dias, horas, minutos y segundos" medio de la resolucion de incidencias
    $dias = floor($tiempo_segundos / 86400);
    $horas = floor(($tiempo_segundos - ($dias * 86400)) / 3600);
    $minutos = floor((($tiempo_segundos - ($dias * 86400)) - ($horas * 3600)) / 60);
    //$segundos = $tiempo_segundos - ($horas * 3600) - ($minutos * 60);

    if ($dias == 1) {
        $label_dias = ' dia, ';
    } else {
        $label_dias = ' dias, ';
    }

    if ($horas == 1) {
        $label_horas = ' hora y ';
    } else {
        $label_horas = ' horas y ';
    }

    if ($minutos == 1) {
        $label_minutos = ' minuto';
    } else {
        $label_minutos = ' minutos';
    }

    $dias_horas_minutos = '';
    if ($dias > 0) {
        $dias_horas_minutos .= $dias . $label_dias;
    }
    if ($horas > 0) {
        $dias_horas_minutos .= $horas . $label_horas;
    }

    if (isset($minutos)) {
        $dias_horas_minutos .= $minutos . $label_minutos;
    }

    return $dias_horas_minutos;
}

function charts_incidence($conjunto_de_incidencias_de_categoria) {
    global $OUTPUT;

    foreach ($conjunto_de_incidencias_de_categoria as $id_categoria => $value) {

        $total[] = count($value->incidencia);
        $tiempo_medio_resolucion[] = convert_timestamp_to_int($value->tiempo_medio_resolucion);
        $tiempo_medio_respuesta[] = convert_timestamp_to_int($value->tiempo_medio_respuesta);
        $puntuacion_media[] = $value->puntuacion_media;
        $label_categoria[] = $value->category_name;
    }

    $chart = new \core\chart_bar(); // Create a bar chart instance.
    $series1 = new \core\chart_series('Total incidencias', $total);
    $series2 = new \core\chart_series('Tiempo medio resolucion', $tiempo_medio_resolucion);
    $series3 = new \core\chart_series('Tiempo medio respuesta', $tiempo_medio_respuesta);
    $series4 = new \core\chart_series('Puntuacion media', $puntuacion_media);
    $chart->add_series($series1);
    $chart->add_series($series2);
    $chart->add_series($series3);
    $chart->add_series($series4);
    $chart->set_labels($label_categoria);

    echo '<div class="charts">';
    echo $OUTPUT->render_chart($chart, false);
    echo '</div>';
}

function charts_gerencia($conjunto_de_incidencias_de_categoria) {
    global $OUTPUT;

    $chart = new \core\chart_bar(); // Create a bar chart instance.

    $estadisticas = array();

    foreach ($conjunto_de_incidencias_de_categoria as $id_categoria => $value) {

        $estadisticas[$id_categoria][0] = count($value->incidencia);
        $estadisticas[$id_categoria][1] = convert_timestamp_to_int($value->tiempo_medio_resolucion);
        $estadisticas[$id_categoria][2] = convert_timestamp_to_int($value->tiempo_medio_respuesta);
        $estadisticas[$id_categoria][3] = $value->puntuacion_media;

        $series = new \core\chart_series($value->category_name, $estadisticas[$id_categoria]);
        $chart->add_series($series);
    }

    $chart->set_labels(['Total incidencias', 'Tiempo medio resolucion', 'Tiempo medio respuesta', 'Puntuacion media']);

    echo '<div class="charts">';
    echo $OUTPUT->render_chart($chart, false);
    echo '</div>';
}

function charts_incidence_status($conjunto_de_incidencias_de_categoria) {
    global $OUTPUT;

    echo '<div class="pie">';
    echo '<h3 class="title">Grafico por estados</h3>';
    foreach ($conjunto_de_incidencias_de_categoria as $id_categoria => $value) {

        $chart = new \core\chart_pie();
        $estado = array();

        if (!isset($estado[$id_categoria])) {
            $estado[$id_categoria] = [0, 0, 0, 0, 0];
        }

        foreach ($value->incidencia as $incidencia) {

            switch ($incidencia->estado) {
                    //Enviadas
                case 0:
                    $estado[$id_categoria][0]++;
                    break;
                    //Asignadas
                case 1:
                    $estado[$id_categoria][1]++;
                    break;
                    //Procesadas
                case 2:
                    $estado[$id_categoria][2]++;
                    break;
                    //Cerradas
                case 3:
                    $estado[$id_categoria][3]++;
                    break;
                    //Caducadas
                case 4:
                    $estado[$id_categoria][4]++;
                    break;
            }
        }

        $series = new \core\chart_series($value->category_name, $estado[$id_categoria]);
        $chart->add_series($series);
        $chart->set_title($value->category_name);
        $labels = ['Enviadas', 'Asignadas', 'Procesadas', 'Cerradas', 'Caducadas'];
        $chart->set_labels($labels);
        echo '<div>';
        echo $OUTPUT->render_chart($chart, false);
        echo '</div>';
    }
    echo "</div>";
}

function charts_incidence_respuesta($conjunto_de_incidencias_de_categoria) {
    global $OUTPUT;

    echo '<div class="pie">';
    echo '<h3 class="title">Grafico por respuesta</h3>';

    foreach ($conjunto_de_incidencias_de_categoria as $id_categoria => $value) {

        $menos_48 = 0;
        $menos_96 = 0;
        $mas_96 = 0;

        //Mostraremos los que tengan tiempo resolucion
        $mostrar = false;

        $chart = new \core\chart_pie();

        foreach ($value->incidencia as $incidencia) {

            if ($incidencia->sla != null) {
                $mostrar = true;

                $tiempo_resolucion = convert_timestamp_to_int($incidencia->tiempo_resolucion);

                if ($tiempo_resolucion < 48) {

                    $menos_48++;
                } else if ($tiempo_resolucion < 96) {
                    $menos_96++;
                } else {
                    $mas_96++;
                }
            }
        }

        $horas = [$menos_48, $menos_96, $mas_96];

        $series = new \core\chart_series($value->category_name, $horas);
        $chart->add_series($series);
        $chart->set_title($value->category_name);
        $labels = ['Menos 48 horas', 'Entre 48 horas y 96 horas', 'más 96 horas'];
        $chart->set_labels($labels);

        if ($mostrar) {
            echo '<div>';
            echo $OUTPUT->render_chart($chart, false);
            echo '</div>';
        }
    }

    echo "</div>";
}

function charts_incidence_resolution($conjunto_de_incidencias_de_categoria) {
    global $OUTPUT;

    echo '<div class="pie">';
    echo '<h3 class="title">Grafico por resolucion</h3>';

    foreach ($conjunto_de_incidencias_de_categoria as $id_categoria => $value) {

        $menos_48 = 0;
        $menos_96 = 0;
        $mas_96 = 0;

        //Mostraremos los que tengan tiempo resolucion
        $mostrar = false;

        $chart = new \core\chart_pie();

        foreach ($value->incidencia as $incidencia) {

            if ($incidencia->tiempo_resolucion != null) {
                $mostrar = true;

                $tiempo_resolucion = convert_timestamp_to_int($incidencia->tiempo_resolucion);

                if ($tiempo_resolucion < 48) {

                    $menos_48++;
                } else if ($tiempo_resolucion < 96) {
                    $menos_96++;
                } else {
                    $mas_96++;
                }
            }
        }

        $horas = [$menos_48, $menos_96, $mas_96];

        $series = new \core\chart_series($value->category_name, $horas);
        $chart->add_series($series);
        $chart->set_title($value->category_name);
        $labels = ['Menos 48 horas', 'Entre 48 horas y 96 horas', 'más 96 horas'];
        $chart->set_labels($labels);

        if ($mostrar) {
            echo '<div>';
            echo $OUTPUT->render_chart($chart, false);
            echo '</div>';
        }
    }

    echo "</div>";
}

function charts_incidence_puntuacion($conjunto_de_incidencias_de_categoria) {
    global $OUTPUT;

    echo '<div class="pie">';
    echo '<h3 class="title">Grafico por puntuación</h3>';

    foreach ($conjunto_de_incidencias_de_categoria as $id_categoria => $value) {

        //Mostraremos los que tengan puntuacion
        $mostrar = false;

        $chart = new \core\chart_pie();

        if (!isset($puntuacion[$id_categoria])) {
            $puntuacion[$id_categoria] = [0, 0, 0, 0, 0];
        }

        foreach ($value->incidencia as $incidencia) {

            switch ($incidencia->points) {
                    //1
                case 1:
                    $puntuacion[$id_categoria][0]++;
                    $mostrar = true;
                    break;
                    //2
                case 2:
                    $puntuacion[$id_categoria][1]++;
                    $mostrar = true;
                    break;
                    //3
                case 3:
                    $puntuacion[$id_categoria][2]++;
                    $mostrar = true;
                    break;
                    //4
                case 4:
                    $puntuacion[$id_categoria][3]++;
                    $mostrar = true;
                    break;
                    //5
                case 5:
                    $puntuacion[$id_categoria][4]++;
                    $mostrar = true;
                    break;
            }
        }

        $series = new \core\chart_series($value->category_name, $puntuacion[$id_categoria]);
        $chart->add_series($series);
        $chart->set_title($value->category_name);
        $labels = ['1', '2', '3', '4', '5'];
        $chart->set_labels($labels);
        if ($mostrar) {
            echo '<div>';
            echo $OUTPUT->render_chart($chart, false);
            echo '</div>';
        }
    }

    echo "</div>";
}

function convert_timestamp_to_int($timestamp) {


    $hours = (float) date('h', $timestamp) - 1;
    $minutes = date('i', $timestamp);

    return $hours . '.' . $minutes;
}

function exportar_csv($data) {
    $enlace_download = new moodle_url('/local/incidence_report/download.php');

    if (isset($data->status)) $enlace_download = "$enlace_download?status=$data->status";
    if (isset($data->type)) $enlace_download .= "&type=$data->type";
    if (isset($data->course)) $enlace_download .= "&couse=$data->course";
    if (isset($data->gerencia)) $enlace_download .= "&management=$data->gerencia";
    if (isset($data->gestor)) $enlace_download .= "&manager=$data->gestor";
    if (isset($data->fecha_ini)) $enlace_download .= "&start_date=$data->fecha_ini";
    if (isset($data->fecha_fin)) $enlace_download .= "&final_date=$data->fecha_fin";
    if (isset($data->mostrar_categoria)) $enlace_download .= "&show_category=$data->mostrar_categoria";
    if (isset($data->mostrar_curso)) $enlace_download .= "&show_course=$data->mostrar_curso";
    if (isset($data->mostrar_gestor)) $enlace_download .= "&show_manager=$data->mostrar_gestor";

    echo '<button class="btn btn-primary" type="button" onclick=window.location.href="' . $enlace_download . '">Exportar CSV</button>';
}

function count_incidence_filtro($value, $id_categoria, $init, $filtro) {


    $count[$id_categoria] = $init;


    foreach ($value->incidencia as $incidencia) {

        switch ($incidencia->$filtro) {
                //Enviadas || Login
            case 0:
                $count[$id_categoria][0]++;
                break;
                //Asignadas || Tecnica
            case 1:
                $count[$id_categoria][1]++;
                break;
                //Procesadas || Funcional
            case 2:
                $count[$id_categoria][2]++;
                break;
                //Cerradas || Otra
            case 3:
                $count[$id_categoria][3]++;
                break;
                //Caducadas
            case 4:
                $count[$id_categoria][4]++;
                break;
        }
    }

    return $count[$id_categoria];
}

//Visualizacion por TODAS
function charts_visualizacion_todas($incidencias, $nombre = null, $totales, $filtro) {
    global $OUTPUT;
    global $CFG;

    $show = true;
    $name_serie = 'category_name';
    $name_label = $nombre;

    $colores = LOCAL_INCIDENCE_REPORT_COLORS_5;
    $colores_4 = LOCAL_INCIDENCE_REPORT_COLORS_4;
    $colores_3 = LOCAL_INCIDENCE_REPORT_COLORS_3;

    $CFG->chart_colorset = LOCAL_INCIDENCE_REPORT_COLORS_8;

    // TOTALES
    if ($totales) {

        $chart = new \core\chart_bar(); // Create a bar chart instance.

        switch ($filtro) {
            case ('Gerencias'):
                $chart->set_title("Total de incidencias por $filtro");

                $key = 0;
                $color_set = LOCAL_INCIDENCE_REPORT_COLORS_8;
                foreach ($incidencias as $id => $value) {

                    if ($value->$name_serie == null) {
                        $nombre = 'sin asignar';
                    } else {
                        $nombre = $value->$name_serie;
                    }

                    $total = count($value->incidencia);
                    $serie = new \core\chart_series($nombre, [$total]);
                    $serie->set_color($color_set[$key++]);
                    if ($key > (count($color_set) - 1)) $key = 0;
                    $chart->add_series($serie);
                }
                $yaxis = $chart->get_yaxis(0, false);
                $yaxis->set_stepsize(1);
                $chart->set_labels([$name_label]);
                break;

            case ('Tipos'):
                $chart->set_title("Total de incidencias por $filtro");
                $yaxis = $chart->get_yaxis(0, false);
                $yaxis->set_stepsize(1);

                $total = [0, 0, 0, 0];
                $labels = ['Login', 'Técnica', 'Funcional', 'otra'];



                foreach ($incidencias as $id => $value) {

                    $count = count_incidence_filtro($value, $id, [0, 0, 0, 0], 'tipo');

                    foreach ($count as $id => $value) {

                        $total[$id] += $value;
                    }
                }
                foreach ($total as $id => $value) {
                    $serie = new \core\chart_series($labels[$id], [$total[$id]]);
                    $serie->set_color($colores[$id]);
                    $chart->add_series($serie);
                }

                break;

            case ('Estados'):
                $chart->set_title("Total de incidencias por $filtro");
                $yaxis = $chart->get_yaxis(0, false);
                $yaxis->set_stepsize(1);

                $total = [0, 0, 0, 0, 0];
                $labels = ['Enviada', 'Asignada', 'Procesando', 'Cerrada', 'Caducada'];

                foreach ($incidencias as $id => $value) {

                    $count = count_incidence_filtro($value, $id, [0, 0, 0, 0, 0], 'estado');

                    foreach ($count as $id => $value) {

                        $total[$id] += $value;
                    }
                }
                foreach ($total as $id => $value) {
                    $serie = new \core\chart_series($labels[$id], [$total[$id]]);
                    $serie->set_color($colores[$id]);
                    $chart->add_series($serie);
                }

                break;

            case ('Gerente'):
                $chart->set_title("Total de incidencias por $filtro");
                $yaxis = $chart->get_yaxis(0, false);
                $yaxis->set_stepsize(1);

                foreach ($incidencias as $gerencia) {

                    foreach ($gerencia as $key => $value) {

                        $total = count($value->incidencia);

                        if (isset($value->manager_firstname)) {
                            if (isset($value->manager_lastname))
                                $name = $value->manager_firstname . ', ' . $value->manager_lastname;
                        } else {
                            $name = 'Sin Asignar';
                        }

                        $serie = new \core\chart_series($name, [$total]);
                        $chart->add_series($serie);
                    }
                }
                break;
        }

        // PORCENTAJE
    } else {

        $chart = new \core\chart_pie();

        switch ($filtro) {
            case ('Gerencias'):

                $chart->set_title("Porcentaje de incidencias por $filtro");
                $count = 0;
                $name_label = array();

                foreach ($incidencias as $id => $value) {
                    $count += count($value->incidencia);
                    $total[] = count($value->incidencia);

                    if ($value->$name_serie == null) {
                        $name = 'sin asignar';
                    } else {
                        $name = $value->$name_serie;
                    }
                    $name_label[] = $name;
                }
                /*
                  $aux = array();
                  foreach ($total as $n) {
                  $aux[] = round(($n / $count), 2) * 100;
                  }
                  $total = $aux; */

                $key = 0;
                $count = count($total);
                $color_set = array();
                for ($i = 0; $i <= $count; $i++) {
                    $color_set[] = LOCAL_INCIDENCE_REPORT_COLORS_8[$key++];
                    if ($key == 7) {
                        $key = 0;
                    }
                }

                $color_set = LOCAL_INCIDENCE_REPORT_COLORS_8;




                $series = new \core\chart_series($nombre, $total);
                $chart->add_series($series); // On pie charts we just need to set one series.
                $chart->set_labels($name_label);


                break;

            case ('Tipos'):

                $chart->set_title("Porcentaje de incidencias por $filtro");

                $total = [0, 0, 0, 0];
                $labels = ['Login', 'Técnica', 'Funcional', 'otra'];
                $count = 0;

                foreach ($incidencias as $id => $value) {
                    $count += count($value->incidencia);
                    $count_tipos = count_incidence_filtro($value, $id, [0, 0, 0, 0], 'tipo');

                    foreach ($count_tipos as $id => $value) {

                        $total[$id] += $value;
                    }
                }

                /*
                  $aux = array();
                  foreach ($total as $n) {
                  $aux[] = round(($n / $count), 2) * 100;
                  }
                  $total = $aux;
                 * 
                 */

                $series = new \core\chart_series($nombre, $total);
                $series->set_colors($colores_4);
                $chart->add_series($series); // On pie charts we just need to set one series.
                $chart->set_labels($labels);

                break;

            case ('Estados'):

                $chart->set_title("Porcentaje de incidencias por $filtro");

                $total = [0, 0, 0, 0, 0];
                $labels = ['Enviada', 'Asignada', 'Procesando', 'Cerrada', 'Caducada'];
                $count = 0;

                foreach ($incidencias as $id => $value) {
                    $count += count($value->incidencia);
                    $count_tipos = count_incidence_filtro($value, $id, [0, 0, 0, 0, 0], 'estado');

                    foreach ($count_tipos as $id => $value) {

                        $total[$id] += $value;
                    }
                }
                /*
                  $aux = array();
                  foreach ($total as $n) {
                  $aux[] = round(($n / $count), 2) * 100;
                  }
                  $total = $aux;
                 * 
                 */

                $series = new \core\chart_series($nombre, $total);
                $series->set_colors($colores);
                $chart->add_series($series); // On pie charts we just need to set one series.
                $chart->set_labels($labels);

                break;

            case ('Gerente'):

                $chart->set_title("Porcentaje de incidencias por $filtro en una gerencia");
                foreach ($incidencias as $gerencia) {

                    foreach ($gerencia as $key => $value) {

                        $total[] = count($value->incidencia);

                        if ($value->category_name == null) {
                            $nameGerencia = 'Sin gerencia';
                        } else {
                            $nameGerencia = $value->category_name;
                        }

                        if (isset($value->manager_firstname)) {
                            if (isset($value->manager_lastname))
                                $name[] = $nameGerencia . ': ' . $value->manager_firstname . ', ' . $value->manager_lastname;
                        } else {
                            $name[] = $nameGerencia . ': Sin Asignar';
                        }
                    }
                }

                $serie = new \core\chart_series($nombre, $total);
                $series->set_colors($colores);
                $chart->add_series($serie);
                $chart->set_labels($name);

                break;
        }
    }



    if ($show) {
        echo '<div class="charts">';
        echo $OUTPUT->render_chart($chart, false);
        echo '</div>';
    }
}

function charts_visualizacion_gerencia($incidencias, $nombre = null, $totales, $filtro) {
    global $OUTPUT;

    $show = true;
    $name_serie = 'category_name';
    $name_label = $nombre;

    // Gerencia en TOTALES
    if ($totales) {

        switch ($filtro) {
            case ('Cursos'):

                $show = false;

                foreach ($incidencias as $curso) {
                    $chart = new \core\chart_bar(); // Create a bar chart instance.


                    foreach ($curso as $value) {

                        $chart->set_title("Totales de incidencias de $value->category_name por $filtro");
                        $yaxis = $chart->get_yaxis(0, false);
                        $yaxis->set_stepsize(1);

                        $total = count($value->incidencia);

                        $serie = new \core\chart_series($value->curso_name, [$total]);
                        $chart->add_series($serie);
                        $chart->set_labels([$nombre]);
                    }
                    echo '<div class="charts">';
                    echo $OUTPUT->render_chart($chart, false);
                    echo '</div>';
                }


                break;

            case ('Tipos'):

                $chart = new \core\chart_bar(); // Create a bar chart instance
                $total = [0, 0, 0, 0];
                $labels = ['Login', 'Técnica', 'Funcional', 'otra'];
                $show = false;
                $gerencias = array();
                $chart->set_title("Totales de incidencias de Gerencias por $filtro");
                $yaxis = $chart->get_yaxis(0, false);
                $yaxis->set_stepsize(1);

                foreach ($incidencias as $id => $value) {

                    if ($value->category_name == null) {
                        $nombre = 'sin asignar';
                    } else {
                        $nombre = $value->category_name;
                    }


                    $gerencias[] = $nombre;
                    $count_incidencias[] = count_incidence_filtro($value, $id, $total, 'tipo');
                }

                //Cambiamos el array para que sea array por tipos del conjunto de incidencias
                foreach ($count_incidencias as $id => $incid) {

                    foreach ($incid as $key => $value) {
                        $result[$key][] = $incid[$key];
                    }
                }

                //creamos la serie por tipo
                foreach ($result as $key => $value) {

                    if ($key == 0) $serie1 = new \core\chart_series('Login', $result[$key]);
                    if ($key == 1) $serie2 = new \core\chart_series('Técnica', $result[$key]);
                    if ($key == 2) $serie3 = new \core\chart_series('Funcional', $result[$key]);
                    if ($key == 3) $serie4 = new \core\chart_series('otra', $result[$key]);
                }

                $chart->set_labels($gerencias);
                $chart->add_series($serie1);
                $chart->add_series($serie2);
                $chart->add_series($serie3);
                $chart->add_series($serie4);
                echo '<div class="charts">';
                echo $OUTPUT->render_chart($chart, false);
                echo '</div>';

                break;


            case 'Estados':

                $chart = new \core\chart_bar(); // Create a bar chart instance
                $total = [0, 0, 0, 0, 0];
                $labels = ['Enviada', 'Asignada', 'Procesando', 'Cerrada', 'Caducada'];
                $show = false;
                $gerencias = array();

                $chart->set_title("Totales de incidencias de Gerencias por $filtro");
                $yaxis = $chart->get_yaxis(0, false);
                $yaxis->set_stepsize(1);

                foreach ($incidencias as $id => $value) {

                    if ($value->category_name == null) {
                        $nombre = 'sin asignar';
                    } else {
                        $nombre = $value->category_name;
                    }

                    $gerencias[] = $nombre;
                    $count_incidencias[] = count_incidence_filtro($value, $id, $total, 'estado');
                }

                //Cambiamos el array para que sea array por tipos del conjunto de incidencias
                foreach ($count_incidencias as $id => $incid) {

                    foreach ($incid as $key => $value) {
                        $result[$key][] = $incid[$key];
                    }
                }

                //creamos la serie por tipo
                foreach ($result as $key => $value) {

                    if ($key == 0) $serie1 = new \core\chart_series('Enviada', $result[$key]);
                    if ($key == 1) $serie2 = new \core\chart_series('Asignada', $result[$key]);
                    if ($key == 2) $serie3 = new \core\chart_series('Procesando', $result[$key]);
                    if ($key == 3) $serie4 = new \core\chart_series('Cerrada', $result[$key]);
                    if ($key == 4) $serie5 = new \core\chart_series('Caducada', $result[$key]);
                }

                $chart->set_labels($gerencias);
                $chart->add_series($serie1);
                $chart->add_series($serie2);
                $chart->add_series($serie3);
                $chart->add_series($serie4);
                $chart->add_series($serie5);
                echo '<div class="charts">';
                echo $OUTPUT->render_chart($chart, false);
                echo '</div>';

                break;
        }

        // Gerencia en %
    } else {

        switch ($filtro) {
            case ('Cursos'):

                $show = false;

                foreach ($incidencias as $gerencia) {
                    $chart = new \core\chart_pie();

                    $count_total = 0;
                    $count = array();
                    $labels = array();
                    foreach ($gerencia as $curso) {

                        $count_total += count($curso->incidencia);
                        $total = count($curso->incidencia);
                        if ($curso->category_name == null) {
                            $category_name = '"sin asignar"';
                        } else {
                            $category_name = $curso->category_name;
                        }

                        if ($curso->curso_name == null) {
                            $curso_name = 'sin asignar';
                        } else {
                            $curso_name = $curso->curso_name;
                        }
                        $chart->set_title("Porcentaje de incidencias de $category_name por $filtro");
                        $series = new \core\chart_series($nombre, [$total]);
                        $labels[] = $curso_name;
                    }

                    $chart->add_series($series); // On pie charts we just need to set one series.
                    $chart->set_labels($labels);
                    echo '<div class="charts">';
                    echo $OUTPUT->render_chart($chart, false);
                    echo '</div>';
                }

                break;


            case ('Tipos'):

                $labels = ['Login', 'Técnica', 'Funcional', 'otra'];
                $count = array();
                $show = false;

                foreach ($incidencias as $id => $value) {

                    $chart = new \core\chart_pie();
                    $count_tipos = array();
                    $aux = array();
                    $total = [0, 0, 0, 0];

                    $count = count($value->incidencia);
                    $total = count_incidence_filtro($value, $id, $total, 'tipo');

                    if ($value->category_name == null) {
                        $category_name = '"sin asignar"';
                    } else {
                        $category_name = $value->category_name;
                    }

                    $chart->set_title("Porcentaje de incidencias de $category_name por $filtro");
                    $series = new \core\chart_series($nombre, $total);
                    $chart->add_series($series); // On pie charts we just need to set one series.
                    $chart->set_labels($labels);
                    echo '<div class="charts">';
                    echo $OUTPUT->render_chart($chart, false);
                    echo '</div>';
                }

                break;

            case ('Estados'):

                $labels = ['Enviada', 'Asignada', 'Procesando', 'Cerrada', 'Caducada'];
                $count = array();
                $show = false;

                foreach ($incidencias as $id => $value) {

                    $chart = new \core\chart_pie();
                    $count_tipos = array();
                    $aux = array();
                    $total = [0, 0, 0, 0, 0];
                    $count = count($value->incidencia);
                    $total = count_incidence_filtro($value, $id, $total, 'estado');
                    if ($value->category_name == null) {
                        $category_name = '"sin asignar"';
                    } else {
                        $category_name = $value->category_name;
                    }

                    $chart->set_title("Porcentaje de incidencias de $category_name por $filtro");
                    $series = new \core\chart_series($nombre, $total);
                    $chart->add_series($series); // On pie charts we just need to set one series.
                    $chart->set_labels($labels);
                    echo '<div class="charts">';
                    echo $OUTPUT->render_chart($chart, false);
                    echo '</div>';
                }

                break;
        }
    }
    if ($show) {
        echo '<div class="charts">';
        echo $OUTPUT->render_chart($chart, false);
        echo '</div>';
    }
}

function charts_visualizacion_gestor($incidencias, $nombre = null, $totales, $filtro) {
    global $OUTPUT;

    $show = true;

    if ($totales) {
        switch ($filtro) {
            case ('Cursos'):

                $show = false;

                foreach ($incidencias as $gerencia) {
                    $chart = new \core\chart_bar(); // Create a bar chart instance.
                    $label = null;

                    foreach ($gerencia as $value) {

                        if ($value->manager_firstname == null) {
                            $label = 'gestor(sin asignar)';
                        } else {

                            $label = "gestor $value->manager_firstname $value->manager_lastname ";
                        }

                        $chart->set_title("Totales de incidencias de $label por $filtro");
                        $yaxis = $chart->get_yaxis(0, false);
                        $yaxis->set_stepsize(1);

                        $total = count($value->incidencia);
                        $serie = new \core\chart_series($label, [$total]);
                        $chart->add_series($serie);
                        $chart->set_labels([$nombre]);

                        echo '<div class="charts">';
                        echo $OUTPUT->render_chart($chart, false);
                        echo '</div>';
                    }
                }


                break;

            case ('Tipos'):

                $chart = new \core\chart_bar(); // Create a bar chart instance

                $total = [0, 0, 0, 0];
                $labels = ['Login', 'Técnica', 'Funcional', 'otra'];
                $show = false;
                $gerencias = array();
                $chart->set_title("Totales de incidencias de Gestores por $filtro");
                $yaxis = $chart->get_yaxis(0, false);
                $yaxis->set_stepsize(1);

                foreach ($incidencias as $gerencia) {
                    foreach ($gerencia as $id => $value) {

                        if ($value->manager_firstname == null) {
                            $gerencias[] = 'gestor(sin asignar)';
                        } else {
                            $gerencias[] = $value->manager_firstname;
                        }
                        $count_incidencias[] = count_incidence_filtro($value, $id, $total, 'tipo');
                    }
                }

                //Cambiamos el array para que sea array por tipos del conjunto de incidencias
                foreach ($count_incidencias as $id => $incid) {

                    foreach ($incid as $key => $value) {
                        $result[$key][] = $incid[$key];
                    }
                }

                //creamos la serie por tipo
                foreach ($result as $key => $value) {

                    if ($key == 0) $serie1 = new \core\chart_series('Login', $result[$key]);
                    if ($key == 1) $serie2 = new \core\chart_series('Técnica', $result[$key]);
                    if ($key == 2) $serie3 = new \core\chart_series('Funcional', $result[$key]);
                    if ($key == 3) $serie4 = new \core\chart_series('otra', $result[$key]);
                }

                $chart->set_labels($gerencias);
                $chart->add_series($serie1);
                $chart->add_series($serie2);
                $chart->add_series($serie3);
                $chart->add_series($serie4);
                echo '<div class="charts">';
                echo $OUTPUT->render_chart($chart, false);
                echo '</div>';

                break;

            case ('Estados'):

                $chart = new \core\chart_bar(); // Create a bar chart instance

                $total = [0, 0, 0, 0, 0];
                $labels = ['Enviada', 'Asignada', 'Procesando', 'Cerrada', 'Caducada'];
                $show = false;
                $gestor = array();
                $chart->set_title("Totales de incidencias de Gestores por $filtro");
                $yaxis = $chart->get_yaxis(0, false);
                $yaxis->set_stepsize(1);

                foreach ($incidencias as $gerencia) {
                    foreach ($gerencia as $id => $value) {

                        if ($value->manager_firstname == null) {
                            $gestor[] = 'gestor(sin asignar)';
                        } else {
                            $gestor[] = $value->manager_firstname;
                        }
                        $count_incidencias[] = count_incidence_filtro($value, $id, $total, 'estado');
                    }
                }

                //Cambiamos el array para que sea array por tipos del conjunto de incidencias
                foreach ($count_incidencias as $id => $incid) {

                    foreach ($incid as $key => $value) {
                        $result[$key][] = $incid[$key];
                    }
                }

                //creamos la serie por tipo
                foreach ($result as $key => $value) {

                    if ($key == 0) $serie1 = new \core\chart_series('Enviada', $result[$key]);
                    if ($key == 1) $serie2 = new \core\chart_series('Asignada', $result[$key]);
                    if ($key == 2) $serie3 = new \core\chart_series('Procesando', $result[$key]);
                    if ($key == 3) $serie4 = new \core\chart_series('Cerrada', $result[$key]);
                    if ($key == 4) $serie5 = new \core\chart_series('Caducada', $result[$key]);
                }

                $chart->set_labels($gestor);
                $chart->add_series($serie1);
                $chart->add_series($serie2);
                $chart->add_series($serie3);
                $chart->add_series($serie4);
                $chart->add_series($serie5);
                echo '<div class="charts">';
                echo $OUTPUT->render_chart($chart, false);
                echo '</div>';
                break;
        }

        //En porcentajes %
    } else {
        switch ($filtro) {
            case ('Cursos'):

                $show = false;

                foreach ($incidencias as $gerencia) {
                    $chart = new \core\chart_pie();

                    $count_total = 0;
                    $count = array();
                    $labels = array();
                    foreach ($gerencia as $gestor) {

                        $count_total += count($gestor->incidencia);
                        $total = count($gestor->incidencia);

                        if ($gestor->manager_firstname == null) {
                            $nameGestor = 'gestor(sin asignar)';
                            $labels[] = 'gerencia(sin asignar)';
                        } else {
                            $nameGestor = "$gestor->manager_firstname  $gestor->manager_lastname";
                            $labels[] = "$gestor->manager_firstname  $gestor->manager_lastname";
                        }

                        $chart->set_title("Porcentaje de incidencias de $nameGestor por $filtro");
                        $series = new \core\chart_series($nombre, [$total]);
                    }

                    $chart->add_series($series); // On pie charts we just need to set one series.
                    $chart->set_labels($labels);
                    echo '<div class="charts">';
                    echo $OUTPUT->render_chart($chart, false);
                    echo '</div>';
                }

                break;
            case ('Tipos'):

                $labels = ['Login', 'Técnica', 'Funcional', 'otra'];
                $count = array();
                $show = false;

                foreach ($incidencias as $gerencia) {
                    foreach ($gerencia as $id => $gestor) {

                        $chart = new \core\chart_pie();
                        $count_tipos = array();
                        $aux = array();
                        $total = [0, 0, 0, 0];

                        $count = count($gestor->incidencia);
                        $total = count_incidence_filtro($gestor, $id, $total, 'tipo');

                        if ($gestor->manager_firstname == null) {
                            $nameGestor = 'gestor(sin asignar)';
                        } else {
                            $nameGestor = "$gestor->manager_firstname  $gestor->manager_lastname";
                        }

                        $chart->set_title("Porcentaje de incidencias $nameGestor por $filtro");
                        $series = new \core\chart_series($nombre, $total);
                        $chart->add_series($series); // On pie charts we just need to set one series.
                        $chart->set_labels($labels);
                        echo '<div class="charts">';
                        echo $OUTPUT->render_chart($chart, false);
                        echo '</div>';
                    }
                }

                break;

            case ('Estados'):

                $labels = ['Enviada', 'Asignada', 'Procesando', 'Cerrada', 'Caducada'];
                $count = array();
                $show = false;

                foreach ($incidencias as $gerencia) {
                    foreach ($gerencia as $id => $gestor) {

                        $chart = new \core\chart_pie();
                        $count_tipos = array();
                        $aux = array();
                        $total = [0, 0, 0, 0, 0];

                        $count = count($gestor->incidencia);
                        $total = count_incidence_filtro($gestor, $id, $total, 'estado');

                        if ($gestor->manager_firstname == null) {
                            $nameGestor = 'gestor(sin asignar)';
                        } else {
                            $nameGestor = "$gestor->manager_firstname  $gestor->manager_lastname";
                        }

                        $chart->set_title("Porcentaje de incidencias $nameGestor por $filtro");
                        $series = new \core\chart_series($nombre, $total);
                        $chart->add_series($series); // On pie charts we just need to set one series.
                        $chart->set_labels($labels);
                        echo '<div class="charts">';
                        echo $OUTPUT->render_chart($chart, false);
                        echo '</div>';
                    }
                }

                break;
        }
    }

    if ($show) {
        echo '<div class="charts">';
        echo $OUTPUT->render_chart($chart, false);
        echo '</div>';
    }
}

function charts_visualizacion_tiempos($incidencias, $nombre = null, $filtro) {
    global $OUTPUT;

    switch ($filtro) {
        case ('Respuesta'):
            $chart = new \core\chart_pie();

            $menos_48 = 0;
            $menos_96 = 0;
            $mas_96 = 0;

            foreach ($incidencias as $id_categoria => $value) {

                foreach ($value->incidencia as $incidencia) {

                    if ($incidencia->tiempo_resolucion != null) {

                        $tiempo = convert_timestamp_to_int($incidencia->tiempo_resolucion);

                        if ($tiempo < 48) {

                            $menos_48++;
                        } else if ($tiempo < 96) {
                            $menos_96++;
                        } else {
                            $mas_96++;
                        }
                    }
                }

                $horas = [$menos_48, $menos_96, $mas_96];
            }

            $chart->set_title('Tiempo de respuesta');
            $series = new \core\chart_series($nombre, $horas);
            $series->set_colors(LOCAL_INCIDENCE_REPORT_COLORS_3);
            $chart->add_series($series);
            $labels = ['Menos 48 horas', 'Entre 48 horas y 96 horas', 'más 96 horas'];
            $chart->set_labels($labels);

            break;
        case ('Resolucion'):
            $chart = new \core\chart_pie();

            $menos_48 = 0;
            $menos_96 = 0;
            $mas_96 = 0;

            foreach ($incidencias as $id_categoria => $value) {

                foreach ($value->incidencia as $incidencia) {

                    if ($incidencia->sla != null) {

                        $tiempo = convert_timestamp_to_int($incidencia->sla);

                        if ($tiempo < 48) {

                            $menos_48++;
                        } else if ($tiempo < 96) {
                            $menos_96++;
                        } else {
                            $mas_96++;
                        }
                    }
                }

                $horas = [$menos_48, $menos_96, $mas_96];
            }

            $chart->set_title('Tiempo de resolucion');
            $series = new \core\chart_series($nombre, $horas);
            $series->set_colors(LOCAL_INCIDENCE_REPORT_COLORS_3);
            $chart->add_series($series);
            $labels = ['Menos 48 horas', 'Entre 48 horas y 96 horas', 'más 96 horas'];
            $chart->set_labels($labels);

            break;
    }

    echo '<div class="charts">';
    echo $OUTPUT->render_chart($chart, false);
    echo '</div>';
}

function charts_visualizacion_puntuacion($incidencias, $nombre = null) {
    global $OUTPUT;

    $chart = new \core\chart_pie();
    $puntuacion = [0, 0, 0, 0, 0];

    foreach ($incidencias as $id_categoria => $value) {



        foreach ($value->incidencia as $incidencia) {

            switch ($incidencia->points) {
                    //1
                case 1:
                    $puntuacion[0]++;
                    break;
                    //2
                case 2:
                    $puntuacion[1]++;
                    break;
                    //3
                case 3:
                    $puntuacion[2]++;
                    break;
                    //4
                case 4:
                    $puntuacion[3]++;
                    //$puntuacion[$id_categoria][3] ++;
                    //$mostrar = true;
                    break;
                    //5
                case 5:
                    $puntuacion[4]++;
                    break;
            }
        }
    }

    $series = new \core\chart_series($nombre, $puntuacion);
    $series->set_colors(LOCAL_INCIDENCE_REPORT_COLORS_5);
    $chart->add_series($series);
    $chart->set_title('Incidencias por puntuacion');
    $labels = ['1', '2', '3', '4', '5'];
    $chart->set_labels($labels);
    echo '<div class="charts">';
    echo $OUTPUT->render_chart($chart, false);
    echo '</div>';
}

function render_incidence_chart($incidencias) {
    global $OUTPUT;
    global $CFG;

    // [i] Parse all incidences in array...

    $tipo = array();
    $tipo[0] = 0;
    $tipo_name[0] = 'Login';
    $tipo_color[0] = '#588c7e';
    $tipo[1] = 0;
    $tipo_name[1] = 'Técnica';
    $tipo_color[1] = '#f2e394';
    $tipo[2] = 0;
    $tipo_name[2] = 'Funcional';
    $tipo_color[2] = '#f2ae72';
    $tipo[3] = 0;
    $tipo_name[3] = 'Otra';
    $tipo_color[3] = '#d96459';
    $tipo_series_color = ['#588c7e', '#f2e394', '#f2ae72', '#d96459'];

    $estado = array();
    $estado[0] = 0;
    $estado_name[0] = 'Enviada';
    $estado_color[0] = '#588c7e';
    $estado[1] = 0;
    $estado_name[1] = 'Asignada';
    $estado_color[1] = '#f2e394';
    $estado[2] = 0;
    $estado_name[2] = 'Procesando';
    $estado_color[2] = '#f2ae72';
    $estado[3] = 0;
    $estado_name[3] = 'Cerrada';
    $estado_color[3] = '#d96459';
    $estado[4] = 0;
    $estado_name[4] = 'Caducada';
    $estado_color[4] = '#ffcc5c';
    $estado_series_color = ['#588c7e', '#f2e394', '#f2ae72', '#d96459', '#ffcc5c'];


    foreach ($incidencias as $incidencia) {
        $tipo[$incidencia->tipo]++;
        $estado[$incidencia->estado]++;
    }

    $CFG->chart_colorset = LOCAL_INCIDENCE_REPORT_COLORS_5;

    $chart = new \core\chart_bar(); // Create a bar chart instance.
    $chart->set_title("Por tipo");

    foreach ($tipo as $key => $value) {
        $serie = new \core\chart_series($tipo_name[$key], [$value]);
        $serie->set_color($tipo_color[$key]);
        $chart->add_series($serie);
    }
    $yaxis = $chart->get_yaxis(0, false);
    $yaxis->set_stepsize(1);
    $chart->set_labels(['']);

    echo '<div class="charts">';
    echo $OUTPUT->render_chart($chart, false);
    echo '</div>';

    $chart = new \core\chart_pie();
    $series = new \core\chart_series('% por tipo', $tipo);
    $series->set_colors($tipo_series_color);
    $chart->add_series($series); // On pie charts we just need to set one series.
    $chart->set_labels($tipo_name);

    echo '<div class="charts">';
    echo $OUTPUT->render_chart($chart, false);
    echo '</div>';

    $chart = new \core\chart_bar(); // Create a bar chart instance.
    $chart->set_title("Por estado");

    foreach ($estado as $key => $value) {
        $serie = new \core\chart_series($estado_name[$key], [$value]);
        $serie->set_color($estado_color[$key]);
        $chart->add_series($serie);
    }
    $yaxis = $chart->get_yaxis(0, false);
    $yaxis->set_stepsize(1);
    $chart->set_labels(['']);


    echo '<div class="charts">';
    echo $OUTPUT->render_chart($chart, false);
    echo '</div>';

    $chart = new \core\chart_pie();
    $series = new \core\chart_series('% por estado', $estado);
    $series->set_colors($estado_series_color);
    $chart->add_series($series); // On pie charts we just need to set one series.
    $chart->set_labels($estado_name);

    echo '<div class="charts">';
    echo $OUTPUT->render_chart($chart, false);
    echo '</div>';

    return;
}
