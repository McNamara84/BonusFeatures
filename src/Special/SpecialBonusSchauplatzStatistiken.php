<?php

namespace MediaWiki\Extension\BonusFeatures\Special;

use SpecialPage;
use MediaWiki\MediaWikiServices;

class SpecialBonusSchauplatzStatistiken extends SpecialPage
{
    private $requiredPoints = 2000; // Setzen Sie hier die erforderliche Punktzahl

    function __construct()
    {
        parent::__construct('BonusSchauplatzStatistiken');
    }

    function execute($par)
    {
        $output = $this->getOutput();
        $this->setHeaders();
        $output->addWikiTextAsContent("== Statistiken zur MADDRAX-Serie ==");
        $output->addWikiTextAsContent("Hier sind die Statistiken zur MADDRAX-Serie.");
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

    private function getStatisticsHTML()
    {
        // Meldung anzeigen, dass diese Seite noch keinen inhalt hat
        return '<div class="alert alert-info" role="alert">
            Die Statistiken zu den Schauplätzen der Maddrax-Romane sind noch in Arbeit. Bitte habe noch etwas Geduld.
        </div>';
    }

    private function getInsufficientPointsMessage($userPoints)
    {
        return '<div class="alert alert-warning" role="alert">
            Du benötigst mindestens ' . $this->requiredPoints . ' Punkte, um auf diese Seite zugreifen zu können. 
            Deine aktuelle Punktzahl beträgt ' . $userPoints . ' Punkte.
        </div>';
    }
}