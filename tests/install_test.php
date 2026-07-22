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

namespace local_monlaututoria;

use local_monlaututoria\repository\reason_repository;
use local_monlaututoria\repository\modality_repository;

/**
 * Tests that the initial catalogue seed ran during install and is idempotent.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class install_test extends \advanced_testcase {

    public function test_seed_reasons_exist(): void {
        $this->resetAfterTest();

        $repository = new reason_repository();
        $shortnames = [
            'acogida_inicial', 'seguimiento_ordinario', 'rendimiento_academico', 'asistencia',
            'puntualidad', 'convivencia', 'motivacion', 'habitos_estudio', 'organizacion',
            'orientacion_academica', 'orientacion_profesional', 'practicas_empresa',
            'situacion_personal', 'seguimiento_acuerdos', 'contacto_familia', 'solicitud_alumno',
            'solicitud_familia', 'reconocimiento_positivo', 'derivacion', 'otro',
        ];

        $this->assertCount(20, $shortnames);
        foreach ($shortnames as $shortname) {
            $this->assertTrue(
                $repository->shortname_exists($shortname),
                "Expected seeded reason '$shortname' to exist after install"
            );
        }
    }

    public function test_seed_modalities_exist(): void {
        $this->resetAfterTest();

        $repository = new modality_repository();
        $shortnames = [
            'presencial', 'telefono', 'videoconferencia', 'correo_electronico',
            'mensajeria', 'reunion_coordinacion', 'otra',
        ];

        $this->assertCount(7, $shortnames);
        foreach ($shortnames as $shortname) {
            $this->assertTrue(
                $repository->shortname_exists($shortname),
                "Expected seeded modality '$shortname' to exist after install"
            );
        }
    }

    public function test_seeding_is_idempotent(): void {
        $this->resetAfterTest();

        global $CFG;
        require_once($CFG->dirroot . '/local/monlaututoria/db/upgradelib.php');

        $repository = new reason_repository();
        $before = count($repository->get_all());

        local_monlaututoria_seed_catalogues();

        $after = count($repository->get_all());
        $this->assertSame($before, $after);
    }
}
