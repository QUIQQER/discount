/**
 * Makes an input field to a user selection field
 *
 * @module package/quiqqer/discount/bin/controls/Select
 * @author www.pcsg.de (Henning Leutz)
 *
 * @require qui/controls/Control
 * @require qui/controls/buttons/Button
 * @require package/quiqqer/discount/bin/controls/SelectItem
 * @require package/quiqqer/discount/bin/classes/Handler
 * @require Locale
 * @require css!package/quiqqer/discount/bin/controls/Select.css
 *
 * @event onAddDiscount [ this, id ]
 */
define('package/quiqqer/discount/bin/controls/Select', [

    'qui/controls/Control',
    'qui/controls/buttons/Button',
    'package/quiqqer/discount/bin/controls/SelectItem',
    'package/quiqqer/discount/bin/classes/Handler',
    'Locale',

    'css!package/quiqqer/discount/bin/controls/Select.css'

], function (QUIControl, QUIButton, SelectItem, Handler, QUILocale) {
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

        Extends: QUIControl,
        Type   : 'package/quiqqer/discount/bin/controls/Select',

        Binds: [
            'close',
            'fireSearch',
            'update',

            '$onDiscountDestroy',
            '$onInputFocus',
            '$onImport'
        ],

        options: {
            max     : false, // max entries
            multible: true,  // select more than one entry?
            name    : '',    // string
            styles  : false, // object
            label   : false  // text string or a <label> DOMNode Element
        },

        initialize: function (options, Input) {
            this.parent(options);

            this.$Input    = Input || null;
            this.$Elm      = null;
            this.$List     = null;
            this.$Search   = null;
            this.$DropDown = null;

            this.$SearchButton = null;

            this.$search = false;
            this.$values = [];

            this.addEvents({
                onImport: this.$onImport
            });
        },

        /**
         * Return the DOMNode Element
         *
         * @method package/quiqqer/discount/bin/controls/Select#create
         * @return {HTMLElement} The main DOM-Node Element
         */
        create: function () {

            if (this.$Elm) {
                return this.$Elm;
            }

            var self = this;

            this.$Elm = new Element('div', {
                'class'     : 'qui-discount-list',
                'data-quiid': this.getId()
            });

            if (!this.$Input) {
                this.$Input = new Element('input', {
                    name: this.getAttribute('name')
                }).inject(this.$Elm);
            } else {
                this.$Elm.wraps(this.$Input);
            }

            if (this.getAttribute('styles')) {
                this.$Elm.setStyles(this.getAttribute('styles'));
            }

            this.$Input.set({
                styles: {
                    opacity : 0,
                    position: 'absolute',
                    zIndex  : 1,
                    left    : 5,
                    top     : 5,
                    cursor  : 'pointer'
                },
                events: {
                    focus: this.$onInputFocus
                }
            });


            this.$List = new Element('div', {
                'class': 'qui-discount-list-list'
            }).inject(this.$Elm);

            this.$Search = new Element('input', {
                'class'    : 'qui-discount-list-search',
                placeholder: QUILocale.get(lg, 'control.select.search.placeholder'),
                events     : {
                    keyup: function (event) {
                        if (event.key === 'down') {
                            self.down();
                            return;
                        }

                        if (event.key === 'up') {
                            self.up();
                            return;
                        }

                        if (event.key === 'enter') {
                            self.submit();
                            return;
                        }

                        self.fireSearch();
                    },

                    blur : self.close,
                    focus: self.fireSearch
                }
            }).inject(this.$Elm);

            this.$SearchButton = new QUIButton({
                icon  : 'icon-search fa fa-search',
                styles: {
                    width: 50
                },
                events: {
                    onClick: function (Btn) {
                        Btn.setAttribute('icon', 'fa fa-spinner fa-spin');

                        require([
                            'package/quiqqer/discount/bin/controls/search/Window'
                        ], function (Window) {

                            new Window({
                                events: {
                                    onSubmit: function (Win, values) {
                                        for (var i = 0, len = values.length; i < len; i++) {
                                            self.addDiscount(values[i].id);
                                        }

                                        Win.close();
                                    }
                                }
                            }).open();

                            Btn.setAttribute('icon', 'icon-search fa fa-search');
                        });
                    }
                }
            }).inject(this.$Elm);

            this.$DropDown = new Element('div', {
                'class': 'qui-discount-list-dropdown',
                styles : {
                    display: 'none',
                    top    : this.$Search.getPosition().y + this.$Search.getSize().y,
                    left   : this.$Search.getPosition().x
                }
            }).inject(document.body);

            if (this.getAttribute('label')) {
                var Label = this.getAttribute('label');

                if (typeof this.getAttribute('label').nodeName === 'undefined') {
                    Label = new Element('label', {
                        html: this.getAttribute('label')
                    });
                }

                Label.inject(this.$Elm, 'top');

                if (Label.get('data-desc') && Label.get('data-desc') != '&nbsp;') {
                    new Element('div', {
                        'class': 'description',
                        html   : Label.get('data-desc'),
                        styles : {
                            marginBottom: 10
                        }
                    }).inject(Label, 'after');
                }
            }


            // load values
            if (this.$Input.value || this.$Input.value !== '') {
                this.$Input.value.split(',').each(function (discountId) {
                    self.addDiscount(discountId);
                });
            }

            return this.$Elm;
        },

        /**
         * event: on inject
         */
        $onImport: function () {
            var Elm = this.getElm();

            if (Elm.nodeName === 'INPUT') {
                this.$Input = Elm;
            }

            this.$Elm = null;
            this.create();
        },

        /**
         * fire the search
         *
         * @method package/quiqqer/discount/bin/controls/Select#fireSearch
         */
        fireSearch: function () {
            if (this.$Search.value === '') {
                return this.close();
            }

            this.cancelSearch();

            this.$DropDown.set({
                html  : '<span class="icon-spinner icon-spin fa fa-spinner fa-spin"></span>',
                styles: {
                    display: '',
                    top    : this.$Search.getPosition().y + this.$Search.getSize().y,
                    left   : this.$Search.getPosition().x
                }
            });

            this.$search = this.search.delay(500, this);
        },

        /**
         * cancel the search timeout
         *
         * @method package/quiqqer/discount/bin/controls/Select#cancelSearch
         */
        cancelSearch: function () {
            if (this.$search) {
                clearTimeout(this.$search);
            }
        },

        /**
         * close the users search
         *
         * @method package/quiqqer/discount/bin/controls/Select#close
         */
        close: function () {
            this.cancelSearch();
            this.$DropDown.setStyle('display', 'none');
            this.$Search.value = '';
        },

        /**
         * trigger a users search and open a discount dropdown for selection
         *
         * @method package/quiqqer/discount/bin/controls/Select#search
         */
        search: function () {

            var self  = this,
                value = this.$Search.value;

            Discounts.search({
                'id'      : value,
                'discount': value
            }, {
                order: 'id ASC',
                limit: 5
            }).then(function (result) {

                var i, id, len, nam, entry, Entry,
                    func_mousedown, func_mouseover,

                    DropDown = self.$DropDown;


                DropDown.set('html', '');

                if (!result || !result.length) {
                    new Element('div', {
                        html  : QUILocale.get(lg, 'control.select.no.results'),
                        styles: {
                            'float': 'left',
                            'clear': 'both',
                            padding: 5,
                            margin : 5
                        }
                    }).inject(DropDown);

                    return;
                }

                // events
                func_mousedown = function (event) {
                    var Elm = event.target;

                    if (!Elm.hasClass('qui-discount-list-dropdown-entry')) {
                        Elm = Elm.getParent('.qui-discount-list-dropdown-entry');
                    }

                    self.addDiscount(Elm.get('data-id'));
                };

                func_mouseover = function () {
                    this.getParent().getElements(
                        '.qui-discount-list-dropdown-entry-hover'
                    ).removeClass(
                        'qui-discount-list-dropdown-entry-hover'
                    );

                    this.addClass('qui-discount-list-dropdown-entry-hover');
                };

                // create
                for (i = 0, len = result.length; i < len; i++) {

                    entry = result[i];
                    id    = entry.id;

                    nam = '#' + id + ' - ';
                    nam = nam + QUILocale.get(lg, 'discount.' + id + '.title');

                    if (value) {
                        nam = nam.toString().replace(
                            new RegExp('(' + value + ')', 'gi'),
                            '<span class="mark">$1</span>'
                        );
                    }

                    Entry = new Element('div', {
                        html     : '<span class="fa fa-percent"></span>' +
                                   '<span>' + nam + ' (' + id + ')</span>',
                        'class'  : 'box-sizing qui-discount-list-dropdown-entry',
                        'data-id': id,
                        events   : {
                            mousedown : func_mousedown,
                            mouseenter: func_mouseover
                        }
                    }).inject(DropDown);
                }
            });
        },

        /**
         * Add a user to the input
         *
         * @method package/quiqqer/discount/bin/controls/Select#addUser
         * @param {Number|String} id - id of the user
         * @return {Object} this (package/quiqqer/discount/bin/controls/Select)
         */
        addDiscount: function (id) {
            if (!id || id === '') {
                return this;
            }

            new SelectItem({
                id    : id,
                events: {
                    onDestroy: this.$onDiscountDestroy
                }
            }).inject(this.$List);

            this.$values.push(id);

            this.fireEvent('addDiscount', [this, id]);
            this.$refreshValues();

            return this;
        },

        /**
         * keyup - users dropdown selection one step up
         *
         * @method package/quiqqer/discount/bin/controls/Select#up
         * @return {Object} this (package/quiqqer/discount/bin/controls/Select)
         */
        up: function () {
            if (!this.$DropDown || !this.$DropDown.getFirst()) {
                return this;
            }

            var Active = this.$DropDown.getElement(
                '.qui-discount-list-dropdown-entry-hover'
            );

            // Last Element
            if (!Active) {
                this.$DropDown.getLast().addClass(
                    'qui-discount-list-dropdown-entry-hover'
                );

                return this;
            }

            Active.removeClass(
                'qui-discount-list-dropdown-entry-hover'
            );

            if (!Active.getPrevious()) {
                this.up();
                return this;
            }

            Active.getPrevious().addClass(
                'qui-discount-list-dropdown-entry-hover'
            );
        },

        /**
         * keydown - users dropdown selection one step down
         *
         * @method package/quiqqer/discount/bin/controls/Select#down
         * @return {Object} this (package/quiqqer/discount/bin/controls/Select)
         */
        down: function () {
            if (!this.$DropDown || !this.$DropDown.getFirst()) {
                return this;
            }

            var Active = this.$DropDown.getElement(
                '.qui-discount-list-dropdown-entry-hover'
            );

            // First Element
            if (!Active) {
                this.$DropDown.getFirst().addClass(
                    'qui-discount-list-dropdown-entry-hover'
                );

                return this;
            }

            Active.removeClass(
                'qui-discount-list-dropdown-entry-hover'
            );

            if (!Active.getNext()) {
                this.down();
                return this;
            }

            Active.getNext().addClass(
                'qui-discount-list-dropdown-entry-hover'
            );

            return this;
        },

        /**
         * select the selected user / group
         *
         * @method package/quiqqer/discount/bin/controls/Select#submit
         */
        submit: function () {
            if (!this.$DropDown) {
                return;
            }

            var Active = this.$DropDown.getElement(
                '.qui-discount-list-dropdown-entry-hover'
            );

            if (Active) {
                this.addDiscount(Active.get('data-id'));
            }

            this.$Input.value = '';
            this.search();
        },

        /**
         * Set the focus to the input field
         *
         * @method package/quiqqer/discount/bin/controls/Select#focus
         * @return {Object} this (package/quiqqer/discount/bin/controls/Select)
         */
        focus: function () {
            if (this.$Search) {
                this.$Search.focus();
            }

            return this;
        },

        /**
         * Write the ids to the real input field
         *
         * @method package/quiqqer/discount/bin/controls/Select#$refreshValues
         */
        $refreshValues: function () {
            this.$Input.value = this.$values.join(',');
            this.$Input.fireEvent('change', [{
                target: this.$Input
            }]);
        },

        /**
         * event : if a user or a groupd would be destroyed
         *
         * @method package/quiqqer/discount/bin/controls/Select#$onDiscountDestroy
         * @param {Object} Item - package/quiqqer/discount/bin/controls/DiscountDisplay
         */
        $onDiscountDestroy: function (Item) {
            this.$values = this.$values.erase(
                Item.getAttribute('id')
            );

            this.$refreshValues();
        },

        /**
         * event : on input focus, if the real input field get the focus
         *
         * @param {DOMEvent} event
         */
        $onInputFocus: function (event) {
            if (typeof event !== 'undefined') {
                event.stop();
            }

            this.focus();
        }
    });
});
