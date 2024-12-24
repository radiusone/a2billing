<?php

namespace A2billing\Forms;

use A2billing\Table;
use Closure;
use DateTime;

/**
 * @var FormHandler $form
 * @var array $processed
 * @var array $list
 * @var array $db_data
 */

?>

<script>
    function sendto(action, record, field_inst, instance) {
        let form = $("form#editForm");
        form.find("input[name=form_action]").val(action);
        form.find("input[name=form_el_index]").val(record);
        if (field_inst) {
            let hid = form.find(`input[name=${field_inst}]`);
            if (!hid.length) {
                form.append($(`<input type="hidden" name="${field_inst}" value="${instance}"/>`));
            } else {
                hid.val(instance);
            }
        }
        form.trigger("submit");
    }

    function sendtolittle(direction) {
        $("form#editForm").attr("action", direction).trigger("submit");
    }

    function deleteRow(index, value) {
        let form = $("form#editForm");
        form.find("input[name=form_action]").val("del-content");
        form.append($(`<input type="hidden" name="del-content-value" value="${value}"/>`))
        form.trigger("submit");
    }

    function addRow(index, input_id) {
        let form = $("form#editForm");
        let value = form.find(`#${input_id}`).val();
        form.find("input[name=form_action]").val("add-content");
        form.find("input[name=form_el_index]").val(index);
        form.append($(`<input type="hidden" name="add-content-value" value="${value}"/>`))
        form.trigger("submit");
    }
</script>

