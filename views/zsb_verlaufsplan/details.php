<?= $this->render_partial("zsb/partials/navigation_bar.php", compact("last", "next", "item_name")) ?>
<form action="?" method="post">
<?= add_safely_security_token() ?>
<input type="hidden" id="item_id" name="item_id" value="<?= $verlaufsplan->getId() ? $verlaufsplan->getId() : "neu" ?>">
<h2 style="text-align: center;"><?= _("Verlaufsplan:") ?> <?= htmlReady($verlaufsplan['titel']) ?></h2>

<div class="accordion">
    <h2><?= _("Daten") ?></h2>
    <div>
        <ul>
            <li>
                <label for="verlaufsplan_titel"><?= _("Titel") ?></label>
                <? $titel = Request::get("stg_profil_id") ? StgProfil::getName(Request::get("stg_profil_id")) : $verlaufsplan['titel'] ?>
                <input type="text" required id="verlaufsplan_titel" name="verlaufsplan_titel" value="<?= htmlReady($titel) ?>">
            </li>
            <? if ($verlaufsplan->isNew()) : ?>
            <li>
                <label for="is_kombo"><?= _("Verlaufsplan einer Fächerkombination") ?></label>
                <input type="checkbox" id="is_kombo" name="is_kombo"<?= $verlaufsplan['fach_kombi_id'] ? " checked" : ""?> value="1">
            </li>
            <li id="profilsuche" style="<?= $verlaufsplan['fach_kombi_id'] ? "display: none; " : "" ?>">
                <label for="stg_profil_id_1"><?= _("Studiengang-Profil") ?></label>
                <? $quicksearch = QuickSearch::get("stg_profil_id", $profilsuche)
                        ->withoutButton()
                        ->defaultValue($verlaufsplan['stg_profil_id'], $verlaufsplan['stg_profil_id'] ? StgProfil::getName($verlaufsplan['stg_profil_id']) : "")
                        ->noSelectBox()
                        ->setAttributes(!$verlaufsplan['fach_kombi_id'] ? array('required' => "required") : array());
                    if (Request::get("stg_profil_id")) {
                        $quicksearch->defaultValue(Request::get("stg_profil_id"), StgProfil::getName(Request::get("stg_profil_id")));
                    }
                    echo $quicksearch->render();
                ?>
            </li>
            <li id="fachkombi_suche" style="<?= !$verlaufsplan['fach_kombi_id'] ? "display: none; " : "" ?>">
                <label for="fach_kombi_id_2"><?= _("Fächerkombination") ?></label>
                <?= QuickSearch::get("fach_kombi_id", $fachkombisuche)
                        ->withoutButton()
                        ->defaultValue($verlaufsplan['fach_kombi_id'], $verlaufsplan['fach_kombi_id'] ? $fachkombinationsname : "")
                        ->noSelectBox()
                        ->setAttributes($verlaufsplan['fach_kombi_id'] ? array('required' => "required") : array())
                        ->render() ?>
            </li>
            <? else : ?>
            <li>
                <?= _("Profile") ?>
                <ul>
                <? foreach ($verlaufsplan->getProfile() as $profil) : ?>
                    <li><a class="icon_link-intern" href="<?= $this->controller->url_for("zsb_studiengang/studiengaenge?item_id=".$profil->getId()) ?>"><?= htmlReady(StgProfil::getName($profil->getId())) ?></a></li>
                <? endforeach ?>
                </ul>
            </li>
            <? endif ?>
            <li>
                <label>
                    <?= _("Sichtbar Fach 1") ?>
                    <input<?= $edit_fach1 || PersonalRechte::isRoot() ? "" : " disabled" ?> type="checkbox" name="sichtbar_fach1" value="1"<?= $verlaufsplan['sichtbar_fach1'] ? " checked" : ""?>>
                </label>
            </li>
            <li id="sichtbar_fach2_li" style="<?= !$verlaufsplan['fach_kombi_id'] ? "display: none; " : "" ?>">
                <label>
                    <?= _("Sichtbar Fach 2") ?>
                    <input<?= $edit_fach2 || PersonalRechte::isRoot() ? "" : " disabled" ?> type="checkbox" name="sichtbar_fach2" value="1"<?= $verlaufsplan['sichtbar_fach2'] ? " checked" : ""?>>
                </label>
            </li>
            <li>
                <label for="untertitel"><?= _("Untertitel") ?></label>
                <input type="text" id="untertitel" name="untertitel" value="<?= htmlReady($verlaufsplan['untertitel']) ?>">
            </li>
            <li>
                <label for="notiz"><?= _("Notiz") ?></label>
                <textarea id="notiz" name="notiz"><?= htmlReady($verlaufsplan['notiz']) ?></textarea>
            </li>
            <li>
                <label for="version"><?= _("Prüfungsversion") ?></label>
                <input id="version" type="text" name="version" value="<?= htmlReady($verlaufsplan['version']) ?>">
            </li>
        </ul>
    </div>
    <? if (!$verlaufsplan->isNew()) : ?>
    <h2><?= _("Verlauf") ?></h2>
    <div>
        <?= $this->render_partial("zsb_verlaufsplan/verlaufsplan_plugin.php", array(
                "plugin_url" => $plugin_url,
                "verlaufsplan" => $verlaufsplan,
                "gettersetter_verlaufsplan" => $gettersetter_verlaufsplan,
                "module_type" => $module_type,
                "modules" => $modules,
                "dimensions" => $dimensions
        )) ?>
    </div>
    <? endif ?>
    
</div>

<div style="text-align: center; margin-left: auto; margin-right: auto; margin-top: 12px;">
    <?= makebutton("absenden", "input") ?> <a href="?"><?= makebutton("abbrechen") ?></a>
</div>
</form>
