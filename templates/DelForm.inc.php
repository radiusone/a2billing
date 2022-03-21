<?php

use A2billing\Table;

/**
 * @var A2billing\Forms\Formhandler $this
 * @var array $processed
 * @var array $list
 * @var string $form_action
 */

if ($form_action === "ask-delete") {
    if (!$this->isFKDataExists()) {
        $this->FG_FK_DELETE_ALLOWED = false;
        $this->FG_ISCHILDS = false;
        $this->FG_FK_WARNONLY = false;
        $this->FG_FK_DELETE_CONFIRM = false;
    }
}
?>

<script>
function sendto(action, record, field_inst, instance) {
  document.myForm.submit();
}
</script>

<form action="" id="myForm" method="post" name="myForm">
    <input type="hidden" name="id" value="<?= $processed["id"] ?>">
    <input type="hidden" name="atmenu" value="<?= $processed["atmenu"] ?>">
    <input type="hidden" name="current_page" value="<?= $processed['current_page'] ?>">
    <input type="hidden" name="order" value="<?= $processed['order'] ?>">
    <input type="hidden" name="sens" value="<?= $processed['sens'] ?>">
    <?= $this->csrf_inputs() ?>

<?php if ($this->FG_FK_DELETE_CONFIRM && $form_action == "ask-del-confirm" && $this-> FG_FK_DELETE_ALLOWED): ?>

    <input type="hidden" name="form_action" value="delete">

	<table cellspacing="2"  class="tablestyle_001">
        <tr>
            <td>
                <table cellspacing=0 class="delform_table2">
                    <tr>
                        <td align=left class="delform_table2_td1"><?= gettext("Message") ?></td>
                    </tr>
                    <tr >
                        <td class="bgcolor_006">&nbsp;</td>
                    </tr>
                    <tr height="50px">
                        <td align=center class="bgcolor_006">
                            <?= gettext("You have ")?> <?= $processed["fkCount"] ?> <?= gettext(" dependent records.") ?>
                            <br>
                            <?= $this -> FG_FK_DELETE_MESSAGE ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="bgcolor_006">&nbsp;</td>
                    </tr>
                    <tr>
                        <td align=center class="bgcolor_006">
                            <input
                                title="Delete this record"
                                alt="Delete this Record"
                                hspace=2
                                id=submit22
                                name=submit22
                                src="data:image/gif;base64,R0lGODlhXgAUANUAAAAAAP///+JfYPO5uvfR0v33+PjZ2NAAANQXF9gtLds5Od1GRt9SUuJgX+Vzc+WHh+qXl+2lpfGysvG1tfS/v/O/vvTFxfXLy/rf3/ng4Prm5vzs7P3y8v74+P/7+/78/P///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAACAALAAAAABeABQAAAb/wABgSCwaj8ikcslsOgHCh2RKrVqv2Kx2y+12H8OHeEwujyWBtHrNbrvf8Li8DQY8HPi8fu+Qzv+AgYJpdXd8h3kSHx6MjY6PkJGSk5SVH5eXhQ0CAptpnJucogISBaanqKmqq6ytrqgdsbKxBR21HYUMurppu767EhzCw8TFwwfIB8bFysvCzc7RzoUL1dYLadfXERvd3t/g3gfi4d/j5d3n6OvohQrv8App8fERGvf4+fr4B/n998n4+UMGkGDBf/sS6iuUoKFDh2keNoyAAUOGixgzasxwIGNHjhg/fgR5UWTIkxtTVryIgSGCBC9jJkgj8yUEAzhz6tyZ84BOzZ8GkiHDCTSoUKI9j/JcurMQgqdQEdCMChUCgatYs2rFeiBrVwJfvXLdGjbs1rNnnSI48JRtGrZr2yKAcKGu3bt47R7Qy7fu3gt/AfcNTDiv4byFhB54q1goBAuQI0ueHFno5GSVJWOGjKxyZ8qgKScWyrgxsgcVUqtezbq169ewY6+mQLs2bdajTet+MKC379/AgwsfTry48eC5dTd+MKG58+fQo0ufTr269ejJlQuV4qW79+9eCpkZT768+fPo06sHI+SJ+/fw4zMJEAQAOw=="
                                type="image"
                            />
                        </td>
                    </tr>
                    <tr height="5px">
                        <td class="bgcolor_006">&nbsp;</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

