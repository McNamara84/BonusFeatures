(function () {
  "use strict";
  const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
  const tooltipList = [...tooltipTriggerList].map((tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl));
  console.log("Test!");

  function loadMaddraxData() {
    return mw.loader.using("ext.bonusFeatures.data").then(function () {
      return $.getJSON(mw.config.get("wgExtensionAssetsPath") + "/BonusFeatures/resources/maddrax.json");
    });
  }

  function initializeDataTable(tableId, data, columns, order, language) {
    $(tableId).DataTable({
      data: data,
      columns: columns,
      order: order,
      pageLength: 25,
      language: language,
    });
  }

  const dataTableLanguage = {
    info: "Zeige _START_ bis _END_ von _TOTAL_ Einträgen",
    infoEmpty: "Zeige 0 bis 0 von 0 Einträgen",
    infoFiltered: "(gefiltert von _MAX_ gesamten Einträgen)",
    lengthMenu: "Zeige _MENU_ Einträge",
    loadingRecords: "Lade...",
    processing: "Verarbeite...",
    search: "Suche:",
    zeroRecords: "Keine passenden Einträge gefunden",
    paginate: {
      first: "Erste",
      last: "Letzte",
      next: "Nächste",
      previous: "Vorherige",
    },
    aria: {
      sortAscending: ": aktivieren, um Spalte aufsteigend zu sortieren",
      sortDescending: ": aktivieren, um Spalte absteigend zu sortieren",
    },
  };

  loadMaddraxData().then(function (data) {
    console.log("Geladene Daten:", data);
    // TOP MADDRAX-Heftromane
    var filteredData = data.filter((row) => row.stimmen >= 5);
    initializeDataTable(
      "#stats",
      filteredData,
      [{ data: "nummer" }, { data: "evt" }, { data: "zyklus" }, { data: "titel" }, { data: "text" }, { data: "bewertung" }, { data: "stimmen" }],
      [
        [5, "desc"],
        [6, "desc"],
        [0, "asc"],
      ],
      { ...dataTableLanguage, info: "Zeige _START_ bis _END_ von _TOTAL_ Romanen" }
    );

    // TOP MADDRAX-Zyklen
    var cycleStats = data.reduce((acc, item) => {
      if (item.stimmen < 5) return acc;
      if (!acc[item.zyklus]) acc[item.zyklus] = { sum: 0, count: 0 };
      acc[item.zyklus].sum += item.bewertung;
      acc[item.zyklus].count++;
      return acc;
    }, {});

    var cycleData = Object.entries(cycleStats).map(([cycle, stats]) => ({
      cycle,
      averageRating: (stats.sum / stats.count).toFixed(2),
    }));

    initializeDataTable(
      "#nachZyklus",
      cycleData,
      [{ data: "cycle" }, { data: "averageRating" }],
      [
        [1, "desc"],
        [0, "asc"],
      ],
      { ...dataTableLanguage, info: "Zeige _START_ bis _END_ von _TOTAL_ Zyklen" }
    );

    // TOP MADDRAX-Autor:innen
    var authorStats = data.reduce((acc, item) => {
      if (item.stimmen < 5) return acc;
      item.text.split(",").forEach((author) => {
        author = author.trim();
        if (!acc[author]) acc[author] = { sum: 0, count: 0, votes: 0 };
        acc[author].sum += item.bewertung;
        acc[author].count++;
        acc[author].votes += item.stimmen;
      });
      return acc;
    }, {});

    var authorData = Object.entries(authorStats)
      .filter(([_, stats]) => stats.count >= 5)
      .map(([author, stats]) => ({
        author,
        numberOfNovels: stats.count,
        averageRating: (stats.sum / stats.count).toFixed(2),
      }));

    initializeDataTable(
      "#authorStats",
      authorData,
      [{ data: "author" }, { data: "numberOfNovels" }, { data: "averageRating" }],
      [
        [2, "desc"],
        [1, "desc"],
        [0, "asc"],
      ],
      { ...dataTableLanguage, info: "Zeige _START_ bis _END_ von _TOTAL_ Autor:innen" }
    );

    // TOP MADDRAX-Jahrgänge
    var yearStats = data.reduce((acc, item) => {
      if (item.stimmen < 5) return acc;
      var year = item.evt.slice(-4);
      if (!acc[year]) acc[year] = { count: 0, sum: 0 };
      acc[year].count++;
      acc[year].sum += item.bewertung;
      return acc;
    }, {});

    var yearData = Object.entries(yearStats).map(([year, stats]) => ({
      year,
      count: stats.count,
      averageRating: (stats.sum / stats.count).toFixed(2),
    }));

    initializeDataTable("#yearsStats", yearData, [{ data: "year" }, { data: "count" }, { data: "averageRating" }], [[2, "desc"]], {
      ...dataTableLanguage,
      info: "Zeige _START_ bis _END_ von _TOTAL_ Jahrgänge",
    });

    // Gesamtstatistiken
    var totalVotes = data.reduce((sum, item) => sum + item.stimmen, 0);
    $("#gesBew").text(totalVotes);
    $("#gesRomane").text(data.length);
    $("#durchschBew").text((totalVotes / data.length).toFixed(0));

    // Figuren nach Anzahl der Auftritte
    var zyklusOrder = [
      "Euree",
      "Meeraka",
      "Expedition",
      "Kratersee",
      "Daa'muren",
      "Wandler",
      "Mars",
      "Ausala",
      "Afra",
      "Antarktis",
      "Schatten",
      "Ursprung",
      "Streiter",
      "Archivar",
      "Zeitsprung",
      "Fremdwelt",
      "Parallelwelt",
      "Weltenriss",
      "Amraka",
    ];

    var characterData = data.reduce((acc, row) => {
      row.personen.forEach((person) => {
        if (!acc[person]) acc[person] = { zyklen: new Set(), nummern: [], count: 0 };
        acc[person].zyklen.add(row.zyklus);
        acc[person].nummern.push(row.nummer);
        acc[person].count++;
      });
      return acc;
    }, {});

    var characterTableData = Object.entries(characterData).map(([person, data]) => ({
      person,
      zyklen: Array.from(data.zyklen)
        .sort((a, b) => zyklusOrder.indexOf(a) - zyklusOrder.indexOf(b))
        .join(", "),
      nummern: data.nummern.sort((a, b) => a - b).join(", "),
      count: data.count,
    }));

    initializeDataTable(
      "#charsStats",
      characterTableData,
      [
        { data: "person", title: "Person" },
        { data: "zyklen", title: "Zyklen" },
        { data: "nummern", title: "Bandnummern" },
        { data: "count", title: "Auftritte" },
      ],
      [[3, "desc"]],
      { ...dataTableLanguage, info: "Zeige _START_ bis _END_ von _TOTAL_ Personen" }
    );

    // TOP Themen
    var schlagworteInfo = data.reduce((acc, roman) => {
      if (roman.schlagworte) {
        roman.schlagworte.forEach((schlagwort) => {
          if (!acc[schlagwort]) acc[schlagwort] = { count: 0, totalRating: 0 };
          acc[schlagwort].count++;
          acc[schlagwort].totalRating += roman.bewertung;
        });
      }
      return acc;
    }, {});

    var schlagwortData = Object.entries(schlagworteInfo)
      .filter(([_, info]) => info.count >= 5)
      .map(([schlagwort, info]) => [schlagwort, info.count, (info.totalRating / info.count).toFixed(2)]);

    initializeDataTable(
      "#schlagwortStats",
      schlagwortData,
      [{ title: "Thema" }, { title: "Häufigkeit" }, { title: "Bewertung" }],
      [
        [2, "desc"],
        [1, "desc"],
        [0, "asc"],
      ],
      { ...dataTableLanguage, info: "Zeige _START_ bis _END_ von _TOTAL_ Themen" }
    );

    // Zyklen-Charts
    createCycleCharts(data);

    // Jahres-Chart
    createYearChart(data);

    // Figuren-Chart
    createCharacterChart(data);
  });

  function calculateTrendlineData(numbers, averageRatings) {
    var sumX = 0,
      sumY = 0,
      sumXY = 0,
      sumXX = 0,
      count = 0;

    for (var i = 0; i < numbers.length; i++) {
      if (averageRatings[i] === null) continue;
      sumX += numbers[i];
      sumY += averageRatings[i];
      sumXX += numbers[i] * numbers[i];
      sumXY += numbers[i] * averageRatings[i];
      count++;
    }

    var m = (count * sumXY - sumX * sumY) / (count * sumXX - sumX * sumX);
    var b = sumY / count - (m * sumX) / count;

    return numbers.map((number) => m * number + b);
  }

  function createCycleCharts(data) {
    const cycles = [
      "Euree",
      "Meeraka",
      "Expedition",
      "Kratersee",
      "Daa'muren",
      "Wandler",
      "Mars",
      "Ausala",
      "Afra",
      "Antarktis",
      "Schatten",
      "Ursprung",
      "Streiter",
      "Archivar",
      "Zeitsprung",
      "Fremdwelt",
      "Parallelwelt",
      "Weltenriss",
      "Amraka",
    ];

    cycles.forEach((cycle) => createCycleChart(cycle, data));
  }

  function createCycleChart(cycleName, data) {
    var cycleData = data.filter((item) => item.zyklus === cycleName && item.stimmen >= 5);

    var numbers = cycleData.map((item) => item.nummer);
    var ratings = cycleData.map((item) => item.bewertung);

    var trendlineData = calculateTrendlineData(numbers, ratings);

    var ctx = document.getElementById(`zyklus${cycleName}Chart`).getContext("2d");
    new Chart(ctx, {
      type: "line",
      data: {
        labels: numbers,
        datasets: [
          {
            label: "Durchschnittliche Bewertung",
            data: ratings,
            backgroundColor: "rgba(0, 123, 255, 0.5)",
            borderColor: "rgba(0, 123, 255, 1)",
            borderWidth: 1,
          },
          {
            label: "Trend",
            data: trendlineData,
            backgroundColor: "rgba(255, 0, 0, 0.5)",
            borderColor: "rgba(255, 0, 0, 1)",
            borderWidth: 1,
            tension: 0,
            pointRadius: 0,
            pointHitRadius: 0,
          },
        ],
      },
      options: {
        scales: {
          y: {
            min: 1.0,
            suggestedMax: 5.0,
          },
        },
      },
    });
  }

  function createYearChart(data) {
    var yearStats = data.reduce((acc, item) => {
      if (item.stimmen < 5) return acc;
      var year = item.evt.slice(-4);
      if (!acc[year]) acc[year] = { count: 0, sum: 0 };
      acc[year].count++;
      acc[year].sum += item.bewertung;
      return acc;
    }, {});

    var years = Object.keys(yearStats).sort();
    var averageRatings = years.map((year) => (yearStats[year].sum / yearStats[year].count).toFixed(2));

    var ctx = document.getElementById("yearsChart").getContext("2d");
    new Chart(ctx, {
      type: "line",
      data: {
        labels: years,
        datasets: [
          {
            label: "Durchschnittliche Bewertung",
            data: averageRatings,
            backgroundColor: "rgba(0, 123, 255, 0.5)",
            borderColor: "rgba(0, 123, 255, 1)",
            borderWidth: 1,
          },
        ],
      },
      options: {
        scales: {
          y: {
            min: 1.0,
            suggestedMax: 5.0,
          },
        },
      },
    });
  }

  function createCharacterChart(data) {
    var characterCounts = data.reduce((acc, row) => {
      row.personen.forEach((person) => {
        acc[person] = (acc[person] || 0) + 1;
      });
      return acc;
    }, {});

    var sortedCharacters = Object.entries(characterCounts)
      .sort((a, b) => b[1] - a[1])
      .slice(0, 25);

    var top25Count = sortedCharacters.reduce((sum, [_, count]) => sum + count, 0);
    var totalCount = Object.values(characterCounts).reduce((sum, count) => sum + count, 0);
    var otherCount = totalCount - top25Count;

    var labels = sortedCharacters.map(([name, _]) => name);
    labels.push("Sonstige");

    var counts = sortedCharacters.map(([_, count]) => count);
    counts.push(otherCount);

    var ctx = document.getElementById("charsChart").getContext("2d");
    new Chart(ctx, {
      type: "pie",
      data: {
        labels: labels,
        datasets: [
          {
            data: counts,
            backgroundColor: [
              // Hier können Sie 26 verschiedene Farben definieren
            ],
          },
        ],
      },
      options: {
        title: {
          display: true,
          text: "Top 25 Personen mit den meisten Auftritten und Sonstige",
        },
      },
    });
  }

  function openChangelogModal() {
    $.get("changelog-modal.html", function (data) {
      $("body").append(data);
      var modal = new bootstrap.Modal(document.getElementById("changelogModal"));
      modal.show();
    });
  }
})();
