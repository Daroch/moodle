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
define('LOCAL_INCIDENCE_REPORT_NOTICE_ERROR', 0);
define('LOCAL_INCIDENCE_REPORT_NOTICE_SUCCESS', 1);
define('LOCAL_INCIDENCE_REPORT_NOTICE_INFO', 2);
define('LOCAL_INCIDENCE_REPORT_NOTICE_WARNING', 3);

define('LOCAL_INCIDENCE_REPORT_TABLE', 'local_incidence_report_msgs');

// TODO - Pasar estas opción a settings del plugin
define('LOCAL_INCIDENCE_REPORT_ENABLE_MOTD', false);
define('LOCAL_INCIDENCE_REPORT_ENABLE_REPORTS', true);
define('LOCAL_INCIDENCE_REPORT_PAGINATE_REPORTS_PER_PAGE', 10);

define('LOCAL_INCIDENCE_REPORT_DEFAULT_ROLE_MANAGER', 1); // DEPRECATED

require_once(__DIR__ . '/lib/profiles.php');
require_once(__DIR__ . '/lib/incidence_status.php');
require_once(__DIR__ . '/lib/incidence_types.php');
require_once(__DIR__ . '/lib/reports.php');

define('LOCAL_INCIDENCE_REPORT_TIMEOUT_DAYS', 14);
define('LOCAL_INCIDENCE_REPORT_TIMEOUT', 14 * 24 * 60 * 60);

define('LOCAL_INCIDENCE_REPORT_HIDE_AFTER', 30);

define('LOCAL_INCIDENCE_REPORT_MAX_FILES_PER_POST', 5);
define('LOCAL_INCIDENCE_REPORT_MAX_BYTES_PER_POST', 10 * 1024 * 1024);

define('LOCAL_INCIDENCE_REPORT_EMAIL_NEW', 1); // logged (manager) and unlogged (user and admin)
define('LOCAL_INCIDENCE_REPORT_EMAIL_ANSWER', 2);
define('LOCAL_INCIDENCE_REPORT_EMAIL_CLOSED', 3);
define('LOCAL_INCIDENCE_REPORT_EMAIL_ASSIGNED', 4);
define('LOCAL_INCIDENCE_REPORT_EMAIL_EVALUATED', 5);
define('LOCAL_INCIDENCE_REPORT_EMAIL_TIMEDOUT', 6);

define('LOCAL_INCIDENCE_REPORT_EXPORT_FORMAT_CSV', 0);
define('LOCAL_INCIDENCE_REPORT_EXPORT_FORMAT_XLS', 1);

/**
 * Generic function used to filter the authorized users. In this case, everyone is allowed to use the plugin, even if not logged in.
 *
 * @param int|object $param user object or user id
 * @return boolean
 */
function local_incidence_report_is_user_allowed($user) {

    return true;
}

function local_incidence_report_allow_management($userid, $incidenceid = null) {
    if (is_siteadmin($userid)) {
        return true;
    }

    global $DB;

    $courseid = optional_param('courseid', 0, PARAM_INT);
    if ($courseid == 0) {
        $courseid = optional_param('fcourse', 0, PARAM_INT);
    }

    if ($incidenceid !== null) {
        $incidence = $DB->get_record('local_incidence_report_msgs', array('id' => $incidenceid));
        if ($incidence) {
            $courseid = $incidence->courseid;
        } else {
            $courseid = 0;
        }
    }

    //$ismanager = local_incidence_report_allow_management($userid);
    //if ($ismanager === false) {
    //    return false;
    //}

    // Vamos a ver qué perfil tiene el usuario indicado en el contexto requerido (en su caso)
    // NOTA Se considera el rol de alumno aunque sabemos que por configuración no va a gestionar ningun tipo de incidencia.
    // Estos perfiles NO son globales por lo que tienen que estar matriculados en el curso indicado para poder gestionar...
    if ($courseid != 0) {
        $isalumno = local_incidence_report_profile_check(LOCAL_INCIDENCE_REPORT_PROFILE_ALUMNO, CONTEXT_COURSE, $courseid);
        $istutor = local_incidence_report_profile_check(LOCAL_INCIDENCE_REPORT_PROFILE_TUTOR, CONTEXT_COURSE, $courseid);
        $isprofesor = local_incidence_report_profile_check(LOCAL_INCIDENCE_REPORT_PROFILE_PROFESOR, CONTEXT_COURSE, $courseid);
        $iscoordinador = local_incidence_report_profile_check(LOCAL_INCIDENCE_REPORT_PROFILE_COORDINADOR, CONTEXT_COURSE, $courseid);
    } else {
        $isalumno = false;
        $istutor = false;
        $isprofesor = false;
        $iscoordinador = false;
    }
    // Estos son perfiles globales, no dependientes de curso...
    $isjefatura = local_incidence_report_profile_check(LOCAL_INCIDENCE_REPORT_PROFILE_JEFATURA);
    $issecretaria = local_incidence_report_profile_check(LOCAL_INCIDENCE_REPORT_PROFILE_SECRETARIA);
    $ismoodle = local_incidence_report_profile_check(LOCAL_INCIDENCE_REPORT_PROFILE_MOODLE);
    $isfederacion = local_incidence_report_profile_check(LOCAL_INCIDENCE_REPORT_PROFILE_FEDERACION);

    // echo '<pre>';
    // echo 'isalumno: ' . (($isalumno) ? 'si' : 'no').'<br>';
    // echo 'istutor: ' . (($istutor) ? 'si' : 'no').'<br>';
    // echo 'isprofesor: ' . (($isprofesor) ? 'si' : 'no').'<br>';
    // echo 'iscoordinador: ' . (($iscoordinador) ? 'si' : 'no').'<br>';
    // echo 'isjefatura: ' . (($isjefatura) ? 'si' : 'no').'<br>';
    // echo 'issecretaria: ' . (($issecretaria) ? 'si' : 'no').'<br>';
    // echo 'ismoodle: ' . (($ismoodle) ? 'si' : 'no').'<br>';
    // echo 'isfederacion: ' . (($isfederacion) ? 'si' : 'no').'<br>';
    // echo '</pre>';

    $shortnamearray = array();
    // Ahora recuperamos los shortnames de los roles que tienen capacidad para gestionar, pero sólo si cumplimos la condición
    // del perfil correspondiente.
    foreach (LOCAL_INCIDENCE_REPORT_DEFAULT_MANAGER_ROLES as $profile) {
        switch ($profile) {
            case LOCAL_INCIDENCE_REPORT_PROFILE_ALUMNO:
                $shortnamearray = ($isalumno) ? array_merge($shortnamearray, LOCAL_INCIDENCE_REPORT_PROFILE_ROLES_ALUMNO) : $shortnamearray;
                break;
            case LOCAL_INCIDENCE_REPORT_PROFILE_TUTOR:
                $shortnamearray = ($istutor) ? array_merge($shortnamearray, LOCAL_INCIDENCE_REPORT_PROFILE_ROLES_TUTOR) : $shortnamearray;
                break;
            case LOCAL_INCIDENCE_REPORT_PROFILE_PROFESOR:
                $shortnamearray = ($isprofesor) ? array_merge($shortnamearray, LOCAL_INCIDENCE_REPORT_PROFILE_ROLES_PROFESOR) : $shortnamearray;
                break;
            case LOCAL_INCIDENCE_REPORT_PROFILE_COORDINADOR:
                $shortnamearray = ($iscoordinador) ? array_merge($shortnamearray, LOCAL_INCIDENCE_REPORT_PROFILE_ROLES_COORDINADOR) : $shortnamearray;
                break;
            case LOCAL_INCIDENCE_REPORT_PROFILE_JEFATURA:
                $shortnamearray = ($isjefatura) ? array_merge($shortnamearray, LOCAL_INCIDENCE_REPORT_PROFILE_ROLES_JEFATURA) : $shortnamearray;
                break;
            case LOCAL_INCIDENCE_REPORT_PROFILE_SECRETARIA:
                $shortnamearray = ($issecretaria) ? array_merge($shortnamearray, LOCAL_INCIDENCE_REPORT_PROFILE_ROLES_SECRETARIA) : $shortnamearray;
                break;
            case LOCAL_INCIDENCE_REPORT_PROFILE_MOODLE:
                $shortnamearray = ($ismoodle) ? array_merge($shortnamearray, LOCAL_INCIDENCE_REPORT_PROFILE_ROLES_MOODLE) : $shortnamearray;
                break;
            case LOCAL_INCIDENCE_REPORT_PROFILE_FEDERACION:
                $shortnamearray = ($isfederacion) ? array_merge($shortnamearray, LOCAL_INCIDENCE_REPORT_PROFILE_ROLES_FEDERACION) : $shortnamearray;
                break;
        }
    }

    if (count($shortnamearray) == 0) {
        return false;
    }

    // Luego buscamos si nuestro usuario tiene asignado algúno de esos roles
    $shortnamelist = implode(',', $shortnamearray);

    $sql = 'SELECT ra.id
              FROM {role_assignments} AS ra
                   JOIN {role} AS r ON (r.id = ra.roleid)
             WHERE ra.userid=:userid
                   AND r.shortname in (' . $shortnamelist . ')';
    $params = array(
        'userid' => $userid,
    );
    $count = count($DB->get_records_sql($sql, $params));

    if ($count > 0) {
        return true;
    }

    return false;
}

