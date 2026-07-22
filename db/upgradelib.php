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

/**
 * Shared helper functions for local_monlaututoria install/upgrade steps.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Seeds the reason and modality catalogues with their stable initial values.
 *
 * Idempotent by shortname: only missing rows are inserted, so it is safe to
 * call this from both db/install.php (fresh installs) and db/upgrade.php
 * (existing installs upgrading into the version that introduced these tables).
 * The visible `name` is resolved from a language string at seed time; all
 * later application logic must key off `shortname`, never off `name`.
 */
function local_monlaututoria_seed_catalogues(): void {
    global $USER;

    $reasonrepository = new \local_monlaututoria\repository\reason_repository();
    $modalityrepository = new \local_monlaututoria\repository\modality_repository();

    $userid = (!empty($USER->id) && $USER->id > 0) ? (int) $USER->id : get_admin()->id;

    $reasonshortnames = [
        'acogida_inicial',
        'seguimiento_ordinario',
        'rendimiento_academico',
        'asistencia',
        'puntualidad',
        'convivencia',
        'motivacion',
        'habitos_estudio',
        'organizacion',
        'orientacion_academica',
        'orientacion_profesional',
        'practicas_empresa',
        'situacion_personal',
        'seguimiento_acuerdos',
        'contacto_familia',
        'solicitud_alumno',
        'solicitud_familia',
        'reconocimiento_positivo',
        'derivacion',
        'otro',
    ];

    foreach ($reasonshortnames as $index => $shortname) {
        if ($reasonrepository->shortname_exists($shortname)) {
            continue;
        }

        $reasonrepository->create((object) [
            'name'      => get_string('reason_seed_' . $shortname, 'local_monlaututoria'),
            'shortname' => $shortname,
            'sortorder' => $index + 1,
            'createdby' => $userid,
        ]);
    }

    $modalityshortnames = [
        'presencial',
        'telefono',
        'videoconferencia',
        'correo_electronico',
        'mensajeria',
        'reunion_coordinacion',
        'otra',
    ];

    foreach ($modalityshortnames as $index => $shortname) {
        if ($modalityrepository->shortname_exists($shortname)) {
            continue;
        }

        $modalityrepository->create((object) [
            'name'      => get_string('modality_seed_' . $shortname, 'local_monlaututoria'),
            'shortname' => $shortname,
            'sortorder' => $index + 1,
            'createdby' => $userid,
        ]);
    }
}
