<form action="<?= $controller->url_for('zsb_textbausteine/edit', $id) ?>" method="post">
    <?= add_safely_security_token() ?>
    <h3><?= $id ? sprintf(_('Textbaustein "%s" bearbeiten'), htmlReady($code)) : _('Textbaustein anlegen') ?></h3>
    
    <div class="type-text">
        <label>
            <span><?= _('Code') ?></span><br>
            <input required type="text" name="code" value="<?= htmlReady($code) ?>" style="width: 100%">
        </label>
    </div>

    <div class="type-text">
        <label>
            <span><?= _('Titel') ?></span><br>
            <input required type="text" name="title" value="<?= htmlReady($title) ?>" style="width: 100%">
        </label>
    </div>

    <div class="type-text">
        <label>
            <span><?= _('Inhalt') ?></span><br>
            <textarea class="add_toolbar" required name="content" style="width: 100%"><?= htmlReady($content) ?></textarea>
        </label>
    </div>
    
    <div class="type-button">
        <?= makebutton('absenden', 'input', _('Textbaustein speichern'), 'store') ?>
        <a href="<?= $controller->url_for('zsb_textbausteine') ?>">
            <?= makebutton('abbrechen') ?>
        </a>
    </div>
</form>