<?php else: ?>

    <table cellspacing="3" class="delform_table3">
        <tr>
            <td class="delform_table3_td1" valign="top">
                <span class="textnegrita"><?= $this->FG_INTRO_TEXT_ASK_DELETION ?></span>
            </td>
        </tr>
    </table>

    <input type="hidden" name="fkCount" value="<?= $this -> FG_FK_RECORDS_COUNT ?>">
    <?php if ($this->FG_FK_DELETE_CONFIRM && $this-> FG_FK_DELETE_ALLOWED && $this -> FG_ISCHILDS): ?>
	<input type="hidden" name="form_action" value="ask-del-confirm">
    <?php else: ?>
    <input type="hidden" name="form_action" value="delete">
    <?php endif ?>

    <?php foreach ($this->FG_EDIT_QUERY_HIDDEN_INPUTS as $name => $value): ?>
        <input type="hidden" name="<?= htmlspecialchars($name) ?>" value="<?= htmlspecialchars($value) ?>"/>
    <?php endforeach ?>

	<table cellspacing="2" class="tablestyle_001">
		<?php foreach($this->FG_EDIT_FORM_ELEMENTS as $i=> $row): ?>
		<tr>
			<td width="25%" valign="middle" class="form_head">
				<label for="<?= $row["name"] ?>"><?= $row["label"] ?></label>
			</td>
			<td valign="top" class="tablestyle_001">
				<?php if ($this->FG_DEBUG == 1): ?>
				    <?= $row[3] ?>
                <?php endif ?>

                <?php if ($row["type"] === "INPUT" || str_starts_with($row["type"], "POPUP")): ?>
					<input
					    id="<?= $row["name"] ?>"
					    class="form_enter"
					    readonly
					    name=<?= $row["name"] ?>
					    <?= $row["attributes"] ?>
					    value="<?= $list[0][$i] ?>"
                    />

				<?php elseif ($row["type"] === "TEXTAREA"): ?>
					<textarea
					    id="<?= $row["name"] ?>"
					    class="form_input_textarea"
					    readonly
					    name="<?= $row["name"] ?>"
					    <?= $row["attributes"]?>
                    >
                        <?= $list[0][$i] ?>
                    </textarea>

				<?php elseif ($row["type"] === "SELECT"): ?>
                    <?php if ($row["select_type"] === "SQL"): ?>
                        <?php $options = (new Table($row["sql_table"], $row["sql_field"]))->get_list($this->DBHandle, $row["sql_clause"])?>
                    <?php elseif ($row["select_type"] === "LIST"): ?>
                        <?php $options = $row["select_fields"] ?>
                    <?php endif ?>
                    <?php if ($this->FG_DEBUG >= 2): ?>
                        <br/><?php print_r($options)?><br/><?php print_r($list)?><br/>#<?= $i ?>::><?= $this->VALID_SQL_REG_EXP ?><br/><br/>::><?= $list[0][$i] ?><br/><br/>::><?= $row["name"] ?>
                    <?php endif ?>
					<select class="form_input_select" disabled name="<?= $row["name"] ?>" id="<?= $row["name"] ?>">
                    <?php if (is_array($options) && count($options)): ?>
                        <?php foreach ($options as $option): ?>
                            <option
                                value="<?= $option[1] ?>"
                                <?php if ($list[0][$i] === $option[1]): ?>
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
						<?= gettext("No data found !!!") ?>
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
		  	</td>
		</tr>
		<?php endforeach ?>
	</table>

	<table cellspacing="0" class="delform_table5">
		<tr height="2">
			<td colspan="2" style="border-bottom: medium dotted rgb(255, 119, 102);">&nbsp; </td>
		</tr>
		<tr>
		    <td width="50%" class="text_azul">
		        <span class="tableBodyRight"><?= $this->FG_BUTTON_DELETION_BOTTOM_TEXT ?></span>
            </td>
		    <td width="50%" align="right" class="text">
				<a href="#" onclick="sendto('delete');" class="cssbutton_big" title="<?= gettext("Remove this ");?> <?= $this->FG_INSTANCE_NAME; ?>">
				    <img alt="" src="data:image/gif;base64,R0lGODlhDwAPAMQYAP+yPf+fEv+qLP+3Tf+pKv++Xf/Gcv+mJP+tNf+tMf+kH/+/YP+oJv+wO/+jHP/Ohf/WmP+vOv/cpv+kHf+iGf+jG/////Hw7P///wAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAABgALAAAAAAPAA8AAAVjIHaNZEmKF6auLJpiEvQYxQAgiTpiMm0Tk4pigsLMag2Co8KkFA0Lm8XCbBajDcFkWnXuBlkFk1vxpgACcYVcLqbHVKaDuFNXqwxGkUK5VyYMEQhFGAGGhxQHOS4tjTsmkDshADs="/>
				    <?= _("Delete") ?>
				</a>
			</td>
		</tr>
	</table>
</form>
<?php endif ?>
