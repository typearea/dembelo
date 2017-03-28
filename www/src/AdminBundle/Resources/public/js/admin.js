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

define(function () {
    function showError(msg) {
        webix.modalbox({
            title: "Fehler",
            buttons: ["Ok"],
            text: msg
        });
    }

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

    function formsave(type) {
        var id = type + "form",
            values = $$(id).getValues();
        values['formtype'] = type;

        if (!$$(id).validate()) {
            return;
        }

        webix.ajax().post(window.paths.adminFormSave, values, function (text) {
            var params = JSON.parse(text);
            if (params.session_expired) {
                window.location = window.paths.login;
                return;
            }
            if (params['error'] === false) {
                $$("topicuploadimagelist").clearAll();
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
                showError("Das Speichern ist leider fehlgeschlagen...");
            }
        });
    }

    function userSave() {
        formsave("user");
    }

    function licenseeSave() {
        formsave("licensee");
    }

    function topicSave() {
        formsave("topic");
    }

    function importfileSave() {
        formsave("importfile");
    }

    function importfileImport() {
        var importfileId = $$('importfilegrid').getSelectedId().id;

        webix.ajax().post(window.paths.adminImport, {importfileId: importfileId}, {
            success: function (text) {
                var params = JSON.parse(text);
                if (params.session_expired) {
                    window.location = window.paths.login;
                    return;
                }
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

    function getToolbar(type) {

        var clickString;

        switch(type) {
            case 'user':
                clickString = "$$('usergrid').add({id: 'new', email: '', roles: 'ROLE_USER'})";
                break;
            case 'licensee':
                clickString = "$$('licenseegrid').add({id: 'new', name: ''})";
                break;
            case 'topic':
                clickString = "$$('topicgrid').add({id: 'new', name: '', status: 0})";
                break;
            case 'importfile':
                clickString = "$$('importfilegrid').add({id: 'new', name: ''});";
                break;
        }

        return {
            view: "toolbar",
            cols: [
                {
                    id: "newBtn" + type,
                    view: "button",
                    value: "Neu",
                    type: "form",
                    click: clickString
                }
            ]
        };
    }

    function sendActivationMail() {
        var userId = $$('usergrid').getSelectedId().id;

        webix.ajax().post(paths.adminUserActivationMail, {userId: userId}, function (text) {
            var params = JSON.parse(text);
            if (params['error'] === false) {
                webix.modalbox({
                    title: "Aktivierungsmail versandt",
                    buttons: ["Ok"],
                    text: "Die Email zur Aktivierung wurde erfolgreich versandt."
                });
            } else {
                showError("Der Mailversandt ist leider fehlgeschlagen...");
            }
        });
    }

    function buildStatusFilter(grid) {
        var filter = grid.getFilter("status"),
            oldStatusValue = filter.value
        filter.innerHTML = "<option value></option>" +
            "<option value=\"0\">inaktiv</option>" +
            "<option value=\"1\">aktiv</option>";

        filter.value = oldStatusValue;
    }

    function ajaxCallback(text, response) {
        if (response.json().session_expired) {
            window.location = window.paths.login;
        }
    }

    return {
        init: function () {
            $$("mainnav").attachEvent("onAfterSelect", function (id){
                if (id === '1') {
                    $$('usergrid').clearAll();
                    $$('usergrid').load(window.paths.adminUsers, ajaxCallback);
                    $$('userstuff').show();
                } else if (id === '2') {
                    $$('licenseegrid').clearAll();
                    $$('licenseegrid').load(window.paths.adminLicensees, ajaxCallback);
                    $$('licenseestuff').show();
                } else if (id === '3') {
                    $$('topicgrid').clearAll();
                    $$('topicgrid').load(window.paths.adminTopics, ajaxCallback);
                    $$('topicstuff').show();
                } else if (id === '4') {
                    $$('importfilegrid').clearAll();
                    $$('importfilegrid').load(window.paths.adminImportfiles, ajaxCallback);
                    $$('importfilestuff').show();
                } else if (id === '5') {
                    $$('textnodegrid').clearAll();
                    $$('textnodegrid').load(window.paths.adminTextnodes, ajaxCallback);
                    $$('textnodestuff').show();
                }
            });

            $$("mainnav").select(1);
            $$('usergrid').load(window.paths.adminUsers, ajaxCallback);
            $$('userstuff').show();

            $$('userform').attachEvent('onValues', checkFormBindStatus);
            $$('textnodeform').attachEvent('onValues', checkFormBindStatus);
            $$('importfileform').attachEvent('onValues', checkFormBindStatus);
            $$('topicform').attachEvent('onValues', checkFormBindStatus);


            $$('userform').bind($$('usergrid'));
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

            $$('topicuploadimage').attachEvent("onUploadComplete", function(response) {
                if (response.status === "error") {
                    showError("Das Hochladen des Bildes ist fehlgeschlagen...");
                }
                $$('topicform').setValues(response, true);
            });

            $$('uploadfile').attachEvent("onUploadComplete", function(response) {
                $$('importfileform').setValues(response, true);
            });

            $$('importfileform').bind($$('importfilegrid'));
            $$('importfileform').attachEvent('onChange', importfileCheckActions);
            $$('importfileform').attachEvent('onValues', importfileCheckActions);


            $$('licenseeform').bind($$('licenseegrid'));
            $$('licenseeform').attachEvent('onValues', checkFormBindStatus);


            $$("topicgrid").attachEvent("onAfterLoad", function () {
                buildStatusFilter($$("topicgrid"));
            });

            $$("topicform").bind($$("topicgrid"));
            $$("textnodeform").bind($$("textnodegrid"));
        },

        getUiJson: function () {
            return {
                rows: [
                    {
                        view: "template",
                        type: "header", template: "Was zu lesen - Admin Area"
                    },
                    {
                        cols: [
                            {
                                id: "mainnav",
                                view: "tree",
                                gravity: 0.15,
                                select: true,
                                data: window.mainMenuData
                            },
                            {view: "resizer"},
                            {
                                rows: [
                                    {
                                        fitBiggest:true,
                                        multiview: true,
                                        cells: [
                                            {
                                                id: "userstuff",
                                                cols: [
                                                    {
                                                        rows: [
                                                            getToolbar('user'),
                                                            {
                                                                id: "usergrid",
                                                                view: "datatable",
                                                                autoConfig: true,
                                                                select: true,
                                                                datatype: "json",
                                                                columns: [
                                                                    {id: 'email', header: ['Email', {content: 'serverFilter'}], fillspace: true},
                                                                    {id: 'status', header: ['Status', {content: 'serverSelectFilter'}], format: function (value) { if (value === 0) return 'inaktiv'; else return 'aktiv';}},
                                                                    {id: 'roles', header: 'Rolle', format:function(value){ switch(value){case 'ROLE_ADMIN': return 'Admin';case 'ROLE_LICENSEE': return 'Lizenznehmer';} return 'Leser';}}
                                                                ]
                                                            }
                                                        ]
                                                    },
                                                    {view: "resizer"},
                                                    {
                                                        view: "scrollview",
                                                        scroll: "y",
                                                        body: {
                                                            rows: [
                                                                {
                                                                    view: "form",
                                                                    id: "userform",
                                                                    gravity: 0.5,
                                                                    elements: [
                                                                        {view: "text", name: "email", label: "Email", validate:webix.rules.isEmail},
                                                                        {cols: [
                                                                            {view: "label", label: "Angelegt", width: 80},
                                                                            {view: "label", id: "userformcreated", name: "created"},
                                                                        ]},
                                                                        {cols: [
                                                                            {view: "label", label: "Aktualisiert", width: 80},
                                                                            {view: "label", id: "userformcreated", name: "updated"},
                                                                        ]},
                                                                        {view: "combo", id: "userformrole", name: "roles", label: "Rolle", options: [{id:"ROLE_ADMIN", value: "Admin"}, {id:"ROLE_USER", value: "Leser"}, {id:"ROLE_LICENSEE", value: "Lizenznehmer"}], validate:webix.rules.isNotEmpty},
                                                                        {view: "combo", id: "userformlicensee", name: "licenseeId", label: "Lizenznehmer", suggest: paths.adminLicenceeSuggest, disabled: true},
                                                                        {view: "combo", id: "userformgender", name: "gender", label: "Geschlecht", options: [{id: 'm', value: 'männlich'},{ id: 'f', value: 'weiblich'}]},
                                                                        {view: "combo", id: "userformstatus", name: "status", label: "Status", options: [{id: '0', value: 'inaktiv'}, {id: '1', value: 'aktiv'}], validate:webix.rules.isNotEmpty},
                                                                        {view: "textarea", id: "userformsource", name: "source", label: "Quelle", height: 100},
                                                                        {view: "textarea", id: "userformreason", name: "reason", label: "Grund", height: 100},
                                                                        {view: "text", name: "password", type:"password", label:"Passwort"},
                                                                        {view: "button", id: "userformactivation", value:"Aktivierungsmail verschicken", click: sendActivationMail, disabled: true},
                                                                        {view: "button", value:"Speichern", click:userSave }
                                                                    ]
                                                                }
                                                            ]
                                                        }
                                                    }
                                                ]
                                            },
                                            {
                                                id: "licenseestuff",
                                                cols: [
                                                    {
                                                        rows: [
                                                            getToolbar('licensee'),
                                                            {
                                                                id: "licenseegrid",
                                                                view: "datatable",
                                                                autoConfig: true,
                                                                select: true,
                                                                datatype: "json"
                                                            }
                                                        ]
                                                    },
                                                    {
                                                        view: "form",
                                                        id: "licenseeform",
                                                        gravity: 0.5,
                                                        elements: [
                                                            {view: "text", name: "name", label: "Name", validate:webix.rules.isNotEmpty},
                                                            {view: "button", value:"Speichern", click:licenseeSave }
                                                        ]
                                                    }
                                                ]
                                            },
                                            {
                                                id: "topicstuff",
                                                cols: [
                                                    {
                                                        rows: [
                                                            getToolbar('topic'),
                                                            {
                                                                id: "topicgrid",
                                                                view: "datatable",
                                                                autoConfig: true,
                                                                select: true,
                                                                datatype: "json",
                                                                columns: [
                                                                    {id: 'name', header: ['Name', {content: 'serverFilter'}], fillspace: true},
                                                                    {id: 'status', header: ['Status', {content: 'serverSelectFilter'}], format: function (value) { if (value === '0') return 'inaktiv'; else return 'aktiv';}},
                                                                    {id: 'sortKey', header: ["Sortierschlüssel"]},
                                                                ]
                                                            }
                                                        ]
                                                    },
                                                    {view: "resizer"},
                                                    {
                                                        view: "scrollview",
                                                        scroll: "y",
                                                        body: {
                                                            rows: [
                                                                {
                                                                    view: "form",
                                                                    id: "topicform",
                                                                    gravity: 0.5,
                                                                    disabled: true,
                                                                    elements: [
                                                                        {view: "text", name: "name", label: "Name", validate:webix.rules.isNotEmpty},
                                                                        {view: "combo", id: "topicformstatus", name: "status", label: "Status", options: [{id:"0", value: "inaktiv"}, {id:"1", value: "aktiv"}], validate:webix.rules.isNotEmpty},
                                                                        {view: "text", id: "topicformsortkey", name: "sortKey", label: "Sortierschlüssel", validate:webix.rules.isNumber()},
                                                                        {view: "text", name: "originalImageName", label: "Bild", disabled: true},
                                                                        {
                                                                            view:"uploader",
                                                                            id: "topicuploadimage",
                                                                            value:"Dateiauswahl",
                                                                            link:"topicuploadimagelist",
                                                                            upload: paths.adminTopicImageUploader,
                                                                            multiple: false
                                                                        },
                                                                        {
                                                                            view:"list",
                                                                            id:"topicuploadimagelist",
                                                                            type:"uploader",
                                                                            autoheight:true,
                                                                            borderless:true
                                                                        },
                                                                        {view: "button", value:"Speichern", click:topicSave }
                                                                    ]
                                                                }
                                                            ]
                                                        }
                                                    }
                                                ]
                                            },
                                            {
                                                id: "importfilestuff",
                                                cols: [
                                                    {
                                                        rows: [
                                                            getToolbar('importfile'),
                                                            {
                                                                id: "importfilegrid",
                                                                view: "datatable",
                                                                autoConfig: true,
                                                                select: true,
                                                                datatype: "json",
                                                                columns: [
                                                                    {id: 'name', header: 'Name', fillspace: true},
                                                                    {id: 'author', header: 'Autor'},
                                                                    {id: 'publisher', header: 'Verlag'},
                                                                    {id: 'imported', header: 'Importiert'}
                                                                ]
                                                            }
                                                        ]
                                                    },
                                                    {view: "resizer"},
                                                    {
                                                        view: "scrollview",
                                                        scroll: "y",
                                                        body: {
                                                            rows: [
                                                                {
                                                                    view: "form",
                                                                    id: "importfileform",
                                                                    disabled: true,
                                                                    gravity: 0.5,
                                                                    elements: [
                                                                        {view: "text", name: "name", label: "Name"},
                                                                        {view: "text", name: "author", label: "Autor"},
                                                                        {view: "text", name: "publisher", label: "Verlag"},
                                                                        {view: "combo", id: "userformlicensee", name: "licenseeId", label: "Lizenznehmer", suggest: paths.adminLicenceeSuggest},
                                                                        {view: "text", name: "orgname", label: "Datei", disabled: true},
                                                                        {
                                                                            view:"uploader",
                                                                            id: "uploadfile",
                                                                            value:"Dateiauswahl",
                                                                            link:"uploadfilelist",
                                                                            upload: window.paths.adminImportfileUploader,
                                                                            multiple: false
                                                                        },
                                                                        {
                                                                            view:"list",
                                                                            id:"uploadfilelist",
                                                                            type:"uploader",
                                                                            autoheight:true,
                                                                            borderless:true
                                                                        },
                                                                        {view: "button", id: "importfileSaveButton", value:"Speichern", click:importfileSave, disabled: true },
                                                                        {view: "button", id: "importfileImportButton", value:"Importieren", click:importfileImport, disabled: true}
                                                                    ]
                                                                }
                                                            ]
                                                        }
                                                    }
                                                ]

                                            },
                                            {
                                                id: "textnodestuff",
                                                cols: [
                                                    {
                                                        rows: [
                                                            {
                                                                id: "textnodegrid",
                                                                view: "datatable",
                                                                autoConfig: true,
                                                                select: true,
                                                                datatype: "json",
                                                                columns: [
                                                                    {id: 'created', header: 'angelegt'},
                                                                    {id: 'status', header: 'Status'},
                                                                    {id: 'beginning', header: 'Text', fillspace: true},
                                                                    {id: 'importfile', header: 'Importdatei'}
                                                                ]
                                                            }
                                                        ]
                                                    },
                                                    {
                                                        view: "form",
                                                        id: "textnodeform",
                                                        disabled: true,
                                                        gravity: 0.5,
                                                        elements: [
                                                            {view: "text", name: "id", label: "ID", disabled: true},
                                                            {view: "text", name: "created", label: "angelegt", disabled: true},
                                                            {view: "text", name: "status", label: "Status", disabled: true},
                                                            {view: "text", name: "access", label: "Access-Knoten", disabled: true},
                                                            {view: "text", name: "licensee", label: "Lizenznehmer", disabled: true},
                                                            {view: "text", name: "importfile", label: "Importdatei", disabled: true},
                                                            {view: "textarea", name: "beginning", label: "Textanfang", height: 200, disabled: true}
                                                        ]
                                                    }
                                                ]
                                            }
                                        ]
                                    }
                                ]
                            }
                        ]
                    }
                ]
            };
        }

    };

});