jQuery(document).ready(function ($) {
    var range = $("#range"),
        Reports = {
            dropDowns: $('.Tajer .ui.dropdown'),
            container: $(".tajer-container"),
            init: function () {
                //this.flot();
                this.datePicker();

                this.showHideCustomFilter();
                this.getGraphData();
                range.on('change', this.showHideCustomFilter);
                range.on('change', this.getGraphData);
                $("button#tajer-filter").on('click', this.getCustomGraphData);
                $("a.tajer-tab-nav").on('click', this.showHideTheWell);
                this.tab();
                this.dropDowns.tajerdropdown({
                    context: Reports.container,
                    keepOnScreen: true
                });
                $(".Tajer form *[data-content]").popup({
                    context: Reports.container,
                    inline: true
                });
            },
            tab: function () {
                $('.ui.menu .item').tab();
            },
            showHideTheWell: function () {
                var self = $(this),
                    well = $(".tajer-saving"),
                    type = self.attr("data-tajer-type");
                if (type == 'analytics') {
                    well.show();
                } else {
                    well.hide();
                }
            },
            getCustomGraphData: function (e) {
                e.preventDefault();
                Reports.getGraphData();
            },
            getGraphData: function () {

                var nonce = $("#tajer_reports_nonce_field").val(),
                    range = $("#range").val(),
                    from = $("input[name='tajer-from']").val(),
                    to = $("input[name='tajer-to']").val(),
                    segment = $(".tajer-saving"),
                    params = {
                        action: 'tajer_get_graph_data',
                        nonce: nonce,
                        tajerFrom: from,
                        tajerTo: to,
                        tajerRange: range
                    },
                    data = $.param(params);

                if ((range == 'custom') && ((from == '____-__-__ __:__:__') || (to == '____-__-__ __:__:__'))) {
                    return;
                }

                segment.addClass('loading');
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: data,
                    success: function (result) {
                        //in case we got unexpected result then just extract our json string
                        var extractJsonString = result.match(/\[tajer_json\](\{.+\})\[\/tajer_json\]/);
                        result = $.parseJSON(extractJsonString[1]);
                        if (result.message !== 'error') {
                            segment.removeClass('loading');
                            Reports.flot(result.flotData);
                            Reports.flotDetails(result);
                        }
                    }
                });
            },
            flotDetails: function (result) {
                var div = $("div#tajer-graph-details");
                div.find("p#tajer-sales").find("span").text(result.sales);
                div.find("p#tajer-earnings").find("span").text(result.earnings);
            },
            showHideCustomFilter: function () {
                var value = $("#range").val();
                if (value === 'custom') {
                    $("#custom-filter").show();
                } else {
                    $("#custom-filter").hide();
                }
            },
            datePicker: function () {
                $("input.tajer-date").datetimepicker({
                    format: 'Y-m-d H:i:s',
                    mask: true
                });
            },
            flot: function (data) {
                var options = {
                    series: {
                        lines: {
                            show: true
                        },
                        points: {
                            show: true
                        }
                    },
                    grid: {
                        hoverable: true,
                        clickable: true
                    },
                    xaxis: {
                        mode: "time",
                        timeformat: "%Y-%m-%d %H:%M:%S"
                    }
                };
                $.plot("#flot-placeholder", data, options);

                $("<div id='tajer-tooltip'></div>").css({
                    position: "absolute",
                    display: "none",
                    border: "1px solid #fdd",
                    padding: "2px",
                    "background-color": "#fee",
                    opacity: 0.80
                }).appendTo("body");

                $("#flot-placeholder").bind("plothover", function (event, pos, item) {
                    if (item) {
                        var x = (item.datapoint[0]) / 1000,
                            td = moment.utc(x, 'X').format("YYYY-MM-DD HH:mm:ss"),
                            y = item.datapoint[1];
                        $("#tajer-tooltip").html(item.series.label + " of " + td + " = " + y)
                            .css({top: item.pageY + 5, left: item.pageX + 5})
                            .fadeIn(200);
                    } else {
                        $("#tajer-tooltip").hide();
                    }
                });
            }
        };
    Reports.init();
});