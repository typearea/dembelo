require.config({
    baseUrl: "/bundles/admin/js/"
});

require(["admin"], function (dembeloAdmin) {

    webix.attachEvent("onBeforeAjax",
        function(mode, url, data, request, headers){
            headers["X-Requested-With"] = "XMLHttpRequest";
        }
    );
    webix.ui(dembeloAdmin.getUiJson());

    dembeloAdmin.init();
});