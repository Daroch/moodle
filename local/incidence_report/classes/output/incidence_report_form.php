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

class local_incidence_report_review_incidence_by_code_form extends moodleform
{

    function definition()
    {
        $mform = $this->_form;

        $action = optional_param('action', null, PARAM_TEXT);
        $mform->addElement('hidden', 'action', $action);
        $mform->setType('action', PARAM_TEXT);

        $attributes = array(
            'autocomplete' => 'off',
            'value' => ' ',
        );


        $mform->addElement('text', 'code', get_string('submit_code', 'local_incidence_report'), $attributes);
        $mform->setType('code', PARAM_TEXT);

        $this->add_action_buttons(false, get_string('submit_review_button', 'local_incidence_report'));

        return $mform;
    }

    function definition_after_data()
    {
        $mform = $this->_form;
    }

    function get_data()
    {
        $data = parent::get_data();
        if ($data && $data != null) {
            //var_dump($data);
        }
        return $data;
    }

    function save_data()
    {
        return;
    }

    function update_data($event_id)
    {
        global $USER;
        global $DB;

        $data = $this->get_data();

        return;
    }
}

class local_incidence_report_new_incidence_form extends moodleform
{

    function definition()
    {
        $mform = $this->_form;

        $courseid = optional_param('fcourse', 0, PARAM_INT);

        if ($courseid != 0) {
            $course = get_course($courseid);
            $mform->addElement(
                'html',
                local_incidence_report_notice(
                    get_string('add_new_report_on_course', 'local_incidence_report', $course->fullname),
                    LOCAL_INCIDENCE_REPORT_NOTICE_INFO
                )
            );
        }

        $mform->addElement('hidden', 'courseid', $courseid);
        $mform->setType('courseid', PARAM_INT);

        $action = optional_param('action', null, PARAM_TEXT);

        $mform->addElement('hidden', 'action', $action);
        $mform->setType('action', PARAM_TEXT);

        if (!isloggedin()) {
            $options = array(
                LOCAL_INCIDENCE_REPORT_TYPE_PLATAFORMA_LOGIN => get_string(
                    'submit_type_string_' . LOCAL_INCIDENCE_REPORT_TYPE_PLATAFORMA_LOGIN,
                    'local_incidence_report'
                ),
            );
        } else {
            $options = array();

            $isalumno = local_incidence_report_profile_check(LOCAL_INCIDENCE_REPORT_PROFILE_ALUMNO);
            $istutor = local_incidence_report_profile_check(LOCAL_INCIDENCE_REPORT_PROFILE_TUTOR);
            $isprofesor = local_incidence_report_profile_check(LOCAL_INCIDENCE_REPORT_PROFILE_PROFESOR);
            $iscoordinador = local_incidence_report_profile_check(LOCAL_INCIDENCE_REPORT_PROFILE_COORDINADOR);
            $isjefatura = local_incidence_report_profile_check(LOCAL_INCIDENCE_REPORT_PROFILE_JEFATURA);
            $issecretaria = local_incidence_report_profile_check(LOCAL_INCIDENCE_REPORT_PROFILE_SECRETARIA);
            $ismoodle = local_incidence_report_profile_check(LOCAL_INCIDENCE_REPORT_PROFILE_MOODLE);
            $isfederacion = local_incidence_report_profile_check(LOCAL_INCIDENCE_REPORT_PROFILE_FEDERACION);

            $mform->addElement(
                'html',
                local_incidence_report_notice(
                    get_string('submit_comment_help', 'local_incidence_report'),
                    LOCAL_INCIDENCE_REPORT_NOTICE_INFO
                )
            );

            // Esto es para debugear... :)
            $verbose_position = false;
            if ($verbose_position) {
                if ($isalumno) {
                    $mform->addElement('html', ' isalumno ');
                }
                if ($istutor) {
                    $mform->addElement('html', ' istutor ');
                }
                if ($isprofesor) {
                    $mform->addElement('html', ' isprofesor ');
                }
                if ($iscoordinador) {
                    $mform->addElement('html', ' iscoordinador ');
                }
                if ($isjefatura) {
                    $mform->addElement('html', ' isjefatura ');
                }
                if ($issecretaria) {
                    $mform->addElement('html', ' issecretaria ');
                }
                if ($ismoodle) {
                    $mform->addElement('html', ' ismoodle ');
                }
                if ($isfederacion) {
                    $mform->addElement('html', ' isfederacion ');
                }
            }


            ob_start();
            $customs_count = get_config('local_incidence_report', 'customs_count');
            // Repasar todos los grupos para ver si en alguno está el id del curso actual
            $aux = $customs_count;
            $configlist = [];
            $allowed_incidences = [];
            while ($aux > 0) {
                $courselist = get_config('local_incidence_report', 'custom_courselist_' . $aux);
                $courselist = array_map('trim', explode(',', $courselist));
                if (in_array($courseid, $courselist)) {
                    $configlist[] = $aux;
                }
                $aux--;
            }

            foreach ($configlist as $groupid) {
                $incidencelist = get_config('local_incidence_report', 'custom_incidencelist_' . $groupid);
                $incidencelist = array_map('trim', explode(',', $incidencelist));
                $allowed_incidences = array_merge($allowed_incidences, $incidencelist);
            }

            $temp = [];
            $all_configured_incidences = [
                201,
                202,
                312,
                313,
                314,
                315,
                316,
                317,
                318,
                319,
                320,
                321,
                322,
                323,
                324,
                325,
            ];

            if (count($allowed_incidences) == 0) {
                $temp = $all_configured_incidences;
            } else {
                foreach ($allowed_incidences as $key => $value) {
                    if (in_array($value, $all_configured_incidences)) {
                        $temp[] = $value;
                    }
                }
            }

            $allowed_incidences = $temp;

            $temp_debug = ob_get_contents();
            ob_end_clean();
            $mform->addElement('html', $temp_debug);

            // Solicitudes asignadas a un único perfil
            if ($courseid != 0) {
                // ALUMNO
                if ($isalumno) {
                    $context_incidences = [
                        //LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_TAREAS,
                        //LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_ACCESO,
                        //LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_ENTREGAS,
                        //LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_CALIFICACIONES,
                        LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_SECCION,
                        LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_ENTREGA,
                        LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_EXPEDIENTE,
                        LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_CONVOCATORIA,
                        LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_BAJA,
                        LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_CERTIFICADOS,
                    ];
                    foreach ($context_incidences as $incidence_type) {
                        if (in_array($incidence_type, $allowed_incidences)) {
                            $options[$incidence_type] = get_string('submit_type_string_' . $incidence_type, 'local_incidence_report');
                        }
                    }
                    // Reset context incidences just in case...
                    $context_incidences = [];
                } // TUTOR
                if ($istutor) {
                    $context_incidences = [
                        //LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_PLAGIO,
                        //LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_EXAMEN_LUGAR,
                        //LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_ACTAS,
                        LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_FIRMA_ACTAS,
                        LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_CORRECCION,
                        LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_CALIFICADOR,
                    ];
                    foreach ($context_incidences as $incidence_type) {
                        if (in_array($incidence_type, $allowed_incidences)) {
                            $options[$incidence_type] = get_string('submit_type_string_' . $incidence_type, 'local_incidence_report');
                        }
                    }
                    // Reset context incidences just in case...
                    $context_incidences = [];
                }
                // COORDINADOR
                if ($iscoordinador) {
                    $context_incidences = [
                        LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_COMISIONES
                    ];
                    foreach ($context_incidences as $incidence_type) {
                        if (in_array($incidence_type, $allowed_incidences)) {
                            $options[$incidence_type] = get_string('submit_type_string_' . $incidence_type, 'local_incidence_report');
                        }
                    }
                    // Reset context incidences just in case...
                    $context_incidences = [];
                }
                // Solicitudes asignadas a múltiples perfiles
                if ($istutor || $iscoordinador) {
                    $context_incidences = [
                        //LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_CONVOCATORIA_JE,
                        //LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_EXAMEN_RECOGIDA,
                        LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_FALLO,
                        LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_CONTENIDOS,
                    ];
                    foreach ($context_incidences as $incidence_type) {
                        if (in_array($incidence_type, $allowed_incidences)) {
                            $options[$incidence_type] = get_string('submit_type_string_' . $incidence_type, 'local_incidence_report');
                        }
                    }
                    // Reset context incidences just in case...
                    $context_incidences = [];
                }
                if ($isalumno || $istutor) {
                    $context_incidences = [
                        LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_MATRICULA,
                        LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_RECURSO,
                        //LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_CALIFICACION,
                        //LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_EXAMEN_FECHA,
                    ];
                    foreach ($context_incidences as $incidence_type) {
                        if (in_array($incidence_type, $allowed_incidences)) {
                            $options[$incidence_type] = get_string('submit_type_string_' . $incidence_type, 'local_incidence_report');
                        }
                    }
                    // Reset context incidences just in case...
                    $context_incidences = [];
                }
            } else {
                // ALUMNO
                if ($isalumno) {
                    $context_incidences = [
                        //LOCAL_INCIDENCE_REPORT_TYPE_SECRETARIA_EXPEDIENTE,
                        //LOCAL_INCIDENCE_REPORT_TYPE_SECRETARIA_CONVOCATORIA,
                        //LOCAL_INCIDENCE_REPORT_TYPE_SECRETARIA_BAJA,
                        //LOCAL_INCIDENCE_REPORT_TYPE_SECRETARIA_CERTIFICADOS,
                        LOCAL_INCIDENCE_REPORT_TYPE_PLATAFORMA_CURSO,
                        //LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_SECCION,
                        //LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_ENTREGA,
                    ];
                    foreach ($context_incidences as $incidence_type) {
                        if (in_array($incidence_type, $allowed_incidences)) {
                            $options[$incidence_type] = get_string('submit_type_string_' . $incidence_type, 'local_incidence_report');
                        }
                    }
                    // Reset context incidences just in case...
                    $context_incidences = [];
                }
                // COORDINADOR
                if ($iscoordinador) {
                    //$options[LOCAL_INCIDENCE_REPORT_TYPE_SECRETARIA_COMISIONES] = get_string('submit_type_string_' . LOCAL_INCIDENCE_REPORT_TYPE_SECRETARIA_COMISIONES, 'local_incidence_report');
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
                    //$options[LOCAL_INCIDENCE_REPORT_TYPE_SECRETARIA_ACTAS] = get_string('submit_type_string_' . LOCAL_INCIDENCE_REPORT_TYPE_SECRETARIA_ACTAS, 'local_incidence_report');
                }
                if ($istutor || $iscoordinador) {
                    //$options[LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_FALLO] = get_string('submit_type_string_' . LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_FALLO, 'local_incidence_report');
                    //$options[LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_MATRICULA] = get_string('submit_type_string_' . LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_MATRICULA, 'local_incidence_report');
                    //$options[LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_RECURSO] = get_string('submit_type_string_' . LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_RECURSO, 'local_incidence_report');
                    //$options[LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_CONTENIDOS] = get_string('submit_type_string_' . LOCAL_INCIDENCE_REPORT_TYPE_CURSOS_CONTENIDOS, 'local_incidence_report');
                }
            }
            ksort($options);
        }

        if (count($options) > 0) {
            $mform->addElement('select', 'type', get_string('submit_type', 'local_incidence_report'), $options);
        }

        $attributes = null;

        if (!isloggedin()) {
            $mform->addElement('text', 'name', get_string('submit_fullname', 'local_incidence_report'), $attributes);
            $mform->setType('name', PARAM_TEXT);

            $mform->addElement('text', 'email', get_string('submit_email', 'local_incidence_report'), $attributes);
            $mform->setType('email', PARAM_TEXT);

            $mform->addElement(
                'html',
                local_incidence_report_notice(
                    get_string('submit_comment_help', 'local_incidence_report'),
                    LOCAL_INCIDENCE_REPORT_NOTICE_INFO
                )
            );
        }

        $mform->addElement('editor', 'comment', get_string('submit_comment', 'local_incidence_report'));
        $mform->setType('comment', PARAM_RAW);

        if (isloggedin()) {
            $mform->addElement(
                'filemanager',
                'attachments',
                get_string('attachments', 'local_incidence_report'),
                null,
                array(
                    'subdirs' => 0,
                    'maxbytes' => 0,
                    'areamaxbytes' => get_config('local_incidence_report', 'max_bytes'),
                    'maxfiles' => get_config('local_incidence_report', 'max_files'),
                    'accepted_types' => array('document', 'image'),
                    'return_types' => FILE_INTERNAL | FILE_EXTERNAL
                )
            );
        }

        //$this->add_action_buttons(false, get_string('submit_button', 'local_incidence_report'));
        $buttonarray = array();
        $buttonarray[] = $mform->createElement(
            'submit',
            'submitbutton',
            get_string('submit_query_button', 'local_incidence_report')
        );
        $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);

