/******/ (function(modules) { // webpackBootstrap
    /******/ 	// The module cache
    /******/ 	var installedModules = {};
    /******/
    /******/ 	// The require function
    /******/ 	function __webpack_require__(moduleId) {
        /******/
        /******/ 		// Check if module is in cache
        /******/ 		if(installedModules[moduleId]) {
            /******/ 			return installedModules[moduleId].exports;
            /******/ 		}
        /******/ 		// Create a new module (and put it into the cache)
        /******/ 		var module = installedModules[moduleId] = {
            /******/ 			i: moduleId,
            /******/ 			l: false,
            /******/ 			exports: {}
            /******/ 		};
        /******/
        /******/ 		// Execute the module function
        /******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
        /******/
        /******/ 		// Flag the module as loaded
        /******/ 		module.l = true;
        /******/
        /******/ 		// Return the exports of the module
        /******/ 		return module.exports;
        /******/ 	}
    /******/
    /******/
    /******/ 	// expose the modules object (__webpack_modules__)
    /******/ 	__webpack_require__.m = modules;
    /******/
    /******/ 	// expose the module cache
    /******/ 	__webpack_require__.c = installedModules;
    /******/
    /******/ 	// define getter function for harmony exports
    /******/ 	__webpack_require__.d = function(exports, name, getter) {
        /******/ 		if(!__webpack_require__.o(exports, name)) {
            /******/ 			Object.defineProperty(exports, name, {
                /******/ 				configurable: false,
                /******/ 				enumerable: true,
                /******/ 				get: getter
                /******/ 			});
            /******/ 		}
        /******/ 	};
    /******/
    /******/ 	// getDefaultExport function for compatibility with non-harmony modules
    /******/ 	__webpack_require__.n = function(module) {
        /******/ 		var getter = module && module.__esModule ?
            /******/ 			function getDefault() { return module['default']; } :
            /******/ 			function getModuleExports() { return module; };
        /******/ 		__webpack_require__.d(getter, 'a', getter);
        /******/ 		return getter;
        /******/ 	};
    /******/
    /******/ 	// Object.prototype.hasOwnProperty.call
    /******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
    /******/
    /******/ 	// __webpack_public_path__
    /******/ 	__webpack_require__.p = "";
    /******/
    /******/ 	// Load entry module and return exports
    /******/ 	return __webpack_require__(__webpack_require__.s = 119);
    /******/ })
