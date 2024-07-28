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
            ['stats_user_id' => $user->getId()],
            __METHOD__
        );

        return $row ? (int) $row->stats_total_points : 0;
    }

    private function getFeatures($userPoints)
    {
        $features = [
            ['title' => 'Feature 1', 'description' => 'Beschreibung 1', 'requiredPoints' => 2000],
            ['title' => 'Feature 2', 'description' => 'Beschreibung 2', 'requiredPoints' => 4000],
            ['title' => 'Feature 3', 'description' => 'Beschreibung 3', 'requiredPoints' => 8000],
            ['title' => 'Feature 4', 'description' => 'Beschreibung 4', 'requiredPoints' => 16000],
            ['title' => 'Feature 5', 'description' => 'Beschreibung 5', 'requiredPoints' => 32000],
            ['title' => 'Feature 6', 'description' => 'Beschreibung 6', 'requiredPoints' => 64000],
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