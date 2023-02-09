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

    function showCurso(gerencia) {
        if (gerencia) {
            $.ajax({
                url: M.cfg.wwwroot + '/local/incidence_report/data.php',
                type: 'post',
                data: {
                    action: "getCurso",
                    gerencia: gerencia,
                },
                success: function (response) {
                    console.log(response);
                    var len = response.length;
                    $("#id_courses").empty();
                    if (len == 0 || response[0].id == 1) {
                        //$("#id_courses").append("<option value='0'>No existen o no se puede filtrar</option>");
                        $("#id_courses").append("<option>Seleccione primero una categoría</option>");                        
                    } else {
                        $("#id_courses").append("<option value='0'>Todos los cursos de la categoría</option>");
                        for (var i = 0; i < len; i++) {
                            var id = response[i]["id"];
                            var name = response[i]['name'];
                            if (id != 1)
                                $("#id_courses").append("<option value='" + id + "'>" + name + "</option>");
                        }
                    }
                }
            });
        } else {
            $("#id_courses").empty();
            $("#id_courses").append("<option>Seleccione primero una categoría</option>");
        }
    }

    function showGestor(gerencia = 0, courseid = 0) {
        $.ajax({
            url: M.cfg.wwwroot + '/local/incidence_report/data.php',
            type: 'post',
            data: {
                action: "getGestor",
                gerencia: gerencia,
                courseid: courseid,
            },
            success: function (response) {
                var len = response.length;
                $("#id_gestors").empty();
                if (len == 0) {
                    $("#id_gestors").append("<option value='0'>No existen, no se puede filtrar</option>");
                } else {
                    $("#id_gestors").empty();
                    if (courseid != 0) {
                        $("#id_gestors").append("<option value='0'>Todos los gestores del curso</option>");
                    } else {
                        $("#id_gestors").append("<option value='0'>Todos los gestores de la categoría</option>");
                    }
                    for (var i = 0; i < len; i++) {
                        var id = response[i]["id"];
                        var firstname = response[i]['firstname'];
                        var lastname = response[i]['lastname'];
                        $("#id_gestors").append("<option value='" + id + "'>" + firstname, lastname + "</option>");
                    }
                }
            }
        });
    }

    function openTab(tabName, ele) {
        var i;
        var x = document.getElementsByClassName("tab-hidden");
        for (i = 0; i < x.length; i++) {
            x[i].style.display = "none";
        }
        document.getElementById(tabName).style.display = "block";
        ele.parent().find('.tab-button').removeClass("active");
        ele.addClass("active");
    }

    return {
        init: function () {
            $(document).ready(function () {
                console.log("ready!");
                showGestor();
            });
            // Seleccionamos una Gerencia, desplegamos el section curso
            $("#id_gerencia").change(function () {
                var gerencia = $("#id_gerencia").val();
                showCurso(gerencia);
            });
            // Seleccionamos una Gerencia, desplegamos el section gestor
            $("#id_gerencia").change(function () {
                var gerencia = $("#id_gerencia").val();
                showGestor(gerencia);
            });
            $('#id_categoria').change(function () {
                $(".categoria").toggle();
            });
            $('#id_gestor').change(function () {
                $(".gestor").toggle();
            });
            $('#id_curso').change(function () {
                $(".curso").toggle();
            });
            $('#id_courses').change(function () {
                var gerencia = $("#id_gerencia").val();
                var courseid = $("#id_courses").val();
                showGestor(gerencia, courseid);
            });
            $('#show-review-tab').on('click', function (e) {
                openTab('review-tab', $(e.target));
            });
            $('#show-filter-tab').on('click', function (e) {
                openTab('filter-tab', $(e.target));
            });
        }
    };
});