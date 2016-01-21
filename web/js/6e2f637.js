;(function ( $, window, document, undefined ) {
    // Defaults
    var _buffer = null,
        _watch  = [],
        $window = $(window),
        Plugin  = function() { }
    ;

    $.expr[":"].hasClassStartingWith = function(el, i, selector) {
        var re = new RegExp("\\b" + selector[3]);
        return re.test(el.className);
    };

    Plugin.prototype = {
        globals: {
            pluginName: "fadeThis",
            bufferTime: 200,
        },
        defaults: {
            baseName:       "slide-",
            speed:          500,
            easing:         "swing",
            offset:         -100,
            reverse:        false,
            distance:       125,
            scrolledIn:     null,
            scrolledOut:    null
        },
        init: function ( elem, options ) {
            this.addElements( elem, options );

            this._setEvent();
            this._checkVisibleElements();
        },
        addElements: function ( elem, options ) {
            var element         = elem === document.body    ? window    : elem,
                $element        = element === window        ? $("body") : $(element),
                base            = this,
                classBaseName   = (options && options.baseName) ? options.baseName : this.defaults.baseName
            ;
            
            if (!$element.is(":hasClassStartingWith('" + classBaseName + "')")) {

                $element.find(":hasClassStartingWith('" + classBaseName + "')").each(function() {
                    base._addElement($(this), options);
                });
            } else {
                base._addElement($element, options);
            }

            return $element;
        },
        _addElement: function ($elem, options) {
            var metadata        = $elem.data( "plugin-options" ),
                localOptions    = $.extend({}, this.defaults, options, metadata),
                item = {
                    element:    $elem,
                    options:    localOptions,
                    invp:       false
                }
            ;

            _watch.push(item);

            this._prepareElement(item);

            return $elem;
        },
        _prepareElement: function (item) {
            var cssOptionsIn = {
                    opacity: 0,
                    visibility: "visible",
                    position: "relative"
                },
                direction = null
            ;

            if (item.element.hasClass(item.options.baseName + "right")) {
                direction = "left";
            } else if (item.element.hasClass(item.options.baseName + "left")) {
                direction = "right";
            } else if (item.element.hasClass(item.options.baseName + "top")) {
                direction = "bottom";
            } else if (item.element.hasClass(item.options.baseName + "bottom")) {
                direction = "top";
            } else {
                return false;
            }

            cssOptionsIn[direction] = item.options.distance;

            item.element.css(cssOptionsIn);
        },
        _setEvent: function () {
            var base = this;

            $window.on("scroll", function(e) {
                if(!_buffer) {
                    _buffer = setTimeout(function(){
                        base._checkVisibleElements( e );
                        _buffer = null;
                    }, base.globals.bufferTime);
                }
            });
        },
        _checkVisibleElements: function( e ) {
            var base = this;

            $.each(_watch, function(key, item){
                if (base._isVisible(item)) {
                    if ( !item.invp ) {
                        item.invp = true;
                        base._triggerFading(item);
                        if ( item.options.scrolledIn ) {
                            item.options.scrolledIn.call( item.element, e );
                        }
                        item.element.trigger("fadethisscrolledin", e);
                    }
                } else if ( item.invp ) {
                    item.invp = false;
                    if ( item.options.reverse ) {
                        base._triggerFading(item, false);
                    }
                    if ( item.options.scrolledOut ) {
                        item.options.scrolledOut.call( item.element, e );
                    }
                    item.element.trigger("fadethisscrolledout", e);
                }
            });
        },
        _isVisible: function(item) {
            var docViewTop      = $window.scrollTop() + item.options.offset,
                docViewBottom   = docViewTop + $window.height() - 2 * item.options.offset,
                elemTop         = item.element.offset().top,
                elemBottom      = elemTop + item.element.height()
            ;

            return ((elemBottom >= docViewTop) &&  (elemTop <= docViewBottom) &&  (elemBottom <= docViewBottom) &&  (elemTop >= docViewTop));
        },
        _triggerFading:function (item, appear) {
            appear = typeof appear !== "undefined" ? appear : true;

            var stateAnimIn = {
                    opacity: 1
                },
                stateAnimOut = {
                    opacity: 0,
                },
                direction = null
            ;

            if (item.element.hasClass(item.options.baseName + "right")) {
                direction = "left";
            } else if (item.element.hasClass(item.options.baseName + "left")) {
                direction = "right";
            } else if (item.element.hasClass(item.options.baseName + "top")) {
                direction = "bottom";
            } else if (item.element.hasClass(item.options.baseName + "bottom")) {
                direction = "top";
            } else {
                return false;
            }

            stateAnimIn[direction]  = 0;
            stateAnimOut[direction] = item.options.distance;


            if (appear) {
                item.element.stop(true).animate(stateAnimIn, item.options.speed, item.options.easing);
            } else {
               item.element.stop(true).animate(stateAnimOut, item.options.speed, item.options.easing);
            }
        }
    };

    Plugin.defaults = Plugin.prototype.defaults;
    Plugin.globals  = Plugin.prototype.globals;

    window.Plugin = new Plugin();

    // Preventing against multiple instantiations for the same DOM element
    $.fn[ Plugin.globals.pluginName ] = function ( options ) {
        this.each(function() {
            if ( !$.data( window, "plugin_" + Plugin.globals.pluginName ) ) {
                $.data( window, "plugin_" + Plugin.globals.pluginName, "set" );
                $.data( this, "plugin_" + Plugin.globals.pluginName, window.Plugin.init( this, options ) );
            } else if ( !$.data( this, "plugin_" + Plugin.globals.pluginName )) {
                $.data( this, "plugin_" + Plugin.globals.pluginName, window.Plugin.addElements( this, options ) );
            }
        });

        // chain jQuery functions
        return this;
    };

})( jQuery, window, document );

