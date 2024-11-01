jQuery(document).ready(function ($) {
    var form = $("form#tajer_checkout_form"),
        PayPal = {
            init: function () {
                this.updateQuantity();
                $(".tajer_quantity_field").on("spinchange", this.updateQuantity);
                form.on('tajer_form_loaded', this.updateQuantity);
            },
            updateQuantity: function () {
                var quantityField = $(".tajer_quantity_field"),
                    quantity = quantityField.length ? quantityField.spinner("value") : 1;
                $("input[name='quantity']").val(quantity);
            }
        };
    PayPal.init();
});
