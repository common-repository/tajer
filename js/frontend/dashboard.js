jQuery(document).ready(function ($) {
    var Dashboard = {
        init: function () {

            $("a.tajer-remove-frontend-product").off('click').on('click', this.deleteProduct);
            $("a.tajer-remove-label").off('click').on('click', this.deleteLabel);
            $('.ui.dropdown').tajerdropdown({
                action: 'nothing'
            });
        },
        deleteLabel: function () {
            $(this).remove();
        },
        deleteProduct: function (e) {
            e.preventDefault();
            var self = $(this),
                nonce = self.attr("data-nonce"),
                item_id = self.attr("data-id"),
                params = {
                    action: 'tajer_remove_user_product_from_frontend',
                    nonce: nonce,
                    user_product_id: item_id
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
                        self.closest("tr").remove();
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


            //if (confirm("Are you sure!")) {
            //
            //}
        }
    };
    Dashboard.init();
});