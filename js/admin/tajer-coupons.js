jQuery(document).ready(function ($) {

    var Coupons = {
        init: function () {
            this.datePicker();
            this.Chosen();
        },
        datePicker: function () {
            $('#tajer_coupon\\[tajer_expiration_date\\], #tajer_coupon\\[tajer_start_date\\]').datetimepicker({
                format: 'Y-m-d H:i:s',
                mask: true
            });
        },
        Chosen: function () {
            $('select').chosen({
                width: "50%"
            });
        }
    };
    Coupons.init();
});