<form action="?" method="post">
<?= add_safely_security_token() ?>
<input type="hidden" id="bereichs_id" name="bereichs_id" value="<?= $bereich->getId() ? $bereich->getId() : "neu" ?>">
<h2 style="text-align: center;"><?= _("Bereich:") ?> <?= htmlReady($bereich['bereich_name']) ?></h2>

<div class="accordion">
    <h2><?= _("Daten") ?></h2>
    <div>
        <ul>
            <li>
                <label for="bereich_name"><?= _("Name") ?></label>
                <input type="text" id="bereich_name" name="bereich_name" value="<?= htmlReady($bereich['bereich_name']) ?>">
            </li>
            <li>
                <label for="sichtbar_fsb"><?= _("Sichtbar für Fachstudienberater") ?></label>
                <input type="checkbox" id="sichtbar_fsb" name="sichtbar_fsb"<?= $bereich['sichtbar_fsb'] ? " checked" : ""?> value="1">
            </li>
            <li>
                <label for="sichtbar_pamt"><?= _("Sichtbar fürs Prüfungsamt") ?></label>
                <input type="checkbox" id="sichtbar_pamt" name="sichtbar_pamt"<?= $bereich['sichtbar_pamt'] ? " checked" : ""?> value="1">
            </li>
            <li>
                <label for="sichtbar_iamt"><?= _("Sichtbar fürs Immatrikulationsamt") ?></label>
                <input type="checkbox" id="sichtbar_iamt" name="sichtbar_iamt"<?= $bereich['sichtbar_iamt'] ? " checked" : ""?> value="1">
            </li>
            <li>
                <label for="sichtbar_stuko"><?= _("Sichtbar für Studienkoordinatoren") ?></label>
                <input type="checkbox" id="sichtbar_stuko" name="sichtbar_stuko"<?= $bereich['sichtbar_stuko'] ? " checked" : ""?> value="1">
            </li>
            <li>
                <label for="sichtbar_stab"><?= _("Sichtbar für Stabstelle") ?></label>
                <input type="checkbox" id="sichtbar_stab" name="sichtbar_stab"<?= $bereich['sichtbar_stab'] ? " checked" : ""?> value="1">
            </li>
            <li>
                <label for="oeffentlich"><?= _("Nach außen sichtbar") ?></label>
                <input type="checkbox" id="oeffentlich" name="oeffentlich"<?= $bereich['oeffentlich'] ? " checked" : ""?> value="1">
            </li>
        </ul>
    </div>
    <? if ($bereich->getId()) : ?>
    <h2><?= _("Dokument-Typen") ?></h2>
    <div>
        <select multiple id="stg_dokument_typ" name="stg_dokument_typ[]" style="width: 80%; height: 150px;">
            <? foreach ($alle_dokument_typen as $typ) : ?>
            <option value="<?= $typ['doku_typ_id'] ?>"<?= in_array($typ['doku_typ_id'], $dokument_typen) ? " selected" : "" ?>><?= htmlReady($typ['name']) ?></option>
            <? endforeach ?>
        </select>
        <script>
        STUDIP.MultiSelect.create("#stg_dokument_typ", "<?= _("Dokument-Typ") ?>"); 
        </script>
        <br>
        <label for="stg_dokument_typ_neu"><?= _("Neuen Dokument-Typen anlegen:") ?></label>
        <input type="text" name="stg_dokument_typ_neu" id="stg_dokument_typ_neu">
        <input type="image" src="<?= Assets::image_path("icons/16/blue/plus.png") ?>" name="create">
        <br>
        <label for="delete_stg_dokument_typ_id"><?= _("Dokument-Typ löschen:") ?></label> 
        <select name="delete_stg_dokument_typ_id" id="delete_stg_dokument_typ_id">
        	<? foreach ($alle_dokument_typen as $typ) : ?>
            <option value="<?= $typ['doku_typ_id'] ?>"<?= StgBereich::isDokumententypDeletable($typ['doku_typ_id']) ? "" : ' disabled title="'._("Dieser Typ kann nicht gelöscht werden, da er noch verwendet wird.").'"' ?>><?= htmlReady($typ['name']) ?></option>
            <? endforeach ?>
        </select>
        <input type="image" src="<?= Assets::image_path("icons/16/blue/trash.png") ?>" name="delete_dokument_typ">
    </div>
    <h2><?= _("Ansprechpartner-Typen") ?></h2>
    <div>
        <ul id="ansprechpartner_typen">
        <? foreach ($ansprechpartner_typen as $typ) : ?>
        	<li id="anprechpartner_typ_<?= $typ['ansprechpartner_typ_id']?>"><?= $typ['name'] ?>
               <? if ($bereich->isAnsprechpartnerDeletable($typ['ansprechpartner_typ_id'])) : ?>
               <a class="icon_trash"></a>
               <? else : ?>
               <?= Assets::img("icons/16/grey/trash.png", array('title' => _("Dieser Typ kann nicht gelöscht werden, da er noch verwendet wird."))) ?>
               <? endif ?>
            </li>
        <? endforeach ?>
        </ul>
        <?= _("Neuen Typ ") ?>
        <input type="text" name="ansprechpartner_typ_neu" id="ansprechpartner_typ_neu">
        <a onClick="STUDIP.zsb.addAnsprechpartnerTyp(jQuery('#ansprechpartner_typ_neu').val()); jQuery('#ansprechpartner_typ_neu').val('');" class="middle"><?= Assets::img("icons/16/yellow/arr_2up.png") ?></a>
    </div>
    <? endif ?>
    <? if (!$bereich->isNew() && $bereich->is_deletable()) : ?>
    <h2><?= _("Bereich löschen") ?></h2>
    <div>
        <?= makebutton('loeschen', 'input', _("Bereich löschen - kann nicht rückgängig gemacht werden."), 'delete') ?>
    </div>
    <? endif ?>
    
</div>

<div style="text-align: center; margin-left: auto; margin-right: auto; margin-top: 12px;">
    <?= makebutton("absenden", "input") ?> <a href="?"><?= makebutton("abbrechen") ?></a>
</div>
</form>