jQuery(document).ready(function ($) {

    var TajerPostType = {
        init: function () {
            this.showHideRolesMenu();
            this.showHideMultipleFiles();
            this.sortPrices();
            this.priceOptionsDialogIntinator();
            this.recurring();
            this.enableUpgrade();
            this.enableTrial();
            this.Chosen();

            $(".tajer-add-price").on('click', this.addPricingRow);
            $("span.tajer_price_id").off('click').on('click', this.editPriceId);
            $(".tajer-upload-file").off('click').on('click', this.openMediaLibrary);
            $("a.tajer_product_prices_options").on('click', this.changePriceOptions);
            $(".tajer-add-recurring").on('click', {id: "recurring_table", n: 9}, this.add);
            $(".tajer-add-upgrade").on('click', {id: "upgrade_table", n: 7}, this.add);
            $(".tajer-add-trial").on('click', {id: "trial_table", n: 7}, this.add);
            $(".tajer-remove-recurring").on('click', {table: "recurring_table"}, this.removeTableRow);
            $(".tajer-remove-upgrade").on('click', {table: "upgrade_table"}, this.removeTableRow);
            $(".tajer-remove-trial").on('click', {table: "trial_table"}, this.removeTableRow);
            $(".tajer-add-file").on('click', this.addFile);
            $(".tajer-add-bundle-product").on('click', this.addBundleProduct);
            $('.tajer_bundled_products_product').on('change', this.getProductSubIds);
            $("input:radio[name='tajer-capabilities-price-option']").on('click', this.showHideRolesMenu);
            $("a.tajer_remove_repeatable").on('click', this.removePricingRow);
            $("a.tajer_remove_repeatable_bundle_product").on('click', this.removeFilesRow);
            $("a.tajer_remove_repeatable_file_assignment").on('click', {action: "tajer_delete_file_assignment"}, this.removeFilesRow);
            $("input:checkbox[name='tajer_bundle']").on('click', this.showHideMultipleFiles);
            $("input:radio[name='tajer_is_recurring']").on('click', this.recurring);
            $("input:radio[name='tajer_is_upgrade']").on('click', this.enableUpgrade);
            $("input:radio[name='tajer_is_trial']").on('click', this.enableTrial);
        },
        editPriceId: function () {
            //check if there is another one
            $("#tajer-new-price-id").closest("td").find(".tajer_price_id").show().end().end().remove();

            var self = $(this),
                currentId = parseInt(self.text()),
                td = self.closest("td"),
                newPriceId,
                newInput,
                currentIds = TajerPostType.getPriceIds();

            self.hide();

            $('<input id="tajer-new-price-id" value="' + currentId + '" type="text"/>').appendTo(td).focus().on('input', function () {
                var $this = $(this);
                newInput = $this;
                newPriceId = parseInt($this.val());
                TajerPostType.validatePriceId(self, newInput, td, newPriceId, currentId, currentIds);

                //on click on everywhere except #tajer-new-price-id update it.
                $('html').on('click', function () {
                    TajerPostType.updatePriceId(td, newInput, self, newPriceId);
                    newInput.remove();
                    TajerPostType.pricesAssignment(currentId, false, newPriceId);
                    $('select').trigger("chosen:updated");
                });


            });

            //on click on everywhere except #tajer-new-price-id update it.
            $('.tajer_price_id').click(function (event) {
                event.stopPropagation();
            });

            $('html').one('click', function () {
                newInput = $("#tajer-new-price-id");
                newPriceId = parseInt(newInput.val());
                TajerPostType.validatePriceId(self, newInput, td, newPriceId, currentId, currentIds);
                newInput.closest("td").find(".tajer_price_id").show().end().end().remove();
                TajerPostType.pricesAssignment(currentId, false, newPriceId);
                $('select').trigger("chosen:updated");
            });
        },
        validatePriceId: function (self, newInput, td, newPriceId, currentId, currentIds) {
            var span = td.find(".tajer_price_id");
            if ((($.inArray(newPriceId, currentIds) !== -1) && (currentId != newPriceId)) || (newPriceId <= 0)) {
                newInput.val(currentId);
                span.text(currentId);
            } else if (isNaN(newPriceId)) {
                newInput.val(currentId);
                span.text(currentId);
            } else {
                newInput.val(newPriceId);
                span.text(newPriceId);
            }
        },
        updatePriceId: function (td, newInput, span, newPriceId) {
            //var newId = newInput.val();
            td.closest("tr").attr('data-index', newPriceId).find("input[type=text], input[type=hidden]").each(function () {
                var self = $(this);
                if (self.attr("name")) {
                    self.attr({
                        'name': TajerPostType.newInputName(self, newPriceId)
                    });
                }
            }).end().find("input[type=radio]").val(newPriceId);
            span.text(newPriceId).show();
            //$("span.tajer_price_id").off('click').on('click', this.editPriceId);
        },
        getProductSubIds: function () {
            var self = $(this),
                nonce = $("#tajer_bundle_nonce_field").val(),
                productId = self.find(":selected").val(),
                subIdsElement = self.closest("tr").find(".tajer_bundled_products_ids"),
                loader = $(".tajer-bundle-loader"),
                params = {action: "tajer_get_product_sub_ids", tajerBundleNonce: nonce, productId: productId},
                data = $.param(params);

            loader.show();
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: data,
                success: function (result) {
                    loader.hide();
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
        openMediaLibrary: function (e) {
            e.preventDefault();
            var file_frame, image_data,
                self = $(this),
                container = self.closest("tr");

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

                // Unset default menu items
                view.unset('library-separator');
                view.unset('gallery');
                view.unset('featured-image');
                view.unset('embed');

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

                container.find(".tajer-attachment-id").val(json.id);
                container.find(".tajer-file-name").val(json.filename);
                container.find(".tajer-file-url").val(json.url);


            });

            file_frame.open();
        },
        changePriceOptions: function (e) {
            e.preventDefault();
            var priceOptionsDialog = $("#price-options-dialog");
            priceOptionsDialog.dialog("open");

            var self = $(this);

            var variablePriceContainerTag = self.closest('tr.tajer_repeatable_row');

            priceOptionsDialog.trigger("tajerPriceOptionsDialog", [self, variablePriceContainerTag]);

            var container = variablePriceContainerTag,
                priceOptionsFor = variablePriceContainerTag.attr('data-index');

            var roles = container.find("input[name*='roles']").val(),
                rolesArray = roles.split(','),
            //download_link_expiration = container.find("input[name*='download_link_expiration']").val(),
                price_expiration = container.find("input[name*='price_expiration']").val(),
                file_download_limit = container.find("input[name*='file_download_limit']").val(),
                capabilities = container.find("input[name*='capabilities']").val();

            $('#tajer-for-' + capabilities + '-capabilities-price-option').prop('checked', true);
            TajerPostType.showHideRolesMenu();

            $("#tajer-role-price-option > option").each(function () {
                var option = $(this);
                option.prop('selected', false);
                if ($.inArray(option.val(), rolesArray) !== -1) {
                    option.prop('selected', true);
                }
            });
            //$("#tajer-download-link-expiration-price-option").val(download_link_expiration);
            $("#tajer-price-expiration-date").val(price_expiration);
            $("#tajer-file-download-limit-price-option").val(file_download_limit);

            $("input:hidden[name='tajer_price_options_for']").val(priceOptionsFor);

            $('select').trigger("chosen:updated");
        },
        add: function (e) {
            e.preventDefault();
            var tableId = e.data.id,
                numberOfRows = e.data.n,
                clone = $("table#" + tableId).find('tr:lt(' + numberOfRows + ')').clone(),
                maxIndex = TajerPostType.getMaxRowIndex("table#" + tableId);
            //clone.appendTo("table#"+table);
            //set the index of the rows.
            clone.each(function () {
                var self = $(this);
                self.attr('data-index', maxIndex);
            });

            //for input fields
            clone.find("input[type=text]").each(function () {
                var self = $(this);
                if (self.attr("name")) {
                    self.attr({
                        'name': TajerPostType.newInputName(self, maxIndex),
                        'value': ''
                    });
                }
            });
            //for select fields
            clone.find("select").each(function () {

                var self = $(this);
                if (self.attr("name")) {
                    self.attr({
                        'name': TajerPostType.newInputName(self, maxIndex)
                    });
                }

            });

            clone.find("div.chosen-container").remove().end().find("select").show().end().appendTo("table#" + tableId);
            //apply chosen again chosen
            TajerPostType.Chosen();
        },
        removeFilesRow: function (e) {
            e.preventDefault();
            var self = $(this);
            var rowCount = self.closest("table").find("tr").length;
            if (rowCount > 2) {
                self.closest("tr").remove();
            }
        },
        removeTableRow: function (e) {
            e.preventDefault();
            //get the index of the last table row
            var index = parseInt($('table#' + e.data.table + ' tr:last').data('index'));
            if (index > 1) {
                $('tr[data-index="' + index + '"]').remove();
            }
        },
        showHideDiv: function (self, div, ddd) {
            if (self.length) {
                div.show();
            } else if (ddd.length) {
                div.hide();
            }
        }, enableTrial: function () {
            var self = $('#is_trial_yes:checked'),
                ddd = $('#is_trial_no:checked'),
                div = $('.tajer_enable_trial');
            TajerPostType.showHideDiv(self, div, ddd);
        },
        newInputName: function (self, maxIndex) {
            var currentName = self.attr("name");
            if (currentName.indexOf("tajer") >= 0) {
                //just replace the number in the name by a new number
                return currentName.replace(/\d+/, maxIndex);
            }
            return currentName;
        },
        refresh: function () {
            $("a.tajer_remove_repeatable_bundle_product").on('click', TajerPostType.removeFilesRow);
            $("span.tajer_price_id").off('click').on('click', TajerPostType.editPriceId);
            $("a.tajer_remove_repeatable").off('click').on('click', TajerPostType.removePricingRow);
            $("a.tajer_remove_repeatable_file_assignment").off('click').on('click', {action: "tajer_delete_file_assignment"}, TajerPostType.removeFilesRow);
            $('select').trigger("chosen:updated");
        },
        //specialRefresh:function(){
        //        $("a.tajer_remove_repeatable_bundle_product").on('click', TajerPostType.removeFilesRow);
        //        $("a.tajer_remove_repeatable").off('click').on('click', TajerPostType.removePricingRow);
        //        $("a.tajer_remove_repeatable_file_assignment").off('click').on('click', {action: "tajer_delete_file_assignment"}, TajerPostType.removeFilesRow);
        //        $('select').trigger("chosen:updated");
        //},
        showHideMultipleFiles: function () {
            var self = $('input:checkbox[name="tajer_bundle"]:checked'),
                div = $('.tajer_enable_multiple_files'),
                single = $('.tajer_enable_bundle');
            if (self.length) {
                div.hide();
                single.show();
                //In case of bundle disable unnecessary product features
                //TajerPostType.disableUnnecessaryProductFeatures();
            } else {
                div.show();
                single.hide();
                //In case of bundle enable necessary product features
                //TajerPostType.enableNecessaryProductFeatures();
            }

            $("#tajer_bundle").trigger('showHideBundleProducts');
        },
        //disableUnnecessaryProductFeatures: function () {
        //    var pricingTable = $("#multiple_price_table");
        //    var rowCount = pricingTable.find("tr").length;
        //    if (rowCount > 2) {
        //        pricingTable.find("tr:gt(1)").remove().end().find("input[type=radio]").prop("checked", true);
        //    }
        //
        //    var rowIndex = pricingTable.find("tr.tajer_repeatable_row").attr('data-index');
        //    $('.tajer_multiple_files_prices option[value!="' + rowIndex + '"]').remove();
        //    $('select').trigger("chosen:updated");
        //
        //    $(".tajer-add-price").hide();
        //},
        //enableNecessaryProductFeatures: function () {
        //    $(".tajer-add-price").show();
        //},
        addPricingRow: function (e) {
            e.preventDefault();
            var clone = $('div.tajer_enable_multiple_price').find("tbody tr:first").clone(),

                maxIndex = TajerPostType.getMaxRowIndex("table#multiple_price_table");
            //console.log(clone);
            clone.attr('data-index', maxIndex).find("input").each(function () {
                var self = $(this);
                clone.find('span.tajer_price_id').text(maxIndex);

                if ((self.attr("type") != 'radio') && (self.attr("type") != 'hidden')) {
                    if (self.attr("name").indexOf("price") >= 0) {
                        self.attr({
                            'name': TajerPostType.newInputName(self, maxIndex),
                            'value': ''
                        });
                    } else if ((self.attr("name").indexOf("name") >= 0)) {
                        //console.log('hi');
                        self.attr({
                            'name': TajerPostType.newInputName(self, maxIndex),
                            'value': ''
                        });
                    }
                } else if (self.attr("type") == 'radio') {
                    self.attr('value', maxIndex)
                } else if (self.attr("type") == 'hidden') {
                    self.attr({
                        'name': TajerPostType.newInputName(self, maxIndex),
                        'value': ''
                    });
                }
            }).end().appendTo("table#multiple_price_table");
            TajerPostType.pricesAssignment(maxIndex);
            TajerPostType.refresh();
            $("a.tajer_product_prices_options").on('click', TajerPostType.changePriceOptions);
        },
        pricesAssignment: function (id, remove, newId) {
            if (newId) {
                $(".tajer_multiple_files_prices option[value='" + id + "']").val(newId).text(newId);
            } else {
                //either add or remove
                remove = remove || false;
                if (!remove) {
                    $(".tajer_multiple_files_prices").append('<option value="' + id + '">' + id + '</option>');
                } else {
                    $(".tajer_multiple_files_prices option[value='" + id + "']").remove();
                }
            }

        },

        getMaxRowIndex: function (table) {
            var highest = 1,
                clone = $(table).find('tr');
            clone.each(function () {
                var current = $(this).data('index');
                if (parseInt(current) > highest) {
                    highest = current;
                }
            });
            return highest += 1;
        },
        getPriceIds: function () {
            var ids = [];
            $("#multiple_price_table").find("tr.tajer_repeatable_row").each(function () {
                ids.push(parseInt($(this).attr('data-index')));
            });

            return ids;
        },
        sortPrices: function () {
            $("#multiple_price_table tbody").sortable({
                handle: '.tajer_draghandle',
                items: '.tajer_repeatable_row',
                placeholder: "ui-state-highlight",
                helper: function (e, tr) {
                    var $originals = tr.children();
                    var $helper = tr.clone();
                    $helper.children().each(function (index) {
                        // Set helper cell sizes to match the original sizes
                        $(this).width($originals.eq(index).width());
                    });
                    return $helper;
                },
                opacity: 0.6,
                cursor: 'move',
                axis: 'y'
            });
        },
        showHideRolesMenu: function () {
            //Hide and show roles multi select menu
            var self = $('input:radio[name="tajer-capabilities-price-option"]:checked');
            if (self.val() === 'free') {
                self.closest('fieldset').find('tr#tajer-roles-fieldset').show();
            } else {
                self.closest('fieldset').find('tr#tajer-roles-fieldset').hide();
            }
        },
        removePricingRow: function (e) {
            e.preventDefault();
            //console.log('yes');
            var self = $(this);
            //var rowCount = $("#multiple_price_table tr").length;
            var rowCount = self.closest("table").find('tr').length;

            //to get the id
            var id = self.closest("tr").find("span.tajer_price_id").text();
            if (rowCount > 2) {
                if (confirm('Are you sure?')) {
                    self.closest('tr').remove();
                    TajerPostType.pricesAssignment(id, true);
                    TajerPostType.refresh();
                }
            }
        },
        addBundleProduct: function (e) {
            e.preventDefault();
            var table = $("table#tajer_bundle_table"),
                clone = table.find("tr:first").next().clone(),
                maxIndex = TajerPostType.getMaxRowIndex("table#tajer_bundle_table");
            clone.attr('data-index', maxIndex).find("input").each(function () {
                var self = $(this);
                if (self.attr("name") && self.attr("value")) {
                    self.attr({
                        'name': TajerPostType.newInputName(self, maxIndex),
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
                        'name': TajerPostType.newInputName(self, maxIndex)
                    });
                }
            });
            clone.find("div.chosen-container").remove().end().find("select").show().end().appendTo("table#tajer_bundle_table");
            $("a.tajer_remove_repeatable_bundle_product").on('click', TajerPostType.removeFilesRow);
            $('.tajer_bundled_products_product').on('change', TajerPostType.getProductSubIds);
            //apply chosen again chosen
            TajerPostType.Chosen();
        },
        addFile: function (e) {
            e.preventDefault();
            var clone = $('.tajer_enable_multiple_files').find("table tr:first").next().clone(),
                maxIndex = TajerPostType.getMaxRowIndex("table#multiple_files_table");

            clone.attr('data-index', maxIndex).find("input").each(function () {

                var self = $(this);
                if (self.attr("name")) {

                    self.attr({
                        'name': TajerPostType.newInputName(self, maxIndex),
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
                        'name': TajerPostType.newInputName(self, maxIndex)
                    });
                }
            });
            clone.find("div.chosen-container").remove().end().find("select").show().end().appendTo("table#multiple_files_table");
            //apply chosen again chosen
            TajerPostType.Chosen();
            $(".tajer-upload-file").off('click').on('click', TajerPostType.openMediaLibrary);
            $("a.tajer_remove_repeatable_file_assignment").on('click', {action: "tajer_delete_file_assignment"}, TajerPostType.removeFilesRow);

            //$("#drag-and-drop-zone").dialog("open");
        },
        recurring: function () {
            var self = $('#is_recurring_yes:checked'),
                ddd = $('#is_recurring_no:checked'),
                div = $('.tajer_enable_recurring');
            TajerPostType.showHideDiv(self, div, ddd);
        },
        enableUpgrade: function () {
            var self = $('#is_upgrade_yes:checked'),
                ddd = $('#is_upgrade_no:checked'),
                div = $('.tajer_enable_upgrade');
            TajerPostType.showHideDiv(self, div, ddd);
        },

        priceOptionsDialogIntinator: function () {

            var priceOptionsDialog = $("#price-options-dialog");

            //initialize the dialog box
            priceOptionsDialog.dialog({
                autoOpen: false,
                height: 400,
                width: 600,
                modal: true,
                buttons: {
                    "Save": function () {
                        priceOptionsDialog.trigger("tajerSavePriceOptionsDialog", [priceOptionsDialog]);
                        TajerPostType.savePriceOptions(priceOptionsDialog);
                    },
                    Cancel: function () {
                        priceOptionsDialog.dialog("close");
                    }
                }
            });
        },
        savePriceOptions: function (priceOptionsDialog) {
            //var download_link_expiration = $("#tajer-download-link-expiration-price-option").val(),
            var price_expiration = $("#tajer-price-expiration-date").val(),
                file_download_limit = $("#tajer-file-download-limit-price-option").val(),
                rolesSelectTag = $("#tajer-role-price-option"),
                capabilities = $('input:radio[name="tajer-capabilities-price-option"]:checked').val(),
                role = rolesSelectTag.val() ? rolesSelectTag.val().join(",") : "",
                forPriceOption = $("input:hidden[name='tajer_price_options_for']").val();

            var container = $("table#multiple_price_table").find('tr.tajer_repeatable_row[data-index="' + forPriceOption + '"]');

            container.find("input[name*='roles']").val(role);
            //container.find("input[name*='download_link_expiration']").val(download_link_expiration);
            container.find("input[name*='price_expiration']").val(price_expiration);
            container.find("input[name*='file_download_limit']").val(file_download_limit);
            container.find("input[name*='capabilities']").val(capabilities);
            priceOptionsDialog.dialog("close");
        },
        Chosen: function () {
            //jQuery chosen
            $('select:not(.tajer_recurring_recurrence_w)').chosen({
                width: "100%"
            });
            $('.tajer_recurring_recurrence_w').chosen({//todo Mohammed if the above selector still 100% in width then this behaviour should be changed
                width: "100%"
            });
        }
    };
    TajerPostType.init();
});