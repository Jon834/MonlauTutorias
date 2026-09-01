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

defined('MOODLE_INTERNAL') || die();

/**
 * "Modo simple" (fase 13) — single site-level switch that hides the advanced
 * modules of this plugin without deleting anything.
 *
 * When the site setting local_monlaututoria/simplemode is off (the default,
 * and what every existing installation gets after upgrading), enabled() always
 * returns true and the plugin behaves exactly as before. When it is on, the
 * features listed in HIDDEN_IN_SIMPLE_MODE are hidden: their navigation entries
 * and admin pages are not rendered, and their pages refuse to load
 * (require_enabled() throws). The underlying services, repositories, database
 * tables and PHPUnit tests are untouched — turning the setting back off
 * restores everything.
 *
 * Defense in depth: every consumer checks enabled() to hide UI, AND every
 * hidden page calls require_enabled() right after require_login(), so
 * manipulating the URL does not bypass the mode.
 *
 * @package    local_monlaututoria
 * @copyright  2026 Monlau Tutoria Project
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class feature {

    /** @var string Agreements (acuerdos). */
    public const AGREEMENTS = 'agreements';

    /** @var string Follow-ups (seguimientos). */
    public const FOLLOWUPS = 'followups';

    /** @var string Referrals to coordination/orientation/management (derivaciones). */
    public const REFERRALS = 'referrals';

    /** @var string Aggregated coordination dashboard and coordination scopes. */
    public const COORDINATION = 'coordination';

    /** @var string Notifications and their scheduled reminders. */
    public const NOTIFICATIONS = 'notifications';

    /**
     * CSV assignment import plus per-cohort visibility config. NOTE: cohort ->
     * tutor bulk assignment (assignments/cohort_create.php) is NOT covered by
     * this — it stays available in simple mode as a coordination tool.
     *
     * @var string
     */
    public const IMPORTS = 'imports';

    /** @var string Co-tutor management and primary-tutor reassignment screens. */
    public const COTUTORS = 'cotutors';

    /** @var string File attachments on tutoring entries. */
    public const ATTACHMENTS = 'attachments';

    /** @var string The "full" tutoring entry registration form (multi-reason, participants, restricted note). */
    public const FULLENTRY = 'fullentry';

    /** @var string The "restricted" content tier of a tutoring entry. */
    public const RESTRICTEDNOTES = 'restrictednotes';

    /**
     * Features hidden while simple mode is on. Anything not in this list
     * (the core tutor/student flow) is always available.
     *
     * @var string[]
     */
    private const HIDDEN_IN_SIMPLE_MODE = [
        self::AGREEMENTS,
        self::FOLLOWUPS,
        self::REFERRALS,
        self::COORDINATION,
        self::NOTIFICATIONS,
        self::IMPORTS,
        self::COTUTORS,
        self::ATTACHMENTS,
        self::FULLENTRY,
        self::RESTRICTEDNOTES,
    ];

    /**
     * @return bool whether the site is running in simple mode
     */
    public static function simple_mode(): bool {
        // === '1', not a bool cast: get_config() returns false (not '0') when
        // the setting has never been written yet — same reasoning as the
        // dashboard_showreferrals check in dashboard.php. false !== '1', so a
        // brand-new or freshly-upgraded install defaults to "full mode".
        return get_config('local_monlaututoria', 'simplemode') === '1';
    }

    /**
     * @param string $feature one of the class constants
     * @return bool true when the feature may be used
     */
    public static function enabled(string $feature): bool {
        if (!self::simple_mode()) {
            return true;
        }

        return !in_array($feature, self::HIDDEN_IN_SIMPLE_MODE, true);
    }

    /**
     * Guards a page that belongs to a feature which may be hidden. Call it
     * right after require_login(), before any output.
     *
     * @param string $feature one of the class constants
     * @throws \moodle_exception when the feature is hidden in simple mode
     */
    public static function require_enabled(string $feature): void {
        if (self::enabled($feature)) {
            return;
        }

        throw new \moodle_exception(
            'error_featuredisabled',
            'local_monlaututoria',
            (new \moodle_url('/local/monlaututoria/dashboard.php'))->out(false)
        );
    }
}