function local_incidence_report_allow_observer($userid, $incidenceid = null, $context = 0, $instanceid = 0) {
    global $DB;
    global $USER;

    if ($incidenceid !== null) {
        $parent = $DB->get_record(LOCAL_INCIDENCE_REPORT_TABLE, array('id' => $incidenceid));
    } else {
        $parent = false;
    }

    $isadmin = is_siteadmin($userid);
    if ($parent) {
        $isreporter = ($parent->userid == $USER->id);
    } else {
        $isreporter = false;
    }

    $couldmanage = local_incidence_report_allow_management($USER->id, $incidenceid);

    if (
        $isadmin ||
        $isreporter ||
        $couldmanage
    ) {
        return true;
    }

    // Profiles that can not manage the incidence but could see it...

    // TUTOR of the parent->courseid
    $istutor = local_incidence_report_profile_check(LOCAL_INCIDENCE_REPORT_PROFILE_TUTOR, $context, $instanceid);
    if ($istutor) {
        return true;
    }

    // COORDINATOR of the parent->courseid
    $iscoordinador = local_incidence_report_profile_check(LOCAL_INCIDENCE_REPORT_PROFILE_COORDINADOR, $context, $instanceid);
    if ($iscoordinador) {
        return true;
    }

    return false;
}

function local_incidence_report_notice($message, $type) {

    $css = '';

    switch ($type) {
        case LOCAL_INCIDENCE_REPORT_NOTICE_ERROR:
            $css .= ' incidence-report-notice alert alert-danger ';
            break;
        case LOCAL_INCIDENCE_REPORT_NOTICE_SUCCESS:
            $css .= ' incidence-report-notice alert alert-success';
            break;
        case LOCAL_INCIDENCE_REPORT_NOTICE_INFO:
            $css .= ' incidence-report-notice alert alert-info';
            break;
        case LOCAL_INCIDENCE_REPORT_NOTICE_WARNING:
            $css .= ' incidence-report-notice alert alert-warning';
            break;
        default:
    }

    $html = '';
    $html .= "<div class='$css'>";
    $html .= $message;
    $html .= "</div>";

    return $html;
}

function local_incidence_report_extend_navigation($root) {
    global $USER;
    global $COURSE;

    // Quitamos el elemento del menú lateral siguiendo instrucciones del cliente.
    if ($COURSE->id == 1) {
        return;
    }

    $hideoncourses = get_config('local_incidence_report', 'hide_on_courses');

    $hideoncourses = explode(',',$hideoncourses);
    foreach ($hideoncourses as $courseid) {
        $courseid = trim($courseid);
        if (!is_numeric($courseid)) {
            continue;
        }
        if ($COURSE->id == $courseid) {
            return;
        }
    }

    if (local_incidence_report_is_user_allowed($USER)) {

        $url = new moodle_url("/local/incidence_report/incidence_report.php?courseid={$COURSE->id}");
        $node = navigation_node::create(
            get_string('navigation_title', 'local_incidence_report'),
            $url,
            navigation_node::TYPE_ROOTNODE,
            null,
            null,
            new pix_icon('i/report', '')
        );
        $node->showinflatnavigation = true;

        $child = $root->add_node($node, 'mycourses');
    }

    return;
}

function local_incidence_report_crypto_rand_secure($min, $max) {
    $range = $max - $min;
    if ($range < 1) {
        return $min; // not so random...
    }
    $log = ceil(log($range, 2));
    $bytes = (int) ($log / 8) + 1; // length in bytes
    $bits = (int) $log + 1; // length in bits
    $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
    do {
        $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
        $rnd = $rnd & $filter; // discard irrelevant bits
    } while ($rnd > $range);
    return $min + $rnd;
}

function local_incidence_report_getToken($length) {
    $token = "";
    $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $codeAlphabet .= "abcdefghijklmnopqrstuvwxyz";
    $codeAlphabet .= "0123456789";
    $max = strlen($codeAlphabet); // edited

    for ($i = 0; $i < $length; $i++) {
        $token .= $codeAlphabet[local_incidence_report_crypto_rand_secure(0, $max - 1)];
    }

    $token = implode("-", str_split($token, 4));

    return $token;
}

function local_incidence_report_secondsToTime($seconds) {
    $dtF = new \DateTime('@0');

    if ($seconds == null) {
        $seconds = 0;
    }

    $dtT = new \DateTime("@$seconds");

    $days = $dtF->diff($dtT)->format('%a');
    $hours = $dtF->diff($dtT)->format('%h');
    $hours = sprintf("%'.02d", $hours);
    $minutes = $dtF->diff($dtT)->format('%i');
    $minutes = sprintf("%'.02d", $minutes);
    $seconds = $dtF->diff($dtT)->format('%s');
    $seconds = sprintf("%'.02d", $seconds);

    $timer = "$hours:$minutes h";

    if ($days != '0') {
        $timer = $days . "d " . $timer;
    }

    return $timer;
}

function local_incidence_report_status_literals($code, $admin = false) {
    if ($admin) {
        return get_string('submit_status_admin_string_' . $code, 'local_incidence_report');
    } else {
        return get_string('submit_status_string_' . $code, 'local_incidence_report');
    }
}

function local_incidence_report_type_literals($code, $admin = false) {
    switch ($code) {
        case null:
            return '--';
        case LOCAL_INCIDENCE_REPORT_TYPE_PLATAFORMA_LOGIN:
            return get_string('submit_type_admin_string_0', 'local_incidence_report');

        default:
            return get_string('submit_type_admin_string_' . substr($code, 0, 1), 'local_incidence_report');
    }

    //if ($code == LOCAL_INCIDENCE_REPORT_TYPE_PLATAFORMA_LOGIN) {
    //    return get_string('submit_type_admin_string_0', 'local_incidence_report');
    //}
    //
    // This is done over the first char on the $code
    //return get_string('submit_type_admin_string_' . substr($code, 0, 1), 'local_incidence_report');

    //if ($admin) {
    //    return get_string('submit_type_admin_string_' . substr($code, 0, 1), 'local_incidence_report');
    //} else {
    //    return get_string('submit_type_string_' . substr($code, 0, 1), 'local_incidence_report');
    //}
}

function local_incidence_report_incidenceid_from_code($code) {
    global $DB;

    $conditions = array(
        'token' => trim($code),
    );

    $record = $DB->get_record(LOCAL_INCIDENCE_REPORT_TABLE, $conditions);

    if ($record) {
        return $record->id;
    }

    return false;
}

function local_incidence_report_save_incidence($dataobject) {
    global $DB;

    $sql = "SELECT *
              FROM {local_incidence_report_msgs}
             WHERE userid=:userid
                   AND courseid=:courseid
                   AND ";
    if (!isloggedin()) {
        $sql .= "fullname=:fullname AND
                email=:email AND ";
    }

    $sql .= ' type=:type AND ';

    $sql .=  $DB->sql_compare_text('message') . "=" . $DB->sql_compare_text(':message');
    $sql .= ' AND addtime(from_unixtime(timestamp), "' . LOCAL_INCIDENCE_REPORT_RESPONSE_TIME_DUPLICATED . '") > now()';
    $result = $DB->get_record_sql($sql, $dataobject);

    if ($result) {
        return $result->token;
    } else {
        $token = null;

        if (!isloggedin()) {
            $token = local_incidence_report_getToken(8);
        }

        $dataobject['token'] = $token;
        $dataobject['modified'] = time();

        $result = $DB->insert_record(LOCAL_INCIDENCE_REPORT_TABLE, $dataobject);

        if ($token == null) {
            $token = $result;
        }

        echo local_incidence_report_email(LOCAL_INCIDENCE_REPORT_EMAIL_NEW, $token, $dataobject);

        return $token;
    }
}

function local_incidence_report_get_answers_for($incidenceid) {
    global $DB;

    $sql = "SELECT *
              FROM {local_incidence_report_msgs}
             WHERE answers=:answers
                   ORDER BY timestamp ASC";

    $sql_params = array(
        'answers' => $incidenceid,
    );

    $records = $DB->get_records_sql($sql, $sql_params);

    return $records;
}

function local_incidence_report_get_sla_for($incidence) {
    global $DB;

    $timestart = $incidence->timestamp;

    $sql = 'SELECT timestamp
              FROM {local_incidence_report_msgs}
             WHERE answers=:answers
                   AND userid!=:userid
                   ORDER BY timestamp ASC
                   LIMIT 1';
    $sql_params = array(
        'answers' => $incidence->id,
        'userid' => $incidence->userid,
    );
    $record = $DB->get_record_sql($sql, $sql_params);

    if ($record) {
        $firstanswertime = $record->timestamp;
    } else {
        // [i] There is an special situation when incidence has been closed without manager answers
        if ($incidence->status == LOCAL_INCIDENCE_REPORT_STATUS_CLOSED) {
            $firstanswertime = $incidence->modified;
        } else {
            $firstanswertime = time();
        }
    }

    return local_incidence_report_secondsToTime($firstanswertime - $timestart);
}

