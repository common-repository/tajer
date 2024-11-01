jQuery(document).ready(function ($) {
    var ContentSeller = {
        init: function () {
            this.Chosen();
            $("a.tajer_content_seller_remove_row").on('click', this.removeRow);
            $("input:radio[name='tajer_content_seller[enabled]']").on('click', this.showHide);
            this.showHide();
            $(".tajer-add-content_seller-product").on('click', this.addProduct);
            $('.tajer_content_seller_product').on('change', this.getProductSubIds);
        },
        getProductSubIds: function () {


            var self = $(this),
                nonce = $("#tajer_content_seller_nonce_field").val(),
                productId = self.find(":selected").val(),
                subIdsElement = self.closest("tr").find(".tajer_content_seller_products_ids"),
                loader = self.closest('.Tajer').find("span.is-active"),
                params = {action: "tajer_get_content_seller_product_sub_ids", nonce: nonce, productId: productId},
                data = $.param(params);

            loader.addClass('spinner');
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: data,
                success: function (result) {
                    loader.removeClass('spinner');
                    //in case we got unexpected result then just extract our json string
                    var extractJsonString = result.match(/\[tajer_json\](\{.+\})\[\/tajer_json\]/);
                    result = $.parseJSON(extractJsonString[1]);

                    //console.log(result);
                    subIdsElement.html(result.subIds);
                    //TajerPostType.Chosen();
                    $('select').trigger("chosen:updated");
                }
            });
        },
        getMaxRowIndex: function (table) {


            var highest = 1,
                clone = $(table).find('tr');


            clone.each(function () {
                //console.log('p');
                var current = $(this).attr('data-index');

                if (parseInt(current) > highest) {
                    highest = parseInt(current);
                }
            });
            return highest += 1;
        },
        newInputName: function (self, maxIndex) {
            var currentName = self.attr("name");
            if (currentName.indexOf("tajer") >= 0) {
                //just replace the number in the name by a new number
                return currentName.replace(/\d+/, maxIndex);
            }
            return currentName;
        },
        addProduct: function (e) {
            e.preventDefault();
            var table = $("table#tajer_content_seller_table"),
                clone = table.find("tr:first").next().clone(),
                maxIndex = ContentSeller.getMaxRowIndex("table#tajer_content_seller_table");

            clone.attr('data-index', maxIndex).find("input").each(function () {
                var self = $(this);
                if (self.attr("name") && self.attr("value")) {
                    self.attr({
                        'name': ContentSeller.newInputName(self, maxIndex),
                        'value': ''
                    });
                }

                if (self.attr("value")) {
                    self.attr({
                        'value': ''
                    });
                }

            });
            //for select fields
            clone.find("select").find("option").prop("selected", false).end().each(function () {
                var self = $(this);
                if (self.attr("name")) {
                    self.attr({
                        'name': ContentSeller.newInputName(self, maxIndex)
                    });
                }
            });
            clone.find("div.chosen-container").remove().end().find("select").show().end().appendTo("table#tajer_content_seller_table");
            //$("a.tajer_remove_repeatable_bundle_product").on('click', ContentSeller.removeFilesRow);
            //$('.tajer_bundled_products_product').on('change', ContentSeller.getProductSubIds);
            //apply chosen again chosen
            $('.tajer_content_seller_product').on('change', ContentSeller.getProductSubIds);
            $("a.tajer_content_seller_remove_row").on('click', ContentSeller.removeRow);
            ContentSeller.Chosen();
        },
        showHide: function () {
            var self = $('#content_seller_yes:checked'),
                ddd = $('#content_seller_no:checked'),
                div = $('.tajer_content_seller');

            if (self.length) {
                div.show();
            } else if (ddd.length) {
                div.hide();
            }
        },
        removeRow: function (e) {
            e.preventDefault();
            //console.log('yes');
            var self = $(this);
            //var rowCount = $("#multiple_price_table tr").length;
            var rowCount = self.closest("table").find('tr').length;

            //to get the id
            if (rowCount > 2) {
                if (confirm('Are you sure?')) {
                    self.closest('tr').remove();
                }
            }
        },
        Chosen: function () {
            //jQuery chosen
            $('.tajer_content_seller select').chosen({
                width: "100%"
            });
        }
        //doSomething: function (e) {
        //    e.preventDefault();
        //    var self = $(this),
        //        form = self.closest('form'),
        //        params = {action: 'tajer_action'},
        //        data = form.serialize() + '&' + $.param(params);
        //
        //    self.addClass('loading');
        //    $.ajax({
        //        url: Tajer.ajaxurl,
        //        type: 'POST',
        //        data: data,
        //        success: function (result) {
        //            //in case we got unexpected result then just extract our json string
        //            var extractJsonString = result.match(/\[tajer_json\](\{.+\})\[\/tajer_json\]/);
        //            result = $.parseJSON(extractJsonString[1]);
        //            self.removeClass('loading');
        //            if (result.status != 'example') {
        //
        //            } else {
        //                setTimeout(
        //                    function () {
        //                        successMessage.empty();
        //                    }, 3000);
        //            }
        //        }
        //    });
        //}
    };
    ContentSeller.init();
});
