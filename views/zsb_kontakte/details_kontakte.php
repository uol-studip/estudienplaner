<? $item_name = "kontakt_id" ?>
<form action="?" method="post">
<?= add_safely_security_token() ?>
<input type="hidden" id="kontakt_id" name="kontakt_id" value="<?= $kontakt->getId() ? $kontakt->getId() : "neu" ?>">
<h2 style="text-align: center;"><?= _("Ansprechpartner:") ?> <?= htmlReady($kontakt->getName()) ?></h2>

<div class="accordion">
    <? if (!$kontakt->isNew()) : ?>
    <h2><?= _("Angezeigte Informationen") ?></h2>
    <div>
        <div style="float: left;">
            <?= $kontakt->getAvatar(Avatar::MEDIUM) ?>
        </div>
        <div style="float: left;">
            <table>
                <tbody>
                    <tr>
                        <td><?= _("Name: ") ?></td>
                        <td><?= htmlReady($kontakt->getName()) ?></td>
                    </tr>
                    <tr>
                        <td><?= _("Email: ") ?></td>
                        <td><?= htmlReady($kontakt->getEmail()) ?></td>
                    </tr>
                    <tr>
                        <td><?= _("Homepage: ") ?></td>
                        <td><?= htmlReady($kontakt->getHomepageURL()) ?></td>
                    </tr>
                    <tr>
                        <td><?= _("Telefonnummer: ") ?></td>
                        <td><?= htmlReady($kontakt->getTelefon()) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div style="clear: both;">
            <p class="info">
            <?= _("Die hier angezeigten Daten können sich von den unten angegebenen unterscheiden, da zuerst die Daten des Instituts/des Benutzeraccounts in Stud.IP ausgewertet werden.") ?>
            </p>
        </div>
    </div>
    <? endif ?>
    <h2><?= _("Daten") ?></h2>
    <div>
        <table style="width: 80%; margin-left: auto; margin-right: auto;">
            <tbody>
                <tr>
                    <td><label for="range_typ"><?= _("Typ:") ?></label></td>
                    <td>
                        <select name="range_typ" id="range_typ" onChange="if (this.value == '') { jQuery('tr.freitext').show(); jQuery('tr#identitaet').hide(); } else { jQuery('tr.freitext').hide(); jQuery('tr#identitaet').show(); };" style="width: 100%;">
                            <option value=""<?= !$kontakt['range_typ'] ? " selected" : "" ?>><?= _("Externer Ansprechpartner") ?></option>
                            <option value="auth_user_md5"<?= $kontakt['range_typ'] === "auth_user_md5" ? " selected" : "" ?>><?= _("Nutzer im System") ?></option>
                            <option value="institute"<?= $kontakt['range_typ'] === "institute" ? " selected" : "" ?>><?= _("Einrichtung") ?></option>
                        </select>
                    </td>
                </tr>
                <tr id="identitaet" style="<?= !$kontakt['range_typ'] ? "display: none;" : "" ?>">
                    <td><?= _("Identität:") ?></td>
                    <td><?= QuickSearch::get("range_id", $kontaktsuche)
                                ->defaultValue($kontakt['range_id'], $kontakt->getName())
                                ->setInputStyle("width: 100%;")
                                ->render() ?></td>
                </tr>
                <tr>
                    <td><label for="ansprechpartner_typ_id"><?= _("Ansprechpartner-Typ") ?></label></td>
                    <td>
                        <?
                        if (PersonalRechte::isRoot() || !$kontakt['ansprechpartner_typ_id']) {
                            $editieren = true;
                        } else {
                            $editieren = false;
                            foreach ($typen as $typ) {
                                if ($typ['ansprechpartner_typ_id'] === $kontakt['ansprechpartner_typ_id']) {
                                    $editieren = true;
                                }
                            }
                        }
                        ?>
                        <? if ($editieren) : ?>
                        <select id="ansprechpartner_typ_id" name="ansprechpartner_typ_id" style="width: 100%;">
                            <? foreach ($typen as $typ) : ?>
                            <option value="<?= $typ['ansprechpartner_typ_id'] ?>"<?= $typ['ansprechpartner_typ_id'] === $kontakt['ansprechpartner_typ_id'] ? " selected" : ""?>><?= htmlReady($typ['name']) ?></option>
                            <? endforeach ?>
                        </select>
                        <? else : ?>
                            <?= htmlReady(StgAnsprechpartner::getAnsprechpartnerTypName($kontakt['ansprechpartner_typ_id'])) ?>
                        <? endif ?>
                    </td>
                </tr>
                <tr class="freitext" style="<?= !$kontakt['range_typ'] ? "" : "display: none;" ?>">
                    <td><label for="freitext_name"><?= _("Freitext Name") ?></label></td>
                    <td><input type="text" id="freitext_name" name="freitext_name" value="<?= htmlReady($kontakt['freitext_name']) ?>" style="width: 100%;"></td>
                </tr>
                <tr class="freitext" style="<?= !$kontakt['range_typ'] ? "" : "display: none;" ?>">
                    <td><label for="freitext_homepage"><?= _("Freitext Homepage") ?></label></td>
                    <td><input type="text" id="freitext_homepage" name="freitext_homepage" value="<?= htmlReady($kontakt['freitext_homepage']) ?>" style="width: 100%;"></td>
                </tr>
                <tr class="freitext" style="<?= !$kontakt['range_typ'] ? "" : "display: none;" ?>">
                    <td><label for="freitext_mail"><?= _("Freitext Email") ?></label></td>
                    <td><input type="text" id="freitext_mail" name="freitext_mail" value="<?= htmlReady($kontakt['freitext_mail']) ?>" style="width: 100%;"></td>
                </tr>
                <tr class="freitext" style="<?= !$kontakt['range_typ'] ? "" : "display: none;" ?>">
                    <td><label for="freitext_telefon"><?= _("Freitext Telefonnummer") ?></label></td>
                    <td><input type="text" id="freitext_telefon" name="freitext_telefon" value="<?= htmlReady($kontakt['freitext_telefon']) ?>" style="width: 100%;"></td>
                </tr>
            </tbody>
        </table>
    </div>
    <? if (!$kontakt->isNew()) : ?>
    <h2><?= _("Studiengänge") ?></h2>
    <div>
        <div>
            <?= QuickSearch::get("neues_profil", $profilsuche)
                        ->withoutButton()
                        ->noSelectBox()
                        ->fireJSFunctionOnSelect("STUDIP.zsb.addToStudiengaenge")
                        ->render() ?>
            <ul id="stg_profile">
            <? $verknupefteStudiengaenge = array(); foreach ($kontakt->getStudiengaenge() as $stg_profil) : ?>
                <? $verknupefteStudiengaenge[] = $stg_profil->getId() ?>
                <li id="profil_id_<?= $stg_profil->getId() ?>"><?= htmlReady(StgProfil::getName($stg_profil->getId())) ?><?= $stg_profil->hasPermission() ? '<a class="icon_trash"></a>' : "" ?></li>
            <? endforeach ?>
            </ul>
        </div>
        <? if (PersonalRechte::isPamt() || PersonalRechte::isIamt()) : ?>
        <a class="icon_folder-empty" onClick="jQuery(this).hide().next().show(); STUDIP.MultiSelect.create('#verknuepfte_studiengaenge', '<?= _("Studiengänge") ?>'); return false;"><?= _("Großes Auswahlfeld anzeigen") ?></a>
        <div style="display: none;">
            <select name="verknuepfte_studiengaenge[]" id="verknuepfte_studiengaenge" multiple style="width: 80%; height: 150px;">
            <? foreach (StgProfil::getMeineProfile() as $profil) : ?>
                <option value="<?= $profil->getId() ?>"<?= in_array($profil->getId(), $verknupefteStudiengaenge) ? " selected" : "" ?>><?= StgProfil::getName($profil->getId()) ?></option>
            <? endforeach ?>
            </select>
        </div>
        <? endif ?>
    </div>

    <? endif ?>
