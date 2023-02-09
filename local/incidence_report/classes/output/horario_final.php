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


// [i] Pasamos las horas como 'Y-m-d H:i'
function tiempo_empleado($inicio_tarea, $fin_tarea, $verbose = true) {

    date_default_timezone_set('Europe/Madrid');
    // [i] Convertir a timestamp
    $ts_inicio = strtotime($inicio_tarea);
    $ts_fin = strtotime($fin_tarea);

    $horario_semana[1] = ['8:00', '17:00'];
    $horario_semana[2] = ['8:00', '17:00'];
    $horario_semana[3] = ['8:00', '17:00'];
    $horario_semana[4] = ['8:00', '17:00'];
    $horario_semana[5] = ['8:00', '17:00'];
    $horario_semana[6] = [];
    $horario_semana[7] = [];

    $horario_dia[strtotime('2020/05/01')] = [];
    //$dias = floor(($ts_fin - $ts_inicio) / (60 * 60 * 24));
    //if ($dias == 0) {
    // [i] No llegan a pasar 24 horas (pero pueden ser 2 días distintos)
    //echo horas_en_horario($horario_dia, date('H:i', $ts_inicio), date('H:i', $ts_fin))."<br>";
    //} else {
    // [i] Pasan al menos 24 horas, inicio y final son necesariamente 2 días distintos
    $dia_inicio = strtotime(date('Y-m-d', $ts_inicio));
    $hora_inicio = date('H:i', $ts_inicio);

    $dia_fin = strtotime(date('Y-m-d', $ts_fin));
    $hora_fin = date('H:i', $ts_fin);

    $horas = 0;
    $minutos = 0;

    for ($dia = $dia_inicio; $dia <= $dia_fin; $dia = strtotime("+1 day", $dia)) {
        if (isset($horario_dia[$dia])) {
            $horario = $horario_dia[$dia];
        } else {
            $horario = $horario_semana[date('N', $dia)];
        }
        if ($verbose)
            //echo date('d/m/Y', $dia) . " ";
            if ($dia == $dia_inicio) {
                if ($dia != $dia_fin) {
                    if ($verbose)
                        //echo "desde las " . $hora_inicio . " ";
                        $temp = horas_en_horario($horario, $hora_inicio);
                    if ($verbose)
                        //echo $temp . "\r\n";
                        $temp = explode(':', $temp);
                    $horas += (int) $temp[0];
                    $minutos += (int) $temp[1];
                    continue;
                } else {
                    if ($verbose)
                        //echo "entre las " . $hora_inicio . " y las " . $hora_fin . " ";
                        $temp = horas_en_horario($horario, $hora_inicio, $hora_fin);
                    if ($verbose)
                        //echo $temp . "\r\n";
                        $temp = explode(':', $temp);
                    $horas += (int) $temp[0];
                    $minutos += (int) $temp[1];
                    continue;
                }
            }
        if ($dia == $dia_fin) {
            if ($verbose)
                //echo "hasta las " . $hora_fin . " ";
                $temp = horas_en_horario($horario, '0:0', $hora_fin);
            if ($verbose)
                //echo $temp . "\r\n";
                $temp = explode(':', $temp);
            $horas += (int) $temp[0];
            $minutos += (int) $temp[1];
            continue;
        }
        $temp = horas_en_horario($horario);
        if ($verbose)
            //echo $temp . "\r\n";
            $temp = explode(':', $temp);
        $horas += (int) $temp[0];
        $minutos += (int) $temp[1];
    }
    //}
    $horas += intdiv(abs($minutos), 60);
    $minutos = (abs($minutos) % 60);
    //echo "$horas horas y $minutos minutos";

    //Modificacion para poder sumar segundos
    $horas_seg = $horas * 60 * 60;
    $min_seg = $minutos * 60;

    return $horas_seg + $min_seg;
}

