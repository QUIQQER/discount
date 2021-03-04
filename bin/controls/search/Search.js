/**
 *
 * @module package/quiqqer/discount/bin/controls/search/Search
 * @author www.pcsg.de (Henning Leutz)
 *
 * @event onLoaded
 */
define('package/quiqqer/discount/bin/controls/search/Search', [

    'qui/QUI',
    'qui/controls/Control',
    'qui/controls/buttons/Button',
    'qui/utils/Form',
    'package/quiqqer/discount/bin/classes/Handler',
    'Locale',
    'Mustache',

    'text!package/quiqqer/discount/bin/controls/search/Search.html'

], function (QUI, QUIControl, QUIButton, QUIFormUtils, Handler, QUILocale, Mustache, template) {
    "use strict";

    var Discounts = new Handler();
    var lg        = 'quiqqer/discount';

    return new Class({
        Extends: QUIControl,
        Type   : 'package/quiqqer/discount/bin/controls/search/Search',

        Binds: [
            '$onInject'
        ],

        options: {},

        initialize: function (options) {
            this.parent(options);

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

            Elm.set('html', Mustache.render(template, {
                header                      : QUILocale.get(lg, 'control.search.title'),
                id                          : QUILocale.get(lg, 'control.edit.template.id'),
                title                       : QUILocale.get(lg, 'control.edit.template.title'),
                discount                    : QUILocale.get(lg, 'control.edit.template.discount'),
                usageHeader                 : QUILocale.get(lg, 'control.edit.template.usage'),
                usageVat                    : QUILocale.get(lg, 'control.edit.template.vat'),
                usageVatDesc                : QUILocale.get(lg, 'control.edit.template.vatDesc'),
                usageFrom                   : QUILocale.get(lg, 'control.edit.template.usage.from'),
                usageTo                     : QUILocale.get(lg, 'control.edit.template.usage.to'),
                usageAmountOf               : QUILocale.get(lg, 'control.edit.template.shopping.amount.of'),
                usageAmountTo               : QUILocale.get(lg, 'control.edit.template.shopping.amount.to'),
                usageValueOf                : QUILocale.get(lg, 'control.edit.template.purchase.value.of'),
                usageValueTo                : QUILocale.get(lg, 'control.edit.template.purchase.value.to'),
                usageAssignment             : QUILocale.get(lg, 'control.edit.template.assignment'),
                usageAssignmentAreas        : QUILocale.get(lg, 'control.edit.template.areas'),
                usageLastSumDiscount        : QUILocale.get(lg, 'control.edit.template.usageLastSumDiscount'),
                usageLastSumDiscountDesc    : QUILocale.get(lg, 'control.edit.template.usageLastSumDiscountDesc'),
                usageLastProductDiscount    : QUILocale.get(lg, 'control.edit.template.usageLastProductDiscount'),
                usageLastProductDiscountDesc: QUILocale.get(lg, 'control.edit.template.usageLastProductDiscountDesc')
            }));

            Elm.setStyles({
                'float': 'left',
                'width': '100%'
            });

            return Elm;
        },

        /**
         * event : on inject
         */
        $onInject: function () {
            this.fireEvent('loaded');
        },

        /**
         * Execute a search
         *
         * @return {Promise}
         */
        search: function () {
            var Form = this.getElm().getElement('form');

            var data = Object.filter(QUIFormUtils.getFormData(Form), function (value) {
                return value !== '';
            });

            return new Promise(function (resolve, reject) {
                Discounts.search(data).then(resolve).catch(reject);
            });
        }
    });
});
