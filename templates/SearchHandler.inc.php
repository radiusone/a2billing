<?php
/**
 * @var A2billing\Forms\Formhandler $this
 * @var array $processed
 * @var array $list
 * @var bool $full_modal
 * @var bool $with_hide_button
 */
$action = http_build_query([
    "s" => $processed["s"],
    "t" => $processed["t"],
    "order" => $processed["order"],
    "sens" => $processed["sens"],
    "current_page" => $processed["current_page"],
]);
?>
<?php if ($full_modal && $with_hide_button): ?>
<div class="row pb-3 justify-content-center">
    <div class="col-auto">
        <button
                class="btn btn-sm <?= empty($_SESSION[$HD_Form->search_session_key]) ? "btn-outline-primary" : "btn-primary" ?>"
                data-bs-toggle="modal"
                data-bs-target="#searchModal"
                title="<?= sprintf(_("Search %s"), $this->FG_INSTANCE_NAME) ?> <?= empty($_SESSION[$HD_Form->search_session_key]) ? "" : "(" . _("search activated") . ")" ?>"
        >
            <?= sprintf(_("Search %s"), $this->FG_INSTANCE_NAME) ?>
        </button>
    </div>
</div>
<?php endif ?>
<form method="post" name="searchForm" id="searchForm" class="container-fluid form-striped" action="?<?= $action ?>">
    <input type="hidden" name="posted_search" value="1"/>
    <input type="hidden" name="current_page" value="0"/>
    <?= $this->csrf_inputs() ?>

<?php if ($full_modal): ?>
    <div class="modal" id="searchModal" aria-labelledby="modal-title-search" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-title-search"><?= $this->search_form_title ?? sprintf(_("Search %s"), $this->FG_INSTANCE_NAME) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
<?php else: ?>

    <div class="row">
        <div class="col">
            <strong><?php echo $this->search_form_title?></strong>
        </div>
    </div>
<?php endif ?>

<?php if ($this -> search_date_enabled): ?>
    <div class="row pb-1">
        <label class="col-4 col-form-label col-form-label-sm">
            <?= $this->search_date_text ?>
        </label>
        <div class="col-8">
            <div class="row pb-1">
                <div class="col-3">
                    <div class="form-check form-check-inline">
                        <input type="checkbox" name="fromday" id="search_fromday" value="true" aria-label="<?= _("enable the search start date")?>" <?php if ($processed["fromday"]): ?>checked="checked"<?php endif ?> class="form-check-input"/>
                        <label class="form-check-label" for="fromstatsday_sday"><?= gettext("From :") ?></label>
                    </div>
                </div>
                <div class="col-4">
                    <!-- will need to get backend fixed up to use a reasonable date input
                    <input type="date" name="fromstats_sday" id="fromstats_sday" value="<?= $processed["fromstats_sday"] ?? (new DateTime())->format("Y-m-d") ?>" aria-label="<?= _("search start date") ?>"/>
                    -->
                    <select name="fromstatsday_sday" id="fromstatsday_sday" class="form-select form-select-sm" aria-label="<?= _("day component of search start date") ?>">
                        <?php for ($i = 1; $i <= 31; $i++): ?>
                            <option <?php if ($processed['fromstatsday_sday'] === sprintf("%02d", $i)):?>selected="selected"<?php endif ?>>
                                <?= sprintf("%02d", $i) ?>
                            </option>
                        <?php endfor ?>
                    </select>
                </div>
                <div class="col-5">
                    <select name="fromstatsmonth_sday" id="fromstatsmonth_sday" class="form-select form-select-sm" aria-label="<?= _("month and year component of search start date") ?>">
                        <?= $this->create_date_options($processed["fromstatsmonth_sday"]) ?>
                    </select>
                </div>
            </div>
            <div class="row pb-1">
                <div class="col-3">
                    <div class="form-check form-check-inline">
                        <input type="checkbox" name="today" id="search_today" value="true" aria-label="<?= _("enable the search end date") ?>" <?php if ($processed["today"]): ?>checked="checked"<?php endif ?> class="form-check-input"/>
                        <label class="form-check-label" for="tostatsday_sday"><?= gettext("To :") ?></label>
                    </div>
                </div>
                <div class="col-4">
                    <!-- will need to get backend fixed up to use a reasonable date input
                    <input type="date" name="tostats_sday" id="tostats_sday" value="<?= $processed["tostats_sday"] ?? (new DateTime())->format("Y-m-d") ?>" aria-label="<?= _("search end date") ?>"/>
                    -->
                    <select name="tostatsday_sday" id="tostatsday_sday" class="form-select form-select-sm" aria-label="<?= _("day component of search end date") ?>">
                        <?php for ($i = 1; $i <= 31; $i++): ?>
                            <option <?php if ($processed['tostatsday_sday'] == sprintf("%02d", $i)):?>selected="selected"<?php endif ?>>
                                <?= sprintf("%02d", $i) ?>
                            </option>
                        <?php endfor ?>
                    </select>
                </div>
                <div class="col-5">
                    <select name="tostatsmonth_sday" id="tostatsmonth_sday" class="form-select form-select-sm" aria-label="<?= _("month and year component of search end date") ?>">
                        <?= $this->create_date_options($processed["tostatsmonth_sday"]) ?>
                    </select>
                </div>
            </div>
        </div>
    </div>