        if (!isloggedin()) {
            $mform->addRule('name', get_string('mandatory_name', 'local_incidence_report'), 'required', '', 'client');
            $mform->addRule('email', get_string('mandatory_email', 'local_incidence_report'), 'required', '', 'client');
            $mform->addRule('email', null, 'email', null, 'client');
        }
        $mform->addRule('comment', get_string('mandatory_comment', 'local_incidence_report'), 'required', '', 'client');

        return $mform;
    }

    function definition_after_data()
    {
        $mform = $this->_form;
        return $mform;
    }

    function get_data()
    {
        $data = parent::get_data();
        if ($data && $data != null) {
            // jmazcunan - En algunos casos NO aparece el parámetro type como recuperado
            //             para el formulario, pero si está si lo recupero a mano...
            //             No se qué pasa, pero lo añado a mano si no aparece.
            $type = optional_param('type', null, PARAM_TEXT);
            if ($type != null) {
                $data->type = $type;
            }
        }
        return $data;
    }

    function save_data()
    {
        global $USER;

        $data = $this->get_data();

        $userid = $USER->id;

        if (!isloggedin()) {
            $dataobject = array(
                //'answers' => ,
                //'token' => $token,
                'userid' => $userid,
                'fullname' => $data->name,
                'courseid' => $data->courseid,
                'email' => $data->email,
                'type' => $data->type,
                'message' => $data->comment['text'],
                'status' => LOCAL_INCIDENCE_REPORT_STATUS_SENT,
                'timestamp' => time(),
            );
        } else {
            $dataobject = array(
                //'answers' => ,
                //'token' => $token,
                'userid' => $userid,
                //'fullname' => $data->name,
                'courseid' => $data->courseid,
                //'email' => $data->email,
                'type' => (isset($data->type)) ? $data->type : null,
                'message' => $data->comment['text'],
                'status' => LOCAL_INCIDENCE_REPORT_STATUS_SENT,
                'timestamp' => time(),
            );
        }

        $token = local_incidence_report_save_incidence($dataobject);

        return $token;
    }

    function update_data($event_id)
    {
        global $USER;
        global $DB;

        $data = $this->get_data();

        return;
    }
}
class local_incidence_report_answer_incidence_form extends moodleform
{

