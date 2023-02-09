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

define(['jquery'], function ($) {

    function local_incidence_report_close_all_modals() {
        $('#incidence-report-modal-background').addClass('incidence-report-hidden');
        $('.incidence-report-modal').addClass('incidence-report-hidden');
    }

    return {
        init: function () {

            $('.incidence-report-modal-view').on('click', function (e) {
                console.log("eye-click...");
                var id = e.target.dataset.id;
                $('#incidence-report-modal-background').removeClass('incidence-report-hidden');
                $('div[data-id=' + id + ']').removeClass('incidence-report-hidden');
            });

            $('#incidence-report-modal-background').on('click', function (e) {
                local_incidence_report_close_all_modals();
            });

            $('.incidence-report-modal').on('click', function (e) {
                // Don't close to allow copy+paste...
            });

            $('.incidence-report-answer').on('click', function (e) {
                var id = e.target.dataset.id;
                window.location.href = location.protocol + '//' + location.host + location.pathname
                        + '?action=view&incidenceid=' + id;
            });

            $('#motd-editor-title').on('click', function (e) {
                $('#motd-editor-wrapper').slideToggle();
            });

            $('.local-incidence-report-close-incidence').on('click', function (e) {
                var points = e.target.dataset.points;
                var token = e.target.dataset.token;
                var incidenceid = e.target.dataset.incidenceid;

                window.location.href = location.protocol + '//' + location.host + location.pathname
                        + '?action=close&token=' + token + '&points=' + points + '&incidenceid=' + incidenceid;
            });

            $('.local-incidence-report-satisfaction-icon').on('click', function (e) {
                var points = e.target.dataset.points;
                var token = e.target.dataset.token;
                var incidenceid = e.target.dataset.incidenceid;

                window.location.href = location.protocol + '//' + location.host + location.pathname
                        + '?action=close&token=' + token + '&points=' + points + '&incidenceid=' + incidenceid;
            });         



        }
    }
});
