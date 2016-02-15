/**
 * Discount entry for a discount select
 *
 * @module package/quiqqer/discount/bin/controls/SelectItem
 *
 * @require qui/controls/Control
 * @require package/quiqqer/discount/bin/classes/Handler
 * @require css!package/quiqqer/discount/bin/controls/SelectItem.css
 */
define('package/quiqqer/discount/bin/controls/SelectItem', [

    'qui/controls/Control',
    'package/quiqqer/discount/bin/classes/Handler',
    'Locale',

    'css!package/quiqqer/discount/bin/controls/SelectItem.css'

], function (QUIControl, Handler, QUILocale) {
    "use strict";

    var Discounts = new Handler();

    return new Class({
        Extends: QUIControl,
        Type   : 'package/quiqqer/discount/bin/controls/SelectItem',

        Binds: [
            '$onInject'
        ],

        options: {
            id: false
        },

        initialize: function (options) {
            this.parent(options);

            this.$Icon    = null;
            this.$Text    = null;
            this.$Destroy = null;

            this.addEvents({
                onInject: this.$onInject
            });
        },

        /**
         * Return the DOMNode Element
         *
         * @returns {HTMLElement}
         */
        create: function () {
            var self = this,
                Elm  = this.parent();

            Elm.set({
                'class': 'quiqqer-discount-display smooth',
                html   : '<span class="quiqqer-discount-display-icon fa fa-percent"></span>' +
                         '<span class="quiqqer-discount-display-text">&nbsp;</span>' +
                         '<span class="quiqqer-discount-display-destroy fa fa-remove"></span>'
            });

            this.$Icon    = Elm.getElement('.quiqqer-discount-display-icon');
            this.$Text    = Elm.getElement('.quiqqer-discount-display-text');
            this.$Destroy = Elm.getElement('.quiqqer-discount-display-destroy');

            this.$Destroy.addEvent('click', function () {
                self.destroy();
            });

            return Elm;
        },

        /**
         * event : on inject
         */
        $onInject: function () {
            var self = this;

            this.$Text.set({
                html: '<span class="fa fa-spinner fa-spin"></span>'
            });

            Discounts.getChild(
                this.getAttribute('id')
            ).then(function (data) {

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
