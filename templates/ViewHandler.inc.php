<?php
namespace A2billing\Forms;

use A2billing\Table;

/**
 * @var FormHandler $form
 * @var array $processed
 * @var array $list
 * @var string $letter
 * @var string $current_page
 * @var int $popup_select
 * @var bool $hasActionButtons
 */

$cached_options = [];
$origlist = [];
?>

<?php if (($form -> FG_FILTER_ENABLE || $form -> FG_FILTER2_ENABLE) || ($popup_select < 1 && ($form->FG_LIST_ADDING_BUTTON1 || $form->FG_LIST_ADDING_BUTTON2))): ?>
<div class="row pb-3 align-items-end">
    <?php if ($form -> FG_FILTER_ENABLE || $form -> FG_FILTER2_ENABLE): ?>
    <form name="theFormFilter" action="" class="col">
        <input type="hidden" name="popup_select" value="<?= $processed['popup_select'] ?>"/>
        <input type="hidden" name="popup_formname" value="<?= $processed['popup_formname'] ?>"/>
        <input type="hidden" name="popup_fieldname" value="<?= $processed['popup_fieldname'] ?>"/>
        <input type="hidden" name="form_action" value="list"/>
        <?php foreach ($processed as $key => $val): ?>
            <?php if (!empty($key) && $key !== 'current_page' && $key !== 'id'): ?>
            <input type="hidden" name="<?= $key?>" value="<?= $val?>"/>
            <?php endif ?>
        <?php endforeach ?>
        <div class="row align-items-end">
            <?php if ($form->FG_FILTER_ENABLE): ?>
            <div class="col-auto">
                <label for="filterprefix" class="form-label">
                    <?= gettext("Filter on") ?>
                    <?= $form->FG_FILTER_LABEL ?>:
                </label>
                <input
                    type="text"
                    id="filterprefix"
                    name="filterprefix"
                    value="<?= $processed['filterprefix'] ?>"
                    class="form-control form-control-sm"
                />
            </div>
            <?php endif ?>

            <?php if ($form->FG_FILTER2_ENABLE): ?>
            <div class="col-auto">
                <label for="filterprefix2" class="form-label">
                    <?= gettext("Filter on");?>
                    <?= $form->FG_FILTER2_LABEL ?>:
                </label>
                <input
                    type="text"
                    id="filterprefix2"
                    name="filterprefix2"
                    value=""
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
    <?php if ($popup_select < 1 && $form->FG_LIST_ADDING_BUTTON1 && !empty($form->FG_LIST_ADDING_BUTTON_MSG1)): ?>
        <div class="col-auto ms-auto">
            <a href="<?= $form->FG_LIST_ADDING_BUTTON_LINK1 ?>" class="text-decoration-none">
                <?= $form->FG_LIST_ADDING_BUTTON_MSG1 ?>
                <?php if (!empty($form->FG_LIST_ADDING_BUTTON_IMG1)): ?>
                    <img src="<?= $form->FG_LIST_ADDING_BUTTON_IMG1 ?>" alt="<?= $form->FG_LIST_ADDING_BUTTON_ALT1 ?? "" ?>">
                <?php endif ?>
            </a>
        </div>
    <?php endif ?>
    <?php if($popup_select < 1 && $form->FG_LIST_ADDING_BUTTON2 && !empty($form->FG_LIST_ADDING_BUTTON_MSG2)): ?>
        <div class="col-auto ms-auto">
            <a href="<?= $form->FG_LIST_ADDING_BUTTON_LINK2 ?>" class="text-decoration-none">
                <?= $form->FG_LIST_ADDING_BUTTON_MSG2 ?>
                <?php if (!empty($form->FG_LIST_ADDING_BUTTON_IMG2)): ?>
                    <img src="<?= $form->FG_LIST_ADDING_BUTTON_IMG2 ?>" alt="<?= $form->FG_LIST_ADDING_BUTTON_ALT2 ?? "" ?>">
                <?php endif ?>
            </a>
        </div>
    <?php endif ?>
</div>
<?php endif ?>