<form action="" method="post" name="myForm" id="editForm">
    <input type="hidden" name="id" value="<?= $processed["id"] ?>"/>
    <input type="hidden" name="form_action" value="edit"/>
    <input type="hidden" name="form_el_index" value=""/>
    <input type="hidden" name="current_page" value="<?= $processed["current_page"] ?>"/>
    <input type="hidden" name="order" value="<?= $processed["order"] ?>"/>
    <input type="hidden" name="sens" value="<?= $processed["sens"] ?>"/>
    <?= $form->csrf_inputs() ?>

    <?php foreach ($form->FG_EDIT_QUERY_HIDDEN_INPUTS as $name => $value): ?>
        <input type="hidden" name="<?= htmlspecialchars($name) ?>" value="<?= htmlspecialchars($value ?? "") ?>"/>
    <?php endforeach ?>

    <?php foreach ($form->FG_EDIT_FORM_HIDDEN_INPUTS as $name => $value): ?>
        <input type="hidden" name="<?= htmlspecialchars($name) ?>" value="<?= htmlspecialchars($value ?? "") ?>"/>
    <?php endforeach ?>

    <?php foreach ($form->FG_EDIT_FORM_ELEMENTS as $i=> $row): ?>
        <?php if (!empty($row["section_name"])): ?>
        <div class="row mb-3">
            <h4><?= $row["section_name"] ?></h4>
        </div>
        <?php endif ?>

        <?php if (count($row["custom_query"] ?? []) === 0): // SQL CUSTOM QUERY ?>
        <div class="row mb-3">
            <label for="<?= $row["name"] ?>" class="col-3 col-form-label">
                <?= $row["label"] ?>
            </label>
            <div class="col">

            <?php switch ($row["type"]): case "INPUT": ?>
                <?php if (!empty($row["custom_function"])): ?>
                    <?php $db_data[$i] = $row["custom_function"] instanceof Closure ? $row["custom_function"]($db_data[$i]) : call_user_func($row["custom_function"], $db_data[$i]) ?>
                <?php endif ?>
                <input
                    id="<?= $row["name"] ?>"
                    class="form-control <?php if ($row["validation_err"] !== true): ?>is-invalid<?php endif?>"
                    name="<?= $row["name"] ?>"
                    <?= $row["attributes"] ?>
                    <?php if ($form->VALID_SQL_REG_EXP): /* what is VALID_SQL_REG_EXP */ ?>
                        value="<?= $db_data[$i] ?>"
                    <?php else: ?>
                        value="<?= $processed[$row["name"]] ?>"
                    <?php endif ?>
                />
                <?php break ?>

            <?php case "POPUPVALUE": ?>
                <div class="input-group">
                    <input
                        id="<?= $row["name"] ?>"
                        class="form-control <?php if ($row["validation_err"] !== true): ?>is-invalid<?php endif?>"
                        name="<?= $row["name"] ?>"
                        <?= $row["attributes"] ?>
                        value="<?= $form->VALID_SQL_REG_EXP ? $db_data[$i] : $processed[$row["name"]] ?>"
                    />
                    <a
                        href="<?= $row["popup_dest"] ?>"
                        data-window-name="<?= $row["name"] ?>Popup"
                        data-popup-options="<?= $row["popup_params"] ?>"
                        class="btn btn-primary popup_trigger"
                        aria-label="<?= _("open a popup to select an item") ?>"
                    >
                        <svg class="mx-auto" width="16" height="16"><use xlink:href="#popup"></use></svg>
                    </a>
                </div>
                <?php break ?>

            <?php case "TEXTAREA": ?>
                <textarea
                    id="<?= $row["name"] ?>"
                    class="form-control <?php if ($row["validation_err"] !== true): ?>is-invalid<?php endif?>"
                    name="<?= $row["name"] ?>"
                    <?= $row["attributes"] ?>
                ><?= $form->VALID_SQL_REG_EXP ? $db_data[$i] : $processed[$row["name"]] ?></textarea>
                <?php break ?>

            <?php case "SELECT": ?>
                <select
                    id="<?= $row["name"] ?>"
                    name="<?= $row["name"] . (str_contains($row["attributes"], "multiple") ? "[]" : "") ?>"
                    class="form-select <?php if ($row["validation_err"] !== true): ?>is-invalid<?php endif?>"
                    <?= $row["attributes"] ?>
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
                        <?php if ($form->VALID_SQL_REG_EXP): ?>
                            <?php if (str_contains($row["attributes"], "multiple")): ?>
                                <?php if (intval($val) & intval($db_data[$i])): ?>
                        selected="selected"
                                <?php endif ?>
                            <?php elseif ($db_data[$i] == $val): ?>
                        selected="selected"
                            <?php endif ?>
                        <?php else: ?>
                            <?php if (str_contains($row["attributes"], "multiple")): ?>
                                <?php /* TODO: WTF is this? */ if (is_array($processed[$row["name"]]) && (intval($val) & array_sum($processed[$row["name"]]))): ?>
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
                <?php foreach ($row["radio_options"] as $key => $rad): ?>
                    <?php $val = is_array($rad) ? $rad[1] : $key ?>
                <div class="form-check">
                    <?php $check = $form->VALID_SQL_REG_EXP && array_key_exists($i, $db_data) ? $db_data[$i] : ($processed[$row["name"]] ?? "") ?>
                    <input
                        id="<?= $row["name"] ?>_<?= $val ?>"
                        class="form-check-input <?php if ($row["validation_err"] !== true): ?>is-invalid<?php endif?>"
                        type="radio"
                        name="<?= $row["name"] ?>"
                        value="<?= $val ?>"
                        <?php if ("$check" === "$val"): ?>checked="checked"<?php endif ?>
                    />
                    <label for="<?= $row["name"] ?>_<?= $val ?>" class="form-check-label"><?= is_array($rad) ? $rad[0] : $rad ?></label>
                </div>
                <?php endforeach ?>
                <?php break ?>

            <?php case "DAYTIME": ?>
                <?php
                    $value = ($form->VALID_SQL_REG_EXP) ? $db_data[$i] : $processed[$row["name"]];
                    $day = intdiv($value, 1440);
                    $time = (new DateTime("@" . ($value % 1440) * 60))->format("H:i");
                ?>
                <div class="daytime row">
                    <div class="col-6">
                        <label for="<?= $row["name"] ?>_day"><?= _("Day") ?></label>
                        <select
                            id="<?= $row["name"] ?>_day"
                            class="form-select <?php if ($row["validation_err"] !== true): ?>is-invalid<?php endif?>"
                        >
                            <option value="0" <?php if ($day === 0): ?>selected="selected"<?php endif ?>><?= _("Monday") ?></option>
                            <option value="1" <?php if ($day === 1): ?>selected="selected"<?php endif ?>><?= _("Tuesday") ?></option>
                            <option value="2" <?php if ($day === 2): ?>selected="selected"<?php endif ?>><?= _("Wednesday") ?></option>
                            <option value="3" <?php if ($day === 3): ?>selected="selected"<?php endif ?>><?= _("Thursday") ?></option>
                            <option value="4" <?php if ($day === 4): ?>selected="selected"<?php endif ?>><?= _("Friday") ?></option>
                            <option value="5" <?php if ($day === 5): ?>selected="selected"<?php endif ?>><?= _("Saturday") ?></option>
                            <option value="6" <?php if ($day === 6): ?>selected="selected"<?php endif ?>><?= _("Sunday") ?></option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label for="<?= $row["name"] ?>_time"><?= _("Time") ?></label>
                        <input
                            type="time"
                            id="<?= $row["name"] ?>_time"
                            class="form-control <?php if ($row["validation_err"] !== true): ?>is-invalid<?php endif?>"
                            <?= $row["attributes"] ?>
                            value="<?= $time ?>"
                        />
                        <input type="hidden" name="<?= $row["name"] ?>" id="<?= $row["name"] ?>" value="<?= $value ?>"/>
                    </div>
                </div>
                <?php break ?>

            <?php case "HAS_MANY": ?>
                <?php $entries = $row["table"]->getRows($form->DBHandle, [$row["foreign_key"] => $processed["id"]]) ?>
                <ul class="list-group">
                <?php foreach ($entries as $entry): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?= $entry[1] ?>
                        <button
                            type="button"
                            class="btn btn-sm btn-primary"
                            onclick="deleteRow(<?= $i ?>, '<?= $entry[0] ?>')"
                        ><?= _("Delete") ?></button>
                    </li>
                <?php endforeach ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div class="flex-grow-1 me-3">
                            <label for="<?= $row["table"]->table ?>_<?= $row["insert"] ?>" class="form-label"><?= sprintf(_("Add a new %s"), $row["label"]) ?></label>
                            <?php if ($row["multiline"]): ?>
                                <textarea id="<?= $row["table"]->table ?>_<?= $row["insert"] ?>" class="form-control form-control-sm" rows="5"></textarea>
                            <?php else: ?>
                                <input id="<?= $row["table"]->table ?>_<?= $row["insert"] ?>" class="form-control form-control-sm"/>
                            <?php endif ?>
                        </div>
                        <button
                            type="button"
                            class="btn btn-sm btn-primary"
                            onclick="addRow(<?= $i ?>, '<?= $row["table"]->table ?>_<?= $row["insert"] ?>')"
                        ><?= gettext("Add") ?> <?= $row["label"] ?></button>
                    </li>
                </ul>
                <?php break ?>

            <?php endswitch ?>

            <?php if ($row["validation_err"] !== true): ?>
                <div class="form-text invalid-feedback"><?= $row["error"] ?> - <?= $row["validation_err"] ?></div>
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
                    <?php $options = (new Table($table["tables"], $table["columns"]))->get_list($form->DBHandle, str_replace("%id", $processed["id"], $table["where"]))?>
                    <ul class="list-group">
                    <?php if (is_array($options) && count($options)): ?>
                        <?php foreach ($options as $option): ?>
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
                                    <?php $options = (new Table($table["tables"], $table["columns"]))->get_list($form->DBHandle)?>
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

            <?php endif /*  end input type selection  */ ?>
        <?php endif /*  end check for colon in custom query  */ ?>
    <?php endforeach ?>
    <div class="row my-4 justify-content-between">
        <div class="col-auto">
            <?= $form->FG_EDIT_PAGE_BOTTOM_TEXT ?>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary"><?= _("Confirm Data") ?></button>
        </div>
    </div>
</form>
