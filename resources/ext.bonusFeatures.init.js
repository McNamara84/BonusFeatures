(function () {
  "use strict";

  mw.loader
    .using(["ext.bonusFeatures.statistics"])
    .then(function () {
      console.log("Statistik-Modul geladen");
      // Hier können Sie Ihre Initialisierungslogik hinzufügen
      // z.B. Tabellen und Diagramme erstellen
    })
    .catch(function (err) {
      console.error("Fehler beim Laden des Statistik-Moduls:", err);
      document.getElementById("debug-info").innerHTML += "<p>Fehler beim Laden des Statistik-Moduls: " + err + "</p>";
      document.getElementById("debug-info").style.display = "block";
    });
})();
