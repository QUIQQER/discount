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
    'Mustache',
    'package/quiqqer/discount/bin/classes/Handler',
    'package/quiqqer/translator/bin/controls/Update',

    'text!package/quiqqer/discount/bin/controls/DiscountEdit.html',
    'css!package/quiqqer/discount/bin/controls/DiscountEdit.css'

], function (QUI, QUIControl, QUIButton, QUIFormUtils, QUILocale,
             Mustache, Handler, Translation, template) {
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
            this.$Elm.set('class', 'discount-edit');

            this.$Elm.set('html', Mustache.render(template, {
                header              : QUILocale.get(lg, 'control.edit.template.title'),
                id                  : QUILocale.get(lg, 'control.edit.template.id'),
                title               : QUILocale.get(lg, 'control.edit.template.title'),
                discount            : QUILocale.get(lg, 'control.edit.template.discount'),
                usageHeader         : QUILocale.get(lg, 'control.edit.template.usage'),
                usageFrom           : QUILocale.get(lg, 'control.edit.template.usage.from'),
                usageTo             : QUILocale.get(lg, 'control.edit.template.usage.to'),
                usageAmountOf       : QUILocale.get(lg, 'control.edit.template.shopping.amount.of'),
                usageAmountTo       : QUILocale.get(lg, 'control.edit.template.shopping.amount.to'),
                usageValueOf        : QUILocale.get(lg, 'control.edit.template.purchase.value.of'),
                usageValueTo        : QUILocale.get(lg, 'control.edit.template.purchase.value.to'),
                usageAssignment     : QUILocale.get(lg, 'control.edit.template.assignment'),
                usageAssignmentAreas: QUILocale.get(lg, 'control.edit.template.areas'),

                calculationBasis         : QUILocale.get(lg, 'control.edit.template.calculationBasis'),
                calculationBasisNetto    : QUILocale.get(lg, 'control.edit.template.calculationBasis.netto'),
                calculationBasisCalcPrice: QUILocale.get(lg, 'control.edit.template.calculationBasis.calculationBasisCalcPrice'),
                calculationPriority      : QUILocale.get(lg, 'control.edit.template.calculationPriority'),

                usageAssignmentProduct : QUILocale.get(lg, 'control.edit.template.assignment.product'),
                usageAssignmentCategory: QUILocale.get(lg, 'control.edit.template.assignment.category'),
                usageAssignmentUser    : QUILocale.get(lg, 'control.edit.template.assignment.user'),
                usageAssignmentCombine : QUILocale.get(lg, 'control.edit.template.assignment.combine')
            }));


            this.$Translate = null;

            this.$Elm.setStyles({
                overflow: 'hidden',
                opacity : 0
            });

            return this.$Elm;
        },

        /**
         * event : on inject
         */
        $onInject: function () {
            var self = this;

            require(['utils/Controls'], function (Utils) {

                var Elm  = self.getElm(),
                    Form = Elm.getElement('form');

                Discounts.getChild(
                    self.getAttribute('discountId')
                ).then(function (data) {

                    data.discount_type = parseInt(data.discount_type);
                    data.priority      = parseInt(data.priority);

                    switch (data.discount_type) {
                        case Discounts.DISCOUNT_TYPE_PERCENT:
                        case Discounts.DISCOUNT_TYPE_CURRENCY:
                            break;

                        default:
                            data.discount_type = Discounts.DISCOUNT_TYPE_PERCENT;
                    }

                    console.log(data);

                    QUIFormUtils.setDataToForm(data, Form);

                    self.$Translate = new Translation({
                        'group': lg,
                        'var'  : 'discount.' + data.id + '.title'
                    }).inject(
                        Elm.getElement('.discount-title')
                    );

                    Elm.getElement('.field-id').set(
                        'html',
                        '#' + self.getAttribute('discountId')
                    );
                }).then(function () {
                    return QUI.parse(self.$Elm);

                }).then(function () {
                    return Utils.parse(self.$Elm);

                }).then(function () {

                    self.$Elm.setStyles({
                        overflow: null
                    });

                    moofx(self.$Elm).animate({
                        opacity: 1
                    }, {
                        callback: function () {
                            self.fireEvent('loaded');
                        }
                    });
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

                self.$Translate.save().then(function () {
                    return Discounts.update(
                        self.getAttribute('discountId'),
                        data
                    );
                }).then(function () {
                    resolve();

                }).catch(reject);

            });
        }
    });
});
