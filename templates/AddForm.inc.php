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

    <div class="row my-4 justify-content-end">
        <div class="col-auto">
            <a class="btn btn-secondary" href="?form_action=list"><?= _("Cancel") ?></a>
            <button type="submit" class="btn btn-primary"><?= _("Save") ?></button>
        </div>
    </div>
</form>
