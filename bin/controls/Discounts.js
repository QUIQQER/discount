/**
 * Discount handler
 * Create and edit discounts
 *
 * @author www.pcsg.de (Henning Leutz)
 * @module package/quiqqer/discount/bin/controls/Discounts
 */
define('package/quiqqer/discount/bin/controls/Discounts', [

    'qui/QUI',
    'qui/controls/desktop/Panel',
    'qui/controls/buttons/Button',
    'qui/controls/windows/Confirm',
    'qui/controls/windows/Prompt',
    'controls/grid/Grid',
    'Locale',
    'package/quiqqer/discount/bin/classes/Handler'

], function (QUI, QUIPanel, QUIButton, QUIConfirm, QUIPrompt, Grid, QUILocale, Handler) {
    "use strict";

    var lg = 'quiqqer/discount';

    var Discounts = new Handler();

    return new Class({

        Extends: QUIPanel,
        Type   : 'package/quiqqer/discount/bin/controls/Discounts',

        Binds: [
            'createChild',
            'editChild',
            'deleteChild',
            'deleteChildren',
            'refresh',
            '$onCreate',
            '$onResize',
            '$toggleStatus',
            '$setTaxEntryButtonStatus'
        ],

        initialize: function (options) {
            this.parent(options);

            this.$Grid = null;

            this.addEvents({
                onCreate: this.$onCreate,
                onResize: this.$onResize
            });
        },

        /**
         * event : on create
         */
        $onCreate: function () {
            var self = this;

            this.setAttributes({
                title: QUILocale.get(lg, 'discount.panel.title'),
                icon : 'fa fa-cart-arrow-down'
            });

            // buttons
            this.addButton({
                name     : 'add',
                text     : QUILocale.get('quiqqer/system', 'add'),
                textimage: 'fa fa-plus',
                events   : {
                    onClick: this.createChild
                }
            });

            this.addButton({
                name     : 'edit',
                text     : QUILocale.get('quiqqer/system', 'edit'),
                textimage: 'fa fa-edit',
                disabled : true,
                events   : {
                    onClick: function () {
                        self.editChild(
                            self.$Grid.getSelectedData()[0].id
                        );
                    }
                }
            });

            this.addButton({
                type: 'separator'
            });

            this.addButton({
                name     : 'delete',
                text     : QUILocale.get('quiqqer/system', 'delete'),
                textimage: 'fa fa-trash',
                disabled : true,
                events   : {
                    onClick: function () {

                        var selected = this.$Grid.getSelectedData();

                        var ids = selected.map(function (data) {
                            return data.id;
                        });

                        this.deleteChildren(ids);
                    }.bind(this)
                }
            });

            // Grid
            var Container = new Element('div').inject(
                this.getContent()
            );

            this.$Grid = new Grid(Container, {
                multipleSelection: true,
                pagination       : true,
                columnModel      : [{
                    header   : QUILocale.get('quiqqer/system', 'id'),
                    dataIndex: 'id',
                    dataType : 'number',
                    width    : 60
                }, {
                    header   : QUILocale.get('quiqqer/system', 'status'),
                    dataIndex: 'status',
                    dataType : 'button',
                    width    : 60
                }, {
                    header   : QUILocale.get(lg, 'discount.grid.discount'),
                    dataIndex: 'discount',
                    dataType : 'number',
                    width    : 100
                }, {
                    header   : QUILocale.get(lg, 'discount.grid.discount_type'),
                    dataIndex: 'discountTypeText',
                    dataType : 'string',
                    width    : 30
                }, {
                    header   : QUILocale.get('quiqqer/system', 'title'),
                    dataIndex: 'text',
                    dataType : 'string',
                    width    : 200
                }, {
                    header   : QUILocale.get(lg, 'discount.grid.priority'),
                    dataIndex: 'priority',
                    dataType : 'number',
                    width    : 30
                }, {
                    header   : QUILocale.get(lg, 'discount.grid.date_from'),
                    dataIndex: 'date_from',
                    dataType : 'date',
                    width    : 200
                }, {
                    header   : QUILocale.get(lg, 'discount.grid.date_until'),
                    dataIndex: 'date_until',
                    dataType : 'date',
                    width    : 200
                }, {
                    header   : QUILocale.get(lg, 'discount.grid.purchase_quantity'),
                    dataIndex: 'purchase_quantity',
                    dataType : 'number',
                    width    : 100
                }, {
                    header   : QUILocale.get(lg, 'discount.grid.purchase_value'),
                    dataIndex: 'purchase_value',
                    dataType : 'number',
                    width    : 100
                }, {
                    header   : QUILocale.get(lg, 'discount.grid.usage_type'),
                    dataIndex: 'usageTypeText',
                    dataType : 'string',
                    width    : 100
                }, {
                    header   : QUILocale.get(lg, 'discount.grid.areas'),
                    dataIndex: 'areas',
                    dataType : 'string',
                    width    : 200
                }, {
                    header   : QUILocale.get(lg, 'discount.grid.articles'),
                    dataIndex: 'articles',
                    dataType : 'string',
                    width    : 200
                }, {
                    header   : QUILocale.get(lg, 'discount.grid.categories'),
                    dataIndex: 'categories',
                    dataType : 'string',
                    width    : 200
                }, {
                    header   : QUILocale.get(lg, 'discount.grid.user_groups'),
                    dataIndex: 'user_groups',
                    dataType : 'string',
                    width    : 200
                }, {
                    header   : QUILocale.get(lg, 'discount.grid.combined'),
                    dataIndex: 'combined',
                    dataType : 'string',
                    width    : 100
                }]
            });

            this.$Grid.addEvents({
                onRefresh : this.refresh,
                onDblClick: function (event) {
                    self.editChild(
                        self.$Grid.getDataByRow(event.row).id
                    );
                },
                onClick   : function () {
                    var selected = self.$Grid.getSelectedData();

                    if (selected.length) {
                        self.getButtons('delete').enable();
                        self.getButtons('edit').enable();
                    } else {
                        self.getButtons('delete').disable();
                        self.getButtons('edit').disable();
                    }
                }
            });

            this.$Grid.refresh();
        },

        /**
         * event : on resize
         */
        $onResize: function () {
            if (!this.$Grid) {
                return;
            }

            var Body = this.getContent();

            if (!Body) {
                return;
            }


            var size = Body.getSize();

            this.$Grid.setHeight(size.y - 40);
            this.$Grid.setWidth(size.x - 40);
        },

        /**
         * refresh the display
         *
         * @return {Promise}
         */
        refresh: function () {
            var self = this;

            this.Loader.show();
            this.getButtons('delete').disable();
            this.getButtons('edit').disable();
            this.parent();

            return Discounts.getList({
                perPage: this.$Grid.options.perPage,
                page   : this.$Grid.options.page
            }).then(function (result) {
                var i, len, active, entry, discountType, usage_type;

                for (i = 0, len = result.data.length; i < len; i++) {
                    entry  = result.data[i];
                    active = parseInt(result.data[i].active);

                    discountType = parseInt(result.data[i].discount_type);
                    usage_type   = parseInt(result.data[i].usage_type);

                    if (!usage_type) {
                        usage_type = 0;
                    }

                    if (active) {
                        entry.status = {
                            icon      : 'fa fa-check',
                            discountId: entry.id,
                            title     : QUILocale.get(lg, 'discount.grid.status.active'),
                            styles    : {
                                lineHeight: 16
                            },
                            events    : {
                                onClick: self.$toggleStatus
                            }
                        };
                    } else {
                        entry.status = {
                            icon      : 'fa fa-remove',
                            discountId: entry.id,
                            title     : QUILocale.get(lg, 'discount.grid.status.inactive'),
                            styles    : {
                                lineHeight: 16
                            },
                            events    : {
                                onClick: self.$toggleStatus
                            }
                        };
                    }

                    entry.discountTypeText = '%';

                    if (usage_type === 0) {
                        entry.usageTypeText = QUILocale.get(lg, 'discount.usage_type.manuel');
                    } else {
                        entry.usageTypeText = QUILocale.get(lg, 'discount.usage_type.automatic');
                    }

                    if (parseInt(entry.discount_type) === Discounts.DISCOUNT_TYPE_CURRENCY) {
                        entry.discountTypeText = '€';
                    }

                    result.data[i] = entry;
                }

                self.$Grid.setData(result);
                self.Loader.hide();
            });
        },

        /**
         * Create the child and open the edit
         *
         * @return {Promise}
         */
        createChild: function () {
            var self = this;

            // this.Loader.show();
            new QUIPrompt({
                title      : QUILocale.get(lg, 'discount.window.create.title'),
                titleicon  : 'fa fa-plus',
                icon       : false,
                information: QUILocale.get(lg, 'discount.window.create.information'),
                autoclose  : false,
                maxHeight  : 300,
                maxWidth   : 450,
                events     : {
                    onSubmit: function (value, Win) {
                        Win.Loader.show();

                        Discounts.createChild({
                            title: value
                        }).then(function (discountId) {
                            require([
                                'package/quiqqer/translator/bin/classes/Translator'
                            ], function (Translator) {
                                new Translator().publish(lg).then(function () {
                                    return self.refresh();
                                }).then(function () {
                                    Win.close();
                                    self.editChild(discountId);
                                });
                            });
                        }, function () {
                            // reject
                            Win.Loader.hide();
                        });
                    }
                }
            }).open();
        },

        /**
         * Open the discount edit
         *
         * @param {Number} discountId
         */
        editChild: function (discountId) {

            var self = this;

            self.Loader.show();

            return new Promise(function (resolve) {
                self.createSheet({
                    title : QUILocale.get(lg, 'discount.edit.sheet.title', {
                        id: discountId
                    }),
                    icon  : 'fa fa-cart-arrow-down',
                    events: {
                        onShow: function (Sheet) {

                            Sheet.getContent().set({
                                styles: {
                                    padding: 20
                                }
                            });

                            require([
                                'package/quiqqer/discount/bin/controls/DiscountEdit'
                            ], function (DiscountEdit) {

                                var Discount = new DiscountEdit({
                                    discountId: discountId,
                                    events    : {
                                        onLoaded: function () {
                                            self.Loader.hide();
                                            resolve();
                                        }
                                    }
                                }).inject(Sheet.getContent());

                                Sheet.addButton({
                                    text     : QUILocale.get('quiqqer/system', 'save'),
                                    textimage: 'fa fa-save',
                                    events   : {
                                        click: function () {
                                            self.Loader.show();

                                            Discount.save().then(function () {
                                                self.refresh();
                                            }).catch(function () {
                                                self.Loader.hide();
                                            });
                                        }
                                    }
                                });
                            });

                        }
                    }
                }).show();
            });
        },

        /**
         * Opens the delete dialog - delete
         *
         * @param {Array} discountIds
         */
        deleteChildren: function (discountIds) {
            var self = this,
                str  = discountIds.join(',');

            new QUIConfirm({
                title      : QUILocale.get(lg, 'discount.window.delete.title'),
                text       : QUILocale.get(lg, 'discount.window.delete.text', {
                    ids: str
                }),
                information: QUILocale.get(lg, 'discount.window.delete.information', {
                    ids: str
                }),
                icon       : 'fa fa-trash',
                texticon   : 'fa fa-trash',
                maxHeight  : 300,
                maxWidth   : 450,
                autoclose  : false,
                events     : {
                    onSubmit: function (Win) {
                        Win.Loader.show();
                        Discounts.deleteChildren(discountIds).then(function () {
                            return self.refresh();
                        }).then(function () {
                            Win.close();
                        });
                    }
                }
            }).open();
        },

        /**
         * Toggle the discount status
         *
         * @param {Object} Button - qui/controls/buttons/Button
         */
        $toggleStatus: function (Button) {
            Button.setAttribute(
                'icon',
                'fa fa-spinner fa-spin'
            );

            Discounts.toggleStatus(
                Button.getAttribute('discountId')
            ).then(function (status) {
                this.$setTaxEntryButtonStatus(Button, status);
            }.bind(this));
        },

        /**
         * Change the statusbutton of a discount
         *
         * @param {Object} Button
         * @param {Boolean|Number} status
         */
        $setTaxEntryButtonStatus: function (Button, status) {
            if (status) {
                Button.setAttribute('icon', 'fa fa-check');
                Button.setAttribute('title', QUILocale.get(lg, 'discount.grid.status.active'));
                return;
            }

            Button.setAttribute('icon', 'fa fa-remove');
            Button.setAttribute('title', QUILocale.get(lg, 'discount.grid.status.inactive'));
        }
    });
});
