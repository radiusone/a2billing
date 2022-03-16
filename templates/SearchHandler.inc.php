<?php
/**
 * @var A2billing\Forms\Formhandler $this
 * @var array $processed
 * @var array $list
 */
function create_date_options($target)
{
    $month_list = [
        "", gettext("January"), gettext("February"), gettext("March"), gettext("April"), gettext("May"),
        gettext("June"), gettext("July"), gettext("August"), gettext("September"), gettext("October"),
        gettext("November"), gettext("December")
    ];
    $this_year = date("Y");
    $this_month = date("n");
    $month_year_opts = "";
    for ($i = $this_year; $i >= $this_year - 10; $i--) {
        for ($j = ($i == $this_year ? $this_month : 12); $j > 0; $j--) {
            $val = sprintf("%d-%02d", $i, $j);
            $selected = $target == $val ? 'selected="selected"' : "";
            $display = $month_list[$j] . " " . $i;
            $month_year_opts .= "<option value=\"$val\" $selected>$display</option>";
        }
    }
    return $month_year_opts;
}
?>
<div class="row">
    <div class="col">
        <strong><?php echo $this->FG_FILTER_SEARCH_TOP_TEXT?></strong>
    </div>
</div>

<form method="post" name="searchForm" id="searchForm" class="container-fluid form-striped" action="<?= "?s=$processed[s]&t=$processed[t]&order=$processed[order]&sens=$processed[sens]&current_page=$processed[current_page]" ?>">
    <input type="hidden" name="posted_search" value="1"/>
    <input type="hidden" name="current_page" value="0"/>
    <?= $this->csrf_inputs() ?>

<?php if ($this -> FG_FILTER_SEARCH_1_TIME): ?>
    <div class="row pb-1">
        <label class="col-4 col-form-label col-form-label-sm">
            <?= $this->FG_FILTER_SEARCH_1_TIME_TEXT ?>
        </label>
        <div class="col-4">
            <div class="form-check form-check-inline">
                <input type="checkbox" name="fromday" value="true" aria-label="Enable this date search field" <?php if ($processed["fromday"]): ?>checked="checked"<?php endif ?> class="form-check-input"/>
                <label class="form-check-label" for="fromstatsday_sday"><?= gettext("From :") ?></label>
            </div>
            <!-- will need to get backend fixed up to use a reasonable date input
            <input type="date" name="fromstats_sday" id="fromstats_sday" value="<?= $processed["fromstats_sday"] ?? (new DateTime())->format("Y-m-d") ?>"/>
            -->
            <select name="fromstatsday_sday" id="fromstatsday_sday" class="form-select form-select-sm">
                <?php for ($i = 1; $i <= 31; $i++): ?>
                <option <?php if ($processed['fromstatsday_sday'] == sprintf("%02d", $i)):?>selected="selected"<?php endif ?>>
                    <?= sprintf("%02d", $i) ?>
                </option>
                <?php endfor ?>
            </select>
            <select name="fromstatsmonth_sday" id="fromstatsmonth_sday" class="form-select form-select-sm">
                <?= create_date_options($processed["fromstatsmonth_sday"]) ?>
            </select>
        </div>
        <div class="col-4">
            <div class="form-check form-check-inline">
                <input type="checkbox" name="today" value="true" aria-label="Enable this date search field" <?php if ($processed["today"]): ?>checked="checked"<?php endif ?> class="form-check-input"/>
                <label class="form-check-label" for="tostatsday_sday"><?= gettext("To :") ?></label>
            </div>
            <!-- will need to get backend fixed up to use a reasonable date input
            <input type="date" name="tostats_sday" id="tostats_sday" value="<?= $processed["tostats_sday"] ?? (new DateTime())->format("Y-m-d") ?>"/>
            -->
            <select name="tostatsday_sday" id="tostatsday_sday" class="form-select form-select-sm">
                <?php for ($i = 1; $i <= 31; $i++): ?>
                    <option <?php if ($processed['tostatsday_sday'] == sprintf("%02d", $i)):?>selected="selected"<?php endif ?>>
                        <?= sprintf("%02d", $i) ?>
                    </option>
                <?php endfor ?>
            </select>
            <select name="tostatsmonth_sday" id="tostatsmonth_sday" class="form-select form-select-sm">
                <?= create_date_options($processed["tostatsmonth_sday"]) ?>
            </select>
        </div>
    </div>
<?php endif ?>

