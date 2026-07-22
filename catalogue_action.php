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
 * Thin shared controller for reason/modality actions: activate, deactivate,
 * reorder, delete. No business logic here; everything is delegated to
 * catalogue_service.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

use local_monlaututoria\service\catalogue_service;

require_login();
$context = context_system::instance();
require_capability('local/monlaututoria:managecatalogues', $context);
require_sesskey();

$type = required_param('type', PARAM_ALPHA);
$id = required_param('id', PARAM_INT);
$action = required_param('action', PARAM_ALPHA);

$validtypes = [catalogue_service::TYPE_REASON, catalogue_service::TYPE_MODALITY];
if (!in_array($type, $validtypes, true)) {
    throw new \moodle_exception('invalidparameter', 'debug');
}

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/monlaututoria/catalogue_action.php'));

$repository = $type === catalogue_service::TYPE_REASON
    ? new \local_monlaututoria\repository\reason_repository()
    : new \local_monlaututoria\repository\modality_repository();
$service = new catalogue_service($type, $repository);

// Ensures the item exists (throws dml_missing_record_exception otherwise).
$repository->get($id);

$returnurl = new moodle_url(
    $type === catalogue_service::TYPE_REASON
        ? '/local/monlaututoria/reasons.php'
        : '/local/monlaututoria/modalities.php'
);

switch ($action) {
    case 'activate':
        $service->set_active($id, true, (int) $USER->id);
        break;
    case 'deactivate':
        $service->set_active($id, false, (int) $USER->id);
        break;
    case 'moveup':
        $service->move($id, -1);
        break;
    case 'movedown':
        $service->move($id, 1);
        break;
    case 'delete':
        $service->delete($id);
        break;
    default:
        throw new \moodle_exception('invalidparameter', 'debug');
}

redirect($returnurl);
