/**
 * Discount handler
 * Create and edit discounts
 *
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
         * Search discounts
         *
         * @param {Object} [params] - query params
         * @returns {Promise}
         */
        search: function (params) {
            params = params || {};

            return new Promise(function (resolve, reject) {
                Ajax.get('package_quiqqer_discount_ajax_search', resolve, {
                    'package': 'quiqqer/discount',
                    onError  : reject,
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
         *
         * @returns {Promise}
         */
        getList: function () {
            return this.search();
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