<div class="row pb-3">
    <div class="col table-responsive">
        <table class="table table-bordered table-striped table-hover caption-top <?php if ($popup_select): ?>table-sm<?php endif ?>">
            <caption>
                <?= $form->CV_TITLE_TEXT ?> – <?= $form->FG_LIST_VIEW_ROW_COUNT ?> <?= gettext("Records") ?>
            </caption>
            <thead>
                <tr>
                    <?php foreach ($form->FG_LIST_TABLE_CELLS as $row): ?>
                    <th>
                        <?php if ($row["sortable"]): ?>
                        <a class="sort <?= $form->FG_QUERY_ORDERBY_COLUMNS[0] === $row["field"] ? strtolower($form->FG_QUERY_DIRECTION) : "" ?>" href="<?= "?current_page=$current_page&amp;letter=$letter&amp;popup_select=$processed[popup_select]&amp;order=$row[field]&amp;sens=" . ($form->FG_QUERY_DIRECTION === "ASC" ? "DESC" : "ASC") . (str_starts_with($form->CV_FOLLOWPARAMETERS, "&") ? "" : "&amp;") . $form->CV_FOLLOWPARAMETERS ?>">
                        <?php endif ?>
                            <?= $row["header"] ?>
                        <?php if ($row["sortable"]): ?>
                        </a>
                        <?php endif?>
                    </th>
                    <?php endforeach ?>
                    <?php if ($hasActionButtons): ?>
                    <th>
                        <strong> <?= gettext("Action") ?></strong>
                    </th>
                    <?php endif ?>

                </tr>
            </thead>
            <tbody>
            <?php foreach ($list as $num=>$item): ?>
                <tr>
                <?php $k=0 ?>
                <?php foreach($form->FG_LIST_TABLE_CELLS as $j=> $row):
                    $origlist[$num][$j - $k] = $item[$j - $k];
                    if (str_starts_with($row["type"], "lie")) {
                        if (empty($cached_options[$row["field"]])) {
                            $options = (new Table($row["sql_table"], $row["sql_columns"]))->get_list($form->DBHandle, str_replace("%id", $item[$j - $k], $row["sql_clause"]));
                            $cached_options[$row["field"]] = $options;
                        } else {
                            $options = $cached_options[$row["field"]];
                        }
                        $record_display = $row["sql_display"];
                        $record_display = preg_replace_callback(
                            "/%([0-9]+)/",
                            fn ($m) => str_replace($m[0], $options[0][$m[1] - 1] ?? "", $m[0]),
                            $record_display
                        );
                        if (trim($record_display) === "") {
                            $record_display = "n/a";
                        }
                        if ($row["type"] === "lie_link") {
                            if (is_array($options)) {
                                $link = $row["href"] . (str_contains($row["href"], 'form_action') ? "?" : "?form_action=ask-edit&") . "id=" . $options[0][1];
                                if (!$popup_select) {
                                    $record_display = "<a class='text-decoration-underline' href='$link'>$record_display</a>";
                                }
                            }
                        }
                    } elseif ($row["type"] === "eval") {
                        // this exists only so that FG_var_card.inc.php can left pad a card number with zeroes
                        $string_to_eval = preg_replace_callback(
                            "/%([0-9]+)/",
                            fn ($m) => str_replace("%$m[1]", $item[$m[1]] ?? "0", $m[0]),
                            $row["code"]
                        );
                        $record_display = eval("return $string_to_eval;");
                    } elseif ($row["type"] === "list") {
                        $select_list = $row["options"];
                        $record_display = $select_list[$item[$j - $k]][0];
                    } elseif ($row["type"] === "list-conf") {
                        $select_list = $row["options"];
                        $key_config =  $item[$j - $k + 3];
                        $record_display = $select_list[$key_config][0];

                    } elseif ($row["type"] === "value") {
                        $record_display = $row["value"];
                        $k++;
                    } else {
                        $record_display = $item[$j - $k];
                    }

                    /**********************   IF LENGTH OF THE VALUE IS TOO LONG IT MIGHT BE CUT ************************/
                    if ($row["maxsize"] > 0 && strlen($record_display) > $row["maxsize"]) {
                        $record_display = substr($record_display, 0, $row["maxsize"]) . "…";
                    }
                    $item[$j - $k] = $record_display;
                    ?>
                    <td>
                    <?php if (!empty($row["function"]) && is_callable($row["function"])): ?>
                        <?php call_user_func($row["function"], $record_display) ?>
                    <?php else: ?>
                        <?= $record_display ?>
                    <?php endif ?>
                    </td>
                <?php endforeach ?>
                <?php if ($hasActionButtons): ?>
                    <td>
                    <?php if($form->FG_ENABLE_INFO_BUTTON): ?>
                        <a href="<?= $form->FG_INFO_BUTTON_LINK?><?= $item["instance_primary_key"] ?? "" ?>">
                            <img alt="<?= _("About this ") . $form->FG_INSTANCE_NAME ?>" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAJGSURBVDjLjdJLSNRBHMDx78yqLZaKS75DPdgDDaFDbdJmde5QlhCJGxgpRJfqEEKnIsJLB7skQYQKZaSmdLaopPCgEvSCShCMzR5a7oq7/3l12RVtjfzBMA/4fWZ+MyOccwBM3g8HEbIdfCEhfAFnLVapOa28Uevpjrqz/WOsERJgsu9Uq5CZQzgqrJfo9BajNd5irEYn4p3OUiFExtCLmw2tawFi4l5zUMjMIau9u7K+qxeoAcoAA0wDb2OPwmfA16LiiaOHLj1edRLpkO3WmIis7+oBDgJbgQ2AH6gC6jY19N62RkcctKeVIJAhp9QgUA3kJXdONZVcq9JxPSgQoXRAyIDRth8oAXQyKdWnoCKrTD9CBv4GMqx1WGNZkeRWJKbG2hiD1Cb9FbTnzWFdY/LCdLKlgNQ84gyNKqHm0gDjqVHnxDHgA/B9RQkpaB6YklkZl62np9KBhOqwjpKFgeY2YAz4BESBWHI8Hhs6PVVSvc3v98ye4fP7T676B845nt040ip98qpWJmI9PWiU6bfWgXGN2YHcKwU7tsuc4kpUPMbU0+f8+vKt+Pitl7PLAMDI9cNBoB0hQwICzjqUp6MZvsy8yvp95BRuQUjJ75mPvH4wYo1NlJ64Mza7DPwrhi8cCOeXl/aUB4P4c/NJxKLMvpngycCrzxVFG2v/CwAMnguF80oLe8p27cQh+fnpPV/fTc95S6piXQDAw7a9YbWkezZXFbAwMx/xPFXb1D3+Y90AQF/L7kAsri9mZ4lrTd0TcYA/Kakr+x2JSPUAAAAASUVORK5CYII=">
                        </a>
                    <?php endif ?>
                    <?php if($form->FG_ENABLE_EDIT_BUTTON): ?>
                        <?php
                        $check = true;
                        if (!empty($form->FG_EDIT_BUTTON_CONDITION)) {
                            $condition_eval = preg_replace_callback(
                                "/\\|col([0-9]+)\\|/i",
                                fn ($m) => str_replace($m[0], $item[$m[1]] ?? "", $m[0]),
                                // only used in FG_var_invoice.inc and FG_var_receipt.inc
                                $form->FG_EDIT_BUTTON_CONDITION
                            );
                            $check = eval("return $condition_eval;");
                        }
                        ?>
                        <?php if($check): ?>
                        <a href="<?= $form->FG_EDIT_BUTTON_LINK?><?= $item["instance_primary_key"] ?? "" ?>">
                            <img alt="<?= _("Edit this ") . $form->FG_INSTANCE_NAME ?>" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAFUSURBVDjLrZM/SAJxGIZdWwuDlnCplkAEm1zkaIiGFFpyMIwGK5KGoK2lphDKkMDg3LLUSIJsSKhIi+684CokOtTiMizCGuzEU5K3vOEgKvtBDe/2Pc8H3x8NAM1fQlx4H9M3pcOWp6TXWmM8A7j0629v1nraiAVC0IrrwATKIgs5xyG5QiE+Z4iQdoeU2oAsnqCSO1NSTu+D9VhqRLD8nIB8F0Q2MgmJDyipCzjvYJkIfpN2UBLG8MpP4dxvQ3ZzGuyyBQ2H+AnOOCBd9aL6soh81A5hyYSGWyCFvxUcerqI4S+CvYVOFPMHxLAq8I3qdHVY5LbBhJzEsCrwutpRFBlUHy6wO2tEYtWAzLELPN2P03kjfj3luqDycV2F8AgefWbEnVqEHa2IznSD6BdsVDNStB0lfh0FPoQjdx8RrAqGzC0YprSgxzsUMOY2bf37N/6Ud1Vc9yYcH50CAAAAAElFTkSuQmCC">
                        </a>
                        <?php endif ?>
                    <?php endif ?>
                    <?php if($form->FG_ENABLE_DELETE_BUTTON && !in_array($item["instance_primary_key"], $form->FG_DELETION_FORBIDDEN_ID)): ?>
                        <?php
                        $check = true;
                        if (!empty($form->FG_DELETE_BUTTON_CONDITION)) {
                            $condition_eval = preg_replace_callback(
                                "/\\|col([0-9]+)\\|/i",
                                fn ($m) => str_replace($m[0], $item[$m[1]] ?? "", $m[0]),
                                $form->FG_DELETE_BUTTON_CONDITION
                            );
                            $check = eval("return $condition_eval;");
                        }
                        ?>
                        <?php if ($check): ?>
                        <a href="<?= $form->FG_DELETE_BUTTON_LINK?><?= $item["instance_primary_key"] ?? "" ?>">
                            <img alt="<?= _("Delete this ") . $form->FG_INSTANCE_NAME ?>" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAIhSURBVDjLlZPrThNRFIWJicmJz6BWiYbIkYDEG0JbBiitDQgm0PuFXqSAtKXtpE2hNuoPTXwSnwtExd6w0pl2OtPlrphKLSXhx07OZM769qy19wwAGLhM1ddC184+d18QMzoq3lfsD3LZ7Y3XbE5DL6Atzuyilc5Ciyd7IHVfgNcDYTQ2tvDr5crn6uLSvX+Av2Lk36FFpSVENDe3OxDZu8apO5rROJDLo30+Nlvj5RnTlVNAKs1aCVFr7b4BPn6Cls21AWgEQlz2+Dl1h7IdA+i97A/geP65WhbmrnZZ0GIJpr6OqZqYAd5/gJpKox4Mg7pD2YoC2b0/54rJQuJZdm6Izcgma4TW1WZ0h+y8BfbyJMwBmSxkjw+VObNanp5h/adwGhaTXF4NWbLj9gEONyCmUZmd10pGgf1/vwcgOT3tUQE0DdicwIod2EmSbwsKE1P8QoDkcHPJ5YESjgBJkYQpIEZ2KEB51Y6y3ojvY+P8XEDN7uKS0w0ltA7QGCWHCxSWWpwyaCeLy0BkA7UXyyg8fIzDoWHeBaDN4tQdSvAVdU1Aok+nsNTipIEVnkywo/FHatVkBoIhnFisOBoZxcGtQd4B0GYJNZsDSiAEadUBCkstPtN3Avs2Msa+Dt9XfxoFSNYF/Bh9gP0bOqHLAm2WUF1YQskwrVFYPWkf3h1iXwbvqGfFPSGW9Eah8HSS9fuZDnS32f71m8KFY7xs/QZyu6TH2+2+FAAAAABJRU5ErkJggg==">
                        </a>
                        <?php endif ?>
                    <?php endif ?>
                    <?php for ($b = 1; $b <= 5; $b++):
                        if (property_exists($form, "FG_OTHER_BUTTON$b") && !empty($form->{"FG_OTHER_BUTTON$b"})):
                            $check = true;
                            if (!empty($form->{"FG_OTHER_BUTTON{$b}_CONDITION"})) {
                                $condition_eval = preg_replace_callback(
                                    "/\\|col([0-9]+)\\|/i",
                                    fn ($m) => str_replace($m[0], $item[$m[1]] ?? "", $m[0]),
                                    $form->{"FG_OTHER_BUTTON{$b}_CONDITION"}
                                );
                                $check = eval("return $condition_eval;");
                            }
                            if (!$check) {
                                continue;
                            }
                            $new_link = $form->{"FG_OTHER_BUTTON{$b}_LINK"};
                            $new_link = str_replace("|param|", $item["instance_primary_key"] ?? "", $new_link);
                            $new_link = preg_replace_callback(
                                "/\|col([0-9]+)\|/i",
                                fn ($m) => str_replace("|col$m[1]|", $item[$m[1]] ?? "", $m[0]),
                                $new_link
                            );
                            $extra_html = "";
                            $id = $form->{"FG_OTHER_BUTTON{$b}_HTML_ID"};
                            // ID can also have placeholders, unclear why we use $origlist here and not elsewhere
                            preg_replace_callback(
                                "/\|col([0-9]+)\|/",
                                fn ($m) => str_replace($m[0], $origlist[$num][$m[1]] ?? "", $m[0]),
                                $id
                            );
                            $extra_html .= " id='$id' ";

                            if (str_ends_with($new_link, "=")) {
                                $new_link .= $item["instance_primary_key"] ?? "";
                            }

                            $class = $form->{"FG_OTHER_BUTTON{$b}_HTML_CLASS"};
                            if (!empty($class)) {
                                $extra_html .= " class='$class' ";
                            }

                            $img = $form->{"FG_OTHER_BUTTON{$b}_IMG"};
                            ?>
                        <a href="<?= $new_link ?>" <?= $extra_html ?>>
                            <?php if (empty($img)): ?>
                                <?= $form->{"FG_OTHER_BUTTON{$b}_ALT"} ?>
                            <?php else: ?>
                                <img src="<?= $form->{"FG_OTHER_BUTTON{$b}_IMG"} ?>" alt="<?= $form->{"FG_OTHER_BUTTON{$b}_ALT"} ?>" title="<?= $form->{"FG_OTHER_BUTTON{$b}_ALT"} ?>">
                            <?php endif ?>
                        </a>
                        <?php endif ?>
                    <?php endfor ?>
                    </td>
                <?php endif ?>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>