/* =============================================================
 * bootstrap-typeahead.js v2.3.2
 * http://getbootstrap.com/2.3.2/javascript.html#typeahead
 * =============================================================
 * Copyright 2013 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ============================================================ */


!function($){

  "use strict"; // jshint ;_;


 /* TYPEAHEAD PUBLIC CLASS DEFINITION
  * ================================= */

  var Typeahead = function (element, options) {
    this.$element = $(element)
    this.options = $.extend({}, $.fn.typeahead.defaults, options)
    this.matcher = this.options.matcher || this.matcher
    this.sorter = this.options.sorter || this.sorter
    this.highlighter = this.options.highlighter || this.highlighter
    this.updater = this.options.updater || this.updater
    this.source = this.options.source
    this.$menu = $(this.options.menu)
    this.shown = false
    this.listen()
  }

  Typeahead.prototype = {

    constructor: Typeahead

  , select: function () {
      var val = this.$menu.find('.active').attr('data-value')
      this.$element
        .val(this.updater(val))
        .change()
      return this.hide()
    }

  , updater: function (item) {
      return item
    }

  , show: function () {
      var pos = $.extend({}, this.$element.position(), {
        height: this.$element[0].offsetHeight
      })

      this.$menu
        .insertAfter(this.$element)
        .css({
          top: pos.top + pos.height
        , left: pos.left
        })
        .show()

      this.shown = true
      return this
    }

  , hide: function () {
      this.$menu.hide()
      this.shown = false
      return this
    }

  , lookup: function (event) {
      var items

      this.query = this.$element.val()

      if (!this.query || this.query.length < this.options.minLength) {
        return this.shown ? this.hide() : this
      }

      items = $.isFunction(this.source) ? this.source(this.query, $.proxy(this.process, this)) : this.source

      return items ? this.process(items) : this
    }

  , process: function (items) {
      var that = this

      items = $.grep(items, function (item) {
        return that.matcher(item)
      })

      items = this.sorter(items)

      if (!items.length) {
        return this.shown ? this.hide() : this
      }

      return this.render(items.slice(0, this.options.items)).show()
    }

  , matcher: function (item) {
      return ~item.toLowerCase().indexOf(this.query.toLowerCase())
    }

  , sorter: function (items) {
      var beginswith = []
        , caseSensitive = []
        , caseInsensitive = []
        , item

      while (item = items.shift()) {
        if (!item.toLowerCase().indexOf(this.query.toLowerCase())) beginswith.push(item)
        else if (~item.indexOf(this.query)) caseSensitive.push(item)
        else caseInsensitive.push(item)
      }

      return beginswith.concat(caseSensitive, caseInsensitive)
    }

  , highlighter: function (item) {
      var query = this.query.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g, '\\$&')
      return item.replace(new RegExp('(' + query + ')', 'ig'), function ($1, match) {
        return '<strong>' + match + '</strong>'
      })
    }

  , render: function (items) {
      var that = this

      items = $(items).map(function (i, item) {
        i = $(that.options.item).attr('data-value', item)
        i.find('a').html(that.highlighter(item))
        return i[0]
      })

      items.first().addClass('active')
      this.$menu.html(items)
      return this
    }

  , next: function (event) {
      var active = this.$menu.find('.active').removeClass('active')
        , next = active.next()

      if (!next.length) {
        next = $(this.$menu.find('li')[0])
      }

      next.addClass('active')
    }

  , prev: function (event) {
      var active = this.$menu.find('.active').removeClass('active')
        , prev = active.prev()

      if (!prev.length) {
        prev = this.$menu.find('li').last()
      }

      prev.addClass('active')
    }

  , listen: function () {
      this.$element
        .on('focus',    $.proxy(this.focus, this))
        .on('blur',     $.proxy(this.blur, this))
        .on('keypress', $.proxy(this.keypress, this))
        .on('keyup',    $.proxy(this.keyup, this))

      if (this.eventSupported('keydown')) {
        this.$element.on('keydown', $.proxy(this.keydown, this))
      }

      this.$menu
        .on('click', $.proxy(this.click, this))
        .on('mouseenter', 'li', $.proxy(this.mouseenter, this))
        .on('mouseleave', 'li', $.proxy(this.mouseleave, this))
    }

  , eventSupported: function(eventName) {
      var isSupported = eventName in this.$element
      if (!isSupported) {
        this.$element.setAttribute(eventName, 'return;')
        isSupported = typeof this.$element[eventName] === 'function'
      }
      return isSupported
    }

  , move: function (e) {
      if (!this.shown) return

      switch(e.keyCode) {
        case 9: // tab
        case 13: // enter
        case 27: // escape
          e.preventDefault()
          break

        case 38: // up arrow
          e.preventDefault()
          this.prev()
          break

        case 40: // down arrow
          e.preventDefault()
          this.next()
          break
      }

      e.stopPropagation()
    }

  , keydown: function (e) {
      this.suppressKeyPressRepeat = ~$.inArray(e.keyCode, [40,38,9,13,27])
      this.move(e)
    }

  , keypress: function (e) {
      if (this.suppressKeyPressRepeat) return
      this.move(e)
    }

  , keyup: function (e) {
      switch(e.keyCode) {
        case 40: // down arrow
        case 38: // up arrow
        case 16: // shift
        case 17: // ctrl
        case 18: // alt
          break

        case 9: // tab
        case 13: // enter
          if (!this.shown) return
          this.select()
          break

        case 27: // escape
          if (!this.shown) return
          this.hide()
          break

        default:
          this.lookup()
      }

      e.stopPropagation()
      e.preventDefault()
  }

  , focus: function (e) {
      this.focused = true
    }

  , blur: function (e) {
      this.focused = false
      if (!this.mousedover && this.shown) this.hide()
    }

  , click: function (e) {
      e.stopPropagation()
      e.preventDefault()
      this.select()
      this.$element.focus()
    }

  , mouseenter: function (e) {
      this.mousedover = true
      this.$menu.find('.active').removeClass('active')
      $(e.currentTarget).addClass('active')
    }

  , mouseleave: function (e) {
      this.mousedover = false
      if (!this.focused && this.shown) this.hide()
    }

  }


  /* TYPEAHEAD PLUGIN DEFINITION
   * =========================== */

  var old = $.fn.typeahead

  $.fn.typeahead = function (option) {
    return this.each(function () {
      var $this = $(this)
        , data = $this.data('typeahead')
        , options = typeof option == 'object' && option
      if (!data) $this.data('typeahead', (data = new Typeahead(this, options)))
      if (typeof option == 'string') data[option]()
    })
  }

  $.fn.typeahead.defaults = {
    source: []
  , items: 8
  , menu: '<ul class="typeahead dropdown-menu"></ul>'
  , item: '<li><a href="#"></a></li>'
  , minLength: 1
  }

  $.fn.typeahead.Constructor = Typeahead


 /* TYPEAHEAD NO CONFLICT
  * =================== */

  $.fn.typeahead.noConflict = function () {
    $.fn.typeahead = old
    return this
  }


 /* TYPEAHEAD DATA-API
  * ================== */

  $(document).on('focus.typeahead.data-api', '[data-provide="typeahead"]', function (e) {
    var $this = $(this)
    if ($this.data('typeahead')) return
    $this.typeahead($this.data())
  })

}(window.jQuery);

