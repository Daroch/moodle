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

// PROFILES
define('LOCAL_INCIDENCE_REPORT_PROFILE_ALUMNO', 1); // Matricula en X cursos
define('LOCAL_INCIDENCE_REPORT_PROFILE_TUTOR', 2); // Matriculado en 1 curso
define('LOCAL_INCIDENCE_REPORT_PROFILE_PROFESOR', 3); // <- No se usa
define('LOCAL_INCIDENCE_REPORT_PROFILE_COORDINADOR', 4); // Matriculado en X cursos
define('LOCAL_INCIDENCE_REPORT_PROFILE_JEFATURA', 5); // ADMIN
define('LOCAL_INCIDENCE_REPORT_PROFILE_SECRETARIA', 6); // ADMIN
define('LOCAL_INCIDENCE_REPORT_PROFILE_MOODLE', 7); // ADMIN
define('LOCAL_INCIDENCE_REPORT_PROFILE_FEDERACION', 8); // No responde incidencias

// PROFILE ENABLED
// TODO This is not implemented. Just left as a posible future update.
define('LOCAL_INCIDENCE_REPORT_PROFILE_ENABLED', array(
    LOCAL_INCIDENCE_REPORT_PROFILE_ALUMNO,
    LOCAL_INCIDENCE_REPORT_PROFILE_TUTOR,
    //    LOCAL_INCIDENCE_REPORT_PROFILE_PROFESOR,
    LOCAL_INCIDENCE_REPORT_PROFILE_COORDINADOR,
    LOCAL_INCIDENCE_REPORT_PROFILE_JEFATURA,
    LOCAL_INCIDENCE_REPORT_PROFILE_SECRETARIA,
    LOCAL_INCIDENCE_REPORT_PROFILE_MOODLE,
    LOCAL_INCIDENCE_REPORT_PROFILE_FEDERACION,
));

// MANAGERS
// Profiles here would be candidate for managing INCIDENCE TYPES
define('LOCAL_INCIDENCE_REPORT_DEFAULT_MANAGER_ROLES', array(
    //LOCAL_INCIDENCE_REPORT_PROFILE_ALUMNO,
    LOCAL_INCIDENCE_REPORT_PROFILE_TUTOR,
    LOCAL_INCIDENCE_REPORT_PROFILE_PROFESOR,
    LOCAL_INCIDENCE_REPORT_PROFILE_COORDINADOR,
    LOCAL_INCIDENCE_REPORT_PROFILE_JEFATURA,
    LOCAL_INCIDENCE_REPORT_PROFILE_SECRETARIA,
    LOCAL_INCIDENCE_REPORT_PROFILE_MOODLE,
    LOCAL_INCIDENCE_REPORT_PROFILE_FEDERACION,
));
// Relationship PROFILE->MANAGED INCIDENCES
define('LOCAL_INCIDENCE_REPORT_PROFILE_TUTOR_MANAGES', [319]);
define('LOCAL_INCIDENCE_REPORT_PROFILE_PROFESOR_MANAGES', []);
define('LOCAL_INCIDENCE_REPORT_PROFILE_COORDINADOR_MANAGES', [314, 315]);
define('LOCAL_INCIDENCE_REPORT_PROFILE_JEFATURA_MANAGES', []);
define('LOCAL_INCIDENCE_REPORT_PROFILE_SECRETARIA_MANAGES', [320,321,322,323,324,325]);
define('LOCAL_INCIDENCE_REPORT_PROFILE_MOODLE_MANAGES', [201, 202, 312, 313, 314, 315, 316, 317, 318]);
define('LOCAL_INCIDENCE_REPORT_PROFILE_FEDERACION_MANAGES', []);


// GLOBAL PROFILES
// Global profiles are not related to courses. Non global profiles should be enrolled in courses
define('LOCAL_INCIDENCE_REPORT_GLOBAL_PROFILES', array(
    //LOCAL_INCIDENCE_REPORT_PROFILE_ALUMNO,
    //LOCAL_INCIDENCE_REPORT_PROFILE_TUTOR,
    //LOCAL_INCIDENCE_REPORT_PROFILE_PROFESOR,
    //LOCAL_INCIDENCE_REPORT_PROFILE_COORDINADOR,
    LOCAL_INCIDENCE_REPORT_PROFILE_JEFATURA,
    LOCAL_INCIDENCE_REPORT_PROFILE_SECRETARIA,
    LOCAL_INCIDENCE_REPORT_PROFILE_MOODLE,
    LOCAL_INCIDENCE_REPORT_PROFILE_FEDERACION,
));

