<?php

namespace MediaWiki\Extension\BonusFeatures;

use OutputPage;
use Skin;

class Hooks {
    /**
     * Hook: BeforePageDisplay
     *
     * @param OutputPage $out
     * @param Skin $skin
     */
    public static function onBeforePageDisplay( OutputPage $out, Skin $skin ) : void {
        // Fügen Sie hier den Code hinzu, der vor der Seitenausgabe ausgeführt werden soll
        // Zum Beispiel:
        $out->addModules( 'ext.bonusFeatures' );
    }
}