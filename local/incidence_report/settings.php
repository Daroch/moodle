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

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/lib.php');

// Ensure the configurations for this site are set
if ($hassiteconfig) {

        $settings = new admin_settingpage('local_incidence_report', get_string('settings_title', 'local_incidence_report'));

        // Create
        $ADMIN->add('localplugins', $settings);

        //$setting = new admin_setting_configtext(
        //        'local_incidence_report/debug_mail',
        //        'Debug e-mail',
        //        'ID del usuario E-mail para enviar información de debug',
        //        '',
        //        PARAM_INT,
        //        50
        //);
        //$settings->add($setting);


        $setting = new admin_setting_configtext(
                'local_incidence_report/hide_on_courses',
                get_string('settings_hide_on_courses', 'local_incidence_report'),
                get_string('settings_hide_on_courses_desc', 'local_incidence_report'),
                '',
                PARAM_TEXT
        );
        $settings->add($setting);


        $temphideafter = get_config('local_incidence_report', 'hide_after');
        if (!$temphideafter) {
                set_config('hide_after', LOCAL_INCIDENCE_REPORT_HIDE_AFTER, 'local_incidence_report');
        }

        // TODO - El valor por defecto se pasa tambien aquí y está mal
        $setting = new admin_setting_configtext(
                'local_incidence_report/hide_after',
                get_string('settings_hide_after', 'local_incidence_report'),
                get_string('settings_hide_after_desc', 'local_incidence_report'),
                LOCAL_INCIDENCE_REPORT_HIDE_AFTER,
                PARAM_INT,
                50
        );
        $settings->add($setting);

        // MAX number of files per answer
        // MAX bytes per answer
        $tempmaxfiles = get_config('local_incidence_report', 'max_files');
        if (!$tempmaxfiles) {
                set_config('max_files', LOCAL_INCIDENCE_REPORT_MAX_FILES_PER_POST, 'local_incidence_report');
        }

        $setting = new admin_setting_configtext(
                'local_incidence_report/max_files',
                get_string('settings_max_files', 'local_incidence_report'),
                get_string('settings_max_files_desc', 'local_incidence_report'),
                LOCAL_INCIDENCE_REPORT_MAX_FILES_PER_POST,
                PARAM_INT,
                50
        );
        $settings->add($setting);

        $tempmaxfiles = get_config('local_incidence_report', 'max_bytes');
        if (!$tempmaxfiles) {
                set_config('max_bytes', LOCAL_INCIDENCE_REPORT_MAX_BYTES_PER_POST, 'local_incidence_report');
        }

        $setting = new admin_setting_configtext(
                'local_incidence_report/max_bytes',
                get_string('settings_max_bytes', 'local_incidence_report'),
                get_string('settings_max_bytes_desc', 'local_incidence_report'),
                LOCAL_INCIDENCE_REPORT_MAX_BYTES_PER_POST,
                PARAM_INT,
                50
        );
        $settings->add($setting);

        // Days to timeout.
        $tempmaxfiles = get_config('local_incidence_report', 'timeout_days');
        if (!$tempmaxfiles) {
                set_config('timeout_days', LOCAL_INCIDENCE_REPORT_TIMEOUT_DAYS, 'local_incidence_report');
        }

        $setting = new admin_setting_configtext(
                'local_incidence_report/timeout_days',
                get_string('settings_timeout_days', 'local_incidence_report'),
                get_string('settings_timeout_days_desc', 'local_incidence_report'),
                LOCAL_INCIDENCE_REPORT_TIMEOUT_DAYS,
                PARAM_INT,
                50
        );
        $settings->add($setting);

        // eMail messages TO USERS when...
        //      A) A non identified incidence is reported (delivers token)
        //      B) A reported indicence has been answered by admin
        // eMail messages TO MANAGERS when...
        //      C) There is a new incidence report
        //      D) A reported incidence has been answered by user
        //      E) A reported incidence has been closed (delivers evaluation)
        //      F) A reported incidence expires
        // eMail messages TO ADMINS when...
        //      G) There is a new login incidence report
        // Add a setting field to the settings for this page

        $setting = new admin_setting_confightmleditor(
                'local_incidence_report/mail_template_a',
                get_string('mail_template_a_name', 'local_incidence_report'),
                get_string('mail_template_a_description', 'local_incidence_report'),
                null,
                PARAM_RAW
        );
        $settings->add($setting);

        $setting = new admin_setting_confightmleditor(
                'local_incidence_report/mail_template_b',
                get_string('mail_template_b_name', 'local_incidence_report'),
                get_string('mail_template_b_description', 'local_incidence_report'),
                null,
                PARAM_RAW
        );
        $settings->add($setting);

        $setting = new admin_setting_confightmleditor(
                'local_incidence_report/mail_template_c',
                get_string('mail_template_c_name', 'local_incidence_report'),
                get_string('mail_template_c_description', 'local_incidence_report'),
                null,
                PARAM_RAW
        );
        $settings->add($setting);

        $setting = new admin_setting_confightmleditor(
                'local_incidence_report/mail_template_d',
                get_string('mail_template_d_name', 'local_incidence_report'),
                get_string('mail_template_d_description', 'local_incidence_report'),
                null,
                PARAM_RAW
        );
        $settings->add($setting);

        $setting = new admin_setting_confightmleditor(
                'local_incidence_report/mail_template_e',
                get_string('mail_template_e_name', 'local_incidence_report'),
                get_string('mail_template_e_description', 'local_incidence_report'),
                null,
                PARAM_RAW
        );
        $settings->add($setting);

        $setting = new admin_setting_confightmleditor(
                'local_incidence_report/mail_template_f',
                get_string('mail_template_f_name', 'local_incidence_report'),
                get_string('mail_template_f_description', 'local_incidence_report'),
                null,
                PARAM_RAW
        );
        $settings->add($setting);

        $setting = new admin_setting_confightmleditor(
                'local_incidence_report/mail_template_g',
                get_string('mail_template_g_name', 'local_incidence_report'),
                get_string('mail_template_g_description', 'local_incidence_report'),
                null,
                PARAM_RAW
        );
        $settings->add($setting);

        $setting = new admin_setting_confightmleditor(
                'local_incidence_report/mail_template_h',
                get_string('mail_template_h_name', 'local_incidence_report'),
                get_string('mail_template_h_description', 'local_incidence_report'),
                null,
                PARAM_RAW
        );
        $settings->add($setting);

        $setting = new admin_setting_confightmleditor(
                'local_incidence_report/mail_template_i',
                get_string('mail_template_i_name', 'local_incidence_report'),
                get_string('mail_template_i_description', 'local_incidence_report'),
                null,
                PARAM_RAW
        );
        $settings->add($setting);

        // TODO - Estas cadenas deberian estar en los archivos de idiomas. -- jmazcunan
        $settings->add(
                new admin_setting_heading(
                        'local_incidence_report/heading_customs',
                        'Bloques de configuración',
                        'Permite establecee conjuntos de cursos y los tipos de incidencia que serán reportables en estos.',
                )
        );

        $customs_count = get_config('local_incidence_report', 'customs_count');

        if (!is_numeric($customs_count)) {
                $customs_count = 1;
        }

        $setting = new admin_setting_configtext(
                'local_incidence_report/customs_count',
                get_string('settings_customs_count', 'local_incidence_report'),
                get_string('settings_customs_count_desc', 'local_incidence_report'),
                1,
                PARAM_TEXT
        );
        $settings->add($setting);

        $aux = 1;
        while ($aux <= $customs_count) {

                $setting = new admin_setting_configtext(
                        'local_incidence_report/custom_courselist_' . $aux,
                        get_string('settings_customs_courselist', 'local_incidence_report', $aux),
                        get_string('settings_customs_courselist_desc', 'local_incidence_report', $aux),
                        '',
                        PARAM_TEXT
                );
                $settings->add($setting);

                $setting = new admin_setting_configtext(
                        'local_incidence_report/custom_incidencelist_' . $aux,
                        get_string('settings_customs_incidencelist', 'local_incidence_report', $aux),
                        get_string('settings_customs_incidencelist_desc', 'local_incidence_report', $aux),
                        '',
                        PARAM_TEXT
                );
                $settings->add($setting);

                $aux++;
        }


}