    public $incidenceid = 0;
    public $manager = 0;

    function __construct($incidenceid, $manager = null)
    {
        $this->incidenceid = $incidenceid;
        if ($manager == null)
            $manager = 0;
        $this->manager = $manager;

        parent::__construct();
    }

    function definition()
    {
        global $USER;
        global $DB;

        $isadmin = is_siteadmin($USER->id);

        $mform = $this->_form;

        $mform->addElement('hidden', 'action', 'view');
        $mform->setType('action', PARAM_TEXT);

        $mform->addElement('hidden', 'incidenceid', $this->incidenceid);
        $mform->setType('incidenceid', PARAM_INT);

        $incidence = $DB->get_record('local_incidence_report_msgs', array('id' => $this->incidenceid));
        if ($incidence) {
            if ($incidence->token != null) {
                $mform->addElement('hidden', 'token', $incidence->token);
                $mform->setType('token', PARAM_INT);
            }
        }

        $mform->addElement('hidden', 'incidenceid', $this->incidenceid);
        $mform->setType('incidenceid', PARAM_INT);

        $mform->addElement('editor', 'comment', '');
        $mform->setType('comment', PARAM_RAW);

        //$this->add_action_buttons(false, get_string('submit_answer_button', 'local_incidence_report'));

        if (isloggedin()) {
            $mform->addElement(
                'filemanager',
                'attachments',
                get_string('attachments', 'local_incidence_report'),
                null,
                array(
                    'subdirs' => 0,
                    'maxbytes' => 0,
                    'areamaxbytes' => get_config('local_incidence_report', 'max_bytes'),
                    'maxfiles' => get_config('local_incidence_report', 'max_files'),
                    'accepted_types' => array('document', 'image'),
                    'return_types' => FILE_INTERNAL | FILE_EXTERNAL
                )
            );
        }

        $buttonarray = array();

        $temp = ($this->incidenceid === 0) ? get_string('submit_query_button', 'local_incidence_report') : get_string('submit_answer_button', 'local_incidence_report');
        $buttonarray[] = $mform->createElement(
            'submit',
            'submitbutton',
            $temp,
        );

        if (local_incidence_report_can_user_close($this->incidenceid)) {
            $buttonarray[] = $mform->createElement(
                'submit',
                'closebutton',
                get_string('close_incidence_button', 'local_incidence_report')
            );
        }

        $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);

