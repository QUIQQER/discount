/**
 * Makes an input field to a user selection field
 *
 * @module package/quiqqer/discount/bin/controls/Select
 * @author www.pcsg.de (Henning Leutz)
 *
 * @require qui/controls/elements/Select
 * @require package/quiqqer/discount/bin/controls/SelectItem
 * @require package/quiqqer/discount/bin/classes/Handler
 * @require Locale
 */
define('package/quiqqer/discount/bin/controls/Select', [

    'qui/controls/elements/Select',
    'package/quiqqer/discount/bin/controls/SelectItem',
    'package/quiqqer/discount/bin/classes/Handler',
    'Locale'

], function (QUIElementSelect, SelectItem, Handler, QUILocale) {
    "use strict";

    var lg        = 'quiqqer/discount';
    var Discounts = new Handler();

    /**
     * @class controls/usersAndGroups/Input
     *
     * @param {Object} options
     * @param {HTMLInputElement} [Input]  - (optional), if no input given, one would be created
     *
     * @memberof! <global>
     */
    return new Class({

        Extends: QUIElementSelect,
        Type   : 'package/quiqqer/discount/bin/controls/Select',

        Binds: [
            'searchDiscounts',
            '$onSearchButtonClick'
        ],

        initialize: function (options) {
            this.parent(options);

            this.setAttribute('Search', this.searchDiscounts);
            this.setAttribute('icon', 'fa fa-percent');
            this.setAttribute('child', 'package/quiqqer/discount/bin/controls/SelectItem');

            this.setAttribute(
                'placeholder',
                QUILocale.get(lg, 'control.select.search.placeholder')
            );

            this.addEvents({
                onSearchButtonClick: this.$onSearchButtonClick
            });
        },

        /**
         * trigger a users search and open a discount dropdown for selection
         *
         * @method package/quiqqer/discount/bin/controls/Select#search
         * @return {Promise}
         */
        searchDiscounts: function () {
            var value = this.$Search.value;

            return Discounts.search({
                'id'      : value,
                'discount': value
            }, {
                order: 'id ASC',
                limit: 5
            }).then(function (result) {
                return result.map(function (Entry) {
                    return {
                        id   : Entry.id,
                        title: Entry.text
                    };
                });
            });
        },

        /**
         * event : on search button click
         *
         * @param {Object} self - select object
         * @param {Object} Btn - button object
         */
        $onSearchButtonClick: function (self, Btn) {
            Btn.setAttribute('icon', 'fa fa-spinner fa-spin');

            require([
                'package/quiqqer/discount/bin/controls/search/Window'
            ], function (Window) {
                new Window({
                    autoclose: true,
                    multiple : this.getAttribute('multiple'),
                    events   : {
                        onSubmit: function (Win, data) {
                            data = data.map(function (Entry) {
                                return parseInt(Entry.id);
                            });

                            for (var i = 0, len = data.length; i < len; i++) {
                                this.addItem(data[i]);
                            }
                        }.bind(this)
                    }
                }).open();

                Btn.setAttribute('icon', 'fa fa-search');
            }.bind(this));
        }
    });
});
