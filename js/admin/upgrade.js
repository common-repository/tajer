jQuery(document).ready(function ($) {

    var spinner = $(".tajer-upgrade-container span.is-active"),
        paragraph = $(".tajer-upgrade-container p"),
        progressContainer = $(".tajer-upgrade-container .meter"),
        progressBar = $(".tajer-upgrade-container .meter>span"),
        nonce = $("#tajer_upgrade_nonce").val();
    //params = {action: "tajer_upgrade", nonce: nonce},
    //data = $.param(params);

    var Upgrade = {
        init: function () {
            $("a#upgrade-tajer").on('click', this.upgradeTajer);
        },
        upgradeTajer: function (e) {
            e.preventDefault();
            spinner.addClass('spinner');
            progressContainer.show();
            paragraph.text(TajerUpgrade.start_upgrade_process);
            //progressBar.animate({
            //    width: '1%'
            //}, 50, function () {
            //});
            Upgrade.processStep(1, false);
        },
        processStep: function (step, total) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: $.param({action: "tajer_upgrade", nonce: nonce, total: total, step: step}),
                success: function (result) {
                    //in case we got unexpected result then just extract our json string
                    var extractJsonString = result.match(/\[tajer_json\](\{.+\})\[\/tajer_json\]/);
                    result = $.parseJSON(extractJsonString[1]);

                    if (result.completed) {
                        spinner.removeClass('spinner');
                        paragraph.text(TajerUpgrade.success_upgrade);
                        progressBar.animate({
                            width: result.percentage + '%'
                        }, 50, function () {
                            alert(TajerUpgrade.success_upgrade);
                        });

                    } else {
                        progressBar.animate({
                            width: result.percentage + '%'
                        }, 50, function () {
                        });
                        Upgrade.processStep(result.step, result.total);
                    }
                }
            });
        }
    };
    Upgrade.init();
});