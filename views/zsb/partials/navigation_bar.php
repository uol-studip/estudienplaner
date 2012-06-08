<? $item_name || $item_name = "item_id" ?>
<? $url || $url = "?" ?>
<div id="zsb_navigation_bar" style="text-align: center; margin-left: auto; margin-right: auto; width: 50%;">
    <div style="float: left;<?= $last ? "" : " visibility: hidden;" ?>">
        <a href="<?= $last ? URLHelper::getLink($url, array($item_name => $last->getId())) : "" ?>" 
           title="<?= _("Zum vorigen Eintrag in der Liste") ?>"
           class="icon_left icon_arr_1left">
            <?= _("zurück") ?>
        </a>
    </div>
    <div style="float: right;<?= $next ? "" : " visibility: hidden;" ?>">
        <a href="<?= $next ? URLHelper::getLink($url, array($item_name => $next->getId())) : "" ?>" 
           title="<?= _("Zum nächsten Eintrag in der Liste") ?>"
           class="icon_right icon_arr_1right">
            <?= _("weiter") ?>
        </a>
    </div>
    <div>
        <a href="<?= URLHelper::getLink($url, array($item_name => null)) ?>" 
           title="<?= _("Zur Liste") ?>"
           class="icon_left icon_arr_1up">
            <?= _("Liste") ?>
        </a>
    </div>
</div>