function horas_en_horario($horario, $inicio = '0:0', $fin = '24:0') {
    $temp = explode(':', $inicio);
    $hora_inicio = $temp[0];
    $minuto_inicio = $temp[1];
    $temp = explode(':', $fin);
    $hora_fin = $temp[0];
    $minuto_fin = $temp[1];
    // [ToDo] $horario (count tiene que ser par y cada hora igual o superior a la anterior)
    // [ToDo] Validar $inicio y $fin ($fin tiene que ser igual o superior a $inicio)
    $horas = 0;
    $minutos = 0;
    if (count($horario) == 0) {
        return "0:0";
    }
    for ($i = 0; $i < count($horario); $i++) {
        $temp = explode(':', $horario[$i]);
        $hora = $temp[0];
        $minuto = $temp[1];
        if ($i % 2 != 0) {
            // [i] Impar -> fin de tramo
            //echo "Procesando tramo de $ultima_hora:$ultimo_minuto a $hora:$minuto<br>";
            $solape = true;
            // [i] Mirar hora de inicio
            if ($hora_inicio < $ultima_hora) {
            } // Anterior al inicio del tramo
            if (($hora_inicio == $ultima_hora) && ($minuto_inicio < $ultimo_minuto)) {
            } // Anterior al inicio del tramo
            if (($hora_inicio == $ultima_hora) && ($minuto_inicio == $ultimo_minuto)) {
            } // Igual al inicio del tramo
            if (($hora_inicio == $ultima_hora) && ($minuto_inicio > $ultimo_minuto)) {
                // Inicia despues del inicio del tramo
                if ($hora_inicio < $hora) {
                    $ultimo_minuto = $minuto_inicio;
                }
                if (($hora_inicio == $hora) && ($minuto_inicio < $minuto)) {
                    $ultimo_minuto = $minuto_inicio;
                }
                if (($hora_inicio == $hora) && ($minuto_inicio == $minuto)) {
                    $solape = false;
                } // Igual al final del tramo
                if (($hora_inicio == $hora) && ($minuto_inicio > $minuto)) {
                    $solape = false;
                } // Posterior al final del tramo
            }
            if (($hora_inicio > $ultima_hora) && ($hora_inicio < $hora)) {
                $ultima_hora = $hora_inicio;
                $ultimo_minuto = $minuto_inicio;
            }
            if (($hora_inicio > $ultima_hora) && ($hora_inicio == $hora) && ($minuto_inicio < $minuto)) {
                $ultima_hora = $hora_inicio;
                $ultimo_minuto = $minuto_inicio;
            }
            if (($hora_inicio > $ultima_hora) && ($hora_inicio == $hora) && ($minuto_inicio == $ultimo_minuto)) {
                $solape = false;
            }
            if (($hora_inicio > $ultima_hora) && ($hora_inicio == $hora) && ($minuto_inicio > $ultimo_minuto)) {
                $solape = false;
            } // Posterior al final del tramo
            if (($hora_inicio > $ultima_hora) && ($hora_inicio > $hora)) {
                $solape = false;
            } // Posterior al final del tramo
            // [i] Mirar hora de final
            if ($hora < $hora_fin) {
            } // Termina despues del tramo
            if (($hora == $hora_fin) && ($minuto < $minuto_fin)) {
            } // Termina despues del tramo
            if (($hora == $hora_fin) && ($minuto == $minuto_fin)) {
            } // Termina cuando termina el tramo
            if (($hora == $hora_fin) && ($minuto < $minuto_fin)) {
                $minuto = $minuto_fin;
            } // Termina antes de que termina el tramo
            if ($hora > $hora_fin) {
                // Finaliza antes del fin del tramo
                if ($hora_fin > $ultima_hora) {
                    $hora = $hora_fin;
                    $minuto = $minuto_fin;
                }
                if (($hora_fin == $ultima_hora) && ($minuto_fin > $ultimo_minuto)) {
                    $minuto = $minuto_fin;
                }
                if (($hora_fin == $ultima_hora) && ($minuto_fin == $ultimo_minuto)) {
                    $solape = false;
                } // Termina en el inicio del tramo
                if (($hora_fin == $ultima_hora) && ($minuto_fin < $ultimo_minuto)) {
                    $solape = false;
                } // Termina antes del inicio del tramo
                if ($hora_fin < $ultima_hora) {
                    $solape = false;
                } // Finaliza antes del inicio del tramo
            }
            //echo "Conversion (final) a tramo de $ultima_hora:$ultimo_minuto a $hora:$minuto<br>";
            if ($solape) {
                $horas += $hora - $ultima_hora;
                $minutos -= $ultimo_minuto;
                $minutos += $minuto;
            }
        }
        $ultima_hora = $hora;
        $ultimo_minuto = $minuto;
    }
    if ($minutos < 0) {
        $horas -= intdiv(abs($minutos), 60);
        $minutos = 60 - (abs($minutos) % 60);
        if ($minutos != 0) {
            $horas--;
        }
    } else {
        $horas += intdiv(abs($minutos), 60);
        $minutos = (abs($minutos) % 60);
    }
    return "$horas:$minutos";
}

//echo tiempo_empleado('2020/5/5 15:0', '2020/5/5 24:0');
