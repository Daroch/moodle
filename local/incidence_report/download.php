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

require(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/report_lib.php');
require_once(__DIR__ . '/classes/output/horario_final.php');
require_once(__DIR__ . '/classes/output/report_render.php');

$format = optional_param('format', null, PARAM_INT);

switch ($format) {
    case LOCAL_INCIDENCE_REPORT_EXPORT_FORMAT_XLS:
        $formato = 'xlsx';
        break;
    case LOCAL_INCIDENCE_REPORT_EXPORT_FORMAT_CSV:
    default:
        $formato = 'csv';
}

$name = 'incidence_report' . date('d-m-Y');
$FilePaths = $name . '.' . $formato;

download_file_revamped($FilePaths);

function download_file_revamped($path) {

    $data = local_report_renderer::process_query();

    if (headers_sent()) die('Headers Sent');

    $path_parts = pathinfo($path);
    $ext = strtolower($path_parts["extension"]);

    switch ($ext) {
        case 'xlsx':
            $ctype = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
            break;
        default:
            $ctype = 'application/force-download';
    }

    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private", false);
    header('Content-Encoding: UTF-8');
    header("Content-Type: $ctype");
    header("Content-Disposition: attachment; filename=\"" . basename($path) . "\";");
    header("Content-Transfer-Encoding: binary");

    switch ($ext) {
        case 'csv':
            echo "\xEF\xBB\xBF"; // UTF-8 BOM
            render_csv($data);
            break;
        case 'xlsx':
            render_xlsx($data);
            break;
    }
}

function render_block($title, $data, $separator = "\t", $newline = "\n", $ext = 'xls') {
    ob_start();
    echo $title . $newline;
    foreach ($data as $key => $value) {
        echo $key . $separator . $value . $newline;
    }
    echo $newline;
    $content = ob_get_contents();
    ob_end_clean();

    switch ($ext) {
        case 'xlsx':
            // TODO Unificar el renderizado de bloques aquí... posiblemente pasando tambien la hoja
            //      en la que hay que renderizar.
            echo $content;
            break;
        case 'csv':
            echo $content;
            break;
    }
}

function render_csv($data, $newline = "\n", $separator = ";") {
    $ext = 'csv';

    $temp = [];
    foreach ($data->filter as $filter) {
        $temp[$filter['key']] = $filter['value'];
    }
    render_block('FILTROS', $temp, $separator, $newline, $ext);

    $temp = [
        'Total incidencias del periodo' => $data->total_incidencias_periodo,
        'Total incidencias resueltas' => $data->total_incidencias_resueltas,
        'Tiempo medio de resolucion' => $data->tiempo_medio_de_resolucion,
        'Total incidencias atendidas' => $data->total_incidencias_atendidas,
        'Tiempo medio SLA' => $data->tiempo_medio_sla,
        'Total de incidencias evaluadas' => $data->total_incidencias_evaluadas,
        'Puntuación media' => $data->puntuacion_media,
    ];
    render_block('RESUMEN', $temp, $separator, $newline, $ext);

    $temp = [
        'Login' => $data->totales_por_tipos[0],
        'Secretaría' => $data->totales_por_tipos[1],
        'Plataforma' => $data->totales_por_tipos[2],
        'Cursos' => $data->totales_por_tipos[3],
        'Otros' => $data->totales_por_tipos[4],
    ];
    render_block('TIPOS', $temp, $separator, $newline, $ext);

    $temp = [
        'Enviadas' => $data->totales_por_estado[0],
        'Asignadas' => $data->totales_por_estado[1],
        'Procesando' => $data->totales_por_estado[2],
        'Cerradas' => $data->totales_por_estado[3],
        'Caducadas' => $data->totales_por_estado[4],
    ];
    render_block('ESTADOS', $temp, $separator, $newline, $ext);

    $temp = [
        '0 puntos' => $data->totales_por_puntuaciones[0],
        '1 punto' => $data->totales_por_puntuaciones[1],
        '2 puntos' => $data->totales_por_puntuaciones[2],
        '3 puntos' => $data->totales_por_puntuaciones[3],
        '4 puntos' => $data->totales_por_puntuaciones[4],
    ];
    render_block('PUNTOS', $temp, $separator, $newline, $ext);

    echo 'SUBTIPOS' . $newline;
    echo 'CÓDIGO' . $separator . 'SUBTIPO' . $separator . 'CANTIDAD' . $newline;
    $temporal = [];
    foreach ($data->totales_por_subtipos as $key => $value) {
        $subtipo = (get_string_manager()->string_exists('submit_type_string_' . $key, 'local_incidence_report')) ? get_string('submit_type_string_' . $key, 'local_incidence_report') : $key;
        $subtipo = ($key == '' || $key == 0) ? '--' : $subtipo;
        $temporal[$subtipo] = $value;

        $temporal[$key] = $key . $separator . $subtipo . $separator . $value . $newline;
    }
    ksort($temporal);
    foreach ($temporal as $key => $value) {
        echo $value;
    }
    echo $newline;
}

function render_xlsx($data) {
    $ext = 'xlsx';

    require_once(__DIR__ . '/vendor/xlsxwriter.class.php');

    $rows = [];

    $rows[] = ['FILTROS'];
    foreach ($data->filter as $filter) {
        $rows[] = [$filter['key'], $filter['value']];
    }

    $writer = new XLSXWriter();

    //$writer->writeSheetHeader('Filtros', $header);
    foreach ($rows as $row)
        $writer->writeSheetRow('Filtros', $row);

    $temp = [
        'RESUMEN' => '',
        'Total incidencias del periodo' => $data->total_incidencias_periodo,
        'Total incidencias resueltas' => $data->total_incidencias_resueltas,
        'Tiempo medio de resolucion' => $data->tiempo_medio_de_resolucion,
        'Total incidencias atendidas' => $data->total_incidencias_atendidas,
        'Tiempo medio SLA' => $data->tiempo_medio_sla,
        'Total de incidencias evaluadas' => $data->total_incidencias_evaluadas,
        'Puntuación media' => $data->puntuacion_media,
        'TIPOS' => '',
        'Login' => $data->totales_por_tipos[0],
        'Secretaría' => $data->totales_por_tipos[1],
        'Plataforma' => $data->totales_por_tipos[2],
        'Cursos' => $data->totales_por_tipos[3],
        'Otros' => $data->totales_por_tipos[4],
        'ESTADOS' => '',
        'Enviadas' => $data->totales_por_estado[0],
        'Asignadas' => $data->totales_por_estado[1],
        'Procesando' => $data->totales_por_estado[2],
        'Cerradas' => $data->totales_por_estado[3],
        'Caducadas' => $data->totales_por_estado[4],
        'PUNTOS' => '',
        '0 puntos' => $data->totales_por_puntuaciones[0],
        '1 punto' => $data->totales_por_puntuaciones[1],
        '2 puntos' => $data->totales_por_puntuaciones[2],
        '3 puntos' => $data->totales_por_puntuaciones[3],
        '4 puntos' => $data->totales_por_puntuaciones[4],
    ];

    foreach ($temp as $key => $value) {
        $writer->writeSheetRow('Datos', [$key, $value]);
    }

    $rows = [];
    $rows[] = ['SUBTIPOS'];
    $rows[] = ['CÓDIGO', 'SUBTIPO', 'CANTIDAD'];

    foreach ($rows as $row) {
        $writer->writeSheetRow('Subtipos', $row);
    }

    $temporal = [];
    foreach ($data->totales_por_subtipos as $key => $value) {
        $subtipo = (get_string_manager()->string_exists('submit_type_string_' . $key, 'local_incidence_report')) ? get_string('submit_type_string_' . $key, 'local_incidence_report') : $key;
        $subtipo = ($key == '' || $key == 0) ? '--' : $subtipo;
        $temporal[$key] = [$key, $subtipo, $value];
    }

    ksort($temporal);

    foreach ($temporal as $row) {
        $writer->writeSheetRow('Subtipos',$row);
    }

    echo $writer->writeToString();

    return;
}
