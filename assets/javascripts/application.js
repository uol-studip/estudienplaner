
STUDIP.zsb = {
    /**
     * Creates an hls-color from a given string. This color is like a hash-box
     * so that usually different strings result in different colors.
     * @param input string: any string
     * @param lightness: between 0 and 100, the lightness of the color.
     * @return: string like "hsl(120, 100%, 75%)";
     */
    createColor: function (input, lightness) {
        input = input.toString();
        if (!input) {
            return "white";
        }
        var zwischenergebnis = 1;
        for (var i = 0; i < input.length; i += 1) {
            zwischenergebnis = zwischenergebnis + input.charCodeAt(input.length - i - 1) * Math.pow(31, i);
            zwischenergebnis = zwischenergebnis % 360;
        }
        if (typeof lightness !== "undefined") {
            lightness = ((Number(lightness) <= 100 && Number(lightness) >= 0) ? lightness : 75);
        } else {
            lightness = 75;
        }
        return "hsl(" + zwischenergebnis + ", 100%, " + lightness + "%)";
    },
    
    clipboard: "",
    copyString: function (input) {
        if (typeof input === "string") {
            this.clipboard = input;
            return true;
        } else {
            this.clipboard = jQuery(input).is("input, select").length ? input.value : jQuery(input).text();
            return true;
        }
        return false;
    },
    pasteString: function (element) {
        if (this.clipboard) { 
            jQuery(element).val(this.clipboard)
                                         .trigger("change");
            return true;
        }
        return false;
    },
    
    toggleDisabling: function (profil, semester, zielgruppe) {
        if (jQuery("td.zielgruppe_" + zielgruppe + ".profil_" + profil + "." + semester + " input[type=text][disabled]").length) {
            jQuery("td.zielgruppe_" + zielgruppe + ".profil_" + profil + "." + semester + " input:not([type=checkbox])").removeAttr("disabled");
        } else {
            jQuery("td.zielgruppe_" + zielgruppe + ".profil_" +    profil + "." + semester + " input:not([type=checkbox])").attr("disabled", "disabled");
        }
    },
    
    filterTable: function (tableselector, value) {
        value = value.split(" ");
        value = jQuery.map(function (index, val) {
            if (val.length < 5) {
                return false;
            }
        });
        jQuery("tbody > tr.firstrow", tableselector).each(function (index) {
            var row_number = Number(jQuery("td:first-child", this).attr("rowspan"));
            if (!row_number) {
                row_number = 1;
            }
            var rows = jQuery(this);
            for (var i = 1; i < row_number; i += 1) {
                rows = rows.add(rows.last().next("tr"));
            }
            var vorhanden = true;
            jQuery.each(value, function (index, word) {
                //die Wörter müssen alle vorhanden sein:
                if (rows.text().toUpperCase().indexOf(word.toUpperCase()) === -1) {
                    vorhanden = false;
                }
            });
            if (vorhanden) {
                if (rows.css("display") === "none") {
                    rows.fadeIn();
                }
            } else {
                if (rows.css("display") !== "none") {
                    rows.fadeOut();
                }
            }
        });
    },
    
    addToList: function (id, name, listselector, prefix, url, edit_option) {
        if (jQuery("li#" + prefix + "_" + id, listselector).length === 0) {
            jQuery.ajax({
                url: url,
                success: function () {
                    var stripBTags = /<b>|<\/\b>/gi;
                    jQuery(listselector).append(jQuery("<li id='" + prefix + "_" + id + "'></li>")
                                                                                         .html(name.replace(stripBTags, "")));
                    jQuery("li#" + prefix + "_" + id, listselector)
                                 .css("display", "none")
                                 .append(jQuery('<a class="icon_trash"></a>'))
                                 .append(edit_option ? jQuery('<a class="icon_edit"></a>') : jQuery("<div/>"))
                                 .slideDown(function () {
                                        jQuery(this).css("display", ""); 
                                    });
                }
            });
            
        }
    },
    addToStudiengaenge: function (id, name) {
        var url = STUDIP.ABSOLUTE_URI_STUDIP + "plugins.php/estudienplaner/zsb_kontakte/change_kontakt_profil?";
        url = url + "profil_id=" + id + "&kontakt_id=" + jQuery("#kontakt_id").val();
        STUDIP.zsb.addToList(id, name, "#stg_profile", "profil_id", url, false);
    },
    addAnsprechpartnerToStudiengaenge: function (id, name) {
        var url = STUDIP.ABSOLUTE_URI_STUDIP + "plugins.php/estudienplaner/zsb_kontakte/change_kontakt_profil?";
        url = url + "profil_id=" + jQuery("#profil_id").val() + "&kontakt_id=" + id;
        STUDIP.zsb.addToList(id, name, "#ansprechpartner", "ansprechpartner", url, true);
    },
    
    addDokumentToStudiengaenge: function (id, name) {
        var url = STUDIP.ABSOLUTE_URI_STUDIP + "plugins.php/estudienplaner/zsb_dateien/change_datei_profil?";
        url = url + "profil_id=" + id + "&doku_id=" + jQuery("#doku_id").val();
        STUDIP.zsb.addToList(id, name, "#stg_dokumente_profil", "doku_id", url, false);
    },
    profilAddDokumentToStudiengaenge: function (id, name) {
        var url = STUDIP.ABSOLUTE_URI_STUDIP + "plugins.php/estudienplaner/zsb_dateien/change_datei_profil?";
        url = url + "profil_id=" + jQuery("#profil_id").val() + "&doku_id=" + id;
        STUDIP.zsb.addToList(id, name, "#dateien", "doku_id", url, true);
    },
    
    addAufbaustudiengang: function (id, name) {
        var url = STUDIP.ABSOLUTE_URI_STUDIP + "plugins.php/estudienplaner/zsb_studiengang/add_aufbaustudiengang?";
        url = url + "profil_id=" + jQuery("#profil_id").val() + "&aufbaustudiengang_id=" + id;
        STUDIP.zsb.addToList(id, name, "#aufbaustudiengaenge", "aufbau_stg_profil_id", url);
    },
    
    "addPersonalToFSB": function (id, name) {
        var dynamicname = "FSB";
        STUDIP.zsb.addPersonal(id, name, dynamicname);
    },
    "addPersonalToStuKo": function (id, name) {
        var dynamicname = "StuKo";
        STUDIP.zsb.addPersonal(id, name, dynamicname);
    },
    "addPersonalToI-Amt": function (id, name) {
        var dynamicname = "I-Amt";
        STUDIP.zsb.addPersonal(id, name, dynamicname);
    },
    "addPersonalToP-Amt": function (id, name) {
        var dynamicname = "P-Amt";
        STUDIP.zsb.addPersonal(id, name, dynamicname);
    },
    addPersonal: function (id, name, dynamicname) {
        var url = STUDIP.ABSOLUTE_URI_STUDIP + "plugins.php/estudienplaner/zsb_personal/add_personal?";
        url = url + "studiengang_id=" + jQuery("#studiengang_id").val() + "&user_id=" + id + "&rollen_typ=" + dynamicname;
        STUDIP.zsb.addToList(id, name, "#role_" + dynamicname, "role_" + dynamicname, url);
    },
    
    addAnsprechpartnerTyp: function (name) {
        var url = STUDIP.ABSOLUTE_URI_STUDIP + "plugins.php/estudienplaner/zsb_bereiche/add_ansprechpartner?";
        url = url + "bereichs_id=" + jQuery("#bereichs_id").val() + "&ansprechpartner_typ_neu=" + encodeURIComponent(name);
        var prefix = "anprechpartner_typ";
        var listselector = "#ansprechpartner_typen";
        jQuery.ajax({
            url: url,
            success: function (message) {
                var id = parseInt(message, 10);
                if (id > 0) {
                    jQuery(listselector).append(jQuery("<li id='" + prefix + "_" + id + "'></li>")
                                                                                         .html(name));
                    jQuery("li#" + prefix + "_" + id, listselector)
                                 .css("display", "none")
                                 .append(jQuery('<a class="icon_trash"></a>'))
                                 .slideDown(function () { 
                                        jQuery(this).css("display", ""); 
                                    });
                }
            }
        });
        
    },

    deleteItem: function (event, ui) {
        event.stopImmediatePropagation();
        jQuery('<div style="text-align: center;">Wollen Sie den Datensatz tatsächlich löschen?<br>' +
                        '<div class="studip_button" onClick="' + "location.href='?delete_x=true&item_id=" + jQuery(this).parents("tr[id]")[0].id + "'" + '">löschen</div> ' +
                        '<div class="studip_button" onClick="' + "jQuery(this).parent().parent().find('.ui-dialog-titlebar-close').trigger('click');" + '">abbrechen</div></div>').dialog({
            title: "Sicherheitsabfrage",
            hide: "fade",
            show: "fade"
        });
        return false;
    },

    deleteAnsprechpartnerFromProfil: function () {
        var id = jQuery('#ansprechpartner_delete_question_id').val();
        var li = jQuery('#ansprechpartner_' + id)[0];
        jQuery("#ansprechpartner_delete_question").dialog('close');
        jQuery.ajax({
            url: STUDIP.ABSOLUTE_URI_STUDIP + "plugins.php/estudienplaner/zsb_kontakte/delete_kontakt_profil",
            data: {
                kontakt_id: id,
                profil_id: jQuery("#profil_id").val()
            },
            success: function () {
                jQuery(li).slideUp(function () {
                    jQuery(this).remove();
                });
            }
        });
    },

    deleteDokumentFromProfil: function () {
        var id = jQuery('#dokument_delete_question_id').val();
        var li = jQuery('#doku_id_' + id)[0];
        jQuery("#dokument_delete_question").dialog('close');
        jQuery.ajax({
            url: STUDIP.ABSOLUTE_URI_STUDIP + "plugins.php/estudienplaner/zsb_dateien/delete_datei_profil",
            data: {
                doku_id: id,
                profil_id: jQuery("#profil_id").val()
            },
            success: function () {
                jQuery(li).slideUp(function () {
                    jQuery(this).remove();
                });
            }
        });
    }
    
};


