<?php

use A2billing\Table;

/**
 * @var A2billing\Forms\Formhandler $this
 * @var array $processed
 * @var array $list
 */

$db_data = $list[0];
$options = null
?>

<script src="javascript/calonlydays.js"></script>
<script>
    function sendto(action, record, field_inst, instance) {
        $("form#editForm input[name=form_action]").val(action);
        $("form#editForm input[name=sub_action]").val(record);
        if (field_inst) {
            $(`form#editForm [name=${field_inst}]`).val(instance);
        }
        $("form#editForm").trigger("submit");
    }

    function sendtolittle(direction) {
        $("form#editForm").attr("action", direction).trigger("submit");
    }
</script>

<form action="" method="post" name="myForm" id="editForm">
    <input type="hidden" name="id" value="<?= $processed["id"] ?>"/>
    <input type="hidden" name="form_action" value="edit"/>
    <input type="hidden" name="sub_action" value=""/>
    <input type="hidden" name="atmenu" value="<?= $processed["atmenu"] ?>"/>
    <input type="hidden" name="stitle" value="<?= $processed["stitle"] ?>"/>
    <input type="hidden" name="current_page" value="<?= $processed["current_page"] ?>"/>
    <input type="hidden" name="order" value="<?= $processed["order"] ?>"/>
    <input type="hidden" name="sens" value="<?= $processed["sens"] ?>"/>
    <?= $this->csrf_inputs() ?>

    <?php foreach ($this->FG_EDIT_QUERY_HIDDEN_INPUTS as $name => $value): ?>
        <input type="hidden" name="<?= htmlspecialchars($name) ?>" value="<?= htmlspecialchars($value) ?>"/>
    <?php endforeach ?>

    <?php foreach ($this->FG_EDIT_FORM_HIDDEN_INPUTS as $name => $value): ?>
        <input type="hidden" name="<?= htmlspecialchars($name) ?>" value="<?= htmlspecialchars($value) ?>"/>
    <?php endforeach ?>

    <?php foreach ($this->FG_EDIT_FORM_ELEMENTS as $i=> $row): ?>
        <?php if (!empty($row["section_name"])): ?>
        <div class="row mb-3">
            <h4><?= $row["section_name"] ?></h4>
        </div>
        <?php endif ?>

        <?php if (count($row["custom_query"]) === 0): // SQL CUSTOM QUERY ?>
        <div class="row mb-3">
            <label for="<?= $row["name"] ?>" class="col-3 col-form-label">
                <?= $row["label"] ?>
            </label>
            <div class="col">

            <?php if ($this->FG_DISPLAY_SELECT && !empty($db_data[$this->FG_SELECT_FIELDNAME]) && $this->FG_CONF_VALUE_FIELDNAME === $row["name"]): ?>
                <select id="<?= $row["name"] ?>" name="<?= $row["name"] ?>" class="form-select">
                    <?php $vals = explode(",", $db_data[$this->FG_SELECT_FIELDNAME]) ?>
                    <?php foreach ($vals as $val): ?>
                        <option <?php if ($val == $db_data[$i]): ?>selected="selected"<?php endif ?>><?= $val ?></option>
                    <?php endforeach ?>
                </select>

            <?php elseif ($row["type"] === "INPUT"): ?>
                <?php if (!empty($row["custom_function"])): ?>
                    <?php $db_data[$i] = call_user_func($row["custom_function"], $db_data[$i]) ?>
                <?php endif ?>
                <input
                    id="<?= $row["name"] ?>"
                    class="form-control <?php if ($row["validation_err"] !== true): ?>is-invalid<?php endif?>"
                    name="<?= $row["name"] ?>"
                    <?= $row["attributes"] ?>
                    <?php if ($this->VALID_SQL_REG_EXP): /* what is VALID_SQL_REG_EXP */ ?>
                        value="<?= $db_data[$i] ?>"
                    <?php else: ?>
                        value="<?= $processed[$row["name"]] ?>"
                    <?php endif ?>
                />

            <?php elseif (str_starts_with($row["type"], "POPUP")): ?>
                <div class="input-group">
                    <input
                        id="<?= $row["name"] ?>"
                        class="form-control <?php if ($row["validation_err"] !== true): ?>is-invalid<?php endif?>"
                        name="<?= $row["name"] ?>"
                        <?= $row["attributes"] ?>
                        <?php if ($this->VALID_SQL_REG_EXP): ?>
                            value="<?= $db_data[$i] ?>"
                        <?php else: ?>
                            value="<?= $processed[$row["name"]] ?>"
                        <?php endif ?>
                    />
                    <?php if ($row["type"] === "POPUPVALUE"): ?>
                    <a
                        href="<?= $row["popup_dest"] ?>"
                        data-window-name="<?= $row["name"] ?>Popup"
                        data-popup-options="<?= $row["popup_params"] ?>"
                        class="btn btn-primary popup_trigger"
                        aria-label="open a popup to select an item"
                    >&gt;</a>
                    <?php elseif ($row["type"] === "POPUPDATETIME"): //minutes since monday 00:00, used 2x in FG_var_def_ratecard.inc ?>
                    <a href="#" class="btn btn-primary calendar_trigger">
                        <img width="16" height="16" alt="Click Here to Pick up the date" src="data:image/gif;base64,R0lGODlhEAAQAKIAAKVNSkpNpUpNSqWmpdbT1v///////wAAACH5BAEAAAYALAAAAAAQABAAAANEaLrcNjDKKUa4OExYM95DVRTEWJLmKKLseVZELMdADcSrOwK7OqQsXkEIm8lsN0IOqCssW8Cicar8Qa/P5kvA7Xq/ggQAOw=="/>
                    </a>
                    <?php endif ?>
                </div>
            <?php elseif ($row["type"] === "TEXTAREA"): ?>
                <textarea
                    id="<?= $row["name"] ?>"
                    class="form-control <?php if ($row["validation_err"] !== true): ?>is-invalid<?php endif?>"
                    name="<?= $row["name"] ?>"
                    <?= $row["attributes"] ?>
                ><?= $this->VALID_SQL_REG_EXP ? $db_data[$i] : $processed[$row["name"]] ?></textarea>

            <?php elseif ($row["type"] === "SPAN"): //used once in FG_var_config.inc ?>
                <span id="<?= $row["name"] ?>" name="<?= $row["name"] ?>" <?= $row["attributes"] ?>>
                <?php if ($this->VALID_SQL_REG_EXP): ?>
                    <?= $db_data[$i] ?>
                <?php else: ?>
                    <?= $processed[$row["name"]] ?>
                <?php endif ?>
                </span>

            <?php elseif ($row["type"] === "SELECT"): ?>
                <?php if ($row["select_type"] === "SQL"): ?>
                    <?php $options = (new Table($row["sql_table"], $row["sql_field"]))->get_list($this->DBHandle, $row["sql_clause"])?>
                <?php elseif ($row["select_type"] === "LIST"): ?>
                    <?php $options = $row["select_fields"] ?>
                <?php endif ?>
                <?php if ($this->FG_DEBUG >= 2): ?>
                    <br/><?php print_r($options)?><br/><?php print_r($db_data)?><br/>#<?= $i ?>::><?= $this->VALID_SQL_REG_EXP ?><br/><br/>::><?= $db_data[$i] ?><br/><br/>::><?= $row["name"] ?>
                <?php endif ?>
                <select
                    id="<?= $row["name"] ?>"
                    name="<?= $row["name"] ?><?php if (str_contains($row["attributes"], "multiple")): ?>[]<?php endif ?>"
                    class="form-select <?php if ($row["validation_err"] !== true): ?>is-invalid<?php endif?>"
                    <?= $row["attributes"] ?>
                >
                    <?= $row["first_option"] ?>
                    <?php if (is_array($options) && count($options)): ?>
                        <?php foreach ($options as $option): ?>
                    <option
                        value="<?= $option[1] ?>"
                            <?php if ($this->VALID_SQL_REG_EXP): ?>
                                <?php if (str_contains($row["attributes"], "multiple")): ?>
                                    <?php if (intval($option[1]) & intval($db_data[$i])): ?>
                        selected="selected"
                                    <?php endif ?>
                                <?php elseif ($db_data[$i] == $option[1]): ?>
                        selected="selected"
                                <?php endif ?>
                            <?php else: ?>
                                <?php if (str_contains($row["attributes"], "multiple")): ?>
                                    <?php /* TODO: WTF is this? */ if (is_array($processed[$row["name"]]) && (intval($option[1]) & array_sum($processed[$row["name"]]))): ?>
                        selected="selected"
                                    <?php endif ?>
                                <?php elseif ($processed[$row["name"]] == $option[1]): ?>
                        selected="selected"
                                <?php endif ?>
                            <?php endif ?>
                    >
                        <?= preg_replace_callback("/%([0-9]+)/", fn ($m) => str_replace($m[0], $option[$m[1] - 1] ?? "", $m[0]), $row["select_format"]); ?>
                    </option>
                        <?php endforeach ?>
                    <?php else: ?>
                    <option value=""><?= gettext("No data found!!!") ?></option>
                    <?php endif ?>
                </select>

            <?php elseif ($row["type"] === "RADIOBUTTON"): ?>
                <?php foreach ($row["radio_options"] as $rad): ?>
                <div class="form-check">
                    <?php if ($this->VALID_SQL_REG_EXP): ?>
                        <?php $check = $db_data[$i] ?>
                    <?php else: ?>
                        <?php $check = $processed[$row["name"]] ?>
                    <?php endif ?>
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
            <?php if ($row["validation_err"] !== true): ?>
                <div class="form-text invalid-feedback"><?= $row["error"] ?> - <?= $row["validation_err"] ?></div>
            <?php endif ?>
            <?php if ($this->FG_DEBUG == 1): ?>
                <div class="form-text"><?= $row["type"] ?></div>
            <?php endif ?>
            <?php if (!empty($row["comment"])): ?>
                <div class="form-text"><?= $row["comment"] ?></div>
            <?php endif ?>
            </div>
        </div>

        <?php else: ?>
            <?php $table = $row["custom_query"] ?>

            <?php if ($row["type"] === "SELECT"): ?>
            <div class="row mb-3">
                <div class="col-3">
                    <?= $row["label"] ?>
                </div>
                <div class="col">
                    <?php $options = (new Table($table["tables"], $table["columns"]))->get_list($this->DBHandle, str_replace("%id", $processed["id"], $table["where"]))?>
                    <ul class="list-group">
                    <?php if (is_array($options) && count($options)): ?>
                        <?php foreach ($options as $k=>$option): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?= $option[0] ?>
                                <button
                                    onclick="sendto('del-content','<?= $i ?>','<?= $table["name"] ?>_hidden','<?= $option[1] ?>');"
                                    id="submit<?= $i ?>"
                                    name="submit<?= $i ?>"
                                    value="add-split"
                                    class="btn btn-sm btn-primary"
                                >
                                    <?= gettext("Delete") ?>
                                </button>
                            </li>
                            <?php endforeach ?>
                    <?php else: ?>
                        <li class="list-group-item"><?= gettext("No") ?> <?= $row["label"] ?></li>
                    <?php endif ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <label for="<?= $table["name"] ?>_ADD" class="form-label"><?= gettext("Add a new") ?> <?= $row["label"] ?></label>
                                <input name="<?= $table["name"] ?>_hidden" type="hidden" value=""/>
                                <select id="<?= $table["name"] ?>_ADD" name="<?= $table["name"] ?>[]" <?= $row["attributes"] ?> class="form-select form-control-sm">
                                    <?php $options = (new Table($table["tables"], $table["columns"]))->get_list($this->DBHandle)?>
                                    <?php if (is_array($options) && count($options)): ?>
                                        <?php foreach ($options as $option): ?>
                                            <?php if (!empty($table["format"])): ?>
                                                <?php $val = preg_replace_callback("/%([0-9]+)/", fn ($m) => str_replace($m[0], $option[$m[1] - 1] ?? "", $m[0]), $table["format"]); ?>
                                                <option value="<?= $option[1] ?>"><?= $val ?></option>
                                            <?php else: ?>
                                                <option value="<?= $option[1] ?>"><?= $option[0] ?></option>
                                            <?php endif ?>
                                        <?php endforeach ?>
                                    <?php else: ?>
                                        <option value=""><?= gettext("No data found !!!") ?></option>
                                    <?php endif ?>
                                </select>
                            </div>
                            <button class="btn btn-sm btn-primary" onclick="sendto('add-content', '<?= $i ?>')">
                                <?= gettext("Add") ?> <?= $row["label"] ?>
                            </button>
                        </li>
                    </ul>
                </div>
            </div>

            <?php elseif ($row["type"] === "HAS_MANY"): ?>
                <?php $col = explode(",", $table["columns"]) ?>
            <div class="row mb-3">
                <div class="col-3"><?= $row["label"] ?></div>
                <div class="col">
                    <?php $options = (new Table($table["table"], $table["columns"]))->get_list($this->DBHandle, str_replace("%id", $processed["id"], $table["where"]))?>
                    <ul class="list-group">
                    <?php if (is_array($options) && count($options)): ?>
                        <?php foreach ($options as $k=>$option): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?php if (isset($table["extra_col"]) && array_key_exists($table["extra_col"], $option)): ?>
                                (<?= $option[$table["extra_col"]] ?>)
                                <?php endif ?>
                                <?= $option[0] ?>
                                <button
                                    onclick="sendto('del-content','<?= $i ?>','<?= $col[0] ?>','<?= $option[0] ?>');"
                                    id="submit<?= $i ?>"
                                    name="submit<?= $i ?>"
                                    value="add-split"
                                    class="btn btn-sm btn-primary"
                                >
                                    <?= gettext("Delete") ?>
                                </button>
                            </li>
                        <?php endforeach ?>
                    <?php else: ?>
                        <li class="list-group-item"><?= gettext("No") ?> <?= $row["label"] ?></li>
                    <?php endif ?>
                        <li class="list-group-item d-flex justify-content-between align-items-end">
                            <div class="flex-grow-1 me-3">
                                <label for="<?= $table["name"] ?>_ADD" class="form-label"><?= gettext("Add a new") ?> <?= $row["label"] ?></label>
                                <?php if ($row["multiline"]): ?>
                                    <textarea id="<?= $table["name"] ?>_ADD" name="<?= $col[0] ?>" class="form-control form-control-sm" cols="40" rows="5"></textarea>
                                <?php else: ?>
                                    <input id="<?= $table["name"] ?>_ADD" name="<?= $col[0] ?>" class="form-control form-control-sm" size="20" maxlength="20"/>
                                <?php endif ?>
                            </div>
                            <button class="btn btn-sm btn-primary" onclick="sendto('add-content', '<?= $i ?>')">
                                <?= gettext("Add") ?> <?= $row["label"] ?>
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
            <?php endif /*  end input type selection  */ ?>
        <?php endif /*  end check for colon in custom query  */ ?>
    <?php endforeach ?>
    <div class="row my-4 justify-content-between">
        <div class="col-auto">
            <?= $this->FG_EDIT_PAGE_BOTTOM_TEXT ?>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary"><?= _("Confirm Data") ?></button>
        </div>
    </div>
</form>
