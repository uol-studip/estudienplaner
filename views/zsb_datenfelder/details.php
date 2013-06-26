<?= $question ?>
<? $item_name = 'datenfeld_id' ?>
<?= $this->render_partial('zsb/partials/navigation_bar.php', compact('last', 'next', 'item_name')) ?>
<form action="?" method="post">
<?= add_safely_security_token() ?>
<input type="hidden" id="datenfeld_id" name="datenfeld_id" value="<?= $datenfeld->getId() ? $datenfeld->getId() : "neu" ?>">
<h2 style="text-align: center;"><?= _("Datenfeld:") ?> <?= htmlReady($datenfeld->getName()) ?></h2>

<div class="accordion" data-active="<?= Request::int('active_tab', 0) ?>">
    <h2><?= _("Daten") ?></h2>
    <div>
        <ul>
            <li>
                <label for="name"><?= _("Name") ?>:</label><br>
                <input name="name" value="<?= basename($datenfeld->getName(), '.profile') ?>" size="30" maxlength="255">
            </li>
            <li>
                <label for="feldtyp"><?= _("Feldtyp") ?>:</label><br>
                <select name="feldtyp" id="feldtyp">
                    <? foreach (DataFieldEntry::getSupportedTypes() as $datafield_type) : ?>
                    <option value="<?= $datafield_type ?>"<?= $datenfeld->getType() == $datafield_type ? 'selected' : '' ?>><?= $datafield_type ?></option>
                    <? endforeach ?>
                </select>
            </li>
            <li id="typeparam-row"<?= in_array($datenfeld->getType(), words('selectbox radio combo')) ? '' : ' style="display: none;"' ?>>
                <label for="typparam"><?= _("Optionen") ?>:</label><br>
                <textarea name="typparam" id="typparam" class="clean" rows="5" cols="30" style="width: 250px;"><?= $datenfeld->getTypeParam() ?></textarea>
            </li>
            <li>
                <label for="context"><?= _("Kontext") ?>:</label><br>
                <select name="context" id="context">
                    <option value="profile-text"><?= _('Profil: Informationen (4x)') ?></option>
                    <option value="profile" <? if (preg_match('/\.profile$/', $datenfeld->getName())) echo 'selected'; ?>><?= _('Profil: Allgemein') ?></option>
                </select>
            </li>
        </ul>
        <input type="hidden" name="datenfeld_id" value="<?= $datenfeld->getID() ?>">
    </div>
</div>

<div style="text-align: center; margin-left: auto; margin-right: auto; margin-top: 12px;">
    <?= makebutton('loeschen', 'input') ?>
    <?= makebutton('absenden', 'input') ?>
    <a href="?"><?= makebutton("abbrechen") ?></a>
</div>

</form>