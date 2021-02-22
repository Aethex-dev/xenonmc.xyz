(function (undefined) {
    'use strict';
    var _root = this;
    var _serviceWorker = navigator.serviceWorker;
    if (!_serviceWorker) {
        _root.UpUp = null;
        return undefined;
    }
    var _settings = {
        'service-worker-url': 'upup.sw.min.js',
        'registration-options': {},
    };
    var _debugState = false;
    var _debugStyle = 'font-weight: bold; color: #00f;';
    _root.UpUp = {
        start: function (settings) {
            this.addSettings(settings);
            _serviceWorker.register(_settings['service-worker-url'], _settings['registration-options']).then(function (registration) {
                if (_debugState) {
                    console.log('Service worker registration successful with scope: %c' + registration.scope, _debugStyle);
                }
                var messenger = registration.installing || _serviceWorker.controller || registration.active;
                messenger.postMessage({
                    'action': 'set-settings',
                    'settings': _settings
                });
            }).catch(function (err) {
                if (_debugState) {
                    console.log('Service worker registration failed: %c' + err, _debugStyle);
                }
            });
        },
        addSettings: function (settings) {
            settings = settings || {};
            if (typeof settings === 'string') {
                settings = {
                    content: settings
                };
            }
            [
                'content',
                'content-url',
                'assets',
                'service-worker-url',
                'cache-version',
            ].forEach(function (settingName) {
                if (settings[settingName] !== undefined) {
                    _settings[settingName] = settings[settingName];
                }
            });
            if (settings['scope'] !== undefined) {
                _settings['registration-options']['scope'] = settings['scope'];
            }
        },
        debug: function (newState) {
            if (arguments.length > 0) {
                _debugState = !!newState;
            } else {
                _debugState = true;
            }
        },
    };
}.call(this));