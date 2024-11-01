jQuery(document).ready(function ($) {

    var button = $(".tajer_remove_from_cart"),
        form = $("form#tajer_checkout_form"),
        FrontendCart = {
            init: function () {
                this.spinner();
                this.checkbox();
                this.getPaymentGatewayFormDetails();
                button.off('click').on('click', this.removeFromCart);
                $("button#tajer_validate_coupon").off('click').on('click', this.applyCoupon);
                $("input[type='radio'][name='payment-mode']").on('change', this.getPaymentGatewayFormDetails);
                $("#tajer_purchase_form_submit_button").on('click', this.checkout);
                form.on('tajer_form_loaded', this.afterLoadingTheFormViaAjax);
                $(".tajer_quantity_field").on("spinchange", this.quantityChanged);
                $("button.tajer_empty_cart").off('click').on("click", this.emptyCart);
            },
            checkbox: function () {
                $('.Tajer .ui.checkbox').checkbox();
            },

            afterLoadingTheFormViaAjax: function () {
                //retrigger the event
                $("#tajer_purchase_form_submit_button").on('click', FrontendCart.checkout);
                $('.Tajer .ui.dropdown').tajerdropdown();

                //close errors message button
                $('.message .close').on('click', function () {
                    $(this).closest('.message').transition('fade');
                    $("#tajer_purchase_form_details").find(".wide").removeClass("error");
                });
            },
            getPaymentGatewayFormDetails: function () {
                var dynamicArea = $('div#tajer_purchase_form_details');

                if (!dynamicArea.length) {
                    return;
                }

                var action = 'tajer_get_payment_gateway_form_details',
                    getSelectedPaymentGateway = $('input[name=payment-mode]:checked'),

                    loading = $("#tajer_payment_method_selection"),
                    params = {action: action},
                    data = form.serialize() + '&' + $.param(params);

                dynamicArea.empty();
                loading.dimmer('show');
                $.ajax({
                    url: Tajer.ajaxurl,
                    type: 'POST',
                    data: data,
                    success: function (result) {
                        //in case we got unexpected result then just extract our json string
                        var extractJsonString = result.match(/\[tajer_json\](\{.+\})\[\/tajer_json\]/);
                        result = $.parseJSON(extractJsonString[1]);
                        //console.log(result);
                        if (result['form_action']) {
                            form.attr('action', result.form_action);
                        } else {
                            if (getSelectedPaymentGateway.length) {
                                //get current page url and append to it ?payment-mode=getSelectedPaymentGateway
                                var currentURL = $(location).attr('href');
                                if (currentURL.indexOf("?") >= 0) {
                                    form.attr('action', currentURL + '&payment-mode=' + getSelectedPaymentGateway.val());
                                } else {
                                    form.attr('action', currentURL + '?payment-mode=' + getSelectedPaymentGateway.val());
                                }
                            }
                        }
                        dynamicArea.append(result.form_fields);
                        loading.dimmer('hide');
                        form.trigger('tajer_form_loaded');
                    }
                });
                //console.log(checked);
            },
            applyCoupon: function (e) {
                e.preventDefault();
                if ($("#tajer_input_discount").val() == '') {
                    return;
                }
                var action = 'tajer_apply_coupon';

                var self = $(this),
                    quantityField = $(".tajer_quantity_field"),
                    value = quantityField.length ? quantityField.spinner("value") : 1,
                    params = {
                        action: action,
                        quantity: value
                    },
                    data = form.serialize() + '&' + $.param(params);
                //loader = $(".tajer-coupon-loader");
                //loader.show();
                self.addClass('loading');
                $.ajax({
                    url: Tajer.ajaxurl,
                    type: 'POST',
                    data: data,
                    success: function (result) {
                        //in case we got unexpected result then just extract our json string
                        var extractJsonString = result.match(/\[tajer_json\](\{.+\})\[\/tajer_json\]/);
                        result = $.parseJSON(extractJsonString[1]);
                        //loader.hide();
                        self.removeClass('loading');
                        $("span#tajer-discount-error-wrap").text('');

                        $.each(result.status, function (index, value) {
                            if (value == 'error') {
                                $("span#tajer-discount-error-wrap").append(result.message[index]);
                            }
                        });
                        $("span.tajer_total_price").find("span").text(result.user_total);

                    }
                });
            },
            checkout: function (e) {
                var purchaseFormDetails = $("#tajer_purchase_form_details");
                //remove any error states from the form fields
                purchaseFormDetails.find(".wide").removeClass("error");
                var action = 'tajer_checkout';
                e.preventDefault();
                var self = $(this),
                    quantityField = $(".tajer_quantity_field"),
                    value = quantityField.length ? quantityField.spinner("value") : 1,
                    params = {
                        action: action,
                        quantity: value
                    },
                    data = form.serialize() + '&' + $.param(params);

                self.addClass('loading');
                $.ajax({
                    url: Tajer.ajaxurl,
                    type: 'POST',
                    data: data,
                    success: function (result) {
                        //in case we got unexpected result then just extract our json string
                        var extractJsonString = result.match(/\[tajer_json\](\{.+\})\[\/tajer_json\]/);
                        result = $.parseJSON(extractJsonString[1]);
                        self.removeClass('loading');
                        if (result['errors']) {
                            var errorsContainer = $("#tajer_purchase_form_errors");
                            errorsContainer.empty();
                            $.each(result.errors, function (index, value) {
                                //console.log(index);
                                purchaseFormDetails.find("*[name='" + index + "']").closest(".wide").addClass('error');
                                $("<li>" + value + "</li>").appendTo(errorsContainer);
                            });

                            //show the errors message
                            errorsContainer.closest(".message").removeClass("hidden").show();
                        }

                        if (result['success']) {
                            var successContainer = $("#tajer_purchase_form_success_message");
                            successContainer.find(".header").text(result.success.header).end().find("p").text(result.success.body);
                            successContainer.removeClass("hidden");
                        }

                        if (result['redirect_to']) {
                            window.location.href = result.redirect_to;
                        }
                    }
                });
            },
            emptyCart: function () {
                var self = $(this),
                    nonce = self.attr("data-nonce"),
                    params = {
                        action: 'tajer_empty_cart',
                        nonce: nonce
                    },
                    data = $.param(params);


                swal({
                    title: Tajer.delete_warning_message,
                    //text: "You will not be able to recover this imaginary file!",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: Tajer.delete_warning_confirmation_button_text,
                    closeOnConfirm: false
                }, function () {
                    $.ajax({
                        url: Tajer.ajaxurl,
                        type: 'POST',
                        data: data,
                        success: function (result) {
                            swal(
                                {
                                    title: Tajer.success_delete_header_text,
                                    text: Tajer.success_delete_message_text,
                                    timer: 1000,
                                    type: "success",
                                    showConfirmButton: true
                                }
                            );
                            location.reload(true);
                        }
                    });

                });
            },
            quantityChanged: function () {
                var self = $(this),
                    cartId = self.attr("data-cart-id"),
                    nonce = self.attr("data-nonce");

                //get the spinner new value
                var value = self.spinner("value");

                if (parseInt(value) <= 0) {
                    value = 1;
                    self.spinner("value", value);
                } else if (value.toString().indexOf('.') != -1) {
                    value = parseInt(value);
                    self.spinner("value", value);
                }

                var params = {
                        action: 'tajer_increase_decrease_quantity',
                        nonce: nonce,
                        coupon: $("input#tajer_input_discount").val(),
                        quantity: value,
                        cart_id: cartId
                    },
                    data = form.serialize() + '&' + $.param(params),
                //data = $.param(params),
                    loader = $(".tajer-calculator-loader"),
                    totalPrice = $("span.tajer_total_price").find("span");
                totalPrice.hide();
                loader.show();
                $.ajax({
                    url: Tajer.ajaxurl,
                    type: 'POST',
                    data: data,
                    success: function (result) {
                        //in case we got unexpected result then just extract our json string
                        var extractJsonString = result.match(/\[tajer_json\](\{.+\})\[\/tajer_json\]/);
                        result = $.parseJSON(extractJsonString[1]);
                        loader.hide();
                        totalPrice.show().text(result.user_total);
                    }
                });
            },
            removeFromCart: function (e) {
                e.preventDefault();
                var self = $(this),
                    nonce = self.attr("data-nonce"),
                    item_id = self.attr("data-id"),
                    item_sub_id = self.attr("data-sub_id"),
                    cartId = self.attr("data-cart-id"),
                    params = {
                        action: 'tajer_remove_from_cart',
                        nonce: nonce,
                        product_id: item_id,
                        product_sub_id: item_sub_id,
                        cart_id: cartId
                    },
                    data = $.param(params);


                swal({
                    title: Tajer.delete_warning_message,
                    //text: "You will not be able to recover this imaginary file!",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: Tajer.delete_warning_confirmation_button_text,
                    closeOnConfirm: false
                }, function () {
                    var loader = $(".tajer-calculator-loader"),
                        totalPrice = $("span.tajer_total_price").find("span");
                    totalPrice.hide();
                    loader.show();
                    $.ajax({
                        url: Tajer.ajaxurl,
                        type: 'POST',
                        data: data,
                        success: function (result) {
                            self.closest("tr").remove();
                            //in case we got unexpected result then just extract our json string
                            var extractJsonString = result.match(/\[tajer_json\](\{.+\})\[\/tajer_json\]/);
                            result = $.parseJSON(extractJsonString[1]);
                            loader.hide();
                            totalPrice.show().text(result.user_total);
                            swal(
                                {
                                    title: Tajer.success_delete_header_text,
                                    text: Tajer.success_delete_message_text,
                                    timer: 1000,
                                    type: "success",
                                    showConfirmButton: true
                                }
                            );
                        }
                    });
                });
            },
            spinner: function () {
                $(".tajer_quantity_field").spinner({
                    min: 1
                });
            },
            refresh: function () {

            }
        };
    FrontendCart.init();
});

//This code will execute immediately instead of waiting for the DOM ready event
(function ($) {
    //from http://atodorov.org/blog/2013/01/28/remove-query-string-with-javascript-and-html5/
    var uri = window.location.toString();
    if ((uri.indexOf("?") > 0) && (uri.indexOf("tajer_action") > 0)) {
        var clean_uri = uri.substring(0, uri.indexOf("?"));
        window.history.replaceState({}, document.title, clean_uri);
    }
})(jQuery);