        // [!!!] Aquí es donde mostramos el bloque de EVALUACIÓN
        //if (local_incidence_report_can_user_evaluate($this->incidenceid)) {
        //    // [!!!] Esto se ejecuta al definir el formulario, pero no hace display!!
        //    // $mform->addElement('html', '<div>This needs to be evaluated</div>');
        //    // Lo he pasado al render... lo hace desde otro formulario.
        //}

        if (local_incidence_report_allow_management($USER->id, $this->incidenceid)) {

            $admins = local_incidence_report_get_admins();
            $options = array();
            if (!$isadmin) {
                $options[$USER->id] = '[YO] ' . $USER->username;
            } else {
                $options['0'] = get_string('unassigned', 'local_incidence_report');
            }

            foreach ($admins as $admin) {
                $options[$admin->id] = '[ADMIN] ' . $admin->username;
            }

            // Admins can also asign to MANAGERS
            if ($isadmin) {
                $managers = local_incidence_report_get_managers($this->incidenceid);

                foreach ($managers as $group => $members) {
                    switch ($group) {
                        case LOCAL_INCIDENCE_REPORT_PROFILE_TUTOR:
                            $groupname = 'TUTOR';
                            break;
                        case LOCAL_INCIDENCE_REPORT_PROFILE_ALUMNO:
                            $groupname = 'ALUMNO';
                            break;
                        case LOCAL_INCIDENCE_REPORT_PROFILE_MOODLE:
                            $groupname = 'MOODLE';
                            break;
                        case LOCAL_INCIDENCE_REPORT_PROFILE_JEFATURA:
                            $groupname = 'JEFATURA';
                            break;
                        case LOCAL_INCIDENCE_REPORT_PROFILE_PROFESOR:
                            $groupname = 'PROFESOR';
                            break;
                        case LOCAL_INCIDENCE_REPORT_PROFILE_FEDERACION:
                            $groupname = 'FEDERACION';
                            break;
                        case LOCAL_INCIDENCE_REPORT_PROFILE_SECRETARIA:
                            $groupname = 'SECRETARIA';
                            break;
                        case LOCAL_INCIDENCE_REPORT_PROFILE_COORDINADOR:
                            $groupname = 'COORDINADOR';
                            break;
                        default:
                            $groupname = 'UNDEFINED';
                            break;
                    }
                    foreach ($members as $member) {
                        $options[$member->id] = '[' . $groupname . '] ' . $member->firstname . ' ' . $member->lastname;
                    }
                }
            }

            $select = $mform->addElement('select', 'manager', get_string('manager_swap', 'local_incidence_report'), $options);

            if ($this->manager != 0) {
                $select->setSelected($this->manager);
            } else {
                $select->setSelected($USER->id);
            }
        }