function local_incidence_report_save_incidence_response($dataobject) {
    global $DB;
    global $USER;

    ob_start();

    $result = array();
    $result['id'] = null;
    $result['msg'] = '';
    $result['type'] = LOCAL_INCIDENCE_REPORT_NOTICE_INFO;

    $parentid = $dataobject['answers'];
    $manager = null;
    $isadmin = is_siteadmin($USER->id);

    if ($isadmin) {
        $parent = $DB->get_record(LOCAL_INCIDENCE_REPORT_TABLE, array('id' => $parentid));
        $manager = $parent->manager;
    }

    if ($dataobject['manager'] == 0) {
        $dataobject['manager'] = null;
    }

    if ($dataobject['message'] == '') {
        if ($isadmin) {
            if ($parent->manager != $dataobject['manager']) {
                $data = array(
                    'id' => $parentid,
                    'manager' => $dataobject['manager'],
                );

                if ($parent->status == LOCAL_INCIDENCE_REPORT_STATUS_SENT) {
                    $data['status'] = LOCAL_INCIDENCE_REPORT_STATUS_ASSIGNED;
                } else if (($parent->status == LOCAL_INCIDENCE_REPORT_STATUS_ASSIGNED) && ($dataobject['manager'] == null)) {
                    $data['status'] = LOCAL_INCIDENCE_REPORT_STATUS_SENT;
                }

                $update = false;

                if ((($dataobject['manager'] == null) && ($parent->status <= LOCAL_INCIDENCE_REPORT_STATUS_ASSIGNED)) ||
                    (($parent->status > LOCAL_INCIDENCE_REPORT_STATUS_ASSIGNED) && ($dataobject['manager'] != null)) ||
                    ($parent->status <= LOCAL_INCIDENCE_REPORT_STATUS_ASSIGNED)
                ) {
                    $queryresult = $DB->update_record(LOCAL_INCIDENCE_REPORT_TABLE, $data);
                    // There was a note here saying "Eval query...". No idea what was supposed to mean. I left this here just in case

                    $result['msg'] = get_string('manager_update', 'local_incidence_report');
                    $result['type'] = LOCAL_INCIDENCE_REPORT_NOTICE_SUCCESS;

                    // Send an email to new manager...
                    $mail_result = local_incidence_report_email(LOCAL_INCIDENCE_REPORT_EMAIL_ASSIGNED, $parentid);

                    $result['msg'] .= $mail_result;

                    return $result;
                } else {
                    $result['msg'] = get_string('cant_swap_manager', 'local_incidence_report');
                    $result['type'] = LOCAL_INCIDENCE_REPORT_NOTICE_WARNING;
                    return $result;
                }
            }
        }
        $result['msg'] = get_string('incidence_message_empty', 'local_incidence_report');
        $result['type'] = LOCAL_INCIDENCE_REPORT_NOTICE_ERROR;

        ob_end_clean();
        return $result;
    }

    $sql = "SELECT *
              FROM {local_incidence_report_msgs}
             WHERE answers=:answers
                   AND userid=:userid AND
            " . $DB->sql_compare_text('message') . "=" . $DB->sql_compare_text(':message');
    $sql .= '      AND addtime(from_unixtime(timestamp), "0:5:0") > now()';

    $sql_params = array(
        'answers' => $parentid,
        'userid' => $dataobject['userid'],
        'message' => $dataobject['message'],
    );

    $record = $DB->get_record_sql($sql, $sql_params);

    if (!$record) {
        // We are checking for a duplicate entry. Not record means no duplicate (on short time)
        $ismanager = false;

        $managergroups = local_incidence_report_get_managers($parentid);
        foreach ($managergroups as $managergroup) {
            if (isset($managergroup[$USER->id])) {
                $ismanager = true;
                break;
            }
        }

        $isadmin = is_siteadmin($USER->id);

        if ($isadmin || $ismanager) {
            if ($dataobject['manager'] == null) {
                $dataobject['manager'] = $USER->id;
            }
            $dataobject['id'] = $dataobject['answers'];
            $dataobject['status'] = LOCAL_INCIDENCE_REPORT_STATUS_ONGOING;
        }

        $record = $DB->insert_record(LOCAL_INCIDENCE_REPORT_TABLE, $dataobject);

        $result['id'] = $record;
        $result['msg'] = get_string('answer_submitted', 'local_incidence_report');
        $result['type'] = LOCAL_INCIDENCE_REPORT_NOTICE_SUCCESS;

        // Update PARENT
        $parentdataobject = array(
            'id' => $parentid,
            'modified' => time(),
        );

        if ($isadmin || $ismanager) {
            $parentdataobject['status'] = LOCAL_INCIDENCE_REPORT_STATUS_ONGOING;
            $parentdataobject['manager'] = $dataobject['manager']; // $USER->id;
        }
        $recordupdated = $DB->update_record(LOCAL_INCIDENCE_REPORT_TABLE, $parentdataobject);
        echo local_incidence_report_email(LOCAL_INCIDENCE_REPORT_EMAIL_ANSWER, $record);

        //$debug = true;
        //if ($debug) {
        //    echo '<pre> DEPURANDO...';
        //    echo 'isadmin:' . ($isadmin ? 'si' : 'no');
        //    echo PHP_EOL;
        //    echo 'ismanager:' . ($ismanager ? 'si' : 'no');
        //    echo PHP_EOL;
        //    var_dump($dataobject);
        //    echo PHP_EOL;
        //    var_dump($parentdataobject);
        //    echo '</pre>';
        //    die();
        //}
    } else {
        $result['msg'] = get_string('answer_duplication_error', 'local_incidence_report');
        $result['type'] = LOCAL_INCIDENCE_REPORT_NOTICE_WARNING;
    }

    $html = ob_get_contents();
    ob_end_clean();
    echo $html;
    return $result;
}

function local_incidence_report_is_waiting($incidenceid) {
    global $DB;
    global $USER;

    $incidence = $DB->get_record('local_incidence_report_msgs', array('id' => $incidenceid));
    $reporter = $incidence->userid;

    $sql = 'SELECT *
              FROM {local_incidence_report_msgs}
             WHERE answers=:answers
                   ORDER BY id DESC
                   LIMIT 1';
    $sql_params = array(
        'answers' => $incidenceid,
    );
    $record = $DB->get_record_sql($sql, $sql_params);
    if ($record) {
        return ($USER->id != $record->userid);
    } else {
        if ($USER->id == $reporter) {
            return false;
        } else {
            return true;
        }
    }
}

function local_incidence_report_countnlink($courseid, $statusarray = null) {
    global $DB;

    $sql = "SELECT count(*) as count FROM {local_incidence_report_msgs} WHERE courseid = :courseid AND status IN (" . implode(
        ',',
        $statusarray
    ) . ")";
    $params = array(
        'courseid' => $courseid,
    );

    $results = $DB->get_records_sql($sql, $params);

    $count = reset($results)->count;

    $params = array();

    $params['fcourse'] = $courseid;
    $params['fstatus'] = implode('-', $statusarray);
    $params['view'] = 'manager';

    $url = new moodle_url("/local/incidence_report/incidence_report.php", $params);

    $link = "<a href='{$url->__tostring()}'>$count</a>";

    return $link;
}