<?php if ($this->FG_FILTER_SEARCH_1_TIME_BIS): ?>
    <div class="row pb-1">
        <label class="col-4 col-form-label col-form-label-sm">
            <?= $this->FG_FILTER_SEARCH_1_TIME_TEXT_BIS ?>
        </label>
        <div class="col-4">
            <input type="checkbox" name="fromday_bis" value="true" aria-label="Enable this date search field" <?php if ($processed["fromday_bis"]): ?>checked="checked"<?php endif ?> class="form-check-input"/>
            <label for="fromstatsday_sday_bis"><?= gettext("From :") ?></label>
            <!-- will need to get backend fixed up to use a reasonable date input
            <input type="date" name="fromstats_sday_bis" id="fromstats_sday_bis" value="<?= $processed["fromstats_sday_bis"] ?? (new DateTime())->format("Y-m-d") ?>"/>
            -->
            <select name="fromstatsday_sday_bis" id="fromstatsday_sday_bis" class="form-select form-select-sm">
                <?php for ($i = 1; $i <= 31; $i++): ?>
                    <option <?php if ($processed['fromstatsday_sday_bis'] == sprintf("%02d", $i)):?>selected="selected"<?php endif ?>>
                        <?= sprintf("%02d", $i) ?>
                    </option>
                <?php endfor ?>
            </select>
            <select name="fromstatsmonth_sday_bis" id="fromstatsmonth_sday_bis" class="form-select form-select-sm">
                <?= create_date_options($processed["fromstatsmonth_sday_bis"]) ?>
            </select>
        </div>
        <div class="col-4">
            <input type="checkbox" name="today_bis" value="true" aria-label="Enable this date search field" <?php if ($processed["today_bis"]): ?>checked="checked"<?php endif ?> class="form-check-input"/>
            <label for="tostatsday_sday_bis"><?= gettext("To :") ?></label>
            <!-- will need to get backend fixed up to use a reasonable date input
            <input type="date" name="tostats_sday_bis" id="tostats_sday_bis" value="<?= $processed["tostats_sday_bis"] ?? (new DateTime())->format("Y-m-d") ?>"/>
            -->
            <select name="tostatsday_sday_bis" id="tostatsday_sday_bis" class="form-select form-select-sm">
                <?php for ($i = 1; $i <= 31; $i++): ?>
                    <option <?php if ($processed['tostatsday_sday_bis'] == sprintf("%02d", $i)):?>selected="selected"<?php endif ?>>
                        <?= sprintf("%02d", $i) ?>
                    </option>
                <?php endfor ?>
            </select>
            <select name="tostatsmonth_sday_bis" id="tostatsmonth_sday_bis" class="form-select form-select-sm">
                <?= create_date_options($processed["tostatsmonth_sday_bis"]) ?>
            </select>
        </div>
    </div>
<?php endif ?>

<?php if ($this->FG_FILTER_SEARCH_3_TIME): // this is only used by A2B_data_archiving.php ?>
    <div class="row pb-1">
        <label class="col-4 col-form-label col-form-label-sm" for="month_earlier">
            <?php echo $this-> FG_FILTER_SEARCH_3_TIME_TEXT?>
        </label>
        <div class="col">
            <select name="month_earlier" id="month_earlier" class="form-select form-select-sm">
                <?php for ($i=3 ; $i<=12 ; $i++): ?>
                <option <?php if ($processed['month_earlier'] == $i): ?>selected="selected"<?php endif ?>><?= $i ?> Months</option>
                <?php endfor ?>
            </select>
        </div>
    </div>
<?php endif ?>

<?php foreach ($this->FG_FILTER_SEARCH_FORM_POPUP as $item): ?>
    <div class="row pb-1">
        <label class="col-4 col-form-label col-form-label-sm" for="<?= $item["name"] ?>">
            <?= $item["label"] ?>
        </label>
        <div class="col-8">
            <div class="input-group">
                <input
                    name="<?= str_replace(".", "^^", $item["name"]) ?>"
                    id="<?= $item["name"] ?>"
                    value="<?= $processed[$item["name"]] ?>"
                    class="form-control form-control-sm"
                />
                <a
                    href="<?= $item["href"] ?>"
                    data-field-name="<?= $item["fieldname"] ?? str_replace(".", "^^", $item["name"]) ?>"
                    data-window-name="<?= $item["windowname"] ?? "popup" ?>"
                    data-popup-options="<?= $item["windowoptions"] ?? "width=550,height=330,top=20,left=100,scrollbars=1" ?>"
                    data-select="<?= $item["select"] ?? 1 ?>"
                    class="badge bg-primary popup_trigger"
                    aria-label="open a popup to select an item"
                >&gt;</a>
            </div>
        </div>
    </div>
<?php endforeach ?>

<?php foreach ($this->FG_FILTER_SEARCH_FORM_1C as $item): ?>
    <div class="row pb-1">
        <label class="col-4 col-form-label col-form-label-sm" for="<?= $item[1] ?>">
            <?= $item[0] ?>
        </label>
        <div class="col-4">
            <input name="<?= str_replace(".", "^^", $item[1]) ?>" id="<?= $item[1] ?>" value="<?= $processed[$item[1]] ?? "" ?>" class="form-control form-control-sm"/>
        </div>
        <div class="col-4">
            <select name="<?= str_replace(".", "^^", $item[2]) ?>" id="<?= $item[2] ?>" class="form-select form-select-sm">
                <option value="1" <?php if (($processed[$item[2]] ?? 1) == 1): ?>selected="selected"<?php endif ?>><?= _("Exact") ?></option>
                <option value="2" <?php if (($processed[$item[2]] ?? 1) == 2): ?>selected="selected"<?php endif ?>><?= _("Begins with") ?></option>
                <option value="3" <?php if (($processed[$item[2]] ?? 1) == 3): ?>selected="selected"<?php endif ?>><?= _("Contains") ?></option>
                <option value="4" <?php if (($processed[$item[2]] ?? 1) == 4): ?>selected="selected"<?php endif ?>><?= _("Ends with") ?></option>
            </select>
        </div>
    </div>