<?php if ($form->CV_DISPLAY_BROWSE_PAGE): ?>
<div class="row pb-3">
    <div class="col">
        <?= FormHandler::printPages(
            $form->CV_CURRENT_PAGE + 1,
            $form->FG_LIST_VIEW_PAGE_COUNT,
            "?" . http_build_query(["current_page" => "%s", "filterprefix" => $processed["filterprefix"], "order" => $processed["order"], "sens" => $processed["sens"]], "", "&amp;") . (str_starts_with($form->CV_FOLLOWPARAMETERS, "&") ? "" : "&amp;") . $form->CV_FOLLOWPARAMETERS
//            "?current_page=%s&amp;filterprefix=$processed[filterprefix]&amp;order=$processed[order]&amp;sens=$processed[sens]&amp;mydisplaylimit=$processed[mydisplaylimit]&amp;popup_select=$processed[popup_select]&amp;letter=$letter" . (str_starts_with($form->CV_FOLLOWPARAMETERS, "&") ? "" : "&amp;") . $form->CV_FOLLOWPARAMETERS
        ) ?>
    </div>
</div>
<?php endif ?>

<div class="row pb-3 justify-content-start align-items-center">
    <?php if ($form->CV_DISPLAY_RECORD_LIMIT): ?>
    <div class="col-4">
        <form id="displaylimit_form" action="">
            <label for="displaylimit" class="form-label d-inline"><?= gettext("Display");?></label>
            <input type="hidden" name="id" value="<?= $processed["id"] ?>"/>
            <input type="hidden" name="form_action" value="list"/>
            <input type="hidden" name="current_page" value="0"/>
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
    <?php endif ?>

    <?php if ($form->FG_EXPORT_CSV): ?>
    <div class="col-auto">
        <a href="export_csv.php?var_export=<?= $form->FG_EXPORT_SESSION_VAR ?>&amp;var_export_type=type_csv" target="_blank" class="text-decoration-none">
            <img alt="" height="32" src="data:image/gif;base64,R0lGODlhQABFAOMIADF+VEaKY3CdgZecl8bLxuLl4vL08f///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEKAAgALAAAAABAAEUAAAT+8MhJq7046827/2BljGRpnugZdsXgvnAszzBhrFtb7Hzv/8Cdy4bLtIoZw4DAvCErx6dFWTA0pZModkI9WIlY7dY7KEi+zqd42z1f1YMxt0xBw+Vnev2NW2Pbe2ArflKAgWkghE+Gh4NxeIyNiY9ykZIeikhKNikjLYIcmUVKNDNmmJRJBgUEpxtMsLGyBHosqWesBAICAb0BBB2dJjofOrC8vsm+wBzCJrVTq0y7ytXVzCEkZyS10rrI1uHWnClcwnOuBOLr60uzsBPvsK6A6uz3yu7y8fKt6PH4Avbq508CwTT1BAbE9qeWPYXsGBZyWG2XRWsWqSmTuIjiRgr+BaohBNdL3zt+8uh5VIaIJMMB1UzOQvlOZTqMIH0JSBMyJkGas2wCtObqADJsBkguq7RS2c4sAQRQePhxTMJwDAW46jnO3Agvdc7luSluKxelyWTKAirr1AiHAOLKnSvVAlWf/djGMsMt3dy/cSVyDac2lt55JeACnlvUC9pkHEGcULwYQOS7VXGgoAz4qUEKjweK9eKsb7zKAOgFUE34p8F+VUwbrPwy6tTWeV+nTOy3M0+5Wef6KgxPd03epwEXFSDXs5cAgCNjKE1s9l+GBK5Pje6h9CrOAHaWYC7cBHngwbxXP5AdtXvUxJm4gU1iffv3+OfGJ0cw9vfe+QWiuB8J/dUHXoDuSefdCfYhGGBkCzJ4oIOLkRNhJ+t5kcsA51Eo14AjFOgJNBd402F+IH6x24iNaUALLBzyAt9BIdLH4ge0+BCLC7ugd6EwGeZQRhA9IPYjhiRqoEMJRO5wJJBJGjEAdTw8iWSLUlqppYFYYtACItts6QwPUXo5pQZiotBDmRd8QtCbcL7JpgUtlGLnnXjC0CUefPbp55+AThABADs="/>
            <?= gettext("Export CSV") ?>
        </a>
    </div>
    <?php endif ?>

    <?php if ($form->FG_EXPORT_XML): ?>
    <div class="col-auto">
        <a href="export_csv.php?var_export=<?= $form->FG_EXPORT_SESSION_VAR ?>&amp;var_export_type=type_xml" target="_blank" class="text-decoration-none">
            <img alt="" height="32" src="data:image/gif;base64,R0lGODlhKgAqAOMIAPRLJHW42bOqqZ7N5vfNjtfV08/n9P///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEKAAgALAAAAAAqACoAAAT+EEl0qr34zs37rkEojuRQVF7KVUVgvEMsz0MwGJaagmQvxqHY6aDrHFovA21pCwpRRckB6OvVmq4nMXpwwZYzLPA7jCKTYFkwpLy1T2YvOv3zxr5Q1Tn5pYnaZEplenJ8bWEhBYCHglspBW6GfVQuikJKQYMekJJ8iC5HjDVPepGdh1c4oVdqcI+mp2o/bqw1rpuwsZQjaraOHZynnrIlowG3wLm6dMbIHJAHwoZpxgbHv88DFdLTTDXWzhvQFtzdYy7XpRjl3ejg2OLaFwUCBvQCigb19ZLu6a/zBAAoAKAgAH0ABL7I98JfuAnjBA40aKBgxYQHEWbM8k4dQQKcFgCALChR4UKLHE3AgyhPIsGCLw2adKmkpkp1FgoQALlzp0ifOwswvEH0oYRxFkBW2HmAZ9Od02waRYA0QwZhRBvhtMq1U9abALmKlfR1alWxVz0pmWJWHtqxMNaCxfX2LYwpvrbW7aptAL62e98CEZonWWC0fvEVBmaDmuMwhFeKuyegsuXLmDP/1bRDqOfPoEOHXrzj8N4oCCIAADs="/>
            <?= gettext("Export XML") ?>
        </a>
    </div>
    <?php endif ?>
</div>

<script>
$(function() {
    $("#displaylimit").on("change", () => $("#displaylimit_form").trigger("submit"));
});
</script>
