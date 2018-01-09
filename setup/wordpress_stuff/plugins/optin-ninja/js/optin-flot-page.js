/**
 * OptIn Ninja
 * (c) Web factory Ltd, 2016
 */

jQuery(document).ready(function($){
  function plotAccordingToChoices() {
    var data = [];


    var i = 0;
    $.each(datasets, function(key, val) {
      val.color = i;
      i = i+8;
    });


    $("<div id='optin-tooltip'></div>").css({
      position: "absolute",
      display: "none",
      border: "1px solid #fdd",
      padding: "2px",
      "background-color": "#fee",
      opacity: 0.80
    }).appendTo("body");


    $("#optin-graph").bind("plothover", function (event, pos, item) {
        var str = "(" + pos.x.toFixed(2) + ", " + pos.y.toFixed(2) + ")";
        $("#hoverdata").text(str);


        if (item && item.datapoint[1] == Math.round(item.datapoint[1])) {
          var x = item.datapoint[0],
            y = item.datapoint[1];

          $("#optin-tooltip").html(Math.round(y) + ' <b>' + item.series.label + "</b> on " + $.plot.formatDate(new Date(x), '%b %d %Y'))
            .css({'z-index': 99999, top: item.pageY-35, left: item.pageX-($("#optin-tooltip").width()) / 2})
            .fadeIn(200);
        } else {
          $("#optin-tooltip").hide();
        }
    });

    $.each(datasets, function(key, val) {
      data.push(datasets[key]);
    });

    if (data.length > 0) {
      $("#optin-graph").show();
      $.plot("#optin-graph", data, {
              yaxis: {minTickSize: 1, tickDecimals: 0},
              xaxis: {mode: "time", minTickSize: [1, "day"], timeformat: "%b %d"},
              series: {"interpolate": 1, "interpolateSteps": 3, shadowSize: 3, lines: { show: true }, points: { show: true } },
              lines: { show: true, fill: true },
              grid: {
                show: true,
                aboveData: false,
                color: "#bbb",
                backgroundColor: "#f9f9f9",
                borderColor: "#ccc",
                borderWidth: 2,
                clickable: false,
                hoverable: true,
               }
      });
    }// if
  }

  plotAccordingToChoices();
});