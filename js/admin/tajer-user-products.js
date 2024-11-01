jQuery(document).ready(function ($) {

    var maxNumberOfPages = parseInt($("form#pagination-form").find("span.number-of-pages").text()),
        timer,
        tajerModal = $('.ui.suimodal');

    var UserProducts = {
        init: function () {
            $("button.tajer-add-new").on('click', this.addNew);
            $("button.tajer-edit-user-product").on('click', this.editUserProduct);
            $("button.tajer-delete-user-product").on('click', this.deleteUserProduct);
            $(".tajer-modal-buttons").on('click', this.formSubmitting);
            $("form#pagination-form").find("input[name='page']").on('change', this.checkPageNumber);
            //$("button#get-user-products-page").on('click', this.getThisPage);
            $("#tajer-page-number").on('keyup', this.getThisPage);
            //$("button#tajer-set-items-per-page").on('click', this.setItemsPerPage);
            $("#items-per-page").on('keyup', this.setItemsPerPage);
            $("button.tajer-nav").on('click', this.nav);
            this.modalSetup();
            //tajerModal.modal('onShow', this.getModalContent);
        },
        checkPageNumber: function () {
            var self = $(this),
                value = parseInt(self.val());
            if (value > maxNumberOfPages) {
                self.val(maxNumberOfPages);
            }
        },
        modalSetup: function () {
            tajerModal.suimodal({
                onShow: UserProducts.getModalContent,
                observeChanges: true,
                context: $("div.tajer-container"),
                transition: 'vertical flip'
            });
        },
        refreshTrigers: function () {
            $("button.tajer-delete-user-product").off('click').on('click', this.deleteUserProduct);
            $("button.tajer-edit-user-product").off('click').on('click', this.editUserProduct);
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
            UserProducts.getThisPage();
        },
        deleteUserProduct: function () {
            if (confirm('Are you sure?')) {
                var self = $(this),
                    nonce = self.data("nonce"),
                    item = self.data("item"),
                    params = {action: 'tajer_delete_user_product', tajerModifyUserProductNonce: nonce, itemId: item},
                    data = $.param(params);

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: data,
                    success: function (result) {
                        //in case we got unexpected result then just extract our json string
                        var extractJsonString = result.match(/\[tajer_json\](\{.+\})\[\/tajer_json\]/);
                        result = $.parseJSON(extractJsonString[1]);
                        //console.log(result);
                        if (result.status == 'true') {
                            self.closest('tr').slideUp().remove();
                        }
                    }
                });
            }
        },
        editUserProduct: function () {
            var self = $(this),
                form = $("form#tajer-modal-form"),
                nonce = self.data("nonce"),
                item = self.data("item");
            form.find("input[name='tajer_modal_submitting_type']").val("edit");
            var title = $("input[name='tajer_edit_title']").val(),
                buttonLabel = $("input[name='tajer_edit_button_label']").val();
            $('.tajer-modal-label').text(title);
            $('.tajer-modal-buttons').text(buttonLabel);
            form.find("input[name='tajer-modify-user-product-nonce']").val(nonce);
            form.find("input[name='edit-item']").val(item);
            tajerModal.suimodal('show');
        },
        getModalContent: function () {
            //console.log('hi');
            var loading = $("img.gears-loading"),
                params = {action: 'tajer_modal_form_parser'},
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
                    UserProducts.refresh();
                    //tajerModal.modal("refresh");
                }
            });
        },
        formSubmitting: function () {
            //var loading = $("img.gears-loading"),
            var params = {action: 'tajer_add_user_product'},
                self = $(this),
                messagesArea = $('div.tajer_user_products_message'),
                data = $("form#tajer-modal-form").serialize() + '&' + $.param(params);

            //dynamicArea.empty();
            //loading.show();
            //self.prop('disabled', 'disabled');
            self.addClass('loading');
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: data,
                success: function (result) {
                    //self.prop("disabled", null);
                    self.removeClass('loading');
                    //in case we got unexpected result then just extract our json string
                    var extractJsonString = result.match(/\[tajer_json\](\{.+\})\[\/tajer_json\]/);
                    result = $.parseJSON(extractJsonString[1]);
                    //console.log(result);

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
                var params = {action: 'tajer_set_items_per_page'},
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
                var params = {action: 'tajer_get_this_page'},
                    dynamicArea = $('div#user-products-table-container'),
                    loading = $("#get-this-page-loading"),
                    data = $("form#pagination-form").serialize() + '&' + $.param(params);
                loading.show();
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: data,
                    success: function (result) {
                        loading.hide();
                        //console.log(result);
                        //in case we got unexpected result then just extract our json string
                        var extractJsonString = result.match(/\[tajer_json\](\{.+\})\[\/tajer_json\]/);
                        result = $.parseJSON(extractJsonString[1]);
                        dynamicArea.empty().append(result.html);
                        UserProducts.refreshTrigers();
                    }
                });

            }, 400);
        },
        addNew: function () {
            $("form#tajer-modal-form").find("input[name='tajer_modal_submitting_type']").val("add");
            var title = $("input[name='tajer_add_title']").val(),
                buttonLabel = $("input[name='tajer_add_button_label']").val();
            $('.tajer-modal-label').text(title);
            $('.tajer-modal-buttons').text(buttonLabel);
            tajerModal.suimodal('show');
        },
        refresh: function () {
            $('.Tajer .ui.dropdown').tajerdropdown();
            //tajerModal.modal('refresh');
            $("#buying-date, #expiration-date").datetimepicker({
                format: 'Y-m-d H:i:s',
                mask: true
            });
        }
    };
    UserProducts.init();
});