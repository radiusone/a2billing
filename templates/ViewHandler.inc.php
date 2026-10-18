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
 * @var string[] $cols
 */
$cols = array_keys(array_first($list ?? []) ?? []);
?>

<?php if (count($form->list_filters) > 0 || ($popup_select < 1 && count($form->list_top_buttons) > 0)): ?>
<div class="row pb-3 align-items-end" id="list-filter-container">
    <?php if ($form->FG_LIST_VIEW_ROW_COUNT > 0 && count($form->list_filters) > 0): ?>
    <form method="get" action="<?= $_SERVER["PHP_SELF"] ?>" class="col">
        <input type="hidden" name="form_action" value="list"/>
        <?php foreach ($query_params as $key => $val): ?>
            <?php if (!empty($key) && !is_array($val)): ?>
        <input type="hidden" name="<?= $key ?>" value="<?= $val ?>"/>
            <?php endif ?>
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
        <?= $button->renderButton() ?>
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
                <?= $form->list_message_intro ?> – <?= sprintf(_("%d records"), $form->FG_LIST_VIEW_ROW_COUNT) ?>
            </caption>
            <thead>
                <tr>
                    <?php foreach ($form->FG_LIST_TABLE_CELLS as $i => $column): ?>
                        <?php
                        $sort_params["order"] = $cols[$i];
                        $sort_params["sens"] = ($form->query_builder->orders[0]["direction"] ?? "") === "asc" ?  "desc" : "asc";
                        ?>
                        <?= $column->renderHeader(($form->query_builder->orders[0]["column"] ?? "") === $cols[$i], $sort_params) ?>
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
                <?php foreach($form->FG_LIST_TABLE_CELLS as $j => $column): // todo: numeric keys in result ?>
                    <?= $column->renderCell($row[$j], $row) ?>
                <?php endforeach ?>
                <?php if ($hasActionButtons): ?>
                    <td>
                    <?= $form->listInfoButton?->renderButton($row["instance_primary_key"], $row) ?>
                    <?= $form->listEditButton?->renderButton($row["instance_primary_key"], $row) ?>
                    <?php if(!in_array($row["instance_primary_key"], $form->FG_DELETION_FORBIDDEN_ID)): ?>
                        <?= $form->listDeleteButton?->renderButton($row["instance_primary_key"], $row) ?>
                    <?php endif ?>
                    <?php foreach ($form->list_action_buttons as $button): ?>
                        <?= $button->renderButton($row["instance_primary_key"], $row) ?>
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
                <?php if (!empty($key) && !is_array($val)): ?>
            <input type="hidden" name="<?= $key ?>" value="<?= $val ?>"/>
                <?php endif ?>
            <?php endforeach ?>
            <?php foreach ($processed as $key => $val): ?>
                <?php if (!empty($key) && $key !== 'current_page' && $key !== 'id' && !is_array($val)): ?>
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
