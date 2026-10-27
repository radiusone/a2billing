<?php

namespace A2billing\Forms;

use A2billing\Forms\Elements\EditElement;

/**
 * @var FormHandler $form
 * @var array $processed
 * @var array $list
 * @var string $form_action
 * @var array $db_data
 * @var EditElement[] $elements
 */
?>
<?php if (($help = $form->del_help_text ?: $form->help_text)): ?>
<div class="row pb-3 align-items-center" id="create_toppage_help">
    <div class='col'>
        <?= $help ?>
    </div>
</div>
<?php endif ?>

<?php if ($form->delete_message_intro): ?>
<div class="row pb-3 align-items-center" id="create_toppage">
    <div class='col'>
        <?= $form->delete_message_intro ?>
    </div>
</div>
<?php endif ?>

<form action="" id="myForm" method="post" name="myForm">
    <input type="hidden" name="id" value="<?= $processed["id"] ?>">
    <input type="hidden" name="current_page" value="<?= $processed['current_page'] ?? "" ?>">
    <input type="hidden" name="order" value="<?= $processed['order'] ?? "" ?>">
    <input type="hidden" name="sens" value="<?= $processed['sens'] ?? "" ?>">
    <?= $form->csrf_inputs() ?>

<?php if ($form_action === "ask-del-confirm"): ?>

    <input type="hidden" name="form_action" value="delete">

    <div class="row pb-3 justify-content-center">
        <div class="col-6 align-content-center">
            <h4><?= _("Warning") ?></h4>
        </div>
    </div>
    <div class="row pb-3 justify-content-center">
        <div class="col-6">
            <p>
                <?= sprintf(ngettext("You have %d dependent record.", "You have %d dependent records.", $processed["fk_count"]), $processed["fk_count"]) ?>
            </p>
            <?php if($form->fk_confirm_delete === $form::CONFIRM_DELETE): ?><p>
                <?= _("All dependent records will be permanently deleted") ?>
            </p><?php endif ?>
            <p>
                <?= $form->delete_message_confirm_fk ?>
            </p>
        </div>
    </div>
    <div class="row my-4 justify-content-end">
        <div class="col-6">
            <button type="submit" class="btn btn-secondary" name="form_action" value="ask-delete"><?= _("Cancel") ?></button>
            <button type="submit" class="btn btn-danger"><?= _("Delete this Record") ?></button>
        </div>
    </div>

<?php else: ?>

    <?php if ($form->fk_confirm_delete === $form::CONFIRM_NONE || $form->fk_record_count === 0): ?>
    <input type="hidden" name="form_action" value="delete"/>
    <?php else: ?>
    <input type="hidden" name="fk_count" value="<?= $form->fk_record_count ?>"/>
    <input type="hidden" name="form_action" value="ask-del-confirm"/>
    <?php endif ?>
    <?php foreach ($elements as $i=>$row):?>
        <?= $row->render($i, $db_data[$i]) ?>
    <?php endforeach ?>

    <div class="row my-4 justify-content-end">
        <div class="col-auto">
            <a class="btn btn-secondary" href="?form_action=list"><?= _("Cancel") ?></a>
            <button type="submit" class="btn btn-danger"><?= _("Delete") ?></button>
        </div>
    </div>

<?php endif ?>
</form>
