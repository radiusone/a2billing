<?php

use A2billing\Forms\FormHandler;
/**
 * @var FormHandler $form
 * @var array $processed
 * @var array $list
 * @var bool $full_modal
 * @var bool $with_hide_button
 * @var string $action
 */
?>
<?php if ($full_modal && $with_hide_button): ?>
<div class="row pb-3 justify-content-center">
    <div class="col-auto">
        <?= $form->create_search_button() ?>
    </div>
</div>
<?php endif ?>
<form method="post" name="searchForm" id="searchForm" class="container-fluid form-striped" action="?<?= $action ?>">
    <input type="hidden" name="posted_search" value="1"/>
    <input type="hidden" name="current_page" value="0"/>
    <?= $form->csrf_inputs() ?>

<?php if ($full_modal): ?>
    <div class="modal" id="searchModal" aria-labelledby="modal-title-search" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-title-search"><?= $form->search_form_title ?? sprintf(_("Search %s"), $form->FG_INSTANCE_NAME) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
<?php else: ?>

    <div class="row">
        <div class="col">
            <strong><?php echo $form->search_form_title?></strong>
        </div>
    </div>
<?php endif ?>

<?php $inputs = array_filter($form->search_form_elements, fn ($v) => !in_array($v["type"], ["SELECT", "BUTTON"])) ?>
<?php foreach ($inputs as $k => $item): ?>
    <div class="row py-1">
        <label class="col-4 col-form-label col-form-label-sm" for="<?= $item["input"][0] ?>" id="item<?= $k ?>_label">
            <?= $item["label"] ?>
        </label>
    <?php if ($item["type"] === "POPUP"): ?>
        <div class="col-8">
            <div class="input-group">
                <input
                    name="<?= $item["input"][0] ?>"
                    id="<?= $item["input"][0] ?>"
                    value="<?= $processed[$item["input"][0]] ?? "" ?>"
                    class="form-control form-control-sm"
                />
                <a
                    href="<?= $item["href"] ?>"
                    data-field-name="<?= $item["input"][0] ?>"
                    data-window-name="<?= $item["input"][0] ?>_popup"
                    data-popup-options="width=750,height=450,top=50,left=100,scrollbars=1"
                    data-select="<?= $item["select"] ?>"
                    class="btn btn-primary popup_trigger"
                    aria-label="<?= _("open a popup to select an item") ?>"
                >
                    <svg class="mx-auto" width="16" height="16"><use xlink:href="#popup"></use></svg>
                </a>
            </div>
        </div>

    <?php elseif ($item["type"] === "TEXT"): ?>
        <div class="col-8">
            <div class="input-group">
                <div>
                    <select name="<?= $item["operator"][0] ?>" id="<?= $item["operator"][0] ?>" class="form-select form-select-sm" aria-label="<?= _("select a search type for the previous input") ?>">
                        <option value="1" <?php if (($processed[$item["operator"][0]] ?? 3) == 1): ?>selected="selected"<?php endif ?>><?= _("Exact") ?></option>
                        <option value="2" <?php if (($processed[$item["operator"][0]] ?? 3) == 2): ?>selected="selected"<?php endif ?>><?= _("Begins with") ?></option>
                        <option value="3" <?php if (($processed[$item["operator"][0]] ?? 3) == 3): ?>selected="selected"<?php endif ?>><?= _("Contains") ?></option>
                        <option value="4" <?php if (($processed[$item["operator"][0]] ?? 3) == 4): ?>selected="selected"<?php endif ?>><?= _("Ends with") ?></option>
                    </select>
                </div>
                <input name="<?= $item["input"][0] ?>" id="<?= $item["input"][0] ?>" value="<?= $processed[$item["input"][0]] ?? "" ?>" class="form-control form-control-sm"/>
            </div>
        </div>

    <?php elseif ($item["type"] === "COMPARISON"): ?>
        <div class="col">
            <div class="row align-items-center">
                <div class="col-5">
                    <div class="input-group">
                        <div>
                            <select name="<?= $item["operator"][0] ?>" class="form-select form-select-sm" aria-label="select an operator to apply to the next input">
                                <option value="4" <?php if (($processed[$item["operator"][0]] ?? 1) == 4): ?> selected="selected"<?php endif ?> aria-label="greater than">&gt;</option>
                                <option value="5" <?php if (($processed[$item["operator"][0]] ?? 1) == 5): ?> selected="selected"<?php endif ?> aria-label="greater than or equal to">&gt;=</option>
                                <option value="1" <?php if (($processed[$item["operator"][0]] ?? 1) == 1): ?> selected="selected"<?php endif ?> aria-label="equal to">=</option>
                                <option value="2" <?php if (($processed[$item["operator"][0]] ?? 1) == 2): ?> selected="selected"<?php endif ?> aria-label="less than or equal to">&lt;=</option>
                                <option value="3" <?php if (($processed[$item["operator"][0]] ?? 1) == 3): ?> selected="selected"<?php endif ?> aria-label="less than">&lt;</option>
                            </select>
                        </div>
                        <input type="text" name="<?= $item["input"][0] ?>" id="<?= $item["input"][0] ?>" value="<?= $processed[$item["input"][0]] ?? "" ?>" class="form-control form-control-sm"/>
                    </div>
                </div>
                <div class="col-2 text-center">
                    <?= _("AND") ?>
                </div>
                <div class="col-5">
                    <div class="input-group">
                        <div>
                            <select name="<?= $item["operator"][1] ?>" class="form-select form-select-sm" aria-label="select an operator to apply to the next input">
                                <option></option>
                                <option value="4" <?php if (($processed[$item["operator"][1]] ?? 1) == 4): ?> selected="selected"<?php endif ?> aria-label="greater than">&gt;</option>
                                <option value="5" <?php if (($processed[$item["operator"][1]] ?? 1) == 5): ?> selected="selected"<?php endif ?> aria-label="greater than or equal to">&gt;=</option>
                                <option value="2" <?php if (($processed[$item["operator"][1]] ?? 1) == 2): ?> selected="selected"<?php endif ?> aria-label="less than or equal to">&lt;=</option>
                                <option value="3" <?php if (($processed[$item["operator"][1]] ?? 1) == 3): ?> selected="selected"<?php endif ?> aria-label="less than">&lt;</option>
                            </select>
                        </div>
                        <input type="text" name="<?= $item["input"][1] ?>" id="<?= $item["input"][1] ?>" value="<?= $processed[$item["input"][1]] ?? "" ?>" class="form-control form-control-sm" aria-label="<?= $item["label"] ?>"/>
                    </div>
                </div>
            </div>
        </div>

    <?php elseif ($item["type"] === "DATE"): ?>
        <?php if (!$item["relative"] && !$item["recent"]): ?>
        <div class="col-4">
            <div class="input-group">
                <div class="input-group-text">
                    <input
                        type="checkbox"
                        name="enable_<?= $item["input"][0] ?>"
                        id="enable_<?= $item["input"][0] ?>"
                        value="true"
                        aria-label="<?= _("enable the search start date")?>"
                        <?php if ($processed["enable_" . $item["input"][0]] ?? ""): ?>checked="checked"<?php endif ?>
                        class="form-check-input m-0 date-input-enabler"
                    />&nbsp;<label for="enable_<?= $item["input"][0] ?>" class="form-label form-label-sm m-0"><?=_("From") ?></label>
                </div>
                <input type="date" name="<?= $item["input"][0] ?>" id="<?= $item["input"][0] ?>" value="<?= $processed[$item["input"][0]] ?? (new DateTime('first day of this month'))->format("Y-m-d") ?>" aria-label="<?= _("search start date") ?>" class="form-control form-control-sm"/>
                <input type="hidden" name="<?= $item["operator"][0] ?>" value="5"/><!-- >= -->
            </div>
        </div>
        <?php endif ?>

        <div class="<?= $item["relative"] || $item["recent"] ? "col-8" : "col-4" ?>">
            <div class="input-group">
                <div class="input-group-text">
                    <input
                        type="checkbox"
                        name="enable_<?= $item["input"][1] ?>"
                        id="enable_<?= $item["input"][1] . ($item["relative"] ? "_relative" : ($item["recent"] ? "_recent" : "")) ?>"
                        value="true"
                        aria-label="<?= _("enable the search end date") ?>"
                        <?php if ($processed["enable_" . $item["input"][1]] ?? ""): ?>checked="checked"<?php endif ?>
                        class="form-check-input m-0 date-input-enabler <?= $item["relative"] || $item["recent"] ? "relative-date" : "" ?>"
                    />&nbsp;<label for="enable_<?= $item["input"][1] ?>" class="form-label form-label-sm m-0">
                        <?php if ($item["relative"]): ?>
                        <?= _("Before") ?>
                        <?php elseif ($item["recent"]): ?>
                        <?= _("Since") ?>
                        <?php else: ?>
                        <?= _("To") ?>
                        <?php endif ?>
                    </label>
                </div>
            <?php if ($item["relative"]): ?>
                <select name="<?= $item["input"][1] ?>" id="<?= $item["input"][1] ?>_relative" class="form-select form-select-sm" aria-labelledby="item<?= $k ?>_label">
                    <?php for ($i = 1; $i <= 12; $i++): ?>
                    <?php $val = (new DateTime("$i months ago"))->format("Y-m-d") ?>
                        <option
                            <?php if (($processed[$item["input"][1]] ?? 0) === "$val"): ?>selected="selected"<?php endif ?>
                            value="<?= $val ?>"
                        >
                            <?= sprintf(ngettext("%d month ago", "%d months ago", $i), $i) ?>
                        </option>
                    <?php endfor ?>
                </select>
            <?php elseif ($item["recent"]): ?>
                <select name="<?= $item["input"][1] ?>" id="<?= $item["input"][1] ?>_recent" class="form-select form-select-sm" aria-labelledby="item<?= $k ?>_label">
                    <option value="<?= $val = (new DateTime("1 hour ago"))->format("Y-m-d H:00") ?>" <?= ($processed[$item["input"][1]] ?? 0) === "$val" ? "selected=\"selected\"" : "" ?>>
                        <?= sprintf(ngettext("%d hour ago", "%d hours ago", 1), 1) ?>
                    </option>
                    <option value="<?= $val = (new DateTime("6 hours ago"))->format("Y-m-d H:00") ?>" <?= ($processed[$item["input"][1]] ?? 0) === "$val" ? "selected=\"selected\"" : "" ?>>
                        <?= sprintf(ngettext("%d hour ago", "%d hours ago", 6), 6) ?>
                    </option>
                    <option value="<?= $val = (new DateTime("1 day ago"))->format("Y-m-d H:00") ?>" <?= ($processed[$item["input"][1]] ?? 0) === "$val" ? "selected=\"selected\"" : "" ?>>
                        <?= sprintf(ngettext("%d day ago", "%d days ago", 1), 1) ?>
                    </option>
                    <option value="<?= $val = (new DateTime("1 week ago"))->format("Y-m-d H:00") ?>" <?= ($processed[$item["input"][1]] ?? 0) === "$val" ? "selected=\"selected\"" : "" ?>>
                        <?= sprintf(ngettext("%d week ago", "%d weeks ago", 1), 1) ?>
                    </option>
                </select>
            <?php else: ?>
                <input type="date" name="<?= $item["input"][1] ?>" id="<?= $item["input"][1] ?>" value="<?= $processed[$item["input"][1]] ?? (new DateTime('first day of next month'))->format("Y-m-d") ?>" aria-label="<?= _("search end date") ?>" class="form-control form-control-sm"/>
            <?php endif ?>
                <input type="hidden" name="<?= $item["operator"][1] ?>" value="<?= $item["recent"] ? 5 : 2 ?>"/><!-- >= (recent) or <= -->
            </div>
        </div>

    <?php endif ?>
    </div>
