<?php

namespace A2billing\Forms;

use A2billing\Table;

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
                <?= gettext("You have ")?> <?= $processed["fk_count"] ?> <?= gettext(" dependent records.") ?>
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

    <?php foreach($form->FG_EDIT_FORM_ELEMENTS as $i=> $row): ?>
        <?php if (!empty($row["custom_query"]) || $row["type"] === "HAS_MANY") {continue;} ?>
    <div class="row pb-3">
        <label for="<?= $row["name"] ?>" class="col-3 col-form-label"><?= $row["label"] ?></label>
        <div class="col">
            <?php if ($form->FG_DEBUG == 1): ?><?= $row["type"] ?><?php endif ?>
            <?php if ($row["type"] === "INPUT" || $row["type"] === "POPUPVALUE"): ?>
            <input
                id="<?= $row["name"] ?>"
                class="form-control"
                readonly="readonly"
                disabled="disabled"
                <?= $row["attributes"] ?>
                value="<?= $db_data[$i] ?>"
            />

            <?php elseif ($row["type"] === "TEXTAREA"): ?>
            <textarea
                id="<?= $row["name"] ?>"
                class="form-control"
                readonly="readonly"
                disabled="disabled"
                <?= $row["attributes"]?>
            ><?= $db_data[$i] ?></textarea>

            <?php elseif ($row["type"] === "SELECT"): ?>
                <?php if ($row["select_type"] === "SQL"): ?>
                    <?php $options = (new Table($row["sql_table"], $row["sql_field"]))->get_list($form->DBHandle, $row["sql_clause"])?>
                <?php else: ?>
                    <?php $options = $row["select_fields"] ?>
                <?php endif ?>
                <?php if ($form->FG_DEBUG >= 2): ?>
                    <br/><?php print_r($options)?><br/><?php print_r($list)?><br/>#<?= $i ?>::><?= $form->VALID_SQL_REG_EXP ?><br/><br/>::><?= $db_data[$i] ?><br/><br/>::><?= $row["name"] ?>
                <?php endif ?>
            <select class="form-select" disabled="disabled" id="<?= $row["name"] ?>" <?= $row["html_attributes"] ?>>
                <?php if (!empty($row["first_option"]) && is_array($row["first_option"]) && count($row["first_option"]) === 2): ?>
                <option value="<?= $row["first_option"][0] ?>"><?= $row["first_option"][1] ?></option>
                <?php elseif (!empty($row["first_option"]) && is_array($row["first_option"])): ?>
                <option value="<?= array_keys($row["first_option"])[0] ?>"><?= array_values($row["first_option"])[0] ?></option>
                <?php elseif (!empty($row["first_option"])): ?>
                <option value=""><?= $row["first_option"] ?></option>
                <?php endif ?>
                <?php if (is_array($options) && count($options)): ?>
                    <?php foreach ($options as $key => $option): ?>
                        <?php $opt = is_array($option) ? $option[0] : $option; $val = is_array($option) ? $option[1] : $key ?>
                <option
                    value="<?= $val ?>"
                    <?php if ($db_data[$i] == $val): ?>selected="selected"<?php endif ?>
                >
                    <?php if (!empty($row["select_format"]) && is_array($option)): ?>
                        <?= preg_replace_callback("/%([0-9]+)/", fn ($m) => str_replace($m[0], $option[$m[1] - 1] ?? "", $m[0]), $row["select_format"]); ?>
                    <?php else: ?>
                        <?= $opt ?>
                    <?php endif ?>
                </option>
                    <?php endforeach ?>
                <?php else: ?>
                    <?= gettext("No data found !!!") ?>
                <?php endif ?>
            </select>

            <?php elseif ($row["type"] === "RADIOBUTTON"): ?>
            <?php foreach ($row["radio_options"] as $key => $rad): ?>
                <?php $val = is_array($rad) ? $rad[1] : $key ?>
                <?php $check = $form->VALID_SQL_REG_EXP && array_key_exists($i, $db_data) ? $db_data[$i] : ($processed[$row["name"]] ?? "") ?>
            <div class="form-check">
                <input
                        id="<?= $row["name"] ?>_<?= $val ?>"
                        class="form-check-input"
                        type="radio"
                        value="<?= $val ?>"
                        disabled="disabled"
                        <?php if ("$check" === "$val"): ?>checked="checked"<?php endif ?>
                />
                <label for="<?= $row["name"] ?>_<?= $val ?>" class="form-check-label"><?= is_array($rad) ? $rad[0] : $rad ?></label>
            </div>
            <?php endforeach ?>
        <?php endif ?>
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
