/**
 * Discount entry for a discount select
 *
 * @module package/quiqqer/discount/bin/controls/SelectItem
 * @author www.pcsg.de (Henning Leutz)
 */
define('package/quiqqer/discount/bin/controls/SelectItem', [

    'qui/QUI',
    'qui/controls/elements/SelectItem',
    'package/quiqqer/discount/bin/classes/Handler',
    'Locale'

], function (QUI, QUISelectItem, Handler, QUILocale) {
    "use strict";

    var Discounts = new Handler();

    return new Class({
        Extends: QUISelectItem,
        Type   : 'package/quiqqer/discount/bin/controls/SelectItem',

        Binds: [
            'refresh'
        ],

        initialize: function (options) {
            this.parent(options);
            this.setAttribute('icon', 'fa fa-percent');
        },

        /**
         * event : on inject
         */
        refresh: function () {
            var self = this;

            this.$Text.set({
                html: '<span class="fa fa-spinner fa-spin"></span>'
            });

            Discounts.getChild(this.getAttribute('id')).then(function (data) {
                var locale = QUILocale.get(
                    'quiqqer/discount',
                    'discount.' + data.id + '.title'
                );

                self.$Text.set({
                    html: '#' + data.id + ' - ' + locale
                });

            }).catch(function () {
                self.$Icon.removeClass('fa-percent');
                self.$Icon.addClass('fa-bolt');
                self.$Text.set('html', '...');
            });
        }
    });
});
