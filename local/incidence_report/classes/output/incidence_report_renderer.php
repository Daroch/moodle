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
require_once(__DIR__ . '/incidence_report_form.php');
require_once(__DIR__ . '/incidence_report_motd_renderer.php');

class local_incidence_report_renderer extends \plugin_renderer_base {

    public function render_unlogged() {

        global $DB;
        global $USER;
        global $PAGE;

        ob_start();

        $action = optional_param('action', null, PARAM_TEXT);

        $motd = new local_incidence_report_motd_renderer($PAGE, RENDERER_TARGET_GENERAL);

        switch ($action) {
            case 'new':
                echo $motd->render_motd_viewer();

                $mform = new local_incidence_report_new_incidence_form();
                if ($mform->is_cancelled()) {
                } else if ($fromform = $mform->get_data()) {
                    $data = $mform->get_data();
                    $token = $mform->save_data();
                    if (is_string($token)) {
                        $a = new stdClass();
                        $a->token = $token;
                        $a->email = $data->email;
                        echo local_incidence_report_notice(
                            get_string('token_info', 'local_incidence_report', $a),
                            LOCAL_INCIDENCE_REPORT_NOTICE_SUCCESS
                        );
                        echo '<a href="">' . get_string('continue', 'local_incidence_report') . "</a>";
                    } else {
                        echo local_incidence_report_notice(
                            get_string('token_error', 'local_incidence_report'),
                            LOCAL_INCIDENCE_REPORT_NOTICE_ERROR
                        );
                        echo '<a href="">' . get_string('continue', 'local_incidence_report') . "</a>";
                    }
                } else {
                    $mform->display();
                }
                break;
            case 'code':
                $mform = new local_incidence_report_review_incidence_by_code_form();
                if ($mform->is_cancelled()) {
                } else if ($fromform = $mform->get_data()) {
                    $code = optional_param('code', null, PARAM_TEXT);
                    $incidenceid = local_incidence_report_incidenceid_from_code($code);
                    if ($incidenceid) {
                        echo $this->render_incidence($incidenceid);
                    } else {
                        echo local_incidence_report_notice(
                            get_string('incidence_not_found', 'local_incidence_report'),
                            LOCAL_INCIDENCE_REPORT_NOTICE_ERROR
                        );
                        $mform->display();
                    }
                } else {
                    echo $motd->render_motd_viewer();
                    $mform->display();
                }
                break;
            case 'view':
                if (isloggedin()) {
                    echo $motd->render_motd_viewer();
                    $incidenceid = optional_param('incidenceid', null, PARAM_INT);
                    echo $this->render_incidence($incidenceid);
                } else {
                    $token = optional_param('token', null, PARAM_RAW);
                    if ($token) {
                        $incidenceid = local_incidence_report_get_id_for($token);
                        echo $this->render_incidence($incidenceid);
                    } else {
                        echo local_incidence_report_notice(
                            get_string('login_required_to_view_incidence', 'local_incidence_report'),
                            LOCAL_INCIDENCE_REPORT_NOTICE_ERROR
                        );
                    }
                }

                break;
            case 'close':
                // Called by URL
                $token = optional_param('token', null, PARAM_TEXT);
                $points = optional_param('points', 0, PARAM_INT);

                $result = local_incidence_report_close_incidence($token, $points);

                echo local_incidence_report_notice($result['msg'], $result['type']);

                break;
            default:
                echo $motd->render_motd_viewer();
                echo '<div id="incidence-report-unlogged-options">';
                echo '    <div class="columen-left">';
                echo '<a href="?action=new">' . get_string('report_login_incidence', 'local_incidence_report') . '</a>';
                echo '    </div>';
                echo '    <div class="columen-right">';
                echo '<a href="?action=code">' . get_string('review_incidence_status_by_code', 'local_incidence_report') . '</a>';
                echo '    </div>';
                echo '</div>';
                break;
        }

        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

    public function render_logged() {
        global $DB;
        global $USER;
        global $PAGE;

        ob_start();

        $motd = new local_incidence_report_motd_renderer($PAGE, RENDERER_TARGET_GENERAL);

        $action = optional_param('action', null, PARAM_TEXT);

        switch ($action) {
            case 'new':
                $mform = new local_incidence_report_new_incidence_form();
                if ($mform->is_cancelled()) {
                    // No hacemos nada si se cancela el formulario.
                } else if ($fromform = $mform->get_data()) {
                    $newincidenceid = $mform->save_data();

                    global $COURSE;
                    $context = context_course::instance($COURSE->id);

                    $data = $mform->get_data();

                    if (isset($data->attachments)) {

                        $entry = new stdClass;
                        $entry->id = $newincidenceid;

                        $draftitemid = file_get_submitted_draft_itemid('attachments');

                        file_prepare_draft_area(
                            $draftitemid,
                            $context->id,
                            'local_incidence_report',
                            'attachments',
                            $entry->id,
                            array('subdirs' => 0, 'maxbytes' => get_config('local_incidence_report', 'max_bytes'), 'maxfiles' => get_config(
                                'local_incidence_report',
                                'max_files'
                            ))
                        );

                        file_save_draft_area_files(
                            $data->attachments,
                            $context->id,
                            'local_incidence_report',
                            'attachments',
                            $entry->id,
                            array('subdirs' => 0, 'maxbytes' => get_config('local_incidence_report', 'max_bytes'), 'maxfiles' => get_config(
                                'local_incidence_report',
                                'max_files'
                            ))
                        );
                    }

                    echo local_incidence_report_notice(
                        get_string('new_incidence_submitted', 'local_incidence_report'),
                        LOCAL_INCIDENCE_REPORT_NOTICE_SUCCESS
                    );
                    echo '<a href="">' . get_string('continue', 'local_incidence_report') . "</a>";
                } else {
                    echo $motd->render_motd_viewer();

                    $mform->display();
                }
                break;
            case 'view':
                echo $motd->render_motd_viewer();

                $incidenceid = optional_param('incidenceid', null, PARAM_INT);

                echo $this->render_incidence($incidenceid);
                break;
            case 'close':
                $token = null;
                $points = optional_param('points', 0, PARAM_INT);
                $incidenceid = optional_param('incidenceid', 0, PARAM_INT);

                $result = local_incidence_report_close_incidence($token, $points, null, $incidenceid);

                echo local_incidence_report_notice($result['msg'], $result['type']);

                break;
            default:
                $isadmin = is_siteadmin($USER->id);

                if ($isadmin) {
                    echo $motd->render_motd_editor();
                }
                echo $motd->render_motd_viewer();

                if (local_incidence_report_allow_management($USER->id)) {
                    echo $this->render_switch_view();
                }

                $params = local_incidence_report_get_all_params(false);
                if (($params->view = 'user') || !$isadmin) {
                    // Retiramos la opción de añadir incidencias generales siguiendo instrucciones del cliente.
                    //echo '<a href="?action=new">' . get_string('add_new_report_general', 'local_incidence_report') . '</a>';
                }

                $baseurl = '/local/incidence_report/incidence_report.php';
                $this->render_course_filter((array)$params->filter, $baseurl);

                $result = local_incidence_report_get_reports();
                $reports = $result['records'];
                $pagination = $result['pagination'];

                global $OUTPUT;
                $params = local_incidence_report_get_all_params();
                $url = new moodle_url('/local/incidence_report/incidence_report.php', $params);
                echo $OUTPUT->paging_bar($pagination->totalcount, $pagination->page, $pagination->perpage, $url);

                echo $this->render_reports($reports);


                echo $this->render_modal_background();
        }
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

    public function render_admin($readonly = false) {
        global $DB;
        global $USER;
        global $PAGE;

        ob_start();

        local_incidence_report_set_timedout_reports();

        $action = optional_param('action', null, PARAM_TEXT);

        switch ($action) {
            case 'dbdebug':
                $records = $DB->get_records('local_incidence_report_msgs');

                echo '<table class="generaltable"><tr><th>id<th>answers<th>token<th>userid<th>fullname<th>email<th>type<th>courseid<th>message<th>manager<th>status<th>timestamp<th>points<th>modified';

                foreach ($records as $record) {
                    echo '<tr>';
                    foreach ($record as $field) {
                        echo '<td>' . $field;
                    }
                }
                echo '</table>';

                break;
            case 'view':
                $incidenceid = optional_param('incidenceid', null, PARAM_INT);
                echo $this->render_incidence($incidenceid, $readonly);
                break;

            case 'close':
                // Called by URL
                $token = optional_param('token', null, PARAM_TEXT);
                $points = optional_param('points', -1, PARAM_INT);
                $incidenceid = required_param('incidenceid', PARAM_INT);

                $result = local_incidence_report_close_incidence($token, $points, '', $incidenceid);

                echo local_incidence_report_notice($result['msg'], $result['type']);

                break;

            default:
                $isadmin = is_siteadmin($USER->id);

                $motd = new local_incidence_report_motd_renderer($PAGE, RENDERER_TARGET_GENERAL);
                if ($isadmin) {
                    echo $motd->render_motd_editor();
                }
                echo $motd->render_motd_viewer();

                echo $this->render_switch_view();

                $render_filter_panel = false;
                if ($isadmin) {
                    $render_filter_panel  = true;
                } else {
                    $view = optional_param('view', 'user', PARAM_TEXT);
                    switch ($view) {
                        case 'manager':
                        case 'observer':
                            $render_filter_panel = true;
                            break;
                        case 'view':
                        default:
                            $render_filter_panel = false;
                    }
                }
                if ($render_filter_panel) {
                    echo $this->render_filter_panel();
                }

                $result = local_incidence_report_get_reports();
                $reports = $result['records'];
                $pagination = $result['pagination'];

                global $OUTPUT;
                $params = local_incidence_report_get_all_params();
                $url = new moodle_url('/local/incidence_report/incidence_report.php', (array)$params);
                echo $OUTPUT->paging_bar($pagination->totalcount, $pagination->page, $pagination->perpage, $url);

                echo $this->render_reports($reports, $readonly);
                if (LOCAL_INCIDENCE_REPORT_ENABLE_REPORTS === true) {
                    echo '<button class="btn btn-primary" onclick="location.href=\'report.php\'" type="button">Generar Informe</button>';
                    //echo '<a href="report.php">Generar Informe</a>';
                }
                echo $this->render_modal_background();
        }
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

    public function render_modal_background() {
        ob_start();

        echo '<div id="incidence-report-modal-background" class="incidence-report-hidden"></div>';

        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

    public function render_reports($reports, $readonly = false) {
        global $DB;
        global $USER;
        ob_start();

        $modals = '';

        $isadmin = is_siteadmin($USER->id);

        $ismanager = local_incidence_report_allow_management($USER->id);
        $params = local_incidence_report_get_all_params(false);

        if ($params->view == 'user') {
            $ismanager = false;
        }

        if (is_numeric($params->filter->course)) {
            if ($params->filter->course != 1) {
                $ismanagerof = local_incidence_report_manages_course($params->filter->course);
                $course = $DB->get_record('course', array('id' => $params->filter->course));
                if ($course) {
                    echo local_incidence_report_notice(get_string(
                        'showing_reports_for_course',
                        'local_incidence_report',
                        $course->fullname
                    ), LOCAL_INCIDENCE_REPORT_NOTICE_SUCCESS);
                    if (($params->view == 'user') && !$ismanagerof && !$isadmin) {
                        echo '<a href="?action=new&fcourse=' . $params->filter->course . '">' . get_string(
                            'add_new_report_course',
                            'local_incidence_report',
                            $course->fullname
                        ) . '</a>';
                    }
                } else {
                    echo local_incidence_report_notice(
                        get_string('error_showing_reports_for_course', 'local_incidence_report'),
                        LOCAL_INCIDENCE_REPORT_NOTICE_ERROR
                    );
                }
            }
        }

        echo '<table class="incidence-report-table generaltable">';
        echo '<tr>';

        echo '<th> ' . get_string('report_header_id', 'local_incidence_report');
        if ($ismanager) {
            echo '<th> ' . get_string('report_header_user', 'local_incidence_report');
            echo '<th> ' . get_string('report_header_type', 'local_incidence_report');
        }
        echo '<th> ' . get_string('report_header_course', 'local_incidence_report');
        echo '<th> ' . get_string('report_header_message', 'local_incidence_report');
        echo '<th> ' . get_string('report_header_status', 'local_incidence_report');
        echo '<th> ' . get_string('report_header_submission_date', 'local_incidence_report');
        if ($ismanager) {
            echo '<th> ' . get_string('report_header_sla_status', 'local_incidence_report');
            //echo '<th> ' . get_string('report_header_manager', 'local_incidence_report');
        }
        echo '<th> ' . get_string('report_header_details', 'local_incidence_report');
        echo '</tr>';

        $coursepool = array();
        $coursepool[0] = get_string('course_0_shortname', 'local_incidence_report');

        foreach ($reports as $report) {
            echo '<tr class="report-row">';
            echo '<td>' . $report->id;

            if ($ismanager) {
                if ($report->userid == 0) {
                    echo '<td> <i>' . $report->fullname . '<i>';
                } else {
                    $user = $DB->get_record('user', array('id' => $report->userid));

                    $params = local_incidence_report_get_all_params();
                    $params['fusername'] = $user->username;
                    // [i] Fake a click on filter button...
                    $params['fusernamebutton'] = 'true';
                    $url = new moodle_url('/local/incidence_report/incidence_report.php', (array)$params);
                    $link = html_writer::link($url, "$user->lastname, $user->firstname ($user->username)");
                    echo "<td> $link";
                }

                echo '<td>' . local_incidence_report_type_literals($report->type);
                //if ($report->type !== null) {
                //    echo '<td>' . get_string('submit_type_admin_string_' . substr($report->type, 0, 1), 'local_incidence_report');
                //} else {
                //    echo '<td> --';
                //}
            }

            if (!isset($coursepool[$report->courseid])) {
                $course = get_course($report->courseid);
                $coursepool[$report->courseid] = $course->shortname;
            }

            echo '<td>' . $coursepool[$report->courseid];

            echo '<td> <i class="fa fa-eye incidence-report-modal-view" data-id="' . $report->id . '"></i>';
            echo '<td class="status-' . $report->status . '">' . local_incidence_report_status_literals($report->status);
            echo '<td>' . date('d/m/y H:i:s', $report->timestamp);

            if ($ismanager) {
                echo '<td><span ';
                if ($report->status == 0) {
                    echo ' style="color: red" ';
                }
                echo '>';
                echo local_incidence_report_get_sla_for($report);
                echo '</span></td>';

                //if ($report->manager == null) {
                //    echo '<td>' . get_string('unassigned', 'local_incidence_report');
                //} else {
                //    $user = $DB->get_record('user', array('id' => $report->manager));
                //    echo '<td>' . $user->username;
                //}
            }

            echo '<td> <i class="fa fa-arrow-right incidence-report-answer" data-id="' . $report->id . '"></i>';

            $waiting = local_incidence_report_is_waiting($report->id);

            if (
                $waiting &&
                //(($ismanager && $waitingmanager) || (!$ismanager && !$waitingmanager)) &&
                (($report->status != LOCAL_INCIDENCE_REPORT_STATUS_CLOSED) && ($report->status != LOCAL_INCIDENCE_REPORT_STATUS_TIMEDOUT))
            ) {
                echo ' <i class="fa fa-warning" style="color: orange; cursor: default;"></i>';
            }

            echo '</tr>';

            $modal = '<div class="incidence-report-modal incidence-report-hidden" data-id="' . $report->id . '">' . $report->message . '</div>';

            $modals .= $modal;
        }
        echo '</table>';

        echo $modals;

        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

    public function render_incidence($incidenceid, $readonly = false) {
        global $DB;
        global $USER;
        global $COURSE;

        $context = context_course::instance($COURSE->id);
        $action = optional_param('action', null, PARAM_TEXT);
        $parent = $DB->get_record(LOCAL_INCIDENCE_REPORT_TABLE, array('id' => $incidenceid));

        $isadmin = is_siteadmin($USER->id);
        $isreporter = ($parent->userid == $USER->id);
        $ismanager = ($parent->manager == $USER->id);
        $isunmanaged = ($parent->manager == null);
        $couldmanage = local_incidence_report_allow_management($USER->id, $incidenceid);
        if ($parent->courseid == null) {
            // Non course-related incidences can't be observed
            $isobserver = false;
        } else {
            $isobserver = local_incidence_report_allow_observer($USER->id, $incidenceid, CONTEXT_COURSE, $parent->courseid);
        }
        $isanonymous = ($parent->token != null);

        //echo 'isadmin: ' . ($isadmin ? 'si' : 'no') . '<br>';
        //echo 'isreporter: ' . ($isreporter ? 'si' : 'no') . '<br>';
        //echo 'ismanager: ' . ($ismanager ? 'si' : 'no') . '<br>';
        //echo 'isunmanaged: ' . ($isunmanaged ? 'si' : 'no') . '<br>';
        //echo 'couldmanage: ' . ($couldmanage ? 'si' : 'no') . '<br>';
        //echo 'isobserver: ' . ($isobserver ? 'si' : 'no') . '<br>';
        //echo 'isanonymous: ' . ($isanonymous ? 'si' : 'no') . '<br>';

        if ($parent->answers != null) {
            // Can't see the incidence as this is a response...
            echo local_incidence_report_notice(
                'No estás autorizado a ver esta incidencia',
                LOCAL_INCIDENCE_REPORT_NOTICE_ERROR
            );
            return;
        }

        if (
            !$isadmin &&
            !$isreporter &&
            !$ismanager &&
            !$couldmanage &&
            !$isobserver &&
            !$isanonymous
        ) {
            // Can't see the incidence as user is not related
            echo local_incidence_report_notice(
                'No estás autorizado a ver esta incidencia',
                LOCAL_INCIDENCE_REPORT_NOTICE_ERROR
            );
            return;
        }

        if ($readonly == false) {
            $readonly = true;
            if (
                $isadmin ||
                $isreporter ||
                $ismanager ||
                ($isunmanaged && $couldmanage)
            ) {
                $readonly = false;
            }
        }

        $mform = new local_incidence_report_answer_incidence_form($incidenceid, $parent->manager);

        ob_start();

        if ($fromform = $mform->get_data()) {

            $data = $mform->get_data();

            if (isset($data->closebutton)) {
                // Trying to close incidence

                $closedbyuser = false; // Right now it's not user, is manager...
                if ($closedbyuser) {
                    echo local_incidence_report_notice(
                        get_string('closing_incidence', 'local_incidence_report'),
                        LOCAL_INCIDENCE_REPORT_NOTICE_INFO
                    );

                    echo $this->render_satisfaction_survey($incidenceid);
                } else {
                    // [!!!] Make some double confirmation magic
                    // This code was used when USER was clossing incidence AND we want evaluation.

                    echo local_incidence_report_notice(
                        'Va a proceder al CIERRE de la incidencia. Este proceso no puede ser revertido.',
                        LOCAL_INCIDENCE_REPORT_NOTICE_WARNING
                    );

                    $incidenceid = required_param('incidenceid', PARAM_INT);
                    $token = optional_param('token', null, PARAM_RAW);

                    echo '<input class="local-incidence-report-close-incidence" type="submit" class="btn btn-primary" name="closebutton" ';
                    echo ' id="id_closebutton" value="Cerrar incidencia" data-points="-1" ';
                    echo ' data-incidenceid="' . $incidenceid . '" data-token="' . $token . '">';
                }

                $html = ob_get_contents();
                ob_end_clean();
                return $html;
            }

            if (isset($data->submitbutton)) {

                $result = $mform->save_data();
                // Redirect to avoid resubmission of data
                $params = array(
                    'action' => 'view',
                    'incidenceid' => $incidenceid,
                );

                if ($parent->token != null) {
                    $params['token'] = $parent->token;
                }

                $url = new moodle_url("/local/incidence_report/incidence_report.php", $params);

                // Llegado a este punto hemos guardado el mensaje pero NO los archivos...

                if (($result['id'] != null) && (isset($data->attachments))) {

                    //if (empty($entry->id)) {
                    //    $entry = new stdClass;
                    //    $entry->id = null;
                    //}
                    $entry = new stdClass;
                    $entry->id = $result['id'];

                    $draftitemid = file_get_submitted_draft_itemid('attachments');

                    file_prepare_draft_area(
                        $draftitemid,
                        $context->id,
                        'local_incidence_report',
                        'attachments',
                        $entry->id,
                        array('subdirs' => 0, 'maxbytes' => get_config('local_incidence_report', 'max_bytes'), 'maxfiles' => get_config(
                            'local_incidence_report',
                            'max_files'
                        ),)
                    );

                    file_save_draft_area_files(
                        $data->attachments,
                        $context->id,
                        'local_incidence_report',
                        'attachments',
                        $entry->id,
                        array('subdirs' => 0, 'maxbytes' => get_config('local_incidence_report', 'max_bytes'), 'maxfiles' => get_config(
                            'local_incidence_report',
                            'max_files'
                        ),)
                    );
                }

                redirect($url, $result['msg'], 15, local_incidence_report_moodle_notify_type($result['type']));
            }
        }

        $parent = $DB->get_record(LOCAL_INCIDENCE_REPORT_TABLE, array('id' => $incidenceid));

        $manager = new stdClass();
        $managers = array();
        $unmanaged = new stdClass();
        $unmanaged->id = false;
        $unmanaged->username = get_string('unassigned', 'local_incidence_report');
        $unmanaged->firstname = null;
        $unmanaged->lastname = get_string('unassigned', 'local_incidence_report');
        $unmanaged->email = '';


        if ($parent->manager == null) {
            $manager = $unmanaged;
        } else {
            $manager = core_user::get_user($parent->manager);
            $managers[$manager->id] = $manager;
        }

        $user = new stdClass();

        if ($parent->userid == 0) {
            $user->id = false;
            $user->username = $parent->fullname;
            $user->email = $parent->email;
        } else {
            $user = core_user::get_user($parent->userid);
        }


        echo '<table class="incidence-report-conversation">';
        echo '<tr>';
        echo '<td class="id"><span>' . get_string('incidence_report_id', 'local_incidence_report') . '</span> ' . $parent->id . ' (' . $parent->token . ')</td>';
        echo '<td class="type"><span>' . get_string('incidence_report_type', 'local_incidence_report') . '</span> ';
        if ($parent->type != null) {
            echo local_incidence_report_type_literals($parent->type, true);
        } else {
            echo "--";
        }
        echo '</td>';

        echo '<td class="manager"><span>' . get_string('incidence_report_manager', 'local_incidence_report') . '</span> ';

        //echo $manager->username;
        echo $manager->lastname;
        if ($manager->firstname != null) {
            echo ', ' . $manager->firstname;
        }

        echo '</td>';

        echo '<td class="status status-' . $parent->status . '"><span>Status:</span> ' . local_incidence_report_status_literals($parent->status) . '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td colspan="4" class="type">';
        echo '<span>' . get_string('incidence_report_subtype', 'local_incidence_report') . '</span> ';
        if ($parent->type != null) {
            echo explode(' - ', get_string('submit_type_string_' . $parent->type, 'local_incidence_report'))[1];
        } else {
            echo '--';
        }
        echo '</tr>';
        echo '<tr class="new-message">';

        if (isset($user->firstname)) {
            $tempusername = $user->firstname . ' ' . $user->lastname;
        } else {
            $tempusername = '<i> * ' . $user->username . '</i>';
        }

        echo '<td class="submitted" colspan="2"><span>' . get_string('incidence_report_submited_by', 'local_incidence_report') . '</span> ' . $tempusername . ' <a href="mailto:' . $user->email . '"><i class="fa fa-envelope-o"></i></a></td>';
        echo '<td class="date" ><span>' . get_string('incidence_report_at', 'local_incidence_report') . '</span> ' . date(
            'd/m/y H:i',
            $parent->timestamp
        ) . '</td>';
        echo '<td class="sla" ><span>' . get_string('incidence_report_sla', 'local_incidence_report') . '</span> ' . local_incidence_report_get_sla_for($parent) . '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td class="message-title" colspan="4"><span>' . get_string('incidence_report_message', 'local_incidence_report') . '</span></td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td class="message-body" colspan="4">' . $parent->message . '</td>';
        echo '</tr>';

        echo local_incidence_report_list_files_for($incidenceid, $context->id);


        $answers = local_incidence_report_get_answers_for($incidenceid);
        foreach ($answers as $answer) {
            echo '<tr class="new-message">';

            switch ($answer->userid) {
                case 0:
                    $who = new stdClass();
                    $who->username = $parent->fullname;
                    $who->email = $parent->email;
                    $who->firstname = null;
                    $who->lastname = $parent->fullname;
                    break;
                case $user->id:
                    $who = $user;
                    break;
                default:
                    if (isset($managers[$answer->userid])) {
                        $who = $managers[$answer->userid];
                    } else {
                        $who = core_user::get_user($answer->userid);
                        $managers[$who->id] = $who;
                    }
            }


            echo '<td class="submitted" colspan="2"><span>' . get_string('incidence_report_submitted_by', 'local_incidence_report') . '</span> ';
            //echo $who->username;
            echo $who->lastname;
            if ($who->firstname != null) {
                echo ', ' . $who->firstname;
            }

            if ($who->email != '') {
                echo ' <a href="mailto:' . $who->email . '"><i class="fa fa-envelope-o"></i></a>';
            }


            echo '</td>';

            echo '</td>';
            echo '<td colspan="2"><span>' . get_string('incidence_report_at', 'local_incidence_report') . '</span> ' . date(
                'd/m/y H:i',
                $answer->timestamp
            ) . '</td>';
            echo '</tr>';
            echo '<tr>';
            echo '<td class="message-title" colspan="4"><span>' . get_string('incidence_report_message', 'local_incidence_report') . '</span></td>';
            echo '</tr>';
            echo '<tr>';
            echo '<td class="message-body" colspan="4">' . $answer->message . '</td>';
            echo '</tr>';

            echo local_incidence_report_list_files_for($answer->id, $context->id);
        }

        if (!$readonly && !$mform->is_submitted() && ($parent->status != LOCAL_INCIDENCE_REPORT_STATUS_CLOSED) && ($parent->status != LOCAL_INCIDENCE_REPORT_STATUS_TIMEDOUT)) {
            echo '<tr class="new-message">';
            echo '<td colspan="4"><span>' . get_string('incidence_report_submit_answer_as', 'local_incidence_report') . '</span> ';
            if (!isloggedin()) {
                echo $user->username;
            } else {
                //echo $USER->username;
                echo $USER->lastname . ', ' . $USER->firstname;
            }
            echo '</td>';
            echo '</tr>';

            echo '<tr>';
            echo '<td class="message-title" colspan="4"><span>' . get_string('incidence_report_message', 'local_incidence_report') . '</span></td>';
            echo '</tr>';
            echo '<tr>';
            echo '<td class="message-body" colspan="4">';

            $mform->display();

            echo '</td>';
            echo '</tr>';
        }


        // [!!!] IF closed and not evaluated, USER evaluates it
        // This is done width a LIB function... in the FORM DISPLAY!!!!
        // local_incidence_report_can_user_evaluate($incidence)

        if (($parent->status == LOCAL_INCIDENCE_REPORT_STATUS_CLOSED) &&
            ($parent->points == -1) &&
            ($USER->id == $parent->userid)
        ) {

            echo '<tr>';
            echo '<td class="message-body" colspan="4">';

            $mform2 = new local_incidence_report_form_eval_support($parent->id);
            if ($mform2->is_cancelled()) {
                //Handle form cancel operation, if cancel button is present on form
            } else if ($fromform = $mform2->get_data()) {
                //In this case you process validated data. $mform->get_data() returns data posted in form.
                $mform2->save_data();

                // [i] should update $parent->points. Just update ALL on parent...
                $parent = $DB->get_record(LOCAL_INCIDENCE_REPORT_TABLE, array('id' => $incidenceid));
            } else {
                // this branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
                // or on the first display of the form.
                //Set default data (if any)
                //$mform->set_data($toform);
                //displays the form
                $mform2->display();
            }

            echo '</td>';
            echo '</tr>';
        }

        // Just show the evaluation
        if ($parent->status == LOCAL_INCIDENCE_REPORT_STATUS_CLOSED) {
            echo '<tr>';
            echo '<td class="message-body" colspan="4"><i>';
            if ($parent->points == -1) {
                echo "El usuario no ha evaluado el soporte recibido.";
            } else {
                echo "Soporte evaluado por el usuario con " . $parent->points . " puntos. (" . local_incidence_report_point_to_string($parent->points) . ")";
            }
            echo '</i></td>';
            echo '</tr>';
        }

        echo '</table>';

        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

    public function render_satisfaction_survey($incidenceid) {
        ob_start();

        $token = local_incidence_report_get_token_for($incidenceid);

        echo '<div id="local-incidence-report-satisfaction-wrapper">';

        echo '<div class="local-incidence-report-satisfaction-icon" data-points="0" data-incidenceid="' . $incidenceid . '" data-token="' . $token . '"></div>';
        echo '<div class="local-incidence-report-satisfaction-icon" data-points="1" data-incidenceid="' . $incidenceid . '" data-token="' . $token . '"></div>';
        echo '<div class="local-incidence-report-satisfaction-icon" data-points="2" data-incidenceid="' . $incidenceid . '" data-token="' . $token . '"></div>';
        echo '<div class="local-incidence-report-satisfaction-icon" data-points="3" data-incidenceid="' . $incidenceid . '" data-token="' . $token . '"></div>';
        echo '<div class="local-incidence-report-satisfaction-icon" data-points="4" data-incidenceid="' . $incidenceid . '" data-token="' . $token . '"></div>';

        echo '</div>';

        $html = ob_get_contents();
        ob_end_clean();

        return $html;
    }

    public function render_search_panel() {
        ob_start();



        echo '<div id="local-incidence-report-search-panel">';
        echo '<ul>';
        echo '<li>Buscar por ID';
        echo '<li>Buscar por username';
        echo '</ul>';
        echo '</div>';

        $html = ob_get_contents();
        ob_end_clean();
        return;
        return $html;
    }

    public function render_filter_panel() {
        global $USER;

        ob_start();

        $params = local_incidence_report_get_all_params();

        $label = get_string('filter_status_title', 'local_incidence_report');
        echo <<< HTML
<div class="d-flex flex-wrap">
HTML;
        echo <<< HTML
<div id="incidence-report-filter-status" class="px-2">
    <div class="title">{$label}</div>
    <div class="status-wrapper text-uppercase">
HTML;
        $statuslist = [
            LOCAL_INCIDENCE_REPORT_STATUS_SENT,
            //LOCAL_INCIDENCE_REPORT_STATUS_ASSIGNED,
            LOCAL_INCIDENCE_REPORT_STATUS_ONGOING,
            LOCAL_INCIDENCE_REPORT_STATUS_CLOSED,
            LOCAL_INCIDENCE_REPORT_STATUS_TIMEDOUT,
        ];
        $baseurl = '/local/incidence_report/incidence_report.php';
        foreach ($statuslist as $status) {
            $item['label'] = get_string('submit_status_admin_string_' . $status, 'local_incidence_report');
            if (is_numeric($params['fstatus'])) {
                $item['css'] = ($params['fstatus'] == $status) ? 'selected' : '';
            } else {
                $item['css'] = '';
            }
            $item['params'] = $params;
            $item['params']['fstatus'] = $status;
            $url = new moodle_url($baseurl, $item['params']);
            $item['url'] = $url->__tostring();
            echo <<< HTML
        <div class="item-wrapper">
            <div class="item {$item['css']}"><a href="{$item['url']}">{$item['label']}</a></div>
        </div>
HTML;
        }
        $item['label'] = get_string('clear_filter', 'local_incidence_report');
        $item['css'] = ($params['fstatus'] === null) ? 'selected' : '';
        $item['params'] = $params;
        $item['params']['fstatus'] = null;
        $url = new moodle_url($baseurl, $item['params']);
        $item['url'] = $url->__tostring();
        echo <<<HTML
        <div class="item-wrapper">
            <div class="item {$item['css']}"><a href="{$item['url']}">{$item['label']}</a></div>
        </div>
HTML;
        echo <<< HTML
    </div>
</div>
HTML;
        $label = get_string('filter_type_title', 'local_incidence_report');
        echo <<< HTML
<div id="incidence-report-filter-type" class="px-2">
    <div class="title">{$label}</div>
    <div class="status-wrapper text-uppercase">
HTML;
        $typelist = [
            LOCAL_INCIDENCE_REPORT_FILTER_TYPE_LOGIN,
            // LOCAL_INCIDENCE_REPORT_FILTER_TYPE_SECRETARY,
            LOCAL_INCIDENCE_REPORT_FILTER_TYPE_PLATFORM,
            LOCAL_INCIDENCE_REPORT_FILTER_TYPE_COURSE,
        ];
        if (is_siteadmin($USER)) {
            $typelist[] = LOCAL_INCIDENCE_REPORT_FILTER_TYPE_OTHERS;
        }
        $baseurl = '/local/incidence_report/incidence_report.php';
        foreach ($typelist as $type) {
            $item['label'] = get_string('submit_type_admin_string_' . $type, 'local_incidence_report');
            if (is_numeric($params['ftype'])) {
                $item['css'] = ($params['ftype'] == $type) ? 'selected' : '';
            } else {
                $item['css'] = '';
            }
            $item['params'] = $params;
            $item['params']['ftype'] = $type;
            $url = new moodle_url($baseurl, $item['params']);
            $item['url'] = $url->__tostring();
            echo <<<HTML
        <div class="item-wrapper">
            <div class="item {$item['css']}"><a href="{$item['url']}">{$item['label']}</a></div>
        </div>
HTML;
        }
        $item['label'] = get_string('clear_filter', 'local_incidence_report');
        $item['css'] = ($params['ftype'] === null) ? 'selected' : '';
        $item['params'] = $params;
        $item['params']['ftype'] = null;
        $url = new moodle_url($baseurl, $item['params']);
        $item['url'] = $url->__tostring();
        echo <<<HTML
        <div class="item-wrapper">
            <div class="item {$item['css']}"><a href="{$item['url']}">{$item['label']}</a></div>
        </div>
        </div></div>
HTML;
        echo <<< HTML
    </div><!-- d-flex -->
HTML;
        // [i] Adding username filtering
        // [!!!] Cadena idioma
        // $label = get_string('filter_username_title', 'local_incidence_report');
        $label = "Filtrar por usuario";
        $labeltag = html_writer::div($label, 'title');
        $inputattributes = array(
            'type' => 'text',
            'name' => 'fusername',
            'id' => 'incidence-report-filter-username-input',
            'value' => $params['fusername'],
            'class' => 'form-control',
        );
        $inputtag = html_writer::start_tag('input', $inputattributes);
        // [!!!] Cadena idioma
        // $label = get_string('filter_username_button_title', 'local_incidence_report');
        $buttonlabel = "Filtrar";
        $fusernameparams = $params;
        unset($fusernameparams['fusername']);
        $url = new moodle_url($baseurl, (array)$fusernameparams);
        $attributes = array(
            'class' => 'btn btn-primary',
            'onclick' => "
var url = '" . html_entity_decode($url) . "&fusernamebutton=true&fusername='+document.getElementById('incidence-report-filter-username-input').value;
window.location.href = url;",
        );
        $buttontag = html_writer::tag('button', 'Filtrar', $attributes);
        $attributes['onclick'] = "var url = '" . html_entity_decode($url) . "&fusernamebutton=true&fusername'; window.location.href = url;";
        $attributes['class'] = 'btn btn-secondary';
        $cleantag = html_writer::tag('button', 'Limpiar', $attributes);
        $attributes = array(
            'id' => 'incidence-report-filter-username',
            'class' => 'px-2',
        );
        echo html_writer::div($labeltag . $inputtag . $buttontag . $cleantag, '', $attributes);

        $this->render_course_filter($params, $baseurl);

        if (is_siteadmin($USER)) {
            $this->render_show_hidden();
        }

        $html = ob_get_contents();
        ob_end_clean();

        return $html;
    }

    public function render_course_filter($params, $baseurl) {
        global $DB;
        global $USER;

        $label = 'Filtrar por curso';
        $labeltag = html_writer::div($label, 'title');

        $options = [
            '1' => '-- todos --',
        ];

        // Necesitamos saber los cursos a los que tengo acceso.
        if (is_siteadmin($USER)) {
            $sql = 'SELECT id, fullname FROM {course} WHERE id != 1 ORDER BY fullname';
            $records = $DB->get_records_sql($sql);
        } else {
            $sql = 'SELECT id, fullname
                      FROM {course}
                     WHERE id IN ( SELECT DISTINCT(e.courseid)
                                     FROM {user_enrolments} AS ue
                                          JOIN {enrol} AS e ON (e.id = ue.enrolid)
                                    WHERE ue.userid = :userid
                                          AND ue.status = :status
                                  )';
            $params = [
                'userid' => $USER->id,
                'status' => ENROL_USER_ACTIVE,
            ];
            $records = $DB->get_records_sql($sql, $params);
        }
        foreach ($records as $record) {
            $options[$record->id] = $record->fullname;
        }

        $name = 'incidence-report-filter-course-select';
        $selected = optional_param('fcourse', null, PARAM_INT);
        $nothing = null;

        if (!isset($params['fcourse'])){
            $params['fcourse'] = 1;
        }

        $selectattributes = array(
            'id' => $name,
            'value' => $params['fcourse'],
            'class' => 'form-control',
        );
        $selecttag = html_writer::select($options, $name, $selected, $nothing, $selectattributes);

        $buttonlabel = 'Filtrar';
        $fcourseparams = $params;
        unset($fcourseparams['fcourse']);

        $url = new moodle_url($baseurl, (array)$fcourseparams);
        $attributes = array(
            'class' => 'btn btn-primary',
            'onclick' => "
    var url = '" . html_entity_decode($url) . "&fcoursebutton=true&fcourse='+document.getElementById('incidence-report-filter-course-select').value;
    window.location.href = url;",
        );
        $buttontag = html_writer::tag('button', $buttonlabel, $attributes);

        $buttonlabel = 'Limpiar';
        $attributes = [
            'onclick' => "var url = '" . html_entity_decode($url) . "&fcoursebutton=true&fcourse'; window.location.href = url;",
            'class' => 'btn btn-secondary'
        ];
        $cleantag = html_writer::tag('button', $buttonlabel, $attributes);

        $attributes = array(
            'id' => 'incidence-report-filter-course',
            'class' => 'px-2',
        );
        echo html_writer::div($labeltag . $selecttag . $buttontag . $cleantag, '', $attributes);
    }

    public function render_show_hidden() {
        $params = local_incidence_report_get_all_params(true);

        if (!isset($params['fhidden'])) {
            $params['fhidden'] = '0';
        }

        $label = ($params['fhidden'] === '0') ? 'mostrar antiguas' : 'ocultar antiguas';

        $params['fhidden'] = ($params['fhidden'] === '0') ? '1' : '0';

        $baseurl = '/local/incidence_report/incidence_report.php';
        $url = new moodle_url($baseurl, (array)$params);

        echo '<a href="' . $url . '">' . $label . '</a>';
    }

    public function render_switch_view() {
        global $USER;

        if (is_siteadmin($USER->id)) {
            return;
        }

        $params = local_incidence_report_get_all_params();
        $paramsmanager = $params;
        $paramsuser = $params;
        $paramsobserver = $params;

        $paramsmanager['view'] = 'manager';
        $paramsuser['view'] = 'user';
        $paramsobserver['view'] = 'observer';

        $label = get_string('switch_view_title', 'local_incidence_report');

        $managerurl = new moodle_url('/local/incidence_report/incidence_report.php', (array)$paramsmanager);
        $userurl = new moodle_url('/local/incidence_report/incidence_report.php', (array)$paramsuser);
        $observerurl = new moodle_url('/local/incidence_report/incidence_report.php', (array)$paramsobserver);

        $manager = array(
            'label' => get_string('switch_view_manager_label', 'local_incidence_report'),
            'view' => 'manager',
            'url' => $managerurl->__tostring(),
            'css' => '',
        );

        $user = array(
            'label' => get_string('switch_view_user_label', 'local_incidence_report'),
            'view' => 'user',
            'url' => $userurl->__tostring(),
            'css' => '',
        );

        $observer = array(
            'label' => 'Observador',
            'view' => 'observer',
            'url' => $observerurl->__tostring(),
            'css' => '',
        );

        $params = local_incidence_report_get_all_params(false);

        switch ($params->view) {
            case 'manager':
                $manager['css'] = 'selected';
                break;
            case 'user':
                $user['css'] = 'selected';
                break;
            case 'observer':
                $observer['css'] = 'selected';
                break;
            default:
                if (is_siteadmin($USER->id)) {
                    $manager['css'] = 'selected';
                } else {
                    $user['css'] = 'selected';
                }
        }

        echo <<< HTML
<div id="incidence-report-switch-view">
    <div class="title">{$label}</div>
    <div class="status-wrapper">
        <div class="item-wrapper">
            <div class="item {$manager['css']}"><a href="{$manager['url']}">{$manager['label']}</a></div>
        </div>
        <div class="item-wrapper">
            <div class="item {$user['css']}"><a href="{$user['url']}">{$user['label']}</a></div>
        </div>
        <div class="item-wrapper">
            <div class="item {$observer['css']}"><a href="{$observer['url']}">{$observer['label']}</a></div>
        </div>
    </div>
</div>
HTML;
    }
}
