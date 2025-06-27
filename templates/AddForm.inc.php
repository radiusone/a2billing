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
    <?php if ($row["type"] === "HAS_MANY") {continue;} ?>
    <?php if (!empty($row["section_name"])): ?>
    <div class="row mb-3">
        <h4><?= $row["section_name"] ?></h4>
    </div>
    <?php endif ?>

    <div class="row mb-3">
        <label for="<?= $row["name"] ?>" class="col-3 col-form-label">
            <?= $row["label"] ?>
        </label>
        <div class="col">
    <?php switch ($row["type"]): case "INPUT": ?>
            <input
                class="form-control <?php if ($row["validation_err"] !== true): ?>is-invalid<?php endif?>"
                name="<?= $row["name"] ?>"
                <?= array_html_attr($row["attributes"]) ?>
        <?php if (!empty($processed[$row["name"]])): ?>
                value="<?= $processed[$row["name"]] ?>"
        <?php endif ?>
            />
            <?php break ?>

    <?php case "POPUPVALUE": ?>
            <div class="input-group">
                <input
                    class="form-control <?php if ($row["validation_err"] !== true): ?>is-invalid<?php endif?>"
                    name="<?= $row["name"] ?>"
                    <?= array_html_attr($row["attributes"]) ?>
        <?php if (!empty($processed[$row["name"]])): ?>
                    value="<?= $processed[$row["name"]] ?>"
        <?php endif ?>
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
                class="form-control <?php if ($row["validation_err"] !== true): ?>is-invalid<?php endif?>"
                name="<?= $row["name"] ?>"
                <?= array_html_attr($row["attributes"]) ?>
            ><?= $processed[$row["name"]] ?? $row["default"] ?? "" ?></textarea>
        <?php break ?>

    <?php case "SELECT": ?>
            <select
                name="<?= $row["name"] . (array_key_exists("multiple", $row["attributes"]) ? "[]" : "") ?>"
                class="form-select <?php if ($row["validation_err"] !== true): ?>is-invalid<?php endif?>"
                <?= array_html_attr($row["attributes"]) ?>
            >
            <?php if (!empty($row["error_message"])): ?>
                <option value="" class="text-danger"><?= $row["error_message"] ?></option>
            <?php endif ?>

            <?php foreach ($row["first_option"] as $val => $opt): ?>
                <option value="<?= $val ?>"><?= $opt ?></option>
            <?php endforeach ?>

            <?php if (empty($row["select_fields"])): ?>
                <option value=""><?= gettext("No data found!!!") ?></option>
            <?php endif ?>

            <?php foreach ($row["select_fields"] as $val => $opt): ?>
                <option
                    value="<?= $val ?>"
                    <?php if ($val == $row["default"]): ?>selected="selected"<?php endif ?>
                >
                    <?= $opt ?>
                </option>
                <?php endforeach ?>
            </select>
        <?php break ?>

        <?php case "RADIOBUTTON": ?>
            <?php foreach ($row["radio_options"] as $val => $rad): ?>
            <div class="form-check">
            <?php if ((string)($processed[$row["name"]] ?? "") === "$val"): ?>
                <?php $check = $val ?>
            <?php elseif ($form->all_fields_valid && array_key_exists($i, $db_data)): ?>
                <?php $check = $db_data[$i] ?>
            <?php else: ?>
                <?php $check = $row["default"] ?>
            <?php endif ?>
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

    <?php case "DAYTIME": //used 2x in FG_var_def_ratecard.inc ?>
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
        <?php break ?>

    <?php case "CAPTCHAIMAGE": ?>
            <table>
                <tr>
                    <td>
                        <img alt="captcha" src="captcha/captcha.php"/>
                    </td>
                </tr>
                <tr>
                    <td>
                        <input
                            id="<?= $row["name"] ?>_captcha"
                            class="form_input_text"
                            name="<?= $row["name"] ?>"
                            value="<?= $processed[$row["name"]] ?? "" ?>"
                            <?= array_html_attr($row["attributes"]) ?>
                        >
                        <label for="<?= $row["name"] ?>_captcha">Enter code from above picture here.</label>
                    </td>
                </tr>
            </table>
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
            <?= $form->FG_ADD_PAGE_BOTTOM_TEXT ?>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary"><?= $form->FG_ADD_PAGE_SAVE_BUTTON_TEXT ?></button>
        </div>
    </div>
</form>