<?php endif ?>

<?php if ($this->search_date2_enabled): ?>
    <div class="row pb-1">
        <label class="col-4 col-form-label col-form-label-sm">
            <?= $this->search_date2_text ?>
        </label>
        <div class="col-8">
            <div class="row pb-1">
                <div class="col-3">
                    <input type="checkbox" name="fromday_bis" id="search_fromday_bis" value="true" aria-label="<?= _("enable the search start date")?>" <?php if ($processed["fromday_bis"]): ?>checked="checked"<?php endif ?> class="form-check-input"/>
                    <label for="fromstatsday_sday_bis"><?= gettext("From :") ?></label>
                </div>
                <div class="col-4">
                    <!-- will need to get backend fixed up to use a reasonable date input
                    <input type="date" name="fromstats_sday_bis" id="fromstats_sday_bis" value="<?= $processed["fromstats_sday_bis"] ?? (new DateTime())->format("Y-m-d") ?>" aria-label="<?= _("search start date") ?>"/>
                    -->
                    <select name="fromstatsday_sday_bis" id="fromstatsday_sday_bis" class="form-select form-select-sm" aria-label="<?= _("day component of search start date") ?>">
                        <?php for ($i = 1; $i <= 31; $i++): ?>
                            <option <?php if ($processed['fromstatsday_sday_bis'] == sprintf("%02d", $i)):?>selected="selected"<?php endif ?>>
                                <?= sprintf("%02d", $i) ?>
                            </option>
                        <?php endfor ?>
                    </select>
                </div>
                <div class="col-5">
                    <select name="fromstatsmonth_sday_bis" id="fromstatsmonth_sday_bis" class="form-select form-select-sm" aria-label="<?= _("month and year component of search start date") ?>">
                        <?= $this->create_date_options($processed["fromstatsmonth_sday_bis"]) ?>
                    </select>
                </div>
            </div>
            <div class="row pb-1">
                <div class="col-3">
                    <input type="checkbox" name="today_bis" id="search_today_bis" value="true" aria-label="<?= _("enable the search end date") ?>" <?php if ($processed["today_bis"]): ?>checked="checked"<?php endif ?> class="form-check-input"/>
                    <label for="tostatsday_sday_bis"><?= gettext("To :") ?></label>
                </div>
                <div class="col-4">
                    <!-- will need to get backend fixed up to use a reasonable date input
                    <input type="date" name="tostats_sday_bis" id="tostats_sday_bis" value="<?= $processed["tostats_sday_bis"] ?? (new DateTime())->format("Y-m-d") ?>" aria-label="<?= _("search end date") ?>"/>
                    -->
                    <select name="tostatsday_sday_bis" id="tostatsday_sday_bis" class="form-select form-select-sm" aria-label="<?= _("day component of search end date") ?>">
                        <?php for ($i = 1; $i <= 31; $i++): ?>
                            <option <?php if ($processed['tostatsday_sday_bis'] == sprintf("%02d", $i)):?>selected="selected"<?php endif ?>>
                                <?= sprintf("%02d", $i) ?>
                            </option>
                        <?php endfor ?>
                    </select>
                </div>
                <div class="col-5">
                    <select name="tostatsmonth_sday_bis" id="tostatsmonth_sday_bis" class="form-select form-select-sm" aria-label="<?= _("month and year component of search end date") ?>">
                        <?= $this->create_date_options($processed["tostatsmonth_sday_bis"]) ?>
                    </select>
                </div>
            </div>
        </div>
    </div>
