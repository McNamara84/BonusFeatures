(function () {
  "use strict";

  mw.bonusFeatures = {
    init: function () {
      console.log("BonusFeatures Erweiterung wurde initialisiert!");
      // Hier können Sie weitere Initialisierungslogik hinzufügen
    },

    doSomething: function () {
      // Implementieren Sie hier spezifische Funktionalitäten
      console.log("BonusFeatures tut etwas!");
    },
  };

  $(document).ready(function () {
    mw.bonusFeatures.init();
  });
})();