// PROFILE ROLES
// CAUTION: Don't repeat roles between profiles. If so, the profiles will be equivalent.
define('LOCAL_INCIDENCE_REPORT_PROFILE_ROLES_ALUMNO', array('"student"', '"convalidado"'));
define('LOCAL_INCIDENCE_REPORT_PROFILE_ROLES_TUTOR', array('"tutor"'));
define('LOCAL_INCIDENCE_REPORT_PROFILE_ROLES_PROFESOR', array('"tutor"')); // NO SE USA
define('LOCAL_INCIDENCE_REPORT_PROFILE_ROLES_COORDINADOR', array('"coordinador"'));
define('LOCAL_INCIDENCE_REPORT_PROFILE_ROLES_JEFATURA', array('"jefatura"'));
define('LOCAL_INCIDENCE_REPORT_PROFILE_ROLES_SECRETARIA', array('"secretaria"'));
define('LOCAL_INCIDENCE_REPORT_PROFILE_ROLES_MOODLE', array('"admin"'));
define('LOCAL_INCIDENCE_REPORT_PROFILE_ROLES_FEDERACION', array('"supervisor"'));

// PROFILE CAPABILITIES
// TODO This is not implemented. Just left as a posible future update.
define('LOCAL_INCIDENCE_REPORT_PROFILE_CAPABILITIES_ALUMNO', array());
define('LOCAL_INCIDENCE_REPORT_PROFILE_CAPABILITIES_TUTOR', array());
define('LOCAL_INCIDENCE_REPORT_PROFILE_CAPABILITIES_PROFESOR', array());
define('LOCAL_INCIDENCE_REPORT_PROFILE_CAPABILITIES_COORDINADOR', array());
define('LOCAL_INCIDENCE_REPORT_PROFILE_CAPABILITIES_JEFATURA', array());
define('LOCAL_INCIDENCE_REPORT_PROFILE_CAPABILITIES_SECRETARIA', array());
define('LOCAL_INCIDENCE_REPORT_PROFILE_CAPABILITIES_MOODLE', array());
define('LOCAL_INCIDENCE_REPORT_PROFILE_CAPABILITIES_FEDERACION', array());


/**
 * Returns true|false if the profile should be considered global.
 * In origin refers to the need of the role to be assigned on a given context (false) or not (true).
 *
 * @param integer $profile
 * @return boolean
 */
function local_incidence_report_profile_isglobal(int $profile): bool {
    $isglobalprofile = false;
    if (in_array($profile, LOCAL_INCIDENCE_REPORT_GLOBAL_PROFILES)) {
        $isglobalprofile = true;
    }

    return $isglobalprofile;
}

/**
 * Gets the shortname (roles) array for the given profile or NULL
 *
 * @param [type] $profile
 * @return array
 */
function local_incidence_report_profile_get_shortnamearray($profile): array {
    $shortnamearray = array();

    switch ($profile) {
        case LOCAL_INCIDENCE_REPORT_PROFILE_ALUMNO:
            $shortnamearray = LOCAL_INCIDENCE_REPORT_PROFILE_ROLES_ALUMNO;
            break;
        case LOCAL_INCIDENCE_REPORT_PROFILE_TUTOR:
            $shortnamearray = LOCAL_INCIDENCE_REPORT_PROFILE_ROLES_TUTOR;
            break;
        case LOCAL_INCIDENCE_REPORT_PROFILE_PROFESOR:
            $shortnamearray = LOCAL_INCIDENCE_REPORT_PROFILE_ROLES_PROFESOR;
            break;
        case LOCAL_INCIDENCE_REPORT_PROFILE_COORDINADOR:
            $shortnamearray = LOCAL_INCIDENCE_REPORT_PROFILE_ROLES_COORDINADOR;
            break;
        case LOCAL_INCIDENCE_REPORT_PROFILE_JEFATURA:
            $shortnamearray = LOCAL_INCIDENCE_REPORT_PROFILE_ROLES_JEFATURA;
            break;
        case LOCAL_INCIDENCE_REPORT_PROFILE_SECRETARIA:
            $shortnamearray = LOCAL_INCIDENCE_REPORT_PROFILE_ROLES_SECRETARIA;
            break;
        case LOCAL_INCIDENCE_REPORT_PROFILE_MOODLE:
            $shortnamearray = LOCAL_INCIDENCE_REPORT_PROFILE_ROLES_MOODLE;
            break;
        case LOCAL_INCIDENCE_REPORT_PROFILE_FEDERACION:
            $shortnamearray = LOCAL_INCIDENCE_REPORT_PROFILE_ROLES_FEDERACION;
            break;
    }

    return $shortnamearray;
}

