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

    function MM_openBrWindow(theURL,winName,features) {
        window.open(theURL,winName,features);
    }

    function sendto(action, record, field_inst, instance) {
        document.myForm.form_action.value = action;
        document.myForm.sub_action.value = record;
        if (field_inst != null) {
            document.myForm.elements[field_inst].value = instance;
        }
        document.myForm.submit();
    }

    function sendtolittle(direction) {
        document.myForm.action=direction;
        document.myForm.submit();
    }
</script>

<form action="" method="post" name="myForm" id="myForm">
    <input type="hidden" name="id" value="<?= $processed["id"] ?>"/>
    <input type="hidden" name="form_action" value="edit"/>
    <input type="hidden" name="sub_action" value=""/>
    <input type="hidden" name="atmenu" value="<?= $processed["atmenu"] ?>"/>
    <input type="hidden" name="stitle" value="<?= $processed["stitle"] ?>"/>
    <input type="hidden" name="current_page" value="<?= $processed["current_page"] ?>"/>
    <input type="hidden" name="order" value="<?= $processed["order"] ?>"/>
    <input type="hidden" name="sens" value="<?= $processed["sens"] ?>"/>
    <?php if ($this->FG_CSRF_STATUS): ?>
        <input type="hidden" name="<?= $this->FG_FORM_UNIQID_FIELD ?>" value="<?= $this->FG_FORM_UNIQID ?>"/>
        <input type="hidden" name="<?= $this->FG_CSRF_FIELD ?>" value="<?= $this->FG_CSRF_TOKEN ?>"/>
    <?php endif ?>

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

    <table class="editform_table1" cellspacing="2">
        <tbody>
        <?php foreach ($this->FG_TABLE_EDITION as $i=>$row): ?>
            <?php $options = null ?>
            <?php if (strlen($row["section_name"]) > 1): ?>
                <tr>
                    <td width="25%" valign="top" bgcolor="#fefeee" colspan="2" class="tableBodyRight">
                        <i><?= $row["section_name"] ?></i>
                    </td>
                </tr>
            <?php endif ?>

            <?php if (!str_contains($row["custom_query"], ":")): // SQL CUSTOM QUERY ?>
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

                <?php if ($this->FG_DISPLAY_SELECT && !empty($list[0][$this->FG_SELECT_FIELDNAME]) && $this->FG_CONF_VALUE_FIELDNAME === $row["name"]): ?>
                        <select id="<?= $row["name"] ?>" name="<?= $row["name"] ?>" class="form_input_select">
                            <?php $vals = explode(",", $list[0][$this->FG_SELECT_FIELDNAME]) ?>
                            <?php foreach ($vals as $val): ?>
                                <option <?php if ($val == $list[0][$i]): ?>selected<?php endif ?>><?= $val ?></option>
                            <?php endforeach ?>
                        </select>

                <?php elseif ($row["type"] === "INPUT"): ?>
                        <?php if (!empty($row["custom_function"])): ?>
                            <?php $list[0][$i] = call_user_func($row["custom_function"], $list[0][$i]) ?>
                        <?php endif ?>
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
                            <?php if (str_icontains($row["attributes"], "readonly")): ?>style="background-color: #ccc"<?php endif ?>
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
                            class="form_enter"
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

                    <?php elseif ($row["type"] === "POPUPVALUETIME"): ?>
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
                            <?php if (str_icontains($row["attributes"], "readonly")): ?>style="background-color: #ccc"<?php endif ?>
                        >
                            <?php if ($this->VALID_SQL_REG_EXP): ?>
                                <?= $list[0][$i] ?>
                            <?php else: ?>
                                <?= $processed[$row["name"]] ?>
                            <?php endif ?>
                        </texarea>

                <?php elseif ($row["type"] === "SPAN"): ?>
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
                            class="form_input_select"
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
                                                    selected
                                                <?php endif ?>
                                            <?php else: ?>
                                                <?php if ($list[0][$i] === $option[1]): ?>
                                                    selected
                                                <?php endif ?>
                                            <?php endif ?>
                                        <?php else: ?>
                                            <?php if (str_icontains($row["attributes"], "multiple")): ?>
                                                <?php if (is_array($processed[$row["name"]]) && (intval($option[1]) & array_sum($processed[$row["name"]]))): ?>
                                                    selected
                                                <?php endif ?>
                                            <?php else: ?>
                                                <?php if ($processed[$row["name"]] === $option[1]): ?>
                                                    selected
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
                        <?php $rad = explode(":", $v) ?>
                        <label for="<?= $row["name"] ?>_<?= $rad[1] ?>"><?= $rad[0] ?></label>
                        <?php if ($this->VALID_SQL_REG_EXP): ?>
                            <?php $check = $list[0][$i] ?>
                        <?php else: ?>
                            <?php $check = $processed[$row["name"]] ?>
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
                    </td>
                </tr>

            <?php elseif (str_contains($row["custom_query"], ":")): ?>
                <?php $table = explode(":", $row["custom_query"]) ?>

                <?php if ($row["type"] === "SELECT"): ?>
                <tr>
                    <td width="122" class="form_head"><?= $row["label"] ?></td>
                    <td align="center" valign="top" class="editform_table1_td1">
                        <br/>
                        <table class="editform_table2" cellspacing="0">
                            <tr class="editform_table2_td1">
                                <td height="16" style="padding-left: 5px; padding-right: 3px" class="form_head">
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                        <tr>
                                            <td class="form_head"><?= $row["label"] ?> <?= gettext("LIST ") ?></td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <table class="editform_table3" cellspacing="0">
                                        <?php $options = (new Table($table[2], $table[3]))->get_list($this->DBHandle, str_replace("%id", $processed["id"], $table[4]))?>
                                        <?php if (is_array($options) && count($options)): ?>
                                            <?php foreach ($options as $k=>$option): ?>
                                                <?php if (is_numeric($table[7])): ?>
                                                    <?php $newopts = (new Table($option[$table[7]], $table[11]))->get_list($this->DBHandle, str_replace("%1", $option[$table[7]], $table[11]))?>
                                                    <?php $option[$table[7]] = $newopts[0][0] ?>
                                                <?php endif ?>
                                                <tr bgcolor="<?= $this->FG_TABLE_ALTERNATE_ROW_COLOR[$k % 2] ?>" onmouseover="bgColor='#c4ffd7'" onmouseout="bgColor='<?= $this->FG_TABLE_ALTERNATE_ROW_COLOR[$k % 2] ?>'">
                                                    <td valign="top" class="tableBody" style="font-family: Verdana,sans-serif; font-size: small">
                                                        <?php if (!empty($option[$table[7]])): ?>
                                                            <strong><?= $option[$table[7]] ?></strong>
                                                        <?php endif ?>
                                                        <?= $option[0] ?>
                                                    </td>
                                                    <td align="center" vAlign="top" class="tableBodyRight">
                                                        <input
                                                                onclick="sendto('del-content','<?= $i ?>','<?= $table[1] ?>_hidden','<?= $option[1] ?>');"
                                                                title="Remove this <?= $row["label"] ?>"
                                                                alt="Remove this <?= $row["label"] ?>"
                                                                border="0" height="11" hspace="2"
                                                                id="submit<?= $i ?>"
                                                                name="submit<?= $i ?>"
                                                                src="data:image/gif;base64,R0lGODlhIQALAJEAAPPt2ZycnAAAAP///yH5BAEAAAMALAAAAAAhAAsAAAJAXI6py2gAo5y0xmOzljgKAX0gGH6AeJpXII2nB8ckx8IjOatvOnXhm4v9drQWzoRC7ZQQ3+bZq0GnTUPjinUUAAA7"
                                                                type="image"
                                                                width="33"
                                                                value="add-split"
                                                        />
                                                    </td>
                                                </tr>
                                            <?php endforeach ?>
                                        <?php else: ?>
                                            <tr bgcolor="<?= $this->FG_TABLE_ALTERNATE_ROW_COLOR[0] ?>" onmouseover="bgColor='#C4FFD7'" onmouseout="bgColor='<?= $this->FG_TABLE_ALTERNATE_ROW_COLOR[0] ?>'">
                                                <td colspan="2" align="<?= $this->FG_TABLE_COL[$i][3] ?>" valign=top class="tableBody">
                                                    <div align="center" class="liens">
                                                        <?= gettext("No") ?><?= $row["label"] ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif ?>
                                    </table>
                                </td>
                            </tr>
                            <tr class="bgcolor_016">
                                <td class="editform_table3_td2" height="4"></td>
                            </tr>
                        </table>
                        <br/>
                    </td>
                </tr>
                <tr>
                    <?php /* *******************   Select to ADD new instances  ****************************** */ ?>
                    <td class="form_head">&nbsp;</td>
                    <td align="center" valign="top" class="text" style="background:url('data:image/gif;base64,R0lGODlhBQB6AKUBAAAAAP////7///3+/fr7+vr7+fn6+Pj59vf49fb39PT18fPz7/Ly7vj49ff39Pb28/X18vT08fLy7/v7+fn59/j49vb29PX18////v7+/f39/Pz8+/n5+PTz7/Tz8PX08v38+/z7+vr5+Pn49//+/v79/fz7+/7+/v///////////////////////////////////////////////////////////////////////////////////////////////yH5BAEAAD8ALAAAAAAFAHoAAAaVQIZwSCwWJUjkYslsOjtQqGdKrUauV4VW++l2IeCw+EImP87odGK9trjdjngcQac37veKXn/o90eAgBSDgxyGhiKJiQaMjAWPjxOSkgSVlSGYmCabmxueniChoZ+eGqanqCWqqhmtrQOwsCezsyS2trSzAru8vRi/vwHCw8TFxsfIycrLzM3Oz9DR0tPU1dbX2NnayUEAOw==')">
                        <br/>
                        <table width="300" height="50" border="0" align="center" cellpadding="0" cellspacing="0">
                            <tr>
                                <td bgcolor="#7f99cc" colspan="3" height="16" class="form_head" style="padding-left: 5px; padding-right: 5px">
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                        <tr>
                                            <td class="form_head"><label for="<?= $table[1] ?>_ADD"><?= gettext("Add a new") ?> <?= $row["label"] ?></label></td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td class="form_head">
                                    <img alt="" src="data:image/gif;base64,R0lGODlhAQABAIAAAOZ3fP///yH5BAEAAAEALAAAAAABAAEAAAICTAEAOw=="/>
                                </td>
                                <td class="editform_table4_td1">
                                    <table width="97%" border="0" align="center" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td width="122" class="tableBody"><?= $row["label"] ?></td>
                                            <td width="516">
                                                <div align="center">
                                                    <input name="<?= $table[1] ?>_hidden" type="hidden" value=""/>
                                                    <select id="<?= $table[1] ?>_ADD"> name="<?= $table[1] ?>[]" <?= $row["attributes"] ?> class="form_input_select">
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
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" height="4"></td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" align="center" valign="middle">
                                                <a href="#" onclick="sendto('add-content', '<?= $i ?>')">
                                                    <span class="cssbutton"><?= gettext("ADD") ?><?= $row["label"] ?></span>
                                                </a>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                                <td class="form_head">
                                    <img alt="" src="data:image/gif;base64,R0lGODlhAQABAIAAAOZ3fP///yH5BAEAAAEALAAAAAABAAEAAAICTAEAOw=="/>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3" class="form_head">
                                    <img alt="" src="data:image/gif;base64,R0lGODlhAQABAIAAAOZ3fP///yH5BAEAAAEALAAAAAABAAEAAAICTAEAOw==">
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <?php elseif ($row["type"] === "INSERT"): ?>
                <tr>
                    <td width="122" class="form_head"><?= $row["label"] ?></td>
                    <td align="center" valign="top" style="background:url('data:image/gif;base64,R0lGODlhBQB6AKUBAAAAAP////7///3+/fr7+vr7+fn6+Pj59vf49fb39PT18fPz7/Ly7vj49ff39Pb28/X18vT08fLy7/v7+fn59/j49vb29PX18////v7+/f39/Pz8+/n5+PTz7/Tz8PX08v38+/z7+vr5+Pn49//+/v79/fz7+/7+/v///////////////////////////////////////////////////////////////////////////////////////////////yH5BAEAAD8ALAAAAAAFAHoAAAaVQIZwSCwWJUjkYslsOjtQqGdKrUauV4VW++l2IeCw+EImP87odGK9trjdjngcQac37veKXn/o90eAgBSDgxyGhiKJiQaMjAWPjxOSkgSVlSGYmCabmxueniChoZ+eGqanqCWqqhmtrQOwsCezsyS2trSzAru8vRi/vwHCw8TFxsfIycrLzM3Oz9DR0tPU1dbX2NnayUEAOw==')" class="text">
                        <br/>
                        <table cellspacing="0" class="editform_table2">
                            <tr bgcolor="#fff">
                                <td height="16" style="padding-left: 5px; padding-right: 3px;" class="form_head">
                                    <table border="0" cellspacing="0" cellpadding="0" width="100%">
                                        <tr>
                                            <td class="form_head"><?= $row["label"] ?>&nbsp;<?= gettext("LIST") ?></td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                        <?php $options = (new Table($table[2], $table[3]))->get_list($this->DBHandle, str_replace("%id", $processed["id"], $table[4]))?>
                                        <?php if (is_array($options) && count($options)): ?>
                                            <?php foreach ($options as $k=>$option): ?>
                                                <tr bgcolor="<?= $this->FG_TABLE_ALTERNATE_ROW_COLOR[$k % 2] ?>"  onmouseover="bgColor='#C4FFD7'" onmouseout="bgColor='<?= $this->FG_TABLE_ALTERNATE_ROW_COLOR[$k % 2] ?>'">
                                                    <td colspan="2" align="<?= $this->FG_TABLE_COL[$i][3] ?>" valign="top" class="tableBody" style="font: small Verdana,sans-serif">
                                                        <?php if (!empty($option[$table[7]])): ?>
                                                            <strong><?= $option[$table[7]] ?></strong> :
                                                        <?php endif ?>
                                                        <?= $option[0] ?>
                                                    </td>
                                                    <td align="center" valign="top2" class="tableBodyRight">
                                                        <input
                                                                onclick="sendto('del-content','<?= $i ?>','<?= $table[1] ?>','<?= $option[1] ?>');"
                                                                alt="Remove this <?= $row["label"] ?>"
                                                                border="0" height="11" hspace="2"
                                                                id="submit<?= $i ?>"
                                                                name="submit<?= $i ?>"
                                                                src="data:image/gif;base64,R0lGODlhIQALAJEAAPPt2ZycnAAAAP///yH5BAEAAAMALAAAAAAhAAsAAAJAXI6py2gAo5y0xmOzljgKAX0gGH6AeJpXII2nB8ckx8IjOatvOnXhm4v9drQWzoRC7ZQQ3+bZq0GnTUPjinUUAAA7"
                                                                type="image"
                                                                width="33"
                                                                value="add-split"
                                                        />
                                                    </td>
                                                </tr>
                                            <?php endforeach ?>
                                        <?php else: ?>
                                            <tr bgcolor="<?= $this->FG_TABLE_ALTERNATE_ROW_COLOR[0] ?>"  onmouseover="bgColor='#C4FFD7'" onmouseout="bgColor='<?= $this->FG_TABLE_ALTERNATE_ROW_COLOR[0] ?>'">
                                                <td colspan="2" align="<?= $this->FG_TABLE_COL[$i][3] ?>" valign="top" class="tableBody">
                                                    <div align="center" class="liens">
                                                        No <?= $row["label"] ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif ?>
                                    </table>
                                </td>
                            </tr>
                            <tr class="bgcolor_016">
                                <td class="editform_table3_td2">
                                    <table border="0" cellspacing="0" cellpadding="0" width="100%">
                                        <tr>
                                            <td height="4" align="right"></td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                        <br/>
                    </td>
                </tr>
                <tr>
                    <?php /* ******************   Select to ADD new instances  ***************************** */ ?>
                    <td class="form_head">&nbsp;</td>
                    <td align="center" valign="top" style="background:url('data:image/gif;base64,R0lGODlhBQB6AKUBAAAAAP////7///3+/fr7+vr7+fn6+Pj59vf49fb39PT18fPz7/Ly7vj49ff39Pb28/X18vT08fLy7/v7+fn59/j49vb29PX18////v7+/f39/Pz8+/n5+PTz7/Tz8PX08v38+/z7+vr5+Pn49//+/v79/fz7+/7+/v///////////////////////////////////////////////////////////////////////////////////////////////yH5BAEAAD8ALAAAAAAFAHoAAAaVQIZwSCwWJUjkYslsOjtQqGdKrUauV4VW++l2IeCw+EImP87odGK9trjdjngcQac37veKXn/o90eAgBSDgxyGhiKJiQaMjAWPjxOSkgSVlSGYmCabmxueniChoZ+eGqanqCWqqhmtrQOwsCezsyS2trSzAru8vRi/vwHCw8TFxsfIycrLzM3Oz9DR0tPU1dbX2NnayUEAOw==')" class="text">
                        <br/>
                        <table width="300" height="50" border="0" align="center" cellpadding="0" cellspacing="0">
                            <tr>
                                <td bgcolor="#7f99cc" colspan="3" height="16" style="padding-left: 5px; padding-right: 5px;" class="form_head">
                                    <table border="0" cellspacing="0" cellpadding="0" width="100%">
                                        <tr>
                                            <td class="form_head"><label for="<?= $table[1] ?>_ADD"><?= gettext("Add a new") ?> <?= $row["label"] ?></label></td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td class="form_head">
                                    <img alt="" src="data:image/gif;base64,R0lGODlhAQABAIAAAOZ3fP///yH5BAEAAAEALAAAAAABAAEAAAICTAEAOw=="/>
                                </td>
                                <td class="editform_table4_td1">
                                    <table width="97%" border="0" cellpadding="0" cellspacing="0" align="center">
                                        <tr>
                                            <td width="122" class="tableBody"><?= $row["label"] ?></td>
                                            <td width="516">
                                                <div align="left">
                                                    <?php if (($row["attributes"] == "multiline")): ?>
                                                        <textarea id="<?= $table[1] ?>_ADD" name="<?= $table[1] ?>" class="form_input_text" cols="40" rows="5"></textarea>
                                                    <?php else: ?>
                                                        <input id="<?= $table[1] ?>_ADD" name="<?= $table[1] ?>" class="form_input_text" size="20" maxlength="20"/>
                                                    <?php endif ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" align="center">
                                                <a href="#" onclick="sendto('add-content', '<?= $i ?>')">
                                                    <span class="cssbutton"><?= gettext("ADD") ?> <?= $row["label"] ?></span>
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" height="4"></td>
                                        </tr>
                                        <tr>
                                            <td colspan="2">
                                                <div align="right"></div>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                                <td class="form_head">
                                    <img alt="" src="data:image/gif;base64,R0lGODlhAQABAIAAAOZ3fP///yH5BAEAAAEALAAAAAABAAEAAAICTAEAOw=="/>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3" class="form_head">
                                    <img alt="" src="data:image/gif;base64,R0lGODlhAQABAIAAAOZ3fP///yH5BAEAAAEALAAAAAABAAEAAAICTAEAOw=="/>
                                </td>
                            </tr>
                        </table>
                        <br/>
                    </td>
                </tr>

                <?php elseif ($row["type"] === "HAS_MANY"): ?>
                    <?php $col = explode(",", $table[2]) ?>
                <tr>
                    <td width="122" class="form_head"><?= $row["label"] ?></td>
                    <td align="center" valign="top" style="background: url('data:image/gif;base64,R0lGODlhBQB6AKUBAAAAAP////7///3+/fr7+vr7+fn6+Pj59vf49fb39PT18fPz7/Ly7vj49ff39Pb28/X18vT08fLy7/v7+fn59/j49vb29PX18////v7+/f39/Pz8+/n5+PTz7/Tz8PX08v38+/z7+vr5+Pn49//+/v79/fz7+/7+/v///////////////////////////////////////////////////////////////////////////////////////////////yH5BAEAAD8ALAAAAAAFAHoAAAaVQIZwSCwWJUjkYslsOjtQqGdKrUauV4VW++l2IeCw+EImP87odGK9trjdjngcQac37veKXn/o90eAgBSDgxyGhiKJiQaMjAWPjxOSkgSVlSGYmCabmxueniChoZ+eGqanqCWqqhmtrQOwsCezsyS2trSzAru8vRi/vwHCw8TFxsfIycrLzM3Oz9DR0tPU1dbX2NnayUEAOw==')" class="text">
                        <br/>
                        <table cellspacing="0" class="editform_table2">
                            <tr bgcolor="#fff">
                                <td height="16" style="padding-left: 5px; padding-right: 3px;" class="form_head">
                                    <table border="0" cellspacing="0" cellpadding="0" width="100%">
                                        <tr>
                                            <td class="form_head"><?= $row["label"] ?> <?= gettext("LIST") ?></td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                        <?php $options = (new Table($table[0], $table[2]))->get_list($this->DBHandle, str_replace("%id", $processed["id"], $table[3]))?>
                                        <?php if (is_array($options) && count($options)): ?>
                                            <?php foreach ($options as $k=>$option): ?>
                                                <tr bgcolor="<?= $this->FG_TABLE_ALTERNATE_ROW_COLOR[$k % 2] ?>"  onmouseover="bgColor='#C4FFD7'" onmouseout="bgColor='<?= $this->FG_TABLE_ALTERNATE_ROW_COLOR[$k % 2] ?>'">
                                                    <td colspan="2" align="<?= $this->FG_TABLE_COL[$i][3] ?>" valign="top" class="tableBody" style="font: small Verdana,sans-serif">
                                                        <?php if (!empty($option[$table[7]])): ?>
                                                            ( <?= $option[$table[7]] ?> )
                                                        <?php endif ?>
                                                        <?= $option[0] ?>
                                                    </td>
                                                    <td align="center" valign="top2" class="tableBodyRight">
                                                        <input
                                                                onclick="sendto('del-content','<?= $i ?>','<?= $col[0] ?>','<?= $option[0] ?>');"
                                                                alt="Remove this <?= $row["label"] ?>"
                                                                border="0" height="11" hspace="2"
                                                                id="submit<?= $i ?>"
                                                                name="submit<?= $i ?>"
                                                                src="data:image/gif;base64,R0lGODlhIQALAJEAAPPt2ZycnAAAAP///yH5BAEAAAMALAAAAAAhAAsAAAJAXI6py2gAo5y0xmOzljgKAX0gGH6AeJpXII2nB8ckx8IjOatvOnXhm4v9drQWzoRC7ZQQ3+bZq0GnTUPjinUUAAA7"
                                                                type="image"
                                                                width="33"
                                                                value="add-split"
                                                        />
                                                    </td>
                                                </tr>
                                            <?php endforeach ?>
                                        <?php else: ?>
                                            <tr bgcolor="<?= $this->FG_TABLE_ALTERNATE_ROW_COLOR[0] ?>"  onmouseover="bgColor='#C4FFD7'" onmouseout="bgColor='<?= $this->FG_TABLE_ALTERNATE_ROW_COLOR[0] ?>'">
                                                <td colspan="2" align="<?= $this->FG_TABLE_COL[$i][3] ?>" valign="top" class="tableBody">
                                                    <div align="center" class="liens">
                                                        No <?= $row["label"] ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif ?>
                                    </table>
                                </td>
                            </tr>
                            <tr class="bgcolor_016">
                                <td class="editform_table3_td2">
                                    <table border="0" cellspacing="0" cellpadding="0" width="100%">
                                        <tr>
                                            <td height="4" align="right"></td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                        <br/>
                    </td>
                </tr>
                <tr>
                    <?php /* ******************   Select to ADD new instances  ***************************** */ ?>
                    <td class="form_head">&nbsp;</td>
                    <td align="center" valign="top" style="background:url('data:image/gif;base64,R0lGODlhBQB6AKUBAAAAAP////7///3+/fr7+vr7+fn6+Pj59vf49fb39PT18fPz7/Ly7vj49ff39Pb28/X18vT08fLy7/v7+fn59/j49vb29PX18////v7+/f39/Pz8+/n5+PTz7/Tz8PX08v38+/z7+vr5+Pn49//+/v79/fz7+/7+/v///////////////////////////////////////////////////////////////////////////////////////////////yH5BAEAAD8ALAAAAAAFAHoAAAaVQIZwSCwWJUjkYslsOjtQqGdKrUauV4VW++l2IeCw+EImP87odGK9trjdjngcQac37veKXn/o90eAgBSDgxyGhiKJiQaMjAWPjxOSkgSVlSGYmCabmxueniChoZ+eGqanqCWqqhmtrQOwsCezsyS2trSzAru8vRi/vwHCw8TFxsfIycrLzM3Oz9DR0tPU1dbX2NnayUEAOw==')" class="text">
                        <br/>
                        <table width="300" height="50" border="0" align="center" cellpadding="0" cellspacing="0">
                            <tr>
                                <td bgcolor="#7f99cc" colspan="3" height="16" style="padding-left: 5px; padding-right: 5px;" class="form_head">
                                    <table border="0" cellspacing="0" cellpadding="0" width="100%">
                                        <tr>
                                            <td class="form_head"><label for="<?= $table[1] ?>_ADD"><?= gettext("Add a new") ?> <?= $row["label"] ?></label></td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td class="form_head">
                                    <img alt="" src="data:image/gif;base64,R0lGODlhAQABAIAAAOZ3fP///yH5BAEAAAEALAAAAAABAAEAAAICTAEAOw=="/>
                                </td>
                                <td class="editform_table4_td1">
                                    <table width="97%" border="0" cellpadding="0" cellspacing="0" align="center">
                                        <tr>
                                            <td width="122" class="tableBody"><?= $row["label"] ?></td>
                                            <td width="516">
                                                <div align="left">
                                                    <?php if (($row["attributes"] == "multiline")): ?>
                                                        <textarea id="<?= $table[1] ?>_ADD" name="<?= $col[1] ?>" class="form_input_text" cols="40" rows="5"></textarea>
                                                    <?php else: ?>
                                                        <input id="<?= $table[1] ?>_ADD" name="<?= $col[1] ?>" class="form_input_text" size="20" maxlength="20"/>
                                                    <?php endif ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" align="center">
                                                <a href="#" onclick="sendto('add-content', '<?= $i ?>')">
                                                    <span class="cssbutton"><?= gettext("ADD") ?> <?= $row["label"] ?></span>
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" height="4"></td>
                                        </tr>
                                        <tr>
                                            <td colspan="2">
                                                <div align="right"></div>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                                <td class="form_head">
                                    <img alt="" src="data:image/gif;base64,R0lGODlhAQABAIAAAOZ3fP///yH5BAEAAAEALAAAAAABAAEAAAICTAEAOw=="/>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3" class="form_head">
                                    <img alt="" src="data:image/gif;base64,R0lGODlhAQABAIAAAOZ3fP///yH5BAEAAAEALAAAAAABAAEAAAICTAEAOw=="/>
                                </td>
                            </tr>
                        </table>
                        <br/>
                    </td>
                </tr>

                <?php elseif ($row["type"] === "CHECKBOX"): ?>
                <tr>
                    <td class="editform_table5_td1">
                        <table width="100%" border="0" cellpadding="0" cellspacing="0" class="form_text">
                            <tr>
                                <td width="122"><?= $row["label"] ?></td>
                            </tr>
                        </table>
                    </td>
                    <td valign="top" class="editform_table5_td2">
                        <?php $options = (new Table($table[2], $table[3]))->get_list($this->DBHandle, str_replace("%id", $processed["id"], $table[4]))?>
                        <?php $table[12] = str_replace("%id", $processed["id"], $table[12]) ?>
                        <?php $options2 = (new Table($table[2], $table[3]))->get_list($this->DBHandle, $table[12])?>
                        <?php if (is_array($options2) && count($options2)): ?>
                            <table class="editform_table6" cellspacing="0">
                                <tr>
                                    <td colspan="3" class="editform_table6_td1">
                                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                            <tr>
                                                <td class="editform_table7_td1"><?= $this->FG_TABLE_COMMENT[$i] ?></td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="form_head">
                                        <img alt="" src="data:image/gif;base64,R0lGODlhAQABAIAAAOZ3fP///yH5BAEAAAEALAAAAAABAAEAAAICTAEAOw=="/>
                                    </td>
                                    <td class="editform_table4_td1">
                                        <table width="97%" border="0" align="center" cellspacing="0" cellpadding="0">
                                            <?php foreach ($options2 as $option): ?>
                                                <tr>
                                                    <td class="tableBody">
                                                        <?php $checked = false ?>
                                                        <?php foreach ($options as $o): ?>
                                                            <?php if ($option[1] == $o[1]): ?>
                                                                <?php $checked = true ?>
                                                            <?php endif ?>
                                                        <?php endforeach ?>
                                                        <input
                                                            id="<?= $table[0] ?>_<?= $option[1] ?>"
                                                            type="checkbox"
                                                            name="<?= $table[0] ?>[]"
                                                            value="<?= $option[1] ?>"
                                                            <?php if ($checked): ?>checked<?php endif ?>
                                                        />
                                                    </td>
                                                    <td class="text_azul">
                                                        <?php if (!is_null($table[6]) && $table[6] !== ""): ?>
                                                            <?php if (is_numeric($table[7])): ?>
                                                                <?php $options3 = (new Table($table[8], $table[9]))->get_list($this->DBHandle, str_replace("%1", $option[$table[7]], $table[11]))?>
                                                                <?php $option[$table[7]] = $options3[0][0] ?>
                                                            <?php endif ?>
                                                            <?php $val = $table[6] ?>
                                                            <?php for ($k = 1; $k <= count($option); $k++): ?>
                                                                <?php $val = str_replace("%$k", $option[$k - 1], $val) ?>
                                                            <?php endfor ?>
                                                        <?php else: ?>
                                                            <?php $val = $option[0] ?>
                                                        <?php endif ?>
                                                        <label for="<?= $table[0] ?>_<?= $option[1] ?>"><?= $val ?></label>
                                                    </td>
                                                </tr>
                                            <?php endforeach ?>
                                            <tr>
                                                <td colspan="2" height="4">
                                                    <span class="liens">
                                                        <?php if (isset($this->FG_fit_expression[$i]) && !$this->FG_fit_expression[$i]): ?>
                                                            <br/>
                                                            <?= $row["error"] ?>
                                                        <?php endif ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td class="form_head">
                                        <img alt="" src="data:image/gif;base64,R0lGODlhAQABAIAAAOZ3fP///yH5BAEAAAEALAAAAAABAAEAAAICTAEAOw=="/>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="form_head">
                                        <img alt="" src="data:image/gif;base64,R0lGODlhAQABAIAAAOZ3fP///yH5BAEAAAEALAAAAAABAAEAAAICTAEAOw=="/>
                                    </td>
                                </tr>
                            </table>
                        <?php else: ?>
                            <?= gettext("No data found !!!") ?>
                        <?php endif ?>
                    </td>
                </tr>
                <?php endif /*  end input type selection  */ ?>
            <?php endif /*  end check for colon in custom query  */ ?>
        <?php endforeach ?>
        </tbody>
    </table>

    <table cellspacing="0" class="editform_table8">
        <tr>
            <td width="50%">
                <span class="tableBodyRight"><?= $this->FG_BUTTON_EDITION_BOTTOM_TEXT ?></span>
            </td>
            <td width="50%" align="right" valign="top" class="text">
                <input value="<?= $this->FG_EDIT_PAGE_CONFIRM_BUTTON ?>" class="form_input_button" type="submit"/>
            </td>
        </tr>
    </table>
</form>
