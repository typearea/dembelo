/** global: webix,require */
(function () {
    "use strict";

    var getUiJson = require(__dirname + '/admin.js').getUiJson,
        init = require(__dirname + '/admin.js').init;

    require('Webix');

    window.onload = function () {
        webix.attachEvent("onBeforeAjax",
            function (mode, url, data, request, headers) {
                headers["X-Requested-With"] = "XMLHttpRequest";
            }
        );
        webix.ui(getUiJson());

        init();
    }
})();