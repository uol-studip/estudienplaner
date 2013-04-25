
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
        /*var url = STUDIP.ABSOLUTE_URI_STUDIP + "plugins.php/estudienplaner/zsb_dateien/change_datei_profil?";
        url = url + "profil_id=" + jQuery("#profil_id").val() + "&doku_id=" + id;
        STUDIP.zsb.addToList(id, name, "#dateien", "doku_id", url, true);*/
        location.href = STUDIP.URLHelper.getURL(location.href, { 'addDocument': id, 'delete_x': 0});
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
        var id = jQuery(this).parents("tr[id]")[0].id;
        var type = id.substr(0, id.lastIndexOf("_"));
        id = id.substr(id.lastIndexOf("_") + 1);
        jQuery('<div style="text-align: center;">Wollen Sie den Datensatz tatsächlich löschen?<br>' +
                        '<div class="studip_button" onClick="' + "location.href='" + STUDIP.URLHelper.getURL(location.href, {'delete_x': 1, 'item_id': id, 'type': type, 'addDocument': 0}) + "'" + '">löschen</div> ' +
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
    jQuery(".accordion").each(function () {
        var active = jQuery(this).data().active || 0,
            input  = jQuery('<input type="hidden" name="active_tab"/>').val(active);
        jQuery(this).closest('form').append(input);
        jQuery(this).accordion({
            collapsible: true,
            autoHeight: false,
            active: active
        });
        jQuery(this).bind('accordionchange', function (event, ui) {
            input.val(ui.options.active || 0);
        });
    });
    jQuery('.accordion textarea:not(.clean)').addToolbar(STUDIP.Markup ? STUDIP.Markup.buttonSet : STUDIP.Toolbar.buttonset);

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
});

//
(function ($) {

    var replaceSelection = function (element, text) {
        var scroll_top = element.scrollTop;
        if (!!document.selection) {
            element.focus();
            var range = document.selection.createRange();
            range.text = text;
            range.select();
        } else if (!!element.setSelectionRange) {
            var selection_start = element.selectionStart;
            element.value = element.value.substring(0, selection_start) +
                text +
                element.value.substring(element.selectionEnd);
            element.setSelectionRange(selection_start + text.length,
                                      selection_start + text.length);
        }
        element.focus();
        element.scrollTop = scroll_top;
    };

    $('a.open-in-dialog').live('click', function () {
        var href = $(this).attr('href');
        $.get(href, function (response) {
            var $content = $(response),
                title    = $content.find(':header').remove().text(),
                buttons  = {};

            buttons["Speichern".toLocaleString()] = function () {
                $content.find('label.erroneous').removeClass('error');

                var probes = ['code', 'title', 'content'],
                    error = false,
                    temp, i;
                for (i = 0; i < probes.length; i++) {
                    temp = $content.find('[name=' + probes[i] + ']');
                    if (!temp.val()) {
                        if (!error) {
                            temp.focus();
                            error = true;
                        }
                        temp.one('change', function () {
                            $(this).siblings('label').removeClass('erroneous');
                        }).siblings('label').addClass('erroneous');
                    }
                }
                if (!error) {
                    $content.filter('form').submit();
                }
            };
            buttons["Vorschau".toLocaleString()] = function () {
                var content = $('textarea[name=content]').val();
                $.get(href, {content: content, preview: true}, function (response) {
                    var preview = $(response).filter('#preview');
                    $('<div/>').html(preview).dialog({
                        title: "Vorschau".toLocaleString(),
                        width: $(window).width() * 3 / 4,
                        height: $(window).height() * 3 / 4,
                        buttons: {
                            "Ok": function () { $(this).dialog('close'); }
                        },
                        modal: true
                    });
                });
            };
            buttons["Abbrechen".toLocaleString()] = function () {
                $content.dialog('close');
            };

            $content.find('.type-button').remove();

            var var_button = $('<button>#{var}</button>').click(function () {
                alert('variables');
                return false;
            });
            $content.find('.add_toolbar').addToolbar(STUDIP.Markup ? STUDIP.Markup.buttonSet : STUDIP.Toolbar.buttonset).prev().append(var_button);

            $content.dialog({
                title: title,
                width: $(window).width() * 2 / 3,
                height: $(window).height() * 2 / 3,
                buttons: buttons,
                modal: true
            });
        });
        return false;
    });

}(jQuery));

