<?php

use A2billing\Table;

/**
 * @var A2billing\Forms\Formhandler $this
 * @var array $processed
 * @var array $list
 * @var string $wh
 * @var bool $VALID_SQL_REG_EXP
 */
getpost_ifset(array('wh'));
?>

<script src="javascript/calonlydays.js"></script>

<form action="" method="post" name="myForm" id="myForm">
    <input type="hidden" name="form_action" value="add"/>
    <input type="hidden" name="wh" value="<?= $wh ?>"/>
    <input type="hidden" name="atmenu" value="<?= $processed["atmenu"]?>">

    <?= $this->csrf_inputs() ?>
<?php if (!empty($this->FG_QUERY_ADITION_HIDDEN_FIELDS)): ?>
    <?php $fields = explode(",",trim($this->FG_QUERY_ADITION_HIDDEN_FIELDS))?>
    <?php $values = explode(",",trim($this->FG_QUERY_ADITION_HIDDEN_VALUE))?>
    <?php foreach ($fields as $k=>$v): ?>
        <input type="hidden" name="<?= trim($v) ?>" value="<?= trim($values[$k]) ?>"/>
    <?php endforeach ?>
<?php endif ?>

<?php if (!empty($this->FG_ADITION_HIDDEN_PARAM)): ?>
    <?php $fields = explode(",",trim($this->FG_ADITION_HIDDEN_PARAM))?>
    <?php $values = explode(",",trim($this->FG_ADITION_HIDDEN_PARAM_VALUE))?>
    <?php foreach ($fields as $k=>$v): ?>
        <input type="hidden" name="<?= trim($v) ?>" value="<?= trim($values[$k]) ?>"/>
    <?php endforeach ?>
<?php endif ?>

    <table cellspacing="2" class="addform_table1">
         <tbody>