<?php endif ?>

<?php if ($this->search_months_ago_enabled): // this is only used by A2B_data_archiving.php ?>
    <div class="row pb-1">
        <label class="col-4 col-form-label col-form-label-sm" for="month_earlier">
            <?php echo $this-> search_months_ago_text?>
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

<?php $inputs = array_filter($this->SEARCH_FORM_ELEMENTS, fn ($v) => $v["type"] !== "SELECT") ?>
<?php foreach ($inputs as $item): ?>
    <div class="row pb-1">
        <label class="col-4 col-form-label col-form-label-sm" for="<?= $item["input"] ?>">
            <?= $item["label"] ?>
        </label>
    <?php if ($item["type"] === "POPUP"): ?>
        <div class="col-8">
            <div class="input-group">
                <input
                    name="<?= $item["input"] ?>"
                    id="<?= $item["input"] ?>"
                    value="<?= $processed[$item["input"]] ?>"
                    class="form-control form-control-sm"
                />
                <a
                    href="<?= $item["href"] ?>"
                    data-field-name="<?= str_replace(".", "^^", $item["input"]) ?>"
                    data-window-name="<?= $item["input"] ?>_popup"
                    data-popup-options="width=550,height=330,top=20,left=100,scrollbars=1"
                    data-select="<?= $item["select"] ?>"
                    class="badge bg-primary popup_trigger"
                    aria-label="open a popup to select an item"
                >
                    <svg class="mx-auto" width="16" height="16"><use xlink:href="#calendar"></use></svg>
                </a>
            </div>
        </div>

    <?php elseif ($item["type"] === "TEXT"): ?>
        <div class="col-4">
            <input name="<?= $item["input"][0] ?>" id="<?= $item["input"][0] ?>" value="<?= $processed[$item["input"][0]] ?? "" ?>" class="form-control form-control-sm"/>
        </div>
        <div class="col-4">
            <select name="<?= $item["operator"][0] ?>" id="<?= $item["operator"][0] ?>" class="form-select form-select-sm" aria-label="<?= _("select a search type for the previous input") ?>">
                <option value="1" <?php if (($processed[$item["operator"][0]] ?? 3) == 1): ?>selected="selected"<?php endif ?>><?= _("Exact") ?></option>
                <option value="2" <?php if (($processed[$item["operator"][0]] ?? 3) == 2): ?>selected="selected"<?php endif ?>><?= _("Begins with") ?></option>
                <option value="3" <?php if (($processed[$item["operator"][0]] ?? 3) == 3): ?>selected="selected"<?php endif ?>><?= _("Contains") ?></option>
                <option value="4" <?php if (($processed[$item["operator"][0]] ?? 3) == 4): ?>selected="selected"<?php endif ?>><?= _("Ends with") ?></option>
            </select>
        </div>

    <?php elseif ($item["type"] === "COMPARISON"): ?>
        <div class="col">
            <div class="row">
                <div class="col-2">
                    <select name="<?= $item["input"][0] ?>" class="form-select form-select-sm" aria-label="select an operator to apply to the next input">
                        <option value="4" <?php if (($processed[$item["operator"][0]] ?? 1) == 4): ?> selected="selected"<?php endif ?> aria-label="greater than">&gt;</option>
                        <option value="5" <?php if (($processed[$item["operator"][0]] ?? 1) == 5): ?> selected="selected"<?php endif ?> aria-label="greater than or equal to">&gt;=</option>
                        <option value="1" <?php if (($processed[$item["operator"][0]] ?? 1) == 1): ?> selected="selected"<?php endif ?> aria-label="equal to">=</option>
                        <option value="2" <?php if (($processed[$item["operator"][0]] ?? 1) == 2): ?> selected="selected"<?php endif ?> aria-label="less than or equal to">&lt;=</option>
                        <option value="3" <?php if (($processed[$item["operator"][0]] ?? 1) == 3): ?> selected="selected"<?php endif ?> aria-label="less than">&lt;</option>
                    </select>
                </div>
                <div class="col-3">
                    <input type="text" name="<?= $item["input"][0] ?>" id="<?= $item["input"][0] ?>" value="<?= $processed[$item["input"][0]] ?? "" ?>" class="form-control form-control-sm"/>
                </div>
                <div class="col-1">
                    <?= gettext("AND") ?>
                </div>
                <div class="col-2">
                    <select name="<?= $item["input"][1] ?>" class="form-select form-select-sm" aria-label="select an operator to apply to the next input">
                        <option></option>
                        <option value="4" <?php if (($processed[$item["operator"][1]] ?? 1) == 4): ?> selected="selected"<?php endif ?> aria-label="greater than">&gt;</option>
                        <option value="5" <?php if (($processed[$item["operator"][1]] ?? 1) == 5): ?> selected="selected"<?php endif ?> aria-label="greater than or equal to">&gt;=</option>
                        <option value="2" <?php if (($processed[$item["operator"][1]] ?? 1) == 2): ?> selected="selected"<?php endif ?> aria-label="less than or equal to">&lt;=</option>
                        <option value="3" <?php if (($processed[$item["operator"][1]] ?? 1) == 3): ?> selected="selected"<?php endif ?> aria-label="less than">&lt;</option>
                    </select>
                </div>
                <div class="col-3">
                    <input type="text" name="<?= $item["input"][1] ?>" id="<?= $item["input"][1] ?>" value="<?= $processed[$item["input"][1]] ?? "" ?>" class="form-control form-control-sm" aria-label="<?= $item["label"] ?>"/>
                </div>
            </div>
        </div>

    <?php endif ?>
    </div>