/*!
 * typeaheadbundle.js
 * Part of the Lifo/TypeaheadBundle bundle for Symfony 2.2+
 * @author Jason Morriss <lifo2013@gmail.com>
 * @link https://github.com/lifo101/typeahead-bundle
 */

/*
 * Override the base Typeahead object with new features.
 * Based on https://gist.github.com/ecmel/4365063
 */
!function($) {
    var defs = $.fn.typeahead.defaults,
        base = $.fn.typeahead.Constructor.prototype;

    // save original events ...
    base._listen = base.listen;
    base._updater = base.updater;
    base._blur = base.blur;
    base._lookup = base.lookup;
    base._matcher = base.matcher;
    base._render = base.render;
    base._select = base.select;

    defs.delay = 250;               // default delay before triggering lookup
    defs.resetOnSelect = false;     // reset the input when an item is selected?
    defs.change = null;             // onChange callback when $element is changed

    defs.beforeSend = function(xhr, opts) {
        if (!this.options.spinner || this.$addon.data('prev-icon-class') != undefined) return;
        var icon = this.$addon.find('[class*="glyphicon-"]');
        if (icon.length >= 1) {
            this.$addon.data('prev-icon-class', icon.attr('class'));
            icon.attr('class', this.options.spinner);
        }
    };

    defs.afterSend = function(xhr, status) {
        if (!this.options.spinner || this.$addon.data('prev-icon-class') == undefined) return;
        var icon = this.$addon.find('[class*="glyphicon-"]');
        if (icon.length >= 1) {
            var cls = this.$addon.data('prev-icon-class');
            this.$addon.removeData('prev-icon-class');
            icon.attr('class', cls);
        }
    };

    defs.source = function(query, process) {
        query = $.trim(query.toLowerCase());

        if (query === '' || query.length < this.options.minLength) {
            return null;
        }

        var that = this,
            items = this.queries[query];

        // return cache if available
        if (items) {
            return items;
        }

        // stop current ajax request
        if (this.xhr) {
            this.xhr.abort();
        }

        // start new ajax request
        this.xhr = $.ajax({
            context: this,
            url: this.options.url,
            type: 'post',
            data: { query: query, limit: this.options.items },
            beforeSend: this.options.beforeSend,
            complete: this.options.afterSend,
            success: function(data) {
                that.queries[query] = items = [];       // clear cache
                for (var i = 0; i < data.length; i++) {
                    if (data[i].value !== undefined && data[i].id !== undefined) {
                        // map displayed value to its object
                        that.ids[data[i].value] = data[i];
                        items[i] = data[i].value;
                    } else {
                        var err = "Typeahead Error: data[" + i + "] is missing required properties: " + JSON.stringify(data[i]);
                        if (window.console === undefined) {
                            throw err;
                        } else {
                            console.error(err);
                        }
                    }
                }
                process(items);
            }
        });

        return null;
    };

    base.select = function() {
        var val = this.updater(this.$menu.find('.active').attr('data-value'));
        this.$element
          .val(this.options.resetOnSelect ? '' : val)
          .change();

        if ($.isFunction(this.options.change)) {
            this.options.change.apply(this, [val, this.ids[val]]);
        }

        if (this.options.resetOnSelect) {
            this.$id.val('');
        }

        return this.hide();
    };

    base.updater = function(item) {
        // update value of related field
        if (this.$id && this.ids[item]) {
            this.$id.val(this.ids[item].id);
            // update original to new value so if we blur w/o selecting
            // something the new value will populate.
            this.orig = {
                value: this.ids[item].value,
                id: this.ids[item].id
            };
        } else {
            // user didn't select an item so reset the element and ID.
            // If the item is empty then allow the field to be cleared.
            var val = '', id = '';
            if (this.orig && item != '') {
                val = this.orig.value;
                id = this.orig.id;
            }

            this.$element.val(val);
            if (this.$id) {
                this.$id.val(id);
            }
        }

        return this._updater(item);
    };

    base.blur = function(e) {
        // only call updater if a menu item was not selected. This prevents a
        // flicker of the original (orig) from showing up briefly when user
        // selects an item from the menu.
        if (!this.mousedover) {
            this.updater($.trim(this.$element.val()));
        }
        this._blur(e);
    };

    base.lookup = function() {
        if (this.options.delay) {
            clearTimeout(this.delayedLookup);
            this.delayedLookup = setTimeout($.proxy(function(){ this._lookup() }, this), this.options.delay);
        } else {
            this._lookup();
        }
    };

    base.listen = function() {
        this._listen();

        this.ids = {};
        this.queries = {};

        // save original value when page was loaded
        if (this.orig === undefined) {
            this.orig = { value: this.$element.val() };
        }

        // maintain relationship with another element that will hold the
        // selected ID (usually a hidden input).
        if (this.options.id) {
            this.$id = $('#' + this.options.id.replace(/(:|\.|\[|\])/g, '\\$1'));
            if (this.$element.val() != '') {
                this.ids[this.$element.val()] = { id: this.$id.val(), value: this.$element.val() };
            }
            this.orig.id = this.$id.val();
        }

        // handle pasting via mouse
        this.$element
            //.on('contextmenu', $.proxy(this.on_contextmenu, this))
            .on('paste', $.proxy(this.on_paste, this));

        // any "addon" icons?
        this.$addon = this.$element.siblings('.input-group-addon');
    };

    base.on_paste = function(e) {
        // since the pasted text has not actually been updated in the input
        // when this event fires we have to put a very small delay before
        // triggering a new lookup or else it'll simply do the lookup with
        // the current text in the input.
        clearTimeout(this.pasted);
        this.pasted = setTimeout($.proxy(function(){ this.lookup(); this.pasted = undefined; }, this), 100);
    };

    // convienence method to auto-select the input text; might not actually
    // be wanted in all cases but for now I want it...
    //base.on_contextmenu = function(e) {
    //    this.$element.select();
    //}
}(jQuery);

