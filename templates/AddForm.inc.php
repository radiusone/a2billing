<?php

namespace A2billing\Forms;

use A2billing\Table;
use DateTime;

/**
 * @var FormHandler $form
 * @var array $processed
 * @var array $list
 * @var array $db_data
 */
?>

<form action="" method="post" name="myForm" id="myForm">
    <input type="hidden" name="form_action" value="add"/>
    <?= $form->csrf_inputs() ?>

<?php foreach ($form->FG_ADD_QUERY_HIDDEN_INPUTS as $name => $value): ?>
    <input type="hidden" name="<?= htmlspecialchars($name) ?>" value="<?= htmlspecialchars($value) ?>"/>
<?php endforeach ?>

<?php foreach ($form->FG_ADD_FORM_HIDDEN_INPUTS as $name => $value): ?>
    <input type="hidden" name="<?= htmlspecialchars($name) ?>" value="<?= htmlspecialchars($value) ?>"/>
<?php endforeach ?>

<?php foreach ($form->FG_EDIT_FORM_ELEMENTS as $i=>$row):?>
    <?php if (!empty($row["section_name"]) && $row["type"] !== "HAS_MANY"): ?>
    <div class="row mb-3">
        <h4><?= $row["section_name"] ?></h4>
    </div>
    <?php endif ?>

    <?php if (count($row["custom_query"] ?? []) === 0): ?>
    <div class="row mb-3">
        <label for="<?= $row["name"] ?>" class="col-3 col-form-label">
            <?= $row["label"] ?>
        </label>
        <div class="col">
        <?php if ($row["type"] === "INPUT"): ?>
            <input
                id="<?= $row["name"] ?>"
                class="form-control <?php if ($row["validation_err"] !== true): ?>is-invalid<?php endif?>"
                name="<?= $row["name"] ?>"
                <?= $row["attributes"] ?>
                value="<?= $processed[$row["name"]] ?>"
            />

        <?php elseif ($row["type"] === "POPUPVALUE"): ?>
            <div class="input-group">
                <input
                    id="<?= $row["name"] ?>"
                    class="form-control <?php if ($row["validation_err"] !== true): ?>is-invalid<?php endif?>"
                    name="<?= $row["name"] ?>"
                    <?= $row["attributes"] ?>
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

        <?php elseif ($row["type"] === "TEXTAREA"): ?>
            <textarea
                id="<?= $row["name"] ?>"
                class="form-control <?php if ($row["validation_err"] !== true): ?>is-invalid<?php endif?>"
                name="<?= $row["name"] ?>"
                <?= $row["attributes"] ?>
            ></textarea>

        <?php elseif ($row["type"] === "SELECT"): ?>
            <?php if ($row["select_type"] === "SQL"): ?>
                <?php $options = (new Table($row["sql_table"], $row["sql_field"]))->get_list($form->DBHandle, $row["sql_clause"])?>
            <?php else: ?>
                <?php $options = $row["select_type"] === "LIST" ? $row["select_fields"] : [] ?>
            <?php endif ?>
            <?php if ($form->FG_DEBUG >= 2): ?>
                <br/><?php print_r($options)?><br/><?php print_r($db_data)?><br/>#<?= $i ?>::><?= $db_data[$i] ?><br/><br/>::><?= $row["name"] ?>
            <?php endif ?>
            <select
                id="<?= $row["name"] ?>"
                name="<?= $row["name"] ?><?php if (str_icontains($row["attributes"], "multiple")): ?>[]<?php endif ?>"
                class="form-select <?php if ($row["validation_err"] !== true): ?>is-invalid<?php endif?>"
                <?= $row["attributes"] ?>
            >
            <?php if (!empty($row["error_message"])): ?>
                <option value="-1"><?= $row["error_message"] ?></option>
            <?php endif ?>
            <?php if (is_array($row["first_option"] ?? "") && count($row["first_option"]) === 2): ?>
                <option value="<?= $row["first_option"][0] ?>"><?= $row["first_option"][1] ?></option>
            <?php elseif (is_array($row["first_option"] ?? "")): ?>
                <option value="<?= array_keys($row["first_option"])[0] ?>"><?= array_values($row["first_option"])[0] ?></option>
            <?php endif ?>
            <?php if (is_array($options) && count($options)): ?>
                <?php foreach ($options as $key => $option): ?>
                    <?php $opt = is_array($option) ? $option[0] : $option; $val = is_array($option) ? $option[1] : $key ?>
                <option
                    value="<?= $val ?>"
                    <?php if ($val == $row["default"]): ?>selected="selected"<?php endif ?>
                >
                    <?php if (!empty($row["select_format"]) && is_array($option)): ?>
                        <?= preg_replace_callback("/%([0-9]+)/", fn ($m) => str_replace($m[0], $option[$m[1] - 1] ?? "", $m[0]), $row["select_format"]); ?>
                    <?php else: ?>
                        <?= $opt ?>
                    <?php endif ?>
                </option>
                <?php endforeach ?>
            <?php else: ?>
                <option value=""><?= gettext("No data found!!!") ?></option>
            <?php endif ?>
            </select>

        <?php elseif ($row["type"] === "RADIOBUTTON"): ?>
            <?php foreach ($row["radio_options"] as $rad): ?>
            <div class="form-check">
                <?php if ($processed[$row["name"]] === $rad[1]): ?>
                    <?php $check = $rad[1] ?>
                <?php elseif ($form->VALID_SQL_REG_EXP): ?>
                    <?php $check = $db_data[$i] ?>
                <?php else: ?>
                    <?php $check = $row["default"] ?>
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

        <?php elseif ($row["type"] === "DAYTIME"): //used 2x in FG_var_def_ratecard.inc ?>
            <?php
            $value = $row["default"] ?? 0;
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

        <?php elseif ($row["type"] === "CAPTCHAIMAGE"): ?>
            <table>
                <tr>
                    <td>
                        <img alt="captcha" src="captcha/captcha.php"/>
                    </td>
                </tr>
                <tr>
                    <td>
                        <input id="<?= $row["name"] ?>_captcha" class="form_input_text" name="<?= $row["name"] ?>" <?= $row["attributes"] ?> value="<?= $processed[$row["name"]] ?>">
                        <label for="<?= $row["name"] ?>_captcha">Enter code from above picture here.</label>
                    </td>
                </tr>
            </table>
        <?php endif ?>

            <?php if ($row["validation_err"] !== true): ?>
                <div class="form-text invalid-feedback"><?= $row["error"] ?> - <?= $row["validation_err"] ?></div>
            <?php endif ?>
            <?php if ($form->FG_DEBUG == 1): ?>
                <div class="form-text"><?= $row["type"] ?></div>
            <?php endif ?>
            <?php if (!empty($row["comment"])): ?>
                <div class="form-text"><?= $row["comment"] ?></div>
            <?php endif ?>
        </div>
    </div>
    <?php endif ?>
<?php endforeach ?>
    <div class="row my-4 justify-content-between">
        <div class="col-auto">
            <?= $form->FG_ADD_PAGE_BOTTOM_TEXT ?>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary"><?= $form->FG_ADD_PAGE_SAVE_BUTTON_TEXT ?></button>
        </div>
    </div>
</form>