jQuery(function () {
    jQuery("table.zsb_editable").tablesorter();
});

//Datepicker einbauen
jQuery(function () {
    var color_func = function () {
        jQuery(this).css("background-color", STUDIP.zsb.createColor(this.value));
    };
    jQuery("table.zsb_timetable input.date").bind("change", color_func).each(color_func);
    jQuery("table.zsb_timetable input.date").bind("mouseover", function () {
        if (!jQuery(this).is(".hasDatepicker")) {
            jQuery(this).datepicker({
                dateFormat: 'dd.mm.yy',
                firstDay: 1,
                constrainInput: false
            });
        }
    });
    
});


jQuery(function () {
    jQuery(".accordion").accordion({
        collapsible: true,
        autoHeight: false
    });
    jQuery('.accordion textarea:not(.clean)').addToolbar(STUDIP.Markup.buttonSet);
    
    jQuery("ul.sortable#ansprechpartner").sortable({
        axis: "y",
        revert: 200,
        containment: 'parent',
        tolerance: 'pointer',
        update: function (event, ui) {
            var order = jQuery(this).sortable('toArray');
            jQuery.each(order, function (key, element) {
                order[key] = element.substr(element.lastIndexOf("_") + 1);
            })
            jQuery.ajax({
                url: STUDIP.ABSOLUTE_URI_STUDIP + "plugins.php/estudienplaner/zsb_studiengang/kontakt_profil_order",
                data: {
                    'profil_id': jQuery("#profil_id").val(),
                    'ansprechpartner': order
                }
            });
        }
    });
    jQuery("ul.sortable#dateien").sortable({
        axis: "y",
        revert: 200,
        containment: 'parent',
        tolerance: 'pointer',
        update: function (event, ui) {
            var order = jQuery(this).sortable('toArray');
            jQuery.each(order, function (key, element) {
                order[key] = element.substr(element.lastIndexOf("_") + 1);
            })
            jQuery.ajax({
                url: STUDIP.ABSOLUTE_URI_STUDIP + "plugins.php/estudienplaner/zsb_studiengang/dokumente_profil_order",
                data: {
                    'profil_id': jQuery("#profil_id").val(),
                    'dokumente': order
                }
            });
        }
    });
});



