<?php

namespace A2billing\Forms;

use DateTime;

/**
 * @var FormHandler $form
 * @var array $processed
 * @var array $list
 * @var array $db_data
 */

?>

<script>
    function deleteRow(index, value) {
        let form = $("form#editForm");
        form.find("input[name=form_action]").val("del-content");
        form.find("input[name=form_el_index]").val(index);
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
    <input type="hidden" name="current_page" value="<?= $processed["current_page"] ?? "" ?>"/>
    <input type="hidden" name="order" value="<?= $processed["order"] ?? "" ?>"/>
    <input type="hidden" name="sens" value="<?= $processed["sens"] ?? "" ?>"/>
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

    <div class="row mb-3">
        <label id="item<?=$i?>_label" for="<?= $row["name"] ?? "" ?>" class="col-3 col-form-label">
            <?= $row["label"] ?>
        </label>
        <div class="col">

        <?php switch ($row["type"]): case "INPUT": ?>
            <?php if (!empty($row["custom_function"])): ?>
                <?php $db_data[$i] = call_user_func($row["custom_function"], $db_data[$i]) ?>
            <?php endif ?>
            <input
                id="<?= $row["name"] ?>"
                class="form-control <?php if ($row["validation_err"] !== true): ?>is-invalid<?php endif?>"
                name="<?= $row["name"] ?>"
                <?= array_html_attr($row["attributes"]) ?>
            <?php if ($form->all_fields_valid): ?>
                value="<?= $db_data[$i] ?>"
            <?php else: /* if there was a validation error, refill the field with submitted data */ ?>
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
                    <?= array_html_attr($row["attributes"]) ?>
                    value="<?= $form->all_fields_valid ? $db_data[$i] : $processed[$row["name"]] ?>"
                />
                <a
                    href="<?= $row["popup_dest"] ?>"
                    data-window-name="<?= $row["name"] ?>Popup"
                    data-popup-options="<?= $row["popup_params"] ?>"
                    class="btn btn-primary popup_trigger"
                    aria-label="<?= _("open a popup to select an item") ?>"
                >
                    <span class="bi bi-box-arrow-up-right fw-bolder fs-6"></span>
                </a>
            </div>
            <?php break ?>

        <?php case "TEXTAREA": ?>
            <textarea
                id="<?= $row["name"] ?>"
                class="form-control <?php if ($row["validation_err"] !== true): ?>is-invalid<?php endif?>"
                name="<?= $row["name"] ?>"
                <?= array_html_attr($row["attributes"]) ?>
            ><?= $form->all_fields_valid ? $db_data[$i] : $processed[$row["name"]] ?></textarea>
            <?php break ?>

        <?php case "SELECT": ?>
            <select
                id="<?= $row["name"] ?>"
                name="<?= $row["name"] . (array_key_exists("multiple", $row["attributes"]) ? "[]" : "") ?>"
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
                    <?php if (array_key_exists("multiple", $row["attributes"]) && (intval($val) & intval($db_data[$i]))): ?>
                    selected="selected"
                    <?php elseif ($db_data[$i] == $val): ?>
                    selected="selected"
                    <?php endif ?>
                <?php else: /* if there was a validation error, select based on submitted data */ ?>
                    <?php if (array_key_exists("multiple", $row["attributes"]) && (intval($val) & array_sum($processed[$row["name"]]))): ?>
                    selected="selected"
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
            <div class="form-check">
            <?php $check = $form->all_fields_valid && array_key_exists($i, $db_data) ? $db_data[$i] : ($processed[$row["name"]] ?? "") ?>
                <input
                    id="<?= $row["name"] ?>_<?= $val ?>"
                    class="form-check-input <?php if ($row["validation_err"] !== true): ?>is-invalid<?php endif?>"
                    type="radio"
                    name="<?= $row["name"] ?>"
                    value="<?= $val ?>"
                    <?php if ("$check" === "$val"): ?>checked="checked"<?php endif ?>
                />
                <label for="<?= $row["name"] ?>_<?= $val ?>" class="form-check-label"><?= $rad ?></label>
            </div>
            <?php endforeach ?>
            <?php break ?>

        <?php case "DAYTIME": ?>
            <?php
                $value = ($form->all_fields_valid) ? $db_data[$i] : $processed[$row["name"]];
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
                        value="<?= $time ?>"
                    />
                    <input type="hidden" name="<?= $row["name"] ?>" id="<?= $row["name"] ?>" value="<?= $value ?>"/>
                </div>
            </div>
            <?php break ?>

        <?php case "HAS_MANY": ?>
            <?php $entries = $row["table"]->getRows($form->DBHandle, [$row["foreign_key"] => $processed["id"]]) ?>
            <ul class="list-group" aria-labelledby="item<?=$i?>_label">
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
            <?php if (!empty($row["select"])): ?>
                <?php
                $res = $row["table"]->getRows($form->DBHandle);
                $options = array_combine(array_column($res, 0), array_column($res, 1));
                $options = array_filter($options, fn ($k) => !in_array($k, array_column($entries, 0)), ARRAY_FILTER_USE_KEY);
                ?>
                <?php if (count($options)): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div class="flex-grow-1 me-3">
                        <label for="<?= $row["table"]->table ?>_<?= $row["insert"] ?>" class="form-label">
                            <?= _("Add a new entry") ?>
                        </label>
                        <select
                            id="<?= $row["table"]->table ?>_<?= $row["insert"] ?>"
                            class="form-select form-select-sm"
                    <?php if (count($options) === 1): ?>
                            multiple="multiple"
                    <?php else: ?>
                            size="<?= count($options) ?>"
                    <?php endif ?>
                        >
                    <?php foreach ($options as $val => $opt): ?>
                            <option value="<?= $val ?>"><?= $opt ?></option>
                    <?php endforeach ?>
                        </select>
                    </div>
                    <button
                            type="button"
                            class="btn btn-sm btn-primary"
                            onclick="addRow(<?= $i ?>, '<?= $row["table"]->table ?>_<?= $row["insert"] ?>')"
                    ><?= gettext("Add") ?> <?= $row["label"] ?></button>
                </li>
                <?php endif ?>
            <?php else: ?>
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
            <?php endif ?>
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
