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
 

// Name of the plugin should be defined.
$string['pluginname'] = 'Incidence report';

$string['forbidden_access'] = 'User is not allowed to use plugin.';
$string['navigation_title'] = 'Incidence report';

$string['add_new_report_course'] = 'add new incidence on course {$a}';
$string['add_new_report_general'] = 'add new general incidence';
$string['add_new_report_on_course'] = 'Adding new incidence report for course <b>{$a}</b>';
$string['answer_duplication_error'] = 'It seems you are duplicating an answer.';
$string['answer_submitted'] = 'An answer has been submitted...';
$string['attachments'] = 'Attachments';
$string['cant_swap_manager'] = 'Invalid manager swap.';
$string['clear_filter'] = 'clear filter';
$string['close_incidence_button'] = 'Close incidence';
$string['closing_incidence'] = 'Closing incidence. To close the indicence just click on the icon showing your satisfaction level.';
$string['continue'] = 'Continue';
$string['course_0_shortname'] = '-';
$string['error_showing_reports_for_course'] = 'Can\'t find the course.';
$string['filter_status_title'] = 'Filter <br>Status';
$string['filter_type_title'] = 'Filter <br>Type';
$string['incidence_already_closed'] = 'Incidence has been closed before...';
$string['incidence_closed'] = 'Incidence has been closed successfully';
$string['incidence_message_empty'] = 'Incidence message can\'t be empty.';
$string['incidence_not_found'] = 'An incidence for the given code was not found... Try again.';
$string['incidence_report_at'] = 'At:';
$string['incidence_report_id'] = 'ID:';
$string['incidence_report_manager'] = 'Manager:';
$string['incidence_report_message'] = 'Message:';
$string['incidence_report_sla'] = 'SLA:';
$string['incidence_report_submit_answer_as'] = 'Submit answer as:';
$string['incidence_report_submited_by'] = 'Submitted by:';
$string['incidence_report_submitted_by'] = 'Submitted by:';
$string['incidence_report_type'] = 'Type:';
$string['incidence_report_subtype'] = 'Subtype:';
$string['mail_template_a_description'] = 'Send to given email when incidence is reported. Should include {token}.';
$string['mail_template_a_name'] = 'Mail to unidentified users on reporting.';
$string['mail_template_b_description'] = 'A manager has answer to the incidence. Should include {id}.';
$string['mail_template_b_name'] = 'Mail to inform user of response.';
$string['mail_template_c_description'] = 'A new incidence has been submited';
$string['mail_template_c_name'] = 'Mail to managers on new incidence';
$string['mail_template_d_description'] = 'An user has answered to the incidence. Should include {id}';
$string['mail_template_d_name'] = 'Mail to inform manager of response';
$string['mail_template_e_description'] = 'The incidence has been closed. Could include evaluation {points} and {id}';
$string['mail_template_e_name'] = 'Mail on closing incidence';
$string['mail_template_f_description'] = 'An incidence has expired due to user inactivity. Could include {id}.';
$string['mail_template_f_name'] = 'Mail on expiring incidence';
$string['mail_template_g_description'] = 'A new login incidence has been reported';
$string['mail_template_g_name'] = 'Mail on new login incidence';
$string['mail_template_h_description'] = 'An incidence has been assigned.';
$string['mail_template_h_name'] = 'Mail on incidence assignment.';
$string['mail_template_i_description'] = 'Se envía a los usuarios cuando un gestor les cierra una incidencia. Puede incluir: {id_incidencia}, {texto_incidencia_inicial}, {nombre_del_curso}, {nombre_del_usuario} y {url_incidencia}';
$string['mail_template_i_name'] = 'Mail cuando se cierra una incidencia.';
$string['manager_swap'] = 'Swap manager';
$string['manager_update'] = 'Manager has been updated.';
$string['mandatory_comment'] = 'Add details on what is happening...';
$string['mandatory_email'] = 'We will need an email to contact you...';
$string['mandatory_name'] = 'You must tell us who you are...';
$string['motd_editor_title'] = 'Edit message of the day';
$string['motd_form_content'] = 'MOTD Content';
$string['motd_form_type_error'] = 'Error';
$string['motd_form_type_info'] = 'Info';
$string['motd_form_type_selector'] = 'Type of MOTD';
$string['motd_form_type_success'] = 'Success';
$string['motd_form_type_warning'] = 'Warning';
$string['navigation_title'] = 'Incidences';
$string['new_incidence_submitted'] = 'New incidence submitted succesfully.';
$string['report_header_user'] = 'user';
$string['report_header_type'] = 'type';
$string['report_header_course'] = 'course';
$string['report_header_details'] = 'details';
$string['report_header_id'] = 'id';
$string['report_header_manager'] = 'manager';
$string['report_header_message'] = 'message';
$string['report_header_sla_status'] = 'sla status';
$string['report_header_status'] = 'status';
$string['report_header_submission_date'] = 'date';
$string['report_login_incidence'] = 'Report login incidence';
$string['review_incidence_status_by_code'] = 'Review incidence status by code';
$string['send_mail_incidence_mail_error'] = 'There was an error sending the mail. Copy your token or you will not be able to check the status.';
$string['send_mail_incidence_not_found'] = 'Can\'t send email. The incidence couldn\'t be fetched.';
$string['settings_max_bytes'] = 'Max bytes per answer';
$string['settings_max_bytes_desc'] = 'The maximum amount of bytes allowed for all the files attached to a singe answer.';
$string['settings_max_files'] = 'Max files per answer';
$string['settings_max_files_desc'] = 'The maximum quantity of files users can attach to a single answer.';
$string['settings_hide_after'] = 'Hide after days';
$string['settings_hide_after_desc'] = 'Days after which the closed/expired incidences will be hidden.';
$string['settings_tab_email_sheets_title'] = 'eMail Sheets';
$string['settings_tab_vars_title'] = 'Variables';
$string['settings_timeout_days'] = 'Timeout Days';
$string['settings_timeout_days_desc'] = 'Days that should pass without answer for an incidence to expire.';
$string['settings_title'] = 'Incidence Report - Setting';
$string['settings_hide_on_courses'] = 'Hide on Courses';
$string['settings_hide_on_courses_desc'] = 'A comma-separated list of ids of courses that hide the incidence report link';
$string['showing_reports_for_course'] = 'Showing reports for course <b>{$a}</b>.';
$string['submit_answer_button'] = 'Submit answer';
$string['submit_query_button'] = 'Submit query';
$string['submit_button'] = 'Submit incidence';
$string['submit_code'] = 'Incidence code';
$string['submit_comment'] = 'Comment';
$string['submit_comment_help'] = 'Add details on what is happening. By example, provide your the username you are using. NEVER provide password.';
$string['submit_email'] = 'Contact email';
$string['submit_fullname'] = 'Full name';
$string['submit_review_button'] = 'Look up';
$string['submit_status_admin_string_0'] = 'SUBMITTED';
$string['submit_status_admin_string_1'] = 'ASSIGNED';
$string['submit_status_admin_string_2'] = 'ONGOING';
$string['submit_status_admin_string_3'] = 'CLOSED';
$string['submit_status_admin_string_4'] = 'TIMEDOUT';
$string['submit_status_admin_string_5'] = 'HIDDEN';
$string['submit_status_string_0'] = 'Submitted by user';
$string['submit_status_string_1'] = 'Assigned';
$string['submit_status_string_2'] = 'On going';
$string['submit_status_string_3'] = 'Closed';
$string['submit_status_string_4'] = 'Timed out';
$string['submit_type'] = 'Type of incidence';

