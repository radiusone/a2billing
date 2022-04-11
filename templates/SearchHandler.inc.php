<?php
/**
 * @var A2billing\Forms\FormHandler $this
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
        <?= $this->create_search_button() ?>
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
                        <input type="checkbox" name="enable_search_start_date" id="enable_search_start_date" value="true" aria-label="<?= _("enable the search start date")?>" <?php if ($processed["enable_search_start_date"]): ?>checked="checked"<?php endif ?> class="form-check-input"/>
                        <label class="form-check-label" for="enable_search_start_date"><?= gettext("From :") ?></label>
                    </div>
                </div>
                <div class="col-4">
                    <input type="date" name="search_start_date" id="search_start_date" value="<?= $processed["search_start_date"] ?? (new DateTime())->format("Y-m-d") ?>" aria-label="<?= _("search start date") ?>"/>
                </div>
            </div>
            <div class="row pb-1">
                <div class="col-3">
                    <div class="form-check form-check-inline">
                        <input type="checkbox" name="enable_search_end_date" id="enable_search_end_date" value="true" aria-label="<?= _("enable the search end date") ?>" <?php if ($processed["enable_search_end_date"]): ?>checked="checked"<?php endif ?> class="form-check-input"/>
                        <label class="form-check-label" for="enable_search_end_date"><?= gettext("To :") ?></label>
                    </div>
                </div>
                <div class="col-4">
                    <input type="date" name="search_end_date" id="search_end_date" value="<?= $processed["search_end_date"] ?? (new DateTime('+1 month'))->format("Y-m-d") ?>" aria-label="<?= _("search end date") ?>"/>
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
                    <input type="checkbox" name="enable_search_start_date2" id="enable_search_start_date2" value="true" aria-label="<?= _("enable the search start date")?>" <?php if ($processed["enable_search_start_date2"]): ?>checked="checked"<?php endif ?> class="form-check-input"/>
                    <label for="enable_search_start_date2"><?= gettext("From :") ?></label>
                </div>
                <div class="col-4">
                    <input type="date" name="search_start_date2" id="search_start_date2" value="<?= $processed["search_start_date2"] ?? (new DateTime())->format("Y-m-d") ?>" aria-label="<?= _("search start date") ?>"/>
                </div>
            </div>
            <div class="row pb-1">
                <div class="col-3">
                    <input type="checkbox" name="enable_search_end_date2" id="enable_search_end_date2" value="true" aria-label="<?= _("enable the search end date") ?>" <?php if ($processed["enable_search_end_date2"]): ?>checked="checked"<?php endif ?> class="form-check-input"/>
                    <label for="tostatsday_sday_bis"><?= gettext("To :") ?></label>
                </div>
                <div class="col-4">
                    <input type="date" name="search_end_date2" id="search_end_date2" value="<?= $processed["search_end_date2"] ?? (new DateTime('+1 month'))->format("Y-m-d") ?>" aria-label="<?= _("search end date") ?>"/>
                </div>
            </div>
        </div>
    </div>
<?php endif ?>

<?php if ($this->search_months_ago_enabled): // this is only used by A2B_data_archiving.php ?>
    <div class="row pb-1">
        <label class="col-4 col-form-label col-form-label-sm" for="search_date_months">
            <?php echo $this->search_months_ago_text?>
        </label>
        <div class="col-8">
            <div class="row pb-1">
                <div class="col-3">
                    <input type="checkbox" name="enable_search_months" id="enable_search_months" value="true" aria-label="<?= _("enable the search for months ago")?>" <?php if ($processed["enable_search_start_date2"]): ?>checked="checked"<?php endif ?> class="form-check-input"/>
                </div>
                <div class="col-4">
                    <select name="search_date_months" id="search_date_months" class="form-select form-select-sm">
                        <?php for ($i=3 ; $i<=12 ; $i++): ?>
                        <option <?php if ($processed['search_date_months'] === "$i"): ?>selected="selected"<?php endif ?>><?= sprintf("%d months", $i) ?></option>
                        <?php endfor ?>
                    </select>
                </div>
            </div>
        </div>
    </div>
<?php endif ?>

<?php $inputs = array_filter($this->search_form_elements, fn ($v) => $v["type"] !== "SELECT") ?>
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
                    data-popup-options="width=750,height=450,top=50,left=100,scrollbars=1"
                    data-select="<?= $item["select"] ?>"
                    class="badge bg-primary popup_trigger"
                    aria-label="open a popup to select an item"
                >
                    <svg class="mx-auto" width="16" height="16"><use xlink:href="#popup"></use></svg>
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

<?php $selects = array_filter($this->search_form_elements, fn ($v) => $v["type"] === "SELECT") ?>
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
