<?php
namespace A2billing\Forms;

/**
 * @var FormHandler $form
 * @var array $processed
 * @var array $list
 * @var int $popup_select
 * @var bool $hasActionButtons
 * @var array<string,mixed> $query_params
 * @var array<string,mixed> $sort_params
 * @var array<string,mixed> $pagination_params
 */
?>

<?php if (($form->FG_FILTER_ENABLE || $form->FG_FILTER2_ENABLE) || ($popup_select < 1 && ($form->FG_LIST_ADDING_BUTTON1 || $form->FG_LIST_ADDING_BUTTON2))): ?>
<div class="row pb-3 align-items-end">
    <?php if ($form->FG_LIST_VIEW_ROW_COUNT > 0 && ($form->FG_FILTER_ENABLE || $form->FG_FILTER2_ENABLE)): ?>
    <form method="post" action="<?= $_SERVER["PHP_SELF"] ?>" class="col">
        <input type="hidden" name="form_action" value="list"/>
        <?php foreach ($query_params as $key => $val): ?>
        <input type="hidden" name="<?= $key ?>" value="<?= $val ?>"/>
        <?php endforeach ?>
        <?php foreach ($processed as $key => $val): ?>
            <?php if (!empty($key) && $key !== 'current_page' && $key !== 'id' && !is_array($val)): ?>
        <input type="hidden" name="<?= $key?>" value="<?= $val?>"/>
            <?php endif ?>
        <?php endforeach ?>
        <input type="hidden" name="current_page" value="0"/>
        <?= $form->csrf_inputs() ?>
        <div class="row align-items-end">
            <?php if ($form->FG_FILTER_ENABLE): ?>
            <div class="col-auto">
                <label for="filterprefix" class="form-label">
                    <?= sprintf(_("Filter on %s"), $form->FG_FILTER_LABEL) ?>
                </label>
                <input
                    type="text"
                    id="filterprefix"
                    name="filterprefix"
                    value="<?= $processed['filterprefix'] ?? "" ?>"
                    class="form-control form-control-sm"
                />
            </div>
            <?php endif ?>

            <?php if ($form->FG_FILTER2_ENABLE): ?>
            <div class="col-auto">
                <label for="filterprefix2" class="form-label">
                    <?= sprintf(_("Filter on %s"), $form->FG_FILTER2_LABEL) ?>
                </label>
                <input
                    type="text"
                    id="filterprefix2"
                    name="filterprefix2"
                    value="<?= $processed["filterprefix2"] ?? "" ?>"
                    class="form-control form-control-sm"
                />
            </div>
            <?php endif ?>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary"><?= _("Apply Filter") ?></button>
            </div>
        </div>
    </form>
    <?php endif ?>
    <?php if ($popup_select < 1 && $form->FG_LIST_ADDING_BUTTON1): ?>
        <div class="col-auto ms-auto">
            <a href="<?= $form->FG_LIST_ADDING_BUTTON_LINK1 ?>" class="text-decoration-none">
                <?= $form->FG_LIST_ADDING_BUTTON_MSG1 ?>
                <?php if ($form->FG_LIST_ADDING_BUTTON_ICON1): ?>
                    <span
                        class="bi bi-16 bi-<?= $form->FG_LIST_ADDING_BUTTON_ICON1 ?>"
                        <?php if (!$form->FG_LIST_ADDING_BUTTON_MSG1): ?>
                        aria-label="<?= $form->FG_LIST_ADDING_BUTTON_ALT1 ?>"
                        <?php else: ?>
                        aria-hidden="true"
                        <?php endif ?>
                    ></span>
                <?php elseif ($form->FG_LIST_ADDING_BUTTON_IMG1): ?>
                    <img src="<?= $form->FG_LIST_ADDING_BUTTON_IMG1 ?>" alt="<?= $form->FG_LIST_ADDING_BUTTON_ALT1 ?>">
                <?php endif ?>
            </a>
        </div>
    <?php endif ?>
    <?php if($popup_select < 1 && $form->FG_LIST_ADDING_BUTTON2 && $form->FG_LIST_ADDING_BUTTON_MSG2): ?>
        <div class="col-auto ms-auto">
            <a href="<?= $form->FG_LIST_ADDING_BUTTON_LINK2 ?>" class="text-decoration-none">
                <?= $form->FG_LIST_ADDING_BUTTON_MSG2 ?>
                <?php if ($form->FG_LIST_ADDING_BUTTON_ICON2): ?>
                    <span class="bi bi-16 bi-<?= $form->FG_LIST_ADDING_BUTTON_ICON2 ?>" aria-hidden="true"></span>
                <?php elseif ($form->FG_LIST_ADDING_BUTTON_IMG2): ?>
                    <img src="<?= $form->FG_LIST_ADDING_BUTTON_IMG2 ?>" alt="<?= $form->FG_LIST_ADDING_BUTTON_ALT2 ?>">
                <?php endif ?>
            </a>
        </div>
    <?php endif ?>