<?php endforeach ?>

<?php $selects = array_filter($this->SEARCH_FORM_ELEMENTS, fn ($v) => $v["type"] === "SELECT") ?>
<?php foreach (array_chunk($selects, 3) as $chunk): ?>
    <div class="row pb-1">
    <?php foreach ($chunk as $item): ?>
        <div class="col-4">
            <select name="<?= $item["input"][0] ?>" aria-label="<?= $item["label"] ?>" class="form-select form-select-sm">
                <option value=""><?= $item["label"] ?></option>
                <?php foreach ($item["options"] as $opt): ?>
                    <option value="<?= $opt[0] ?>" <?php if (strcmp($processed[$item["input"][0]] ?? "zzzzzz", $opt[0]) === 0): ?>selected="selected"<?php endif ?>>
                        <?= $opt[1] ?>
                    </option>
                <?php endforeach ?>
            </select>
        </div>
    <?php endforeach ?>
    </div>
<?php endforeach ?>

<?php if ($full_modal): ?>
                </div> <!-- .modal-body -->
                <div class="modal-footer">
<?php else: ?>
    <div class="row justify-content-end border-top pt-3 mt-3 bg-transparent">
        <div class="col text-end">
<?php endif ?>
            <?php if (strlen($_SESSION[$this->search_session_key] ?? "") > 10): ?>
                <?php if ($this->search_delete_enabled): ?>
                    <a class="btn btn-danger" href="?deleteselected=true" onclick="return confirm('<?= "Are you sure to delete " . $this->FG_LIST_VIEW_ROW_COUNT . " selected records?" ?>')"><?= _("Delete") ?></a>
                <?php endif ?>
            <a class="btn btn-secondary" href="?cancelsearch=true"><?= _("Cancel") ?></a>
            <?php endif ?>
            <button type="submit" class="btn btn-primary"><?= _("Search") ?></button>
<?php if ($full_modal): ?>
                </div><!-- .modal-footer -->
            </div><!-- .modal-content -->
        </div><!-- .modal-dialog -->
    </div><!-- .modal -->
<?php else: ?>
        </div><!-- .col -->
    </div><!-- .row -->
<?php endif ?>
</form>
