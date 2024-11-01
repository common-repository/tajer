jQuery(document).ready(function ($) {
    var PricingWidget = {
        init: function () {
            $(".tajer-pricing-widget-submit").on("click", this.submitForm);
        },
        submitForm: function (e) {
            e.preventDefault();
            var self = $(this),
                form = self.closest('form'),
                params = {action: 'tajer_submit_pricing_widget'},
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
                    //loader.hide();
                    self.removeClass('loading');
                    if (result.status != 'add') {
                        if (result.status == 'exist') {
                            form.find(".tajer-pricing-widget-checkout-link").show();
                        }
                        form.addClass('error');
                        form.removeClass('success');
                        var errorMessage = form.find('.error.message');
                        errorMessage.text(result.message);
                        setTimeout(
                            function () {
                                errorMessage.empty();
                            }, 3000);
                    } else {
                        form.find(".tajer-pricing-widget-checkout-link").show();
                        form.addClass('success');
                        form.removeClass('error');
                        var successMessage = form.find('.success.message');
                        successMessage.text(result.message);
                        setTimeout(
                            function () {
                                successMessage.empty();
                            }, 3000);
                    }
                }
            });
        }
    };
    PricingWidget.init();
});
