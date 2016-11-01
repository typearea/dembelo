require.config({
    baseUrl: "/bundles/admin/js/"
});

require(["admin"], function (dembeloAdmin) {

    webix.ui(dembeloAdmin.getUiJson());

    dembeloAdmin.init();
});