</div>

<div style="text-align: center; margin-left: auto; margin-right: auto; margin-top: 12px;">
    <?= makebutton("absenden", "input") ?> <a href="?"><?= makebutton("abbrechen") ?></a>
</div>
</form>
<?
if (count($ansprechpartner)) {
    $nav_select = '<form action="'.URLHelper::getLink("?").'" method="GET">';
    $nav_select .= '<input type="hidden" name="typ_id" value="'.htmlReady(Request::option("typ_id")).'">';
    $nav_select .= '<select class="text-top" aria-label="'._("Springen Sie zu einem anderen Ansprechpartner.").'" name="kontakt_id" onkeydown="if (event.keyCode === 13) { jQuery(this).closest('."'form'".')[0].submit(); }" onclick="jQuery(this).closest('."'form'".')[0].submit();" size="'.(count($ansprechpartner) < 8 ? count($ansprechpartner) : 8).'" style="max-width: 200px; cursor: pointer;">';
    $lastone = $nextone = null;
    $one = false;
    foreach ($ansprechpartner as $kontakt) {
        $nav_select .= '<option value="'.$kontakt->getId().'"'.($kontakt->getId() === Request::get("kontakt_id") ? " selected" : "").'>'.htmlReady($kontakt->getName()).'</option>';
        if ($kontakt->getId() !== Request::get("kontakt_id") && $lastone !== null && $nextone === null) {
            $nextone = $kontakt;
        }
        if ($kontakt->getId() === Request::get("kontakt_id")) {
            $lastone = $one;
        }
        $one = $kontakt;
    }
    $nav_select .= "</select></form>";
    if ($lastone) {
        $zurueck = '<div style="float: left;"><a class="icon_arr_1left" href="'.URLHelper::getLink("?", array('kontakt_id' => $lastone->getId())).'" title="'._("zurück").'"></a></div>';
    }
    if ($nextone) {
        $vor = '<div style="float: right;"><a class="icon_arr_1right" href="'.URLHelper::getLink("?", array('kontakt_id' => $nextone->getId())).'" title="'._("vorwärts").'"></a></div>';
    }
    $hoch = '<div style="text-align: center;"><a class="icon_arr_1up" href="'.URLHelper::getLink("?", array('typ_id' => $kontakt['ansprechpartner_typ_id'])).'" title="'._("Zu den Ansprechpartnern").'"></a></div>';

}