//Und hier die Mülltonnen-Logik:

jQuery(".accordion ul#stg_profile li a.icon_trash").live("click", function (event) {
    var li = jQuery(this).parent()[0];
    var id = li.id.substr(li.id.lastIndexOf("_") + 1);
    jQuery.ajax({
        url: STUDIP.ABSOLUTE_URI_STUDIP + "plugins.php/estudienplaner/zsb_kontakte/delete_kontakt_profil",
        data: {
            kontakt_id: jQuery("#kontakt_id").val(),
            profil_id: id
        },
        success: function () {
            jQuery(li).slideUp(function () {
                jQuery(this).remove();
            });
        }
    });
});
jQuery("#dateien a.icon_edit, #interne_dokumente a.icon_edit").live("click", function (event) {
    var li = jQuery(this).parent()[0];
    var id = li.id.substr(li.id.lastIndexOf("_") + 1);
    window.location.href = STUDIP.ABSOLUTE_URI_STUDIP + "plugins.php/estudienplaner/zsb_dateien/dateien?doku_id=" + id;
});
jQuery("#ansprechpartner a.icon_trash").live("click", function (event) {
    var li = jQuery(this).parent()[0];
    var id = li.id.substr(li.id.lastIndexOf("_") + 1);
    jQuery("#ansprechpartner_delete_question_id").val(id);
    jQuery("#ansprechpartner_delete_question").dialog();
});
jQuery("#ansprechpartner a.icon_edit, #interne_ansprechpartner a.icon_edit").live("click", function (event) {
    var li = jQuery(this).parent()[0];
    var id = li.id.substr(li.id.lastIndexOf("_") + 1);
    window.location.href = STUDIP.ABSOLUTE_URI_STUDIP + "plugins.php/estudienplaner/zsb_kontakte/kontakte?kontakt_id=" + id;
});
jQuery(".accordion ul#dateien li a.icon_trash").live("click", function (event) {
    var li = jQuery(this).parent()[0];
    var id = li.id.substr(li.id.lastIndexOf("_") + 1);
    jQuery("#dokument_delete_question_id").val(id);
    jQuery("#dokument_delete_question").dialog();
});
jQuery(".accordion ul#stg_dokumente_profil li a.icon_trash").live("click", function (event) {
    var li = jQuery(this).parent()[0];
    var id = li.id.substr(li.id.lastIndexOf("_") + 1);
    jQuery.ajax({
        url: STUDIP.ABSOLUTE_URI_STUDIP + "plugins.php/estudienplaner/zsb_dateien/delete_datei_profil",
        data: {
            doku_id: jQuery("#doku_id").val(),
            profil_id: id
        },
        success: function () {
            jQuery(li).slideUp(function () {
                jQuery(this).remove();
            });
        }
    });
});