function local_incidence_report_get_reports($courseidarray = null, $statusarray = null) {
    global $DB;
    global $USER;

    $isalumno = false;
    $istutor = false;
    $isprofesor = false;
    $iscoordinador = false;
    $isjefatura = false;
    $issecretaria = false;
    $ismoodle = false;
    $isfederacion = false;

    $ismanager = local_incidence_report_allow_management($USER->id);

    if ($ismanager) {
        //$isalumno = local_incidence_report_profile_check(LOCAL_INCIDENCE_REPORT_PROFILE_ALUMNO);
        $istutor = local_incidence_report_profile_check(LOCAL_INCIDENCE_REPORT_PROFILE_TUTOR);
        $isprofesor = local_incidence_report_profile_check(LOCAL_INCIDENCE_REPORT_PROFILE_PROFESOR);
        $iscoordinador = local_incidence_report_profile_check(LOCAL_INCIDENCE_REPORT_PROFILE_COORDINADOR);
        $isjefatura = local_incidence_report_profile_check(LOCAL_INCIDENCE_REPORT_PROFILE_JEFATURA);
        $issecretaria = local_incidence_report_profile_check(LOCAL_INCIDENCE_REPORT_PROFILE_SECRETARIA);
        //$ismoodle = local_incidence_report_profile_check(LOCAL_INCIDENCE_REPORT_PROFILE_MOODLE);
        $isfederacion = local_incidence_report_profile_check(LOCAL_INCIDENCE_REPORT_PROFILE_FEDERACION);
    }

    $isadmin = is_siteadmin($USER->id);
    if ($isadmin) {
        $ismoodle = true;
    }

    $filters = local_incidence_report_get_all_params(false);

    $isobserver = false;
    if (is_numeric($filters->filter->course)) {
        if (optional_param('view', 'user', PARAM_RAW) == 'observer') {
            $isobserver = local_incidence_report_allow_observer($USER->id, null, CONTEXT_COURSE, $filters->filter->course);
        }
    }

    $sql_params = array();

    if ($isadmin || ($ismanager && ($filters->view == 'manager'))) {
        if ($isadmin) {
            $sql = 'SELECT ir.*
                      FROM {local_incidence_report_msgs} as ir
                           LEFT JOIN {user} AS u ON (u.id = ir.userid)
                     WHERE ir.answers IS NULL ';
        } else {
            // Get managed types
            // TODO Está automatizado la relación incidencia->managers pero no manager->incidencias
            $managedtypes = array();
            if ($istutor) {
                $managedtypes = array_merge($managedtypes, LOCAL_INCIDENCE_REPORT_PROFILE_TUTOR_MANAGES);
            };
            if ($isprofesor) {
                $managedtypes = array_merge($managedtypes, LOCAL_INCIDENCE_REPORT_PROFILE_PROFESOR_MANAGES);
            };
            if ($iscoordinador) {
                $managedtypes = array_merge($managedtypes, LOCAL_INCIDENCE_REPORT_PROFILE_COORDINADOR_MANAGES);
            };
            if ($isjefatura) {
                $managedtypes = array_merge($managedtypes, LOCAL_INCIDENCE_REPORT_PROFILE_JEFATURA_MANAGES);
            };
            if ($issecretaria) {
                $managedtypes = array_merge($managedtypes, LOCAL_INCIDENCE_REPORT_PROFILE_SECRETARIA_MANAGES);
            };
            if ($ismoodle) {
                $managedtypes = array_merge($managedtypes, LOCAL_INCIDENCE_REPORT_PROFILE_MOODLE_MANAGES);
            };
            if ($isfederacion) {
                $managedtypes = array_merge($managedtypes, LOCAL_INCIDENCE_REPORT_PROFILE_FEDERACION_MANAGES);
            };
            $sql = 'SELECT ir.*
                      FROM {local_incidence_report_msgs} AS ir
                           LEFT JOIN {user} AS u ON (u.id = ir.userid)
                     WHERE ir.answers IS NULL
                           AND type IN (' . implode(',', $managedtypes) . ')
                           AND (ir.manager IS NULL OR ir.manager=:userid) ';
            $sql_params['userid'] = $USER->id;
        }
    } else if ($isobserver) {
        $sql = 'SELECT ir.*
                  FROM {local_incidence_report_msgs} AS ir
                 WHERE ir.answers IS NULL ';
        // Observer could be 'tutor' or 'coordinador'... if 'tutor' don't show 'coordinador' incidences
        $profiles = array_merge(LOCAL_INCIDENCE_REPORT_PROFILE_ROLES_COORDINADOR);
        $coordinadores = local_incidence_report_get_userids_for_profile($profiles, CONTEXT_COURSE, $filters->filter->course);
        $hideusers = array_merge($coordinadores, [$USER->id]);
        if ($istutor) {
            $sql .= ' AND ir.userid NOT IN (' . implode(',', $hideusers) . ') ';
        }
    } else {
        $sql = 'SELECT ir.*
                  FROM {local_incidence_report_msgs} AS ir
                 WHERE ir.answers IS NULL
                       AND (ir.userid=:userid)';
        $sql_params['userid'] = $USER->id;
    }

    if ($isadmin || ($ismanager && ($filters->view == 'manager')) || $isobserver) {
        $enablefilters = true;
        if ($enablefilters) {
            if ($filters->filter->type !== null) {
                switch ($filters->filter->type) {
                    case LOCAL_INCIDENCE_REPORT_FILTER_TYPE_LOGIN:
                        $sql .= ' AND type=:type ';
                        $sql_params['type'] = LOCAL_INCIDENCE_REPORT_TYPE_PLATAFORMA_LOGIN;
                        break;
                    case LOCAL_INCIDENCE_REPORT_FILTER_TYPE_PLATFORM: // 2XX
                        $sql .= ' AND instr(type,"' . $filters->filter->type . '") = 1';
                        $sql .= ' AND type!=:type ';
                        $sql_params['type'] = LOCAL_INCIDENCE_REPORT_TYPE_PLATAFORMA_LOGIN;
                        break;
                    case LOCAL_INCIDENCE_REPORT_FILTER_TYPE_OTHERS: // 3XX
                        $sql .= ' AND type IS NULL ';
                        break;
                    case LOCAL_INCIDENCE_REPORT_FILTER_TYPE_SECRETARY: // 1XX
                    case LOCAL_INCIDENCE_REPORT_FILTER_TYPE_COURSE: // 3XX
                    default:
                        $sql .= ' AND instr(type,"' . $filters->filter->type . '") = 1';
                }
            }

            if ($filters->filter->manager !== null) {
                $sql .= " AND ir.manager=:manager ";
                $sql_params['manager'] = $filters->filter->manager;
            }

            if (($filters->filter->username !== null) && ($filters->filter->username !== '')) {
                $sql .= " AND u.username LIKE :username ";
                $sql_params['username'] = '%' . $filters->filter->username . '%';
            }

            // jmazcunan - En teoría nos pueden llegar los estados numéricos separados por guiones
            //             y un array de estados a considerar. Si llega el array, el array tiene
            //             prioridad sobre el filtro.

            // Su existe, convertimos el array al formato esperado y machacamos el filtro.
            if ($statusarray != null) {
                $filters->filter->status = implode('-', $statusarray);
            }

            // Tenemos que convertir el formto de estados separados por guiones a separados por comas.
            if ($filters->filter->status !== null) {
                $statuslist = str_replace('-', ',', $filters->filter->status);
                $sql .= ' AND status IN (' . $statuslist . ')';
            }
        }
    }

    if (is_numeric($filters->filter->course)) {
        if ($filters->filter->course != 1) {
            $sql .= ' AND ir.courseid = :courseid ';
            $sql_params['courseid'] = $filters->filter->course;
        }
    }

    if ($filters->filter->hidden === '0') {
        $hideafterdays = get_config('local_incidence_report', 'hide_after');
        $hideafterdaysstatus = [LOCAL_INCIDENCE_REPORT_STATUS_CLOSED, LOCAL_INCIDENCE_REPORT_STATUS_TIMEDOUT];
        $sql .= ' AND IF (modified < unix_timestamp(CURRENT_DATE - INTERVAL ' . $hideafterdays . ' DAY),status NOT IN (' . implode(',', $hideafterdaysstatus) . '), true) ';
    }

    $sql .= ' ORDER BY ir.modified DESC ';

    //$DB->set_debug(true);
    $records = $DB->get_records_sql($sql, $sql_params);
    //$DB->set_debug(false);

    $pagination = new stdClass();
    $pagination->totalcount = count($records);
    $pagination->perpage = LOCAL_INCIDENCE_REPORT_PAGINATE_REPORTS_PER_PAGE;
    $pagination->lastpage = floor($pagination->totalcount / $pagination->perpage);
    if ($filters->page > $pagination->lastpage) {
        $pagination->page = $pagination->lastpage;
    } else {
        $pagination->page = $filters->page;
    }
    $pagination->baseurl = '';

    if ($pagination->page == '') {
        $pagination->page = 0;
    }

    $limitfrom = $pagination->page * $pagination->perpage;
    $limitnum = $pagination->perpage;

    $sql = $sql . " LIMIT $limitfrom, $limitnum ";

    $records = $DB->get_records_sql($sql, $sql_params);

    $result['pagination'] = $pagination;
    $result['records'] = $records;

    return $result;
}

function local_incidence_report_set_timedout_reports() {
    global $DB;

    $params = array();
    list($statuslist, $params) = $DB->get_in_or_equal(
        [LOCAL_INCIDENCE_REPORT_STATUS_CLOSED, LOCAL_INCIDENCE_REPORT_STATUS_TIMEDOUT],
        SQL_PARAMS_NAMED,
        'status'
    );

    $params['timeout'] = '-' . get_config('local_incidence_report', 'timeout_days') . ' 0:0:0';

    $sql = 'SELECT id, userid
              FROM {local_incidence_report_msgs}
             WHERE answers IS NULL
                   AND status NOT ' . $statuslist . '
                   AND ADDTIME(now(), :timeout) > from_unixtime(modified)
                   ORDER BY modified DESC';

    $results = $DB->get_records_sql($sql, $params);

    foreach ($results as $result) {
        $params = array(
            'answers' => $result->id,
        );
        $answers = $DB->get_records(LOCAL_INCIDENCE_REPORT_TABLE, $params, $sort = 'timestamp DESC', '*', null, 1);

        if (!$answers) {
            continue;
        }

        $lastanswer = reset($answers);

        if ($lastanswer->userid != $result->userid) {
            $dataobject = array(
                'id' => $result->id,
                'status' => LOCAL_INCIDENCE_REPORT_STATUS_TIMEDOUT,
            );
            $updated = $DB->update_record(LOCAL_INCIDENCE_REPORT_TABLE, $dataobject);
            echo local_incidence_report_email(LOCAL_INCIDENCE_REPORT_EMAIL_TIMEDOUT, $result->id);
        } else {
            // Don't expire... but maybe ping manager by mail...
            // Alex Ferrer on 20191217 - "do nothing"
        }
    }

    return;
}

