<h2 style="text-align: center;"><?= _("Fach:") ?> <?= htmlReady($studiengang['name']) ?></h2>

<input type="hidden" name="studiengang_id" id="studiengang_id" value="<?= $studiengang->getId() ?>">

<div class="accordion" data-active="<?= Request::int('active_tab', 0) ?>">
    <h2><?= _("Daten") ?></h2>
    <div>
        <ul>
            <li><?= _("Name") ?>: <?= htmlReady($studiengang['name']) ?></li>
            <li><?= _("Beschreibung") ?>: <?= htmlReady($studiengang['beschreibung']) ?></li>
        </ul>
    </div>
    <? foreach (DBHelper::getEnumOptions('stg_fsb_rollen', 'rollen_typ') as $rollen_typ) : ?>
    <h2><?= _("Zuständigkeiten: ").htmlReady($rollen_typ) ?></h2>
    <div>
        <?= QuickSearch::get("new_".$rollen_typ, new PermissionSearch(
                                "user",
                                _("Dozenten hinzufügen"),
                                "user_id",
                                array(
                                    'permission' => 'dozent',
                                    'exclude_user' => array()
                                )
                            )
                        )
                    ->fireJSFunctionOnSelect("STUDIP.zsb['addPersonalTo".$rollen_typ."']")
                    ->render() ?>
        <ul id="role_<?= htmlReady($rollen_typ) ?>">
            <? foreach ($rollen[$rollen_typ] as $user_id) : ?>
            <li id="role_<?= $rollen_typ ?>_<?= $user_id ?>">
                <?= Avatar::getAvatar($user_id)->getImageTag(Avatar::SMALL) ?>
                <?= get_fullname($user_id, "full_rev_username") ?>
                <a class="icon_trash"></a>
            </li>
            <? endforeach ?>
        </ul>
    </div>
    <? endforeach ?>
</div>

<?
foreach ($studiengaenge as $studiengang_id) {
    $studiengang = new Studiengang($studiengang_id);
    $studiengang_suche .= '<option value="'. htmlReady($studiengang_id) .'" title="'.htmlReady($studiengang['name']).'"'.($studiengang_id === Request::get("studiengang_id") ? " selected" : "").'>'. htmlReady($studiengang['name']) .'</option>';
}
$studiengang_suche =
'<form action="'.URLHelper::getLink("?").' method="get">
<select name="studiengang_id" onChange="jQuery(this).closest('."'form'".').submit();" style="max-width: 200px;">
    <option value="">'. _("auswählen") .'</option>
    '.$studiengang_suche.'
</select>
</form>';

$infobox = array(
    array("kategorie" => _("Aktionen:"),
          "eintrag"   =>
        array(
            array(
                "icon" => "icons/16/black/search.png",
                "text" => "<label>"._("Fach auswählen")." ".$studiengang_suche."</label>"
            )
        )
    )
);

$infobox = array(
    'picture' => $assets_url . "/images/monument.jpg",
    'content' => $infobox
);