foreach ($typen as $typ) {
    $typ_suche .= '<option value="'. htmlReady($typ['ansprechpartner_typ_id']) .'" title="'.htmlReady($typ['name']).'"'.(Request::get('typ_id') === $typ['ansprechpartner_typ_id'] ? " selected" : "").'>'. htmlReady($typ['name']) .'</option>';
}
$typ_suche = 
'<form action="?" method="get">
<select name="typ_id" onChange="jQuery(this).closest('."'form'".').submit();" style="max-width: 200px;">
    <option value="">'. _("auswählen") .'</option>
    '.$typ_suche.'
</select>
</form>';

$infobox = array(
    ($nav_select ? array("kategorie" => _("Navigation:"),
          "eintrag"   =>
        array(
            array(
                "icon" => "icons/16/black/link-intern.png",
                "text" => $nav_select
            ),
            array(
                "icon" => "",
                "text" => $zurueck." ".$vor." ".$hoch
            )
        )
    ) : null),
    array("kategorie" => _("Aktionen:"),
          "eintrag"   =>
        array(
            array(
                "icon" => "icons/16/black/search.png",
                "text" => "<label>"._("Filter nach Typen")." ".$typ_suche."</label>"
            )
        )
    )
);
$infobox = array(
    'picture' => $assets_url . "/images/monument.jpg",
    'content' => $infobox
);