        return $mform;
    }

    function definition_after_data()
    {
        $mform = $this->_form;
    }

    function get_data()
    {
        $data = parent::get_data();
        if ($data && $data != null) {
            //var_dump($data);
        }
        return $data;
    }

    function save_data()
    {
        global $USER;

        $data = $this->get_data();
        //var_dump($data);

        $userid = $USER->id;

        $manager = null;

        if (isset($data->manager)) {
            $manager = $data->manager;
        }

        $dataobject = array(
            'answers' => $this->incidenceid,
            //'token' => $token,
            'userid' => $userid,
            //'fullname' => $data->name,
            //'email' => $data->email,
            //'type' => $data->type,
            'message' => $data->comment['text'],
            //'status' => LOCAL_INCIDENCE_REPORT_STATUS_SENT,
            'timestamp' => time(),
            'manager' => $manager,
        );

        $result = local_incidence_report_save_incidence_response($dataobject);

        return $result;
    }

    function update_data($event_id)
    {
        global $USER;
        global $DB;

        $data = $this->get_data();

        return;
    }
}

class local_incidence_report_motd_editor_form extends moodleform
{

    function __construct()
    {
        parent::__construct();
    }

    function definition()
    {
        global $USER;
        global $DB;

        $motd = new stdClass();

        $motd->motdcontent = get_config('local_incidence_report', 'motdcontent');
        $motd->motdtype = get_config('local_incidence_report', 'motdtype');

        $mform = $this->_form;

        $mform->addElement(
            'html',
            '<div id="motd-editor-title">' . get_string('motd_editor_title', 'local_incidence_report') . '</div><div id="motd-editor-wrapper" style="display:none">'
        );

        $options = array(
            LOCAL_INCIDENCE_REPORT_NOTICE_INFO => get_string('motd_form_type_info', 'local_incidence_report'),
            LOCAL_INCIDENCE_REPORT_NOTICE_SUCCESS => get_string('motd_form_type_success', 'local_incidence_report'),
            LOCAL_INCIDENCE_REPORT_NOTICE_WARNING => get_string('motd_form_type_warning', 'local_incidence_report'),
            LOCAL_INCIDENCE_REPORT_NOTICE_ERROR => get_string('motd_form_type_error', 'local_incidence_report'),
        );

        $select = $mform->addElement('select', 'type', get_string('motd_form_type_selector', 'local_incidence_report'), $options);
        $select->setSelected($motd->motdtype);
        $mform->setType('type', PARAM_INT);

        $editor = $mform->addElement('editor', 'content', get_string('motd_form_content', 'local_incidence_report'));
        $editor->setValue(array('text' => $motd->motdcontent));
        $mform->setType('content', PARAM_RAW);

        $this->add_action_buttons(false, 'Publish');

        $mform->addElement('html', '</div>');

        return $mform;
    }

