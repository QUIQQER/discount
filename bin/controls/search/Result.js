/**
 *
 * @module package/quiqqer/discount/bin/controls/search/Search
 * @author www.pcsg.de (Henning Leutz)
 *
 * @event onLoaded
 * @event onDblClick [self]
 */
define('package/quiqqer/discount/bin/controls/search/Result', [

    'qui/QUI',
    'qui/controls/Control',
    'qui/controls/buttons/Button',
    'controls/grid/Grid',
    'Locale'

], function (QUI, QUIControl, QUIButton, Grid, QUILocale) {
    "use strict";

    var lg = 'quiqqer/discount';

    return new Class({
        Extends: QUIControl,
        Type   : 'package/quiqqer/discount/bin/controls/search/Result',

        Binds: [
            '$onInject'
        ],

        options: {
            multipleSelection: true
        },

        initialize: function (options) {
            this.parent(options);

            this.$Grid = null;

            this.addEvents({
                onInject: this.$onInject
            });
        },

        /**
         * Return the DOMNode Element
         * @returns {HTMLDivElement}
         */
        create: function () {
            var Elm = this.parent();

            Elm.set('html', '');

            Elm.setStyles({
                'float' : 'left',
                'height': '100%',
                'width' : '100%'
            });

            var Container = new Element('div').inject(Elm);

            this.$Grid = new Grid(Container, {
                multipleSelection: this.getAttribute('multipleSelection'),
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
                    header   : QUILocale.get('quiqqer/system', 'title'),
                    dataIndex: 'text',
                    dataType : 'string',
                    width    : 200
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

            this.$Grid.addEvent('onDblClick', function () {
                this.fireEvent('dblClick', [this]);
            }.bind(this));

            return Elm;
        },

        /**
         * event : on inject
         */
        $onInject: function () {
            this.fireEvent('loaded');
        },

        /**
         * Set data to the grid
         *
         * @param {Object} data - grid data
         */
        setData: function (data) {
            if (!this.$Grid) {
                return;
            }

            this.$Grid.setData(data);
        },

        /**
         * Return the selected data
         *
         * @returns {Array}
         */
        getSelected: function () {
            if (!this.$Grid) {
                return [];
            }

            return this.$Grid.getSelectedData();
        },

        /**
         * Resize the control
         *
         * @return {Promise}
         */
        resize: function () {
            var size = this.getElm().getSize();

            this.$Grid.setWidth(size.x);
            this.$Grid.setHeight(size.y);

            return this.$Grid.resize();
        }
    });
});
