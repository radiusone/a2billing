<?php

use A2billing\Forms\FormHandler;
/**
 * @var FormHandler $form
 * @var array $processed
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
                    <span class="bi bi-box-arrow-up-right fw-bolder fs-6"></span>
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

    <?php elseif ($item["type"] === "SINGLEDATE"): ?>
        <div class="col-8">
            <input type="date" name="<?= $item["input"][0] ?>" id="<?= $item["input"][0] ?>" value="<?= $processed[$item["input"][0]] ?? date("Y-m-d") ?>" class="form-control form-control-sm"/>
            <input type="hidden" name="<?= $item["operator"][0] ?>" value="2"/><!-- starts with -->
        </div>

    <?php elseif ($item["type"] === "RELATIVEDATE"): ?>
        <div class="col-8">
            <div class="input-group">
                <div class="input-group-text">
                    <input
                        type="checkbox"
                        name="enable_<?= $item["input"][0] ?>"
                        id="enable_<?= $item["input"][0] ?>"
                        value="true"
                        aria-label="<?= _("enable the relative date search") ?>"
                        <?php if (!empty($processed["enable_" . $item["input"][0]])): ?>checked="checked"<?php endif ?>
                        class="form-check-input m-0 date-input-enabler relative-date"
                    />&nbsp;<label for="enable_<?= $item["input"][0] ?>" class="form-label form-label-sm m-0">
                        <?= ($item["start"]) ? _("Since") : _("Before") ?>
                    </label>
                </div>
                <select name="<?= $item["input"][0] ?>" id="<?= $item["input"][0] ?>" class="form-select form-select-sm" aria-labelledby="item<?= $k ?>_label">
            <?php if ($item["months"]): ?>
                <?php for ($i = 1; $i <= 12; $i++): ?>
                    <option value="<?= $val = (new DateTime("$i months ago"))->format("Y-m-d") ?>" <?php if (($processed[$item["input"][0]] ?? 0) === "$val"): ?>selected="selected"<?php endif ?>>
                        <?= sprintf(ngettext("%d month ago", "%d months ago", $i), $i) ?>
                    </option>
                <?php endfor ?>
            <?php else: ?>
                    <option value="<?= $val = (new DateTime("1 hour ago"))->format("Y-m-d H:i") ?>" <?php if (($processed[$item["input"][0]] ?? 0) === "$val"): ?>selected="selected"<?php endif ?>>
                        <?= sprintf(ngettext("%d hour ago", "%d hours ago", 1), 1) ?>
                    </option>
                    <option value="<?= $val = (new DateTime("6 hours ago"))->format("Y-m-d H:i") ?>" <?php if (($processed[$item["input"][0]] ?? 0) === "$val"): ?>selected="selected"<?php endif ?>>
                        <?= sprintf(ngettext("%d hour ago", "%d hours ago", 6), 6) ?>
                    </option>
                    <option value="<?= $val = (new DateTime("1 day ago"))->format("Y-m-d H:00") ?>" <?php if (($processed[$item["input"][0]] ?? 0) === "$val"): ?>selected="selected"<?php endif ?>>
                        <?= sprintf(ngettext("%d day ago", "%d days ago", 1), 1) ?>
                    </option>
                    <option value="<?= $val = (new DateTime("1 week ago"))->format("Y-m-d H:00") ?>" <?php if (($processed[$item["input"][0]] ?? 0) === "$val"): ?>selected="selected"<?php endif ?>>
                        <?= sprintf(ngettext("%d week ago", "%d weeks ago", 1), 1) ?>
                    </option>
                    <option value="<?= $val = (new DateTime("1 month ago"))->format("Y-m-d") ?>" <?php if (($processed[$item["input"][0]] ?? 0) === "$val"): ?>selected="selected"<?php endif ?>>
                        <?= sprintf(ngettext("%d month ago", "%d months ago", 1), 1) ?>
                    </option>
                    <option value="<?= $val = (new DateTime("2 months ago"))->format("Y-m-d") ?>" <?php if (($processed[$item["input"][0]] ?? 0) === "$val"): ?>selected="selected"<?php endif ?>>
                        <?= sprintf(ngettext("%d month ago", "%d months ago", 2), 2) ?>
                    </option>
                    <option value="<?= $val = (new DateTime("3 months ago"))->format("Y-m-d") ?>" <?php if (($processed[$item["input"][0]] ?? 0) === "$val"): ?>selected="selected"<?php endif ?>>
                        <?= sprintf(ngettext("%d month ago", "%d months ago", 3), 3) ?>
                    </option>
                    <option value="<?= $val = (new DateTime("6 months ago"))->format("Y-m-d") ?>" <?php if (($processed[$item["input"][0]] ?? 0) === "$val"): ?>selected="selected"<?php endif ?>>
                        <?= sprintf(ngettext("%d month ago", "%d months ago", 6), 6) ?>
                    </option>
                    <option value="<?= $val = (new DateTime("1 year ago"))->format("Y-m-d") ?>" <?php if (($processed[$item["input"][0]] ?? 0) === "$val"): ?>selected="selected"<?php endif ?>>
                        <?= sprintf(ngettext("%d year ago", "%d years ago", 1), 1) ?>
                    </option>
            <?php endif ?>
                </select>
                <input type="hidden" name="<?= $item["operator"][0] ?>" value="<?= $item["start"] ? 5 : 3 ?>"/><!-- gte or lt; assume they want midnight cutoff -->
            </div>
        </div>

    <?php elseif ($item["type"] === "DATE"): ?>
        <div class="col-4">
            <div class="input-group">
                <div class="input-group-text">
                    <input
                        type="checkbox"
                        name="enable_<?= $item["input"][0] ?>"
                        id="enable_<?= $item["input"][0] ?>"
                        value="true"
                        aria-label="<?= _("enable the search start date")?>"
                        <?php if (!empty($processed["enable_" . $item["input"][0]])): ?>checked="checked"<?php endif ?>
                        class="form-check-input m-0 date-input-enabler"
                    />&nbsp;<label for="enable_<?= $item["input"][0] ?>" class="form-label form-label-sm m-0"><?=_("From") ?></label>
                </div>
                <input type="date" name="<?= $item["input"][0] ?>" id="<?= $item["input"][0] ?>" value="<?= $processed[$item["input"][0]] ?? (new DateTime('first day of this month'))->format("Y-m-d") ?>" aria-label="<?= _("search start date") ?>" class="form-control form-control-sm"/>
                <input type="hidden" name="<?= $item["operator"][0] ?>" value="5"/><!-- >= -->
            </div>
        </div>
        <div class="col-4">
            <div class="input-group">
                <div class="input-group-text">
                    <input
                        type="checkbox"
                        name="enable_<?= $item["input"][1] ?>"
                        id="enable_<?= $item["input"][1] ?>"
                        value="true"
                        aria-label="<?= _("enable the search end date") ?>"
                        <?php if (!empty($processed["enable_" . $item["input"][1]])): ?>checked="checked"<?php endif ?>
                        class="form-check-input m-0 date-input-enabler"
                    />&nbsp;<label for="enable_<?= $item["input"][1] ?>" class="form-label form-label-sm m-0">
                        <?= _("To") ?>
                    </label>
                </div>
                <input type="date" name="<?= $item["input"][1] ?>" id="<?= $item["input"][1] ?>" value="<?= $processed[$item["input"][1]] ?? (new DateTime('first day of next month'))->format("Y-m-d") ?>" aria-label="<?= _("search end date") ?>" class="form-control form-control-sm"/>
                <input type="hidden" name="<?= $item["operator"][1] ?>" value="2"/><!-- <= -->
            </div>
        </div>

    <?php elseif ($item["type"] === "RADIO"): ?>
        <div class="col-8">
            <div class="row">
            <?php foreach ($item["options"] as $val => $opt): ?>
                <div class="form-check col-6 col-lg-3 mb-1">
                    <input
                        type="radio"
                        name="<?= $item["input"][0] ?>"
                        id="<?= $item["input"][0] . $val ?>"
                        value="<?= $val ?>"
                        <?php if (strval($processed[$item["input"][0]] ?? $item["default"] ?? "zzzzzz") === "$val"):?>
                        checked="checked"
                        <?php endif ?>
                        class="form-check-input"
                    />
                    <label class="form-check-label form-check-label-sm" for="<?= $item["input"][0] . $val ?>"><?= $opt ?></label>
                </div>
            <?php endforeach ?>
            </div>
        </div>

    <?php endif ?>
    </div>
<?php endforeach ?>

<?php $selects = array_filter($form->search_form_elements, fn ($v) => $v["type"] === "SELECT") ?>
    <div class="row py-1">
    <?php foreach ($selects as $item): ?>
        <div class="col-4 mb-1">
            <label for="<?= $item["input"][0] ?>" class="form-label form-label-sm"><?= $item["label"] ?></label>
            <select name="<?= $item["input"][0] ?>" id="<?= $item["input"][0] ?>" class="form-select form-select-sm">
                <option value=""></option>
                <?php foreach ($item["options"] as $key => $opt): ?>
                    <option value="<?= $key ?>" <?php if (strval($processed[$item["input"][0]] ?? $item["default"] ?? "zzzzzz") === "$key"): ?>selected="selected"<?php endif ?>>
                        <?= $opt ?>
                    </option>
                <?php endforeach ?>
            </select>
        </div>
    <?php endforeach ?>
    </div>

<?php if ($full_modal): ?>
                </div> <!-- .modal-body -->
                <div class="modal-footer">
<?php else: ?>
    <div class="row justify-content-end border-top pt-3 mt-3 bg-transparent">
        <div class="col text-end">
<?php endif ?>
            <?php if (strlen($_SESSION[$form->search_session_key] ?? "") > 10): ?>
                <?php if ($form->search_delete_enabled): ?>
                    <a class="btn btn-danger confirm-with-message" href="?deleteselected=true" data-message="<?= htmlspecialchars(sprintf(_("Are you sure you want to delete %d selected records?"), $form->FG_LIST_VIEW_ROW_COUNT)) ?>"><?= _("Delete") ?></a>
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
