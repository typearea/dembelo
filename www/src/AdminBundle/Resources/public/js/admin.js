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
        }  else {
            this.disable();
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
                }
            });

            $$("mainnav").select(1);

            $$('userform').bind($$('usergrid'));
            $$('userform').attachEvent('onValues', checkFormBindStatus);
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

            $$('licenseeform').bind($$('licenseegrid'));
            $$('licenseeform').attachEvent('onValues', checkFormBindStatus);
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

            var clickString;

            switch(type) {
                case 'user':
                    clickString = "$$('usergrid').add({id: 'new', email: '', roles: 'ROLE_USER'})";
                    break;
                case 'licensee':
                    clickString = "$$('licenseegrid').add({id: 'new', name: ''})";
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
                },
                {
                    id: "deleteBtn" + type,
                    view: "button",
                    value: "Löschen",
                    type: "danger",
                    click: "dembeloAdmin.delItem('" + type + "')"
                }
            ]
            };
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
                        text: "Der Mailversandt ist leider fehlgeschlagen..."
                    });
                }
            });
        }

    };

}());