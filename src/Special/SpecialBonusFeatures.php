<?php

namespace MediaWiki\Extension\BonusFeatures\Special;

use MediaWiki\MediaWikiServices;
use SpecialPage;

class SpecialBonusFeatures extends SpecialPage
{
    public function __construct()
    {
        parent::__construct('BonusFeatures');
    }

    public function execute($subPage)
    {
        parent::execute($subPage);
        $this->setHeaders();

        $output = $this->getOutput();
        $user = $this->getUser();

        $output->setPageTitle($this->msg('bonusfeatures-title'));
        $output->addModules('ext.bonusFeatures');

        $userPoints = $this->getUserPoints($user);
        $features = $this->getFeatures($userPoints);

        $output->addHTML($this->renderFeatures($features));
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

    private function getFeatures($userPoints)
    {
        $features = [
            [
                'title' => 'Belohnung 1: Statistiken - Schauplätze',
                'description' => 'Als Belohnung für deine ersten Schritte hier im Maddraxikon erhältst du Zugriff auf die ausführlichen Statistiken zu Handlunnsorten der Serie. Hier findest du alle Informationen zu den Schauplätzen der Maddrax-Romane.',
                'requiredPoints' => 1000,
                'linkText' => 'Zu den Schauplatz-Statistiken',
                'linkUrl' => 'BonusSchauplatzStatistiken'
            ],
            [
                'title' => 'Belohnung 2: Hörbücher vorab',
                'description' => 'Erhalte Zugriff auf die neuesten, unveröffentlichten EARDRAX-Fanhörbücher. Mit 2.000 Punkten kannst du dir hier mindestens ein unveröffentlichtes Maddrax-Hörbuch anhören - noch bevor es auf YouTube erscheint!',
                'requiredPoints' => 2000,
                'linkText' => 'Zur Hörbuch-Vorschau',
                'linkUrl' => 'BonusHoerbuch'
            ],
            [
                'title' => 'Belohnung 3: Statistiken - Personen',
                'description' => 'Erhalte mit 4.000 Punkten Zugriff auf die ausführlichen Statistiken zu den Charakteren der Serie. Welche Charaktere kamen häufiger vor? Welche sind die beliebtesten? Hier findest du alle Informationen zu den Charakteren der Maddrax-Romane.',
                'requiredPoints' => 4000,
                'linkText' => 'Zu den Personen-Statistiken',
                'linkUrl' => 'BonusPersonenStatistiken'
            ],
            [
                'title' => 'Belohnung 4: Statistiken - Romane',
                'description' => 'IN ARBEIT: Welcher Roman wurde am häufigsten bewertet? Welcher ist der bestbewertete Roman? Mit 6.000 Punkten erhältst du Zugriff auf die ausführlichen Statistiken zu den Romanen der Serie.',
                'requiredPoints' => 6000,
                'linkText' => 'Zu den Roman-Statistiken',
                'linkUrl' => 'BonusRomaneStatistiken'
            ],
            [
                'title' => 'Belohnung 5: Statistiken - Autoren',
                'description' => 'Wer hat am meisten Romane im Maddraxiversum verfasst? Welcher Autor schreibt die am besten bewerteten Romane? Mit 8.000 Punkten erhältst du Zugriff auf die ausführlichen Statistiken zu den Autoren der Serie.',
                'requiredPoints' => 8000,
                'linkText' => 'Zu den Autoren-Statistiken',
                'linkUrl' => 'BonusAutorenStatistiken'
            ],
            [
                'title' => 'Belohnung 6: Klemmbaustein-Anleitung - Prototyp XP-1',
                'description' => 'IN ARBEIT: Mit 32.000 Punkten erhältst du Zugriff auf die Anleitung zum Bau des Prototyp XP-1 aus Klemmbausteinen. Bau Dir dein eigenes Modell des Prototyp XP-1!',
                'requiredPoints' => 10000,
                'linkText' => 'Zum Download',
                'linkUrl' => 'BonusProtoAnleitung'
            ],
            [
                'title' => 'Belohnung 7: Karten - Kartenwerk Erde',
                'description' => 'IN ARBEIT',
                'requiredPoints' => 20000,
                'linkText' => 'Zur Karte der Erde',
                'linkUrl' => 'BonusMapdraxErde'
            ],
            [
                'title' => 'Belohnung 8: Statistiken - Zyklen',
                'description' => 'IN ARBEIT',
                'requiredPoints' => 30000,
                'linkText' => 'Zu den Zyklen-Statistiken',
                'linkUrl' => 'BonusZyklenStatistiken'
            ],
            [
                'title' => 'Belohnung 9: 3D-Druck - Druckdateien für Prototyp XP-1',
                'description' => 'IN ARBEIT',
                'requiredPoints' => 40000,
                'linkText' => 'Zu den Roman-Statistiken',
                'linkUrl' => 'BonusRomaneStatistiken'
            ],
            // TODO: Weitere Belohnungen hinzufügen
        ];

        foreach ($features as &$feature) {
            $feature['unlocked'] = $userPoints >= $feature['requiredPoints'];
        }

        return $features;
    }

    private function renderFeatures($features)
    {
        $html = '<div class="bonus-features">';
        foreach ($features as $feature) {
            $html .= $this->renderFeature($feature);
        }
        $html .= '</div>';
        return $html;
    }

    private function renderFeature($feature)
    {
        $class = $feature['unlocked'] ? 'unlocked' : 'locked';
        $html = "<div class='feature {$class}'>";
        $html .= "<h3>{$feature['title']}</h3>";
        $html .= "<p>{$feature['description']}</p>";
        if ($feature['unlocked']) {
            $linkUrl = SpecialPage::getTitleFor($feature['linkUrl'])->getLocalURL();
            $html .= "<a href='{$linkUrl}'>{$feature['linkText']}</a>";
        } else {
            $progress = min(100, ($this->userPoints / $feature['requiredPoints']) * 100);
            $html .= "<p>Benötigt {$feature['requiredPoints']} Punkte</p>";
            $html .= "<div class='progress-bar'><div class='progress' style='width: {$progress}%'></div></div>";
        }
        $html .= '</div>';
        return $html;
    }

    protected function getGroupName()
    {
        return 'other';
    }
}