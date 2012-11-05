<?php 
$headers || $headers = array("Erste Spalte", "Zweite Spalte");
$preformatted || $preformatted = false;
?>
<table class="zsb_editable">
    <thead>
        <tr class="headline">
        <? foreach ($headers as $header) : ?>
            <th><?= $preformatted ? $header : htmlReady($header) ?></th>
        <? endforeach ?>
            <th style="max-width: 16px;"></th>
            <th style="max-width: 16px;"></th>
        </tr>
    </thead>
    <tbody>
    <? if (!$items) : ?>
        <tr>
            <td colspan="<?= count($headers)+2 ?>" style="text-align: center;"><?= _("Keine Einträge") ?></td>
        </tr>
    <? else : ?>
    <? foreach($items as $key => $item_attributes) : ?>
        <tr id="<?= $type ?>_<?= $item_attributes['item'] ? $item_attributes['item']->getId() : "" ?>" url="<?= $item_attributes['url'] ?>" class="firstrow">
        <? foreach ($item_attributes['content'] as $attributeKey => $attribute) : ?>
            <td><?= ($attribute !== null && $attribute !== '') ? ($preformatted ? $attribute : htmlReady($attribute)) : "-" ?></td>
        <? endforeach ?>
            <td><a class="icon_edit" style="max-width: 16px;" title="<?= _("Zeile editieren") ?>"></a></td>
            <td>
                <? if ($item_attributes['item'] && method_exists($item_attributes['item'], "is_deletable") && $item_attributes['item']->is_deletable()) : ?>
                <a class="icon_trash" style="max-width: 16px;" title="<?= _("Datensatz löschen") ?>"></a>
                <? else : ?>
                <?= Assets::img("icons/16/grey/trash.png", array('title' => _("Kann nicht gelöscht werden, da noch Abhängigkeiten bestehen oder Sie die nötigen Rechte nicht haben."))) ?>
                <? endif ?>
            </td>
        </tr>
    <? endforeach ?>
    <? endif ?>
    </tbody>
    <tfoot>
        <? if ($neu) : ?>
        <tr>
            <td colspan="<?= count($headers)+2 ?>">
                <a href="<?= $neu ?>" title="<?= _("Neu hinzufügen") ?>" class="icon_plus"></a>
            </td>
        </tr>
        <? endif ?>
        <? if ($footer_controls) : ?>
        <tr>
            <td colspan="<?= count($headers)+2 ?>">
                <?= $footer_controls ?>
            </td>
        </tr>
    </tfoot>
    <? endif ?>
</table>
