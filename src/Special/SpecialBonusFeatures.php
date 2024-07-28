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
            ['title' => 'Belohnung 1: Statistiken', 'description' => 'Als Belohnung für deine ersten Schritte hier im Maddraxikon erhältst du Zugriff auf die ausführlichen Statistiken zur Serie. Welcher Roman wurde am besten bewertet? Welcher wurde am häufigsten bewertet? Welche Zyklen wurden besonders gut bewertet? Welche Autoren schreiben die am besten bewerteten Romane? Das alles sind Fragen, die dir hiermit beantwortet werden!', 'requiredPoints' => 2000],
            ['title' => 'Belohnung 2: Hörbücher vorab', 'description' => 'Erhalte Zugriff auf die neuesten, unveröffentlichten EARDRAX-Fanhörbücher. Hier wird immer mindestens ein unveröffentlichtes Hörbuch angeboten - noch bevor es auf YouTube erscheint!', 'requiredPoints' => 4000],
            ['title' => 'Belohnung 3: Coming soon', 'description' => 'Folgt.', 'requiredPoints' => 8000],
            ['title' => 'Belohnung 4: Coming soon', 'description' => 'Folgt.', 'requiredPoints' => 16000],
            ['title' => 'Belohnung 5: Coming soon', 'description' => 'Folgt.', 'requiredPoints' => 32000],
            ['title' => 'Belohnung 6: Coming soon', 'description' => 'Folgt.', 'requiredPoints' => 64000],
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
            $html .= "<a href='#'>Link zum Feature</a>";
        } else {
            $html .= "<p>Benötigt {$feature['requiredPoints']} Punkte</p>";
        }
        $html .= '</div>';
        return $html;
    }

    protected function getGroupName()
    {
        return 'other';
    }
}