function local_incidence_report_get_managers($incidenceid = null, $allownull = false) {
    global $DB;

    $sql = 'SELECT *
              FROM {local_incidence_report_msgs}
             WHERE id=:incidenceid';
    $params = array(
        'incidenceid' => $incidenceid,
    );
    $result = $DB->get_record_sql($sql, $params);

    $courseid = $result->courseid;
    $type = $result->type;

    $enabledmanagers = array();

    // TODO Esta solución empezó como algo temporal y rápido pero ha crecido demasiado.
    //      Sobre todo porque no se han borrado las cosas que ya no se usan.
    switch ($type) {
        case LOCAL_INCIDENCE_REPORT_TYPE_SECRETARIA_MATRICULA:
            $enabledmanagers = LOCAL_INCIDENCE_REPORT_TYPE_SECRETARIA_MATRICULA_MANAGERS;
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_SECRETARIA_EXPEDIENTE:
            $enabledmanagers = LOCAL_INCIDENCE_REPORT_TYPE_SECRETARIA_EXPEDIENTE_MANAGERS;
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_SECRETARIA_CONVOCATORIA:
            $enabledmanagers = LOCAL_INCIDENCE_REPORT_TYPE_SECRETARIA_CONVOCATORIA_MANAGERS;
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_SECRETARIA_BAJA:
            $enabledmanagers = LOCAL_INCIDENCE_REPORT_TYPE_SECRETARIA_BAJA_MANAGERS;
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_SECRETARIA_ACTAS:
            $enabledmanagers = LOCAL_INCIDENCE_REPORT_TYPE_SECRETARIA_ACTAS_MANAGERS;
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_SECRETARIA_CERTIFICADOS:
            $enabledmanagers = LOCAL_INCIDENCE_REPORT_TYPE_SECRETARIA_CERTIFICADOS_MANAGERS;
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_SECRETARIA_COMISIONES:
            $enabledmanagers = LOCAL_INCIDENCE_REPORT_TYPE_SECRETARIA_COMISIONES_MANAGERS;
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_PLATAFORMA_LOGIN:
            $enabledmanagers = LOCAL_INCIDENCE_REPORT_TYPE_PLATAFORMA_LOGIN_MANAGERS;
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_PLATAFORMA_CURSO:
            $enabledmanagers = LOCAL_INCIDENCE_REPORT_TYPE_PLATAFORMA_CURSO_MANAGERS;
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_PLATAFORMA_SECCION:
            $enabledmanagers = LOCAL_INCIDENCE_REPORT_TYPE_PLATAFORMA_SECCION_MANAGERS;
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_PLATAFORMA_FALLO:
            $enabledmanagers = LOCAL_INCIDENCE_REPORT_TYPE_PLATAFORMA_FALLO_MANAGERS;
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_PLATAFORMA_MATRICULA:
            $enabledmanagers = LOCAL_INCIDENCE_REPORT_TYPE_PLATAFORMA_MATRICULA_MANAGERS;
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_PLATAFORMA_RECURSO:
            $enabledmanagers = LOCAL_INCIDENCE_REPORT_TYPE_PLATAFORMA_RECURSO_MANAGERS;
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_PLATAFORMA_CONTENIDOS:
            $enabledmanagers = LOCAL_INCIDENCE_REPORT_TYPE_PLATAFORMA_CONTENIDOS_MANAGERS;
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_PLATAFORMA_CORRECCION:
            $enabledmanagers = LOCAL_INCIDENCE_REPORT_TYPE_PLATAFORMA_CORRECCION_MANAGERS;
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_PLATAFORMA_CALIFICADOR:
            $enabledmanagers = LOCAL_INCIDENCE_REPORT_TYPE_PLATAFORMA_CALIFICADOR_MANAGERS;
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_PLATAFORMA_ENTREGA:
            $enabledmanagers = LOCAL_INCIDENCE_REPORT_TYPE_PLATAFORMA_ENTREGA_MANAGERS;
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_PLAGIO:
            $enabledmanagers = LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_PLAGIO_MANAGERS;
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_CALIFICACION:
            $enabledmanagers = LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_CALIFICACION_MANAGERS;
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_EXAMEN_FECHA:
            $enabledmanagers = LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_EXAMEN_FECHA_MANAGERS;
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_EXAMEN_LUGAR:
            $enabledmanagers = LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_EXAMEN_LUGAR_MANAGERS;
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_CONVOCATORIA_JE:
            $enabledmanagers = LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_CONVOCATORIA_JE_MANAGERS;
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_EXAMEN_RECOGIDA:
            $enabledmanagers = LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_EXAMEN_RECOGIDA_MANAGERS;
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_ACTAS:
            $enabledmanagers = LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_ACTAS_MANAGERS;
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_TAREAS:
            $enabledmanagers = LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_TAREAS_MANAGERS;
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_ACCESO:
            $enabledmanagers = LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_ACCESO_MANAGERS;
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_ENTREGAS:
            $enabledmanagers = LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_ENTREGAS_MANAGERS;
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_CALIFICACIONES:
            $enabledmanagers = LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_CALIFICACIONES_MANAGERS;
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_SECCION:
            $enabledmanagers = LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_SECCION_MANAGERS;
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_FALLO:
            $enabledmanagers = LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_FALLO_MANAGERS;
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_MATRICULA:
            $enabledmanagers = LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_MATRICULA_MANAGERS;
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_RECURSO:
            $enabledmanagers = LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_RECURSO_MANAGERS;
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_CONTENIDOS:
            $enabledmanagers = LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_CONTENIDOS_MANAGERS;
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_CORRECCION:
            $enabledmanagers = LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_CORRECCION_MANAGERS;
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_CALIFICADOR:
            $enabledmanagers = LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_CALIFICADOR_MANAGERS;
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_ENTREGA:
            $enabledmanagers = LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_ENTREGA_MANAGERS;
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_EXPEDIENTE:
            $enabledmanagers = LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_EXPEDIENTE_MANAGERS;
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_CONVOCATORIA:
            $enabledmanagers = LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_CONVOCATORIA_MANAGERS;
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_BAJA:
            $enabledmanagers = LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_BAJA_MANAGERS;
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_FIRMA_ACTAS:
            $enabledmanagers = LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_FIRMA_ACTAS_MANAGERS;
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_CERTIFICADOS:
            $enabledmanagers = LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_CERTIFICADOS_MANAGERS;
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_COMISIONES:
            $enabledmanagers = LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_COMISIONES_MANAGERS;
            break;
    }

    $managers = array();

    // Independientes del curso
    if (in_array(LOCAL_INCIDENCE_REPORT_PROFILE_FEDERACION, $enabledmanagers)) {
        $managers[LOCAL_INCIDENCE_REPORT_PROFILE_FEDERACION] = local_incidence_report_profile_get(LOCAL_INCIDENCE_REPORT_PROFILE_FEDERACION);
    }
    if (in_array(LOCAL_INCIDENCE_REPORT_PROFILE_JEFATURA, $enabledmanagers)) {
        $managers[LOCAL_INCIDENCE_REPORT_PROFILE_JEFATURA] = local_incidence_report_profile_get(LOCAL_INCIDENCE_REPORT_PROFILE_JEFATURA);
    }
    if (in_array(LOCAL_INCIDENCE_REPORT_PROFILE_MOODLE, $enabledmanagers)) {
        $managers[LOCAL_INCIDENCE_REPORT_PROFILE_MOODLE] = local_incidence_report_profile_get(LOCAL_INCIDENCE_REPORT_PROFILE_MOODLE);
    }
    if (in_array(LOCAL_INCIDENCE_REPORT_PROFILE_SECRETARIA, $enabledmanagers)) {
        $managers[LOCAL_INCIDENCE_REPORT_PROFILE_SECRETARIA] = local_incidence_report_profile_get(LOCAL_INCIDENCE_REPORT_PROFILE_SECRETARIA);
    }

    // Dependientes del curso
    if (in_array(LOCAL_INCIDENCE_REPORT_PROFILE_PROFESOR, $enabledmanagers)) {
        $managers[LOCAL_INCIDENCE_REPORT_PROFILE_PROFESOR] = local_incidence_report_profile_get(LOCAL_INCIDENCE_REPORT_PROFILE_PROFESOR, CONTEXT_COURSE, $courseid);
    }
    if (in_array(LOCAL_INCIDENCE_REPORT_PROFILE_TUTOR, $enabledmanagers)) {
        $managers[LOCAL_INCIDENCE_REPORT_PROFILE_TUTOR] = local_incidence_report_profile_get(LOCAL_INCIDENCE_REPORT_PROFILE_TUTOR, CONTEXT_COURSE, $courseid);
    }
    if (in_array(LOCAL_INCIDENCE_REPORT_PROFILE_COORDINADOR, $enabledmanagers)) {
        $managers[LOCAL_INCIDENCE_REPORT_PROFILE_COORDINADOR] = local_incidence_report_profile_get(LOCAL_INCIDENCE_REPORT_PROFILE_COORDINADOR, CONTEXT_COURSE, $courseid);
    }

    return $managers;
}

function local_incidence_report_get_admins() {
    global $DB;

    $record = $DB->get_record('config', array('name' => 'siteadmins'));
    $records = $DB->get_records_select('user', 'id IN (' . $record->value . ')');

    return $records;
}

function local_incidence_report_moodle_notify_type($incidencereportnotoficationtype) {
    switch ($incidencereportnotoficationtype) {
        case LOCAL_INCIDENCE_REPORT_NOTICE_SUCCESS;
            return \core\output\notification::NOTIFY_SUCCESS;
        case LOCAL_INCIDENCE_REPORT_NOTICE_WARNING;
            return \core\output\notification::NOTIFY_WARNING;
        case LOCAL_INCIDENCE_REPORT_NOTICE_INFO;
            return \core\output\notification::NOTIFY_INFO;
        case LOCAL_INCIDENCE_REPORT_NOTICE_ERROR;
            return \core\output\notification::NOTIFY_ERROR;
    }

    return null;
}

function local_incidence_report_output($var) {
    ob_start();

    var_dump($var);

    $output = ob_get_contents();
    ob_end_clean();
    return $output;
}

function local_incidence_report_before_footer() {

    global $COURSE;
    $url = new moodle_url("/local/incidence_report/incidence_report.php?fcourse={$COURSE->id}");

    $html = '<a id="local_incidence_report_button_top" href="' . $url->__tostring() . '"> ' . get_string(
        'navigation_title',
        'local_incidence_report'
    ) . '</a>';

    global $PAGE;

    $PAGE->requires->js_init_code("$('#snap-pm-header-quicklinks').append('$html'); $('#local_incidence_report_button_top').insertBefore($('#snap-pm-logout'));");

    $html = '<a id="local_incidence_report_settings_link" href="' . $url->__tostring() . '"> ' . get_string(
        'navigation_title',
        'local_incidence_report'
    ) . '</a>';
    $PAGE->requires->js_init_code("$('#settingsnav').prepend('$html')");

    global $OUTPUT;
    $iconurl = $OUTPUT->image_url('messages', 'theme');
    $icon = '<img src="' . $iconurl . '" class="svg-icon" alt="" role="presentation">';
    $html = '<a id="local_incidence_report_button_settings" href="' . $url->__tostring() . '">' . $icon . get_string(
        'navigation_title',
        'local_incidence_report'
    ) . '</a>';
    $PAGE->requires->js_init_code("$('.toc-footer').append('$html')");
}

function local_incidence_report_get_token_for($incidenceid) {
    global $DB;

    $record = $DB->get_record(LOCAL_INCIDENCE_REPORT_TABLE, array('id' => $incidenceid));
    return $record->token;
}

function local_incidence_report_get_id_for($token) {
    global $DB;

    $record = $DB->get_record(LOCAL_INCIDENCE_REPORT_TABLE, array('token' => $token));
    return $record->id;
}

function local_incidence_report_close_incidence($token = null, $points = null, $message = '', $incidenceid = null) {
    global $DB;

    if ($token == null) {
        if (!isloggedin()) {
            $result['msg'] = get_string('token_or_logged', 'local_incidence_report');
            $result['type'] = LOCAL_INCIDENCE_REPORT_NOTICE_ERROR;

            return $result;
        }
    } else {
        if ($incidenceid == null) {
            $incidenceid = local_incidence_report_get_id_for($token);
        }
    }

    $record = $DB->get_record(LOCAL_INCIDENCE_REPORT_TABLE, array('id' => $incidenceid));

    if ($record->status == LOCAL_INCIDENCE_REPORT_STATUS_CLOSED) {
        $result['msg'] = get_string('incidence_already_closed', 'local_incidence_report');
        $result['type'] = LOCAL_INCIDENCE_REPORT_NOTICE_ERROR;

        return $result;
    }

    $dataobject = array(
        'id' => $incidenceid,
        'status' => LOCAL_INCIDENCE_REPORT_STATUS_CLOSED,
        'points' => $points,
        'modified' => time(),
    );

    $record = $DB->update_record(LOCAL_INCIDENCE_REPORT_TABLE, $dataobject);
    // [!!!] Previously mailing to usert clossing the incidence. NOW mailing to user as manager
    echo local_incidence_report_email(LOCAL_INCIDENCE_REPORT_EMAIL_CLOSED, $incidenceid);




    $result['msg'] = get_string('incidence_closed', 'local_incidence_report');
    $result['type'] = LOCAL_INCIDENCE_REPORT_NOTICE_SUCCESS;

    return $result;
}

