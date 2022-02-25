<?php

use A2billing\Table;

/**
 * @var A2billing\Forms\Formhandler $this
 * @var array $processed
 * @var array $list
 */
?>

<script src="javascript/calonlydays.js"></script>
<script>
    function sendto(action, record, field_inst, instance) {
        $("form#editForm input[name=form_action]").value(action);
        $("form#editForm input[name=sub_action]").value(record);
        if (field_inst) {
            $(`form#editForm [name=${field_inst}]`).value(instance);
        }
        $("form#editForm").submit();
    }

    function sendtolittle(direction) {
        $("form#editForm").attr("action", direction).submit();
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

    <?php if (!empty($this->FG_QUERY_EDITION_HIDDEN_FIELDS)): ?>
        <?php $fields = explode(",",trim($this->FG_QUERY_EDITION_HIDDEN_FIELDS))?>
        <?php $values = explode(",",trim($this->FG_QUERY_EDITION_HIDDEN_VALUE))?>
        <?php foreach ($fields as $k=>$v): ?>
        <input type="hidden" name="<?= trim($v) ?>" value="<?= trim($values[$k]) ?>"/>
        <?php endforeach ?>
    <?php endif ?>

    <?php if (!empty($this->FG_EDITION_HIDDEN_PARAM)): ?>
        <?php $fields = explode(",",trim($this->FG_EDITION_HIDDEN_PARAM))?>
        <?php $values = explode(",",trim($this->FG_EDITION_HIDDEN_PARAM_VALUE))?>
        <?php foreach ($fields as $k=>$v): ?>
        <input type="hidden" name="<?= trim($v) ?>" value="<?= trim($values[$k]) ?>"/>
        <?php endforeach ?>
    <?php endif ?>

    <?php foreach ($this->FG_TABLE_EDITION as $i=>$row): ?>
        <?php $options = null ?>
        <?php if (strlen($row["section_name"]) > 1): ?>
        <div class="row mb-3">
            <h4><?= $row["section_name"] ?></h4>
        </div>
        <?php endif ?>

        <?php if (!str_contains($row["custom_query"], ":")): // SQL CUSTOM QUERY ?>
        <div class="row mb-3">
            <label for="<?= $row["name"] ?>" class="col-3 col-form-label">
                <?= $row["label"] ?>
            </label>
            <div class="col">

            <?php if ($this->FG_DISPLAY_SELECT && !empty($list[0][$this->FG_SELECT_FIELDNAME]) && $this->FG_CONF_VALUE_FIELDNAME === $row["name"]): ?>
                <select id="<?= $row["name"] ?>" name="<?= $row["name"] ?>" class="form-select">
                    <?php $vals = explode(",", $list[0][$this->FG_SELECT_FIELDNAME]) ?>
                    <?php foreach ($vals as $val): ?>
                        <option <?php if ($val == $list[0][$i]): ?>selected="selected"<?php endif ?>><?= $val ?></option>
                    <?php endforeach ?>
                </select>

            <?php elseif ($row["type"] === "INPUT"): ?>
                <?php if (!empty($row["custom_function"])): ?>
                    <?php $list[0][$i] = call_user_func($row["custom_function"], $list[0][$i]) ?>
                <?php endif ?>
                <input
                    id="<?= $row["name"] ?>"
                    class="form-control <?php if (isset($this->FG_fit_expression[$i]) && !$this->FG_fit_expression[$i]): ?>is-invalid<?php endif?>"
                    name="<?= $row["name"] ?>"
                    <?= $row["attributes"] ?>
                    <?php if ($this->VALID_SQL_REG_EXP): ?>
                        value="<?= $list[0][$i] ?>"
                    <?php else: ?>
                        value="<?= $processed[$row["name"]] ?>"
                    <?php endif ?>
                />

            <?php elseif ($row["type"] === "LABEL"): ?>
                        <?php if (!empty($row["custom_function"])): ?>
                            <?php $list[0][$i] = call_user_func($row["custom_function"], $list[0][$i]) ?>
                        <?php endif ?>
                        <?php if ($this->VALID_SQL_REG_EXP): ?>
                            <?= $list[0][$i] ?>
                        <?php else: ?>
                            <?= $processed[$row["name"]] ?>
                        <?php endif ?>

            <?php elseif (str_starts_with($row["type"], "POPUP")): ?>
                <input
                    id="<?= $row["name"] ?>"
                    class="form-control <?php if (isset($this->FG_fit_expression[$i]) && !$this->FG_fit_expression[$i]): ?>is-invalid<?php endif?>"
                    name="<?= $row["name"] ?>"
                    <?= $row["attributes"] ?>
                    <?php if ($this->VALID_SQL_REG_EXP): ?>
                        value="<?= $list[0][$i] ?>"
                    <?php else: ?>
                        value="<?= $processed[$row["name"]] ?>"
                    <?php endif ?>
                />
                <?php if ($row["type"] === "POPUPVALUE"): ?>
                    <a href="#" title="<?= gettext("SELECT")?>" onclick="window.open('<?= $row["popup_dest"] ?>popup_formname=myForm&popup_fieldname=<?= $row["name"] ?>', <?= $row["popup_params"] ?>)">
                        <img alt="" src="data:image/gif;base64,R0lGODlhDwAPAMQYAP+yPf+fEv+qLP+3Tf+pKv++Xf/Gcv+mJP+tNf+tMf+kH/+/YP+oJv+wO/+jHP/Ohf/WmP+vOv/cpv+kHf+iGf+jG/////Hw7P///wAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAABgALAAAAAAPAA8AAAVjIHaNZEmKF6auLJpiEvQYxQAgiTpiMm0Tk4pigsLMag2Co8KkFA0Lm8XCbBajDcFkWnXuBlkFk1vxpgACcYVcLqbHVKaDuFNXqwxGkUK5VyYMEQhFGAGGhxQHOS4tjTsmkDshADs="/>
                    </a>

                <?php elseif ($row["type"] === "POPUPDATETIME"): //minutes since monday 00:00, used 2x in FG_var_def_ratecard.inc ?>
                    <a href="#" title="<?= gettext("SELECT")?>" onclick="cal<?= $row["name"] ?>.popup()">
                        <img width="16" height="16" border="0" title="Click Here to Pick up the date" alt="Click Here to Pick up the date" src="data:image/gif;base64,R0lGODlhEAAQAKIAAKVNSkpNpUpNSqWmpdbT1v///////wAAACH5BAEAAAYALAAAAAAQABAAAANEaLrcNjDKKUa4OExYM95DVRTEWJLmKKLseVZELMdADcSrOwK7OqQsXkEIm8lsN0IOqCssW8Cicar8Qa/P5kvA7Xq/ggQAOw=="/>
                    </a>
                    <script>
                        var cal<?= $row["name"] ?> = new calendaronlyminutes(document.forms['myForm'].elements['<?= $row["name"] ?>']);
                        cal<?= $row["name"] ?>.year_scroll = false;
                        cal<?= $row["name"] ?>.time_comp = true;
                        cal<?= $row["name"] ?>.formatpgsql = true;
                    </script>
                <?php endif ?>

            <?php elseif ($row["type"] === "TEXTAREA"): ?>
                <textarea
                    id="<?= $row["name"] ?>"
                    class="form-control <?php if (isset($this->FG_fit_expression[$i]) && !$this->FG_fit_expression[$i]): ?>is-invalid<?php endif?>"
                    name="<?= $row["name"] ?>"
                    <?= $row["attributes"] ?>
                    <?php if (str_icontains($row["attributes"], "readonly")): ?>style="background-color: #ccc"<?php endif ?>
                >
                    <?php if ($this->VALID_SQL_REG_EXP): ?>
                        <?= $list[0][$i] ?>
                    <?php else: ?>
                        <?= $processed[$row["name"]] ?>
                    <?php endif ?>
                </textarea>

            <?php elseif ($row["type"] === "SPAN"): //used once in FG_var_config.inc ?>
                <span id="<?= $row["name"] ?>" name="<?= $row["name"] ?>" <?= $row["attributes"] ?>>
                <?php if ($this->VALID_SQL_REG_EXP): ?>
                    <?= $list[0][$i] ?>
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
                    <br/><?php print_r($options)?><br/><?php print_r($list)?><br/>#<?= $i ?>::><?= $this->VALID_SQL_REG_EXP ?><br/><br/>::><?= $list[0][$i] ?><br/><br/>::><?= $row["name"] ?>
                <?php endif ?>
                <select
                    id="<?= $row["name"] ?>"
                    name="<?= $row["name"] ?><?php if (str_icontains($row["attributes"], "multiple")): ?>[]<?php endif ?>"
                    class="form-select <?php if (isset($this->FG_fit_expression[$i]) && !$this->FG_fit_expression[$i]): ?>is-invalid<?php endif?>"
                    <?= $row["attributes"] ?>
                >
                    <?= $row["first_option"] ?>
                    <?php if (is_array($options) && count($options)): ?>
                        <?php foreach ($options as $option): ?>
                            <option
                                    value="<?= $option[1] ?>"
                                <?php if ($this->VALID_SQL_REG_EXP): ?>
                                    <?php if (str_icontains($row["attributes"], "multiple")): ?>
                                        <?php if (intval($option[1]) & intval($list[0][$i])): ?>
                                            selected="selected"
                                        <?php endif ?>
                                    <?php else: ?>
                                        <?php if ($list[0][$i] == $option[1]): ?>
                                            selected="selected"
                                        <?php endif ?>
                                    <?php endif ?>
                                <?php else: ?>
                                    <?php if (str_icontains($row["attributes"], "multiple")): ?>
                                        <?php if (is_array($processed[$row["name"]]) && (intval($option[1]) & array_sum($processed[$row["name"]]))): ?>
                                            selected="selected"
                                        <?php endif ?>
                                    <?php else: ?>
                                        <?php if ($processed[$row["name"]] == $option[1]): ?>
                                            selected="selected"
                                        <?php endif ?>
                                    <?php endif ?>
                                <?php endif ?>
                            >
                                <?php if ($row["select_format"] === ""): ?>
                                    <?= $option[0] ?>
                                <?php else: ?>
                                    <?php $val = $row["select_format"] ?>
                                    <?php for ($k = 1; $k <= count($option); $k++): ?>
                                        <?php $val = str_replace("%$k", $option[$k -1], $val) ?>
                                    <?php endfor ?>
                                    <?= $val ?>
                                <?php endif ?>
                            </option>
                        <?php endforeach ?>
                    <?php else: ?>
                        <option value=""><?= gettext("No data found!!!") ?></option>
                    <?php endif ?>
                </select>

            <?php elseif ($row["type"] === "RADIOBUTTON"): ?>
                <?php $vals = explode(",", $row["radio_options"]) ?>
                <?php foreach ($vals as $v): ?>
                <div class="form-check">
                    <?php $rad = explode(":", $v) ?>
                    <?php if ($this->VALID_SQL_REG_EXP): ?>
                        <?php $check = $list[0][$i] ?>
                    <?php else: ?>
                        <?php $check = $processed[$row["name"]] ?>
                    <?php endif ?>
                    <input
                        id="<?= $row["name"] ?>_<?= $rad[1] ?>"
                        class="form-check-input <?php if (isset($this->FG_fit_expression[$i]) && !$this->FG_fit_expression[$i]): ?>is-invalid<?php endif?>"
                        type="radio"
                        name="<?= $row["name"] ?>"
                        value="<?= $rad[1] ?>"
                        <?php if ($check == $rad[1]): ?>checked="checked"<?php endif ?>
                    />
                    <label for="<?= $row["name"] ?>_<?= $rad[1] ?>" class="form-check-label"><?= $rad[0] ?></label>
                </div>
                <?php endforeach ?>
            <?php endif ?>
            <?php if (isset($this->FG_fit_expression[$i]) && !$this->FG_fit_expression[$i]): ?>
                <div class="form-text invalid-feedback"><?= $row["error"] ?> - <?= $row["regex"][1] ?></div>
            <?php endif ?>
            <?php if ($this->FG_DEBUG == 1): ?>
                <div class="form-text"><?= $row["type"] ?></div>
            <?php endif ?>
            <?php if (!empty($this->FG_TABLE_COMMENT[$i])): ?>
                <div class="form-text"><?= $this->FG_TABLE_COMMENT[$i] ?></div>
            <?php endif ?>
            </div>
        </div>

        <?php elseif (str_contains($row["custom_query"], ":")): ?>
            <?php $table = explode(":", $row["custom_query"]) ?>

            <?php if ($row["type"] === "SELECT"): ?>
            <div class="row mb-3">
                <div class="col-3">
                    <?= $row["label"] ?>
                </div>
                <div class="col">
                    <?php $options = (new Table($table[2], $table[3]))->get_list($this->DBHandle, str_replace("%id", $processed["id"], $table[4]))?>
                    <ul class="list-group">
                    <?php if (is_array($options) && count($options)): ?>
                        <?php foreach ($options as $k=>$option): ?>
                            <?php if (is_numeric($table[7])): ?>
                                <?php $newopts = (new Table($option[$table[7]], $table[11]))->get_list($this->DBHandle, str_replace("%1", $option[$table[7]], $table[11]))?>
                                <?php $option[$table[7]] = $newopts[0][0] ?>
                            <?php endif ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?php if (!empty($option[$table[7]])): ?><strong><?= $option[$table[7]] ?></strong><?php endif ?>
                                <?= $option[0] ?>
                                <button
                                    onclick="sendto('del-content','<?= $i ?>','<?= $table[1] ?>_hidden','<?= $option[1] ?>');"
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
                                <label for="<?= $table[1] ?>_ADD" class="form-label"><?= gettext("Add a new") ?> <?= $row["label"] ?></label>
                                <input name="<?= $table[1] ?>_hidden" type="hidden" value=""/>
                                <select id="<?= $table[1] ?>_ADD" name="<?= $table[1] ?>[]" <?= $row["attributes"] ?> class="form-select form-control-sm">
                                    <?php $options = (new Table($table[2], $table[3]))->get_list($this->DBHandle, $table[15], $table[13], $table[14])?>
                                    <?php if (is_array($options) && count($options)): ?>
                                        <?php foreach ($options as $option): ?>
                                            <?php if (!empty($table[6])): ?>
                                                <?php if (is_numeric($table[7])): ?>
                                                    <?php $newopts = (new Table($option[$table[8]], $table[9]))->get_list($this->DBHandle, str_replace("%1", $option[$table[7]], $table[11]))?>
                                                    <?php $option[$table[7]] = $newopts[0][0] ?>
                                                <?php endif ?>
                                                <?php $val = $table[6] ?>
                                                <?php for ($k = 1; $k <= count($option); $k++): ?>
                                                    <?php $val = str_replace("%$k", $option[$k -1], $val) ?>
                                                <?php endfor ?>
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
                <?php $col = explode(",", $table[2]) ?>
            <div class="row mb-3">
                <div class="col-3"><?= $row["label"] ?></div>
                <div class="col">
                    <?php $options = (new Table($table[0], $table[2]))->get_list($this->DBHandle, str_replace("%id", $processed["id"], $table[3]))?>
                    <ul class="list-group">
                    <?php if (is_array($options) && count($options)): ?>
                        <?php foreach ($options as $k=>$option): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?php if (!empty($option[$table[7]])): ?>( <?= $option[$table[7]] ?> )<?php endif ?>
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
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div class="flex-grow-1 me-3">
                                <label for="<?= $table[1] ?>_ADD" class="form-label"><?= gettext("Add a new") ?> <?= $row["label"] ?></label>
                                <?php if (($row["attributes"] == "multiline")): ?>
                                    <textarea id="<?= $table[1] ?>_ADD" name="<?= $col[0] ?>" class="form-control form-control-sm" cols="40" rows="5"></textarea>
                                <?php else: ?>
                                    <input id="<?= $table[1] ?>_ADD" name="<?= $col[0] ?>" class="form-control form-control-sm" size="20" maxlength="20"/>
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
            <?= $this->FG_BUTTON_EDITION_BOTTOM_TEXT ?>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary"><?= _("Confirm Data") ?></button>
        </div>
    </div>
</form>
