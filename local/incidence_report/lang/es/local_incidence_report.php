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
$string['pluginname'] = 'Reporte de incidencias';

$string['forbidden_access'] = 'El usuario no tiene permisos para utilizar este plugin.';
$string['navigation_title'] = 'Reporte de incidencias';

$string['add_new_report_course'] = 'añadir nueva incidencia para el curso {$a}';
$string['add_new_report_general'] = 'añadir nueva incidencia general';
$string['add_new_report_on_course'] = 'Añadiendo una nueva incidencia para el curso <b>{$a}</b>';
$string['answer_duplication_error'] = 'Parece que estás duplicando una respuesta.';
$string['answer_submitted'] = 'Se ha enviado una respuesta...';
$string['attachments'] = 'Adjuntos';
$string['cant_swap_manager'] = 'Cambio de gestor inválido.';
$string['clear_filter'] = 'limpiar filtro';
$string['close_incidence_button'] = 'Cerrar incidencia';
$string['closing_incidence'] = 'Cerrando incidencia. Para cerrar la incidencia debes seleccionar el icono que muestre tu nivel de satisfacción.';
$string['continue'] = 'Continuar';
$string['course_0_shortname'] = '-';
$string['error_showing_reports_for_course'] = 'No se encuentra el curso.';
$string['filter_status_title'] = 'Filtrar <br> estado';
$string['filter_type_title'] = 'Filtrar <br> tipo';
$string['incidence_already_closed'] = 'La incidencia ya había sido cerrada...';
$string['incidence_closed'] = 'La incidencia se ha cerrado correctamente.';
$string['incidence_message_empty'] = 'Debes aportar algún tipo de comentario.';
$string['incidence_not_found'] = 'No se ha encontrado una incidencia asociada al código proporcionado.';
$string['incidence_report_at'] = 'En:';
$string['incidence_report_id'] = 'ID:';
$string['incidence_report_manager'] = 'Gestor:';
$string['incidence_report_message'] = 'Mensaje:';
$string['incidence_report_sla'] = 'SLA:';
$string['incidence_report_submit_answer_as'] = 'Enviar respuesta como:';
$string['incidence_report_submited_by'] = 'Enviada por:';
$string['incidence_report_submitted_by'] = 'Enviado por:';
$string['incidence_report_type'] = 'Tipo:';
$string['incidence_report_subtype'] = 'Subtipo:';
$string['mail_template_a_description'] = 'Se envía al mail proporcionado por el usuario al reportar. Debería incluir el {token}.';
$string['mail_template_a_name'] = 'Mail a los usuarios no identificados cuando reportan.';
$string['mail_template_b_description'] = 'Se envía cuando un gestor ha contestado a la incidencia. Puede incluir: {id_incidencia}, {texto_incidencia_inicial}, {nombre_del_curso}, {nombre_del_usuario} y {url_incidencia}';
$string['mail_template_b_name'] = 'Mail para informar al usuario de una respuesta.';
$string['mail_template_c_description'] = 'Una nueva incidencia ha sido reportada. Puede incluir: {id_incidencia}, {texto_incidencia_inicial}, {nombre_del_curso}, {nombre_del_usuario} y {url_incidencia}';
$string['mail_template_c_name'] = 'Mail a los gestores cuando se reporta una incidencia.';
$string['mail_template_d_description'] = 'Un usuario ha contestado a la incidencia. Puede incluir: {id_incidencia}, {texto_incidencia_inicial}, {nombre_del_curso}, {nombre_del_usuario} y {url_incidencia}';
$string['mail_template_d_name'] = 'Mail para informar al gestor de la respuesta del usuario.';
$string['mail_template_e_description'] = 'Al evaluar una incidencia se informa al gestor. Puede incluir: {id_incidencia}, {texto_incidencia_inicial}, {nombre_del_curso}, {nombre_del_usuario} y {url_incidencia}';
$string['mail_template_e_name'] = 'Mail cuando se evalua una incidencia.';
$string['mail_template_f_description'] = 'Se envía a los gestores cuando una incidencia caduca. Puede incluir: {id_incidencia}, {texto_incidencia_inicial}, {nombre_del_curso}, {nombre_del_usuario}, {url_incidencia} y {points}';
$string['mail_template_f_name'] = 'Mail cuando una indicencia caduca.';
$string['mail_template_g_description'] = 'Se envía a los administradores cuando se reporta una nueva incidencia de inicio de sesión. Puede incluir: {id_incidencia}, {texto_incidencia_inicial}, {nombre_del_curso}, {nombre_del_usuario} y {url_incidencia}';
$string['mail_template_g_name'] = 'Mail cuando se reporta una incidencia de login.';
$string['mail_template_h_description'] = 'Se envía a los gestores cuando se les asigna una incidencia. Puede incluir: {id_incidencia}, {texto_incidencia_inicial}, {nombre_del_curso}, {nombre_del_usuario} y {url_incidencia}';
$string['mail_template_h_name'] = 'Mail cuando se reasigna una incidencia.';
$string['mail_template_i_description'] = 'Se envía a los usuarios cuando un gestor les cierra una incidencia. Puede incluir: {id_incidencia}, {texto_incidencia_inicial}, {nombre_del_curso}, {nombre_del_usuario} y {url_incidencia}';
$string['mail_template_i_name'] = 'Mail cuando se cierra una incidencia.';
$string['manager_swap'] = 'Cambiar gestor';
$string['manager_update'] = 'Se ha actualizado el gestor.';
$string['mandatory_comment'] = 'Añade detalles sobre qué está pasando...';
$string['mandatory_email'] = 'Necesitamos una dirección de correo para contactarte...';
$string['mandatory_name'] = 'Debes indicarnos quién eres...';
$string['motd_editor_title'] = 'Editar el mensaje del día';
$string['motd_form_content'] = 'Contenido del MdD';
$string['motd_form_type_error'] = 'Error';
$string['motd_form_type_info'] = 'Info';
$string['motd_form_type_selector'] = 'Tipo de MdD';
$string['motd_form_type_success'] = 'Éxito';
$string['motd_form_type_warning'] = 'Advertencia';
$string['navigation_title'] = 'Incidencias';
$string['new_incidence_submitted'] = 'Se he enviado con éxito una nueva incidencia.';
$string['report_header_user'] = 'usuario';
$string['report_header_type'] = 'tipo';
$string['report_header_course'] = 'curso';
$string['report_header_details'] = 'detalles';
$string['report_header_id'] = 'id';
$string['report_header_manager'] = 'gestor';
$string['report_header_message'] = 'mensaje';
$string['report_header_sla_status'] = 'estado SLA';
$string['report_header_status'] = 'estado';
$string['report_header_submission_date'] = 'fecha';
$string['report_login_incidence'] = 'Reportar una incidencia de inicio de sesión';
$string['review_incidence_status_by_code'] = 'Revisar el estado de una incidencia usando un código';
$string['send_mail_incidence_mail_error'] = 'Se ha producido un error enviando el correo. Copia el token o no podrás consultar el estado de la incidencia.';
$string['send_mail_incidence_not_found'] = 'No se ha podido enviar el email. La incidencia no se ha encontrado.';
$string['settings_max_bytes'] = 'Máximos bytes por respuesta.';
$string['settings_max_bytes_desc'] = 'El máximo tamaño que puede ocupar la suma de tamaños de los archivos adjuntados a una incidencia.';
$string['settings_hide_after'] = 'Esconder después de días';
$string['settings_hide_after_desc'] = 'Días que tienen que pasar para que las incidencias cerradas/caducadas no se muestren.';
$string['settings_max_files'] = 'Máximo número de archivos por respuesta';
$string['settings_max_files_desc'] = 'Cantidad máxima de archivos que pueden adjuntarse a una respuesta.';
$string['settings_tab_email_sheets_title'] = 'Plantillas de correo';
$string['settings_tab_vars_title'] = 'Variables';
$string['settings_timeout_days'] = 'Días para caducar';
$string['settings_timeout_days_desc'] = 'Transcurridos esos días sin respuesta por parte del usuario, la incidencia caduca.';
$string['settings_title'] = 'Reporte de incidencias - Configuración';
$string['settings_hide_on_courses'] = 'Ocultar en cursos';
$string['settings_hide_on_courses_desc'] = 'Listado de los id de los cursos separados por comas en los que no se mostrará el acceso al plugin de incidencias';
$string['showing_reports_for_course'] = 'Mostrando reportes para el curso <b>{$a}</b>.';
$string['submit_answer_button'] = 'Enviar respuesta';
$string['submit_query_button'] = 'Enviar consulta';
$string['submit_button'] = 'Enviar incidencia';
$string['submit_code'] = 'Código de incidencia';
$string['submit_comment'] = 'Comentario';
$string['submit_comment_help'] = 'Añade detalles sobre qué está pasando. Por ejemplo, proporciona tu nombre de usuarios. NUNCA indiques contraseñas.';
$string['submit_email'] = 'eMail de contacto';
$string['submit_fullname'] = 'Nombre completo';
$string['submit_review_button'] = 'Buscar';
$string['submit_status_admin_string_0'] = 'ENVIADA';
$string['submit_status_admin_string_1'] = 'ASIGNADA';
$string['submit_status_admin_string_2'] = 'PROCESANDO';
$string['submit_status_admin_string_3'] = 'CERRADA';
$string['submit_status_admin_string_4'] = 'CADUCADA';
$string['submit_status_admin_string_5'] = 'OCULTAS';
$string['submit_status_string_0'] = 'Enviada por el  usuario';
$string['submit_status_string_1'] = 'Asignada';
$string['submit_status_string_2'] = 'En proceso';
$string['submit_status_string_3'] = 'Cerrada';
$string['submit_status_string_4'] = 'Caducada';
$string['submit_type'] = 'Tipo de incidencia';