/************************************************************************/
/******/ ([
    /* 0 */
    /***/ (function(module, exports) {

        var core = module.exports = { version: '2.5.7' };
        if (typeof __e == 'number') __e = core; // eslint-disable-line no-undef


        /***/ }),
    /* 1 */
    /***/ (function(module, exports, __webpack_require__) {

        var store = __webpack_require__(29)('wks');
        var uid = __webpack_require__(30);
        var Symbol = __webpack_require__(2).Symbol;
        var USE_SYMBOL = typeof Symbol == 'function';

        var $exports = module.exports = function (name) {
            return store[name] || (store[name] =
                USE_SYMBOL && Symbol[name] || (USE_SYMBOL ? Symbol : uid)('Symbol.' + name));
        };

        $exports.store = store;


        /***/ }),
    /* 2 */
    /***/ (function(module, exports) {

// https://github.com/zloirock/core-js/issues/86#issuecomment-115759028
        var global = module.exports = typeof window != 'undefined' && window.Math == Math
            ? window : typeof self != 'undefined' && self.Math == Math ? self
                // eslint-disable-next-line no-new-func
                : Function('return this')();
        if (typeof __g == 'number') __g = global; // eslint-disable-line no-undef


        /***/ }),
    /* 3 */
    /***/ (function(module, exports, __webpack_require__) {

        var isObject = __webpack_require__(9);
        module.exports = function (it) {
            if (!isObject(it)) throw TypeError(it + ' is not an object!');
            return it;
        };


        /***/ }),
    /* 4 */
    /***/ (function(module, exports, __webpack_require__) {

        var dP = __webpack_require__(8);
        var createDesc = __webpack_require__(28);
        module.exports = __webpack_require__(6) ? function (object, key, value) {
            return dP.f(object, key, createDesc(1, value));
        } : function (object, key, value) {
            object[key] = value;
            return object;
        };


        /***/ }),
    /* 5 */
    /***/ (function(module, exports) {

        module.exports = {};


        /***/ }),
    /* 6 */
    /***/ (function(module, exports, __webpack_require__) {

// Thank's IE8 for his funny defineProperty
        module.exports = !__webpack_require__(11)(function () {
            return Object.defineProperty({}, 'a', { get: function () { return 7; } }).a != 7;
        });


        /***/ }),
    /* 7 */
    /***/ (function(module, exports, __webpack_require__) {

        var global = __webpack_require__(2);
        var core = __webpack_require__(0);
        var ctx = __webpack_require__(19);
        var hide = __webpack_require__(4);
        var has = __webpack_require__(10);
        var PROTOTYPE = 'prototype';

        var $export = function (type, name, source) {
            var IS_FORCED = type & $export.F;
            var IS_GLOBAL = type & $export.G;
            var IS_STATIC = type & $export.S;
            var IS_PROTO = type & $export.P;
            var IS_BIND = type & $export.B;
            var IS_WRAP = type & $export.W;
            var exports = IS_GLOBAL ? core : core[name] || (core[name] = {});
            var expProto = exports[PROTOTYPE];
            var target = IS_GLOBAL ? global : IS_STATIC ? global[name] : (global[name] || {})[PROTOTYPE];
            var key, own, out;
            if (IS_GLOBAL) source = name;
            for (key in source) {
                // contains in native
                own = !IS_FORCED && target && target[key] !== undefined;
                if (own && has(exports, key)) continue;
                // export native or passed
                out = own ? target[key] : source[key];
                // prevent global pollution for namespaces
                exports[key] = IS_GLOBAL && typeof target[key] != 'function' ? source[key]
                    // bind timers to global for call from export context
                    : IS_BIND && own ? ctx(out, global)
                        // wrap global constructors for prevent change them in library
                        : IS_WRAP && target[key] == out ? (function (C) {
                            var F = function (a, b, c) {
                                if (this instanceof C) {
                                    switch (arguments.length) {
                                        case 0: return new C();
                                        case 1: return new C(a);
                                        case 2: return new C(a, b);
                                    } return new C(a, b, c);
                                } return C.apply(this, arguments);
                            };
                            F[PROTOTYPE] = C[PROTOTYPE];
                            return F;
                            // make static versions for prototype methods
                        })(out) : IS_PROTO && typeof out == 'function' ? ctx(Function.call, out) : out;
                // export proto methods to core.%CONSTRUCTOR%.methods.%NAME%
                if (IS_PROTO) {
                    (exports.virtual || (exports.virtual = {}))[key] = out;
                    // export proto methods to core.%CONSTRUCTOR%.prototype.%NAME%
                    if (type & $export.R && expProto && !expProto[key]) hide(expProto, key, out);
                }
            }
        };
// type bitmap
        $export.F = 1;   // forced
        $export.G = 2;   // global
        $export.S = 4;   // static
        $export.P = 8;   // proto
        $export.B = 16;  // bind
        $export.W = 32;  // wrap
        $export.U = 64;  // safe
        $export.R = 128; // real proto method for `library`
        module.exports = $export;


        /***/ }),
    /* 8 */
    /***/ (function(module, exports, __webpack_require__) {

        var anObject = __webpack_require__(3);
        var IE8_DOM_DEFINE = __webpack_require__(42);
        var toPrimitive = __webpack_require__(43);
        var dP = Object.defineProperty;

        exports.f = __webpack_require__(6) ? Object.defineProperty : function defineProperty(O, P, Attributes) {
            anObject(O);
            P = toPrimitive(P, true);
            anObject(Attributes);
            if (IE8_DOM_DEFINE) try {
                return dP(O, P, Attributes);
            } catch (e) { /* empty */ }
            if ('get' in Attributes || 'set' in Attributes) throw TypeError('Accessors not supported!');
            if ('value' in Attributes) O[P] = Attributes.value;
            return O;
        };


        /***/ }),
    /* 9 */
    /***/ (function(module, exports) {

        module.exports = function (it) {
            return typeof it === 'object' ? it !== null : typeof it === 'function';
        };


        /***/ }),
    /* 10 */
    /***/ (function(module, exports) {

        var hasOwnProperty = {}.hasOwnProperty;
        module.exports = function (it, key) {
            return hasOwnProperty.call(it, key);
        };


        /***/ }),
    /* 11 */
    /***/ (function(module, exports) {

        module.exports = function (exec) {
            try {
                return !!exec();
            } catch (e) {
                return true;
            }
        };


        /***/ }),
    /* 12 */
    /***/ (function(module, exports) {

// 7.1.4 ToInteger
        var ceil = Math.ceil;
        var floor = Math.floor;
        module.exports = function (it) {
            return isNaN(it = +it) ? 0 : (it > 0 ? floor : ceil)(it);
        };


        /***/ }),
    /* 13 */
    /***/ (function(module, exports) {

// 7.2.1 RequireObjectCoercible(argument)
        module.exports = function (it) {
            if (it == undefined) throw TypeError("Can't call method on  " + it);
            return it;
        };


        /***/ }),
    /* 14 */
    /***/ (function(module, exports, __webpack_require__) {

// to indexed object, toObject with fallback for non-array-like ES3 strings
        var IObject = __webpack_require__(32);
        var defined = __webpack_require__(13);
        module.exports = function (it) {
            return IObject(defined(it));
        };


        /***/ }),
    /* 15 */
    /***/ (function(module, exports) {

        var toString = {}.toString;

        module.exports = function (it) {
            return toString.call(it).slice(8, -1);
        };


        /***/ }),
    /* 16 */
    /***/ (function(module, exports, __webpack_require__) {

        var shared = __webpack_require__(29)('keys');
        var uid = __webpack_require__(30);
        module.exports = function (key) {
            return shared[key] || (shared[key] = uid(key));
        };


        /***/ }),
    /* 17 */
    /***/ (function(module, exports, __webpack_require__) {

        "use strict";

        var $at = __webpack_require__(41)(true);

// 21.1.3.27 String.prototype[@@iterator]()
        __webpack_require__(27)(String, 'String', function (iterated) {
            this._t = String(iterated); // target
            this._i = 0;                // next index
// 21.1.5.2.1 %StringIteratorPrototype%.next()
        }, function () {
            var O = this._t;
            var index = this._i;
            var point;
            if (index >= O.length) return { value: undefined, done: true };
            point = $at(O, index);
            this._i += point.length;
            return { value: point, done: false };
        });


        /***/ }),
    /* 18 */
    /***/ (function(module, exports) {

        module.exports = true;


        /***/ }),
    /* 19 */
    /***/ (function(module, exports, __webpack_require__) {

// optional / simple context binding
        var aFunction = __webpack_require__(20);
        module.exports = function (fn, that, length) {
            aFunction(fn);
            if (that === undefined) return fn;
            switch (length) {
                case 1: return function (a) {
                    return fn.call(that, a);
                };
                case 2: return function (a, b) {
                    return fn.call(that, a, b);
                };
                case 3: return function (a, b, c) {
                    return fn.call(that, a, b, c);
                };
            }
            return function (/* ...args */) {
                return fn.apply(that, arguments);
            };
        };


        /***/ }),
    /* 20 */
    /***/ (function(module, exports) {

        module.exports = function (it) {
            if (typeof it != 'function') throw TypeError(it + ' is not a function!');
            return it;
        };


        /***/ }),
    /* 21 */
    /***/ (function(module, exports, __webpack_require__) {

        var isObject = __webpack_require__(9);
        var document = __webpack_require__(2).document;
// typeof document.createElement is 'object' in old IE
        var is = isObject(document) && isObject(document.createElement);
        module.exports = function (it) {
            return is ? document.createElement(it) : {};
        };


        /***/ }),
    /* 22 */
    /***/ (function(module, exports, __webpack_require__) {

// 19.1.2.14 / 15.2.3.14 Object.keys(O)
        var $keys = __webpack_require__(48);
        var enumBugKeys = __webpack_require__(31);

        module.exports = Object.keys || function keys(O) {
            return $keys(O, enumBugKeys);
        };


        /***/ }),
    /* 23 */
    /***/ (function(module, exports, __webpack_require__) {

        var def = __webpack_require__(8).f;
        var has = __webpack_require__(10);
        var TAG = __webpack_require__(1)('toStringTag');

        module.exports = function (it, tag, stat) {
            if (it && !has(it = stat ? it : it.prototype, TAG)) def(it, TAG, { configurable: true, value: tag });
        };


        /***/ }),
    /* 24 */
    /***/ (function(module, exports, __webpack_require__) {

// 7.1.13 ToObject(argument)
        var defined = __webpack_require__(13);
        module.exports = function (it) {
            return Object(defined(it));
        };


        /***/ }),
    /* 25 */
    /***/ (function(module, exports, __webpack_require__) {

        __webpack_require__(52);
        var global = __webpack_require__(2);
        var hide = __webpack_require__(4);
        var Iterators = __webpack_require__(5);
        var TO_STRING_TAG = __webpack_require__(1)('toStringTag');

        var DOMIterables = ('CSSRuleList,CSSStyleDeclaration,CSSValueList,ClientRectList,DOMRectList,DOMStringList,' +
            'DOMTokenList,DataTransferItemList,FileList,HTMLAllCollection,HTMLCollection,HTMLFormElement,HTMLSelectElement,' +
            'MediaList,MimeTypeArray,NamedNodeMap,NodeList,PaintRequestList,Plugin,PluginArray,SVGLengthList,SVGNumberList,' +
            'SVGPathSegList,SVGPointList,SVGStringList,SVGTransformList,SourceBufferList,StyleSheetList,TextTrackCueList,' +
            'TextTrackList,TouchList').split(',');

        for (var i = 0; i < DOMIterables.length; i++) {
            var NAME = DOMIterables[i];
            var Collection = global[NAME];
            var proto = Collection && Collection.prototype;
            if (proto && !proto[TO_STRING_TAG]) hide(proto, TO_STRING_TAG, NAME);
            Iterators[NAME] = Iterators.Array;
        }


        /***/ }),
    /* 26 */
    /***/ (function(module, exports, __webpack_require__) {

// getting tag from 19.1.3.6 Object.prototype.toString()
        var cof = __webpack_require__(15);
        var TAG = __webpack_require__(1)('toStringTag');
// ES3 wrong here
        var ARG = cof(function () { return arguments; }()) == 'Arguments';

// fallback for IE11 Script Access Denied error
        var tryGet = function (it, key) {
            try {
                return it[key];
            } catch (e) { /* empty */ }
        };

        module.exports = function (it) {
            var O, T, B;
            return it === undefined ? 'Undefined' : it === null ? 'Null'
                // @@toStringTag case
                : typeof (T = tryGet(O = Object(it), TAG)) == 'string' ? T
                    // builtinTag case
                    : ARG ? cof(O)
                        // ES3 arguments fallback
                        : (B = cof(O)) == 'Object' && typeof O.callee == 'function' ? 'Arguments' : B;
        };


        /***/ }),
    /* 27 */
    /***/ (function(module, exports, __webpack_require__) {

        "use strict";

        var LIBRARY = __webpack_require__(18);
        var $export = __webpack_require__(7);
        var redefine = __webpack_require__(44);
        var hide = __webpack_require__(4);
        var Iterators = __webpack_require__(5);
        var $iterCreate = __webpack_require__(45);
        var setToStringTag = __webpack_require__(23);
        var getPrototypeOf = __webpack_require__(51);
        var ITERATOR = __webpack_require__(1)('iterator');
        var BUGGY = !([].keys && 'next' in [].keys()); // Safari has buggy iterators w/o `next`
        var FF_ITERATOR = '@@iterator';
        var KEYS = 'keys';
        var VALUES = 'values';

        var returnThis = function () { return this; };

        module.exports = function (Base, NAME, Constructor, next, DEFAULT, IS_SET, FORCED) {
            $iterCreate(Constructor, NAME, next);
            var getMethod = function (kind) {
                if (!BUGGY && kind in proto) return proto[kind];
                switch (kind) {
                    case KEYS: return function keys() { return new Constructor(this, kind); };
                    case VALUES: return function values() { return new Constructor(this, kind); };
                } return function entries() { return new Constructor(this, kind); };
            };
            var TAG = NAME + ' Iterator';
            var DEF_VALUES = DEFAULT == VALUES;
            var VALUES_BUG = false;
            var proto = Base.prototype;
            var $native = proto[ITERATOR] || proto[FF_ITERATOR] || DEFAULT && proto[DEFAULT];
            var $default = $native || getMethod(DEFAULT);
            var $entries = DEFAULT ? !DEF_VALUES ? $default : getMethod('entries') : undefined;
            var $anyNative = NAME == 'Array' ? proto.entries || $native : $native;
            var methods, key, IteratorPrototype;
            // Fix native
            if ($anyNative) {
                IteratorPrototype = getPrototypeOf($anyNative.call(new Base()));
                if (IteratorPrototype !== Object.prototype && IteratorPrototype.next) {
                    // Set @@toStringTag to native iterators
                    setToStringTag(IteratorPrototype, TAG, true);
                    // fix for some old engines
                    if (!LIBRARY && typeof IteratorPrototype[ITERATOR] != 'function') hide(IteratorPrototype, ITERATOR, returnThis);
                }
            }
            // fix Array#{values, @@iterator}.name in V8 / FF
            if (DEF_VALUES && $native && $native.name !== VALUES) {
                VALUES_BUG = true;
                $default = function values() { return $native.call(this); };
            }
            // Define iterator
            if ((!LIBRARY || FORCED) && (BUGGY || VALUES_BUG || !proto[ITERATOR])) {
                hide(proto, ITERATOR, $default);
            }
            // Plug for library
            Iterators[NAME] = $default;
            Iterators[TAG] = returnThis;
            if (DEFAULT) {
                methods = {
                    values: DEF_VALUES ? $default : getMethod(VALUES),
                    keys: IS_SET ? $default : getMethod(KEYS),
                    entries: $entries
                };
                if (FORCED) for (key in methods) {
                    if (!(key in proto)) redefine(proto, key, methods[key]);
                } else $export($export.P + $export.F * (BUGGY || VALUES_BUG), NAME, methods);
            }
            return methods;
        };


        /***/ }),
    /* 28 */
    /***/ (function(module, exports) {

        module.exports = function (bitmap, value) {
            return {
                enumerable: !(bitmap & 1),
                configurable: !(bitmap & 2),
                writable: !(bitmap & 4),
                value: value
            };
        };


        /***/ }),
    /* 29 */
    /***/ (function(module, exports, __webpack_require__) {

        var core = __webpack_require__(0);
        var global = __webpack_require__(2);
        var SHARED = '__core-js_shared__';
        var store = global[SHARED] || (global[SHARED] = {});

        (module.exports = function (key, value) {
            return store[key] || (store[key] = value !== undefined ? value : {});
        })('versions', []).push({
            version: core.version,
            mode: __webpack_require__(18) ? 'pure' : 'global',
            copyright: '© 2018 Denis Pushkarev (zloirock.ru)'
        });


        /***/ }),
    /* 30 */
    /***/ (function(module, exports) {

        var id = 0;
        var px = Math.random();
        module.exports = function (key) {
            return 'Symbol('.concat(key === undefined ? '' : key, ')_', (++id + px).toString(36));
        };


        /***/ }),
    /* 31 */
    /***/ (function(module, exports) {

// IE 8- don't enum bug keys
        module.exports = (
            'constructor,hasOwnProperty,isPrototypeOf,propertyIsEnumerable,toLocaleString,toString,valueOf'
        ).split(',');


        /***/ }),
    /* 32 */
    /***/ (function(module, exports, __webpack_require__) {

// fallback for non-array-like ES3 and non-enumerable old V8 strings
        var cof = __webpack_require__(15);
// eslint-disable-next-line no-prototype-builtins
        module.exports = Object('z').propertyIsEnumerable(0) ? Object : function (it) {
            return cof(it) == 'String' ? it.split('') : Object(it);
        };


        /***/ }),
    /* 33 */
    /***/ (function(module, exports, __webpack_require__) {

        module.exports = { "default": __webpack_require__(61), __esModule: true };

        /***/ }),
    /* 34 */
    /***/ (function(module, exports, __webpack_require__) {

// 7.1.15 ToLength
        var toInteger = __webpack_require__(12);
        var min = Math.min;
        module.exports = function (it) {
            return it > 0 ? min(toInteger(it), 0x1fffffffffffff) : 0; // pow(2, 53) - 1 == 9007199254740991
        };


        /***/ }),
    /* 35 */
    /***/ (function(module, exports, __webpack_require__) {

        var document = __webpack_require__(2).document;
        module.exports = document && document.documentElement;


        /***/ }),
    /* 36 */
    /***/ (function(module, exports, __webpack_require__) {

        var classof = __webpack_require__(26);
        var ITERATOR = __webpack_require__(1)('iterator');
        var Iterators = __webpack_require__(5);
        module.exports = __webpack_require__(0).getIteratorMethod = function (it) {
            if (it != undefined) return it[ITERATOR]
                || it['@@iterator']
                || Iterators[classof(it)];
        };


        /***/ }),
    /* 37 */
    /***/ (function(module, exports, __webpack_require__) {

        "use strict";


        exports.__esModule = true;

        var _assign = __webpack_require__(38);

        var _assign2 = _interopRequireDefault(_assign);

        function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

        exports.default = _assign2.default || function (target) {
            for (var i = 1; i < arguments.length; i++) {
                var source = arguments[i];

                for (var key in source) {
                    if (Object.prototype.hasOwnProperty.call(source, key)) {
                        target[key] = source[key];
                    }
                }
            }

            return target;
        };

        /***/ }),
    /* 38 */
    /***/ (function(module, exports, __webpack_require__) {

        module.exports = { "default": __webpack_require__(70), __esModule: true };

        /***/ }),
    /* 39 */
    /***/ (function(module, exports, __webpack_require__) {

        module.exports = { "default": __webpack_require__(79), __esModule: true };

        /***/ }),
    /* 40 */
    /***/ (function(module, exports, __webpack_require__) {

        "use strict";


        Object.defineProperty(exports, "__esModule", {
            value: true
        });
        exports.extractOrderOptions = exports.getParams = exports.merge = undefined;

        var _slicedToArray2 = __webpack_require__(57);

        var _slicedToArray3 = _interopRequireDefault(_slicedToArray2);

        var _keys = __webpack_require__(39);

        var _keys2 = _interopRequireDefault(_keys);

        var _getIterator2 = __webpack_require__(33);

        var _getIterator3 = _interopRequireDefault(_getIterator2);

        var _assign = __webpack_require__(38);

        var _assign2 = _interopRequireDefault(_assign);

        function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

// Merge a `source` object to a `target` recursively
        var merge = exports.merge = function merge(target, source) {
            // Iterate through `source` properties and if an `Object` set property to merge of `target` and `source` properties
            var _iteratorNormalCompletion = true;
            var _didIteratorError = false;
            var _iteratorError = undefined;

            try {
                for (var _iterator = (0, _getIterator3.default)((0, _keys2.default)(source)), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
                    var key = _step.value;

                    if (source[key] instanceof Object) (0, _assign2.default)(source[key], merge(target[key], source[key]));
                }

                // Join `target` and modified `source`
            } catch (err) {
                _didIteratorError = true;
                _iteratorError = err;
            } finally {
                try {
                    if (!_iteratorNormalCompletion && _iterator.return) {
                        _iterator.return();
                    }
                } finally {
                    if (_didIteratorError) {
                        throw _iteratorError;
                    }
                }
            }

            (0, _assign2.default)(target || {}, source);
            return target;
        };

        var getParams = exports.getParams = function getParams(query) {
            if (!query) {
                return {};
            }

            return (/^[?#]/.test(query) ? query.slice(1) : query).split('&').reduce(function (params, param) {
                var _param$split = param.split('='),
                    _param$split2 = (0, _slicedToArray3.default)(_param$split, 2),
                    key = _param$split2[0],
                    value = _param$split2[1];

                params[key] = value ? decodeURIComponent(value.replace(/\+/g, ' ')) : '';
                return params;
            }, {});
        };

        var extractOrderOptions = exports.extractOrderOptions = function extractOrderOptions(url) {
            var _url = new URL(url);
            var query = _url.search;

            var _getParams = getParams(query),
                merchantID = _getParams.m,
                token = _getParams.t;

            return { merchantID: merchantID, token: token };
        };

        /***/ }),
    /* 41 */
    /***/ (function(module, exports, __webpack_require__) {

        var toInteger = __webpack_require__(12);
        var defined = __webpack_require__(13);
// true  -> String#at
// false -> String#codePointAt
        module.exports = function (TO_STRING) {
            return function (that, pos) {
                var s = String(defined(that));
                var i = toInteger(pos);
                var l = s.length;
                var a, b;
                if (i < 0 || i >= l) return TO_STRING ? '' : undefined;
                a = s.charCodeAt(i);
                return a < 0xd800 || a > 0xdbff || i + 1 === l || (b = s.charCodeAt(i + 1)) < 0xdc00 || b > 0xdfff
                    ? TO_STRING ? s.charAt(i) : a
                    : TO_STRING ? s.slice(i, i + 2) : (a - 0xd800 << 10) + (b - 0xdc00) + 0x10000;
            };
        };


        /***/ }),
    /* 42 */
    /***/ (function(module, exports, __webpack_require__) {

        module.exports = !__webpack_require__(6) && !__webpack_require__(11)(function () {
            return Object.defineProperty(__webpack_require__(21)('div'), 'a', { get: function () { return 7; } }).a != 7;
        });


        /***/ }),
    /* 43 */
    /***/ (function(module, exports, __webpack_require__) {

// 7.1.1 ToPrimitive(input [, PreferredType])
        var isObject = __webpack_require__(9);
// instead of the ES6 spec version, we didn't implement @@toPrimitive case
// and the second argument - flag - preferred type is a string
        module.exports = function (it, S) {
            if (!isObject(it)) return it;
            var fn, val;
            if (S && typeof (fn = it.toString) == 'function' && !isObject(val = fn.call(it))) return val;
            if (typeof (fn = it.valueOf) == 'function' && !isObject(val = fn.call(it))) return val;
            if (!S && typeof (fn = it.toString) == 'function' && !isObject(val = fn.call(it))) return val;
            throw TypeError("Can't convert object to primitive value");
        };


        /***/ }),
    /* 44 */
    /***/ (function(module, exports, __webpack_require__) {

        module.exports = __webpack_require__(4);


        /***/ }),
    /* 45 */
    /***/ (function(module, exports, __webpack_require__) {

        "use strict";

        var create = __webpack_require__(46);
        var descriptor = __webpack_require__(28);
        var setToStringTag = __webpack_require__(23);
        var IteratorPrototype = {};

// 25.1.2.1.1 %IteratorPrototype%[@@iterator]()
        __webpack_require__(4)(IteratorPrototype, __webpack_require__(1)('iterator'), function () { return this; });

        module.exports = function (Constructor, NAME, next) {
            Constructor.prototype = create(IteratorPrototype, { next: descriptor(1, next) });
            setToStringTag(Constructor, NAME + ' Iterator');
        };


        /***/ }),
    /* 46 */
    /***/ (function(module, exports, __webpack_require__) {

// 19.1.2.2 / 15.2.3.5 Object.create(O [, Properties])
        var anObject = __webpack_require__(3);
        var dPs = __webpack_require__(47);
        var enumBugKeys = __webpack_require__(31);
        var IE_PROTO = __webpack_require__(16)('IE_PROTO');
        var Empty = function () { /* empty */ };
        var PROTOTYPE = 'prototype';

// Create object with fake `null` prototype: use iframe Object with cleared prototype
        var createDict = function () {
            // Thrash, waste and sodomy: IE GC bug
            var iframe = __webpack_require__(21)('iframe');
            var i = enumBugKeys.length;
            var lt = '<';
            var gt = '>';
            var iframeDocument;
            iframe.style.display = 'none';
            __webpack_require__(35).appendChild(iframe);
            iframe.src = 'javascript:'; // eslint-disable-line no-script-url
            // createDict = iframe.contentWindow.Object;
            // html.removeChild(iframe);
            iframeDocument = iframe.contentWindow.document;
            iframeDocument.open();
            iframeDocument.write(lt + 'script' + gt + 'document.F=Object' + lt + '/script' + gt);
            iframeDocument.close();
            createDict = iframeDocument.F;
            while (i--) delete createDict[PROTOTYPE][enumBugKeys[i]];
            return createDict();
        };

        module.exports = Object.create || function create(O, Properties) {
            var result;
            if (O !== null) {
                Empty[PROTOTYPE] = anObject(O);
                result = new Empty();
                Empty[PROTOTYPE] = null;
                // add "__proto__" for Object.getPrototypeOf polyfill
                result[IE_PROTO] = O;
            } else result = createDict();
            return Properties === undefined ? result : dPs(result, Properties);
        };


        /***/ }),
    /* 47 */
    /***/ (function(module, exports, __webpack_require__) {

        var dP = __webpack_require__(8);
        var anObject = __webpack_require__(3);
        var getKeys = __webpack_require__(22);

        module.exports = __webpack_require__(6) ? Object.defineProperties : function defineProperties(O, Properties) {
            anObject(O);
            var keys = getKeys(Properties);
            var length = keys.length;
            var i = 0;
            var P;
            while (length > i) dP.f(O, P = keys[i++], Properties[P]);
            return O;
        };


        /***/ }),
    /* 48 */
    /***/ (function(module, exports, __webpack_require__) {

        var has = __webpack_require__(10);
        var toIObject = __webpack_require__(14);
        var arrayIndexOf = __webpack_require__(49)(false);
        var IE_PROTO = __webpack_require__(16)('IE_PROTO');

        module.exports = function (object, names) {
            var O = toIObject(object);
            var i = 0;
            var result = [];
            var key;
            for (key in O) if (key != IE_PROTO) has(O, key) && result.push(key);
            // Don't enum bug & hidden keys
            while (names.length > i) if (has(O, key = names[i++])) {
                ~arrayIndexOf(result, key) || result.push(key);
            }
            return result;
        };


        /***/ }),
    /* 49 */
    /***/ (function(module, exports, __webpack_require__) {

// false -> Array#indexOf
// true  -> Array#includes
        var toIObject = __webpack_require__(14);
        var toLength = __webpack_require__(34);
        var toAbsoluteIndex = __webpack_require__(50);
        module.exports = function (IS_INCLUDES) {
            return function ($this, el, fromIndex) {
                var O = toIObject($this);
                var length = toLength(O.length);
                var index = toAbsoluteIndex(fromIndex, length);
                var value;
                // Array#includes uses SameValueZero equality algorithm
                // eslint-disable-next-line no-self-compare
                if (IS_INCLUDES && el != el) while (length > index) {
                    value = O[index++];
                    // eslint-disable-next-line no-self-compare
                    if (value != value) return true;
                    // Array#indexOf ignores holes, Array#includes - not
                } else for (;length > index; index++) if (IS_INCLUDES || index in O) {
                    if (O[index] === el) return IS_INCLUDES || index || 0;
                } return !IS_INCLUDES && -1;
            };
        };


        /***/ }),
    /* 50 */
    /***/ (function(module, exports, __webpack_require__) {

        var toInteger = __webpack_require__(12);
        var max = Math.max;
        var min = Math.min;
        module.exports = function (index, length) {
            index = toInteger(index);
            return index < 0 ? max(index + length, 0) : min(index, length);
        };


        /***/ }),
    /* 51 */
    /***/ (function(module, exports, __webpack_require__) {

// 19.1.2.9 / 15.2.3.2 Object.getPrototypeOf(O)
        var has = __webpack_require__(10);
        var toObject = __webpack_require__(24);
        var IE_PROTO = __webpack_require__(16)('IE_PROTO');
        var ObjectProto = Object.prototype;

        module.exports = Object.getPrototypeOf || function (O) {
            O = toObject(O);
            if (has(O, IE_PROTO)) return O[IE_PROTO];
            if (typeof O.constructor == 'function' && O instanceof O.constructor) {
                return O.constructor.prototype;
            } return O instanceof Object ? ObjectProto : null;
        };


        /***/ }),
    /* 52 */
    /***/ (function(module, exports, __webpack_require__) {

        "use strict";

        var addToUnscopables = __webpack_require__(53);
        var step = __webpack_require__(54);
        var Iterators = __webpack_require__(5);
        var toIObject = __webpack_require__(14);

// 22.1.3.4 Array.prototype.entries()
// 22.1.3.13 Array.prototype.keys()
// 22.1.3.29 Array.prototype.values()
// 22.1.3.30 Array.prototype[@@iterator]()
        module.exports = __webpack_require__(27)(Array, 'Array', function (iterated, kind) {
            this._t = toIObject(iterated); // target
            this._i = 0;                   // next index
            this._k = kind;                // kind
// 22.1.5.2.1 %ArrayIteratorPrototype%.next()
        }, function () {
            var O = this._t;
            var kind = this._k;
            var index = this._i++;
            if (!O || index >= O.length) {
                this._t = undefined;
                return step(1);
            }
            if (kind == 'keys') return step(0, index);
            if (kind == 'values') return step(0, O[index]);
            return step(0, [index, O[index]]);
        }, 'values');

// argumentsList[@@iterator] is %ArrayProto_values% (9.4.4.6, 9.4.4.7)
        Iterators.Arguments = Iterators.Array;

        addToUnscopables('keys');
        addToUnscopables('values');
        addToUnscopables('entries');


        /***/ }),
    /* 53 */
    /***/ (function(module, exports) {

        module.exports = function () { /* empty */ };


        /***/ }),
    /* 54 */
    /***/ (function(module, exports) {

        module.exports = function (done, value) {
            return { value: value, done: !!done };
        };


        /***/ }),
    /* 55 */
    /***/ (function(module, exports, __webpack_require__) {

        "use strict";

// 25.4.1.5 NewPromiseCapability(C)
        var aFunction = __webpack_require__(20);

        function PromiseCapability(C) {
            var resolve, reject;
            this.promise = new C(function ($$resolve, $$reject) {
                if (resolve !== undefined || reject !== undefined) throw TypeError('Bad Promise constructor');
                resolve = $$resolve;
                reject = $$reject;
            });
            this.resolve = aFunction(resolve);
            this.reject = aFunction(reject);
        }

        module.exports.f = function (C) {
            return new PromiseCapability(C);
        };


        /***/ }),
    /* 56 */
    /***/ (function(module, exports, __webpack_require__) {

        "use strict";


        Object.defineProperty(exports, "__esModule", {
            value: true
        });

        var _defineProperty2 = __webpack_require__(75);

        var _defineProperty3 = _interopRequireDefault(_defineProperty2);

        var _extends3 = __webpack_require__(37);

        var _extends4 = _interopRequireDefault(_extends3);

        var _keys = __webpack_require__(39);

        var _keys2 = _interopRequireDefault(_keys);

        var _basil = __webpack_require__(82);

        var _basil2 = _interopRequireDefault(_basil);

        var _utils = __webpack_require__(40);

        function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

        var basil = new _basil2.default({ namespace: 'oyst-config' });

        var getEnhancedTypeOf = function getEnhancedTypeOf(value) {
            return Object.prototype.toString.call(value);
        };

        /** Cache strategies
         *
         *  These strategies are applied to every configuration item.
         *  If a configuration item is empty, it will always be stored, no matter what its source is.
         *  (i.e. if an item is affected to `local` strategy, an API call is still authorized to bootstrap it)
         *
         *  When a value is stored for an item, though, the cache strategies begin to apply, depending on the source:
         * - plugin => Always trust the data-attributes set by the plugin
         * - local => Always trust the data which is stored in localStorage (never refresh from API)
         * - api => Always trust the data coming from an API call (always refresh from API)
         **/
        var cacheStrategy = {
            'oneclickURL': 'api',
            'options.button': {
                'context': 'api',
                'sticky': 'api',
                '*': 'plugin'
            },
            'options.customization': 'api',
            'options.discounts': 'api',
            'options.merchant': 'api',
            'options.order': {
                'merchantID': 'local',
                '*': 'api'
            },
            'options.user': {
                'isAuthenticated': 'api',
                '*': 'local'
            }
        };

        var getCacheStrategyFor = function getCacheStrategyFor(source) {
            return cacheStrategy[source];
        };

        var shouldUpdateStore = function shouldUpdateStore(_ref) {
            var key = _ref.key,
                source = _ref.source,
                subKey = _ref.subKey;

            var storedData = get(key);
            if (!storedData) {
                return true;
            }
            var cacheStrategy = getCacheStrategyFor(key);
            if (getEnhancedTypeOf(cacheStrategy) === '[object Object]') {
                var subStrategy = cacheStrategy[subKey] ? cacheStrategy[subKey] : cacheStrategy['*'];
                return !storedData[subKey] || subStrategy === source;
            }
            return cacheStrategy === source;
        };

        var get = function get(key) {
            return basil.get(key);
        };

        var set = function set(_ref2) {
            var key = _ref2.key,
                source = _ref2.source,
                value = _ref2.value;

            if (getEnhancedTypeOf(value) === '[object Object]') {
                return (0, _keys2.default)(value).forEach(function (subKey) {
                    shouldUpdateStore({ key: key, source: source, subKey: subKey }) && basil.set(key, (0, _extends4.default)({}, basil.get(key), (0, _defineProperty3.default)({}, subKey, value[subKey])));
                });
            }
            return shouldUpdateStore({ key: key, source: source }) && basil.set(key, value);
        };

        var isSticky = function isSticky(merchantOptions, sessionOptions) {
            var is_sticky_button_cart_page_enabled = merchantOptions.is_sticky_button_cart_page_enabled,
                is_sticky_button_product_page_enabled = merchantOptions.is_sticky_button_product_page_enabled;

            var _ref3 = sessionOptions.order || {},
                is_cart_checkout = _ref3.is_cart_checkout;

            return is_cart_checkout ? !!is_sticky_button_cart_page_enabled : !!is_sticky_button_product_page_enabled;
        };

        var handleBootstrap = function handleBootstrap(data, source) {
            set({ key: 'options.discounts', value: data.discounts, source: source });
            set({ key: 'options.merchant', value: data.merchant, source: source });
            set({ key: 'options.button', value: {
                    context: data.session.order.is_cart_checkout ? 'cart' : 'product',
                    sticky: isSticky(data.merchant, data.session) ? 'true' : 'false'
                }, source: source });
            set({ key: 'options.customization', value: data.session.customization, source: source });
            set({ key: 'options.order', value: data.session.order, source: source });
            set({ key: 'options.user', value: (0, _extends4.default)({ isAuthenticated: data.is_authenticated }, data.session.user), source: source });
        };

        var handleOneclickURL = function handleOneclickURL(url, source) {
            set({ key: 'oneclickURL', value: url, source: source });
            set({ key: 'options.order', value: (0, _utils.extractOrderOptions)(url), source: source });
        };

        var keysMap = function keysMap() {
            return basil.keysMap();
        };

        exports.default = {
            get: get,
            getCacheStrategyFor: getCacheStrategyFor,
            handleBootstrap: handleBootstrap,
            handleOneclickURL: handleOneclickURL,
            keysMap: keysMap,
            set: set
        };

        /***/ }),
    /* 57 */
    /***/ (function(module, exports, __webpack_require__) {

        "use strict";


        exports.__esModule = true;

        var _isIterable2 = __webpack_require__(58);

        var _isIterable3 = _interopRequireDefault(_isIterable2);

        var _getIterator2 = __webpack_require__(33);

        var _getIterator3 = _interopRequireDefault(_getIterator2);

        function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

        exports.default = function () {
            function sliceIterator(arr, i) {
                var _arr = [];
                var _n = true;
                var _d = false;
                var _e = undefined;

                try {
                    for (var _i = (0, _getIterator3.default)(arr), _s; !(_n = (_s = _i.next()).done); _n = true) {
                        _arr.push(_s.value);

                        if (i && _arr.length === i) break;
                    }
                } catch (err) {
                    _d = true;
                    _e = err;
                } finally {
                    try {
                        if (!_n && _i["return"]) _i["return"]();
                    } finally {
                        if (_d) throw _e;
                    }
                }

                return _arr;
            }

            return function (arr, i) {
                if (Array.isArray(arr)) {
                    return arr;
                } else if ((0, _isIterable3.default)(Object(arr))) {
                    return sliceIterator(arr, i);
                } else {
                    throw new TypeError("Invalid attempt to destructure non-iterable instance");
                }
            };
        }();

        /***/ }),
    /* 58 */
    /***/ (function(module, exports, __webpack_require__) {

        module.exports = { "default": __webpack_require__(59), __esModule: true };

        /***/ }),
    /* 59 */
    /***/ (function(module, exports, __webpack_require__) {

        __webpack_require__(25);
        __webpack_require__(17);
        module.exports = __webpack_require__(60);


        /***/ }),
    /* 60 */
    /***/ (function(module, exports, __webpack_require__) {

        var classof = __webpack_require__(26);
        var ITERATOR = __webpack_require__(1)('iterator');
        var Iterators = __webpack_require__(5);
        module.exports = __webpack_require__(0).isIterable = function (it) {
            var O = Object(it);
            return O[ITERATOR] !== undefined
                || '@@iterator' in O
                // eslint-disable-next-line no-prototype-builtins
                || Iterators.hasOwnProperty(classof(O));
        };


        /***/ }),
    /* 61 */
    /***/ (function(module, exports, __webpack_require__) {

        __webpack_require__(25);
        __webpack_require__(17);
        module.exports = __webpack_require__(62);


        /***/ }),
    /* 62 */
    /***/ (function(module, exports, __webpack_require__) {

        var anObject = __webpack_require__(3);
        var get = __webpack_require__(36);
        module.exports = __webpack_require__(0).getIterator = function (it) {
            var iterFn = get(it);
            if (typeof iterFn != 'function') throw TypeError(it + ' is not iterable!');
            return anObject(iterFn.call(it));
        };


        /***/ }),
    /* 63 */
    /***/ (function(module, exports, __webpack_require__) {

        module.exports = __webpack_require__(86);


        /***/ }),
    /* 64 */
    /***/ (function(module, exports, __webpack_require__) {

        "use strict";


        exports.__esModule = true;

        var _promise = __webpack_require__(65);

        var _promise2 = _interopRequireDefault(_promise);

        function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

        exports.default = function (fn) {
            return function () {
                var gen = fn.apply(this, arguments);
                return new _promise2.default(function (resolve, reject) {
                    function step(key, arg) {
                        try {
                            var info = gen[key](arg);
                            var value = info.value;
                        } catch (error) {
                            reject(error);
                            return;
                        }

                        if (info.done) {
                            resolve(value);
                        } else {
                            return _promise2.default.resolve(value).then(function (value) {
                                step("next", value);
                            }, function (err) {
                                step("throw", err);
                            });
                        }
                    }

                    return step("next");
                });
            };
        };

        /***/ }),
    /* 65 */
    /***/ (function(module, exports, __webpack_require__) {

        module.exports = { "default": __webpack_require__(88), __esModule: true };

        /***/ }),
    /* 66 */
    /***/ (function(module, exports, __webpack_require__) {

// 7.3.20 SpeciesConstructor(O, defaultConstructor)
        var anObject = __webpack_require__(3);
        var aFunction = __webpack_require__(20);
        var SPECIES = __webpack_require__(1)('species');
        module.exports = function (O, D) {
            var C = anObject(O).constructor;
            var S;
            return C === undefined || (S = anObject(C)[SPECIES]) == undefined ? D : aFunction(S);
        };


        /***/ }),
    /* 67 */
    /***/ (function(module, exports, __webpack_require__) {

        var ctx = __webpack_require__(19);
        var invoke = __webpack_require__(95);
        var html = __webpack_require__(35);
        var cel = __webpack_require__(21);
        var global = __webpack_require__(2);
        var process = global.process;
        var setTask = global.setImmediate;
        var clearTask = global.clearImmediate;
        var MessageChannel = global.MessageChannel;
        var Dispatch = global.Dispatch;
        var counter = 0;
        var queue = {};
        var ONREADYSTATECHANGE = 'onreadystatechange';
        var defer, channel, port;
        var run = function () {
            var id = +this;
            // eslint-disable-next-line no-prototype-builtins
            if (queue.hasOwnProperty(id)) {
                var fn = queue[id];
                delete queue[id];
                fn();
            }
        };
        var listener = function (event) {
            run.call(event.data);
        };
// Node.js 0.9+ & IE10+ has setImmediate, otherwise:
        if (!setTask || !clearTask) {
            setTask = function setImmediate(fn) {
                var args = [];
                var i = 1;
                while (arguments.length > i) args.push(arguments[i++]);
                queue[++counter] = function () {
                    // eslint-disable-next-line no-new-func
                    invoke(typeof fn == 'function' ? fn : Function(fn), args);
                };
                defer(counter);
                return counter;
            };
            clearTask = function clearImmediate(id) {
                delete queue[id];
            };
            // Node.js 0.8-
            if (__webpack_require__(15)(process) == 'process') {
                defer = function (id) {
                    process.nextTick(ctx(run, id, 1));
                };
                // Sphere (JS game engine) Dispatch API
            } else if (Dispatch && Dispatch.now) {
                defer = function (id) {
                    Dispatch.now(ctx(run, id, 1));
                };
                // Browsers with MessageChannel, includes WebWorkers
            } else if (MessageChannel) {
                channel = new MessageChannel();
                port = channel.port2;
                channel.port1.onmessage = listener;
                defer = ctx(port.postMessage, port, 1);
                // Browsers with postMessage, skip WebWorkers
                // IE8 has postMessage, but it's sync & typeof its postMessage is 'object'
            } else if (global.addEventListener && typeof postMessage == 'function' && !global.importScripts) {
                defer = function (id) {
                    global.postMessage(id + '', '*');
                };
                global.addEventListener('message', listener, false);
                // IE8-
            } else if (ONREADYSTATECHANGE in cel('script')) {
                defer = function (id) {
                    html.appendChild(cel('script'))[ONREADYSTATECHANGE] = function () {
                        html.removeChild(this);
                        run.call(id);
                    };
                };
                // Rest old browsers
            } else {
                defer = function (id) {
                    setTimeout(ctx(run, id, 1), 0);
                };
            }
        }
        module.exports = {
            set: setTask,
            clear: clearTask
        };


        /***/ }),
    /* 68 */
    /***/ (function(module, exports) {

        module.exports = function (exec) {
            try {
                return { e: false, v: exec() };
            } catch (e) {
                return { e: true, v: e };
            }
        };


        /***/ }),
    /* 69 */
    /***/ (function(module, exports, __webpack_require__) {

        var anObject = __webpack_require__(3);
        var isObject = __webpack_require__(9);
        var newPromiseCapability = __webpack_require__(55);

        module.exports = function (C, x) {
            anObject(C);
            if (isObject(x) && x.constructor === C) return x;
            var promiseCapability = newPromiseCapability.f(C);
            var resolve = promiseCapability.resolve;
            resolve(x);
            return promiseCapability.promise;
        };


        /***/ }),
    /* 70 */
    /***/ (function(module, exports, __webpack_require__) {

        __webpack_require__(71);
        module.exports = __webpack_require__(0).Object.assign;


        /***/ }),
    /* 71 */
    /***/ (function(module, exports, __webpack_require__) {

// 19.1.3.1 Object.assign(target, source)
        var $export = __webpack_require__(7);

        $export($export.S + $export.F, 'Object', { assign: __webpack_require__(72) });


        /***/ }),
    /* 72 */
    /***/ (function(module, exports, __webpack_require__) {

        "use strict";

// 19.1.2.1 Object.assign(target, source, ...)
        var getKeys = __webpack_require__(22);
        var gOPS = __webpack_require__(73);
        var pIE = __webpack_require__(74);
        var toObject = __webpack_require__(24);
        var IObject = __webpack_require__(32);
        var $assign = Object.assign;

// should work with symbols and should have deterministic property order (V8 bug)
        module.exports = !$assign || __webpack_require__(11)(function () {
            var A = {};
            var B = {};
            // eslint-disable-next-line no-undef
            var S = Symbol();
            var K = 'abcdefghijklmnopqrst';
            A[S] = 7;
            K.split('').forEach(function (k) { B[k] = k; });
            return $assign({}, A)[S] != 7 || Object.keys($assign({}, B)).join('') != K;
        }) ? function assign(target, source) { // eslint-disable-line no-unused-vars
            var T = toObject(target);
            var aLen = arguments.length;
            var index = 1;
            var getSymbols = gOPS.f;
            var isEnum = pIE.f;
            while (aLen > index) {
                var S = IObject(arguments[index++]);
                var keys = getSymbols ? getKeys(S).concat(getSymbols(S)) : getKeys(S);
                var length = keys.length;
                var j = 0;
                var key;
                while (length > j) if (isEnum.call(S, key = keys[j++])) T[key] = S[key];
            } return T;
        } : $assign;


        /***/ }),
    /* 73 */
    /***/ (function(module, exports) {

        exports.f = Object.getOwnPropertySymbols;


        /***/ }),
    /* 74 */
    /***/ (function(module, exports) {

        exports.f = {}.propertyIsEnumerable;


        /***/ }),
    /* 75 */
    /***/ (function(module, exports, __webpack_require__) {

        "use strict";


        exports.__esModule = true;

        var _defineProperty = __webpack_require__(76);

        var _defineProperty2 = _interopRequireDefault(_defineProperty);

        function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

        exports.default = function (obj, key, value) {
            if (key in obj) {
                (0, _defineProperty2.default)(obj, key, {
                    value: value,
                    enumerable: true,
                    configurable: true,
                    writable: true
                });
            } else {
                obj[key] = value;
            }

            return obj;
        };

        /***/ }),
    /* 76 */
    /***/ (function(module, exports, __webpack_require__) {

        module.exports = { "default": __webpack_require__(77), __esModule: true };

        /***/ }),
    /* 77 */
    /***/ (function(module, exports, __webpack_require__) {

        __webpack_require__(78);
        var $Object = __webpack_require__(0).Object;
        module.exports = function defineProperty(it, key, desc) {
            return $Object.defineProperty(it, key, desc);
        };


        /***/ }),
    /* 78 */
    /***/ (function(module, exports, __webpack_require__) {

        var $export = __webpack_require__(7);
// 19.1.2.4 / 15.2.3.6 Object.defineProperty(O, P, Attributes)
        $export($export.S + $export.F * !__webpack_require__(6), 'Object', { defineProperty: __webpack_require__(8).f });


        /***/ }),
    /* 79 */
    /***/ (function(module, exports, __webpack_require__) {

        __webpack_require__(80);
        module.exports = __webpack_require__(0).Object.keys;


        /***/ }),
    /* 80 */
    /***/ (function(module, exports, __webpack_require__) {

// 19.1.2.14 Object.keys(O)
        var toObject = __webpack_require__(24);
        var $keys = __webpack_require__(22);

        __webpack_require__(81)('keys', function () {
            return function keys(it) {
                return $keys(toObject(it));
            };
        });


        /***/ }),
    /* 81 */
    /***/ (function(module, exports, __webpack_require__) {

// most Object methods by ES6 should accept primitives
        var $export = __webpack_require__(7);
        var core = __webpack_require__(0);
        var fails = __webpack_require__(11);
        module.exports = function (KEY, exec) {
            var fn = (core.Object || {})[KEY] || Object[KEY];
            var exp = {};
            exp[KEY] = exec(fn);
            $export($export.S + $export.F * fails(function () { fn(1); }), 'Object', exp);
        };


        /***/ }),
    /* 82 */
    /***/ (function(module, exports, __webpack_require__) {

        var __WEBPACK_AMD_DEFINE_RESULT__;(function () {
            // Basil
            var Basil = function (options) {
                return Basil.utils.extend({}, Basil.plugins, new Basil.Storage().init(options));
            };

            // Version
            Basil.version = '0.4.10';

            // Utils
            Basil.utils = {
                extend: function () {
                    var destination = typeof arguments[0] === 'object' ? arguments[0] : {};
                    for (var i = 1; i < arguments.length; i++) {
                        if (arguments[i] && typeof arguments[i] === 'object')
                            for (var property in arguments[i])
                                destination[property] = arguments[i][property];
                    }
                    return destination;
                },
                each: function (obj, fnIterator, context) {
                    if (this.isArray(obj)) {
                        for (var i = 0; i < obj.length; i++)
                            if (fnIterator.call(context, obj[i], i) === false) return;
                    } else if (obj) {
                        for (var key in obj)
                            if (fnIterator.call(context, obj[key], key) === false) return;
                    }
                },
                tryEach: function (obj, fnIterator, fnError, context) {
                    this.each(obj, function (value, key) {
                        try {
                            return fnIterator.call(context, value, key);
                        } catch (error) {
                            if (this.isFunction(fnError)) {
                                try {
                                    fnError.call(context, value, key, error);
                                } catch (error) {}
                            }
                        }
                    }, this);
                },
                registerPlugin: function (methods) {
                    Basil.plugins = this.extend(methods, Basil.plugins);
                },
                getTypeOf: function (obj) {
                    if (typeof obj === 'undefined' || obj === null)
                        return '' + obj;
                    return Object.prototype.toString.call(obj).replace(/^\[object\s(.*)\]$/, function ($0, $1) { return $1.toLowerCase(); });
                }
            };

            // Add some isType methods: isArguments, isBoolean, isFunction, isString, isArray, isNumber, isDate, isRegExp, isUndefined, isNull.
            var types = ['Arguments', 'Boolean', 'Function', 'String', 'Array', 'Number', 'Date', 'RegExp', 'Undefined', 'Null'];
            for (var i = 0; i < types.length; i++) {
                Basil.utils['is' + types[i]] = (function (type) {
                    return function (obj) {
                        return Basil.utils.getTypeOf(obj) === type.toLowerCase();
                    };
                })(types[i]);
            }

            // Plugins
            Basil.plugins = {};

            // Options
            Basil.options = Basil.utils.extend({
                namespace: 'b45i1',
                storages: ['local', 'cookie', 'session', 'memory'],
                expireDays: 365,
                keyDelimiter: '.'
            }, window.Basil ? window.Basil.options : {});

            // Storage
            Basil.Storage = function () {
                var _salt = 'b45i1' + (Math.random() + 1)
                        .toString(36)
                        .substring(7),
                    _storages = {},
                    _isValidKey = function (key) {
                        var type = Basil.utils.getTypeOf(key);
                        return (type === 'string' && key) || type === 'number' || type === 'boolean';
                    },
                    _toStoragesArray = function (storages) {
                        if (Basil.utils.isArray(storages))
                            return storages;
                        return Basil.utils.isString(storages) ? [storages] : [];
                    },
                    _toStoredKey = function (namespace, path, delimiter) {
                        var key = '';
                        if (_isValidKey(path)) {
                            key += path;
                        } else if (Basil.utils.isArray(path)) {
                            path = Basil.utils.isFunction(path.filter) ? path.filter(_isValidKey) : path;
                            key = path.join(delimiter);
                        }
                        return key && _isValidKey(namespace) ? namespace + delimiter + key : key;
                    },
                    _toKeyName = function (namespace, key, delimiter) {
                        if (!_isValidKey(namespace))
                            return key;
                        return key.replace(new RegExp('^' + namespace + delimiter), '');
                    },
                    _toStoredValue = function (value) {
                        return JSON.stringify(value);
                    },
                    _fromStoredValue = function (value) {
                        return value ? JSON.parse(value) : null;
                    };

                // HTML5 web storage interface
                var webStorageInterface = {
                    engine: null,
                    check: function () {
                        try {
                            window[this.engine].setItem(_salt, true);
                            window[this.engine].removeItem(_salt);
                        } catch (e) {
                            return false;
                        }
                        return true;
                    },
                    set: function (key, value, options) {
                        if (!key)
                            throw Error('invalid key');
                        window[this.engine].setItem(key, value);
                    },
                    get: function (key) {
                        return window[this.engine].getItem(key);
                    },
                    remove: function (key) {
                        window[this.engine].removeItem(key);
                    },
                    reset: function (namespace) {
                        for (var i = 0, key; i < window[this.engine].length; i++) {
                            key = window[this.engine].key(i);
                            if (!namespace || key.indexOf(namespace) === 0) {
                                this.remove(key);
                                i--;
                            }
                        }
                    },
                    keys: function (namespace, delimiter) {
                        var keys = [];
                        for (var i = 0, key; i < window[this.engine].length; i++) {
                            key = window[this.engine].key(i);
                            if (!namespace || key.indexOf(namespace) === 0)
                                keys.push(_toKeyName(namespace, key, delimiter));
                        }
                        return keys;
                    }
                };

                // local storage
                _storages.local = Basil.utils.extend({}, webStorageInterface, {
                    engine: 'localStorage'
                });
                // session storage
                _storages.session = Basil.utils.extend({}, webStorageInterface, {
                    engine: 'sessionStorage'
                });

                // memory storage
                _storages.memory = {
                    _hash: {},
                    check: function () {
                        return true;
                    },
                    set: function (key, value, options) {
                        if (!key)
                            throw Error('invalid key');
                        this._hash[key] = value;
                    },
                    get: function (key) {
                        return this._hash[key] || null;
                    },
                    remove: function (key) {
                        delete this._hash[key];
                    },
                    reset: function (namespace) {
                        for (var key in this._hash) {
                            if (!namespace || key.indexOf(namespace) === 0)
                                this.remove(key);
                        }
                    },
                    keys: function (namespace, delimiter) {
                        var keys = [];
                        for (var key in this._hash)
                            if (!namespace || key.indexOf(namespace) === 0)
                                keys.push(_toKeyName(namespace, key, delimiter));
                        return keys;
                    }
                };

                // cookie storage
                _storages.cookie = {
                    check: function (options) {
                        if (!navigator.cookieEnabled)
                            return false;
                        if (window.self !== window.top) {
                            // we need to check third-party cookies;
                            var cookie = 'thirdparty.check=' + Math.round(Math.random() * 1000);
                            document.cookie = cookie + '; path=/';
                            return document.cookie.indexOf(cookie) !== -1;
                        }
                        // if cookie secure activated, ensure it works (not the case if we are in http only)
                        if (options && options.secure) {
                            try {
                                this.set(_salt, _salt, options);
                                var hasSecurelyPersited = this.get(_salt) === _salt;
                                this.remove(_salt);
                                return hasSecurelyPersited;
                            } catch (error) {
                                return false;
                            }
                        }
                        return true;
                    },
                    set: function (key, value, options) {
                        if (!this.check())
                            throw Error('cookies are disabled');
                        options = options || {};
                        if (!key)
                            throw Error('invalid key');
                        var cookie = encodeURIComponent(key) + '=' + encodeURIComponent(value);
                        // handle expiration days
                        if (options.expireDays) {
                            var date = new Date();
                            date.setTime(date.getTime() + (options.expireDays * 24 * 60 * 60 * 1000));
                            cookie += '; expires=' + date.toGMTString();
                        }
                        // handle domain
                        if (options.domain && options.domain !== document.domain) {
                            var _domain = options.domain.replace(/^\./, '');
                            if (document.domain.indexOf(_domain) === -1 || _domain.split('.').length <= 1)
                                throw Error('invalid domain');
                            cookie += '; domain=' + options.domain;
                        }
                        // handle secure
                        if (options.secure === true) {
                            cookie += '; Secure';
                        }
                        document.cookie = cookie + '; path=/';
                    },
                    get: function (key) {
                        if (!this.check())
                            throw Error('cookies are disabled');
                        var encodedKey = encodeURIComponent(key);
                        var cookies = document.cookie ? document.cookie.split(';') : [];
                        // retrieve last updated cookie first
                        for (var i = cookies.length - 1, cookie; i >= 0; i--) {
                            cookie = cookies[i].replace(/^\s*/, '');
                            if (cookie.indexOf(encodedKey + '=') === 0)
                                return decodeURIComponent(cookie.substring(encodedKey.length + 1, cookie.length));
                        }
                        return null;
                    },
                    remove: function (key) {
                        // remove cookie from main domain
                        this.set(key, '', { expireDays: -1 });
                        // remove cookie from upper domains
                        var domainParts = document.domain.split('.');
                        for (var i = domainParts.length; i > 1; i--) {
                            this.set(key, '', { expireDays: -1, domain: '.' + domainParts.slice(- i).join('.') });
                        }
                    },
                    reset: function (namespace) {
                        var cookies = document.cookie ? document.cookie.split(';') : [];
                        for (var i = 0, cookie, key; i < cookies.length; i++) {
                            cookie = cookies[i].replace(/^\s*/, '');
                            key = cookie.substr(0, cookie.indexOf('='));
                            if (!namespace || key.indexOf(namespace) === 0)
                                this.remove(key);
                        }
                    },
                    keys: function (namespace, delimiter) {
                        if (!this.check())
                            throw Error('cookies are disabled');
                        var keys = [],
                            cookies = document.cookie ? document.cookie.split(';') : [];
                        for (var i = 0, cookie, key; i < cookies.length; i++) {
                            cookie = cookies[i].replace(/^\s*/, '');
                            key = decodeURIComponent(cookie.substr(0, cookie.indexOf('=')));
                            if (!namespace || key.indexOf(namespace) === 0)
                                keys.push(_toKeyName(namespace, key, delimiter));
                        }
                        return keys;
                    }
                };

                return {
                    init: function (options) {
                        this.setOptions(options);
                        return this;
                    },
                    setOptions: function (options) {
                        this.options = Basil.utils.extend({}, this.options || Basil.options, options);
                    },
                    support: function (storage) {
                        return _storages.hasOwnProperty(storage);
                    },
                    check: function (storage) {
                        if (this.support(storage))
                            return _storages[storage].check(this.options);
                        return false;
                    },
                    set: function (key, value, options) {
                        options = Basil.utils.extend({}, this.options, options);
                        if (!(key = _toStoredKey(options.namespace, key, options.keyDelimiter)))
                            return false;
                        value = options.raw === true ? value : _toStoredValue(value);
                        var where = null;
                        // try to set key/value in first available storage
                        Basil.utils.tryEach(_toStoragesArray(options.storages), function (storage, index) {
                            _storages[storage].set(key, value, options);
                            where = storage;
                            return false; // break;
                        }, null, this);
                        if (!where) {
                            // key has not been set anywhere
                            return false;
                        }
                        // remove key from all other storages
                        Basil.utils.tryEach(_toStoragesArray(options.storages), function (storage, index) {
                            if (storage !== where)
                                _storages[storage].remove(key);
                        }, null, this);
                        return true;
                    },
                    get: function (key, options) {
                        options = Basil.utils.extend({}, this.options, options);
                        if (!(key = _toStoredKey(options.namespace, key, options.keyDelimiter)))
                            return null;
                        var value = null;
                        Basil.utils.tryEach(_toStoragesArray(options.storages), function (storage, index) {
                            if (value !== null)
                                return false; // break if a value has already been found.
                            value = _storages[storage].get(key, options) || null;
                            value = options.raw === true ? value : _fromStoredValue(value);
                        }, function (storage, index, error) {
                            value = null;
                        }, this);
                        return value;
                    },
                    remove: function (key, options) {
                        options = Basil.utils.extend({}, this.options, options);
                        if (!(key = _toStoredKey(options.namespace, key, options.keyDelimiter)))
                            return;
                        Basil.utils.tryEach(_toStoragesArray(options.storages), function (storage) {
                            _storages[storage].remove(key);
                        }, null, this);
                    },
                    reset: function (options) {
                        options = Basil.utils.extend({}, this.options, options);
                        Basil.utils.tryEach(_toStoragesArray(options.storages), function (storage) {
                            _storages[storage].reset(options.namespace);
                        }, null, this);
                    },
                    keys: function (options) {
                        options = options || {};
                        var keys = [];
                        for (var key in this.keysMap(options))
                            keys.push(key);
                        return keys;
                    },
                    keysMap: function (options) {
                        options = Basil.utils.extend({}, this.options, options);
                        var map = {};
                        Basil.utils.tryEach(_toStoragesArray(options.storages), function (storage) {
                            Basil.utils.each(_storages[storage].keys(options.namespace, options.keyDelimiter), function (key) {
                                map[key] = Basil.utils.isArray(map[key]) ? map[key] : [];
                                map[key].push(storage);
                            }, this);
                        }, null, this);
                        return map;
                    }
                };
            };

            // Access to native storages, without namespace or basil value decoration
            Basil.memory = new Basil.Storage().init({ storages: 'memory', namespace: null, raw: true });
            Basil.cookie = new Basil.Storage().init({ storages: 'cookie', namespace: null, raw: true });
            Basil.localStorage = new Basil.Storage().init({ storages: 'local', namespace: null, raw: true });
            Basil.sessionStorage = new Basil.Storage().init({ storages: 'session', namespace: null, raw: true });

            // browser export
            window.Basil = Basil;

            // AMD export
            if (true) {
                !(__WEBPACK_AMD_DEFINE_RESULT__ = (function() {
                    return Basil;
                }).call(exports, __webpack_require__, exports, module),
                __WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));
                // commonjs export
            } else if (typeof module !== 'undefined' && module.exports) {
                module.exports = Basil;
            }

        })();


        /***/ }),
    /* 83 */,
    /* 84 */,
    /* 85 */,
    /* 86 */
    /***/ (function(module, exports, __webpack_require__) {

        /**
         * Copyright (c) 2014-present, Facebook, Inc.
         *
         * This source code is licensed under the MIT license found in the
         * LICENSE file in the root directory of this source tree.
         */

// This method of obtaining a reference to the global object needs to be
// kept identical to the way it is obtained in runtime.js
        var g = (function() { return this })() || Function("return this")();

// Use `getOwnPropertyNames` because not all browsers support calling
// `hasOwnProperty` on the global `self` object in a worker. See #183.
        var hadRuntime = g.regeneratorRuntime &&
            Object.getOwnPropertyNames(g).indexOf("regeneratorRuntime") >= 0;

// Save the old regeneratorRuntime in case it needs to be restored later.
        var oldRuntime = hadRuntime && g.regeneratorRuntime;

// Force reevalutation of runtime.js.
        g.regeneratorRuntime = undefined;

        module.exports = __webpack_require__(87);

        if (hadRuntime) {
            // Restore the original runtime.
            g.regeneratorRuntime = oldRuntime;
        } else {
            // Remove the global property added by runtime.js.
            try {
                delete g.regeneratorRuntime;
            } catch(e) {
                g.regeneratorRuntime = undefined;
            }
        }


        /***/ }),
    /* 87 */
    /***/ (function(module, exports) {

        /**
         * Copyright (c) 2014-present, Facebook, Inc.
         *
         * This source code is licensed under the MIT license found in the
         * LICENSE file in the root directory of this source tree.
         */

        !(function(global) {
            "use strict";

            var Op = Object.prototype;
            var hasOwn = Op.hasOwnProperty;
            var undefined; // More compressible than void 0.
            var $Symbol = typeof Symbol === "function" ? Symbol : {};
            var iteratorSymbol = $Symbol.iterator || "@@iterator";
            var asyncIteratorSymbol = $Symbol.asyncIterator || "@@asyncIterator";
            var toStringTagSymbol = $Symbol.toStringTag || "@@toStringTag";

            var inModule = typeof module === "object";
            var runtime = global.regeneratorRuntime;
            if (runtime) {
                if (inModule) {
                    // If regeneratorRuntime is defined globally and we're in a module,
                    // make the exports object identical to regeneratorRuntime.
                    module.exports = runtime;
                }
                // Don't bother evaluating the rest of this file if the runtime was
                // already defined globally.
                return;
            }

            // Define the runtime globally (as expected by generated code) as either
            // module.exports (if we're in a module) or a new, empty object.
            runtime = global.regeneratorRuntime = inModule ? module.exports : {};

            function wrap(innerFn, outerFn, self, tryLocsList) {
                // If outerFn provided and outerFn.prototype is a Generator, then outerFn.prototype instanceof Generator.
                var protoGenerator = outerFn && outerFn.prototype instanceof Generator ? outerFn : Generator;
                var generator = Object.create(protoGenerator.prototype);
                var context = new Context(tryLocsList || []);

                // The ._invoke method unifies the implementations of the .next,
                // .throw, and .return methods.
                generator._invoke = makeInvokeMethod(innerFn, self, context);

                return generator;
            }
            runtime.wrap = wrap;

            // Try/catch helper to minimize deoptimizations. Returns a completion
            // record like context.tryEntries[i].completion. This interface could
            // have been (and was previously) designed to take a closure to be
            // invoked without arguments, but in all the cases we care about we
            // already have an existing method we want to call, so there's no need
            // to create a new function object. We can even get away with assuming
            // the method takes exactly one argument, since that happens to be true
            // in every case, so we don't have to touch the arguments object. The
            // only additional allocation required is the completion record, which
            // has a stable shape and so hopefully should be cheap to allocate.
            function tryCatch(fn, obj, arg) {
                try {
                    return { type: "normal", arg: fn.call(obj, arg) };
                } catch (err) {
                    return { type: "throw", arg: err };
                }
            }

            var GenStateSuspendedStart = "suspendedStart";
            var GenStateSuspendedYield = "suspendedYield";
            var GenStateExecuting = "executing";
            var GenStateCompleted = "completed";

            // Returning this object from the innerFn has the same effect as
            // breaking out of the dispatch switch statement.
            var ContinueSentinel = {};

            // Dummy constructor functions that we use as the .constructor and
            // .constructor.prototype properties for functions that return Generator
            // objects. For full spec compliance, you may wish to configure your
            // minifier not to mangle the names of these two functions.
            function Generator() {}
            function GeneratorFunction() {}
            function GeneratorFunctionPrototype() {}

            // This is a polyfill for %IteratorPrototype% for environments that
            // don't natively support it.
            var IteratorPrototype = {};
            IteratorPrototype[iteratorSymbol] = function () {
                return this;
            };

            var getProto = Object.getPrototypeOf;
            var NativeIteratorPrototype = getProto && getProto(getProto(values([])));
            if (NativeIteratorPrototype &&
                NativeIteratorPrototype !== Op &&
                hasOwn.call(NativeIteratorPrototype, iteratorSymbol)) {
                // This environment has a native %IteratorPrototype%; use it instead
                // of the polyfill.
                IteratorPrototype = NativeIteratorPrototype;
            }

            var Gp = GeneratorFunctionPrototype.prototype =
                Generator.prototype = Object.create(IteratorPrototype);
            GeneratorFunction.prototype = Gp.constructor = GeneratorFunctionPrototype;
            GeneratorFunctionPrototype.constructor = GeneratorFunction;
            GeneratorFunctionPrototype[toStringTagSymbol] =
                GeneratorFunction.displayName = "GeneratorFunction";

            // Helper for defining the .next, .throw, and .return methods of the
            // Iterator interface in terms of a single ._invoke method.
            function defineIteratorMethods(prototype) {
                ["next", "throw", "return"].forEach(function(method) {
                    prototype[method] = function(arg) {
                        return this._invoke(method, arg);
                    };
                });
            }

            runtime.isGeneratorFunction = function(genFun) {
                var ctor = typeof genFun === "function" && genFun.constructor;
                return ctor
                    ? ctor === GeneratorFunction ||
                    // For the native GeneratorFunction constructor, the best we can
                    // do is to check its .name property.
                    (ctor.displayName || ctor.name) === "GeneratorFunction"
                    : false;
            };

            runtime.mark = function(genFun) {
                if (Object.setPrototypeOf) {
                    Object.setPrototypeOf(genFun, GeneratorFunctionPrototype);
                } else {
                    genFun.__proto__ = GeneratorFunctionPrototype;
                    if (!(toStringTagSymbol in genFun)) {
                        genFun[toStringTagSymbol] = "GeneratorFunction";
                    }
                }
                genFun.prototype = Object.create(Gp);
                return genFun;
            };

            // Within the body of any async function, `await x` is transformed to
            // `yield regeneratorRuntime.awrap(x)`, so that the runtime can test
            // `hasOwn.call(value, "__await")` to determine if the yielded value is
            // meant to be awaited.
            runtime.awrap = function(arg) {
                return { __await: arg };
            };

            function AsyncIterator(generator) {
                function invoke(method, arg, resolve, reject) {
                    var record = tryCatch(generator[method], generator, arg);
                    if (record.type === "throw") {
                        reject(record.arg);
                    } else {
                        var result = record.arg;
                        var value = result.value;
                        if (value &&
                            typeof value === "object" &&
                            hasOwn.call(value, "__await")) {
                            return Promise.resolve(value.__await).then(function(value) {
                                invoke("next", value, resolve, reject);
                            }, function(err) {
                                invoke("throw", err, resolve, reject);
                            });
                        }

                        return Promise.resolve(value).then(function(unwrapped) {
                            // When a yielded Promise is resolved, its final value becomes
                            // the .value of the Promise<{value,done}> result for the
                            // current iteration. If the Promise is rejected, however, the
                            // result for this iteration will be rejected with the same
                            // reason. Note that rejections of yielded Promises are not
                            // thrown back into the generator function, as is the case
                            // when an awaited Promise is rejected. This difference in
                            // behavior between yield and await is important, because it
                            // allows the consumer to decide what to do with the yielded
                            // rejection (swallow it and continue, manually .throw it back
                            // into the generator, abandon iteration, whatever). With
                            // await, by contrast, there is no opportunity to examine the
                            // rejection reason outside the generator function, so the
                            // only option is to throw it from the await expression, and
                            // let the generator function handle the exception.
                            result.value = unwrapped;
                            resolve(result);
                        }, reject);
                    }
                }

                var previousPromise;

                function enqueue(method, arg) {
                    function callInvokeWithMethodAndArg() {
                        return new Promise(function(resolve, reject) {
                            invoke(method, arg, resolve, reject);
                        });
                    }

                    return previousPromise =
                        // If enqueue has been called before, then we want to wait until
                        // all previous Promises have been resolved before calling invoke,
                        // so that results are always delivered in the correct order. If
                        // enqueue has not been called before, then it is important to
                        // call invoke immediately, without waiting on a callback to fire,
                        // so that the async generator function has the opportunity to do
                        // any necessary setup in a predictable way. This predictability
                        // is why the Promise constructor synchronously invokes its
                        // executor callback, and why async functions synchronously
                        // execute code before the first await. Since we implement simple
                        // async functions in terms of async generators, it is especially
                        // important to get this right, even though it requires care.
                        previousPromise ? previousPromise.then(
                            callInvokeWithMethodAndArg,
                            // Avoid propagating failures to Promises returned by later
                            // invocations of the iterator.
                            callInvokeWithMethodAndArg
                        ) : callInvokeWithMethodAndArg();
                }

                // Define the unified helper method that is used to implement .next,
                // .throw, and .return (see defineIteratorMethods).
                this._invoke = enqueue;
            }

            defineIteratorMethods(AsyncIterator.prototype);
            AsyncIterator.prototype[asyncIteratorSymbol] = function () {
                return this;
            };
            runtime.AsyncIterator = AsyncIterator;

            // Note that simple async functions are implemented on top of
            // AsyncIterator objects; they just return a Promise for the value of
            // the final result produced by the iterator.
            runtime.async = function(innerFn, outerFn, self, tryLocsList) {
                var iter = new AsyncIterator(
                    wrap(innerFn, outerFn, self, tryLocsList)
                );

                return runtime.isGeneratorFunction(outerFn)
                    ? iter // If outerFn is a generator, return the full iterator.
                    : iter.next().then(function(result) {
                        return result.done ? result.value : iter.next();
                    });
            };

            function makeInvokeMethod(innerFn, self, context) {
                var state = GenStateSuspendedStart;

                return function invoke(method, arg) {
                    if (state === GenStateExecuting) {
                        throw new Error("Generator is already running");
                    }

                    if (state === GenStateCompleted) {
                        if (method === "throw") {
                            throw arg;
                        }

                        // Be forgiving, per 25.3.3.3.3 of the spec:
                        // https://people.mozilla.org/~jorendorff/es6-draft.html#sec-generatorresume
                        return doneResult();
                    }

                    context.method = method;
                    context.arg = arg;

                    while (true) {
                        var delegate = context.delegate;
                        if (delegate) {
                            var delegateResult = maybeInvokeDelegate(delegate, context);
                            if (delegateResult) {
                                if (delegateResult === ContinueSentinel) continue;
                                return delegateResult;
                            }
                        }

                        if (context.method === "next") {
                            // Setting context._sent for legacy support of Babel's
                            // function.sent implementation.
                            context.sent = context._sent = context.arg;

                        } else if (context.method === "throw") {
                            if (state === GenStateSuspendedStart) {
                                state = GenStateCompleted;
                                throw context.arg;
                            }

                            context.dispatchException(context.arg);

                        } else if (context.method === "return") {
                            context.abrupt("return", context.arg);
                        }

                        state = GenStateExecuting;

                        var record = tryCatch(innerFn, self, context);
                        if (record.type === "normal") {
                            // If an exception is thrown from innerFn, we leave state ===
                            // GenStateExecuting and loop back for another invocation.
                            state = context.done
                                ? GenStateCompleted
                                : GenStateSuspendedYield;

                            if (record.arg === ContinueSentinel) {
                                continue;
                            }

                            return {
                                value: record.arg,
                                done: context.done
                            };

                        } else if (record.type === "throw") {
                            state = GenStateCompleted;
                            // Dispatch the exception by looping back around to the
                            // context.dispatchException(context.arg) call above.
                            context.method = "throw";
                            context.arg = record.arg;
                        }
                    }
                };
            }

            // Call delegate.iterator[context.method](context.arg) and handle the
            // result, either by returning a { value, done } result from the
            // delegate iterator, or by modifying context.method and context.arg,
            // setting context.delegate to null, and returning the ContinueSentinel.
            function maybeInvokeDelegate(delegate, context) {
                var method = delegate.iterator[context.method];
                if (method === undefined) {
                    // A .throw or .return when the delegate iterator has no .throw
                    // method always terminates the yield* loop.
                    context.delegate = null;

                    if (context.method === "throw") {
                        if (delegate.iterator.return) {
                            // If the delegate iterator has a return method, give it a
                            // chance to clean up.
                            context.method = "return";
                            context.arg = undefined;
                            maybeInvokeDelegate(delegate, context);

                            if (context.method === "throw") {
                                // If maybeInvokeDelegate(context) changed context.method from
                                // "return" to "throw", let that override the TypeError below.
                                return ContinueSentinel;
                            }
                        }

                        context.method = "throw";
                        context.arg = new TypeError(
                            "The iterator does not provide a 'throw' method");
                    }

                    return ContinueSentinel;
                }

                var record = tryCatch(method, delegate.iterator, context.arg);

                if (record.type === "throw") {
                    context.method = "throw";
                    context.arg = record.arg;
                    context.delegate = null;
                    return ContinueSentinel;
                }

                var info = record.arg;

                if (! info) {
                    context.method = "throw";
                    context.arg = new TypeError("iterator result is not an object");
                    context.delegate = null;
                    return ContinueSentinel;
                }

                if (info.done) {
                    // Assign the result of the finished delegate to the temporary
                    // variable specified by delegate.resultName (see delegateYield).
                    context[delegate.resultName] = info.value;

                    // Resume execution at the desired location (see delegateYield).
                    context.next = delegate.nextLoc;

                    // If context.method was "throw" but the delegate handled the
                    // exception, let the outer generator proceed normally. If
                    // context.method was "next", forget context.arg since it has been
                    // "consumed" by the delegate iterator. If context.method was
                    // "return", allow the original .return call to continue in the
                    // outer generator.
                    if (context.method !== "return") {
                        context.method = "next";
                        context.arg = undefined;
                    }

                } else {
                    // Re-yield the result returned by the delegate method.
                    return info;
                }

                // The delegate iterator is finished, so forget it and continue with
                // the outer generator.
                context.delegate = null;
                return ContinueSentinel;
            }

            // Define Generator.prototype.{next,throw,return} in terms of the
            // unified ._invoke helper method.
            defineIteratorMethods(Gp);

            Gp[toStringTagSymbol] = "Generator";

            // A Generator should always return itself as the iterator object when the
            // @@iterator function is called on it. Some browsers' implementations of the
            // iterator prototype chain incorrectly implement this, causing the Generator
            // object to not be returned from this call. This ensures that doesn't happen.
            // See https://github.com/facebook/regenerator/issues/274 for more details.
            Gp[iteratorSymbol] = function() {
                return this;
            };

            Gp.toString = function() {
                return "[object Generator]";
            };

            function pushTryEntry(locs) {
                var entry = { tryLoc: locs[0] };

                if (1 in locs) {
                    entry.catchLoc = locs[1];
                }

                if (2 in locs) {
                    entry.finallyLoc = locs[2];
                    entry.afterLoc = locs[3];
                }

                this.tryEntries.push(entry);
            }

            function resetTryEntry(entry) {
                var record = entry.completion || {};
                record.type = "normal";
                delete record.arg;
                entry.completion = record;
            }

            function Context(tryLocsList) {
                // The root entry object (effectively a try statement without a catch
                // or a finally block) gives us a place to store values thrown from
                // locations where there is no enclosing try statement.
                this.tryEntries = [{ tryLoc: "root" }];
                tryLocsList.forEach(pushTryEntry, this);
                this.reset(true);
            }

            runtime.keys = function(object) {
                var keys = [];
                for (var key in object) {
                    keys.push(key);
                }
                keys.reverse();

                // Rather than returning an object with a next method, we keep
                // things simple and return the next function itself.
                return function next() {
                    while (keys.length) {
                        var key = keys.pop();
                        if (key in object) {
                            next.value = key;
                            next.done = false;
                            return next;
                        }
                    }

                    // To avoid creating an additional object, we just hang the .value
                    // and .done properties off the next function object itself. This
                    // also ensures that the minifier will not anonymize the function.
                    next.done = true;
                    return next;
                };
            };

            function values(iterable) {
                if (iterable) {
                    var iteratorMethod = iterable[iteratorSymbol];
                    if (iteratorMethod) {
                        return iteratorMethod.call(iterable);
                    }

                    if (typeof iterable.next === "function") {
                        return iterable;
                    }

                    if (!isNaN(iterable.length)) {
                        var i = -1, next = function next() {
                            while (++i < iterable.length) {
                                if (hasOwn.call(iterable, i)) {
                                    next.value = iterable[i];
                                    next.done = false;
                                    return next;
                                }
                            }

                            next.value = undefined;
                            next.done = true;

                            return next;
                        };

                        return next.next = next;
                    }
                }

                // Return an iterator with no values.
                return { next: doneResult };
            }
            runtime.values = values;

            function doneResult() {
                return { value: undefined, done: true };
            }

            Context.prototype = {
                constructor: Context,

                reset: function(skipTempReset) {
                    this.prev = 0;
                    this.next = 0;
                    // Resetting context._sent for legacy support of Babel's
                    // function.sent implementation.
                    this.sent = this._sent = undefined;
                    this.done = false;
                    this.delegate = null;

                    this.method = "next";
                    this.arg = undefined;

                    this.tryEntries.forEach(resetTryEntry);

                    if (!skipTempReset) {
                        for (var name in this) {
                            // Not sure about the optimal order of these conditions:
                            if (name.charAt(0) === "t" &&
                                hasOwn.call(this, name) &&
                                !isNaN(+name.slice(1))) {
                                this[name] = undefined;
                            }
                        }
                    }
                },

                stop: function() {
                    this.done = true;

                    var rootEntry = this.tryEntries[0];
                    var rootRecord = rootEntry.completion;
                    if (rootRecord.type === "throw") {
                        throw rootRecord.arg;
                    }

                    return this.rval;
                },

                dispatchException: function(exception) {
                    if (this.done) {
                        throw exception;
                    }

                    var context = this;
                    function handle(loc, caught) {
                        record.type = "throw";
                        record.arg = exception;
                        context.next = loc;

                        if (caught) {
                            // If the dispatched exception was caught by a catch block,
                            // then let that catch block handle the exception normally.
                            context.method = "next";
                            context.arg = undefined;
                        }

                        return !! caught;
                    }

                    for (var i = this.tryEntries.length - 1; i >= 0; --i) {
                        var entry = this.tryEntries[i];
                        var record = entry.completion;

                        if (entry.tryLoc === "root") {
                            // Exception thrown outside of any try block that could handle
                            // it, so set the completion value of the entire function to
                            // throw the exception.
                            return handle("end");
                        }

                        if (entry.tryLoc <= this.prev) {
                            var hasCatch = hasOwn.call(entry, "catchLoc");
                            var hasFinally = hasOwn.call(entry, "finallyLoc");

                            if (hasCatch && hasFinally) {
                                if (this.prev < entry.catchLoc) {
                                    return handle(entry.catchLoc, true);
                                } else if (this.prev < entry.finallyLoc) {
                                    return handle(entry.finallyLoc);
                                }

                            } else if (hasCatch) {
                                if (this.prev < entry.catchLoc) {
                                    return handle(entry.catchLoc, true);
                                }

                            } else if (hasFinally) {
                                if (this.prev < entry.finallyLoc) {
                                    return handle(entry.finallyLoc);
                                }

                            } else {
                                throw new Error("try statement without catch or finally");
                            }
                        }
                    }
                },

                abrupt: function(type, arg) {
                    for (var i = this.tryEntries.length - 1; i >= 0; --i) {
                        var entry = this.tryEntries[i];
                        if (entry.tryLoc <= this.prev &&
                            hasOwn.call(entry, "finallyLoc") &&
                            this.prev < entry.finallyLoc) {
                            var finallyEntry = entry;
                            break;
                        }
                    }

                    if (finallyEntry &&
                        (type === "break" ||
                            type === "continue") &&
                        finallyEntry.tryLoc <= arg &&
                        arg <= finallyEntry.finallyLoc) {
                        // Ignore the finally entry if control is not jumping to a
                        // location outside the try/catch block.
                        finallyEntry = null;
                    }

                    var record = finallyEntry ? finallyEntry.completion : {};
                    record.type = type;
                    record.arg = arg;

                    if (finallyEntry) {
                        this.method = "next";
                        this.next = finallyEntry.finallyLoc;
                        return ContinueSentinel;
                    }

                    return this.complete(record);
                },

                complete: function(record, afterLoc) {
                    if (record.type === "throw") {
                        throw record.arg;
                    }

                    if (record.type === "break" ||
                        record.type === "continue") {
                        this.next = record.arg;
                    } else if (record.type === "return") {
                        this.rval = this.arg = record.arg;
                        this.method = "return";
                        this.next = "end";
                    } else if (record.type === "normal" && afterLoc) {
                        this.next = afterLoc;
                    }

                    return ContinueSentinel;
                },

                finish: function(finallyLoc) {
                    for (var i = this.tryEntries.length - 1; i >= 0; --i) {
                        var entry = this.tryEntries[i];
                        if (entry.finallyLoc === finallyLoc) {
                            this.complete(entry.completion, entry.afterLoc);
                            resetTryEntry(entry);
                            return ContinueSentinel;
                        }
                    }
                },

                "catch": function(tryLoc) {
                    for (var i = this.tryEntries.length - 1; i >= 0; --i) {
                        var entry = this.tryEntries[i];
                        if (entry.tryLoc === tryLoc) {
                            var record = entry.completion;
                            if (record.type === "throw") {
                                var thrown = record.arg;
                                resetTryEntry(entry);
                            }
                            return thrown;
                        }
                    }

                    // The context.catch method must only be called with a location
                    // argument that corresponds to a known catch block.
                    throw new Error("illegal catch attempt");
                },

                delegateYield: function(iterable, resultName, nextLoc) {
                    this.delegate = {
                        iterator: values(iterable),
                        resultName: resultName,
                        nextLoc: nextLoc
                    };

                    if (this.method === "next") {
                        // Deliberately forget the last sent value so that we don't
                        // accidentally pass it on to the delegate.
                        this.arg = undefined;
                    }

                    return ContinueSentinel;
                }
            };
        })(
            // In sloppy mode, unbound `this` refers to the global object, fallback to
            // Function constructor if we're in global strict mode. That is sadly a form
            // of indirect eval which violates Content Security Policy.
            (function() { return this })() || Function("return this")()
        );


        /***/ }),
    /* 88 */
    /***/ (function(module, exports, __webpack_require__) {

        __webpack_require__(89);
        __webpack_require__(17);
        __webpack_require__(25);
        __webpack_require__(90);
        __webpack_require__(101);
        __webpack_require__(102);
        module.exports = __webpack_require__(0).Promise;


        /***/ }),
    /* 89 */
    /***/ (function(module, exports) {



        /***/ }),
    /* 90 */
    /***/ (function(module, exports, __webpack_require__) {

        "use strict";

        var LIBRARY = __webpack_require__(18);
        var global = __webpack_require__(2);
        var ctx = __webpack_require__(19);
        var classof = __webpack_require__(26);
        var $export = __webpack_require__(7);
        var isObject = __webpack_require__(9);
        var aFunction = __webpack_require__(20);
        var anInstance = __webpack_require__(91);
        var forOf = __webpack_require__(92);
        var speciesConstructor = __webpack_require__(66);
        var task = __webpack_require__(67).set;
        var microtask = __webpack_require__(96)();
        var newPromiseCapabilityModule = __webpack_require__(55);
        var perform = __webpack_require__(68);
        var userAgent = __webpack_require__(97);
        var promiseResolve = __webpack_require__(69);
        var PROMISE = 'Promise';
        var TypeError = global.TypeError;
        var process = global.process;
        var versions = process && process.versions;
        var v8 = versions && versions.v8 || '';
        var $Promise = global[PROMISE];
        var isNode = classof(process) == 'process';
        var empty = function () { /* empty */ };
        var Internal, newGenericPromiseCapability, OwnPromiseCapability, Wrapper;
        var newPromiseCapability = newGenericPromiseCapability = newPromiseCapabilityModule.f;

        var USE_NATIVE = !!function () {
            try {
                // correct subclassing with @@species support
                var promise = $Promise.resolve(1);
                var FakePromise = (promise.constructor = {})[__webpack_require__(1)('species')] = function (exec) {
                    exec(empty, empty);
                };
                // unhandled rejections tracking support, NodeJS Promise without it fails @@species test
                return (isNode || typeof PromiseRejectionEvent == 'function')
                    && promise.then(empty) instanceof FakePromise
                    // v8 6.6 (Node 10 and Chrome 66) have a bug with resolving custom thenables
                    // https://bugs.chromium.org/p/chromium/issues/detail?id=830565
                    // we can't detect it synchronously, so just check versions
                    && v8.indexOf('6.6') !== 0
                    && userAgent.indexOf('Chrome/66') === -1;
            } catch (e) { /* empty */ }
        }();

// helpers
        var isThenable = function (it) {
            var then;
            return isObject(it) && typeof (then = it.then) == 'function' ? then : false;
        };
        var notify = function (promise, isReject) {
            if (promise._n) return;
            promise._n = true;
            var chain = promise._c;
            microtask(function () {
                var value = promise._v;
                var ok = promise._s == 1;
                var i = 0;
                var run = function (reaction) {
                    var handler = ok ? reaction.ok : reaction.fail;
                    var resolve = reaction.resolve;
                    var reject = reaction.reject;
                    var domain = reaction.domain;
                    var result, then, exited;
                    try {
                        if (handler) {
                            if (!ok) {
                                if (promise._h == 2) onHandleUnhandled(promise);
                                promise._h = 1;
                            }
                            if (handler === true) result = value;
                            else {
                                if (domain) domain.enter();
                                result = handler(value); // may throw
                                if (domain) {
                                    domain.exit();
                                    exited = true;
                                }
                            }
                            if (result === reaction.promise) {
                                reject(TypeError('Promise-chain cycle'));
                            } else if (then = isThenable(result)) {
                                then.call(result, resolve, reject);
                            } else resolve(result);
                        } else reject(value);
                    } catch (e) {
                        if (domain && !exited) domain.exit();
                        reject(e);
                    }
                };
                while (chain.length > i) run(chain[i++]); // variable length - can't use forEach
                promise._c = [];
                promise._n = false;
                if (isReject && !promise._h) onUnhandled(promise);
            });
        };
        var onUnhandled = function (promise) {
            task.call(global, function () {
                var value = promise._v;
                var unhandled = isUnhandled(promise);
                var result, handler, console;
                if (unhandled) {
                    result = perform(function () {
                        if (isNode) {
                            process.emit('unhandledRejection', value, promise);
                        } else if (handler = global.onunhandledrejection) {
                            handler({ promise: promise, reason: value });
                        } else if ((console = global.console) && console.error) {
                            console.error('Unhandled promise rejection', value);
                        }
                    });
                    // Browsers should not trigger `rejectionHandled` event if it was handled here, NodeJS - should
                    promise._h = isNode || isUnhandled(promise) ? 2 : 1;
                } promise._a = undefined;
                if (unhandled && result.e) throw result.v;
            });
        };
        var isUnhandled = function (promise) {
            return promise._h !== 1 && (promise._a || promise._c).length === 0;
        };
        var onHandleUnhandled = function (promise) {
            task.call(global, function () {
                var handler;
                if (isNode) {
                    process.emit('rejectionHandled', promise);
                } else if (handler = global.onrejectionhandled) {
                    handler({ promise: promise, reason: promise._v });
                }
            });
        };
        var $reject = function (value) {
            var promise = this;
            if (promise._d) return;
            promise._d = true;
            promise = promise._w || promise; // unwrap
            promise._v = value;
            promise._s = 2;
            if (!promise._a) promise._a = promise._c.slice();
            notify(promise, true);
        };
        var $resolve = function (value) {
            var promise = this;
            var then;
            if (promise._d) return;
            promise._d = true;
            promise = promise._w || promise; // unwrap
            try {
                if (promise === value) throw TypeError("Promise can't be resolved itself");
                if (then = isThenable(value)) {
                    microtask(function () {
                        var wrapper = { _w: promise, _d: false }; // wrap
                        try {
                            then.call(value, ctx($resolve, wrapper, 1), ctx($reject, wrapper, 1));
                        } catch (e) {
                            $reject.call(wrapper, e);
                        }
                    });
                } else {
                    promise._v = value;
                    promise._s = 1;
                    notify(promise, false);
                }
            } catch (e) {
                $reject.call({ _w: promise, _d: false }, e); // wrap
            }
        };

// constructor polyfill
        if (!USE_NATIVE) {
            // 25.4.3.1 Promise(executor)
            $Promise = function Promise(executor) {
                anInstance(this, $Promise, PROMISE, '_h');
                aFunction(executor);
                Internal.call(this);
                try {
                    executor(ctx($resolve, this, 1), ctx($reject, this, 1));
                } catch (err) {
                    $reject.call(this, err);
                }
            };
            // eslint-disable-next-line no-unused-vars
            Internal = function Promise(executor) {
                this._c = [];             // <- awaiting reactions
                this._a = undefined;      // <- checked in isUnhandled reactions
                this._s = 0;              // <- state
                this._d = false;          // <- done
                this._v = undefined;      // <- value
                this._h = 0;              // <- rejection state, 0 - default, 1 - handled, 2 - unhandled
                this._n = false;          // <- notify
            };
            Internal.prototype = __webpack_require__(98)($Promise.prototype, {
                // 25.4.5.3 Promise.prototype.then(onFulfilled, onRejected)
                then: function then(onFulfilled, onRejected) {
                    var reaction = newPromiseCapability(speciesConstructor(this, $Promise));
                    reaction.ok = typeof onFulfilled == 'function' ? onFulfilled : true;
                    reaction.fail = typeof onRejected == 'function' && onRejected;
                    reaction.domain = isNode ? process.domain : undefined;
                    this._c.push(reaction);
                    if (this._a) this._a.push(reaction);
                    if (this._s) notify(this, false);
                    return reaction.promise;
                },
                // 25.4.5.1 Promise.prototype.catch(onRejected)
                'catch': function (onRejected) {
                    return this.then(undefined, onRejected);
                }
            });
            OwnPromiseCapability = function () {
                var promise = new Internal();
                this.promise = promise;
                this.resolve = ctx($resolve, promise, 1);
                this.reject = ctx($reject, promise, 1);
            };
            newPromiseCapabilityModule.f = newPromiseCapability = function (C) {
                return C === $Promise || C === Wrapper
                    ? new OwnPromiseCapability(C)
                    : newGenericPromiseCapability(C);
            };
        }

        $export($export.G + $export.W + $export.F * !USE_NATIVE, { Promise: $Promise });
        __webpack_require__(23)($Promise, PROMISE);
        __webpack_require__(99)(PROMISE);
        Wrapper = __webpack_require__(0)[PROMISE];

// statics
        $export($export.S + $export.F * !USE_NATIVE, PROMISE, {
            // 25.4.4.5 Promise.reject(r)
            reject: function reject(r) {
                var capability = newPromiseCapability(this);
                var $$reject = capability.reject;
                $$reject(r);
                return capability.promise;
            }
        });
        $export($export.S + $export.F * (LIBRARY || !USE_NATIVE), PROMISE, {
            // 25.4.4.6 Promise.resolve(x)
            resolve: function resolve(x) {
                return promiseResolve(LIBRARY && this === Wrapper ? $Promise : this, x);
            }
        });
        $export($export.S + $export.F * !(USE_NATIVE && __webpack_require__(100)(function (iter) {
            $Promise.all(iter)['catch'](empty);
        })), PROMISE, {
            // 25.4.4.1 Promise.all(iterable)
            all: function all(iterable) {
                var C = this;
                var capability = newPromiseCapability(C);
                var resolve = capability.resolve;
                var reject = capability.reject;
                var result = perform(function () {
                    var values = [];
                    var index = 0;
                    var remaining = 1;
                    forOf(iterable, false, function (promise) {
                        var $index = index++;
                        var alreadyCalled = false;
                        values.push(undefined);
                        remaining++;
                        C.resolve(promise).then(function (value) {
                            if (alreadyCalled) return;
                            alreadyCalled = true;
                            values[$index] = value;
                            --remaining || resolve(values);
                        }, reject);
                    });
                    --remaining || resolve(values);
                });
                if (result.e) reject(result.v);
                return capability.promise;
            },
            // 25.4.4.4 Promise.race(iterable)
            race: function race(iterable) {
                var C = this;
                var capability = newPromiseCapability(C);
                var reject = capability.reject;
                var result = perform(function () {
                    forOf(iterable, false, function (promise) {
                        C.resolve(promise).then(capability.resolve, reject);
                    });
                });
                if (result.e) reject(result.v);
                return capability.promise;
            }
        });


        /***/ }),
    /* 91 */
    /***/ (function(module, exports) {

        module.exports = function (it, Constructor, name, forbiddenField) {
            if (!(it instanceof Constructor) || (forbiddenField !== undefined && forbiddenField in it)) {
                throw TypeError(name + ': incorrect invocation!');
            } return it;
        };


        /***/ }),
    /* 92 */
    /***/ (function(module, exports, __webpack_require__) {

        var ctx = __webpack_require__(19);
        var call = __webpack_require__(93);
        var isArrayIter = __webpack_require__(94);
        var anObject = __webpack_require__(3);
        var toLength = __webpack_require__(34);
        var getIterFn = __webpack_require__(36);
        var BREAK = {};
        var RETURN = {};
        var exports = module.exports = function (iterable, entries, fn, that, ITERATOR) {
            var iterFn = ITERATOR ? function () { return iterable; } : getIterFn(iterable);
            var f = ctx(fn, that, entries ? 2 : 1);
            var index = 0;
            var length, step, iterator, result;
            if (typeof iterFn != 'function') throw TypeError(iterable + ' is not iterable!');
            // fast case for arrays with default iterator
            if (isArrayIter(iterFn)) for (length = toLength(iterable.length); length > index; index++) {
                result = entries ? f(anObject(step = iterable[index])[0], step[1]) : f(iterable[index]);
                if (result === BREAK || result === RETURN) return result;
            } else for (iterator = iterFn.call(iterable); !(step = iterator.next()).done;) {
                result = call(iterator, f, step.value, entries);
                if (result === BREAK || result === RETURN) return result;
            }
        };
        exports.BREAK = BREAK;
        exports.RETURN = RETURN;


        /***/ }),
    /* 93 */
    /***/ (function(module, exports, __webpack_require__) {

// call something on iterator step with safe closing on error
        var anObject = __webpack_require__(3);
        module.exports = function (iterator, fn, value, entries) {
            try {
                return entries ? fn(anObject(value)[0], value[1]) : fn(value);
                // 7.4.6 IteratorClose(iterator, completion)
            } catch (e) {
                var ret = iterator['return'];
                if (ret !== undefined) anObject(ret.call(iterator));
                throw e;
            }
        };


        /***/ }),
    /* 94 */
    /***/ (function(module, exports, __webpack_require__) {

// check on default Array iterator
        var Iterators = __webpack_require__(5);
        var ITERATOR = __webpack_require__(1)('iterator');
        var ArrayProto = Array.prototype;

        module.exports = function (it) {
            return it !== undefined && (Iterators.Array === it || ArrayProto[ITERATOR] === it);
        };


        /***/ }),
    /* 95 */
    /***/ (function(module, exports) {

// fast apply, http://jsperf.lnkit.com/fast-apply/5
        module.exports = function (fn, args, that) {
            var un = that === undefined;
            switch (args.length) {
                case 0: return un ? fn()
                    : fn.call(that);
                case 1: return un ? fn(args[0])
                    : fn.call(that, args[0]);
                case 2: return un ? fn(args[0], args[1])
                    : fn.call(that, args[0], args[1]);
                case 3: return un ? fn(args[0], args[1], args[2])
                    : fn.call(that, args[0], args[1], args[2]);
                case 4: return un ? fn(args[0], args[1], args[2], args[3])
                    : fn.call(that, args[0], args[1], args[2], args[3]);
            } return fn.apply(that, args);
        };


        /***/ }),
    /* 96 */
    /***/ (function(module, exports, __webpack_require__) {

        var global = __webpack_require__(2);
        var macrotask = __webpack_require__(67).set;
        var Observer = global.MutationObserver || global.WebKitMutationObserver;
        var process = global.process;
        var Promise = global.Promise;
        var isNode = __webpack_require__(15)(process) == 'process';

        module.exports = function () {
            var head, last, notify;

            var flush = function () {
                var parent, fn;
                if (isNode && (parent = process.domain)) parent.exit();
                while (head) {
                    fn = head.fn;
                    head = head.next;
                    try {
                        fn();
                    } catch (e) {
                        if (head) notify();
                        else last = undefined;
                        throw e;
                    }
                } last = undefined;
                if (parent) parent.enter();
            };

            // Node.js
            if (isNode) {
                notify = function () {
                    process.nextTick(flush);
                };
                // browsers with MutationObserver, except iOS Safari - https://github.com/zloirock/core-js/issues/339
            } else if (Observer && !(global.navigator && global.navigator.standalone)) {
                var toggle = true;
                var node = document.createTextNode('');
                new Observer(flush).observe(node, { characterData: true }); // eslint-disable-line no-new
                notify = function () {
                    node.data = toggle = !toggle;
                };
                // environments with maybe non-completely correct, but existent Promise
            } else if (Promise && Promise.resolve) {
                // Promise.resolve without an argument throws an error in LG WebOS 2
                var promise = Promise.resolve(undefined);
                notify = function () {
                    promise.then(flush);
                };
                // for other environments - macrotask based on:
                // - setImmediate
                // - MessageChannel
                // - window.postMessag
                // - onreadystatechange
                // - setTimeout
            } else {
                notify = function () {
                    // strange IE + webpack dev server bug - use .call(global)
                    macrotask.call(global, flush);
                };
            }

            return function (fn) {
                var task = { fn: fn, next: undefined };
                if (last) last.next = task;
                if (!head) {
                    head = task;
                    notify();
                } last = task;
            };
        };


        /***/ }),
    /* 97 */
    /***/ (function(module, exports, __webpack_require__) {

        var global = __webpack_require__(2);
        var navigator = global.navigator;

        module.exports = navigator && navigator.userAgent || '';


        /***/ }),
    /* 98 */
    /***/ (function(module, exports, __webpack_require__) {

        var hide = __webpack_require__(4);
        module.exports = function (target, src, safe) {
            for (var key in src) {
                if (safe && target[key]) target[key] = src[key];
                else hide(target, key, src[key]);
            } return target;
        };


        /***/ }),
    /* 99 */
    /***/ (function(module, exports, __webpack_require__) {

        "use strict";

        var global = __webpack_require__(2);
        var core = __webpack_require__(0);
        var dP = __webpack_require__(8);
        var DESCRIPTORS = __webpack_require__(6);
        var SPECIES = __webpack_require__(1)('species');

        module.exports = function (KEY) {
            var C = typeof core[KEY] == 'function' ? core[KEY] : global[KEY];
            if (DESCRIPTORS && C && !C[SPECIES]) dP.f(C, SPECIES, {
                configurable: true,
                get: function () { return this; }
            });
        };


        /***/ }),
    /* 100 */
    /***/ (function(module, exports, __webpack_require__) {

        var ITERATOR = __webpack_require__(1)('iterator');
        var SAFE_CLOSING = false;

        try {
            var riter = [7][ITERATOR]();
            riter['return'] = function () { SAFE_CLOSING = true; };
            // eslint-disable-next-line no-throw-literal
            Array.from(riter, function () { throw 2; });
        } catch (e) { /* empty */ }

        module.exports = function (exec, skipClosing) {
            if (!skipClosing && !SAFE_CLOSING) return false;
            var safe = false;
            try {
                var arr = [7];
                var iter = arr[ITERATOR]();
                iter.next = function () { return { done: safe = true }; };
                arr[ITERATOR] = function () { return iter; };
                exec(arr);
            } catch (e) { /* empty */ }
            return safe;
        };


        /***/ }),
    /* 101 */
    /***/ (function(module, exports, __webpack_require__) {

        "use strict";
// https://github.com/tc39/proposal-promise-finally

        var $export = __webpack_require__(7);
        var core = __webpack_require__(0);
        var global = __webpack_require__(2);
        var speciesConstructor = __webpack_require__(66);
        var promiseResolve = __webpack_require__(69);

        $export($export.P + $export.R, 'Promise', { 'finally': function (onFinally) {
                var C = speciesConstructor(this, core.Promise || global.Promise);
                var isFunction = typeof onFinally == 'function';
                return this.then(
                    isFunction ? function (x) {
                        return promiseResolve(C, onFinally()).then(function () { return x; });
                    } : onFinally,
                    isFunction ? function (e) {
                        return promiseResolve(C, onFinally()).then(function () { throw e; });
                    } : onFinally
                );
            } });


        /***/ }),
    /* 102 */
    /***/ (function(module, exports, __webpack_require__) {

        "use strict";

// https://github.com/tc39/proposal-promise-try
        var $export = __webpack_require__(7);
        var newPromiseCapability = __webpack_require__(55);
        var perform = __webpack_require__(68);

        $export($export.S, 'Promise', { 'try': function (callbackfn) {
                var promiseCapability = newPromiseCapability.f(this);
                var result = perform(callbackfn);
                (result.e ? promiseCapability.reject : promiseCapability.resolve)(result.v);
                return promiseCapability.promise;
            } });


        /***/ }),
    /* 103 */
    /***/ (function(module, exports, __webpack_require__) {

        module.exports = { "default": __webpack_require__(104), __esModule: true };

        /***/ }),
    /* 104 */
    /***/ (function(module, exports, __webpack_require__) {

        var core = __webpack_require__(0);
        var $JSON = core.JSON || (core.JSON = { stringify: JSON.stringify });
        module.exports = function stringify(it) { // eslint-disable-line no-unused-vars
            return $JSON.stringify.apply($JSON, arguments);
        };


        /***/ }),
    /* 105 */,
    /* 106 */,
    /* 107 */,
    /* 108 */,
    /* 109 */,
    /* 110 */,
    /* 111 */,
    /* 112 */,
    /* 113 */,
    /* 114 */,
    /* 115 */,
    /* 116 */,
    /* 117 */,
    /* 118 */,
    /* 119 */
    /***/ (function(module, exports, __webpack_require__) {

        "use strict";
        /* global XMLHttpRequest, CDN_URL, GATEWAY_URL, URL */


        var _regenerator = __webpack_require__(63);

        var _regenerator2 = _interopRequireDefault(_regenerator);

        var _stringify = __webpack_require__(103);

        var _stringify2 = _interopRequireDefault(_stringify);

        var _asyncToGenerator2 = __webpack_require__(64);

        var _asyncToGenerator3 = _interopRequireDefault(_asyncToGenerator2);

        var _utils = __webpack_require__(40);

        var _storage = __webpack_require__(56);

        var _storage2 = _interopRequireDefault(_storage);

        function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

        (function () {
            function currentScript() {
                // use native implementation of document.currentScript if exists
                if (document.currentScript) {
                    return document.currentScript;
                }
                // IE does not support document.currentScript and there is no reliable polyfill for all versions :-(
                // https://stackoverflow.com/questions/403967/how-may-i-reference-the-script-tag-that-loaded-the-currently-executing-script#answer-22745553
                var regex = /\/\/cdn\..*\.oyst\..*\/1click\/script\/super-tag\.js/;
                function isMe(scriptElem) {
                    var src = scriptElem.getAttribute('src');
                    return src ? regex.test(src) : false;
                }
                var scripts = document.getElementsByTagName('script');
                for (var i = 0; i < scripts.length; ++i) {
                    if (isMe(scripts[i])) {
                        return scripts[i];
                    }
                }
            }

            var script = currentScript();
            var src = script.getAttribute('src');
            window.__OYST__ = window.__OYST__ || {};

            var merchantId = window.__OYST__.merchantId || (0, _utils.getParams)(new URL(src).search)['omid'];
            window.__OYST__.merchantId = merchantId;
            var scriptUrl = "//cdn.test1.oyst.eu" + '/1click/script/script.min.js';
            var apiBaseUrl = "http://dijon.bwagence.fr:8080" + '/v1';

            /****************************************
             * Button configuration retrieval
             ****************************************/

            var configLoaded = false;
            var defaultButtonConfig = {
                cart: {
                    dataProperties: {
                        context: 'cart',
                        smart: true
                    },
                    position: {}
                },
                product: {
                    dataProperties: {
                        smart: true
                    },
                    position: {}
                }
            };
            var buttonConfig = defaultButtonConfig;
            function getButtonConfig() {
                var configUrl = apiBaseUrl + '/button-config/' + merchantId;
                var xhr = new XMLHttpRequest();
                xhr.open('GET', configUrl);
                xhr.onload = function () {
                    if (xhr.status === 200) {
                        var response = JSON.parse(xhr.responseText);
                console.log(response);
                        buttonConfig = (0, _utils.merge)(response.button_config, defaultButtonConfig);
                        configLoaded = true;
                        injectButton();
                    } else if (_storage2.default.get('forceButtonDisplay')) {
                        log('error', new Error('Received code ' + xhr.status + ' for GET /button-config'));
                    }
                };
                xhr.send();
            }

            /****************************************
             * Button injection
             ****************************************/

            function injectButton() {
                // If the config is not loaded or the page is not loaded, do nothing
                if (document.readyState === 'uninitialized' || document.readyState === 'loading' || !configLoaded || window.__OYST__ && window.__OYST__.disable) {
                    return;
                }

                var config = void 0,
                    getItemsFn = void 0,
                    refElmnt = void 0;
                var fromCart = false;
                if ((buttonConfig.product.activated || _storage2.default.get('forceButtonDisplay')) && buttonConfig.product.position.selector) {
                    config = buttonConfig.product;
                    getItemsFn = getProductPageItem;
                    refElmnt = document.querySelector(config.position.selector);
                }
                if (!refElmnt && (buttonConfig.cart.activated || _storage2.default.get('forceButtonDisplay')) && buttonConfig.cart.position.selector) {
                    config = buttonConfig.cart;
                    getItemsFn = getCartPageItems;
                    refElmnt = document.querySelector(config.position.selector);
                    fromCart = true;
                }

                // If we did not find a place to put the button
                if (!refElmnt) {
                    return log('error', 'Could not find a element to inject one click button');
                }

                // Create the div where the button will be injected
                config.dataProperties.smart = true;
                var btStyle = [];
                var btElmnt = document.createElement('div');
                btElmnt.setAttribute('id', 'oyst-1click-button');
                for (var property in config.dataProperties) {
                    btElmnt.setAttribute('data-' + property, String(config.dataProperties[property]));
                    if (['height', 'width'].includes(property)) {
                        btStyle.push(property + ': ' + config.dataProperties[property]);
                    }
                }
                if (btStyle.length) {
                    btElmnt.setAttribute('style', btStyle.join(';'));
                }
                if (config.position && config.position.after) {
                    refElmnt.parentNode.insertBefore(btElmnt, refElmnt.nextSibling);
                } else {
                    refElmnt.parentNode.insertBefore(btElmnt, refElmnt);
                }

                // Create the function that retrieves the one-click url
                var preload = true;
                // eslint-disable-next-line no-unused-vars
                window.__OYST__.getOneClickURL = function () {
                    var _ref = (0, _asyncToGenerator3.default)( /*#__PURE__*/_regenerator2.default.mark(function _callee(cb) {
                        var cart, cartItems, xhr;
                        return _regenerator2.default.wrap(function _callee$(_context) {
                            while (1) {
                                switch (_context.prev = _context.next) {
                                    case 0:
                                        cart = void 0, cartItems = void 0;
                                        _context.prev = 1;
                                        _context.next = 4;
                                        return getCart();

                                    case 4:
                                        cart = _context.sent;
                                        _context.next = 7;
                                        return getItemsFn();

                                    case 7:
                                        cartItems = _context.sent;
                                        _context.next = 15;
                                        break;

                                    case 10:
                                        _context.prev = 10;
                                        _context.t0 = _context['catch'](1);

                                        log('error', 'An error occured while retrieving one click items');
                                        log('error', _context.t0);
                                        return _context.abrupt('return', cb(_context.t0));

                                    case 15:
                                        xhr = new XMLHttpRequest();

                                        xhr.open('POST', apiBaseUrl + '/authorize-url');
                                        xhr.setRequestHeader('Content-Type', 'application/json');
                                        xhr.onload = function () {
                                            if (xhr.status === 200) {
                                                var response = JSON.parse(xhr.responseText);
                                                cb(null, response.url);
                                            } else {
                                                cb(new Error('Received code ' + xhr.status + ' from POST /authorize-url'));
                                            }
                                        };
                                        xhr.send((0, _stringify2.default)({
                                            cart: cart,
                                            cartItems: cartItems,
                                            cta: config.cta,
                                            fromCart: fromCart,
                                            merchantId: merchantId,
                                            preload: preload
                                        }));
                                        preload = false;

                                    case 21:
                                    case 'end':
                                        return _context.stop();
                                }
                            }
                        }, _callee, this, [[1, 10]]);
                    }));

                    return function (_x) {
                        return _ref.apply(this, arguments);
                    };
                }();

                // Inject the one click script
                var scriptElmt = document.createElement('script');
                scriptElmt.setAttribute('src', scriptUrl);
                document.querySelector('body').appendChild(scriptElmt);
            }

            /****************************************
             * CartId, products referencies
             * and quantities retrieval
             ****************************************/

            function getCart() {
                return window.__OYST__.getCart ? window.__OYST__.getCart() : null;
            }

            function getProductPageItem() {
                return window.__OYST__.getProductPageItem ? window.__OYST__.getProductPageItem() : null;
            }

            function getCartPageItems() {
                return window.__OYST__.getCartPageItems ? window.__OYST__.getCartPageItems() : null;
            }

            /****************************************
             * Button activation by storage
             ****************************************/

            var oystActivation = (0, _utils.getParams)(new URL(window.location.href).search)['oystActivation'];
            if (oystActivation === 'true') {
                _storage2.default.set({ key: 'forceButtonDisplay', value: true });
            } else if (oystActivation === 'false') {
                _storage2.default.set({ key: 'forceButtonDisplay', value: false });
            }

            /****************************************
             * Log activation by storage
             ****************************************/

            var oystLogs = (0, _utils.getParams)(new URL(window.location.href).search)['oystLogs'];
            if (oystLogs === 'true') {
                _storage2.default.set({ key: 'showConsoleLog', value: true });
            } else if (oystLogs === 'false') {
                _storage2.default.set({ key: 'showConsoleLog', value: false });
            }

            function log() {
                if (_storage2.default.get('showConsoleLog')) {
                    var _console;

                    var level = 'log';

                    for (var _len = arguments.length, args = Array(_len), _key = 0; _key < _len; _key++) {
                        args[_key] = arguments[_key];
                    }

                    if (['log', 'info', 'warn', 'error'].includes(args[0])) {
                        level = args.shift();
                    }
                    (_console = console)[level].apply(_console, args); // eslint-disable-line no-console
                }
            }

            /****************************************
             * Retrieve configuration
             * and inject button as soon as possible
             ****************************************/

            // We can get the configuration has soon as possible
            getButtonConfig();
            // For the injection of the button, we have to wait for the config and for the page (dom?) to be loaded
            if (document.readyState !== 'uninitialized' && document.readyState !== 'loading') {
                injectButton();
            } else {
                document.addEventListener('DOMContentLoaded', injectButton);
            }
        })();

        /***/ })
    /******/ ]);