</div>
<?php endif ?>

<?php if ($form->FG_LIST_VIEW_ROW_COUNT > 0): ?>
<div class="row pb-3">
    <div class="col table-responsive">
        <table
            class="list-view-table table table-bordered table-striped table-hover caption-top <?php if ($popup_select): ?>table-sm<?php endif ?>"
            data-popup-formname="<?= $processed["popup_formname"] ?? "" ?>"
            data-popup-fieldname="<?= $processed["popup_fieldname"] ?? "" ?>"
        >
            <caption>
                <?= $form->CV_TITLE_TEXT ?> – <?= sprintf(_("%d records"), $form->FG_LIST_VIEW_ROW_COUNT) ?>
            </caption>
            <thead>
                <tr>
                    <?php foreach ($form->FG_LIST_TABLE_CELLS as $column): ?>
                    <th>
                        <?php if ($column["sortable"]): ?>
                        <?php
                            $sort_params["order"] = $column["field"]; //todo: use the column index instead?
                            $sort_params["sens"] = $form->list_query_order_direction === "ASC" ?  "DESC" : "ASC";
                        ?>
                        <a
                            class="sort <?= $form->list_query_order_columns[0] === $column["field"] ? strtolower($form->list_query_order_direction) : "" ?>"
                            href="<?= "?" . http_build_query($sort_params, "", "&amp;") ?>"
                        >
                        <?php endif ?>
                            <?= $column["header"] ?>
                        <?php if ($column["sortable"]): ?>
                        </a>
                        <?php endif?>
                    </th>
                    <?php endforeach ?>
                    <?php if ($hasActionButtons): ?>
                    <th>
                        <strong> <?= _("Action") ?></strong>
                    </th>
                    <?php endif ?>

                </tr>
            </thead>
            <tbody>
            <?php foreach ($list as $row): ?>
                <tr>
                <?php foreach($form->FG_LIST_TABLE_CELLS as $j => $column):
                    if ($column["type"] === "list") {
                        $record_display = ($column["options"][$row[$j]] ?? null) ?: _("n/a");
                    } else {
                        $record_display = $row[$j];
                    }

                    $arg = [];
                    foreach ($column["arguments"] ?? [] as $argument) {
                        if (is_string($argument)) {
                            $argument = preg_replace_callback(
                                "/%([0-9]+|X)/",
                                fn ($m) => $m[1] === "X" ? $record_display : ($row[$m[1]] ?? ""),
                                $argument
                            );
                        }
                        $arg[] = $argument;
                    }
                    ?>
                    <td>
                    <?php if (!empty($column["function"]) && is_callable($column["function"])): ?>
                        <?= call_user_func_array($column["function"], $arg ?: [$record_display]) ?>
                    <?php elseif (!empty($column["href"])): ?>
                        <a href="<?= $column["href"] ?><?= str_ends_with($column["href"], "=") ? $row[$j] : "" ?>">
                            <?= $record_display ?>
                        </a>
                    <?php else: ?>
                        <?= $record_display ?? "" ?>
                    <?php endif ?>
                    </td>
                <?php $row[$j] = $record_display ?>
                <?php endforeach ?>
                <?php if ($hasActionButtons): ?>
                    <td>
                    <?php if($form->FG_ENABLE_INFO_BUTTON): ?>
                        <a
                            href="<?= $form->FG_INFO_BUTTON_LINK?><?= $row["instance_primary_key"] ?? "" ?>"
                            title="<?= sprintf(_("About this %s"), strtolower($form->FG_INSTANCE_NAME)) ?>"
                            aria-label="<?= sprintf(_("About this %s"), strtolower($form->FG_INSTANCE_NAME)) ?>"
                        >
                            <span class="bi bi-16 bi-search text-info" aria-hidden="true"></span>
                        </a>
                    <?php endif ?>
                    <?php if($form->FG_ENABLE_EDIT_BUTTON): ?>
                        <?php
                        $check = true;
                        if (!empty($form->FG_EDIT_BUTTON_CONDITION)) {
                            $condition_eval = preg_replace_callback(
                                "/\\|col([0-9]+)\\|/i",
                                fn ($m) => str_replace($m[0], $row[$m[1]] ?? "", $m[0]),
                                // only used in FG_var_invoice.inc and FG_var_receipt.inc
                                $form->FG_EDIT_BUTTON_CONDITION
                            );
                            $check = eval("return $condition_eval;");
                        }
                        ?>
                        <?php if($check): ?>
                        <a
                            href="<?= $form->FG_EDIT_BUTTON_LINK?><?= $row["instance_primary_key"] ?? "" ?>"
                            title="<?= sprintf(_("Edit this %s"), strtolower($form->FG_INSTANCE_NAME)) ?>"
                            aria-label="<?= sprintf(_("Edit this %s"), strtolower($form->FG_INSTANCE_NAME)) ?>"
                        >
                            <span class="bi bi-16 bi-pencil-fill text-body" aria-hidden="true"></span>
                        </a>
                        <?php endif ?>
                    <?php endif ?>
                    <?php if($form->FG_ENABLE_DELETE_BUTTON && !in_array($row["instance_primary_key"], $form->FG_DELETION_FORBIDDEN_ID)): ?>
                        <?php
                        $check = true;
                        if (!empty($form->FG_DELETE_BUTTON_CONDITION)) {
                            $condition_eval = preg_replace_callback(
                                "/\\|col([0-9]+)\\|/i",
                                fn ($m) => str_replace($m[0], $row[$m[1]] ?? "", $m[0]),
                                $form->FG_DELETE_BUTTON_CONDITION
                            );
                            $check = eval("return $condition_eval;");
                        }
                        ?>
                        <?php if ($check): ?>
                        <a
                            href="<?= $form->FG_DELETE_BUTTON_LINK?><?= $row["instance_primary_key"] ?? "" ?>"
                            title="<?= sprintf(_("Delete this %s"), strtolower($form->FG_INSTANCE_NAME)) ?>"
                            aria-label="<?= sprintf(_("Delete this %s"), strtolower($form->FG_INSTANCE_NAME)) ?>"
                        >
                            <span class="bi bi-16 bi-trash3-fill text-danger" aria-hidden="true"></span>
                        </a>
                        <?php endif ?>
                    <?php endif ?>
                    <?php foreach ($form->list_action_buttons as $button):
                        $check = true;
                        if (!empty($button["match_index"])) {
                            $check = $row[$button["match_index"]] == $button["match_value"];
                        }
                        if (!$check) {
                            continue;
                        }
                        $link = "";
                        if ($button["url"] !== "") {
                            $link = str_replace("|param|", $row["instance_primary_key"] ?? "", $button["url"]);
                            $link = preg_replace_callback(
                                "/\\|col([0-9]+)\\|/i",
                                fn ($m) => str_replace($m[0], $row[$m[1]] ?? "", $m[0]),
                                $link
                            );
                            if (str_ends_with($link, "=")) {
                                $link .= $row["instance_primary_key"] ?? "";
                            }
                        }
                        if ($button["icon"]) {
                            $contents = "<span class=\"bi bi-16 bi-$button[icon]\" aria-hidden=\"true\" aria-label=\"$button[label]\"></span>";
                        } elseif ($button["image"]) {
                            $contents = "<img alt=\"$button[label]\" src=\"$button[image]\"/>";
                        } else {
                            $contents = $button["label"];
                        }
                    ?>
                        <?php if ($link): ?>
                        <a
                            href="<?= $link ?>"
                            title="<?= $button["label"] ?>"
                            class="<?= $button["class"] ?? "" ?>"
                            data-primary-key="<?= $row["instance_primary_key"] ?? "" ?>"
                            data-popup-select="<?= $popup_select ?>"
                            <?= $button["image"] ? "aria-label=\"$button[label]\"" : "" ?>
                        ><?= $contents ?></a>
                        <?php else: ?>
                        <button
                            type="button"
                            title="<?= $button["label"] ?>"
                            class="btn align-baseline p-0 <?= $button["class"] ?? "" ?>"
                            data-primary-key="<?= $row["instance_primary_key"] ?? "" ?>"
                            data-popup-select="<?= $popup_select ?>"
                            <?= $button["image"] ? "aria-label=\"$button[label]\"" : "" ?>
                        ><?= $contents ?></button>
                        <?php endif ?>
                    <?php endforeach ?>
                    </td>
                <?php endif ?>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>
