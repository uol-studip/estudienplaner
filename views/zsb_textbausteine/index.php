<p>
    <a class="open-in-dialog" href="<?= $controller->url_for('zsb_textbausteine/edit') ?>" style="padding-left: 20px; background: url(<?= Assets::image_path('icons/16/blue/plus') ?>) left center no-repeat;">
        <?= _('Neuen Textbaustein anlegen') ?>
    </a>
</p>

<table class="default zebra-hover">
    <colgroup>
        <col width="100">
        <col width="100">
        <col>
        <col width="50">
    </colgroup>
    <thead>
        <tr>
            <th><?= _('Code') ?></th>
            <th><?= _('Sprache') ?></th>
            <th><?= _('Titel') ?></th>
            <th>&nbsp;</th>
        </tr>
    </thead>
    <tbody>
    <? if (empty($textbausteine)): ?>
        <tr>
            <td colspan="4">
                <?= _('Es wurden noch keine Textbausteine angelegt') ?>
            </td>
        </tr>
    <? endif; ?>
    <? foreach ($textbausteine as $textbaustein): ?>
        <tr class="<?= TextHelper::cycle('steel1', 'steelgraulight') ?>">
            <td>
                <a class="open-in-dialog" href="<?= $controller->url_for('zsb_textbausteine/edit/' . $textbaustein['textbaustein_id']) ?>">
                    <?= htmlReady($textbaustein['code']) ?>
                </a>
            </td>
            <td>
                <a class="open-in-dialog" href="<?= $controller->url_for('zsb_textbausteine/edit/' . $textbaustein['textbaustein_id']) ?>">
                    <?= $textbaustein['language'] === 'de' ? _('deutsch') : _('englisch') ?>
                </a>
            </td>
            <td>
                <a class="open-in-dialog" href="<?= $controller->url_for('zsb_textbausteine/edit/' . $textbaustein['textbaustein_id']) ?>">
                    <?= htmlReady($textbaustein['title']) ?>
                </a>
            </td>
            <td style="text-align: right;">
                <a class="open-in-dialog" href="<?= $controller->url_for('zsb_textbausteine/edit/' . $textbaustein['textbaustein_id']) ?>">
                    <?= Assets::img('icons/16/blue/edit', tooltip2(_('Textbaustein bearbeiten'))) ?>
                </a>
                <a href="<?= $controller->url_for('zsb_textbausteine/delete/' . $textbaustein['textbaustein_id']) ?>" onclick="return confirm('<?= _('Wollen Sie diesen Textbaustein wirklich löschen?') ?>');">
                    <?= Assets::img('icons/16/blue/trash', tooltip2(_('Textbaustein löschen'))) ?>
                </a>
            </td>
        </tr>
    <? endforeach; ?>
    </tbody>
</table>