<?php foreach ($this->FG_TABLE_ADITION as $i=>$row):?>
    <?php if (!empty($this->FG_TABLE_ADITION[$i][16]) && strtoupper ($this->FG_TABLE_ADITION[$i][3])!=("HAS_MANY")): ?>
            <tr>
                <td width="%25" valign="top" bgcolor="#FEFEEE" colspan="2" class="tableBodyRight">
                    <i><?= $this->FG_TABLE_EDITION[$i][16] ?></i>
                </td>
            </tr>
    <?php endif ?>

    <?php if (!str_contains($this->FG_TABLE_ADITION[$i][14], ":")): ?>
            <tr>
                <td
                    width="25%"
                    valign="middle"
                    <?php if (isset($this->FG_fit_expression[$i]) && !$this->FG_fit_expression[$i]): ?>
                        class="form_head_red"
                    <?php else: ?>
                        class="form_head"
                    <?php endif ?>
                >
                    <label for="<?= $row["name"] ?>"><?= $row["label"] ?></label>
                </td>
                <td
                    width="75%"
                    valign="top"
                    class="tableBodyRight"
                    <?php if (isset($this->FG_fit_expression[$i]) && !$this->FG_fit_expression[$i]): ?>
                        style="background:url('data:image/gif;base64,R0lGODlhBQB6AKUlAPLi3vLi3/Pj3/Tj3/Tj4PTk4fXk4vTl4fXl4vXl4/bm4/bm5Pbn5Pfn5Pfo5fjo5fjo5vjo5/jo6Pno6Pjp6Pnq6Pnq6frq6Pvq6fvq6vvr6vzr6vzs6/3s7Pzt7P3t7P3t7f7t7f3u7v7u7f7u7v///////////////////////////////////////////////////////////////////////////////////////////////////////////yH5BAEAAD8ALAAAAAAFAHoAAAaRQIBwSCwWA0ikYMlsOgdQKGFKrRau14NWa+h2EeCwOEEmK87oNGO9XrjdjXjcQac/7neIfs+P+P+AEoKCE4WFFIiIFYuLF46OFpGRGJSUGZeXGpqaG52dm5ocoqOkHaamH6mpHqysIK+vIbKysK8it7i5I7u7JL6/wMHCw8TFxsfIycrLzM3Oz9DR0tPU1dbFQQA7')"
                    <?php else: ?>
                        style="background:url('data:image/gif;base64,R0lGODlhBQB6AKUBAAAAAP////7///3+/fr7+vr7+fn6+Pj59vf49fb39PT18fPz7/Ly7vj49ff39Pb28/X18vT08fLy7/v7+fn59/j49vb29PX18////v7+/f39/Pz8+/n5+PTz7/Tz8PX08v38+/z7+vr5+Pn49//+/v79/fz7+/7+/v///////////////////////////////////////////////////////////////////////////////////////////////yH5BAEAAD8ALAAAAAAFAHoAAAaVQIZwSCwWJUjkYslsOjtQqGdKrUauV4VW++l2IeCw+EImP87odGK9trjdjngcQac37veKXn/o90eAgBSDgxyGhiKJiQaMjAWPjxOSkgSVlSGYmCabmxueniChoZ+eGqanqCWqqhmtrQOwsCezsyS2trSzAru8vRi/vwHCw8TFxsfIycrLzM3Oz9DR0tPU1dbX2NnayUEAOw==')"
                    <?php endif ?>
                >
                    <?php if ($this->FG_DEBUG == 1): ?>
                    <?= $row["type"] ?>
                    <?php endif ?>

        <?php if ($row["type"] === "INPUT"): ?>
                <input
                    id="<?= $row["name"] ?>"
                    class="form_input_text"
                    name="<?= $row["name"] ?>"
                    <?= $row["attributes"] ?>
                    value="<?= $processed[$row["name"]] ?>"
                />

        <?php elseif ($row["type"] === "LABEL"): ?>
                <?= $row[4]?>

        <?php elseif ($row["type"] === "POPUPVALUE"): ?>
                <input
                    id="<?= $row["name"] ?>"
                    class="form_input_text"
                    name="<?= $row["name"] ?>"
                    <?= $row["attributes"] ?>
                    <?php if ($this->VALID_SQL_REG_EXP): ?>
                        value="<?= $list[0][$i] ?>"
                    <?php else: ?>
                        value="<?= $processed[$row["name"]] ?>"
                    <?php endif ?>
                />
                <a href="#" title="<?= gettext("SELECT")?>" onclick="window.open('<?= $row["popup_dest"] ?>popup_formname=myForm&popup_fieldname=<?= $row["name"] ?>', <?= $row["popup_params"] ?>)">
                    <img alt="" src="data:image/gif;base64,R0lGODlhDwAPAMQYAP+yPf+fEv+qLP+3Tf+pKv++Xf/Gcv+mJP+tNf+tMf+kH/+/YP+oJv+wO/+jHP/Ohf/WmP+vOv/cpv+kHf+iGf+jG/////Hw7P///wAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAABgALAAAAAAPAA8AAAVjIHaNZEmKF6auLJpiEvQYxQAgiTpiMm0Tk4pigsLMag2Co8KkFA0Lm8XCbBajDcFkWnXuBlkFk1vxpgACcYVcLqbHVKaDuFNXqwxGkUK5VyYMEQhFGAGGhxQHOS4tjTsmkDshADs="/>
                </a>

        <?php elseif ($row["type"] === "CAPTCHAIMAGE"): ?>
                <table cellpadding="2" cellspacing="0" border="0" width="100%">
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

        <?php elseif (str_starts_with($row["type"], "POPUP")): ?>
                <input
                    id="<?= $row["name"] ?>"
                    class="form_enter"
                    name="<?= $row["name"] ?>"
                    <?= $row["attributes"] ?>
                    <?php if ($this->VALID_SQL_REG_EXP): ?>
                        value="<?= $list[0][$i] ?>"
                    <?php else: ?>
                        value="<?= $processed[$row["name"]] ?>"
                    <?php endif ?>
                />
            <?php if ($row["type"] === "POPUPVALUETIME"): ?>
                <a href="#" title="<?= gettext("SELECT")?>" onclick="window.open('<?= $row["popup_timeval"] ?>popup_formname=myForm&popup_fieldname=<?= $row["name"] ?>', <?= $row["popup_timeval"] ?>)">
                    <img alt="" src="data:image/gif;base64,R0lGODlhDwAPAMQYAP+yPf+fEv+qLP+3Tf+pKv++Xf/Gcv+mJP+tNf+tMf+kH/+/YP+oJv+wO/+jHP/Ohf/WmP+vOv/cpv+kHf+iGf+jG/////Hw7P///wAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAABgALAAAAAAPAA8AAAVjIHaNZEmKF6auLJpiEvQYxQAgiTpiMm0Tk4pigsLMag2Co8KkFA0Lm8XCbBajDcFkWnXuBlkFk1vxpgACcYVcLqbHVKaDuFNXqwxGkUK5VyYMEQhFGAGGhxQHOS4tjTsmkDshADs="/>
                </a>
            <?php elseif ($row["type"] === "POPUPDATETIME"): ?>
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
                <texarea
                    id="<?= $row["name"] ?>"
                    class="form_input_textarea"
                    name="<?= $row["name"] ?>"
                    <?= $row["attributes"] ?>
                >
                </texarea>

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
                    class="form_input_select"
                    <?= $row["attributes"] ?>
                >
                    <?= $row["first_option"] ?>
                <?php if (!empty($row["error_message"])): ?>
                    <option value="-1"><?= $row["error_message"] ?></option>
                <?php endif ?>
                    <?php if (is_array($options) && count($options)): ?>
                        <?php foreach ($options as $option): ?>
                            <option
                                value="<?= $option[1] ?>"
                            <?php if ($row["default"] === $option[1]): ?>
                                selected
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
                <?php $rad = explode(":", $v) ?>
                <label for="<?= $row["name"] ?>_<?= $rad[1] ?>"><?= $rad[0] ?></label>
                <?php if ($processed[$row["name"]] === $rad[1]): ?>
                    <?php $check = $rad[1] ?>
                <?php elseif ($VALID_SQL_REG_EXP): ?>
                    <?php $check = $list[0][$i] ?>
                <?php else: ?>
                    <?php $check = $row["default"] ?>
                <?php endif ?>
                <input
                    id="<?= $row["name"] ?>_<?= $rad[1] ?>"
                    class="form_enter"
                    type="radio"
                    name="<?= $row["name"] ?>"
                    value="<?= $rad[1] ?>"
                    <?php if ($check === $rad[1]): ?>checked<?php endif ?>
                />
            <?php endforeach ?>
        <?php endif ?>

                <span class="liens">
                    <?php if (isset($this->FG_fit_expression[$i]) && !$this->FG_fit_expression[$i]): ?>
                        <br/><?= $row["error"] ?> - <?= $row["regex"][1] ?>
                    <?php endif ?>
                </span>
                <?php if (!empty($this->FG_TABLE_COMMENT[$i])): ?>
                    <br/><?= $this->FG_TABLE_COMMENT[$i] ?>
                <?php endif ?>

    <?php endif ?>