jQuery(".accordion ul#aufbaustudiengaenge li a.icon_trash").live("click", function (event) {
    var li = jQuery(this).parent()[0];
    var id = li.id.substr(li.id.lastIndexOf("_") + 1);
    jQuery.ajax({
        url: STUDIP.ABSOLUTE_URI_STUDIP + "plugins.php/estudienplaner/zsb/delete_aufbaustudiengang",
        data: {
            aufbau_stg_profil_id: id,
            profil_id: jQuery("#profil_id").val()
        },
        success: function () {
            jQuery(li).slideUp(function () { 
                jQuery(this).remove(); 
            });
        }
    });
});
//Löschen aus der Liste im Punkt Personal. Zu jedem Typ des Personals (FSB oder StuKo) gibt es eine Liste.
//Der Typ wird aus der ID des ul-Tags gefiltert:
jQuery(".accordion ul[id^=role_] li a.icon_trash").live("click", function (event) {
    var li = jQuery(this).parent()[0];
    var ul = jQuery(li).parent()[0];
    var role_type = ul.id.substr(ul.id.lastIndexOf("_") + 1);
    var id = li.id.substr(li.id.lastIndexOf("_") + 1);
    jQuery.ajax({
        url: STUDIP.ABSOLUTE_URI_STUDIP + "plugins.php/estudienplaner/zsb_personal/delete_personal",
        data: {
            user_id: id,
            studiengang_id: jQuery("#studiengang_id").val(),
            rollen_typ: role_type
        },
        success: function () {
            jQuery(li).slideUp(function () { 
                jQuery(this).remove(); 
            });
        }
    });
});

