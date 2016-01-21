/**
 * Discount handler
 * Create and edit discounts
 *
 * @author www.pcsg.de (Henning Leutz)
 *
 * @require qui/QUI
 * @require qui/controls/desktop/Panel
 * @require qui/controls/buttons/Button
 * @require qui/controls/windows/Confirm
 * @require controls/grid/Grid
 * @require Locale
 * @require package/quiqqer/discount/bin/classes/Handler
 */
define('package/quiqqer/discount/bin/controls/Discounts', [

    'qui/QUI',
    'qui/controls/desktop/Panel',
    'qui/controls/buttons/Button',
    'qui/controls/windows/Confirm',
    'controls/grid/Grid',
    'Locale',
    'package/quiqqer/discount/bin/classes/Handler'

], function (QUI, QUIPanel, QUIButton, QUIConfirm, Grid, QUILocale, Handler) {
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
            '$onResize'
        ],

        initialize: function (options) {
            this.parent(options);

            this.$Grid = null;

            this.setAttributes({
                'title': QUILocale.get(lg, 'menu.erp.discount.panel.title')
            });

            this.addEvents({
                onCreate: this.$onCreate,
                onResize: this.$onResize
            });
        },

        /**
         * event : on create
         */
        $onCreate: function () {

            // buttons
            this.addButton({
                name     : 'add',
                text     : QUILocale.get('quiqqer/system', 'add'),
                textimage: 'icon-plus fa fa-plus',
                events   : {
                    onClick: this.createChild
                }
            });

            this.addButton({
                name     : 'edit',
                text     : QUILocale.get('quiqqer/system', 'edit'),
                textimage: 'icon-edit fa fa-edit',
                disabled : true,
                events   : {
                    onClick: this.createChild
                }
            });

            this.addButton({
                type: 'seperator'
            });

            this.addButton({
                name     : 'delete',
                text     : QUILocale.get('quiqqer/system', 'delete'),
                textimage: 'icon-trash fa fa-trash',
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
            var self = this;

            var Container = new Element('div').inject(
                this.getContent()
            );

            this.$Grid = new Grid(Container, {
                multipleSelection: true,
                columnModel      : [{
                    header   : QUILocale.get('quiqqer/system', 'id'),
                    dataIndex: 'id',
                    dataType : 'number',
                    width    : 60
                }, {
                    header   : QUILocale.get(lg, 'discount.grid.discount.title'),
                    dataIndex: 'title',
                    dataType : 'string',
                    width    : 200
                }, {
                    header   : QUILocale.get(lg, 'discount.grid.discount.areas'),
                    dataIndex: 'areas',
                    dataType : 'string',
                    width    : 300
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
         */
        refresh: function () {

            var self = this;

            this.Loader.show();
            this.getButtons('delete').disable();
            this.getButtons('edit').disable();

            Discounts.getList().then(function (data) {
                var dataEntry,
                    gridData = [];

                for (var i = 0, len = data.length; i < len; i++) {
                    dataEntry = data[i];

                }

                self.$Grid.setData({
                    data: gridData
                });

                self.Loader.hide();
            });
        }
    });
});

