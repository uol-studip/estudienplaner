<? if (isset($preview)): ?>
<h3><?= _('Vorschau') ?></h3>
<blockquote id="preview" style="border: 1px solid black; background: #aaa; padding: 0.5em;"><?= formatReady($content) ?></blockquote>
<? endif; ?>

<form action="<?= $controller->url_for('zsb_textbausteine/edit/' . $id) ?>" method="post">
    <?= add_safely_security_token() ?>
    <h3><?= $id ? sprintf(_('Textbaustein "%s" bearbeiten'), htmlReady($code)) : _('Textbaustein anlegen') ?></h3>

    <div class="type-text">
        <label for="code">
            <span><?= _('Code') ?></span>
        </label>
        <br>
        <input required type="text" name="code" id="code" value="<?= htmlReady($code) ?>" style="width: 100%">
    </div>

    <div class="type-text">
        <label for="language">
            <span><?= _('Sprache') ?></span>
        </label>
        <br>
        <select name="language" id="language" style="width: 100%;">
            <option value="de" <? if ($language === 'de') echo 'selected'; ?>><?= _('deutsch' ) ?></option>
            <option value="en" <? if ($language === 'en') echo 'selected'; ?>><?= _('englisch') ?></option>
        </select>
    </div>

    <div class="type-text">
        <label for="title">
            <span><?= _('Titel') ?></span>
        </label>
        <br>
        <input required type="text" name="title" id="title" value="<?= htmlReady($title) ?>" style="width: 100%">
    </div>

    <div class="type-text">
        <label for="content">
            <span><?= _('Inhalt') ?></span>
        </label>
        <br>
        <textarea class="add_toolbar" required name="content" id="content" style="width: 100%; height: 150px;"><?= htmlReady($content) ?></textarea>
    </div>

    <div class="type-button">
        <?= makebutton('absenden', 'input', _('Textbaustein speichern'), 'store') ?>
        <?= makebutton('vorschau', 'input', _('Vorschau dieses Textbausteins anzeigen'), 'preview') ?>
        <a href="<?= $controller->url_for('zsb_textbausteine') ?>">
            <?= makebutton('abbrechen') ?>
        </a>
    </div>
</form>