$string['switch_view_manager_label'] = 'Manager';
$string['switch_view_title'] = 'Change <br>View';
$string['switch_view_user_label'] = 'User';
$string['token_error'] = 'An invalid token has been generated. Please, contact and administrator.';
$string['token_info'] = 'Your incidence has been reported succesfully. You can check the status using the code <b>{$a->token}</b>. This has been also send to the given email <b>{$a->email}</b>';
$string['token_or_logged'] = 'You need either a token or be logged in to do that...';
$string['unassigned'] = 'UNASSIGNED';
$string['view_reports_on_course'] = 'Showing incidence reports for course <b>{$a}</b>';

$string['mailing_subject'] = 'Incidence Report';

$string['generalform'] = 'Filtrado';
$string['filter_empty'] = 'There are no incidence with these filters.';

$string['status'] = "Status";
$string['type'] = "Type";
$string['subtype'] = "Subtype";
$string['course'] = "Course";
$string['management'] = "Management";
$string['manager'] = "Manager";
$string['start_date'] = "Star date";
$string['final_date'] = "Final date";
$string['filters_show'] = "Show";
$string['show_categories'] = "Categories";
$string['show_courses'] = "Courses";
$string['show_managers'] = "Managers";

$string['login_required_to_view_incidence'] = "El inicio de sesión es necesario para consultar la incidencia.";

$string['submit_type_admin_string_0'] = 'LOGIN';
$string['submit_type_admin_string_1'] = 'SECRETARY';
$string['submit_type_admin_string_2'] = 'PLATFORM';
$string['submit_type_admin_string_3'] = 'COURSES';
$string['submit_type_admin_string_4'] = 'OTHERS';

