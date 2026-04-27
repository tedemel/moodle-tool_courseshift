<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Deutsche Sprachstrings für tool_courseshift.
 *
 * @package    tool_courseshift
 * @copyright  2026 Tessa Demel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Kurstermine verschieben';
$string['pagetitle'] = 'Termine über mehrere Kurse verschieben';
$string['intro'] = 'Verschiebt Kursstart, Kursende und alle terminbehafteten Aktivitäten ausgewählter Kurse um den gleichen Zeitraum.';
$string['courses'] = 'Kurse';
$string['courses_help'] = 'Wähle die Kurse, deren Termine verschoben werden sollen.';
$string['mode'] = 'Modus';
$string['mode_anchor'] = 'Auf neues Startdatum setzen (pro Kurs)';
$string['mode_delta'] = 'Um festen Zeitraum verschieben';
$string['anchordate'] = 'Neues Kursstart-Datum';
$string['anchordate_help'] = 'Jeder Kurs wird so verschoben, dass sein Startdatum auf den gewählten Termin fällt. Inhalte werden um den gleichen Delta-Wert mitverschoben.';
$string['deltadays'] = 'Verschieben um (Tage)';
$string['deltadays_help'] = 'Positiver Wert = nach hinten, negativer Wert = nach vorne.';
$string['includecontent'] = 'Aktivitätstermine ebenfalls verschieben';
$string['submit'] = 'Vorschau anzeigen';
$string['confirm'] = 'Anwenden';
$string['confirm_back'] = 'Zurück zum Formular';
$string['back_management'] = 'Zurück — Kurs- und Kursbereichverwaltung';
$string['result'] = 'Erfolgreich verschoben: {$a->courses} Kurse, {$a->cms} Aktivitätstermine, {$a->events} Kalender-Events.';
$string['undo_button'] = 'Rückgängig machen';
$string['undo_result'] = 'Wiederhergestellt: {$a->courses} Kurse, {$a->cms} Aktivitätstermine, {$a->events} Kalender-Events.';
$string['error_undo_missing'] = 'Undo-Eintrag nicht gefunden oder bereits verbraucht.';
$string['error_undo_permission'] = 'Keine Berechtigung, diesen Undo-Eintrag zu verwenden.';
$string['error_undo_expired'] = 'Dieser Undo-Eintrag ist abgelaufen.';
$string['task_cleanup_undo'] = 'Abgelaufene Undo-Snapshots löschen';
$string['mode_percourse'] = 'Pro Kurs individuelles Startdatum';
$string['percourse_heading'] = 'Pro-Kurs-Startdaten';
$string['percourse_intro'] = 'Trage für jeden Kurs ein neues Startdatum ein. Aktivitätstermine und Kalender-Events werden um den daraus resultierenden Delta-Wert mitverschoben.';
$string['percourse_continue'] = 'Vorschau';
$string['select_all_in_category'] = 'Alle Kurse dieser Kategorie wählen ({$a})';
$string['scheduled'] = 'Zeitgesteuert ausführen am';
$string['scheduled_help'] = 'Optional. Wird die Verschiebung NICHT sofort ausführen, sondern erst zum gewählten Zeitpunkt via Cron-Task.';
$string['scheduled_queued'] = 'Verschiebung wurde für {$a} eingeplant. Sie wird automatisch via Cron ausgeführt.';
$string['scheduled_pending_heading'] = 'Anstehende geplante Verschiebungen';
$string['scheduled_no_pending'] = 'Keine geplanten Verschiebungen.';
$string['scheduled_cancel'] = 'Plan abbrechen';
$string['nothingtoshift'] = 'Keine Termine zum Verschieben gefunden.';
$string['preview_heading'] = 'Vorschau';
$string['preview_intro'] = 'Folgende Änderungen werden bei Bestätigung angewendet:';
$string['preview_th_element'] = 'Element';
$string['preview_th_before'] = 'Vorher';
$string['preview_th_after'] = 'Nachher';
$string['preview_coursestart'] = 'Kursstart';
$string['preview_courseend'] = 'Kursende';
$string['preview_deltadays'] = 'Δ {$a} Tage';
$string['error_dateshift_zero'] = 'Verschiebungsbetrag muss ungleich 0 sein.';

$string['courseshift:use'] = 'Termine über mehrere Kurse hinweg verschieben';

$string['event_dates_shifted'] = 'Kurstermine verschoben';

$string['privacy:metadata'] = 'Das Plugin Kurstermine verschieben speichert keine personenbezogenen Daten.';
