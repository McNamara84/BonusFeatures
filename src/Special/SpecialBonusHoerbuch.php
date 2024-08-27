<?php

namespace MediaWiki\Extension\BonusFeatures\Special;

use MediaWiki\MediaWikiServices;
use SpecialPage;

class SpecialBonusHoerbuch extends SpecialPage
{
    public function __construct()
    {
        parent::__construct('BonusHoerbuch');
    }

    public function execute($subPage)
    {
        parent::execute($subPage);
        $this->setHeaders();

        $output = $this->getOutput();
        $user = $this->getUser();

        $output->setPageTitle($this->msg('bonushoerbuch-title'));

        $userPoints = $this->getUserPoints($user);
        // Füge den Link zur BonusFeatures-Seite am Anfang hinzu
        $output->addWikiTextAsContent("[[Spezial:BonusFeatures|Zurück zur Übersicht der Bonus-Features]]\n\n");

        if ($userPoints >= 4000) {
            $output->addHTML($this->renderHoerbuecher());
        } else {
            $output->addHTML("<p>Du benötigst mindestens 4000 Punkte, um auf diese Seite zugreifen zu können.</p>");
        }
        // Füge den Link zur BonusFeatures-Seite am Ende hinzu
        $output->addWikiTextAsContent("[[Spezial:BonusFeatures|Zurück zur Übersicht der Bonus-Features]]\n\n");
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

    private function renderHoerbuecher()
    {
        $html = "<div class='bonus-hoerbuecher'>";
        $html .= "<p>Hier kannst du exklusiv das nächste Hörbuch noch vor der eigentlichen Veröffentlichung hören.</p>";

        // Hier können Sie die YouTube-Videos einbinden
        $videos = [
            'YQNNkggPIAY', // F28T1
            'Y2wSPocYNX4', // F28T2
            'fPJed3NoM28', // F28T3
            'Td1cGqTw76Y', // F28T4
            'r_ZCYVtfF8M', // F29T1
            'rxKfwIhHhlI', // F29T2
            'WP2a-X04lMw', // F29T3
        ];

        foreach ($videos as $videoId) {
            $html .= "<div class='video-container'>";
            $html .= "<iframe width='560' height='315' src='https://www.youtube.com/embed/{$videoId}' frameborder='0' allow='autoplay; encrypted-media' allowfullscreen></iframe>";
            $html .= "</div>";
        }

        $html .= "</div>";
        return $html;
    }

    protected function getGroupName()
    {
        return 'other';
    }
}