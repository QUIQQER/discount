/**
 * Discount edit control
 *
 * @author www.pcsg.de (Henning Leutz)
 *
 * @require qui/QUI
 * @require qui/controls/Control
 * @require qui/controls/buttons/Button
 * @require Locale
 * @require package/quiqqer/discount/bin/classes/Handler
 * @require package/quiqqer/translator/bin/controls/VariableTranslation
 * @require text!package/quiqqer/discount/bin/controls/DiscountEdit.html
 *
 * @event onLoaded
 */
define('package/quiqqer/discount/bin/controls/DiscountEdit', [

    'qui/QUI',
    'qui/controls/Control',
    'qui/controls/buttons/Button',
    'qui/utils/Form',
    'Locale',
    'package/quiqqer/discount/bin/classes/Handler',
    'package/quiqqer/translator/bin/controls/VariableTranslation',

    'text!package/quiqqer/discount/bin/controls/DiscountEdit.html',
    'css!package/quiqqer/discount/bin/controls/DiscountEdit.css'

], function (QUI, QUIControl, QUIButton, QUIFormUtils, QUILocale,
             Handler, Translation, template) {
    "use strict";

    var lg = 'quiqqer/discount';

    var Discounts = new Handler();

    return new Class({

        Extends: QUIControl,
        Type   : 'package/quiqqer/discount/bin/controls/DiscountEdit',

        Binds: [
            '$onInject'
        ],

        options: {
            discountId: false
        },

        initialize: function (options) {
            this.parent(options);

            this.addEvents({
                onInject: this.$onInject
            });
        },

        /**
         * Return the DOMNode Element
         *
         * @returns {HTMLDivElement}
         */
        create: function () {
            this.$Elm = this.parent();
            this.$Elm.set('html', template);
            this.$Elm.set('class', 'discount-edit');

            return this.$Elm;
        },

        /**
         * event : on inject
         */
        $onInject: function () {
            var self = this;

            require([
                'utils/Controls'
            ], function (Utils) {
                QUI.parse(self.$Elm).then(
                    Utils.parse(self.$Elm)
                ).then(function () {
                    return Discounts.getChild(self.getAttribute('discountId'));
                }).then(function (data) {

                    var Elm  = self.getElm(),
                        Form = Elm.getElement('form');

                    new Translation({
                        'group': 'quiqqer/discount',
                        'var'  : 'discount.' + data.id + '.title'
                    }).inject(
                        Elm.getElement('.discount-title')
                    );

                    Elm.getElement('.field-id').set(
                        'html',
                        '#' + self.getAttribute('discountId')
                    );
console.log(data);
                    QUIFormUtils.setDataToForm(data, Form);

                    self.fireEvent('loaded');
                });
            });
        },

        /**
         * Save the Discount
         *
         * @return {Promise}
         */
        save: function () {
            var self = this;
            return new Promise(function (resolve, reject) {
                var Elm  = self.getElm(),
                    Form = Elm.getElement('form');

                var data = QUIFormUtils.getFormData(Form);
console.warn(data);
                Discounts.update(
                    self.getAttribute('discountId'),
                    data
                ).then(function () {
                    resolve();
                }).catch(reject);
            });
        }
    });
});
