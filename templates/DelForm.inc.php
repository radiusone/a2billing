<?php

namespace A2billing\Forms;

/**
 * @var FormHandler $form
 * @var array $processed
 * @var array $list
 * @var string $form_action
 * @var array $db_data
 */
?>

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
            <p>
                <?= $form -> FG_FK_DELETE_MESSAGE ?>
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

    <div class="row pb-3">
        <div class="col">
            <strong><?= $form->FG_INTRO_TEXT_ASK_DELETION ?></strong>
        </div>
    </div>

    <?php if ($form->FG_FK_RECORDS_COUNT > 0 && $form->FG_FK_DELETE_ALLOWED && $form->FG_FK_DELETE_CONFIRM): ?>
    <input type="hidden" name="fk_count" value="<?= $form->FG_FK_RECORDS_COUNT ?>">
	<input type="hidden" name="form_action" value="ask-del-confirm">
    <?php else: ?>
    <input type="hidden" name="form_action" value="delete">
    <?php endif ?>

    <?php foreach ($form->FG_EDIT_QUERY_HIDDEN_INPUTS as $name => $value): ?>
    <input type="hidden" name="<?= htmlspecialchars($name) ?>" value="<?= htmlspecialchars($value) ?>"/>
    <?php endforeach ?>

    <?php foreach($form->FG_EDIT_FORM_ELEMENTS as $i => $row): ?>
        <?php if ($row["type"] === "HAS_MANY") {continue;} ?>
    <div class="row pb-3">
        <label for="<?= $row["name"] ?>" class="col-3 col-form-label"><?= $row["label"] ?></label>
        <div class="col">
            <?php switch($row["type"]): case "INPUT": case "POPUPVALUE": ?>
            <input
                id="<?= $row["name"] ?>"
                class="form-control"
                readonly="readonly"
                disabled="disabled"
                <?= array_html_attr($row["attributes"]) ?>
                value="<?= $db_data[$i] ?>"
            />
                <?php break ?>

            <?php case "TEXTAREA": ?>
            <textarea
                id="<?= $row["name"] ?>"
                class="form-control"
                readonly="readonly"
                disabled="disabled"
                <?= array_html_attr($row["attributes"]) ?>
            ><?= $db_data[$i] ?></textarea>
                <?php break ?>

            <?php case "SELECT": ?>
            <select
                id="<?= $row["name"] ?>"
                disabled="disabled"
                class="form-select <?php if ($row["validation_err"] !== true): ?>is-invalid<?php endif?>"
                <?= array_html_attr($row["attributes"]) ?>
            >
                <?php foreach ($row["first_option"] as $val => $opt): ?>
                <option value="<?= $val ?>"><?= $opt ?></option>
                <?php endforeach ?>

                <?php if (empty($row["select_fields"])): ?>
                <option value=""><?= gettext("No data found!!!") ?></option>
                <?php endif ?>

                <?php foreach ($row["select_fields"] as $val => $opt): ?>
                <option
                    value="<?= $val ?>"
                    <?php if ($form->all_fields_valid): ?>
                        <?php if (array_key_exists("multiple", $row["attributes"])): ?>
                            <?php if (intval($val) & intval($db_data[$i])): ?>
                    selected="selected"
                            <?php endif ?>
                        <?php elseif ($db_data[$i] == $val): ?>
                    selected="selected"
                        <?php endif ?>
                    <?php else: ?>
                        <?php if (array_key_exists("multiple", $row["attributes"])): ?>
                            <?php if (is_array($processed[$row["name"]]) && (intval($val) & array_sum($processed[$row["name"]]))): ?>
                    selected="selected"
                            <?php endif ?>
                        <?php elseif ($processed[$row["name"]] == $val): ?>
                    selected="selected"
                        <?php endif ?>
                    <?php endif ?>
                >
                    <?= $opt ?>
                </option>
                <?php endforeach ?>
            </select>
                <?php break ?>

            <?php case "RADIOBUTTON": ?>
                <?php foreach ($row["radio_options"] as $val => $rad): ?>
                    <?php $check = $form->all_fields_valid && array_key_exists($i, $db_data) ? $db_data[$i] : ($processed[$row["name"]] ?? "") ?>
            <div class="form-check">
                <input
                    id="<?= $row["name"] ?>_<?= $val ?>"
                    class="form-check-input"
                    type="radio"
                    value="<?= $val ?>"
                    disabled="disabled"
                    <?php if ("$check" === "$val"): ?>checked="checked"<?php endif ?>
                />
                <label for="<?= $row["name"] ?>_<?= $val ?>" class="form-check-label"><?= $rad ?></label>
            </div>
                <?php endforeach ?>
                <?php break ?>

        <?php endswitch ?>
        </div>
    </div>
    <?php endforeach ?>

    <div class="row my-4 justify-content-end">
        <div class="col-auto">
            <a class="btn btn-secondary" href="?form_action=list"><?= _("Cancel") ?></a>
            <button type="submit" class="btn btn-danger"><?= _("Delete") ?></button>
        </div>
    </div>

<?php endif ?>
</form>