//$string['submit_type_string_0'] = 'I can\'t log in';
//$string['submit_type_string_1'] = 'Technical issue';
//$string['submit_type_string_2'] = 'Functional issue';
//$string['submit_type_string_3'] = 'Other issue';
$string['submit_type_string_101'] = 'SECRETARIA - Matrícula';
$string['submit_type_string_102'] = 'SECRETARIA - Estado expediente y convalidaciones';
$string['submit_type_string_103'] = 'SECRETARIA - Anulaciones convocatoria';
$string['submit_type_string_104'] = 'SECRETARIA - Anulación matrícula y baja';
$string['submit_type_string_105'] = 'SECRETARIA - Firma de Actas de Evaluación';
$string['submit_type_string_106'] = 'SECRETARIA - Certificados';
$string['submit_type_string_107'] = 'SECRETARIA - Comisiones de Servicio';
$string['submit_type_string_201'] = 'PLATAFORMA - No LOGIN';
$string['submit_type_string_202'] = 'PLATAFORMA - Imposibilidad de ver el curso';
$string['submit_type_string_203'] = 'PLATAFORMA - Imposibilidad de entrar en alguna sección de la plataforma';
$string['submit_type_string_204'] = 'PLATAFORMA - Fallos en plugins o secciones de la plataforma';
$string['submit_type_string_205'] = 'PLATAFORMA - Errores en la matriculación de alumnos o datos personales';
$string['submit_type_string_206'] = 'PLATAFORMA - Errores en enlaces o videos';
$string['submit_type_string_207'] = 'PLATAFORMA - Errores en contenidos específicos (Libros, cuestionarios, rúbricas)';
$string['submit_type_string_208'] = 'PLATAFORMA - Problemas en la corrección de tareas';
$string['submit_type_string_209'] = 'PLATAFORMA - Errores en el calificador';
$string['submit_type_string_210'] = 'PLATAFORMA - Problemas en la entrega de tareas';
$string['submit_type_string_301'] = 'CURSOS - Plagio de tareas';
$string['submit_type_string_302'] = 'CURSOS - Reclamación de calificaciones / Solicitud';
$string['submit_type_string_303'] = 'CURSOS - Cambio de fecha de examen';
$string['submit_type_string_304'] = 'CURSOS - Lugar de examen';
$string['submit_type_string_305'] = 'CURSOS - Convocatoria de Junta de Evaluación';
$string['submit_type_string_306'] = 'CURSOS - Recogida de exámenes';
$string['submit_type_string_307'] = 'CURSOS - Incidencias en actas de calificaciones';
$string['submit_type_string_308'] = 'CURSOS - Problemas para realizar tareas';
$string['submit_type_string_309'] = 'CURSOS - Problemas para visualizar algun aparte de las secciones o bloques';
$string['submit_type_string_310'] = 'CURSOS - Problemas con als correcciones o entregas de las tareas';
$string['submit_type_string_311'] = 'CURSOS - Consulta de calificaciones o errores';
$string['submit_type_string_312'] = 'CURSOS - Imposibilidad de entrar en alguna sección de la plataforma';
$string['submit_type_string_313'] = 'CURSOS - Fallos en plugins o secciones de la plataforma';
$string['submit_type_string_314'] = 'CURSOS - Errores en la matriculación de alumnos o datos personales';
$string['submit_type_string_315'] = 'CURSOS - Errores en enlaces o videos';
$string['submit_type_string_316'] = 'CURSOS - Errores en contenidos específicos (Libros, cuestionarios, lecciones, etc..)';
$string['submit_type_string_317'] = 'CURSOS - Problemas en herramientas de evaluación como rúbricas, guías, etc..';
$string['submit_type_string_318'] = 'CURSOS - Errores en los informes de calificación (ponderación, vista, etc..)';
$string['submit_type_string_319'] = 'CURSOS - Problemas en la entrega de tareas';
$string['submit_type_string_320'] = 'CURSOS - Estado expediente y convalidaciones';
$string['submit_type_string_321'] = 'CURSOS - Anulaciones convocatoria';
$string['submit_type_string_322'] = 'CURSOS - Anulación matrícula y baja';
$string['submit_type_string_323'] = 'CURSOS - Firma de Actas de Evaluación';
$string['submit_type_string_324'] = 'CURSOS - Certificados';
$string['submit_type_string_325'] = 'CURSOS - Comisiones de Servicio';


$string['settings_customs_count'] = 'Número de grupos personalizables';
$string['settings_customs_count_desc'] = 'Número de grupos de ids de cursos que van a permitir la personalización de las incidencias que son reportables.';

$string['settings_customs_courselist'] = 'Ids de curso para la configuración {$a}';
$string['settings_customs_courselist_desc'] = 'Listado de ids de cursos, separados por comas, que se verán afectados por la configuración {$a}';
$string['settings_customs_incidencelist'] = 'Ids de tipo de incidencia para la configuración {$a}';
$string['settings_customs_incidencelist_desc'] = 'Listado de ids de los tipos de incidencia, separados por comas, que serán reportables en los cursos que se vean afectados por la configuración {$a}';