!function($) {
    $(function(){
        // The controller handling the request already filtered the items and
        // its possible it matched things that are not in the displayed label so
        // we must return true for all.
        var matcher = function(){ return true };

        // callback when the $element is changed. Gives our customization a
        // chance to act on the new data.
        var change = function(text, data){
            var _id = this.$id.attr('id');
            var list = $('#' + _id + '_list');
            if (list.length) {
                var li = list.find('#' + _id + '_' + data.id);
                if (!li.length) {
                    // convert 'name_subname_extraname' to 'name[subname][extraname][]'
                    var name = _id.split(/_/);
                    name = (name.length > 1 ? name.shift() + '[' + name.join('][') + ']' : name.join()) + '[]';
                    li = $( this.$id.data('prototype') );
                    li.data('value', data.id)
                        .find('input:hidden').val(data.id).attr('id', _id + '_' + data.id).attr('name', name).end()
                        .find('.lifo-typeahead-item').text(text).end()
                        .appendTo(list)
                        ;
                }
            }

            if ($.isFunction(this.options.callback)) {
                this.options.callback.apply(this, [text, data]);
            }
        };

        var typeahead = function(e){
            var me = $(this);
            if (me.data('typeahead')) return;

            var d = me.data(),
                opts = {
                    id: me.attr('id').replace(/_text$/, ''),
                    url: d.url,
                    change: change,
                    matcher: matcher
                };
            if (undefined !== d.delay && d.delay != '') opts.delay = d.delay;
            if (undefined !== d.items && d.items != '') opts.items = d.items;
            if (undefined !== d.spinner) opts.spinner = d.spinner;
            if (undefined !== d.minlength && d.minlength != '') opts.minLength = d.minlength;
            if (undefined !== d.resetonselect && d.resetonselect != '') opts.resetOnSelect = d.resetonselect ? true : false;
            if (undefined !== d.callback && d.callback != '') opts.callback = d.callback;

            // allow the defined callback to be a function string
            if (typeof opts.callback == 'string'
                && opts.callback in window
                && $.isFunction(window[opts.callback])) {
                opts.callback = window[opts.callback];
            }

            me.typeahead(opts);

            var list = $('#' + me.data('typeahead').$id.attr('id') + '_list');

            // BS3+ hack. Must move the list outside of the input_group if there is appended icon/btn
            // since braincrafted/bootstrap-bundle or mopa/bootstrap-bundle will wrap the input in an "input_group"
            // we must move the list outside or it breaks the styling of the appended icon/btn.
            // This was the only way I could fix this w/o overridding the templates of those other bundles (which would
            // be a pain since each bundle defines their templates differently).
            if (list.parent().is('.input-group')) {
                list.parent().after(list);
                list.show();
            }

            // on-click handler to remove items from <ul> list
            list.on({
                'click.lifo-typeahead': function(e){
                    // @todo make this 'prettier' ... fade out, etc...
                    $(this).closest('li').remove();
                    e.preventDefault();
                    e.stopPropagation();
                }
            }, 'a');
        };
        // apply to current elements in DOM
        $('input[data-provide="lifo-typeahead"]').each(typeahead);
        // apply to any future elements
        $(document).on('focus.lifo-typeahead.data-api', 'input[data-provide="lifo-typeahead"]', typeahead);
    });
}(window.jQuery);