<?php endforeach ?>

<?php foreach ($this->FG_FILTER_SEARCH_FORM_2C as $item): ?>
    <div class="row pb-1">
        <label class="col-4 col-form-label col-form-label-sm" for="<?= $item[1] ?>">
            <?= $item[0] ?>
        </label>
        <div class="col">
            <div class="row">
                <div class="col-2">
                    <select name="<?= str_replace(".", "^^", $item[2]) ?>" class="form-select form-select-sm">
                        <option value="4" <?php if (($processed[$item[2]] ?? 1) == 4): ?> selected="selected"<?php endif ?>>&gt;</option>
                        <option value="5" <?php if (($processed[$item[2]] ?? 1) == 5): ?> selected="selected"<?php endif ?>>&gt;=</option>
                        <option value="1" <?php if (($processed[$item[2]] ?? 1) == 1): ?> selected="selected"<?php endif ?>>=</option>
                        <option value="2" <?php if (($processed[$item[2]] ?? 1) == 2): ?> selected="selected"<?php endif ?>>&lt;=</option>
                        <option value="3" <?php if (($processed[$item[2]] ?? 1) == 3): ?> selected="selected"<?php endif ?>>&lt;</option>
                    </select>
                </div>
                <div class="col-3">
                    <input type="text" name="<?= $item[1] ?>" id="<?= $item[1] ?>" value="<?= $processed[$item[1]] ?? "" ?>" class="form-control form-control-sm"/>
                </div>
                <div class="col-1">
                    <?= gettext("AND") ?>
                </div>
                <div class="col-2">
                    <select name="<?= str_replace(".", "^^", $item[4]) ?>" class="form-select form-select-sm">
                        <option></option>
                        <option value="4" <?php if (($processed[$item[4]] ?? 1) == 4): ?> selected="selected"<?php endif ?>>&gt;</option>
                        <option value="5" <?php if (($processed[$item[4]] ?? 1) == 5): ?> selected="selected"<?php endif ?>>&gt;=</option>
                        <option value="2" <?php if (($processed[$item[4]] ?? 1) == 2): ?> selected="selected"<?php endif ?>>&lt;=</option>
                        <option value="3" <?php if (($processed[$item[4]] ?? 1) == 3): ?> selected="selected"<?php endif ?>>&lt;</option>
                    </select>
                </div>
                <div class="col-3">
                    <input type="text" name="<?= str_replace(".", "^^", $item[3]) ?>" id="<?= $item[3] ?>" value="<?= $processed[$item[3]] ?? "" ?>" class="form-control form-control-sm"/>
                </div>
            </div>
        </div>
    </div>
<?php endforeach ?>

<?php if (is_array($this->FG_FILTER_SEARCH_FORM_SELECT) && count($this->FG_FILTER_SEARCH_FORM_SELECT)): ?>
    <div class="row pb-1">
        <?php foreach (array_chunk($this->FG_FILTER_SEARCH_FORM_SELECT, 4) as $chunk): ?>
            <?php foreach ($chunk as $i => $item): ?>
            <div class="col">
                <select name="<?= str_replace(".", "^^", $item[2]) ?>" aria-label="<?= $item[0] ?>" class="form-select form-select-sm">
                    <option value=""><?= $item[0] ?></option>
                    <?php foreach ($item[1] as $opt): ?>
                    <option value="<?= $opt[0] ?>" <?php if (strcmp($processed[$item[2]] ?? "zzzzzz", $opt[0]) === 0): ?>selected="selected"<?php endif ?>>
                        <?= $opt[1] ?>
                    </option>
                    <?php endforeach ?>
                </select>
            </div>
            <?php endforeach ?>
        <?php endforeach ?>
    </div>
<?php endif ?>

    <div class="row justify-content-end border-top pt-3 mt-3 bg-transparent">
        <div class="col text-end">
            <?php if (strlen($_SESSION[$this->FG_FILTER_SEARCH_SESSION_NAME] ?? "") > 10): ?>
                <?php if ($this->FG_FILTER_SEARCH_DELETE_ALL): ?>
                    <a class="btn btn-danger" href="?deleteselected=true" onclick="return confirm('<?= "Are you sure to delete " . $this->FG_NB_RECORD . " selected records?" ?>')"><?= _("Delete") ?></a>
                <?php endif ?>
            <a class="btn btn-secondary" href="?cancelsearch=true"><?= _("Cancel") ?></a>
            <?php endif ?>
            <button type="submit" class="btn btn-primary"><?= _("Search") ?></button>
        </div>
    </div>

</form>
