<?php

defined('MOODLE_INTERNAL') || die();
$functions = array(
    'local_custom_service_update_courses_lti' => array(
        'classname' => 'local_custom_service_external',
        'methodname' => 'update_courses_lti',
        'classpath' => 'local/custom_service/externallib.php',
        'description' => 'Update courses LTI to show in Gradebook',
        'type' => 'write',
        'ajax' => true,
    ),
    'local_custom_service_update_courses_sections' => array(
        'classname' => 'local_custom_service_external',
        'methodname' => 'update_courses_sections',
        'classpath' => 'local/custom_service/externallib.php',
        'description' => 'Update courses sections title in DB',
        'type' => 'write',
        'ajax' => true,
    ),
    'local_custom_service_verificacion_manual_user' => array(
        'classname' => 'local_custom_service_external',
        'methodname' => 'verificacion_manual_user',
        'classpath' => 'local/custom_service/externallib.php',
        'description' => 'Verificar usuario in DB',
        'type' => 'write',
        'ajax' => true,
    )
);

$services = array(
    'Custom Web Services' => array(
        'functions' => array(
            'local_custom_service_update_courses_lti',
            'local_custom_service_update_courses_sections',
            'local_custom_service_verificacion_manual_user'
        ),
        'restrictedusers' => 0,
        'enabled' => 1,
        'shortname' => 'custom_course_services'
    )
);