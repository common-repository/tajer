jQuery(document).ready(function ($) {
    var Thickbox = {
        init: function () {
            //this.colorBox();
            $("#tajer_insert_purchase_link").on('click', this.sendToEditor);
            $("select[name='tajer_thickbox_product_id']").on('change', this.getSubId);
            $("select[name='tajer_thickbox_product_sub_id']").on('change', this.generateURL);
            $("select[name='tajer_thickbox_link_for']").on('change', this.generateURL);
            $("input[name='tajer_link_text']").on('change', this.generateURL);
            $("select[name='tajer_thickbox_link_style']").on('change', this.generateURL);
            $("select[name='tajer_thickbox_link_color']").on('change', this.generateURL);
            $("select[name='tajer_thickbox_link_target']").on('change', this.generateURL);
            $("#tajer_close_thickbox").on('click', this.close_thickbox_window);
        },
        sendToEditor: function (e) {
            e.preventDefault();
            var link = $(".tajer_purchase_link_area");
            if (link.text() != "") {
                window.send_to_editor(link.text());
            }
        },
        getSubId: function () {

            var self = $(this),
                optionSelected = $(this).find("option:selected"),
                valueSelected = optionSelected.val(),
                nonce = $("#tajer_thickbox_nonce_field").val(),
                loader = $(".tajer-thickbox-loading"),
                params = {
                    action: 'tajer_thickbox_get_sub_id',
                    tajerThickBoxNonce: nonce,
                    tajerProductId: valueSelected
                },
                data = $.param(params);

            loader.show();
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: data,
                success: function (result) {
                    loader.hide();
                    //in case we got unexpected result then just extract our json string
                    var extractJsonString = result.match(/\[tajer_json\](\{.+\})\[\/tajer_json\]/);
                    result = $.parseJSON(extractJsonString[1]);
                    //console.log(result);
                    if (result.status == true) {
                        //self.closest('tr').slideUp().remove();
                        $(".tajer_sub_product").show();
                        $("select[name='tajer_thickbox_product_sub_id']").html(result.html);
                        Thickbox.generateURL();
                    } else {
                        $(".tajer_sub_product").hide();
                        $("select[name='tajer_thickbox_product_sub_id']").html("");
                        Thickbox.generateURL();
                    }
                    $("select[name='tajer_thickbox_product_sub_id']").on('change', this.generateURL);
                    $('select').trigger("chosen:updated");
                }
            });
        },

        generateURL: function () {
            var id = $("select[name='tajer_thickbox_product_id']").find("option:selected").val(),

                isSubId = false,
            //check if there is a subid
                subIdDropdown = $("select[name='tajer_thickbox_product_sub_id']"),
                subId = subIdDropdown.find("option:selected").val();

            if (subIdDropdown.html() != '') {
                isSubId = true;
            }

            var linkArea = $("div.tajer_link_area");
            if ($.isNumeric(id)) {
                var action = $("select[name='tajer_thickbox_link_for']").find("option:selected").val(),
                    text = $("input[name='tajer_link_text']").val(),
                    style = $("select[name='tajer_thickbox_link_style']").find("option:selected").val(),
                    color = $("select[name='tajer_thickbox_link_color']").find("option:selected").val(),
                    target = $("select[name='tajer_thickbox_link_target']").find("option:selected").val(),
                    sub_id = isSubId ? ' sub_id= "' + subId + '"' : '';
                linkArea.find("code").text('[tajer_purchase_link action="' + action + '" id="' + id + '"' + sub_id + ' target="' + target + '" style="' + style + '" color="' + color + '" text="' + text + '"]');
                linkArea.show();
            } else {
                linkArea.hide();
            }
        },
        close_thickbox_window: function () {
            //$("#TB_closeWindowButton").fadeOut();
            $("#TB_imageOff").unbind("click");
            $("#TB_closeWindowButton").unbind("click");
            $("#TB_window").fadeOut("fast", function () {
                $('#TB_window,#TB_overlay,#TB_HideSelect').trigger("tb_unload").unbind().remove();
            });
            $('body').removeClass('modal-open');
            $("#TB_load").remove();
            if (typeof document.body.style.maxHeight == "undefined") {//if IE 6
                $("body", "html").css({height: "auto", width: "auto"});
                $("html").css("overflow", "");
            }
            $(document).unbind('.thickbox');
            return false;
        }

    };
    Thickbox.init();
});