jQuery(document).ready(function ($) {

    var maxNumberOfPages = parseInt($("form#pagination-form").find("span.number-of-pages").text()),
        timer;

    var Orders = {
        tajerModal: $('.ui.suimodal'),
        init: function () {
            $("button.tajer-edit-order").on('click', this.editOrder);
            $(".tajer-modal-buttons").on('click', this.formSubmitting);
            $("form#pagination-form").find("input[name='page']").on('change', this.checkPageNumber);
            //$("button#get-orders-page").on('click', this.getThisPage);
            $("#tajer-page-number").on('keyup', this.getThisPage);
            //$("button#tajer-set-items-per-page").on('click', this.setItemsPerPage);
            $("#items-per-page").on('keyup', this.setItemsPerPage);
            $("button.tajer-nav").on('click', this.nav);
            //$('#tajer-modal').on('show.bs.modal', this.getModalContent);
            this.modalSetup();
        },
        modalSetup: function () {
            Orders.tajerModal.suimodal({
                onShow: Orders.getModalContent,
                observeChanges: true,
                context: $("div.tajer-container"),
                transition: 'vertical flip'
            });
        },
        checkPageNumber: function () {
            var self = $(this),
                value = parseInt(self.val());
            if (value > maxNumberOfPages) {
                self.val(maxNumberOfPages);
            }
        },
        refreshTrigers: function () {
            $("button.tajer-delete-order").off('click').on('click', this.deleteOrder);
            $("button.tajer-edit-order").off('click').on('click', this.editOrder);
        },
        nav: function () {
            var currentPage = parseInt($("form#pagination-form").find("input[name='page']").val()),
                self = $(this),
                getThisPage = 1,
                dir = self.data("nav");
            if (dir == 'next') {

                if ((currentPage + 1) > maxNumberOfPages) {
                    $("form#pagination-form").find("input[name='page']").val(maxNumberOfPages);
                } else {
                    getThisPage = currentPage + 1;
                }
            } else {
                if (currentPage == 1) {
                    return;
                }
                getThisPage = currentPage - 1;
            }
            $("form#pagination-form").find("input[name='page']").val(getThisPage);
            Orders.getThisPage();
        },

        editOrder: function () {
            var self = $(this),
                form = $("form#tajer-modal-form"),
                nonce = self.data("nonce"),
                item = self.data("item");
            form.find("input[name='tajer_modal_submitting_type']").val("edit");
            var title = $("input[name='tajer_edit_title']").val(),
                buttonLabel = $("input[name='tajer_edit_button_label']").val();
            $('.tajer-modal-label').text(title);
            $('.tajer-modal-buttons').text(buttonLabel);
            form.find("input[name='tajer-modify-order-nonce']").val(nonce);
            form.find("input[name='edit-item']").val(item);
            Orders.tajerModal.suimodal('show');
        },
        getModalContent: function () {
            var loading = $("img.gears-loading"),
                params = {action: 'tajer_orders_modal_form_parser'},
                dynamicArea = $('div#form-dynamic-area'),
                data = $("form#tajer-modal-form").serialize() + '&' + $.param(params);

            dynamicArea.empty();
            loading.show();
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: data,
                success: function (result) {
                    //in case we got unexpected result then just extract our json string
                    var extractJsonString = result.match(/\[tajer_json\](\{.+\})\[\/tajer_json\]/);
                    result = $.parseJSON(extractJsonString[1]);
                    //console.log(result);
                    dynamicArea.append(result.content);
                    loading.hide();
                    Orders.refresh();
                }
            });
        },
        formSubmitting: function () {
            //var loading = $("img.gears-loading"),
            var params = {action: 'tajer_edit_order'},
                self = $(this),
                messagesArea = $('div.tajer_orders_message'),
            //dynamicArea = $('div#form-dynamic-area'),
                data = $("form#tajer-modal-form").serialize() + '&' + $.param(params);

            //dynamicArea.empty();
            //loading.show();
            self.addClass('loading');
            //self.prop('disabled', 'disabled');
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: data,
                success: function (result) {
                    //console.log(result);
                    //self.prop("disabled", null);
                    //in case we got unexpected result then just extract our json string
                    var extractJsonString = result.match(/\[tajer_json\](\{.+\})\[\/tajer_json\]/);
                    result = $.parseJSON(extractJsonString[1]);
                    //console.log(result);
                    self.removeClass('loading');
                    messagesArea.append(result.message);
                    setTimeout(
                        function () {
                            messagesArea.empty();
                            location.reload(true);
                        }, 3000);
                }
            });
        },
        setItemsPerPage: function () {
            clearTimeout(timer);
            timer = setTimeout(function () {
                var params = {action: 'tajer_set_items_per_order_page'},
                    self = $(this),
                    loading = $("#items-per-page-loading"),
                    data = $("form#items-per-page-form").serialize() + '&' + $.param(params);

                //self.prop('disabled', 'disabled');
                loading.show();
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: data,
                    success: function (result) {

                        //self.prop("disabled", null);
                        loading.hide();
                        location.reload(true);
                        //console.log(result);
                        //result = $.parseJSON(result);
                    }
                });
            }, 400);
        },
        getThisPage: function () {
            clearTimeout(timer);
            timer = setTimeout(function () {
                var params = {action: 'tajer_get_this_order_page'},
                    dynamicArea = $('div#orders-table-container'),
                    loading = $("#get-this-page-loading"),
                    data = $("form#pagination-form").serialize() + '&' + $.param(params);
                loading.show();
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: data,
                    success: function (result) {
                        loading.hide();
                        //in case we got unexpected result then just extract our json string
                        var extractJsonString = result.match(/\[tajer_json\](\{.+\})\[\/tajer_json\]/);
                        //console.log(extractJsonString);
                        result = $.parseJSON(extractJsonString[1]);
                        dynamicArea.empty().append(result.html);
                        Orders.refreshTrigers();
                    }
                });
            }, 400);
        },

        refresh: function () {
            $('.Tajer .ui.dropdown').tajerdropdown();
            $("#date").datetimepicker({
                format: 'Y-m-d H:i:s',
                mask: true
            });
        }
    };
    Orders.init();
});