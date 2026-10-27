<?php

namespace A2billing\Forms;

use A2billing\Forms\Elements\Edit\HasMany;

/**
 * @var EditForm $this
 * @var FormHandler $form
 * @var array $processed
 * @var array $list
 * @var array $db_data
 */
?>
<?php if (($help = $form->edit_help_text ?: $form->help_text)): ?>
<div class="row pb-3 align-items-center" id="create_toppage_help">
    <div class='col'>
        <?= $help ?>
    </div>
</div>
<?php endif ?>

<?php if ($form->edit_message_intro): ?>
<div class="row pb-3 align-items-center" id="create_toppage">
    <div class='col'>
        <?= $form->edit_message_intro ?>
    </div>
</div>
<?php endif ?>

<form action="" method="post" name="myForm" id="editForm">
    <input type="hidden" name="id" value="<?= $processed["id"] ?>"/>
    <input type="hidden" name="form_action" value="edit"/>
    <input type="hidden" name="form_el_index" value=""/>
    <input type="hidden" name="current_page" value="<?= $processed["current_page"] ?? "" ?>"/>
    <input type="hidden" name="order" value="<?= $processed["order"] ?? "" ?>"/>
    <input type="hidden" name="sens" value="<?= $processed["sens"] ?? "" ?>"/>
    <?= $form->csrf_inputs() ?>

    <?php foreach ($this->elements as $i => $row): ?>
        <?php if ($row instanceof HasMany): ?>
        <?= $row->render($i, $processed["id"]) ?>
        <?php else: ?>
        <?= $row->render($i, $form->all_fields_valid ? $db_data[$row->name] ?? null : $processed[$row->name] ?? null) ?>
        <?php endif ?>
    <?php endforeach ?>

    <div class="row my-4 justify-content-end">
        <div class="col-auto">
            <a class="btn btn-secondary" href="?form_action=list"><?= _("Cancel") ?></a>
            <button type="submit" class="btn btn-primary"><?= _("Save") ?></button>
        </div>
    </div>
</form>