<?php endforeach ?>

<?php $selects = array_filter($form->search_form_elements, fn ($v) => $v["type"] === "SELECT") ?>
<?php foreach (array_chunk($selects, 3) as $chunk): ?>
    <div class="row py-1">
    <?php foreach ($chunk as $item): ?>
        <div class="col-4">
            <select name="<?= $item["input"][0] ?>" aria-label="<?= $item["label"] ?>" class="form-select form-select-sm">
                <option value=""><?= $item["label"] ?></option>
                <?php foreach ($item["options"] as $key => $opt): ?>
                    <?php $val = is_array($opt) ? $opt[0] : $key ?>
                    <option value="<?= $val ?>" <?php if (strcmp($processed[$item["input"][0]] ?? "zzzzzz", $val) === 0): ?>selected="selected"<?php endif ?>>
                        <?= is_array($opt) ? $opt[1] : $opt ?>
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
            <?php if (strlen($_SESSION[$form->search_session_key] ?? "") > 10): ?>
                <?php if ($form->search_delete_enabled): ?>
                    <a class="btn btn-danger" href="?deleteselected=true" onclick="return confirm('<?= "Are you sure you want to delete " . $form->FG_LIST_VIEW_ROW_COUNT . " selected records?" ?>')"><?= _("Delete") ?></a>
                <?php endif ?>
            <a class="btn btn-secondary" href="?cancelsearch=true"><?= _("Clear Search") ?></a>
            <?php endif ?>
            <?php $buttons = array_filter($form->search_form_elements, fn ($v) => $v["type"] === "BUTTON") ?>
            <?php foreach ($buttons as $button): ?>
            <button
                type="submit"
                name="<?= $button["input"][0] ?>"
                value="<?= $button["value"] ?>"
                class="btn <?= $button["class"] ?>"
                onclick="<?= $button["onclick"] ?>"
            ><?= $button["label"] ?></button>
            <?php endforeach ?>
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
