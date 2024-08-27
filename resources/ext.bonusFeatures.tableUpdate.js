(function ($, mw) {
  "use strict";

  function loadTableData(prefix, page = 1) {
    var statisticType = mw.config.get("bonusFeatures").statisticType;
    console.log("Loading table data for statisticType:", statisticType);

    $.ajax({
      url: mw.util.wikiScript("api"),
      data: {
        action: "getTableData",
        format: "json",
        prefix: prefix,
        page: page,
        statisticType: statisticType,
      },
      dataType: "json",
      success: function (response) {
        console.log("API Response:", response);
        if (response.getTableData && response.getTableData.error) {
          console.error("API Error:", response.getTableData.error);
          $("#" + prefix + "-container").html("<p class='cdx-message cdx-message--error'>Error: " + response.getTableData.error + "</p>");
          return;
        }
        if (response.getTableData && response.getTableData.tableHtml) {
          if (response.getTableData.totalItems > 0) {
            $("#" + prefix + "-container").html(response.getTableData.tableHtml);

            // Attach click events to the new pagination links
            $("#table-" + prefix + "-pagination .cdx-button").on("click", function (e) {
              e.preventDefault();
              var newPage = $(this).data("page");
              loadTableData(prefix, newPage);
            });

            // Initialize tablesorter
            $("#table-" + prefix).tablesorter();

            // Optional: Spezifische Aktionen basierend auf dem Statistiktyp
            if (statisticType === "schauplatz") {
              console.log("Schauplatz-spezifische Aktionen können hier hinzugefügt werden");
            } else if (statisticType === "person") {
              console.log("Personen-spezifische Aktionen können hier hinzugefügt werden");
            } else if (statisticType === "autor") {
              console.log("Autoren-spezifische Aktionen können hier hinzugefügt werden");
            }
          } else {
            // Wenn keine Daten vorhanden sind, entferne den gesamten Section-Container
            $("#" + prefix + "-section").remove();
          }
        } else {
          console.error("Unexpected API response format:", response);
          $("#" + prefix + "-container").html("<p class='cdx-message cdx-message--error'>Error: Unexpected API response format</p>");
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX Error:", status, error);
        $("#" + prefix + "-container").html("<p class='cdx-message cdx-message--error'>Error: " + error + "</p>");
      },
    });
  }

  // Function to show loading indicator
  function showLoading(prefix) {
    $("#" + prefix + "-container").html("<div class='cdx-progress-bar cdx-progress-bar--inline'></div>");
  }

  // Initial load
  $(function () {
    var series = ["maddraxiversum", "maddrax", "hardcover", "missionmars", "dasvolkdertiefe", "2012", "dieabenteurer"];
    var types = ["haeufigkeit", "bewertung"];

    series.forEach(function (serie) {
      types.forEach(function (type) {
        var prefix = serie + "-" + type;
        showLoading(prefix);
        loadTableData(prefix);
      });
    });
  });
})(jQuery, mediaWiki);
