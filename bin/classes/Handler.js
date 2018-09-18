/**
 * Discount handler
 * Create and edit discounts
 *
 * @module package/quiqqer/discount/bin/classes/Handler
 * @author www.pcsg.de (Henning Leutz)
 *
 * @require qui/QUI
 * @require qui/classes/DOM
 * @require Ajax
 */
define('package/quiqqer/discount/bin/classes/Handler', [

    'qui/QUI',
    'qui/classes/DOM',
    'Ajax'

], function (QUI, QUIDOM, Ajax) {
    "use strict";

    return new Class({

        Extends: QUIDOM,
        Type   : 'package/quiqqer/discount/bin/classes/Handler',

        /**
         * discount type -> percent
         */
        DISCOUNT_TYPE_PERCENT: 1,

        /**
         * discount type -> crrency
         */
        DISCOUNT_TYPE_CURRENCY: 2,

        /**
         * discount scope -> discount is for every product
         */
        DISCOUNT_SCOPE_EVERY_PRODUCT: 1,

        /**
         * discount scope -> discount is for all products (for the complete order)
         */
        DISCOUNT_SCOPE_TOTAL: 2,

        /**
         * discount scope -> discount is for all products (for the complete order)
         */
        DISCOUNT_SCOPE_UNIQUE: 3,

        /**
         * discount usage type -> the discount will be used manuel
         */
        DISCOUNT_USAGE_TYPE_MANUEL: 0,

        /**
         * discount usage type -> the discount will be used manuel
         */
        DISCOUNT_USAGE_TYPE_AUTOMATIC: 1,

        /**
         * Search discounts
         *
         * @param {Object} [fields] - fields to search
         * @param {Object} [params] - query params
         * @returns {Promise}
         */
        search: function (fields, params) {
            fields = fields || {};
            params = params || {};

            return new Promise(function (resolve, reject) {
                Ajax.get('package_quiqqer_discount_ajax_search', resolve, {
                    'package': 'quiqqer/discount',
                    onError  : reject,
                    fields   : JSON.encode(fields),
                    params   : JSON.encode(params)
                });
            });
        },

        /**
         *
         * @param {number} discountId
         * @returns {Promise}
         */
        getChild: function (discountId) {
            return new Promise(function (resolve, reject) {
                Ajax.get('package_quiqqer_discount_ajax_get', resolve, {
                    'package': 'quiqqer/discount',
                    onError  : reject,
                    id       : parseInt(discountId)
                });
            });
        },

        /**
         * Return a discount list for a grid
         *
         * @params {Object} params - grid params
         * @returns {Promise}
         */
        getList: function (params) {
            params = params || {};

            return new Promise(function (resolve, reject) {
                Ajax.get('package_quiqqer_discount_ajax_list', resolve, {
                    'package': 'quiqqer/discount',
                    onError  : reject,
                    params   : JSON.encode(params)
                });
            });
        },

        /**
         * Create a new Discount
         *
         * @params {Array} [params] - Discount attributes
         * @returns {Promise}
         */
        createChild: function (params) {
            return new Promise(function (resolve, reject) {
                Ajax.post('package_quiqqer_discount_ajax_create', function (result) {

                    require([
                        'package/quiqqer/translator/bin/classes/Translator'
                    ], function (Translator) {
                        new Translator().refreshLocale().then(function () {
                            resolve(result);
                        });
                    });
                }, {
                    'package': 'quiqqer/discount',
                    onError  : reject,
                    params   : JSON.encode(params)
                });
            });
        },

        /**
         * Delete an discount
         *
         * @param {Number} discountId - Discount-ID
         * @returns {Promise}
         */
        deleteChild: function (discountId) {
            return new Promise(function (resolve, reject) {
                Ajax.post('package_quiqqer_discount_ajax_deleteChild', resolve, {
                    'package' : 'quiqqer/discount',
                    onError   : reject,
                    discountId: discountId
                });
            });
        },

        /**
         * Delete multible discounts
         *
         * @param {Array} discountIds - array of Discount-IDs
         * @returns {Promise}
         */
        deleteChildren: function (discountIds) {
            return new Promise(function (resolve, reject) {
                Ajax.post('package_quiqqer_discount_ajax_deleteChildren', resolve, {
                    'package'  : 'quiqqer/discount',
                    onError    : reject,
                    discountIds: JSON.encode(discountIds)
                });
            });
        },

        /**
         * Save an discount
         *
         * @param {Number} discountId
         * @param {Object} data - Discount attributes
         */
        update: function (discountId, data) {
            return new Promise(function (resolve, reject) {
                Ajax.post('package_quiqqer_discount_ajax_update', resolve, {
                    'package' : 'quiqqer/discount',
                    onError   : reject,
                    discountId: discountId,
                    params    : JSON.encode(data)
                });
            });
        },

        /**
         * Toggle the status from the discount
         *
         * @param {Number} discountId
         * @returns {Promise}
         */
        toggleStatus: function (discountId) {
            return new Promise(function (resolve, reject) {
                Ajax.post(
                    'package_quiqqer_discount_ajax_toggle',
                    resolve, {
                        'package' : 'quiqqer/discount',
                        discountId: discountId,
                        onError   : reject
                    });
            });
        },

        /**
         * Activate the discount
         *
         * @param {Number} discountId
         * @returns {Promise}
         */
        activate: function (discountId) {
            return new Promise(function (resolve, reject) {
                Ajax.post(
                    'package_quiqqer_discount_ajax_activate',
                    resolve, {
                        'package' : 'quiqqer/discount',
                        discountId: discountId,
                        onError   : reject
                    });
            });
        },

        /**
         * Deactivate the discount
         *
         * @param {Number} discountId
         * @returns {Promise}
         */
        deactivate: function (discountId) {
            return new Promise(function (resolve, reject) {
                Ajax.post(
                    'package_quiqqer_discount_ajax_deactivate',
                    resolve, {
                        'package' : 'quiqqer/discount',
                        discountId: discountId,
                        onError   : reject
                    });
            });
        }
    });
});