<?php endforeach ?>

        </tbody>
    </table>

    <table cellspacing="0" class="editform_table8">
        <tr>
            <td width="50%" class="text_azul">
                <span class="tableBodyRight"><?= $this->FG_BUTTON_ADITION_BOTTOM_TEXT ?></span>
            </td>
            <td width="50%" align="right" valign="top" class="text">
                <a href="#" onclick="document.myForm.submit()" class="cssbutton_big" title="<?= gettext("Create a new ") ?><?= $this->FG_INSTANCE_NAME ?>">
                    <img style="vertical-align:middle" alt="" src="data:image/gif;base64,R0lGODlhDwAPAMQYAP+yPf+fEv+qLP+3Tf+pKv++Xf/Gcv+mJP+tNf+tMf+kH/+/YP+oJv+wO/+jHP/Ohf/WmP+vOv/cpv+kHf+iGf+jG/////Hw7P///wAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAABgALAAAAAAPAA8AAAVjIHaNZEmKF6auLJpiEvQYxQAgiTpiMm0Tk4pigsLMag2Co8KkFA0Lm8XCbBajDcFkWnXuBlkFk1vxpgACcYVcLqbHVKaDuFNXqwxGkUK5VyYMEQhFGAGGhxQHOS4tjTsmkDshADs="/>
                    <?= $this->FG_ADD_PAGE_CONFIRM_BUTTON ?>
                </a>
            </td>
        </tr>
    </table>
</form>
