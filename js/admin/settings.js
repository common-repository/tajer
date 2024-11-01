jQuery(document).ready(function ($) {
    var Settings = {
        container: $(".tajer-container"),
        dropDowns: $('.Tajer .ui.dropdown'),
        tajerModal: $('.ui.suimodal'),
        init: function () {
            this.tab();
            this.dismissableMessages();
            this.showHideTaxSettings();
            this.showHidePurchaseReceiptEmailSettings();
            this.showHideSaleNotificationEmailSettings();
            this.showHideExpirationNotificationEmailSettings();
            this.showHideDebugEmail();
            this.showHideProductsDropdownInModal();
            this.fileUploader();
            this.dropDowns.tajerdropdown({
                context: Settings.container,
                keepOnScreen: true
            });
            $(".Tajer form *[data-content]").popup({
                context: Settings.container,
                inline: true
            });

            $("input:checkbox[name='tajer_tax_settings[enable_taxes]']").on('click', this.showHideTaxSettings);
            $('button#tajer-save-settings').off('click').on('click', this.saveSettings);
            $("button:button[name='tajer_tools_settings[tajer_import]']").off('click').on('click', this.showModal);
            this.modalSetup();
            $(".tajer-upload-file").off('click').on('click', this.openMediaLibrary);
            $(".tajer-remove-image").off('click').on('click', this.removeImage);
            $('input:checkbox[name="tajer_emails_settings[enable_purchase_receipt_notification]"]').on('click', this.showHidePurchaseReceiptEmailSettings);
            $('input:checkbox[name="tajer_emails_settings[enable_sale_notification]"]').on('click', this.showHideSaleNotificationEmailSettings);
            $('input:checkbox[name="tajer_emails_settings[enable_expiration_notification_emails]"]').on('click', this.showHideExpirationNotificationEmailSettings);
            $('input:checkbox[name="tajer_payment_settings[debug_mode]"]').on('click', this.showHideDebugEmail);
            $('input:radio[name="tajer-importing-type"]').on('change', this.showHideProductsDropdownInModal);
            $('#tajer_product_id').on('change', this.fileUploader);

            $("button.tajer_add_tax_rate").on('click', this.addTaxRate);
            $("button.tajer_remove_tax_rate").on('click', this.removeTaxRate);
        },
        showModal: function () {
            Settings.tajerModal.suimodal('show');
        },
        removeImage: function (e) {
            e.preventDefault();
            var self = $(this),
                container = self.closest(".tajer-field");
            container.find("img").attr('src', '');
            container.find("input[type='hidden']").val('');
            container.find("button").removeClass('bottom attached');
            container.find("div.image").hide();
        },
        openMediaLibrary: function (e) {
            e.preventDefault();
            var file_frame, image_data,
                self = $(this),
                container = self.closest(".tajer-field");

            //window.tajerRowWrapper = self.closest("td");

            if (undefined !== file_frame) {
                file_frame.open();
                return;
            }

            file_frame = wp.media.frames.file_frame = wp.media({
                frame: 'post',
                state: 'insert',
                multiple: false
            });

            file_frame.on('menu:render:default', function (view) {
                // Store our views in an object.
                var views = {};

                // Initialize the views in our view object.
                view.set(views);
            });

            file_frame.on('insert', function () {

                // Read the JSON data returned from the Media Uploader
                json = file_frame.state().get('selection').first().toJSON();

                // First, make sure that we have the URL of an image to display
                if (0 > $.trim(json.url.length)) {
                    return;
                }
                //console.log(json);

                container.find("input[type='hidden']").val(json.id);
                //container.find(".tajer-file-name").val(json.filename);
                container.find("img").attr('src', json.url);

                container.find("button").addClass('bottom attached');
                container.find("div.image").show();
            });

            file_frame.open();
        },
        modalSetup: function () {
            Settings.tajerModal.suimodal({
                observeChanges: true,
                context: $("div.tajer-container"),
                transition: 'vertical flip'
            });
        },
        showHideProductsDropdownInModal: function () {
            var radio = $('input:radio[name="tajer-importing-type"]:checked').val(),
                div = $("#tajer-modal-products-section");
            switch (radio) {
                case 'product':
                    div.slideDown();
                    break;
                case 'settings':
                    div.slideUp();
                    break;
            }
            Settings.fileUploader();
        },
        tab: function () {
            $('.ui.menu .item').tab();
        },
        fileUploader: function () {
            var nonce = $("#upload_nonce_field").val(),
                productId = $('#tajer_product_id').val(),
                importingType = $('input:radio[name="tajer-importing-type"]:checked').val();
            $('#fileupload').fileupload({
                //dataType: 'json',
                done: function (e, data) {
                    //in case we got unexpected result then just extract our json string
                    var extractJsonString = data.result.match(/\[tajer_json\](\{.+\})\[\/tajer_json\]/);
                    data.result = $.parseJSON(extractJsonString[1]);
                    data.context.text(data.result.name);
                    location.reload(true);
                },
                progressall: function (e, data) {
                    var progress = parseInt(data.loaded / data.total * 100, 10);
                    $('#progress .bar').css(
                        'width',
                        progress + '%'
                    );
                },
                add: function (e, data) {
                    $.each(data.files, function (index, file) {
                        //console.log('Added file: ' + file.name);
                        data.context = $('<p/>').text(file.name + ' Uploading...').appendTo('#drag-and-drop-zone');
                    });
                    data.submit();
                },
                fail: function (e, data) {
                    console.log('tajer-uploader-error');
                },
                formData: {upload_nonce_field: nonce, tajer_importing_type: importingType, tajer_product_id: productId}
            });

        },
        dismissableMessages: function () {
            $('.message .close')
                .on('click', function () {
                    $(this)
                        .closest('.message')
                        .transition('fade')
                    ;
                })
            ;
        },
        removeTaxRate: function (e) {
            e.preventDefault();
            var rowCount = $("#tajer_tax_rates_table tr").length,
                self = $(this);
            if (rowCount > 2) {
                if (confirm('Are you sure?')) {
                    self.closest('tr').remove();
                }
            }
        },
        //popover: function () {
        //    $('div#tajer-user-settings').find('textarea, input, select').popover();
        //},
        getMaxRowIndex: function () {
            var highest = 1;
            $('div.tajer_tax_rates_div').find('tr.tajer_repeatable_row').each(function () {
                var current = parseInt($(this).attr("data-index"));
                if (current > highest) {
                    highest = current;
                }
            });
            return highest += 1;
        },
        addTaxRate: function (e) {
            //console.log('hi');
            e.preventDefault();
            var clone = $('#tajer_tax_rates_table').find("tbody tr:first").clone(),
                maxIndex = Settings.getMaxRowIndex();
            //console.log(clone.length);
            clone.attr('data-index', maxIndex).find(".tajer_tax_field").each(function () {
                var self = $(this),
                    td = self.closest("td");
                if (self.hasClass("dropdown")) {
                    var select = self.find("select");
                    select.addClass("ui search dropdown tajer_tax_field").attr({
                        'name': Settings.newInputName(select, maxIndex)
                    });
                    td.prepend(select);
                    self.remove();
                    td.find("select").tajerdropdown({
                        context: Settings.container,
                        keepOnScreen: true
                    });
                } else {
                    if (self.attr("type") == 'checkbox') {
                        self.attr({
                            'name': Settings.newInputName(self, maxIndex)
                        });
                        self.prop('checked', false);
                    } else {
                        self.attr({
                            'name': Settings.newInputName(self, maxIndex),
                            'value': ''
                        });
                    }
                }
            }).end().appendTo("table#tajer_tax_rates_table");
            $("button.tajer_remove_tax_rate").off('click').on('click', Settings.removeTaxRate);
        },
        newInputName: function (self, maxIndex) {
            //console.log(self.html()+'|'+maxIndex);
            var currentName = self.attr("name");
            if (currentName.indexOf("tajer") >= 0) {
                //just replace the number in the name by a new number
                return currentName.replace(/\d+/, maxIndex);
            }
            return currentName;
        },
        showHideTaxSettings: function () {
            //Hide and show roles multi select menu
            var self = $("input:checkbox[name='tajer_tax_settings[enable_taxes]']"),
                isChecked = $('input:checkbox[name="tajer_tax_settings[enable_taxes]"]:checked');
            if (isChecked.length > 0) {
                self.closest("div.field").nextAll().slideDown();
            } else {
                self.closest("div.field").nextAll().slideUp();
            }
        },
        showHidePurchaseReceiptEmailSettings: function () {
            //Hide and show roles multi select menu
            var self = $('input:checkbox[name="tajer_emails_settings[enable_purchase_receipt_notification]"]'),
                isChecked = $('input:checkbox[name="tajer_emails_settings[enable_purchase_receipt_notification]"]:checked');
            if (isChecked.length > 0) {
                self.closest("div.field").nextAll('.field:lt(2)').slideDown();
            } else {
                self.closest("div.field").nextAll('.field:lt(2)').slideUp();
            }
        },
        showHideSaleNotificationEmailSettings: function () {
            //Hide and show roles multi select menu
            var self = $('input:checkbox[name="tajer_emails_settings[enable_sale_notification]"]'),
                isChecked = $('input:checkbox[name="tajer_emails_settings[enable_sale_notification]"]:checked');
            if (isChecked.length > 0) {
                self.closest("div.field").nextAll('.field:lt(2)').slideDown();
            } else {
                self.closest("div.field").nextAll('.field:lt(2)').slideUp();
            }
        },
        showHideExpirationNotificationEmailSettings: function () {
            //Hide and show roles multi select menu
            var self = $('input:checkbox[name="tajer_emails_settings[enable_expiration_notification_emails]"]'),
                isChecked = $('input:checkbox[name="tajer_emails_settings[enable_expiration_notification_emails]"]:checked');
            if (isChecked.length > 0) {
                self.closest("div.field").nextAll('.field:lt(3)').slideDown();
            } else {
                self.closest("div.field").nextAll('.field:lt(3)').slideUp();
            }
        },
        showHideDebugEmail: function () {
            //Hide and show roles multi select menu
            var self = $('input:checkbox[name="tajer_payment_settings[debug_mode]"]'),
                isChecked = $('input:checkbox[name="tajer_payment_settings[debug_mode]"]:checked');
            if (isChecked.length > 0) {
                self.closest("div.tajer-field").next().slideDown();
            } else {
                self.closest("div.tajer-field").next().slideUp();
            }
        },
        saveSettings: function () {
            var params = {action: 'tajer_save_settings'},
                self = $(this),
            //saving = $("span.tajer-saving"),
                buttonText = self.find("span"),
                buttonCurrentText = self.find("span").text(),
                buttonIcon = self.find("i"),
                data = $("form.tajer-settings-form").serialize() + '&' + $.param(params);

            self.addClass('loading');
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: data,
                success: function (result) {
                    //in case we got unexpected result then just extract our json string
                    var extractJsonString = result.match(/\[tajer_json\](\{.+\})\[\/tajer_json\]/);
                    result = $.parseJSON(extractJsonString[1]);

                    $.each(result.success_messages, function (index, value) {
                        $('<div class="ui success message"><i class="close icon"></i><p>' + value + '</p></div>').appendTo('.ui.segment');
                    });
                    $.each(result.error_messages, function (index, value) {
                        $('<div class="ui negative message"><i class="close icon"></i><p>' + value + '</p></div>').appendTo('.ui.segment');
                    });

                    Settings.dismissableMessages();

                    self.removeClass('loading');

                    buttonText.text('Saved successfully!');
                    buttonIcon.removeClass("save").addClass("checkmark");
                    setTimeout(
                        function () {
                            buttonText.text(buttonCurrentText);
                            buttonIcon.removeClass("checkmark").addClass("save");
                        }, 1500);
                }
            });
        }
    };
    Settings.init();
});