jQuery(".accordion ul#ansprechpartner_typen li a.icon_trash").live("click", function (event) {
    var li = jQuery(this).parent()[0];
    var id = li.id.substr(li.id.lastIndexOf("_") + 1);
    jQuery.ajax({
        url: STUDIP.ABSOLUTE_URI_STUDIP + "plugins.php/estudienplaner/zsb_bereiche/delete_ansprechpartner_typ",
        data: {
            ansprechpartner_typ_id: id,
            bereichs_id: jQuery("#bereichs_id").val()
        },
        success: function () {
            jQuery(li).slideUp(function () { 
                jQuery(this).remove();
            });
        }
    });
});

jQuery(".editable div.beschraenkungsbox > div > div").live("click", function () {
    jQuery(this).addClass("selected").siblings().removeClass("selected");
    jQuery(this).parent().children("input").val(jQuery(this).attr("meta_value"));
});

// Datenfelder: Anzeige der Parameter bei bestimmten Datenfeldtypen
jQuery('#feldtyp').live('change', function (event) {
    if (jQuery(this).val() === 'radio' || jQuery(this).val() === 'selectbox' || jQuery(this).val() === 'combo') {
        jQuery('#typeparam-row').slideDown('slow', function () {
            jQuery(this).css('display', 'list-item');
        });
    } else {
        jQuery('#typeparam-row').slideUp();
    }
});


jQuery("table.zsb_editable > tbody > tr[id] > td > a.icon_trash").live("click", STUDIP.zsb.deleteItem);
jQuery("table.zsb_editable > tbody > tr[url]").live("click", function () {if (jQuery(this).attr("url")) location.href = jQuery(this).attr("url");});

jQuery(function () {
    jQuery("#is_kombo").bind("change", function (event) {
        if (this.checked) {
            jQuery("#profilsuche").hide();
            jQuery("#profilsuche input[type=text]").removeAttr('required');
            jQuery("#fachkombi_suche").show();
            jQuery("#fachkombi_suche input[type=text]").attr('required', "required");
            jQuery("#sichtbar_fach2_li").show();
            jQuery("#sichtbar_fach2_li input[type=text]").attr('required', "required");
        } else {
            jQuery("#profilsuche").show();
            jQuery("#profilsuche input[type=text]").attr('required', "required");
            jQuery("#fachkombi_suche").hide();
            jQuery("#fachkombi_suche input[type=text]").removeAttr('required');
            jQuery("#sichtbar_fach2_li").hide();
            jQuery("#sichtbar_fach2_li input[type=text]").removeAttr('required');
        }
    });
})