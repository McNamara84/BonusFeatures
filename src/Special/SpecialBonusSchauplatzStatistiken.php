<?php

namespace MediaWiki\Extension\BonusFeatures\Special;

use SpecialPage;
use MediaWiki\MediaWikiServices;

class SpecialBonusSchauplatzStatistiken extends SpecialPage
{
    private $requiredPoints = 2000;

    function __construct()
    {
        parent::__construct('BonusSchauplatzStatistiken');
    }

    private function logInfo($message, $context = [])
    {
        wfLogWarning($message . ' ' . json_encode($context));
    }

    private function logError($message, $context = [])
    {
        wfLogWarning('Error: ' . $message . ' ' . json_encode($context));
    }

    function execute($par)
    {
        $request = $this->getRequest();
        $output = $this->getOutput();

        try {
            if ($request->wasPosted() && $request->getVal('action') === 'getTableData') {
                $this->outputTableDataJson();
                return;
            }

            $this->setHeaders();

            $user = $this->getUser();
            $userPoints = $this->getUserPoints($user);

            $this->logInfo('User accessed BonusSchauplatzStatistiken', [
                'user' => $user->getName(),
                'points' => $userPoints
            ]);

            if ($userPoints < $this->requiredPoints) {
                $output->addWikiTextAsContent($this->getInsufficientPointsMessage($userPoints));
                return;
            }

            $output->addModules('ext.bonusFeatures.tableUpdate');
            $output->addJsConfigVars('bonusFeatures', [
                'updateUrl' => $this->getPageTitle()->getLocalURL()
            ]);

            // Füge den Link zur BonusFeatures-Seite am Anfang hinzu
            $output->addWikiTextAsContent("[[Spezial:BonusFeatures|Zurück zur Übersicht der Bonus-Features]]\n\n");

            $output->addWikiTextAsContent("Diese Seite bietet Statitiken zu den Schauplätzen der MADDRAX-Serie. Sie basieren auf den Daten des Maddraxikons und werden wöchentlich aktualisiert um den Server nicht zu überlasten. Die Auflistung der Häufigste Schauplätze enthält eine vollständige Liste aller Schauplätze. Die beliebtesten Schauplätze enthalten nur Schauplätze, die in mindestens 5 Romanen vorkommen. Die Durchschnittsbewertung basiert auf den Bewertungen der einzelnen Schauplätze.");

            // Füge die Maddraxiversum-Tabelle hinzu
            $output->addWikiTextAsContent("== Maddraxiversum ==\n");
            $output->addWikiTextAsContent($this->getMaddraxiversumTable());

            $headings = [
                "Hauptserie",
                "Hardcover",
                "Mission Mars",
                "Das Volk der Tiefe",
                "2012",
                "Die Abenteuer"
            ];

            foreach ($headings as $heading) {
                $output->addWikiTextAsContent("== $heading ==\n");
                if ($heading === "Hauptserie" && $userPoints >= 20000) {
                    $output->addWikiTextAsContent($this->getSeriesTable('maddrax'));
                } elseif ($heading === "Hardcover" && $userPoints >= 20000) {
                    $output->addWikiTextAsContent($this->getSeriesTable('hardcover'));
                } elseif ($heading === "Mission Mars" && $userPoints >= 20000) {
                    $output->addWikiTextAsContent($this->getSeriesTable('missionmars'));
                } elseif ($heading === "Das Volk der Tiefe" && $userPoints >= 20000) {
                    $output->addWikiTextAsContent($this->getSeriesTable('dasvolkdertiefe'));
                } elseif ($heading === "2012" && $userPoints >= 20000) {
                    $output->addWikiTextAsContent($this->getSeriesTable('2012'));
                } elseif ($heading === "Die Abenteuer" && $userPoints >= 20000) {
                    $output->addWikiTextAsContent($this->getSeriesTable('dieabenteurer'));
                }
            }

            // Füge den Link zur BonusFeatures-Seite am Ende hinzu
            $output->addWikiTextAsContent("\n\n[[Spezial:BonusFeatures|Zurück zur Übersicht der Bonus-Features]]");

        } catch (\Exception $e) {
            $this->logError('Error in BonusSchauplatzStatistiken: {message}', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            $output->addWikiTextAsContent('{{error|Ein Fehler ist aufgetreten: ' .
                wfEscapeWikiText($e->getMessage()) .
                "\nDatei: " . wfEscapeWikiText($e->getFile()) .
                "\nZeile: " . $e->getLine() .
                "\nTrace: " . wfEscapeWikiText($e->getTraceAsString()) . '}}');
        }
    }

    private function outputTableDataJson()
    {
        try {
            $request = $this->getRequest();
            $prefix = $request->getVal('prefix');
            $page = $request->getInt('page', 1);

            $data = $this->getTableData($prefix, $page);

            $this->getOutput()->disable();
            header('Content-Type: application/json');
            echo json_encode($data);
        } catch (\Exception $e) {
            $this->logError('Error in outputTableDataJson: {message}', [
                'message' => $e->getMessage(),
                'exception' => $e,
            ]);
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }

    public function getTableData($prefix, $page)
    {
        $series = explode('-', $prefix)[0];
        $type = explode('-', $prefix)[1];

        if ($series === 'maddraxiversum') {
            $jsonFiles = [
                'maddrax',
                'hardcover',
                'missionmars',
                'dasvolkdertiefe',
                '2012',
                'dieabenteurer'
            ];
        } else {
            $jsonFiles = [$series];
        }

        $ortsHaeufigkeit = [];
        $ortsBewertungen = [];

        foreach ($jsonFiles as $file) {
            $jsonFile = __DIR__ . '/../../resources/' . $file . '.json';

            if (!file_exists($jsonFile)) {
                $this->logError('JSON file not found: {file}', ['file' => $jsonFile]);
                continue;
            }

            $jsonData = file_get_contents($jsonFile);
            if ($jsonData === false) {
                $this->logError('Could not read JSON file: {file}', ['file' => $jsonFile]);
                continue;
            }

            $data = json_decode($jsonData, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->logError('JSON decoding error: {error}', ['error' => json_last_error_msg()]);
                continue;
            }

            foreach ($data as $roman) {
                // Zähle Häufigkeit für alle Romane
                foreach ($roman['orte'] as $ort) {
                    if (!isset($ortsHaeufigkeit[$ort])) {
                        $ortsHaeufigkeit[$ort] = 0;
                    }
                    $ortsHaeufigkeit[$ort]++;
                }

                // Berücksichtige Bewertungen nur für Romane mit mindestens 5 Stimmen
                if ($roman['stimmen'] >= 5) {
                    foreach ($roman['orte'] as $ort) {
                        if (!isset($ortsBewertungen[$ort])) {
                            $ortsBewertungen[$ort] = ['sum' => 0, 'count' => 0];
                        }
                        $ortsBewertungen[$ort]['sum'] += $roman['bewertung'];
                        $ortsBewertungen[$ort]['count']++;
                    }
                }
            }
        }

        if ($type === 'haeufigkeit') {
            arsort($ortsHaeufigkeit);
            $tableData = $ortsHaeufigkeit;
            $headers = ['Ort', 'Häufigkeit'];
            $rowCallback = function ($ort, $haeufigkeit) {
                return [$ort, $haeufigkeit];
            };
        } elseif ($type === 'bewertung') {
            // Entferne Orte mit weniger als 5 bewerteten Romanen für die Bewertungstabelle
            $ortsBewertungen = array_filter($ortsBewertungen, function ($bewertung) {
                return $bewertung['count'] >= 5;
            });

            $durchschnittsBewertungen = [];
            foreach ($ortsBewertungen as $ort => $bewertung) {
                $durchschnittsBewertungen[$ort] = $bewertung['sum'] / $bewertung['count'];
            }
            arsort($durchschnittsBewertungen);
            $tableData = $durchschnittsBewertungen;
            $headers = ['Ort', 'Durchschnittliche Bewertung', 'Anzahl der bewerteten Romane'];
            $rowCallback = function ($ort, $bewertung) use ($ortsBewertungen) {
                return [$ort, number_format($bewertung, 2), $ortsBewertungen[$ort]['count']];
            };
        } else {
            return ['error' => 'Ungültiger Präfix'];
        }

        $itemsPerPage = 25;
        $totalItems = count($tableData);
        $totalPages = ceil($totalItems / $itemsPerPage);

        $paginatedTable = $this->getPaginatedTable($prefix, $headers, $tableData, $rowCallback, $page);

        return [
            'tableHtml' => $paginatedTable,
            'totalItems' => $totalItems,
            'currentPage' => $page,
            'totalPages' => $totalPages
        ];
    }

    private function getUserPoints($user)
    {
        $lb = MediaWikiServices::getInstance()->getDBLoadBalancer();
        $dbr = $lb->getConnection(DB_REPLICA);

        $row = $dbr->selectRow(
            'user_stats',
            'stats_total_points',
            ['stats_actor' => $user->getActorId()],
            __METHOD__
        );

        return $row ? (int) $row->stats_total_points : 0;
    }

    private function getInsufficientPointsMessage($userPoints)
    {
        return "'''Hinweis:''' Du benötigst mindestens " . $this->requiredPoints . " Punkte, um auf diese Seite zugreifen zu können. " .
            "Deine aktuelle Punktzahl beträgt " . $userPoints . " Punkte.";
    }

    private function getSeriesTable($series)
    {
        $output = "";

        // Tabelle für Häufigste Schauplätze
        $output .= "=== Häufigste Schauplätze ===\n";
        $output .= "<div id='{$series}-haeufigkeit-section'>\n";
        $output .= "<div id='{$series}-haeufigkeit-container'></div>\n";
        $output .= "</div>\n";

        $output .= "\n\n"; // Füge etwas Abstand zwischen den Tabellen hinzu

        // Tabelle für beliebteste Schauplätze
        $output .= "=== Beliebteste Schauplätze ===\n";
        $output .= "<div id='{$series}-bewertung-section'>\n";
        $output .= "<div id='{$series}-bewertung-container'></div>\n";
        $output .= "</div>\n";

        return $output;
    }

    private function getMaddraxiversumTable()
    {
        $output = "";

        // Container für Häufigste Schauplätze
        $output .= "=== Häufigste Schauplätze ===\n";
        $output .= "<div id='maddraxiversum-haeufigkeit-section'>\n";
        $output .= "<div id='maddraxiversum-haeufigkeit-container'></div>\n";
        $output .= "</div>\n";

        $output .= "\n\n"; // Füge etwas Abstand zwischen den Tabellen hinzu

        // Container für beliebteste Schauplätze
        $output .= "=== Beliebteste Schauplätze ===\n";
        $output .= "<div id='maddraxiversum-bewertung-section'>\n";
        $output .= "<div id='maddraxiversum-bewertung-container'></div>\n";
        $output .= "</div>\n";

        return $output;
    }

    private function getPaginatedTable($prefix, $headers, $data, $rowCallback, $currentPage)
    {
        $itemsPerPage = 25;
        $totalItems = count($data);
        $totalPages = ceil($totalItems / $itemsPerPage);

        $offset = ($currentPage - 1) * $itemsPerPage;
        $paginatedData = array_slice($data, $offset, $itemsPerPage, true);

        $tableId = "table-" . $prefix;
        $output = "<div id='{$tableId}-container'>\n";
        $output .= "<table class='wikitable sortable' id='{$tableId}'>\n";
        $output .= "<thead><tr><th>" . implode("</th><th>", $headers) . "</th></tr></thead>\n";
        $output .= "<tbody>\n";

        foreach ($paginatedData as $key => $value) {
            $row = $rowCallback($key, $value);
            $output .= "<tr><td>" . implode("</td><td>", $row) . "</td></tr>\n";
        }

        $output .= "</tbody></table>\n";

        // Add pagination links
        $output .= "<div class='bonusfeatures-pagination' id='{$tableId}-pagination'>\n";
        if ($currentPage > 1) {
            $output .= "<a href='#' class='prev-page' data-prefix='{$prefix}' data-page='" . ($currentPage - 1) . "'>< Vorherige</a> ";
        }
        $output .= "Seite {$currentPage} von {$totalPages} ";
        if ($currentPage < $totalPages) {
            $output .= "<a href='#' class='next-page' data-prefix='{$prefix}' data-page='" . ($currentPage + 1) . "'>Nächste ></a>";
        }
        $output .= "</div>\n";

        $output .= "</div>\n";

        return $output;
    }

    private function getNavLinks($prefix, $currentPage, $totalPages)
    {
        $links = [];

        if ($currentPage > 1) {
            $links[] = "<a href='#' data-prefix='{$prefix}' data-page='" . ($currentPage - 1) . "'>< Vorherige</a>";
        }

        for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++) {
            if ($i == $currentPage) {
                $links[] = "'''{$i}'''";
            } else {
                $links[] = "<a href='#' data-prefix='{$prefix}' data-page='{$i}'>{$i}</a>";
            }
        }

        if ($currentPage < $totalPages) {
            $links[] = "<a href='#' data-prefix='{$prefix}' data-page='" . ($currentPage + 1) . "'>Nächste ></a>";
        }

        return implode(" • ", $links);
    }
}