<div class="row pb-3">
    <div class="col">
        <?= FormHandler::printPages(
            (int)($processed['current_page'] ?? 0) + 1,
            $form->FG_LIST_VIEW_PAGE_COUNT,
            "?" . http_build_query($pagination_params, "", "&amp;")
//            "?current_page=%s&amp;filterprefix=$processed[filterprefix]&amp;order=$processed[order]&amp;sens=$processed[sens]&amp;mydisplaylimit=$processed[mydisplaylimit]&amp;popup_select=$processed[popup_select]&amp;letter=$letter" . (str_starts_with($form->CV_FOLLOWPARAMETERS, "&") ? "" : "&amp;") . $form->CV_FOLLOWPARAMETERS
        ) ?>
    </div>
</div>

<div class="row pb-3 justify-content-start align-items-center">
    <div class="col-4">
        <form id="displaylimit_form" action="">
            <label for="displaylimit" class="form-label d-inline"><?= gettext("Display");?></label>
            <input type="hidden" name="id" value="<?= $processed["id"] ?? "" ?>"/>
            <input type="hidden" name="form_action" value="list"/>
            <input type="hidden" name="current_page" value="0"/>
            <?php foreach ($query_params as $key => $val): ?>
            <input type="hidden" name="<?= $key ?>" value="<?= $val ?>"/>
            <?php endforeach ?>
            <?php foreach ($processed as $key => $val): ?>
                <?php if ($key !== 'current_page' && $key !== 'id'): ?>
                    <input type="hidden" name="<?= $key ?>" value="<?= $val ?>">
                <?php endif ?>
            <?php endforeach ?>
            <select id="displaylimit" name="mydisplaylimit" size="1" class="form-select form-select-sm d-inline w-50">
                <option value="10" <?= (int)($_SESSION["$form->FG_QUERY_TABLE_NAME-displaylimit"] ?? 10) < 50 ? 'selected="selected"' : "" ?>>10</option>
                <option value="50" <?= (int)($_SESSION["$form->FG_QUERY_TABLE_NAME-displaylimit"] ?? 10) === 50 ? 'selected="selected"' : "" ?>>50</option>
                <option value="100" <?= (int)($_SESSION["$form->FG_QUERY_TABLE_NAME-displaylimit"] ?? 10) === 100 ? 'selected="selected"' : "" ?>>100</option>
                <option value="ALL" <?= (int)($_SESSION["$form->FG_QUERY_TABLE_NAME-displaylimit"] ?? 10) > 100 ? 'selected="selected"' : "" ?>>All</option>
            </select>
        </form>
    </div>

    <?php if ($form->FG_EXPORT_CSV): ?>
    <div class="col-auto">
        <a href="export_csv.php?var_export=<?= $form->export_session_key ?>&amp;var_export_type=type_csv" target="_blank" class="text-decoration-none">
            <div class="bi bi-40 bi-filetype-xls text-center mb-1" aria-hidden="true"></div>
            <?= gettext("Export CSV") ?>
        </a>
    </div>
    <?php endif ?>

    <?php if ($form->FG_EXPORT_XML): ?>
    <div class="col-auto">
        <a href="export_csv.php?var_export=<?= $form->export_session_key ?>&amp;var_export_type=type_xml" target="_blank" class="text-decoration-none">
            <div class="bi bi-40 bi-filetype-xml text-center mb-1" aria-hidden="true"></div>
            <?= gettext("Export XML") ?>
        </a>
    </div>
    <?php endif ?>
</div>
<?php endif ?>
