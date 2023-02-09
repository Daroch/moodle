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

$plugin->version = 20201001094203;

$plugin->requires = 2018051705.00;

$plugin->component = 'local_incidence_report';
$plugin->maturity = MATURITY_BETA;
$plugin->release = 'beta2';

// Change log
// 2020 04 06 > Version 2019121817
//              - Updates on CSS in (18, 19)
//              - Adds a new filter for admins, by username.
//              - Click on username on reports table acts as the filter button.
//              - Casting a username filter cleans the other filters to avoid viewing empty results due to other filtering.
// 2020 04 06 > Version 2019121820
//              - Previous version has a bug.
//              - Solved
//             [!]Need to review the SQL for get reports as it uses a JOIN that may be unnecesary.
