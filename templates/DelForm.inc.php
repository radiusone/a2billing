<?php

use A2billing\Table;

/**
 * @var A2billing\Forms\Formhandler $this
 * @var array $processed
 * @var array $list
 * @var string $form_action
 * @var bool $has_child_records
 */
$db_data = $list[0];
?>

<form action="" id="myForm" method="post" name="myForm">
    <input type="hidden" name="id" value="<?= $processed["id"] ?>">
    <input type="hidden" name="atmenu" value="<?= $processed["atmenu"] ?>">
    <input type="hidden" name="current_page" value="<?= $processed['current_page'] ?>">
    <input type="hidden" name="order" value="<?= $processed['order'] ?>">
    <input type="hidden" name="sens" value="<?= $processed['sens'] ?>">
    <?= $this->csrf_inputs() ?>

<?php if ($form_action === "ask-del-confirm"): ?>

    <input type="hidden" name="form_action" value="delete">

    <div class="modal show d-block" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="delconfirmTitle" aria-hidden="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow">
                <div class="modal-header">
                    <h4 class="modal-title" id="delconfirmTitle"><?= _("Warning") ?></h4>
                </div>
                <div class="modal-body">
                    <p>
                        <?= gettext("You have ")?> <?= $processed["fk_count"] ?> <?= gettext(" dependent records.") ?>
                    </p>
                    <p>
                        <?= $this -> FG_FK_DELETE_MESSAGE ?>
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger" href="index.php"><?= _("Delete this Record") ?></button>
                </div>
            </div>
        </div>
    </div>

<?php else: ?>

    <div class="row pb-3">
        <div class="col">
            <strong><?= $this->FG_INTRO_TEXT_ASK_DELETION ?></strong>
        </div>
    </div>

    <?php if ($this->FG_FK_RECORDS_COUNT > 0 && $this->FG_FK_DELETE_ALLOWED && $this->FG_FK_DELETE_CONFIRM): ?>
    <input type="hidden" name="fk_count" value="<?= $this->FG_FK_RECORDS_COUNT ?>">
	<input type="hidden" name="form_action" value="ask-del-confirm">
    <?php else: ?>
    <input type="hidden" name="form_action" value="delete">
    <?php endif ?>

    <?php foreach ($this->FG_EDIT_QUERY_HIDDEN_INPUTS as $name => $value): ?>
        <input type="hidden" name="<?= htmlspecialchars($name) ?>" value="<?= htmlspecialchars($value) ?>"/>
    <?php endforeach ?>

    <?php foreach($this->FG_EDIT_FORM_ELEMENTS as $i=> $row): ?>
        <?php if (!empty($row["custom_query"]) || $row["type"] === "HAS_MANY") {continue;} ?>
    <div class="row pb-3">
        <label for="<?= $row["name"] ?>" class="col-3 col-form-label"><?= $row["label"] ?></label>
        <div class="col">
            <?php if ($this->FG_DEBUG == 1): ?><?= $row["type"] ?><?php endif ?>
            <?php if ($row["type"] === "INPUT" || str_starts_with($row["type"], "POPUP")): ?>
            <input
                id="<?= $row["name"] ?>"
                class="form-control"
                readonly="readonly"
                disabled="disabled"
                name="<?= $row["name"] ?>"
                <?= $row["attributes"] ?>
                value="<?= $db_data[$i] ?>"
            />

            <?php elseif ($row["type"] === "TEXTAREA"): ?>
            <textarea
                id="<?= $row["name"] ?>"
                class="form-control"
                readonly="readonly"
                disabled="disabled"
                name="<?= $row["name"] ?>"
                <?= $row["attributes"]?>
            ><?= $db_data[$i] ?></textarea>

            <?php elseif ($row["type"] === "SELECT"): ?>
                <?php if ($row["select_type"] === "SQL"): ?>
                    <?php $options = (new Table($row["sql_table"], $row["sql_field"]))->get_list($this->DBHandle, $row["sql_clause"])?>
                <?php else: ?>
                    <?php $options = $row["select_fields"] ?>
                <?php endif ?>
                <?php if ($this->FG_DEBUG >= 2): ?>
                    <br/><?php print_r($options)?><br/><?php print_r($list)?><br/>#<?= $i ?>::><?= $this->VALID_SQL_REG_EXP ?><br/><br/>::><?= $db_data[$i] ?><br/><br/>::><?= $row["name"] ?>
                <?php endif ?>
            <select class="form-select" disabled="disabled" name="<?= $row["name"] ?>" id="<?= $row["name"] ?>">
                <?= $row["first_option"] ?>
                <?php if (is_array($options) && count($options)): ?>
                    <?php foreach ($options as $option): ?>
                <option
                    value="<?= $option[1] ?>"
                    <?php if ($db_data[$i] === $option[1]): ?>selected="selected"<?php endif ?>
                >
                    <?= preg_replace_callback("/%([0-9]+)/", fn ($m) => str_replace($m[0], $option[$m[1] - 1] ?? "", $m[0]), $row["select_format"]); ?>
                </option>
                    <?php endforeach ?>
                <?php else: ?>
                    <?= gettext("No data found !!!") ?>
                <?php endif ?>
            </select>

            <?php elseif ($row["type"] === "RADIOBUTTON"): ?>
            <?php foreach ($row["radio_options"] as $rad): ?>
                <?php $check = $this->VALID_SQL_REG_EXP ? $db_data[$i] : $processed[$row["name"]] ?>
            <div class="form-check">
                <input
                        id="<?= $row["name"] ?>_<?= $rad[1] ?>"
                        class="form-check-input <?php if ($row["validation_err"] !== true): ?>is-invalid<?php endif?>"
                        type="radio"
                        name="<?= $row["name"] ?>"
                        value="<?= $rad[1] ?>"
                        <?php if ($check === $rad[1]): ?>checked="checked"<?php endif ?>
                />
                <label for="<?= $row["name"] ?>_<?= $rad[1] ?>" class="form-check-label"><?= $rad[0] ?></label>
            </div>
            <?php endforeach ?>
        <?php endif ?>
        </div>
    </div>
    <?php endforeach ?>

    <div class="row my-4 justify-content-end">
        <div class="col-auto">
            <button type="submit" class="btn btn-danger"><?= _("Delete") ?></button>
        </div>
    </div>

<?php endif ?>
</form>
