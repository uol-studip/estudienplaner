
STUDIP.eStudienplaner = {
    url: "estudienplaner/zsb_verlaufsplan",
    current_sem: 1,
    current_module: 1,
    dropCell : '',
    plusSign : '',
    minusSign: '',
    verlauf_typen: [],
    //url : '',
    verlaufsplan_id : -1,

    dragModule: function () {
            
    },

    /**
     * initialize: bind click-functions and set timeout for autosave
     */
    init : function () {
        STUDIP.eStudienplaner.makeDroppables();

        // bind click-events for changing the table
        jQuery('#add_sem').unbind('click');
        jQuery('#add_sem').live('click', function () {
            STUDIP.eStudienplaner.addSemester();
        });

        jQuery('#rem_sem').unbind('click');
        jQuery('#rem_sem').live('click', function () {
            STUDIP.eStudienplaner.removeSemester();
        });

        jQuery('#add_mod').unbind('click');
        jQuery('#add_mod').live('click', function () {
            STUDIP.eStudienplaner.addModule();
        });

        jQuery('#rem_mod').unbind('click');
        jQuery('#rem_mod').live('click', function () {
            STUDIP.eStudienplaner.removeModule();
        });

        // implode whole tree
        jQuery('.toexpand ul').each(function () {
            jQuery(this).hide();
        });

        // bind click events to explode subtrees
        //jQuery('a.expandable').unbind('click');
        jQuery('.dragzone a.expandable').live('click', function () {
            jQuery('#ul_' + jQuery(this).attr('id')).toggle();
            jQuery('#plus_' + jQuery(this).attr('id')).toggle();
            jQuery('#minus_' + jQuery(this).attr('id')).toggle();
        });

        jQuery('#edit_module').dialog({
            buttons: {
                "Ok": function () {
                    // get (changed) data
                    var kp        = jQuery('#edit_module select[name=kp]').val();
                    var type    = jQuery('#edit_module select[name=type]').val();
                    var id        = jQuery('#edit_module input[name=id]').val();
                    var notiz = jQuery('#edit_module textarea[name=notiz]').val();

                    // set data to all of these modules in the studyplan
                    
                    jQuery('.' + id)
                        .attr('data-module', STUDIP.eStudienplaner.getDataString(id, kp, type, notiz));

                    // update visible text to all of these modules in the studyplan
                    jQuery('#studyplan .' + id + ' span.kp').html('(' + kp + ' KP)');

                    //update color
                    jQuery("#studyplan ." + id)
                        .css(
                                 'background-color',
                                 jQuery("#color_legend .color[verlauf_typ_id=" + type + "]").css("background-color")
                         );

                    if (id.charAt(0) == "z") {
                        jQuery("#studyplan ." + id + " span.title").text(notiz);
                    }

                    STUDIP.eStudienplaner.recalculateKP();
                    
                    //and save it!
                    STUDIP.eStudienplaner.saveVerlaufsplan();

                    // close dialog
                    jQuery(this).dialog('close');
                }
            },
            height: 'auto',
            autoOpen: false,
            resizable: false,
            position: 'center',
            title: String('Modul bearbeiten').toLocaleString()
        });

        window.setTimeout(STUDIP.eStudienplaner.autoSaveVerlaufsplan, 60000);
    },

    //wird zurzeit nicht gebraucht:
    moduleListAsWindow: function () {
        jQuery('<div class="dragzone"/>')
            .html(jQuery("#dragzone_container > .dragzone").html())
            .dialog({
                height: 400,
                maxHeight: 1000,
                position: 'right',
                title: String('Modulliste').toLocaleString(),
                close: function (event, ui) {
                    jQuery("#dragzone_container").show();
                }
            });
        jQuery("#dragzone_container").hide();
    },

    makeDraggables: function () {
        jQuery(".dragzone a.modul").draggable({
            revert: true,
            scroll: true,
            helper: 'clone'
        });
    },


    /**
    * adds draggable and droppable areas/elements
    * rebinds/set expand and collapse-images (plus/minus)
    *
    */
    makeDroppables: function () {
        // add draggables and droppables
        jQuery(STUDIP.eStudienplaner.makeDraggables);
        jQuery(".droppable").droppable({
            hoverClass: 'drop_allowed',
            drop: function (event, ui) {
                STUDIP.eStudienplaner.droppedOnMe(this, event, ui);
            }
        });

        // remove old plus-sings and add new ones to rowspan table-cells
        jQuery('img.plus').remove();
        jQuery(".lefty").append(STUDIP.eStudienplaner.plusImage);
        // jQuery(".middy").append(STUDIP.eStudienplaner.plusImage);

        // remove plus-signs from the bottom row
        jQuery('#sem1 > td > img.plus').remove();
        jQuery('#studyplan td[rowspan=2]').each(function () {
            if (jQuery(this).hasClass('lefty')) {
                var row = parseInt(jQuery(this).attr('id').charAt(6), 10) + 1;
                var col = parseInt(jQuery(this).attr('id').charAt(8), 10);

                // remove plus from previous field
                jQuery('#field_' + row + '_' + col).find('img.plus').remove();

                // replace plus of this field with minus
                jQuery(this).find('img.plus').attr('src', STUDIP.eStudienplaner.minusSign);
            }
        });


        // add click-events to plus-signs
        jQuery('img.plus').live('click', function () {
            if (jQuery(this).src === STUDIP.eStudienplaner.minusSign) {

            } else {
                var element = jQuery(this).closest("td");
                var coord = element.attr('id').split("_");
                var prev_row = parseInt(coord[1], 10);
                var prev_col = parseInt(coord[2], 10);
                
                if (jQuery('#field_' + prev_row + '_' + prev_col).find('a').length ||
                    jQuery('#field_' + prev_row + '_' + (prev_col + 1)).find('a').length) {
                    STUDIP.eStudienplaner.expandSemester(this);
                } else {
                    STUDIP.eStudienplaner.expandSemesterApproved(this);
                }
            }
        });

        // add mouse-over effects and click events for placedmodules
        jQuery('.placedmodule').unbind('mouseover');
        jQuery('.placedmodule').bind('mouseover', function () {
            jQuery(this).find('div.icons').show();
        });

        jQuery('.placedmodule').unbind('mouseout');
        jQuery('.placedmodule').bind('mouseout', function () {
            jQuery(this).find('div.icons').hide();
        });

        STUDIP.eStudienplaner.recalculateKP();
    },

    recalculateKP: function () {
        //calculate kp's
        jQuery('#studyplan').find('tr').each(function () {
            var row = jQuery(this).attr('id').charAt(3);
            var sum = 0;
            jQuery(this).find('td').each(function () {
                var max = 0;
                jQuery(this).find("a.placedmodule").each( function () {
                    var local_kp = parseFloat(JSON.parse(jQuery(this).attr('data-module')).kp);
                    max = max >= local_kp ? max : local_kp;
                });
                sum += max;
            });
            //wie ist das mit Modulen, die �ber zwei Semester gehen? Die werden jetzt
            //nur den h�heren angerechnet (es sei denn, ich irre mich).

            jQuery('#kp_' + row).html((Math.round(sum * 100) / 100) + ' KP');
        });
    },

    showEditDialog: function (element) {
        // set data for dialog
        var json = jQuery.parseJSON(jQuery(element).parent().parent().attr('data-module'));
        jQuery('#edit_module select[name=kp]').val(json.kp);
        jQuery('#edit_module select[name=type]').val(json.type);
        jQuery('#edit_module input[name=id]').val(json.id);
        jQuery('#edit_module textarea[name=notiz]').val(json.notiz);

        // show dialog
        jQuery('#edit_module').dialog('open');
    },

    checkMiddys: function () {
        jQuery('td.middy').each(function () {
            if (!jQuery(this).find('a').html()) {
                jQuery(this).prev().attr('colspan', '2');
                jQuery(this).remove();
            }
        });
    },

    deleteModule: function (element) {
        var module = jQuery(element).parent().parent();

        // re-enable disabled original draggable
        var id = JSON.parse(module.attr('data-module')).id;
        module.remove();

        var orig_module = jQuery('.dragzone .' + id);
        //orig_module.draggable({disabled: true, scroll: true});

        //orig_module.draggable({disabled: false, scroll: true});
        orig_module.removeClass('modulmoved').addClass('modul');

        // check, if we can drop some middys
        STUDIP.eStudienplaner.checkMiddys();
        STUDIP.eStudienplaner.saveVerlaufsplan();
    },

    expandSemester: function (plus) {
        jQuery('#dialog').load(STUDIP.eStudienplaner.url + '/expand_sem', function () {
            var pos = 0;
            jQuery('div.messagebox_modal div a').each(function () {
                if (pos === 0) {
                    jQuery(this).bind('click', function () {
                        STUDIP.eStudienplaner.expandSemesterApproved(plus);
                        jQuery('#dialog').html('');
                    });
                } else {
                    jQuery(this).bind('click', function () {
                        jQuery('#dialog').html('');
                    });
                }
                pos += 1;
            });
        });
    },

    expandSemesterApproved: function (plus) {
        var element = jQuery(plus).closest("td");
        var coord = element.attr('id').split("_");
        var row = parseInt(coord[1], 10);
        var col = parseInt(coord[2], 10);
        // expand row, remove td
        if (element.attr('rowspan') == 1) {
            // set rowspan for lefty and righty
            element.attr('rowspan', 2);
            element.next().attr('rowspan', 2);

            if (element.next().hasClass('middy')) {
                element.next().next().attr('rowspan', 2);
            }

            // change plus to minus
            jQuery(plus).attr('src', STUDIP.eStudienplaner.minusSign);

            // remove elements replaced by rowspan
            console.log(jQuery("#field_" + (row - 1) + "_" + col));
            jQuery("#field_" + (row - 1) + "_" + col).hide().html('');
            jQuery('#field_' + (row - 1) + '_' + (col + 1)).hide().html('');
            jQuery("#drop_" + (row - 1) + "_" + (col + 1)).hide().html('');
            
            //old:
            //jQuery('#field_' + row + '_' + col).hide().html('');
            //jQuery('#field_' + row + '_' + (col + 1)).hide().html('');
            //jQuery('#drop_' + row + '_' + (col + 1)).hide();
        }

        // collapse row, add td
        else {
            // set rowspan for lefty and righty
            element.attr('rowspan', 1);
            element.next().attr('rowspan', 1);

            if (element.next().hasClass('middy')) {
                element.next().next().attr('rowspan', 1);
            }

            // change minus to plus
            jQuery(plus).attr('src', STUDIP.eStudienplaner.plusSign);

            // add previously removed elements
            jQuery("#field_" + (row - 1) + "_" + col).show();
            jQuery('#field_' + (row - 1) + '_' + (col + 1)).show();
            jQuery("#drop_" + (row - 1) + "_" + (col + 1)).show();
            
            // recreate droppables
        }

        STUDIP.eStudienplaner.makeDroppables();
    },

    droppedOnMe: function (element, event, ui) {
        if (jQuery(ui.draggable).hasClass('ui-dialog')) {
            return;
            //sieht schon danach aus, als ob hier ein schlechtes Konzept vorlag.
        }
        var data_module = jQuery(ui.draggable).attr('data-module');
        var id = JSON.parse(data_module).id;
        var module = ui.draggable.clone();
        
        if (id != "free") {
            if (jQuery("#studyplan ." + id).length > 0) {
                var json = JSON.parse(jQuery("#studyplan ." + id).attr('data-module'));
                var kp = json.kp;
            }
            var color = jQuery("#studyplan ." + id)
                                            .css('background-color');
        } else {
            //Freitextmodul:
            var json = JSON.parse(data_module);
            json.id = id = "z" + Math.floor(Math.random()* 1000000);
            module.addClass(json.id);
        }
        
        module.removeAttr('style')
                    .attr("id", id)
                    .addClass('placedmodule')
                    .unbind()
                    .css('background-color', STUDIP.eStudienplaner.verlauf_typen[jQuery.parseJSON(module.attr('data-module')).type]);
        if (json) {
            module.attr('data-module', JSON.stringify(json));
        }

        // righty is the small bar to add a second column
        if (jQuery(element).hasClass('righty')) {
            var prevElem = jQuery(element).prev();

            if (prevElem.hasClass('middy')) {
                // we already have a second column
                prevElem.append(module);

            } else {
                // we need to add a second column
                prevElem.addClass('twocolumn').attr('colspan', '1');
                var sem = parseInt(prevElem.attr('id').charAt(6), 10);
                var mod = parseInt(prevElem.attr('id').charAt(8), 10);
                var new_column = jQuery('<td class="droppable middy twocolumn">').attr('id', 'field_' + sem + '_' + (mod + 1)).append(module);
                if (prevElem.attr("rowspan") == 2) {
                        new_column.attr("rowspan", 2);
                }
                prevElem.after(new_column);
            }

        // just add the module
        } else {
            jQuery(element).append(module);
        }
        if (kp) {
            jQuery('#studyplan .' + id + ' span.kp').html('(' + kp + ' KP)');
        }
        if (color) {
            jQuery("#studyplan ." + id).css('background-color', color);
        }

        //im Verlauf selbst st�ren diese spans nur
        jQuery("#studyplan span[id^=plus_], #studyplan span[id^=minus_]").remove();

        STUDIP.eStudienplaner.saveVerlaufsplan();

        STUDIP.eStudienplaner.makeDroppables();
    },

    addSemester: function () {
        STUDIP.eStudienplaner.current_sem += 1;
        var newLine = jQuery('<tr>').attr('id', 'sem' + STUDIP.eStudienplaner.current_sem);
        newLine.append(jQuery('<td>').addClass('sem_label'));
        for (var j = 0; j < STUDIP.eStudienplaner.current_module; j += 1) {
            newLine.append(STUDIP.eStudienplaner.dropCell);
        }

        jQuery('#studyplan').prepend(newLine);
        jQuery('#sem' + STUDIP.eStudienplaner.current_sem + ' > td.sem_label').html(STUDIP.eStudienplaner.current_sem + '. Sem.');

        var i = 0;
        newLine.find('td[class!=sem_label]').each(function () {
            if (i % 2 === 0) {
                jQuery(this).attr('id', 'field_' + STUDIP.eStudienplaner.current_sem + '_' + i);
            } else {
                jQuery(this).attr('id', 'drop_' + STUDIP.eStudienplaner.current_sem + '_' + i);
            }
            i += 1;
        });

        newLine.append(jQuery('<td>').attr('id', 'kp_' + STUDIP.eStudienplaner.current_sem).
                addClass('kp').html('0 KP'));

        STUDIP.eStudienplaner.makeDroppables();
    },

    removeSemester: function () {
        jQuery('#dialog').load(STUDIP.eStudienplaner.url + '/remove_sem', function () {
            var pos = 0;
            jQuery('div.messagebox_modal div a').each(function () {
                if (pos === 0) {
                    jQuery(this).bind('click', function () {
                        STUDIP.eStudienplaner.removeSemesterApproved();
                        jQuery('#dialog').html('');
                    });
                } else {
                    jQuery(this).bind('click', function () {
                        jQuery('#dialog').html('');
                    });
                }
                pos += 1;
            });
        });
    },

    removeSemesterApproved: function () {
        if (STUDIP.eStudienplaner.current_sem > 1) {
            jQuery('#sem' + STUDIP.eStudienplaner.current_sem).remove();
            STUDIP.eStudienplaner.current_sem -= 1;
        }
    },

    addModule: function () {
        jQuery('#studyplan tr').each(function () {
            jQuery(this).find('td[class=kp]').before(STUDIP.eStudienplaner.dropCell);
            var sem = jQuery(this).find('td.lefty').first().attr('id').charAt(6);
            jQuery(this).find('td.lefty').last()
                .attr('id', 'field_' + sem + '_' + (STUDIP.eStudienplaner.current_module * 2));
            jQuery(this).find('td.righty').last()
                .attr('id', 'drop_' + sem + '_' + ((STUDIP.eStudienplaner.current_module * 2) + 1));
        });

        STUDIP.eStudienplaner.makeDroppables();
        STUDIP.eStudienplaner.current_module += 1;
    },

    removeModule: function () {
        jQuery('#dialog').load(STUDIP.eStudienplaner.url + '/remove_mod', function () {
            var pos = 0;
            jQuery('div.messagebox_modal div a').each(function () {
                if (pos === 0) {
                    jQuery(this).bind('click', function () {
                        STUDIP.eStudienplaner.removeModuleApproved();
                        jQuery('#dialog').html('');
                    });
                } else {
                    jQuery(this).bind('click', function () {
                        jQuery('#dialog').html('');
                    });
                }
                pos += 1;
            });
        });
    },

    removeModuleApproved: function () {
        jQuery('#studyplan tr').each(function () {
            var elem = jQuery(this).find('td.lefty').last();

            // if this cell is twocolumn, remove middy as well
            if (jQuery(elem).hasClass('twocolumn')) {
                jQuery(this).find('td.middy').last().remove();
            }

            // remove lefty + righty
            jQuery(elem).remove();
            jQuery(this).find('td.righty').last().remove();
        });

        STUDIP.eStudienplaner.current_module -= 1;
    },

    /**
     * returns a JSON-String created from the submitted data
     *
     * @param id         string
     * @param kp         string
     * @param type     string
     * @param notiz    string
     *
     * @return string
     */
    getDataString: function (id, kp, type, notiz) {
        var data = {
            id: id,
            kp: kp,
            type: type,
            notiz: notiz
        };
        return JSON.stringify(data);
    },

    autoSaveVerlaufsplan: function () {
        STUDIP.eStudienplaner.saveVerlaufsplan();
        window.setTimeout('STUDIP.eStudienplaner.autoSaveVerlaufsplan()', 60000);
    },

    saveVerlaufsplan: function () {
        // fetch data an create json
        var submit_data = [];

        jQuery('#studyplan').find('tr').each(function () {
            var row = jQuery(this).attr('id').charAt(3);
            var col = -2;

            // add the modules to the submit-set
            jQuery(this).find('td.droppable').each(function () {
                if (!jQuery(this).hasClass('righty')) {
                    if (jQuery(this).hasClass('lefty')) {
                        col += 2;
                    }

                    //jQuery(this).append('<span>').html(jQuery(this).prevAll().size());
                    jQuery(this).find('a').each(function () {
                        var tempArr = {};
                        var json = JSON.parse(jQuery(this).attr('data-module'));

                        tempArr['modul_id'] = json.id;
                        tempArr['semester'] = row;
                        if (jQuery(this).parent().hasClass('middy')) {
                            tempArr['position'] = col + 1;
                        } else {
                            tempArr['position'] = col;
                        }
                        tempArr['duration'] = jQuery(this).parent().attr('rowspan');
                        tempArr['kp'] = json.kp;
                        tempArr['type'] = json.type;
                        tempArr['notiz'] = json.notiz;

                        
                        // append data to submit-structure
                        submit_data[submit_data.length] = tempArr;
                    });
                }
            });
        });
        jQuery.ajax({
            type: 'POST',
            url: STUDIP.eStudienplaner.url + '/store_verlaufsplan',
            data: ({
                verlaufsplan_id: STUDIP.eStudienplaner.verlaufsplan_id,
                verlaufsplan: submit_data
            }),
            success: function (data) {
                jQuery('#verlaufsplan_saved').show().delay(5000).fadeOut();
            }
        });
    }
};

jQuery(".dragzone li > a").live('click', function () {
    var ul = jQuery(this).parent().children("ul");
    if (ul.length > 0 && ul.children().length === 0) {
        var id = jQuery(this).attr('id');
        ul.load(
            STUDIP.eStudienplaner.url + '/get_module_children',
            {'modul_id': id},
            STUDIP.eStudienplaner.makeDraggables
        );
    }
});