/**
 * Gets the shortname (roles) list for the given profile or empty string.
 *
 * @param [type] $profile
 * @return string
 */
function local_incidence_report_profile_get_shortnamelist($profile) {
    $shortnamelist = implode(',', local_incidence_report_profile_get_shortnamearray($profile));

    return $shortnamelist;
}

/**
 * Checks is the user matches the desired profile.
 *
 * @param integer $profile
 * @param integer $userid
 * @return boolean
 *
 * @author jmazcunan
 */
function local_incidence_report_profile_check(int $profile, int $context = 0, int $instanceid = 0, int $userid = 0, bool $forceglobalprofile = false): bool {
    global $USER;
    global $DB;

    if ($userid === 0) {
        $userid = $USER->id;
    }

    if ($forceglobalprofile) {
        $isglobalprofile = true;
    } else {
        $isglobalprofile = local_incidence_report_profile_isglobal($profile);
    }

    if ($context === 0) {
        //$context = CONTEXT_SYSTEM;
    }

    $sql = 'SELECT ra.id, ra.userid AS userid,
                   r.shortname AS shortname,
                   ctx.instanceid AS instanceid
              FROM {role_assignments} AS ra
                   JOIN {context} AS ctx ON (';
    if (($isglobalprofile === false) && ($context != 0)) {
        $sql .= 'ctx.contextlevel=:context AND ';
    }
    $sql .= 'ctx.id = ra.contextid)
                   JOIN {role} AS r ON (r.id = ra.roleid)
             WHERE userid=:userid ';

    $shortnamelist = local_incidence_report_profile_get_shortnamelist($profile);
    if ($shortnamelist !== null) {
        $sql .= ' AND r.shortname IN (' . local_incidence_report_profile_get_shortnamelist($profile) . ') ';
    }

    if ($isglobalprofile === false) {
        if ($context === CONTEXT_COURSECAT || $context == CONTEXT_COURSE) {
            $sql .= ' AND instanceid=:instanceid ';
        }
    }

    $params = array(
        'userid' => $userid,
        'context' => $context,
        'instanceid' => $instanceid
    );

    //var_dump($isglobalprofile);

    //$DB->set_debug(true);
    $results = $DB->get_records_sql($sql, $params);
    //echo '<pre>';
    //var_dump($results);
    //echo '</pre>';
    //$DB->set_debug(false);
    //var_dump($results);

    if (count($results) > 0) {
        return true;
    }

    return false;
}

/**
 * Returns an array of users with the given profile in the given context
 *
 * @param integer $profile
 * @param integer $userid
 * @return arrau
 *
 * @author jmazcunan
 */
function local_incidence_report_profile_get(int $profile, int $context = 0, int $instanceid = 0, $debug = false) {
    global $USER;
    global $DB;

    $isglobalprofile = local_incidence_report_profile_isglobal($profile);

    if ($debug) {
        echo '<pre>';
        echo ($isglobalprofile) ? 'GLOBAL' : 'COURSE';
        echo '<br>';
        echo 'CONTEXT:'.$context.'<br>';
        echo 'INSTANCE:'.$instanceid.'<br>';
    }

    $shortnamelist = implode(',', local_incidence_report_profile_get_shortnamearray($profile));

    $sql = 'SELECT u.id, u.username, u.firstname, u.lastname, u.email
              FROM {role_assignments} AS ra
                   JOIN {role} AS r ON (r.id = ra.roleid)
                   JOIN {context} AS c ON (c.id = ra.contextid)
                   JOIN {user} AS u ON (u.id = ra.userid)
             WHERE r.shortname IN (' . $shortnamelist . ') ';

    // Esta parte se puede trabajar un poco mas...
    if ($isglobalprofile === false) {
        if ($instanceid === 0) {
            $context = CONTEXT_SYSTEM;
        }
        $sql .= ' AND c.contextlevel=:context ';
        if ($context == CONTEXT_COURSECAT || $context == CONTEXT_COURSE) {
            $sql .= ' AND instanceid=:instanceid ';
        }
    }

    $sql .= ' GROUP BY u.id';

    $params = array(
        'context' => $context,
        'instanceid' => $instanceid
    );

    if ($debug) {
        $DB->set_debug(true);
    }
    $results = $DB->get_records_sql($sql, $params);
    if ($debug) {
        $DB->set_debug(false);
        //var_dump($results);
        echo '</pre>';
    }

    return $results;
}
