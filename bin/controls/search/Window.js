/**
 *
 * @module package/quiqqer/discount/bin/controls/search/Search
 * @author www.pcsg.de (Henning Leutz)
 */
define('package/quiqqer/discount/bin/controls/search/Window', [

    'qui/QUI',
    'qui/controls/Control',
    'qui/controls/windows/Confirm',
    'package/quiqqer/discount/bin/classes/Handler',
    'Locale',

    'css!package/quiqqer/discount/bin/controls/search/Window.css'

], function (QUI, QUIControl, QUIConfirm, Handler, QUILocale) {
    "use strict";

    var lg = 'quiqqer/discount';

    return new Class({

        Extends: QUIConfirm,
        Type   : 'package/quiqqer/discount/bin/controls/search/Window',

        Binds: [
            '$onOpen'
        ],

        options: {
            maxHeight: 600,
            maxWidth : 800,
            icon     : 'fa fa-percent',
            title    : QUILocale.get(lg, 'window.discount.search.title'),
            autoclose: false,

            cancel_button: {
                text     : QUILocale.get('quiqqer/system', 'cancel'),
                textimage: 'fa fa-remove'
            },
            ok_button    : {
                text     : QUILocale.get(lg, 'control.window.button.search'),
                textimage: 'fa fa-search'
            }
        },

        initialize: function (options) {
            this.parent(options);

            this.$Search = null;
            this.$Result = null;

            this.$ButtonCancel = null;
            this.$ButtonSubmit = null;

            this.addEvents({
                onOpen: this.$onOpen
            });
        },

        /**
         * Return the DOMNode Element
         *
         * @returns {HTMLDivElement}
         */
        $onOpen: function (Win) {
            var self    = this,
                Content = Win.getContent();

            Win.Loader.show();

            Content.set('html', '');
            Content.addClass('discount-search');

            this.$SearchContainer = new Element('div', {
                'class': 'discount-search-searchContainer',
                style  : {
                    display: 'none',
                    opacity: 0
                }
            }).inject(Content);

            this.$ResultContainer = new Element('div', {
                'class': 'discount-search-resultContainer',
                style  : {
                    display: 'none',
                    opacity: 0
                }
            }).inject(Content);

            this.$SC_FX = moofx(this.$SearchContainer);
            this.$RC_FX = moofx(this.$ResultContainer);

            this.$ButtonSubmit = this.getButton('submit');
            this.$ButtonCancel = this.getButton('cancel');

            this.$ButtonSubmit.removeEvents('click');
            this.$ButtonSubmit.addEvent('click', function () {
                self.showResults();
            });

            require([
                'package/quiqqer/discount/bin/controls/search/Search',
                'package/quiqqer/discount/bin/controls/search/Result'
            ], function (Search, Result) {
                self.$Search = new Search({
                    events: {
                        onLoaded: function () {
                            self.$SC_FX.animate({
                                opacity: 1
                            });

                            self.Loader.hide();
                        }
                    }
                }).inject(self.$SearchContainer);

                self.$Result = new Result({
                    events: {
                        onDblClick: function () {
                            self.submit();
                        }
                    }
                }).inject(self.$ResultContainer);

                self.$Result.resize();
                self.showResults();
            });
        },

        /**
         * Submit
         */
        submit: function () {
            if (!this.$Result) {
                return;
            }

            this.fireEvent('submit', [this, this.$Result.getSelected()]);

            if (this.getAttribute('autoclose')) {
                this.close();
            }
        },

        /**
         * Show search
         */
        showSearch: function () {
            var self = this;

            this.$SearchContainer.setStyles({
                display: null,
                opacity: 0
            });

            this.$RC_FX.animate({
                opacity: 0
            }, {
                duration: 200,
                callback: function () {
                    self.$ResultContainer.setStyle('display', 'none');

                    self.$SC_FX.animate({
                        opacity: 1,
                        top    : 0
                    }, {
                        duration: 200,
                        callback: function () {
                            self.$ButtonSubmit.removeEvents('click');
                            self.$ButtonCancel.removeEvents('click');

                            self.$ButtonSubmit.addEvent('click', function () {
                                self.showResults();
                            });

                            self.$ButtonSubmit.setAttribute(
                                'text',
                                QUILocale.get(lg, 'control.window.button.search')
                            );

                            self.$ButtonCancel.addEvent('click', function () {
                                self.cancel();
                            });

                            self.$ButtonCancel.setAttributes({
                                'text'     : QUILocale.get('quiqqer/system', 'cancel'),
                                'textimage': 'fa fa-remove'
                            });
                        }
                    });
                }
            });
        },

        /**
         * Show results
         */
        showResults: function () {
            var self = this;

            this.Loader.show();
            this.$Search.search().then(function (result) {
                self.Loader.hide();

                self.$Result.setData({
                    data: result
                });

                self.$SC_FX.animate({
                    opacity: 0,
                    top    : -50
                }, {
                    duration: 200,
                    callback: function () {
                        self.$SearchContainer.setStyle('display', 'none');

                        self.$ResultContainer.setStyles({
                            display: null,
                            opacity: 0
                        });

                        self.$RC_FX.animate({
                            opacity: 1
                        }, {
                            duration: 200,
                            callback: function () {

                                self.$ButtonSubmit.removeEvents('click');
                                self.$ButtonCancel.removeEvents('click');

                                self.$ButtonSubmit.addEvent('click', function () {
                                    self.submit();
                                });

                                self.$ButtonSubmit.setAttribute(
                                    'text',
                                    QUILocale.get('quiqqer/system', 'accept')
                                );


                                self.$ButtonCancel.addEvent('click', function () {
                                    self.showSearch();
                                });

                                self.$ButtonCancel.setAttributes({
                                    'text'     : QUILocale.get(lg, 'control.window.button.back'),
                                    'textimage': 'fa fa-angle-left'
                                });

                                self.$Result.resize();
                            }
                        });
                    }
                });
            });
        }
    });
});
