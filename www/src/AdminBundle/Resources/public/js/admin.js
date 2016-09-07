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

    return {
        init: function () {
            $$("mainnav").attachEvent("onAfterSelect", function (id){
                if (id === "1") {
                    $$('usergrid').load(paths.adminUsers);
                    $$('userstuff').show();
                } else if (id === "2") {
                    $$('licenseegrid').load(paths.adminLicensees);
                    $$('licenseestuff').show();
                } else if (id === "3") {
                    $$('topicgrid').load(paths.adminTopics);
                    $$('topicgrid').show();
                } else if (id === "4") {
                    $$('storygrid').load(paths.adminStories);
                    $$('storygrid').show();
                } else if (id === "5") {
                    $$('importfilegrid').load(paths.adminImportfiles);
                    $$('importfilestuff').show();
                } else if (id === "6") {
                    $$('textnodegrid').load(paths.adminTextnodes);
                    $$('textnodestuff').show();
                }
            });

            $$("mainnav").select(1);

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

            $$('uploadfile').attachEvent("onUploadComplete", function(response) {
                $$('importfileform').setValues(response, true);
            });

            $$('licenseeform').bind($$('licenseegrid'));
            $$('importfileform').bind($$('importfilegrid'));
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

            var clickString, toolbar = {
                    view: "toolbar",
                    cols: []
                };

            switch(type) {
                case 'user':
                    clickString = "$$('usergrid').add({id: 'new', email: '', roles: 'ROLE_USER'})";
                    break;
                case 'licensee':
                    clickString = "$$('licenseegrid').add({id: 'new', name: ''})";
                    break;
                case 'importfile':
                    clickString = "$$('importfilegrid').add({id: 'new', name: ''})";
                    break;
            }

            toolbar.cols.push({
                id: "newBtn" + type,
                view: "button",
                value: "Neu",
                type: "form",
                click: clickString
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
                        buttons: ["Ok"],
                        text: "Die Email zur Aktivierung wurde erfolgreich versandt."
                    });
                } else {
                    webix.modalbox({
                        title: "Fehler",
                        buttons: ["Ok"],
                        text: "Der Mailversand ist leider fehlgeschlagen..."
                    });
                }
            });
        },

        import: function () {
            var importfileId = $$('importfilegrid').getSelectedId().id;

            webix.ajax().post(paths.adminImport, {importfileId: importfileId}, function (text) {
                var params = JSON.parse(text);
                if (params['error'] === false) {
                    webix.modalbox({
                        title: "Datei importiert",
                        buttons: ["Ok"],
                        text: "Die Datei wurde erfolgreich importiert."
                    });
                } else {
                    webix.modalbox({
                        title: "Fehler",
                        buttons: ["Ok"],
                        text: "Der Import ist leider fehlgeschlagen..."
                    });
                }
            });
        }

    };

}());