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

    function formsave(type) {
        var id = type + "form",
            values = $$(id).getValues();
        values['formtype'] = type;

        if (!$$(id).validate()) {
            return;
        }

        webix.ajax().post(paths.adminFormSave, values, function (text) {
            var params = JSON.parse(text);
            if (params['error'] === false) {
                $$("topicuploadimagelist").clearAll();
                $$(id).save();
                if (params['newId']) {
                    $$(type + 'grid').getSelectedItem().id = params['newId'];
                }
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

    return {
        init: function () {
            $$("mainnav").attachEvent("onAfterSelect", function (id){
                if (id == 1) {
                    $$('usergrid').clearAll();
                    $$('usergrid').load(window.paths.adminUsers);
                    $$('userstuff').show();
                } else if (id == 2) {
                    $$('licenseegrid').clearAll();
                    $$('licenseegrid').load(window.paths.adminLicensees);
                    $$('licenseestuff').show();
                } else if (id == 3) {
                    $$('topicgrid').clearAll();
                    $$('topicgrid').load(window.paths.adminTopics);
                    $$('topicstuff').show();
                }
            });

            $$("mainnav").select(3);

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

            $$('licenseeform').bind($$('licenseegrid'));

            $$("topicform").bind($$("topicgrid"));
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