<?php

namespace A2billing\Forms;

use A2billing\Forms\Elements\EditElement;

/**
 * @var FormHandler $form
 * @var array $processed
 * @var array $list
 * @var array $db_data
 * @var EditElement[] $elements
 */
?>

<form action="" method="post" name="myForm" id="myForm">
    <input type="hidden" name="form_action" value="add"/>
    <?= $form->csrf_inputs() ?>

<?php foreach ($elements as $i=>$row):?>
    <?= $row->render($i, $processed[$row->name] ?? null) ?>
<?php endforeach ?>

    <div class="row my-4 justify-content-between">
        <div class="col-auto">
            <?= $form->add_message_bottom ?>
        </div>
        <div class="col-auto">
            <a class="btn btn-secondary" href="?form_action=list"><?= $form->cancel_button_text ?></a>
            <button type="submit" class="btn btn-primary"><?= $form->save_button_text ?></button>
        </div>
    </div>
</form>
