<table class="zsb_timetable<?= PersonalRechte::isRoot() ? " editable" : "" ?>">
    <thead>
        <tr>
            <td rowspan="3"><?= _("Fach") ?></td>
            <td colspan="6"><?= _("Wintersemester") ?></td>
            <td colspan="6"><?= _("Sommersemester") ?></td>
        </tr>
        <tr>
            <? foreach (array("WS", "SS") as $semester) : ?>
            <td rowspan="2" colspan="2">&nbsp;</td>
            <td colspan="2"><?= _("Deutsch") ?></td>
            <td colspan="2"><?= _("EU") ?></td>
            <? endforeach ?>
        </tr>
        <tr>
            <? foreach (array("WS", "SS") as $semester) : ?>
            <td title="<?= _("Erstes Semester") ?>"><?= _("Erstes Sem.") ?></td>
            <td title="<?= _("Höheres Semester") ?>"><?= _("Höheres Sem.") ?></td>
            <td title="<?= _("Erstes Semester") ?>"><?= _("Erstes Sem.") ?></td>
            <td title="<?= _("Höheres Semester") ?>"><?= _("Höheres Sem.") ?></td>
            <? endforeach ?>
        </tr>
    </thead>
    <tbody>
    <? foreach($profile as $profil_id => $course_attributes) : ?>
        <? foreach (array('BE' => _("Anfang des Bewerbungseitraums"), 'EN' => _("Ende des Bewerbungseitraums")) as $begin_end => $description) : ?>
        <tr<?= ($begin_end === "BE" ? ' class="firstrow"' : "") ?>>
            
            <? if ($begin_end === "BE") : ?>
            <td rowspan="2" class="leftside">
                <?= htmlReady($course_attributes['name'])." (".htmlReady($course_attributes['abschluss_name']).")" ?>
                <!-- Damit auch übernommen wird, wenn alle Checkboxen leer sind -->
                <input type="hidden" name="profile[<?= $profil_id ?>][exist]" value="1">
            </td>
            <? endif ?>
            <? foreach (array("WS", "SS") as $semester) : ?>
            <? if ($begin_end === "BE") : ?>
            <td rowspan="2">
                <div class="beschraenkungsbox">
                    <div>
                        <div style="clear:both;"
                             class="<?= $course_attributes['zulassungsvoraussetzung_'.($semester == "WS" ? "wise" : "sose")] === "ja" ? " selected" : "" ?>"
                             title="<?= _("zulassungsbeschränkt") ?>"
                             meta_value="ja">
                            Z
                        </div>
                        <div title="<?= _("voraussichtlich zulassungsbeschränkt") ?>"
                             class="<?= $course_attributes['zulassungsvoraussetzung_'.($semester == "WS" ? "wise" : "sose")] === "voraussichtlich ja" ? " selected" : "" ?>"
                             meta_value="voraussichtlich ja">
                            ?
                        </div>
                        <div style="clear:both;"
                             title="<?= _("zulassungsfrei") ?>"
                             class="<?= $course_attributes['zulassungsvoraussetzung_'.($semester == "WS" ? "wise" : "sose")] === "nein" ? " selected" : "" ?>"
                             meta_value="nein">
                            F
                        </div>
                        <div title="<?= _("voraussichtlich zulassungsfrei") ?>"
                             class="<?= $course_attributes['zulassungsvoraussetzung_'.($semester == "WS" ? "wise" : "sose")] === "voraussichtlich nein" ? " selected" : "" ?>"
                             meta_value="voraussichtlich nein">
                            ?
                        </div>
                        <input type="hidden"
                               id="zulassungsvoraussetzung_<?= $semester == "WS" ? "wise" : "sose" ?>_<?= $profil_id ?>"
                               name="profile[<?= $profil_id ?>][zulassungsvoraussetzung_<?= ($semester == "WS" ? "wise" : "sose") ?>]"
                               value="<?= $course_attributes['zulassungsvoraussetzung_'.($semester == "WS" ? "wise" : "sose")] ?>">
                    </div>
                    <div>
                        <div style="clear:both;"
                             title="<?= _("besondere Zugangsvoraussetzungen") ?>"
                             class="<?= $course_attributes['besonderezulassungsvoraussetzung_'.($semester == "WS" ? "wise" : "sose")] === "ja" ? " selected" : "" ?>"
                             meta_value="ja">
                            B
                        </div>
                        <div title="<?= _("voraussichtlich besondere Zugangsvoraussetzungen") ?>"
                             class="<?= $course_attributes['besonderezulassungsvoraussetzung_'.($semester == "WS" ? "wise" : "sose")] == "voraussichtlich ja" ? " selected" : "" ?>"
                             meta_value="voraussichtlich ja">
                            ?
                        </div>
                        <div style="clear:both;"
                             title="<?= _("keine besonderen Zugangsvoraussetzungen") ?>"
                             class="<?= $course_attributes['besonderezulassungsvoraussetzung_'.($semester == "WS" ? "wise" : "sose")] === "nein" ? " selected" : "" ?>"
                             meta_value="nein">
                            N
                        </div>
                        <div title="<?= _("voraussichtlich keine besonderen Zugangsvoraussetzungen") ?>"
                             class="<?= $course_attributes['besonderezulassungsvoraussetzung_'.($semester == "WS" ? "wise" : "sose")] == "voraussichtlich nein" ? " selected" : "" ?>"
                             meta_value="voraussichtlich nein">
                            ?
                        </div>
                        <div style="clear: both;"></div>
                        <input type="hidden"
                               id="besonderezulassungsvoraussetzung_<?= $semester == "WS" ? "wise" : "sose" ?>_<?= $profil_id ?>"
                               name="profile[<?= $profil_id ?>][besonderezulassungsvoraussetzung_<?= ($semester == "WS" ? "wise" : "sose") ?>]"
                               value="<?= $course_attributes['besonderezulassungsvoraussetzung_'.($semester == "WS" ? "wise" : "sose")] ?>">
                    </div>
                </div>
            </td>
            <? endif ?>
            <td title="<?= $description ?>">
                <?= $begin_end === "BE" ? _("Anf") : _("End") ?>
            </td>
            <? foreach (array_keys($zielgruppen) as $zielgruppe) : ?>
            <td class="zielgruppe_<?= $zielgruppe ?> <?= $semester ?> profil_<?= $profil_id ?>">
                <? if (PersonalRechte::isRoot()) : ?>
                <input type="text" name="profile[<?= $profil_id ?>][dates][<?= $zielgruppe ?>][<?= $begin_end ?>_<?= $semester ?>]" value="<?= htmlReady($course_attributes['dates'][$zielgruppe][$begin_end."_".$semester]) ?>" class="date" aria-label="<?= _("Datum eingeben") ?>"><br>
                <input type="hidden" name="profile[<?= $profil_id ?>][begin][<?=$zielgruppe?>][<?=$semester?>]" >
                <input type="button" value="copy" class="copypaste" onClick="STUDIP.zsb.copyString(jQuery('input[type=text]', this.parentNode)[0].value)" aria-label="<?= _("Datum kopieren") ?>">
                <input type="button" value="paste" class="copypaste" onClick="STUDIP.zsb.pasteString(jQuery('input[type=text]', this.parentNode)[0])" aria-label="<?= _("Datum einfügen") ?>">
                <? else : ?>
                <?= htmlReady($course_attributes['dates'][$zielgruppe][$begin_end."_".$semester]) ?>
                <? endif ?>
            </td>
            <? endforeach /* zielgruppen */ ?>
            <? endforeach /* semester */ ?>
        </tr>
        <? endforeach /* anfang/ende */ ?>
        <tr class="white">
            <td colspan="13"></td>
        </tr>
    <? endforeach /* stg_profile */ ?>
    </tbody>
</table>