$string['switch_view_manager_label'] = 'Gestor';
$string['switch_view_title'] = 'Cambiar <br>vista';
$string['switch_view_user_label'] = 'Usuario';
$string['token_error'] = 'El token generado ha resultado inválido. Por favor, contacta con un administrador.';
$string['token_info'] = 'Tu incidencia ha sido reportada correctamente. Puedes consultar su estado usando el código: <b>{$a->token}</b>. Esta información se ha enviado tambien al correo proporcionado <b>{$a->email}</b>';
$string['token_or_logged'] = 'Necesitas o un token o estar logeado para hacer eso...';
$string['unassigned'] = 'SIN ASIGNAR';
$string['view_reports_on_course'] = 'Mostrando incidencias para el curso <b>{$a}</b>';

$string['mailing_subject'] = 'Reporte de incidencias';

$string['generalform'] = 'Filtrado';
$string['filter_empty'] = 'No existen incidencias con estos filtros.';

$string['status'] = "Estado";
$string['type'] = "Tipo";
$string['subtype'] = "Subtipo";
$string['course'] = "Curso";
$string['management'] = "Gerencia";
$string['manager'] = "Gestor";
$string['start_date'] = "Fecha inicial";
$string['final_date'] = "Fecha final";
$string['show_categories'] = "Categorias";
$string['show_courses'] = "Cursos";
$string['show_managers'] = "Gestores";
$string['filters_show'] = "Mostrar";

$string['login_required_to_view_incidence'] = "El inicio de sesión es necesario para consultar la incidencia.";

$string['submit_type_admin_string_0'] = 'LOGIN';
$string['submit_type_admin_string_1'] = 'SECRETARÍA';
$string['submit_type_admin_string_2'] = 'PLATAFORMA';
$string['submit_type_admin_string_3'] = 'CURSOS';
$string['submit_type_admin_string_4'] = 'OTROS';

//$string['submit_type_string_0'] = 'No puedo iniciar sesión';
//$string['submit_type_string_1'] = 'Incidencia técnica';
//$string['submit_type_string_2'] = 'Incidencia funcional';
//$string['submit_type_string_3'] = 'Otra incidencia';
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