function local_incidence_report_can_user_close($incidenceid) {
    global $DB;
    global $USER;

    $record = $DB->get_record(LOCAL_INCIDENCE_REPORT_TABLE, array('id' => $incidenceid));

    if (!$record) {
        return false;
    }

    if ($record->userid != $USER->id) {
        // Esto hace que si quien ve la incidencia es distinto del que la ha puesto le deja cerrarla.
        return true;
    } else {
        // Le meto un ELSE y el resto de casos ya no se contemplan...
        return false;
    }
}

function local_incidence_report_list_files_for($incidenceid, $contextid) {

    $html = '';

    $out = array();

    $fs = get_file_storage();
    $files = $fs->get_area_files($contextid, 'local_incidence_report', 'attachments', $incidenceid);

    $count = count($files);

    if ($count == 0) {
        return;
    }

    foreach ($files as $file) {
        $filename = $file->get_filename();
        if ($filename == '.') {
            continue;
        }

        $params = array(
            $file->get_contextid(),
            'local_incidence_report',
            'attachments',
            $file->get_itemid(),
            $file->get_filepath(),
            $filename,
        );

        $url = moodle_url::make_pluginfile_url(
            $file->get_contextid(),
            'local_incidence_report',
            'attachments',
            $file->get_itemid(),
            $file->get_filepath(),
            $filename,
            true
        );

        $out[] = html_writer::link($url, $filename);
    }
    $br = html_writer::empty_tag('br');

    $html .= '<tr>';
    $html .= '<td class="attachements-title" colspan="4"><span>' . get_string('attachments', 'local_incidence_report') . '</span></td>';
    $html .= '</tr>';
    $html .= '<tr>';
    $html .= '<td class="attachements-list" colspan="4">';
    $html .= implode($br, $out);
    $html .= '</td></tr>';

    return $html;
}

function local_incidence_report_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    global $DB;

    if ($filearea != 'attachments') {
        return false;
    }

    $itemid = (int) array_shift($args);

    if ($itemid != 0) {
        // This returned true on sample function... but didn't worked for our use.
    }

    $fs = get_file_storage();
    $filename = array_pop($args);

    if (empty($args)) {
        $filepath = '/';
    } else {
        $filepath = '/' . implode('/', $args) . '/';
    }

    $file = $fs->get_file($context->id, 'local_incidence_report', $filearea, $itemid, $filepath, $filename);

    if (!$file) {
        return false;
    }

    send_stored_file($file, 0, 0, true, $options); // download MUST be forced - security!
}

/**
 * 
 * @global type $USER
 * @param type $asarray
 * @return stdClass|array
 */
function local_incidence_report_get_all_params($asarray = true) {
    $fcourse = optional_param('fcourse', null, PARAM_TEXT);
    $ftype = optional_param('ftype', null, PARAM_TEXT);
    $fstatus = optional_param('fstatus', '0', PARAM_TEXT);
    $fmanager = optional_param('fmanager', null, PARAM_TEXT);
    $fusername = optional_param('fusername', null, PARAM_TEXT);
    $sid = optional_param('sid', null, PARAM_TEXT);
    $susername = optional_param('susername', null, PARAM_TEXT);
    $page = optional_param('page', null, PARAM_TEXT);
    $view = optional_param('view', 'user', PARAM_TEXT);

    $fcourse = ($fcourse == '') ? null : $fcourse;
    $ftype = ($ftype == '') ? null : $ftype;
    $fstatus = ($fstatus == '') ? null : $fstatus;
    $fmanager = ($fmanager == '') ? null : $fmanager;
    $fmanager = ($fusername == '') ? null : $fmanager;

    $courseid = optional_param('courseid', null, PARAM_TEXT);
    $courseid = ($courseid == '') ? null : $courseid;
    $fcourse = ($fcourse == null) ? $courseid : $fcourse;

    $fhidden = optional_param('fhidden', '0', PARAM_TEXT);

    // [i] Filtering username cleans other filters IF it comes from the button...
    //     So we detect it because when it comes from the button comes with a parameter that
    //     we don't store... fusernamebutton

    $fusernamebutton = optional_param('fusernamebutton', null, PARAM_TEXT);
    if (($fusernamebutton != null) && ($fusername != '')) {
        // Clear filters
        $fstatus = null;
        $ftype = null;
    }

    global $USER;
    if (is_siteadmin($USER)) {
        $view = 'manager';
    }

    if ($asarray) {
        $params = array(
            'fcourse' => $fcourse,
            'ftype' => $ftype,
            'fstatus' => $fstatus,
            'fmanager' => $fmanager,
            'fusername' => $fusername,
            'fhidden' => $fhidden,
            'sid' => $sid,
            'susername' => $susername,
            'page' => $page,
            'view' => $view,
        );
    } else {
        $params = new stdClass;
        $params->filter = new stdClass;
        $params->filter->course = $fcourse;
        $params->filter->type = $ftype;
        $params->filter->status = $fstatus;
        $params->filter->manager = $fmanager;
        $params->filter->username = $fusername;
        $params->filter->hidden = $fhidden;
        $params->search = new stdClass;
        $params->search->id = $sid;
        $params->search->username = $susername;
        $params->page = $page;
        $params->view = $view;
    }

    return $params;
}

function local_incidence_report_parse_param_as_list($param) {
    $paramlist = '';

    if (is_array($param)) {
        foreach ($param as $paramid) {
            $paramlist .= $paramid . ', ';
        }
        $paramlist = substr($paramlist, 0, -2);
    } else {
        return false;
    }

    return $paramlist;
}

function local_incidence_report_get_managed_courses($courseid = null) {
    global $USER;
    global $DB;

    $sql = "SELECT id
              FROM {course}
             WHERE category in (
                     SELECT instanceid as category
                       FROM {role_assignments} AS ra, {context} as ctx 
                      WHERE ra.contextid = ctx.id
                            AND roleid = :roleid
                            AND contextlevel = :contextlevel
                            AND userid = :userid 
                   )";

    $sql_params = array(
        'roleid' => LOCAL_INCIDENCE_REPORT_DEFAULT_ROLE_MANAGER,
        'contextlevel' => CONTEXT_COURSECAT,
        'userid' => $USER->id
    );

    $records = $DB->get_records_sql($sql, $sql_params);

    $courseids = array();

    foreach ($records as $record) {
        $courseids[] = $record->id;
    }

    if ($courseid == null) {
        return $courseids;
    } else {
        return in_array($courseid, $courseids);
    }
}

function local_incidence_report_manages_course($courseid) {
    return local_incidence_report_get_managed_courses($courseid = null);
}

function local_incidence_report_parse_message_tokens($message, $incidence) {
    global $DB;

    if ($incidence->courseid) {
        $course = get_course($incidence->courseid);
    } else {
        $course = new stdClass();
        $course->fullname = "Incidencia General";
    }

    if ($incidence->userid != 0) {
        $reporter = $DB->get_record('user', array('id' => $incidence->userid));
    } else {
        $reporter = new stdClass();
        $reporter->firstname = $incidence->fullname;
        $reporter->lastname = '';
    }

    // SAMPLE {moodle_url}/local/incidence_report/incidence_report.php?action=view&incidenceid=34
    $baseurl = '/local/incidence_report/incidence_report.php';
    $url_params = array(
        'action' => 'view',
        'incidenceid' => $incidence->id,
    );
    $url_incidencia = new moodle_url($baseurl, $url_params);

    $tokens = array(
        '{id_incidencia}' => $incidence->id,
        '{texto_incidencia_inicial}' => $incidence->message,
        '{nombre_del_curso}' => $course->fullname,
        '{nombre_del_usuario}' => $reporter->firstname . ' ' . $reporter->lastname,
        '{url_incidencia}' => $url_incidencia,
        '{token}' => $incidence->token,
        '{points}' => $incidence->points,
    );

    $find = array_keys($tokens);
    $replace = array_values($tokens);

    return str_replace($find, $replace, $message);
}

/**
 * Sends an email on different events
 * 
 * @global type $DB
 * @global type $USER
 * @param int as defined by LOCAL_INCIDENCE_REPORT_MAIL_* constants
 * @param int|string|object this could be the incidence id as  numeric string or int or the incidence object. In the case of
 *                          unlogged user, as the id would be '0', the incidence token as unique string is given.
 * @return type
 */
