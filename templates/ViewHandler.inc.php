<?php
namespace A2billing\Forms;

/**
 * @var FormHandler $form
 * @var array $processed values from $_GET or $_POST
 * @var array $list the rows from the query
 * @var int $popup_select will be > 0 if it's a popup window
 * @var bool $hasActionButtons
 * @var array<string,mixed> $query_params
 * @var array<string,mixed> $sort_params
 * @var array<string,mixed> $pagination_params
 */
?>

<?php if (count($form->list_filters) > 0 || ($popup_select < 1 && count($form->list_top_buttons) > 0)): ?>
<div class="row pb-3 align-items-end" id="list-filter-container">
    <?php if ($form->FG_LIST_VIEW_ROW_COUNT > 0 && count($form->list_filters) > 0): ?>
    <form method="get" action="<?= $_SERVER["PHP_SELF"] ?>" class="col">
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
        <div class="row align-items-end">
            <?php foreach ($form->list_filters as $i => $filter): ?>
            <div class="col-auto">
                <input
                    type="text"
                    id="filterprefix<?= $i ?>"
                    name="filterprefix<?= $i ?>"
                    value="<?= $processed["filterprefix$i"] ?? "" ?>"
                    class="form-control form-control-sm"
                    placeholder="<?= $filter["label"] ?>"
                    aria-label="<?= sprintf(_("Filter on %s"), $filter["label"]) ?>"
                />
            </div>
            <?php endforeach; ?>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary"><?= _("Apply Filter") ?></button>
            </div>
        </div>
    </form>
    <?php endif ?>
    <?php if ($popup_select < 1): ?>
    <?php foreach ($form->list_top_buttons as $button): ?>
    <div class="col-auto ms-auto">
        <a href="<?= $button["url"] ?>" class="text-decoration-none">
            <?= htmlspecialchars($button["label"]) ?>
            <?php if ($button["icon"]): ?>
            <span class="bi bi-16 bi-<?= $button["icon"] ?>" aria-hidden="true"></span>
            <?php endif ?>
        </a>
    </div>
    <?php endforeach ?>
    <?php endif ?>
</div>
<?php endif ?>

<?php if (count($list) > 0): ?>
<div class="row pb-3" id="list-table-container">
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

<div class="row pb-3 justify-content-between align-items-center">
    <div class="col-3" id="list-displaylimit-container">
        <form id="displaylimit_form" action="" class="row">
            <label for="displaylimit" class="col-auto col-form-label-sm"><?= gettext("Display");?></label>
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
            <div class="col-auto">
                <select id="displaylimit" name="mydisplaylimit" class="form-select form-select-sm">
                    <option value="10" <?= (int)($_SESSION["$form->FG_QUERY_TABLE_NAME-displaylimit"] ?? 10) < 50 ? 'selected="selected"' : "" ?>>10</option>
                    <option value="50" <?= (int)($_SESSION["$form->FG_QUERY_TABLE_NAME-displaylimit"] ?? 10) === 50 ? 'selected="selected"' : "" ?>>50</option>
                    <option value="100" <?= (int)($_SESSION["$form->FG_QUERY_TABLE_NAME-displaylimit"] ?? 10) === 100 ? 'selected="selected"' : "" ?>>100</option>
                    <option value="ALL" <?= (int)($_SESSION["$form->FG_QUERY_TABLE_NAME-displaylimit"] ?? 10) > 100 ? 'selected="selected"' : "" ?>>All</option>
                </select>
            </div>
        </form>
    </div>

    <div class="col-6" id="list-pagination-container">
        <?= FormHandler::printPages(
            (int)($processed['current_page'] ?? 0) + 1,
            $form->FG_LIST_VIEW_PAGE_COUNT,
            "?" . http_build_query($pagination_params, "", "&amp;")
//            "?current_page=%s&amp;filterprefix=$processed[filterprefix]&amp;order=$processed[order]&amp;sens=$processed[sens]&amp;mydisplaylimit=$processed[mydisplaylimit]&amp;popup_select=$processed[popup_select]&amp;letter=$letter" . (str_starts_with($form->CV_FOLLOWPARAMETERS, "&") ? "" : "&amp;") . $form->CV_FOLLOWPARAMETERS
        ) ?>
    </div>

    <div class="col-3 list-export-container d-flex align-items-center justify-content-end">
        <?php if ($form->export_enable_csv): ?>
            <a href="export_csv.php?export_session=<?= $form->export_session_key ?>&amp;export_type=csv" target="_blank" class="mx-2 text-decoration-none">
                <div class="bi bi-24 bi-filetype-xls text-center mb-1" aria-hidden="true"></div>
                <?= gettext("Export CSV") ?>
            </a>
        <?php endif ?>

        <?php if ($form->export_enable_xml): ?>
            <a href="export_csv.php?export_session=<?= $form->export_session_key ?>&amp;export_type=xml" target="_blank" class="mx-2 text-decoration-none">
                <div class="bi bi-24 bi-filetype-xml text-center mb-1" aria-hidden="true"></div>
                <?= gettext("Export XML") ?>
            </a>
        <?php endif ?>
    </div>
</div>
<?php endif ?>
