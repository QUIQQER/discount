/**
 *
 * @module package/quiqqer/discount/bin/controls/search/Search
 * @author www.pcsg.de (Henning Leutz)
 *
 * @require qui/QUI
 * @require qui/controls/Control
 * @require qui/controls/buttons/Button
 * @require package/quiqqer/discount/bin/classes/Handler
 *
 * @event onLoaded
 */
define('package/quiqqer/discount/bin/controls/search/Search', [

    'qui/QUI',
    'qui/controls/Control',
    'qui/controls/buttons/Button',
    'qui/utils/Form',
    'package/quiqqer/discount/bin/classes/Handler',

    'text!package/quiqqer/discount/bin/controls/search/Search.html'

], function (QUI, QUIControl, QUIButton, QUIFormUtils, Handler, template) {
    "use strict";

    var Discounts = new Handler();

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

            Elm.set('html', template);

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

                Discounts.search(data).then(function (result) {

                    resolve(result);

                }).catch(reject);

            });
        }
    });
});