function local_incidence_report_email($event, $incidence, $details = null) {

    global $DB;
    global $USER;

    // The $incidence could be ID or object but in case of unlogged user, ID would be TOKEN.
    if (is_numeric($incidence)) {
        $incidenceid = $incidence;
        $incidence = $DB->get_record('local_incidence_report_msgs', array('id' => $incidenceid));
    } elseif (is_object($incidence)) {
        $incidenceid = $incidence->id;
    } else {
        $incidences = $DB->get_records('local_incidence_report_msgs', array('token' => $incidence));
        if (count($incidences) > 0) {
            $incidence = reset($incidences);
            $incidenceid = $incidence->id;
        } else {
            $incidence = false;
        }
    }

    if (!$incidence) {
        echo local_incidence_report_notice(
            get_string('send_mail_incidence_not_found', 'local_incidence_report'),
            LOCAL_INCIDENCE_REPORT_NOTICE_ERROR
        );

        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

    $subject = get_string('mailing_subject', 'local_incidence_report');

    // [!!!] This should be "forced" in some systems as the mail server will require a sender mail equal to the one of the user logged
    // in the mail server. So we could get the smtp username through config but this can be the given email or not... no way to set
    // this here...
    $from = core_user::get_user(LOCAL_INCIDENCE_REPORT_MAILING_USERID);

    ob_start();

    switch ($event) {
        case LOCAL_INCIDENCE_REPORT_EMAIL_ASSIGNED:
            // The incidence has been assigned to a new manager.
            // $incidence is the PARENT incidence
            // The new manager is $incidence->manager
            $message = get_config('local_incidence_report', 'mail_template_h');
            $message = local_incidence_report_parse_message_tokens($message, $incidence);

            $manager = $DB->get_record('user', array('id' => $incidence->manager));

            $result = email_to_user($manager, $from, $subject, html_to_text($message), $message);

            if (!$result) {
                echo "No se ha podido informar por correo.";
            } else {
                echo "Se ha informado por correo.";
            }

            break;
        case LOCAL_INCIDENCE_REPORT_EMAIL_NEW:
            // In this case $incidence could be token or incidenceid
            $token = null;

            $token = $incidence->token;

            if ($token != null) {
                // A - Mail token to non identified user
                $message = get_config('local_incidence_report', 'mail_template_a');
                //$message = str_replace('{token}', $incidence->token, $message);
                $message = local_incidence_report_parse_message_tokens($message, $incidence);

                // Fake user object, as the report has been submited without identification (can't login...)
                $user = new stdClass();
                $user->id = 1;
                $user->deleted = 0;
                $user->email = $incidence->email;
                $user->username = 'anonymous';

                $result = email_to_user($user, $from, $subject, html_to_text($message), $message);

                if (!$result) {
                    echo local_incidence_report_notice(
                        get_string('send_mail_incidence_mail_error', 'local_incidence_report'),
                        LOCAL_INCIDENCE_REPORT_NOTICE_ERROR
                    );
                    break;
                }
            }

            if ($token != null) {
                // G - New login incidence
                $message = get_config('local_incidence_report', 'mail_template_g');

                /*
                  if ($details != null) {
                  $message .= '<br><div style="border: 1px solid gray; background-color: lightyellow; padding: 1em; margin: 1em;">';
                  $message .= '<b>' . $details['fullname'] . '</b><br><br>';
                  $message .= $details['message'];
                  $message .= '</div>';
                  echo ''; // This seems to solve
                  }
                 */
            } else {
                // C - New incidence report
                $message = get_config('local_incidence_report', 'mail_template_c');

                /*
                  if ($details != null) {
                  $message .= '<br><div style="border: 1px solid gray; background-color: lightyellow; padding: 1em; margin: 1em;">';
                  $message .= '<b>' . $details['userid'] . '</b><br><br>';
                  $message .= $details['message'];
                  $message .= '</div>';
                  echo '';
                  }
                 */
            }
            $message = local_incidence_report_parse_message_tokens($message, $incidence);

            $managergroups = local_incidence_report_get_managers($incidenceid, true);

            $foundmanagers = 0;
            foreach ($managergroups as $managers) {
                if (count($managers)) {
                    foreach ($managers as $manager) {
                        $foundmanagers++;
                        $result = email_to_user($manager, $from, $subject, html_to_text($message), $message);
                    }
                }
            }

            if ($foundmanagers === 0) {
                $admins = local_incidence_report_get_admins();
                foreach ($admins as $admin) {
                    $result = email_to_user($admin, $from, $subject, html_to_text($message), $message);
                }
            }

            break;
        case LOCAL_INCIDENCE_REPORT_EMAIL_ANSWER:
            $parentincidence = $DB->get_record('local_incidence_report_msgs', array('id' => $incidence->answers));

            $useranswers = ($parentincidence->userid == $incidence->userid);

            if ($useranswers) {
                // D - Answer by user
                $message = get_config('local_incidence_report', 'mail_template_d');
            } else {
                // B - Answer by manager or admin
                $message = get_config('local_incidence_report', 'mail_template_b');
            }
            echo '';
            //$message = str_replace('{id}', $parentincidence->id, $message);
            $message = local_incidence_report_parse_message_tokens($message, $parentincidence);

            if ($useranswers) {
                if (($parentincidence->manager == null) || ($parentincidence->manager == 0)) {
                    $admins = local_incidence_report_get_admins();
                    foreach ($admins as $admin) {
                        $result = email_to_user($admin, $from, $subject, html_to_text($message), $message);
                    }
                } else {
                    $manager = $DB->get_record('user', array('id' => $parentincidence->manager));
                    $result = email_to_user($manager, $from, $subject, html_to_text($message), $message);
                }
            } else {
                if ($parentincidence->userid == 0) {
                    $user = new stdClass();
                    $user->id = 1;
                    $user->deleted = 0;
                    $user->email = $parentincidence->email;
                    $user->username = 'anonymous';
                } else {
                    $user = $DB->get_record('user', array('id' => $parentincidence->userid));
                }
                $result = email_to_user($user, $from, $subject, html_to_text($message), $message);
            }

            break;
        case LOCAL_INCIDENCE_REPORT_EMAIL_EVALUATED:
            $parentincidence = $DB->get_record('local_incidence_report_msgs', array('id' => $incidence->id));

            $message = get_config('local_incidence_report', 'mail_template_e');
            $message = local_incidence_report_parse_message_tokens($message, $parentincidence);

            $manager = $DB->get_record('user', array('id' => $parentincidence->manager));
            if ($manager) {
                $result = email_to_user($manager, $from, $subject, html_to_text($message), $message);
            }
            break;
        case LOCAL_INCIDENCE_REPORT_EMAIL_CLOSED:
        case LOCAL_INCIDENCE_REPORT_EMAIL_TIMEDOUT:
            // [i] Right now is the MANAGER who closes the incidence. So we need to mail to the user
            // asking for an evaluation. The evaluation will de done in the incidence URL.
            // [!!!] So THIS should send an EMAIL to the USER telling that a MANAGER has closed it...

            $parentincidence = $DB->get_record('local_incidence_report_msgs', array('id' => $incidence->id));

            $manager = $DB->get_record('user', array('id' => $parentincidence->manager));

            // I - Clossing incidence
            // F - Expiring incidence
            $template = ($event == LOCAL_INCIDENCE_REPORT_EMAIL_CLOSED) ? 'mail_template_i' : 'mail_template_f';
            $message = get_config('local_incidence_report', $template);
            $message = local_incidence_report_parse_message_tokens($message, $parentincidence);

            if ($parentincidence->token != null) {
                // Login incidence
                $user = new stdClass();
                $user->id = 1;
                $user->deleted = 0;
                $user->email = $incidence->email;
                $user->username = 'anonymous';
            } else {
                // Registered incidence
                // $user = $USER; // ?????
                $user = core_user::get_user($parentincidence->userid);
            }
            // $manager = $DB->get_record('user', array('id' => $parentincidence->manager));

            $additionals = local_incicence_report_email_get_additional_users_for_incidence($parentincidence);

            ob_start();
            echo '<br><br>';
            echo 'Incidencia Padre:' . $parentincidence->id;
            echo '<br><br>';
            echo 'Avisar adicionalmente a:';
            var_dump($additionals);
            $ob_data = ob_get_contents();
            ob_end_clean();

            //$message .= $ob_data;

            $result = email_to_user($user, $from, $subject, html_to_text($message), $message);

            foreach ($additionals as $additional) {
                $result = email_to_user($additional, $from, $subject, html_to_text($message), $message);
            }

            //if ($manager) {
            //    $result = email_to_user($manager, $from, $subject, html_to_text($message), $message);
            //}

            /*
              if ($incidence->points != null) {
              // E - Closing incidence
              $message = get_config('local_incidence_report', 'mail_template_e');
              } else {
             */

            break;
        default:
    }

    $html = ob_get_contents();
    ob_end_clean();
    return $html;
}

function local_incidence_report_can_user_evaluate($incidenceid) {
    global $DB;

    $incidence = $DB->get_record('local_incidence_report_msgs', array('id' => $incidenceid));

    if (!$incidence) {
        return false;
    } else {
        if (($incidence->status == LOCAL_INCIDENCE_REPORT_STATUS_CLOSED) && ($incidence->points == -1)) {
            return true;
        }
    }

    return false;
}

function local_incidence_report_evaluate_support($incidenceid, $points) {
    global $DB;

    $sql = "UPDATE {local_incidence_report_msgs}
               SET points=:points,
                   modified=:modified,
             WHERE id=:id";
    $params = array(
        'id' => $incidenceid,
        'points' => $points,
        'modified' => time(),
    );

    $result = $DB->update_record('local_incidence_report_msgs', $params);

    // Send email

    echo local_incidence_report_notice("El soporte ha sido evaluado...", LOCAL_INCIDENCE_REPORT_NOTICE_SUCCESS);

    echo local_incidence_report_email(LOCAL_INCIDENCE_REPORT_EMAIL_EVALUATED, $incidenceid);

    return;
}

function local_incidence_report_point_to_string($points) {
    switch ($points) {
        case 0:
            return 'NS/NC';
        case 1:
            return 'Muy malo';
        case 2:
            return 'Malo';
        case 3:
            return 'Bueno';
        case 4:
            return 'Muy Bueno';
        case 5:
            return 'Excelente';
        default:
            return '';
    }
}


function local_incicence_report_email_get_additional_users_for_incidence($incidence) {
    global $DB;

    $users = [];

    // We don't send "additional" to the user... that would be "normal" if neede...
    // $userid = $incidence->userid;
    // This one is clossing... so must be ignored
    //$manager = $incidence->manager;

    // This adds the additional managers to the list
    //$sql = 'SELECT DISTINCT(manager) FROM {local_incidence_report_msgs} WHERE answers=:incidenceid';
    //$params = $incidence->id;
    //$records = $DB->get_records_sql($sql, $params);
    //
    //foreach ($records as $record) {
    //    if ($record->manager != $manager) {
    //        $users[] = core_user::get_user($record->manager);
    //    }
    //}

    // This checks if someone else should know...
    switch ($incidence->type) {
        case LOCAL_INCIDENCE_REPORT_TYPE_SECRETARIA_MATRICULA:
            $users = local_incidence_report_email_get_additional_users_for_incidence($incidence, LOCAL_INCIDENCE_REPORT_TYPE_SECRETARIA_MATRICULA_EMAILTO);
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_SECRETARIA_EXPEDIENTE:
            $users = local_incidence_report_email_get_additional_users_for_incidence($incidence, LOCAL_INCIDENCE_REPORT_TYPE_SECRETARIA_EXPEDIENTE_EMAILTO);
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_SECRETARIA_CONVOCATORIA:
            $users = local_incidence_report_email_get_additional_users_for_incidence($incidence, LOCAL_INCIDENCE_REPORT_TYPE_SECRETARIA_CONVOCATORIA_EMAILTO);
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_SECRETARIA_BAJA:
            $users = local_incidence_report_email_get_additional_users_for_incidence($incidence, LOCAL_INCIDENCE_REPORT_TYPE_SECRETARIA_BAJA_EMAILTO);
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_SECRETARIA_ACTAS:
            $users = local_incidence_report_email_get_additional_users_for_incidence($incidence, LOCAL_INCIDENCE_REPORT_TYPE_SECRETARIA_ACTAS_EMAILTO);
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_SECRETARIA_CERTIFICADOS:
            $users = local_incidence_report_email_get_additional_users_for_incidence($incidence, LOCAL_INCIDENCE_REPORT_TYPE_SECRETARIA_CERTIFICADOS_EMAILTO);
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_SECRETARIA_COMISIONES:
            $users = local_incidence_report_email_get_additional_users_for_incidence($incidence, LOCAL_INCIDENCE_REPORT_TYPE_SECRETARIA_COMISIONES_EMAILTO);
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_PLATAFORMA_LOGIN:
            $users = local_incidence_report_email_get_additional_users_for_incidence($incidence, LOCAL_INCIDENCE_REPORT_TYPE_PLATAFORMA_LOGIN_EMAILTO);
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_PLATAFORMA_CURSO:
            $users = local_incidence_report_email_get_additional_users_for_incidence($incidence, LOCAL_INCIDENCE_REPORT_TYPE_PLATAFORMA_CURSO_EMAILTO);
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_PLATAFORMA_SECCION: // DEPRECATED
            $users = local_incidence_report_email_get_additional_users_for_incidence($incidence, LOCAL_INCIDENCE_REPORT_TYPE_PLATAFORMA_SECCION_EMAILTO); // DEPRECATED
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_PLATAFORMA_FALLO: // DEPRECATED
            $users = local_incidence_report_email_get_additional_users_for_incidence($incidence, LOCAL_INCIDENCE_REPORT_TYPE_PLATAFORMA_FALLO_EMAILTO); // DEPRECATED
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_PLATAFORMA_MATRICULA: // DEPRECATED
            $users = local_incidence_report_email_get_additional_users_for_incidence($incidence, LOCAL_INCIDENCE_REPORT_TYPE_PLATAFORMA_MATRICULA_EMAILTO); // DEPRECATED
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_PLATAFORMA_RECURSO: // DEPRECATED
            $users = local_incidence_report_email_get_additional_users_for_incidence($incidence, LOCAL_INCIDENCE_REPORT_TYPE_PLATAFORMA_RECURSO_EMAILTO); // DEPRECATED
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_PLATAFORMA_CONTENIDOS: // DEPRECATED
            $users = local_incidence_report_email_get_additional_users_for_incidence($incidence, LOCAL_INCIDENCE_REPORT_TYPE_PLATAFORMA_CONTENIDOS_EMAILTO); // DEPRECATED
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_PLATAFORMA_CORRECCION: // DEPRECATED
            $users = local_incidence_report_email_get_additional_users_for_incidence($incidence, LOCAL_INCIDENCE_REPORT_TYPE_PLATAFORMA_CORRECCION_EMAILTO); // DEPRECATED
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_PLATAFORMA_CALIFICADOR: // DEPRECATED
            $users = local_incidence_report_email_get_additional_users_for_incidence($incidence, LOCAL_INCIDENCE_REPORT_TYPE_PLATAFORMA_CALIFICADOR_EMAILTO); // DEPRECATED
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_PLATAFORMA_ENTREGA: // DEPRECATED
            $users = local_incidence_report_email_get_additional_users_for_incidence($incidence, LOCAL_INCIDENCE_REPORT_TYPE_PLATAFORMA_ENTREGA_EMAILTO); // DEPRECATED
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_PLAGIO: //DEPRECATED
            $users = local_incidence_report_email_get_additional_users_for_incidence($incidence, LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_PLAGIO_EMAILTO); //DEPRECATED
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_CALIFICACION: //DEPRECATED
            $users = local_incidence_report_email_get_additional_users_for_incidence($incidence, LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_CALIFICACION_EMAILTO); //DEPRECATED
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_EXAMEN_FECHA: //DEPRECATED
            $users = local_incidence_report_email_get_additional_users_for_incidence($incidence, LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_EXAMEN_FECHA_EMAILTO); //DEPRECATED
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_EXAMEN_LUGAR: //DEPRECATED
            $users = local_incidence_report_email_get_additional_users_for_incidence($incidence, LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_EXAMEN_LUGAR_EMAILTO); //DEPRECATED
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_CONVOCATORIA_JE: //DEPRECATED
            $users = local_incidence_report_email_get_additional_users_for_incidence($incidence, LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_CONVOCATORIA_JE_EMAILTO); //DEPRECATED
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_EXAMEN_RECOGIDA: //DEPRECATED
            $users = local_incidence_report_email_get_additional_users_for_incidence($incidence, LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_EXAMEN_RECOGIDA_EMAILTO); //DEPRECATED
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_ACTAS: //DEPRECATED
            $users = local_incidence_report_email_get_additional_users_for_incidence($incidence, LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_ACTAS_EMAILTO); //DEPRECATED
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_TAREAS: //DEPRECATED
            $users = local_incidence_report_email_get_additional_users_for_incidence($incidence, LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_TAREAS_EMAILTO); //DEPRECATED
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_ACCESO: //DEPRECATED
            $users = local_incidence_report_email_get_additional_users_for_incidence($incidence, LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_ACCESO_EMAILTO); //DEPRECATED
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_ENTREGAS: //DEPRECATED
            $users = local_incidence_report_email_get_additional_users_for_incidence($incidence, LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_ENTREGAS_EMAILTO); //DEPRECATED
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_CALIFICACIONES: //DEPRECATED
            $users = local_incidence_report_email_get_additional_users_for_incidence($incidence, LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_CALIFICACIONES_EMAILTO); //DEPRECATED
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_SECCION:
            $users = local_incidence_report_email_get_additional_users_for_incidence($incidence, LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_SECCION_EMAILTO);
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_FALLO:
            $users = local_incidence_report_email_get_additional_users_for_incidence($incidence, LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_FALLO_EMAILTO);
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_MATRICULA:
            $users = local_incidence_report_email_get_additional_users_for_incidence($incidence, LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_MATRICULA_EMAILTO);
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_RECURSO:
            $users = local_incidence_report_email_get_additional_users_for_incidence($incidence, LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_RECURSO_EMAILTO);
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_CONTENIDOS:
            $users = local_incidence_report_email_get_additional_users_for_incidence($incidence, LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_CONTENIDOS_EMAILTO);
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_CORRECCION:
            $users = local_incidence_report_email_get_additional_users_for_incidence($incidence, LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_CORRECCION_EMAILTO);
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_CALIFICADOR:
            $users = local_incidence_report_email_get_additional_users_for_incidence($incidence, LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_CALIFICADOR_EMAILTO);
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_ENTREGA:
            $users = local_incidence_report_email_get_additional_users_for_incidence($incidence, LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_ENTREGA_EMAILTO);
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_EXPEDIENTE:
            $users = local_incidence_report_email_get_additional_users_for_incidence($incidence, LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_EXPEDIENTE_EMAILTO);
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_CONVOCATORIA:
            $users = local_incidence_report_email_get_additional_users_for_incidence($incidence, LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_CONVOCATORIA_EMAILTO);
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_BAJA:
            $users = local_incidence_report_email_get_additional_users_for_incidence($incidence, LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_BAJA_EMAILTO);
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_FIRMA_ACTAS:
            $users = local_incidence_report_email_get_additional_users_for_incidence($incidence, LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_FIRMA_ACTAS_EMAILTO);
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_CERTIFICADOS:
            $users = local_incidence_report_email_get_additional_users_for_incidence($incidence, LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_CERTIFICADOS_EMAILTO);
            break;
        case LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_COMISIONES:
            $users = local_incidence_report_email_get_additional_users_for_incidence($incidence, LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_COMISIONES_EMAILTO);
            break;
    }

    return $users;
}

function local_incidence_report_email_get_additional_users_for_incidence($incidence, $profilearray, $debug = false) {
    global $DB;

    $userids = [];
    $users = [];
    foreach ($profilearray as $profile) {
        $users_candidate = local_incidence_report_profile_get($profile, CONTEXT_COURSE, $incidence->courseid, $debug);
        foreach ($users_candidate as $user) {
            if ((in_array($user->id, $userids) === false)
                && ($user->id != $incidence->userid)
                && ($user->id != $incidence->manager)
            ) {
                $userids[] = $user->id;
                $users[] = core_user::get_user($user->id);
            }
        }
    }

    if ($debug) {
        echo '<pre>';
        var_dump($users);
        echo '</pre>';
    }

    return $users;
}

function local_incidence_report_get_userids_for_profile(array $profiles, $contextlevel = null, $instanceid = null) {
    global $DB;

    $sql = 'SELECT DISTINCT ra.userid
              FROM {role_assignments} ra
                   JOIN {role} AS r ON (r.id = ra.roleid)
                   JOIN {context} AS ctx ON (ctx.id = ra.contextid)
             WHERE r.shortname IN (' . implode(',', $profiles) . ')
                   AND ctx.contextlevel=:contextlevel
                   AND ctx.instanceid=:instanceid';

    $params = [
        'contextlevel' => $contextlevel,
        'instanceid' => $instanceid
    ];

    $result = $DB->get_records_sql($sql, $params);

    return array_keys($result);
}
