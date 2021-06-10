/**
 * Discount edit control
 *
 * @module package/quiqqer/discount/bin/controls/DiscountEdit
 * @author www.pcsg.de (Henning Leutz)
 *
 * @event onLoaded
 */
define('package/quiqqer/discount/bin/controls/DiscountEdit', [

    'qui/QUI',
    'qui/controls/Control',
    'qui/controls/buttons/Button',
    'qui/controls/buttons/ButtonSwitch',
    'qui/utils/Form',
    'Locale',
    'Mustache',
    'package/quiqqer/discount/bin/Discounts',
    'package/quiqqer/translator/bin/controls/Update',

    'text!package/quiqqer/discount/bin/controls/DiscountEdit.html',
    'css!package/quiqqer/discount/bin/controls/DiscountEdit.css'

], function (QUI, QUIControl, QUIButton, QUIButtonSwitch, QUIFormUtils, QUILocale,
             Mustache, Discounts, Translation, template) {
    "use strict";

    var lg = 'quiqqer/discount';

    return new Class({

        Extends: QUIControl,
        Type   : 'package/quiqqer/discount/bin/controls/DiscountEdit',

        Binds: [
            '$onInject',
            '$onActiveStatusChange'
        ],

        options: {
            discountId: false
        },

        initialize: function (options) {
            this.parent(options);

            this.$ActiveSwitch = null;

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
                header                      : QUILocale.get(lg, 'control.edit.template.title'),
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
                usageLastProductDiscountDesc: QUILocale.get(lg, 'control.edit.template.usageLastProductDiscountDesc'),

                usageScope      : QUILocale.get(lg, 'control.edit.template.usageScope'),
                usageScopeEvery : QUILocale.get(lg, 'control.edit.template.usageScopeEvery'),
                usageScopeTotal : QUILocale.get(lg, 'control.edit.template.usageScopeTotal'),
                usageScopeUnique: QUILocale.get(lg, 'control.edit.template.usageScopeUnique'),
                scopeDescription: QUILocale.get(lg, 'control.edit.template.usage.description'),

                calculationBasis          : QUILocale.get(lg, 'control.edit.template.calculationBasis'),
                calculationBasisNetto     : QUILocale.get(lg, 'control.edit.template.calculationBasis.netto'),
                calculationBasisCalcPrice : QUILocale.get(lg, 'control.edit.template.calculationBasis.calculationBasisCalcPrice'),
                calculationBasisCalcBrutto: QUILocale.get(lg, 'control.edit.template.calculationBasis.calculationBasisCalcBrutto'),
                calculationPriority       : QUILocale.get(lg, 'control.edit.template.calculationPriority'),

                considerVat           : QUILocale.get(lg, 'control.edit.template.considerVat'),
                considerVatDescription: QUILocale.get(lg, 'control.edit.template.considerVat.description'),
                considerVatAuto       : QUILocale.get(lg, 'control.edit.template.considerVat.auto'),
                considerVatNetto      : QUILocale.get(lg, 'control.edit.template.considerVat.netto'),
                considerVatBrutto     : QUILocale.get(lg, 'control.edit.template.considerVat.brutto'),

                usageAssignmentProduct : QUILocale.get(lg, 'control.edit.template.assignment.product'),
                usageAssignmentCategory: QUILocale.get(lg, 'control.edit.template.assignment.category'),
                usageAssignmentUser    : QUILocale.get(lg, 'control.edit.template.assignment.user'),
                usageAssignmentCombine : QUILocale.get(lg, 'control.edit.template.assignment.combine'),

                usageType           : QUILocale.get(lg, 'discount.usage_type'),
                usageTypeDescription: QUILocale.get(lg, 'discount.usage_type.description'),
                usageTypeManuel     : QUILocale.get(lg, 'discount.usage_type.manuel'),
                usageTypeAutomatic  : QUILocale.get(lg, 'discount.usage_type.automatic')
            }));


            this.$Translate = null;

            this.$Elm.setStyles({
                overflow: 'hidden',
                opacity : 0
            });

            this.$ActiveSwitch = new QUIButtonSwitch({
                status: false,
                text  : QUILocale.get(lg, 'control.edit.btn.active.status_off'),
                events: {
                    onChange: this.$onActiveStatusChange
                }
            }).inject(this.$Elm.getElement('.quiqqer-discount-entry-edit-buttons'));

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

                    data.usage_type    = parseInt(data.usage_type);
                    data.discount_type = parseInt(data.discount_type);
                    data.priority      = parseInt(data.priority);

                    switch (data.discount_type) {
                        case Discounts.DISCOUNT_TYPE_PERCENT:
                        case Discounts.DISCOUNT_TYPE_CURRENCY:
                            break;

                        default:
                            data.discount_type = Discounts.DISCOUNT_TYPE_PERCENT;
                    }

                    switch (data.usage_type) {
                        case Discounts.DISCOUNT_USAGE_TYPE_MANUEL:
                        case Discounts.DISCOUNT_USAGE_TYPE_AUTOMATIC:
                            break;

                        default:
                            data.usage_type = Discounts.DISCOUNT_USAGE_TYPE_MANUEL;
                    }

                    QUIFormUtils.setDataToForm(data, Form);

                    self.$Translate = new Translation({
                        'group'  : lg,
                        'var'    : 'discount.' + data.id + '.title',
                        'package': 'quiqqer/discount'
                    }).inject(
                        Elm.getElement('.discount-title')
                    );

                    Elm.getElement('.field-id').set(
                        'html',
                        '#' + self.getAttribute('discountId')
                    );

                    if (parseInt(data.active)) {
                        self.$ActiveSwitch.setAttribute('text', QUILocale.get(lg, 'control.edit.btn.active.status_on'));
                        self.$ActiveSwitch.setSilentOn();
                    }
                }).then(function () {
                    return QUI.parse(self.$Elm);

                }).then(function () {
                    return Utils.parse(self.$Elm);

                }).then(function () {

                    self.$Elm.setStyles({
                        overflow: null
                    });

                    var Vat    = self.$Elm.getElement('[name="vat"]'),
                        VatRow = Vat.getParent('tr'),
                        Scope  = self.$Elm.getElement('[name="scope"]');

                    var hideVatRow = function () {
                        VatRow.setStyle('display', 'none');
                    };

                    var showVatRow = function () {
                        VatRow.setStyle('display', null);
                    };

                    Scope.addEvent('change', function (event) {
                        var value = event.target.value;

                        if (parseInt(value) === Discounts.DISCOUNT_SCOPE_TOTAL) {
                            showVatRow();
                            return;
                        }

                        hideVatRow();
                    });

                    if (parseInt(Scope.value) !== Discounts.DISCOUNT_SCOPE_TOTAL) {
                        hideVatRow();
                    }

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
        },

        /**
         * If active switch is triggered
         */
        $onActiveStatusChange: function () {
            this.$ActiveSwitch.disable();

            let Promise;
            const targetStatus = this.$ActiveSwitch.getStatus();

            if (targetStatus) {
                Promise = Discounts.activate(this.getAttribute('discountId'));
            } else {
                Promise = Discounts.deactivate(this.getAttribute('discountId'));
            }

            Promise.then((activeStatus) => {
                this.$ActiveSwitch.enable();

                if (!!activeStatus !== !!targetStatus) {
                    return;
                }

                this.fireEvent('activeStatusChange', [activeStatus, this]);

                if (activeStatus) {
                    this.$ActiveSwitch.setAttribute('text', QUILocale.get(lg, 'control.edit.btn.active.status_on'));
                    this.$ActiveSwitch.setSilentOn();
                } else {
                    this.$ActiveSwitch.setAttribute('text', QUILocale.get(lg, 'control.edit.btn.active.status_off'));
                    this.$ActiveSwitch.setSilentOff();
                }
            });
        }
    });
});