    function definition_after_data()
    {
        $mform = $this->_form;
    }

    function get_data()
    {
        $data = parent::get_data();
        return $data;
    }

    function save_data()
    {
        $data = $this->get_data();

        if ($data->content['text'] != '') {
            set_config('motdcontent', $data->content['text'], 'local_incidence_report');
        }
        set_config('motdtype', $data->type, 'local_incidence_report');

        return;
    }

    function update_data($event_id)
    {
        return;
    }
}

class local_incidence_report_form_eval_support extends moodleform
{

    public $incidenceid;

    function __construct($incidenceid = null)
    {
        $this->incidenceid = $incidenceid;

        parent::__construct();
    }

    function definition()
    {
        global $USER;
        global $DB;

        $mform = $this->_form;

        $mform->addElement('hidden', 'action', 'view');
        $mform->setType('action', PARAM_INT);
        $incidenceid = optional_param('incidenceid', 0, PARAM_INT);


        if ($incidenceid == 0) {
            // P.E. Loading from an anonymous view... no ID, just token
            $incidenceid = $this->incidenceid;
            // [i] Get the token
            $token = local_incidence_report_get_token_for($incidenceid);
            $mform->addElement('hidden', 'token', $token);
            $mform->setType('token', PARAM_RAW);
        }

        $mform->addElement('hidden', 'incidenceid', $incidenceid);
        $mform->setType('incidenceid', PARAM_INT);

        $mform->addElement('html', '<h4>Evaluación del soporte recibido</h4>');

        $options = array(
            '0' => local_incidence_report_point_to_string(0),
            '1' => local_incidence_report_point_to_string(1),
            '2' => local_incidence_report_point_to_string(2),
            '3' => local_incidence_report_point_to_string(3),
            '4' => local_incidence_report_point_to_string(4),
            '5' => local_incidence_report_point_to_string(5),
        );
        $select = $mform->addElement('select', 'points', 'Evalúa el soporte recibido', $options);
        $select->setSelected('3');
        $mform->setType('points', PARAM_INT);

        $mform->addElement('submit', 'submitbutton', 'Evaluar');

        return $mform;
    }

    function definition_after_data()
    {
        $mform = $this->_form;
    }

    function get_data()
    {
        $data = parent::get_data();
        return $data;
    }

    function save_data()
    {
        $data = $this->get_data();
        //var_dump($data);
        local_incidence_report_evaluate_support($data->incidenceid, $data->points);

        return;
    }

    function update_data($event_id)
    {
        return;
    }
}