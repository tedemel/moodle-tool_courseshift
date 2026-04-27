# tool_courseshift — Termine über mehrere Kurse verschieben

Admin-Tool zum Verschieben von Kursstart, Kursende und allen terminbehafteten
Aktivitäten über **mehrere Kurse gleichzeitig**.

## Features

- **Multi-Select Kurse** via Course-Autocomplete (oder vorgewählt aus Kursbereich-/Kurs-Kontext)
- **Zwei Modi:**
  - **Auf neues Startdatum setzen (pro Kurs):** jeder Kurs wird so verschoben, dass sein `startdate` auf das gewählte Datum fällt
  - **Um festen Zeitraum verschieben:** alle Kurse um X Tage (vor/zurück)
- **Optional:** Aktivitätstermine ebenfalls mitverschieben (Häkchen, default an)
- **Vorschau-Schritt** vor Anwendung — Tabelle pro Kurs mit allen betroffenen Daten
- **Audit-Event** `\tool_courseshift\event\dates_shifted` triggert pro Batch — landet im Logbuch
- **„Zurück zur Kursverwaltung"-Button** auf jedem Schritt
- **Erreichbar aus** Kursbereich-Sidebar, Kurs-Sidebar und Site-Admin → Kurse

## Anforderungen

- Moodle 5.0 oder neuer (getestet auf 5.2)
- PHP 8.2 oder neuer
- User braucht `tool/courseshift:use` (default: manager + Site-Admin via Bypass)

## Installation

1. Plugin nach `<MOODLE_ROOT>/admin/tool/courseshift/` kopieren  
   (bei Moodle 5.2 mit `public/`: `<MOODLE_ROOT>/public/admin/tool/courseshift/`)
2. Site-Admin → Mitteilungen → Datenbank-Upgrade ausführen

## Capability

| Capability | Default | Bedeutung |
|---|---|---|
| `tool/courseshift:use` | manager | Termine über mehrere Kurse verschieben (RISK_DATALOSS) |

## Zugriffspfade

- **Site-Admin** → Kurse → „Kurstermine verschieben"
- **Kursbereich-Verwaltung** (`/course/management.php?categoryid=X`) → Sidebar „Einstellungen für Kursbereiche"
- **Kurs-Verwaltung** → Sidebar
- **Direkt-URL:** `/admin/tool/courseshift/index.php`

## Bedienung

1. Plugin-Seite aufrufen
2. Kurse via Autocomplete auswählen (kein Auto-Prefill)
3. Modus wählen
4. Datum bzw. Tage angeben
5. Häkchen „Aktivitätstermine ebenfalls verschieben"
6. **„Vorschau anzeigen"** → Tabelle pro Kurs
7. **„Anwenden"** → ausgeführt + Audit-Event-Eintrag im Logbuch

## Unterstützte Datumsfelder pro Modul-Typ

| Modul | Felder |
|---|---|
| `assign` | allowsubmissionsfromdate, duedate, cutoffdate, gradingduedate |
| `quiz` | timeopen, timeclose |
| `forum` | duedate, cutoffdate |
| `lesson` | available, deadline |
| `choice` | timeopen, timeclose |
| `workshop` | submissionstart, submissionend, assessmentstart, assessmentend |
| `feedback` | timeopen, timeclose |
| `data` | timeavailablefrom, timeavailableto, timeviewfrom, timeviewto, assesstimestart, assesstimefinish |
| `scorm` | timeopen, timeclose |
| `chat` | chattime |

## Datenschutz

Keine eigenen Daten gespeichert. `null_provider`.

## Technik

- Standard-Tool-Plugin: `version.php`, `lib.php` (Navigation-Callbacks), `settings.php` (Admin-Tree),
  `db/access.php`, `db/upgrade.php`
- Form: `\tool_courseshift\form\shift_form` (mit Course-Autocomplete + `hideIf` zwischen Anchor- und Delta-Modus)
- Logik: `\tool_courseshift\local\shifter::preview()` und `::apply()`
- Event: `\tool_courseshift\event\dates_shifted` (LEVEL_OTHER, crud=u)

## Lizenz

GPL v3 oder höher.

## Repository

https://github.com/tedemel/moodle-tool_courseshift
