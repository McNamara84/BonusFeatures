(function ($, mw) {
  "use strict";

  function loadTableData(prefix, page = 1) {
    $.ajax({
      url: mw.util.wikiScript("api"),
      data: {
        action: "getTableData",
        format: "json",
        prefix: prefix,
        page: page,
      },
      dataType: "json",
      success: function (response) {
        console.log("API Response:", response);
        if (response.getTableData && response.getTableData.error) {
          console.error("API Error:", response.getTableData.error);
          $("#table-" + prefix + "-container").html("<p>Error: " + response.getTableData.error + "</p>");
          return;
        }
        if (response.getTableData && response.getTableData.tableHtml) {
          $("#table-" + prefix + "-container").html(response.getTableData.tableHtml);

          // Attach click events to the new pagination links
          $("#table-" + prefix + "-pagination .prev-page, #table-" + prefix + "-pagination .next-page").on("click", function (e) {
            e.preventDefault();
            var newPage = $(this).data("page");
            loadTableData(prefix, newPage);
          });

          // Initialize tablesorter
          $("#table-" + prefix).tablesorter();
        } else {
          console.error("Unexpected API response format:", response);
          $("#table-" + prefix + "-container").html("<p>Error: Unexpected API response format</p>");
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX Error:", status, error);
        $("#table-" + prefix + "-container").html("<p>Error: " + error + "</p>");
      },
    });
  }

  // Function to show loading indicator
  function showLoading(prefix) {
    $("#table-" + prefix + "-container").html("<p>Loading...</p>");
  }

  // Initial load
  $(function () {
    showLoading("haeufigkeit");
    showLoading("bewertung");
    loadTableData("maddrax-haeufigkeit");
    loadTableData("maddrax-bewertung");
    loadTableData("hardcover-haeufigkeit");
    loadTableData("hardcover-bewertung");
    loadTableData("missionmars-haeufigkeit");
    loadTableData("missionmars-bewertung");
    loadTableData("dasvolkdertiefe-haeufigkeit");
    loadTableData("dasvolkdertiefe-bewertung");
  });
})(jQuery, mediaWiki);