//
jQuery(function ($) {

    jQuery('#texteditor-combinations').on('change', 'input[name=language]', function () {
        var index = jQuery(this).val() === 'de' ? 0 : 1;
        jQuery('#texteditor-choices').tabs('select', index);
    });

    var addSelection = function (stage, element) {
        var stage_id = stage.attr('id'),
            id       = element.find('input[type=hidden]').val(),
            text     = element.find('span.content').text(),
            li       = $('<li class="ui-widget-content"/>').text(text);
        $('<input type="hidden" name="textcombination[' + stage_id + '][' + id +'][semester]" value="always"/>').appendTo(li);
        $('<input type="hidden" name="textcombination[' + stage_id + '][' + id +'][restriction]" value="always"/>').appendTo(li);
        $('<span class="options"><a href="#" class="semester selector"><span class="selected" title="Wintersemester">W</span><span class="selected" title="Sommersemester">S</span></a> <a href="#" class="restriction selector"><span class="selected" title="zulassungsbeschränkt">Z</span><span class="selected" title="zulassungsfrei">F</span></a> <a href="#" class="remove">Eintrag entfernen</a></span>').prependTo(li);
        $('ul li.empty', stage).hide();
        $('ul', stage).append(li);

        stage.sortable('refresh');
    }

    $('.tabify').tabs();

    $('#texteditor-choices .draggable').draggable({
        revert: 'valid',
        cursor: 'move',
        helper: function (event) {
            var content = $(this).find('.content').text(),
                width   = $(this).width();
            return $('<div class="ui-widget-content"/>').text(content).width(width);
        }
    });
    $('#texteditor-stage').droppable({
        accept: '.choice',
        activeClass: 'active',
        hoverClass: 'hovered',
        drop: function (event, ui) {
            var stage = $(this).find('.stage:visible');
            addSelection(stage, ui.draggable);
       }
    });
    $('#texteditor-stage .stage .selection').sortable({
        axis: 'y',
        placeholder: 'sortable-placeholder'
    });

    $('#texteditor-stage .selector').live('click', function () {
        var spans = $('span', this),
            state = 0,
            pos   = 1,
            index = $(this).hasClass('semester') ? 'semester' : 'restriction',
            value = 'always';
        spans.each(function () {
            if ($(this).is('.selected')) {
                state += pos;
            }
            pos += 1;
        });
        if (state === 1) {
            value =  $('span', this).removeClass('selected').last().addClass('selected').text().toLowerCase();
        } else if (state === 2) {
            $('span', this).addClass('selected');
        } else if (state === 3) {
            value = $('span', this).removeClass('selected').first().addClass('selected').text().toLowerCase();
        }
        if (value === 'z') {
            value = 'b';
        }
        $(this).closest('li').find('input[type=hidden][name*=' + index + ']').val(value);
        return false;
    });

    $('#texteditor-stage .options .remove').live('click', function () {
        $(this).closest('li').hide('slow', function () {
            var stage = $(this).closest('.stage');
            if ($(this).siblings().length === 1) {
                $(this).siblings().show();
            }
            $(this).remove();
            stage.sortable('refresh');
        });
        return false;
    });
    $('#texteditor-choices .options .add').live('click', function () {
        var stage   = $('#texteditor-stage .stage:visible'),
            element = $(this).closest('li');
        addSelection(stage, element);

        return false;
    });

    $('#text-preview').click(function () {
        var ids = [],
            url = $(this).attr('href');
        $('#texteditor-stage .stage:visible input[name=tb_id]').each(function () {
            var val = $(this).val();
            ids.push(val)
        });
        if (ids.length === 0) {
            alert('Keine Textbausteine ausgewählt');
        } else {
            $('<div/>').load(url, {ids: ids}, function () {
                $(this).dialog({
                    modal: true,
                    title: "Vorschau".toLocaleString(),
                    width: $(window).width() * 3 / 4,
                    height: $(window).height() * 3 / 4,
                    buttons: {
                        "Schliessen": function () {
                            $(this).dialog('close');
                        }
                    }
                });
            });
        }
        return false;
    });

    $('#texteditor-combinations input[name=language]').change(function () {
        var language = $(this).val();
        $(this).closest('li').siblings().hide().filter('.language-' + language).show();
    }).filter(':checked').change();

    $('#texteditor-combinations input[type=radio]').change(function () {
        var checkboxes = $(this).closest('ul').find('input[type=radio]:visible:checked'),
            id = '';
        checkboxes.each(function () { id += $(this).val(); });
        $('#texteditor-stage .stage').hide().filter('#' + (id || 'de1de')).show();
    }).filter(':checked').change();


    $('#texteditor-infobox #copy-from a').click(function () {
        var url       = $(this).attr('href'),
            data      = {},
            erroneous = false;
        $(this).closest('div').find('select').each(function () {
            var name  = $(this).attr('name').replace(/copy_(?:from|to)\[(.*?)\]/, '$1'),
                value = $(this).val();
            if (!value) {
                erroneous = true;
                $(this).addClass('copy-error').one('focus', function () {
                    $(this).removeClass('copy-error');
                })
            };
            data[name] = value;
        });
        if (!erroneous) {
            data.profil_id = $('#profil_id').val();
            $.getJSON(url, data, function (json) {
                if (json === false) {
                    alert("Es gibt keinen Studiengang mit dieser Fach-/Abschlusskombination.".toLocaleString());
                } else if (json !== true) {
                    if ($.isArray(json) && !json.length && !confirm("Dem ausgewählten Studiengang sind keine Textbausteine zugeordnet. Wollen Sie wirklich fortfahren?\nIhre aktuelle Textkombinationen werden gelöscht, wenn Sie fortfahren.")) {
                        return;
                    }
                    $('.selection li:not(.empty)').remove();
                    $.each(json, function (code, ids) {
                        var stage = $('#' + code);
                        $.each(ids, function (index, id) {
                            var element = $('#tb-' + id);
                            addSelection(stage, element);
                        });
                    });
                }
            });
        } else {
            alert("Bitte wählen Sie sowohl ein Fach als auch einen Abschluss aus.".toLocaleString());
        }
        return false;
    });

    $('#texteditor-infobox #copy-to a').click(function () {
        var url   = $(this).attr('href'),
            data  = {},
            count = 0,
            tb    = {},
            temp;
        $(this).closest('div').find('select').each(function () {
            var name  = $(this).attr('name').replace(/copy_(?:from|to)\[(.*?)\]/, '$1'),
                value = $(this).val();
            if (value) {
                data[name] = value;
                count += 1;
            };
        });

        if (count === 0) {
            alert("Sie müssen mindestens ein Fach, einen Abschluss oder einen Status auswählen.".toLocaleString());
        } else {
            $('.stage input[type=hidden][name^=textcombination]').each(function () {
                var temp  = $(this).attr('name').split(/\[|\]\[|\]/),
                    code  = temp[1],
                    id    = temp[2],
                    type  = temp[3],
                    value = $(this).val();
                if (!tb[code]) {
                    tb[code] = {};
                }
                if (!tb[code][id]) {
                    tb[code][id] = {};
                }
                tb[code][id][type] = value;
            });
            data.textcombinations = tb;
            data.profil_id = $('#profil_id').val();
            $.post(url, data, function (json) {
                alert("Diese Textkombinationen wurden erfolgreich in "  + json + " Profile kopiert!");
            }, 'json');
        }
        return false;
    });
});
