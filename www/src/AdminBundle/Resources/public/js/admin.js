/* Copyright (C) 2015 Michael Giesler
 *
 * This file is part of Dembelo.
 *
 * Dembelo is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Dembelo is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License 3 for more details.
 *
 * You should have received a copy of the GNU Affero General Public License 3
 * along with Dembelo. If not, see <http://www.gnu.org/licenses/>.
 */

/*global paths*/
dembeloAdmin = (function () {

    function checkFormBindStatus() {
        var values = this.getValues();

        if (values.hasOwnProperty('id')) {
            this.enable();
        } else {
            this.disable();
        }
    }

    function hasNewRow(dataTableId) {
        var newRows = $$(dataTableId).find(function (obj) {
            return obj.id === 'new';
        });
        return newRows.length > 0;
    }

    function importfileCheckActions() {
        var values = $$('importfileform').getValues();
        if (values.name !== '' && values.licenseeId !== '') {
            $$("importfileSaveButton").enable();
            if (values.id !== 'new') {
                $$("importfileImportButton").enable();
            } else {
                $$("importfileImportButton").disable();
            }
        } else {
            $$("importfileSaveButton").disable();
            $$("importfileImportButton").disable();
        }
    }

    return {
        init: function () {
            $$("mainnav").attachEvent("onAfterSelect", function (id){
                if (id == 1) {
                    $$('usergrid').clearAll();
                    $$('usergrid').load(paths.adminUsers);
                    $$('userstuff').show();
                } else if (id == 2) {
                    $$('licenseegrid').clearAll();
                    $$('licenseegrid').load(paths.adminLicensees);
                    $$('licenseestuff').show();
                } else if (id == 3) {
                    $$('topicgrid').clearAll();
                    $$('topicgrid').load(paths.adminTopics);
                    $$('topicgrid').show();
                } else if (id == 4) {
                    $$('storygrid').clearAll();
                    $$('storygrid').load(paths.adminStories);
                    $$('storygrid').show();
                } else if (id == 5) {
                    $$('importfilegrid').clearAll();
                    $$('importfilegrid').load(paths.adminImportfiles);
                    $$('importfilestuff').show();
                } else if (id == 6) {
                    $$('textnodegrid').clearAll();
                    $$('textnodegrid').load(paths.adminTextnodes);
                    $$('textnodestuff').show();
                }
            });

            $$("mainnav").select(1);
            $$('usergrid').load(paths.adminUsers);
            $$('userstuff').show();

            $$('userform').bind($$('usergrid'));
            $$('userform').attachEvent('onValues', checkFormBindStatus);
            $$('textnodeform').attachEvent('onValues', checkFormBindStatus);
            $$('importfileform').attachEvent('onValues', checkFormBindStatus);
            $$('userformrole').attachEvent('onChange', function (newValue) {
                if (newValue == 'ROLE_LICENSEE') {
                    $$('userformlicensee').enable()
                } else {
                    $$('userformlicensee').setValue('');
                    $$('userformlicensee').disable()
                }
            });
            $$('userformstatus').attachEvent('onChange', function (newValue) {
                if (newValue === 'inaktiv') {
                    $$('userformactivation').enable();
                } else {
                    $$('userformactivation').disable();
                }
            });

            $$('uploadfile').attachEvent("onUploadComplete", function(response) {
                $$('importfileform').setValues(response, true);
            });

            $$('importfileform').bind($$('importfilegrid'));
            $$('importfileform').attachEvent('onChange', importfileCheckActions);
            $$('importfileform').attachEvent('onValues', importfileCheckActions);

            $$('licenseeform').bind($$('licenseegrid'));
            $$('licenseeform').attachEvent('onValues', checkFormBindStatus);

            $$('textnodeform').bind($$('textnodegrid'));

        },
        formsave: function (type) {
            var id = type + "form",
                values = $$(id).getValues();
            values['formtype'] = type;

            if (!$$(id).validate()) {
                return;
            }

            webix.ajax().post(paths.adminFormSave, values, function (text) {
                var params = JSON.parse(text);
                if (params['error'] === false) {
                    $$(id).save();
                    if (params['newId']) {
                        $$(type + 'grid').getSelectedItem().id = params['newId'];
                    }
                    webix.modalbox({
                        title: "Gespeichert",
                        buttons: ["Ok"],
                        text: "Der Datensatz wurde erfolgreich gespeichert..."
                    });
                } else {
                    webix.modalbox({
                        title: "Fehler",
                        buttons: ["Ok"],
                        text: "Das Speichern ist leider fehlgeschlagen..."
                    });
                }
            });
        },

        delItem: function (type) {
            var id = type + "grid",
                values = {};
            values['id'] = $$(id).getSelectedId().row;
            values['formtype'] = type;

            if (values['id'] === undefined) {
                webix.modalbox({
                    title: "Fehler",
                    buttons: ["Ok"],
                    text: "Keine Zeile zum Löschen ausgewählt."
                });
                return;
            }

            webix.ajax().post(paths.adminFormDel, values, function (text) {
                var params = JSON.parse(text);
                if (params['error'] === false) {
                    $$(id).remove($$(id).getSelectedId());
                } else {
                    webix.modalbox({
                        title: "Fehler",
                        buttons: ["Ok"],
                        text: "Das Löschen ist leider fehlgeschlagen..."
                    });
                }
            });
        },

        getToolbar: function(type) {

            var clickFunction, toolbar = {
                    view: "toolbar",
                    cols: []
                };

            switch(type) {
                case 'user':
                    clickFunction = function () {
                        if (hasNewRow('usergrid')) {
                            return;
                        }
                        $$('usergrid').add({id: 'new', email: '', roles: 'ROLE_USER'});
                        $$('usergrid').select('new');
                    };
                    break;
                case 'licensee':
                    clickFunction = function () {
                        if (hasNewRow('licenseegrid')) {
                            return;
                        }
                        $$('licenseegrid').add({id: 'new', name: ''});
                        $$('licenseegrid').select('new');
                    };
                    break;
                case 'importfile':
                    clickFunction = function () {
                        if (hasNewRow('importfilegrid')) {
                            return;
                        }
                        $$('importfilegrid').add({id: 'new', name: ''});
                        $$('importfilegrid').select('new');
                    };
                    break;
            }

            toolbar.cols.push({
                id: "newBtn" + type,
                view: "button",
                value: "Neu",
                type: "form",
                click: clickFunction
            });

            if (type !== 'importfile') {
                toolbar.cols.push({
                    id: "deleteBtn" + type,
                    view: "button",
                    value: "Löschen",
                    type: "danger",
                    click: "dembeloAdmin.delItem('" + type + "')"
                });
            }

            return toolbar;
        },

        sendActivationMail: function () {
            var userId = $$('usergrid').getSelectedId().id;

            webix.ajax().post(paths.adminUserActivationMail, {userId: userId}, function (text) {
                var params = JSON.parse(text);
                if (params['error'] === false) {
                    webix.modalbox({
                        title: "Aktivierungsmail versandt",
                        buttons: ["OK"],
                        text: "Die Email zur Aktivierung wurde erfolgreich versandt."
                    });
                } else {
                    webix.modalbox({
                        title: "Fehler",
                        buttons: ["OK"],
                        text: "Der Mailversand ist leider fehlgeschlagen..."
                    });
                }
            });
        },

        import: function () {
            var importfileId = $$('importfilegrid').getSelectedId().id;

            webix.ajax().post(paths.adminImport, {importfileId: importfileId}, {
                success: function (text) {
                    var params = JSON.parse(text);
                    if (params['success'] === true && params['returnValue'] === true) {
                        webix.modalbox({
                            title: "Datei importiert",
                            buttons: ["OK"],
                            text: "Die Datei wurde erfolgreich importiert."
                        });
                    } else {
                        webix.modalbox({
                            title: "Fehler",
                            buttons: ["OK"],
                            text: "Fehler: " + params['message']
                        });
                    }
                },
                error: function (dom, obj, ajaxObj) {
                    var message = 'Unbekannter Fehler';
                    if (ajaxObj instanceof XMLHttpRequest) {
                        message = ajaxObj.statusText;
                    }
                    webix.modalbox({
                        title: "Fehler",
                        buttons: ["OK"],
                        text: "Fehler: " + message
                    });
                }
            });
        }

    };

}());