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

    <div class="row my-4 justify-content-between">
        <div class="col-auto">
            <?= $form->edit_message_bottom ?>
        </div>
        <div class="col-auto">
            <a class="btn btn-secondary" href="?form_action=list"><?= $form->cancel_button_text ?></a>
            <button type="submit" class="btn btn-primary"><?= $form->save_button_text ?></button>
        </div>
    </div>
</form>
