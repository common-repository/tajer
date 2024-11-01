jQuery(document).ready(function ($) {
    var button = $("div.tajer_add_to_cart");
    var FrontendProduct = {
        init: function () {
            //this.buttonsSetup();
            button.off('click').on('click', this.addRemoveFromCart);
            this.fixBorderRadius();
        },
        addRemoveFromCart: function () {
            var self = $(this),
                nonce = self.attr("data-nonce"),
                item_id = self.attr("data-id"),
                item_sub_id = self.attr("data-sub_id"),
                cartId = self.attr("data-cart-id"),
            //text = self.find("span#tajer-text"),
                dynamicArea = $("div#tajer_buttons_notification"),
                params = {
                    action: 'tajer_add_to_cart_button',
                    nonce: nonce,
                    product_id: item_id,
                    product_sub_id: item_sub_id,
                    cart_id: cartId
                },
                data = $.param(params);
            self.addClass('loading');
            //currentText = text.text();
            //text.text(self.attr("data-cart-text"));
            $.ajax({
                url: Tajer.ajaxurl,
                type: 'POST',
                //cache : false,
                data: data,
                success: function (result) {
                    //in case we got unexpected result then just extract our json string
                    var extractJsonString = result.match(/\[tajer_json\](\{.+\})\[\/tajer_json\]/);
                    result = $.parseJSON(extractJsonString[1]);
                    //text.text(currentText);
                    if ((result.status != 'error') || (result.status != 'login')) {
                        //adding case
                        if (result.status == 'add') {
                            self.find("div.hidden.content").text(Tajer.remove_from_cart_text);
                            //self.find("i").removeClass("fa-square-o");
                            //self.find("i").addClass("fa-check-square-o");
                            self.attr('data-cart-id', result.id);
                        } else {
                            self.find("div.hidden.content").text(Tajer.add_to_cart_text);
                            //removing case
                            //self.find("i").removeClass("fa-check-square-o");
                            //self.find("i").addClass("fa-square-o");
                            self.attr('data-cart-id', result.id);
                        }
                    }
                    //self.find("i").addClass("fa-check-square-o");
                    dynamicArea.empty().append(result.message);
                    self.removeClass('loading');
                    setTimeout(
                        function () {
                            dynamicArea.empty();
                        }, 3000);
                }
            });

        },
        //Fix border radius of the add/remove from cart button in case there is only one price(the border just added to the bottom of the button).
        fixBorderRadius: function () {
            var buttonsGroup = $(".tajer-vertical-button-group"),
                count = buttonsGroup.children().length;
            if (count == 1) {
                var firstDiv = buttonsGroup.find("div").first();
                var currentRadius = firstDiv.css("border-bottom-left-radius");
                firstDiv.css("border-radius", currentRadius);
            }
        }
    };
    FrontendProduct.init();
});