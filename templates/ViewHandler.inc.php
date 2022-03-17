<?php

use A2billing\Forms\FormHandler;
use A2billing\Table;

/**
 * @var A2billing\Forms\Formhandler $this
 * @var array $processed
 * @var array $list
 * @var string $stitle
 * @var string $letter
 * @var string $current_page
 * @var int $popup_select
 */
global $stitle;
global $letter;
global $current_page;
global $popup_select;

getpost_ifset(['stitle', 'letter', 'current_page', 'popup_select']);
/**
 * @var string $stitle
 * @var string $letter
 * @var string $current_page
 * @var int $popup_select
 */
$hasActionButtons = ($this->FG_ENABLE_DELETE_BUTTON || $this->FG_ENABLE_INFO_BUTTON || $this->FG_ENABLE_EDIT_BUTTON || $this->FG_OTHER_BUTTON1 || $this->FG_OTHER_BUTTON2 || $this->FG_OTHER_BUTTON3 || $this->FG_OTHER_BUTTON4 || $this->FG_OTHER_BUTTON5);
?>

<?php if( $popup_select < 1 && ($this->FG_LIST_ADDING_BUTTON1 || $this->FG_LIST_ADDING_BUTTON2)): ?>
<div class="row pb-3 justify-content-end align-items-center">
    <?php if($this->FG_LIST_ADDING_BUTTON1): ?>
        <div class="col-auto">
            <a href="<?= $this->FG_LIST_ADDING_BUTTON_LINK1 ?>" class="text-decoration-none">
                <?= $this->FG_LIST_ADDING_BUTTON_MSG1 ?>
                <?php if ($this->FG_LIST_ADDING_BUTTON_IMG1): ?>
                <img src="<?= $this->FG_LIST_ADDING_BUTTON_IMG1 ?>" alt="<?= $this->FG_LIST_ADDING_BUTTON_ALT1 ?>">
                <?php endif ?>
            </a>
        </div>
    <?php endif ?>
    <?php if($this->FG_LIST_ADDING_BUTTON2): ?>
        <div class="col-auto">
            <a href="<?= $this->FG_LIST_ADDING_BUTTON_LINK2 ?>" class="text-decoration-none">
                <?= $this->FG_LIST_ADDING_BUTTON_MSG2 ?>
                <?php if ($this->FG_LIST_ADDING_BUTTON_IMG2): ?>
                    <img src="<?= $this->FG_LIST_ADDING_BUTTON_IMG2 ?>" alt="<?= $this->FG_LIST_ADDING_BUTTON_ALT2 ?>">
                <?php endif ?>
            </a>
        </div>
    <?php endif ?>
</div>
<?php endif ?>


<?php if (empty($list)): ?>
<div class="row pb-3 justify-content-center">
    <div class="col-8">
        <?= $this -> CV_NO_FIELDS ?>
    </div>
</div>
<?php return ?>
<?php endif ?>

<?php if ($this->CV_DO_ARCHIVE_ALL): ?>
<div class="row pb-3">
    <div class="col">
        <form name="theFormFilter" action="">
            <input type="hidden" name="atmenu" value="<?= $processed['atmenu'] ?>"/>
            <input type="hidden" name="popup_select" value="<?= $processed['popup_select'] ?>"/>
            <input type="hidden" name="popup_formname" value="<?= $processed['popup_formname'] ?>"/>
            <input type="hidden" name="popup_fieldname" value="<?= $processed['popup_fieldname'] ?>"/>
            <input type="hidden" name="archive" value="true"/>
            <button type="submit" class="btn btn-primary" onclick="return confirm('This action will archive the data, Are you sure?')">
                <?= gettext("Archiving All");?>
            </button>
        </form>
    </div>
</div>
<?php endif ?>

<?php if ($this -> FG_FILTER_APPLY || $this -> FG_FILTER_APPLY2): ?>
<div class="row pb-3">
<form name="theFormFilter" action="" class="col">
    <input type="hidden" name="atmenu" value="<?= $processed['atmenu'] ?>"/>
    <input type="hidden" name="popup_select" value="<?= $processed['popup_select'] ?>"/>
    <input type="hidden" name="popup_formname" value="<?= $processed['popup_formname'] ?>"/>
    <input type="hidden" name="popup_fieldname" value="<?= $processed['popup_fieldname'] ?>"/>
    <input type="hidden" name="form_action" value="<?= $this->FG_FILTER_FORM_ACTION ?>"/>
    <?php foreach ($processed as $key => $val): ?>
        <?php if (!empty($key) && $key !== 'current_page' && $key !== 'id'): ?>
        <input type="hidden" name="<?= $key?>" value="<?= $val?>"/>
        <?php endif ?>
    <?php endforeach ?>
    <div class="row pb-3 align-items-center">
        <?php if ($this->FG_FILTER_APPLY): ?>
            <?php $pu = explode(",", trim($this->FG_FILTERPOPUP[1] ?? "", ", ")) ?>
        <div class="col-auto">
            <label for="filterprefix" class="form-label d-inline w-50">
                <?= gettext("Filter on") ?>
                <?= $this->FG_FILTERFIELDNAME ?>:
            </label>
            <input type="hidden" name="filterfield" value="<?= $this->FG_FILTERFIELD?>"/>
            <input
                type="text"
                id="filterprefix"
                name="filterprefix"
                value="<?= $processed['filterprefix'] ?>"
                class="form-control form-control-sm d-inline w-50"
            /><?php if ($this -> FG_FILTERTYPE === 'POPUPVALUE'): ?>&nbsp;<a href="<?= $this->FG_FILTERPOPUP[0] ?>" data-window-name="<?= trim($pu[0], "'\" ") ?>" data-popup-options="<?= trim($pu[1], "'\" ") ?>" class="badge bg-primary popup_trigger" aria-label="open a popup to select an item" >&gt;</a><?php endif ?>
        </div>
        <?php endif ?>

        <?php if ($this->FG_FILTER_APPLY2): ?>
            <?php $pu = explode(",", trim($this->FG_FILTERPOPUP2[1] ?? "", ", ")) ?>
        <div class="col-auto">
            <label for="filterprefix2" class="form-label d-inline w-50">
                <?= gettext("Filter on");?>
                <?= $this->FG_FILTERFIELDNAME2 ?>:
            </label>
            <input type="hidden" name="filterfield2" value="<?= $this->FG_FILTERFIELD2?>"/>
            <input
                type="text"
                id="filterprefix2"
                name="filterprefix2"
                value=""
                class="form-control form-control-sm d-inline w-50"
            /><?php if ($this->FG_FILTERTYPE2 === 'POPUPVALUE'): ?>&nbsp;<a href="<?= $this->FG_FILTERPOPUP2[0] ?>" data-window-name="<?= trim($pu[0], "'\" ") ?>" data-popup-options="<?= trim($pu[1], "'\" ") ?>" class="badge bg-primary popup_trigger" aria-label="open a popup to select an item" >&gt;</a><?php endif ?>
        </div>
        <?php endif ?>
        <div class="col-auto">
            <button type="submit" class="btn btn-sm btn-primary"><?= gettext("APPLY FILTER ") ?></button>
        </div>
    </div>
</form>
</div>
<?php endif ?>

<div class="row pb-3"><div class="col">
<table class="table table-bordered table-striped table-hover caption-top <?php if ($popup_select): ?>table-sm<?php endif ?>">
    <caption>
        <?= $this->CV_TITLE_TEXT ?> – <?= $this->FG_NB_RECORD ?> <?= gettext("Records") ?>
    </caption>
    <thead>
        <tr>
            <?php foreach ($this->FG_LIST_TABLE_CELLS as $row): ?>
            <th>
                <?php if ($row["sortable"]): ?>
                <a class="sort <?= $this->FG_ORDER === $row["field"] ? strtolower($this->FG_SENS) : "" ?>" href="<?= "?stitle=$stitle&atmenu=$processed[atmenu]&current_page=$current_page&letter=$letter&popup_select=$processed[popup_select]&order=$row[field]&sens=" . ($this->FG_SENS === "ASC" ? "DESC" : "ASC") . $this-> CV_FOLLOWPARAMETERS ?>">
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
            <?php foreach($this->FG_LIST_TABLE_CELLS as $j=> $row): ?>
            <?php
            if (str_starts_with($row["type"], "lie")) {
                $options = (new Table($row["sql_table"], $row["sql_columns"]))->get_list($this->DBHandle, str_replace("%id", $item[$j - $k], $row["sql_clause"]), null, null, null, null, null, 10);
                $record_display = $row["sql_display"];
                $record_display = preg_replace_callback("/%([0-9]+)/", function ($m) use ($options) {
                    if (is_array($options) && isset($options[0][$m[1] - 1])) {
                        return str_replace("%$m[1]", $options[0][$m[1] - 1], $m[0]);
                    } else {
                        return str_replace("%$m[1]", "", $m[0]);
                    }
                }, $record_display);
                if (trim($record_display) === "") {
                    $record_display = $item[$j - $k] ?: "n/a";
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
                $string_to_eval = preg_replace_callback("/%([0-9]+)/", function ($m) use ($item) {
                    return str_replace("%$m[1]", empty($item[$m[1]]) ? 0 : $item[$m[1]], $m[0]);
                }, $row["code"]);
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
            $origlist[$num][$j - $k] = $item[$j - $k];
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
                <?php if($this->FG_ENABLE_INFO_BUTTON): ?>
                    <a href="<?= $this->FG_INFO_LINK?><?= $item["id"] ?>">
                        <img alt="<?= $this->FG_INFO_ALT ?>" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAJGSURBVDjLjdJLSNRBHMDx78yqLZaKS75DPdgDDaFDbdJmde5QlhCJGxgpRJfqEEKnIsJLB7skQYQKZaSmdLaopPCgEvSCShCMzR5a7oq7/3l12RVtjfzBMA/4fWZ+MyOccwBM3g8HEbIdfCEhfAFnLVapOa28Uevpjrqz/WOsERJgsu9Uq5CZQzgqrJfo9BajNd5irEYn4p3OUiFExtCLmw2tawFi4l5zUMjMIau9u7K+qxeoAcoAA0wDb2OPwmfA16LiiaOHLj1edRLpkO3WmIis7+oBDgJbgQ2AH6gC6jY19N62RkcctKeVIJAhp9QgUA3kJXdONZVcq9JxPSgQoXRAyIDRth8oAXQyKdWnoCKrTD9CBv4GMqx1WGNZkeRWJKbG2hiD1Cb9FbTnzWFdY/LCdLKlgNQ84gyNKqHm0gDjqVHnxDHgA/B9RQkpaB6YklkZl62np9KBhOqwjpKFgeY2YAz4BESBWHI8Hhs6PVVSvc3v98ye4fP7T676B845nt040ip98qpWJmI9PWiU6bfWgXGN2YHcKwU7tsuc4kpUPMbU0+f8+vKt+Pitl7PLAMDI9cNBoB0hQwICzjqUp6MZvsy8yvp95BRuQUjJ75mPvH4wYo1NlJ64Mza7DPwrhi8cCOeXl/aUB4P4c/NJxKLMvpngycCrzxVFG2v/CwAMnguF80oLe8p27cQh+fnpPV/fTc95S6piXQDAw7a9YbWkezZXFbAwMx/xPFXb1D3+Y90AQF/L7kAsri9mZ4lrTd0TcYA/Kakr+x2JSPUAAAAASUVORK5CYII=">
                    </a>
                <?php endif ?>
                <?php if($this->FG_ENABLE_EDIT_BUTTON): ?>
                    <?php
                    $check = true;
                    $condition_eval = preg_replace_callback(
                        "/col([0-9])/i",
                        function ($m) use ($item, &$check) {
                            $check = false;
                            $tmp = str_replace("col$m[1]", $item[$m[1]], $m[0]);
                            $check = eval("return $tmp;");
                            return $tmp;
                        },
                        // only used in FG_var_invoice.inc and FG_var_receipt.inc
                        $this->FG_EDIT_BUTTON_CONDITION
                    );
                    ?>
                    <?php if($check): ?>
                        <a href="<?= $this->FG_EDITION_LINK?><?= $item["id"]?>">
                            <img alt="<?= _("Edit this ") . $this->FG_INSTANCE_NAME ?>" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAFUSURBVDjLrZM/SAJxGIZdWwuDlnCplkAEm1zkaIiGFFpyMIwGK5KGoK2lphDKkMDg3LLUSIJsSKhIi+684CokOtTiMizCGuzEU5K3vOEgKvtBDe/2Pc8H3x8NAM1fQlx4H9M3pcOWp6TXWmM8A7j0629v1nraiAVC0IrrwATKIgs5xyG5QiE+Z4iQdoeU2oAsnqCSO1NSTu+D9VhqRLD8nIB8F0Q2MgmJDyipCzjvYJkIfpN2UBLG8MpP4dxvQ3ZzGuyyBQ2H+AnOOCBd9aL6soh81A5hyYSGWyCFvxUcerqI4S+CvYVOFPMHxLAq8I3qdHVY5LbBhJzEsCrwutpRFBlUHy6wO2tEYtWAzLELPN2P03kjfj3luqDycV2F8AgefWbEnVqEHa2IznSD6BdsVDNStB0lfh0FPoQjdx8RrAqGzC0YprSgxzsUMOY2bf37N/6Ud1Vc9yYcH50CAAAAAElFTkSuQmCC">
                        </a>
                    <?php endif ?>
                <?php endif ?>
                <?php if($this->FG_ENABLE_DELETE_BUTTON && !in_array($item["id"], $this->FG_DELETION_FORBIDDEN_ID)): ?>
                    <?php
                    $check = true;
                    $condition_eval = preg_replace_callback(
                        "/col([0-9])/i",
                        function ($m) use ($item, &$check) {
                            $check = false;
                            $tmp = str_replace("col$m[1]", $item[$m[1]], $m[0]);
                            $check = eval("return $tmp;");
                            return $tmp;
                        },
                        $this->FG_DELETE_BUTTON_CONDITION
                    );
                    ?>
                    <?php if ($check): ?>
                        <a href="<?= $this->FG_DELETION_LINK?><?= $item["id"]?>">
                            <img alt="<?= _("Delete this ") . $this->FG_INSTANCE_NAME ?>" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAIhSURBVDjLlZPrThNRFIWJicmJz6BWiYbIkYDEG0JbBiitDQgm0PuFXqSAtKXtpE2hNuoPTXwSnwtExd6w0pl2OtPlrphKLSXhx07OZM769qy19wwAGLhM1ddC184+d18QMzoq3lfsD3LZ7Y3XbE5DL6Atzuyilc5Ciyd7IHVfgNcDYTQ2tvDr5crn6uLSvX+Av2Lk36FFpSVENDe3OxDZu8apO5rROJDLo30+Nlvj5RnTlVNAKs1aCVFr7b4BPn6Cls21AWgEQlz2+Dl1h7IdA+i97A/geP65WhbmrnZZ0GIJpr6OqZqYAd5/gJpKox4Mg7pD2YoC2b0/54rJQuJZdm6Izcgma4TW1WZ0h+y8BfbyJMwBmSxkjw+VObNanp5h/adwGhaTXF4NWbLj9gEONyCmUZmd10pGgf1/vwcgOT3tUQE0DdicwIod2EmSbwsKE1P8QoDkcHPJ5YESjgBJkYQpIEZ2KEB51Y6y3ojvY+P8XEDN7uKS0w0ltA7QGCWHCxSWWpwyaCeLy0BkA7UXyyg8fIzDoWHeBaDN4tQdSvAVdU1Aok+nsNTipIEVnkywo/FHatVkBoIhnFisOBoZxcGtQd4B0GYJNZsDSiAEadUBCkstPtN3Avs2Msa+Dt9XfxoFSNYF/Bh9gP0bOqHLAm2WUF1YQskwrVFYPWkf3h1iXwbvqGfFPSGW9Eah8HSS9fuZDnS32f71m8KFY7xs/QZyu6TH2+2+FAAAAABJRU5ErkJggg==">
                        </a>
                    <?php endif ?>
                <?php endif ?>
                <?php for ($b = 1; $b <= 5; $b++):
                    if (property_exists($this, "FG_OTHER_BUTTON$b") && !empty($this->{"FG_OTHER_BUTTON$b"})):
                        $check = true;
                        $condition_eval = preg_replace_callback(
                            "/\|col([0-9]+)\|/i",
                            function ($m) use ($item, &$check) {
                                $check = false;
                                $tmp = str_replace("|col$m[1]|", $item[$m[1]], $m[0]);
                                $check = eval("return $tmp;");
                                return $tmp;
                            },
                            $this->{"FG_OTHER_BUTTON{$b}_CONDITION"}
                        );
                        $new_link = $this->{"FG_OTHER_BUTTON{$b}_LINK"};
                        if ($check) {
                            $new_link = str_replace("|param|", $item["id"], $new_link);
                        }
                        $new_link = preg_replace_callback(
                            "/\|col([0-9]+)\|/i",
                            function ($m) use ($item) {
                                return str_replace("|col$m[1]|", $item[$m[1]], $m[0]);
                            },
                            $new_link
                        );
                        $extra_html = "";
                        $id = $this->{"FG_OTHER_BUTTON{$b}_HTML_ID"};
                        if (!empty($id) && !empty($origlist[$num])) {
                            for ($h = count($item); $h >= 0; $h--) {
                                $id = str_replace("|col$h|", $origlist[$num][$h], $id);
                            }
                            $extra_html .= " id='$id' ";
                        }

                        if (substr($new_link, -1) === "=") {
                            $new_link .= $item["id"];
                        }

                        $class = $this->{"FG_OTHER_BUTTON{$b}_HTML_CLASS"};
                        if (!empty($class)) {
                            $extra_html .= " class='$class' ";
                        }

                        $img = $this->{"FG_OTHER_BUTTON{$b}_IMG"};
                        ?>
                        <a href="<?= $new_link ?>" <?= $extra_html ?>>
                            <?php if (empty($img)): ?>
                                <?= $this->{"FG_OTHER_BUTTON{$b}_ALT"} ?>
                            <?php else: ?>
                                <img src="<?= $this->{"FG_OTHER_BUTTON{$b}_IMG"} ?>" alt="<?= $this->{"FG_OTHER_BUTTON{$b}_ALT"} ?>" title="<?= $this->{"FG_OTHER_BUTTON{$b}_ALT"} ?>">
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
</div></div>
<?php if ($this->CV_DISPLAY_BROWSE_PAGE): ?>
<div class="row pb-3">
    <div class="col">
        <?= FormHandler::printPages($this->CV_CURRENT_PAGE + 1, $this->FG_NB_RECORD_MAX, "?stitle=$stitle&atmenu=$processed[atmenu]&current_page=%s&filterprefix=$processed[filterprefix]&order=$processed[order]&sens=$processed[sens]&mydisplaylimit=$processed[mydisplaylimit]&popup_select=$processed[popup_select]&letter=$letter$this->CV_FOLLOWPARAMETERS") ?>
    </div>
</div>
<?php endif ?>

<div class="row pb-3 justify-content-start align-items-center">
    <?php if ($this->CV_DISPLAY_RECORD_LIMIT): ?>
    <div class="col-4">
        <form id="displaylimit_form" action="">
            <label for="displaylimit" class="form-label d-inline"><?= gettext("Display");?></label>
            <input type="hidden" name="id" value="<?= $processed["id"] ?>"/>
            <input type="hidden" name="stitle" value="<?= $stitle ?>"/>
            <input type="hidden" name="form_action" value="edit"/>
            <input type="hidden" name="current_page" value="0"/>
            <?php foreach ($processed as $key => $val): ?>
                <?php if ($key !== 'current_page' && $key !== 'id'): ?>
                    <input type="hidden" name="<?= $key ?>" value="<?= $val ?>">
                <?php endif ?>
            <?php endforeach ?>
            <select id="displaylimit" name="mydisplaylimit" size="1" class="form-select form-select-sm d-inline w-50">
                <option value="10" <?= $_SESSION["$this->FG_TABLE_NAME-displaylimit"] < 50 ? 'selected="selected"' : "" ?>>10</option>
                <option value="50" <?= $_SESSION["$this->FG_TABLE_NAME-displaylimit"] == 50 ? 'selected="selected"' : "" ?>>50</option>
                <option value="100" <?= $_SESSION["$this->FG_TABLE_NAME-displaylimit"] == 100 ? 'selected="selected"' : "" ?>>100</option>
                <option value="ALL" <?= $_SESSION["$this->FG_TABLE_NAME-displaylimit"] > 100 ? 'selected="selected"' : "" ?>>All</option>
            </select>
        </form>
    </div>
    <?php endif ?>

    <?php if ($this->FG_EXPORT_CSV): ?>
    <div class="col-auto">
        <a href="export_csv.php?var_export=<?= $this->FG_EXPORT_SESSION_VAR ?>&amp;var_export_type=type_csv" target="_blank" class="text-decoration-none">
            <img alt="" height="32" src="data:image/gif;base64,R0lGODlhQABFAOMIADF+VEaKY3CdgZecl8bLxuLl4vL08f///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEKAAgALAAAAABAAEUAAAT+8MhJq7046827/2BljGRpnugZdsXgvnAszzBhrFtb7Hzv/8Cdy4bLtIoZw4DAvCErx6dFWTA0pZModkI9WIlY7dY7KEi+zqd42z1f1YMxt0xBw+Vnev2NW2Pbe2ArflKAgWkghE+Gh4NxeIyNiY9ykZIeikhKNikjLYIcmUVKNDNmmJRJBgUEpxtMsLGyBHosqWesBAICAb0BBB2dJjofOrC8vsm+wBzCJrVTq0y7ytXVzCEkZyS10rrI1uHWnClcwnOuBOLr60uzsBPvsK6A6uz3yu7y8fKt6PH4Avbq508CwTT1BAbE9qeWPYXsGBZyWG2XRWsWqSmTuIjiRgr+BaohBNdL3zt+8uh5VIaIJMMB1UzOQvlOZTqMIH0JSBMyJkGas2wCtObqADJsBkguq7RS2c4sAQRQePhxTMJwDAW46jnO3Agvdc7luSluKxelyWTKAirr1AiHAOLKnSvVAlWf/djGMsMt3dy/cSVyDac2lt55JeACnlvUC9pkHEGcULwYQOS7VXGgoAz4qUEKjweK9eKsb7zKAOgFUE34p8F+VUwbrPwy6tTWeV+nTOy3M0+5Wef6KgxPd03epwEXFSDXs5cAgCNjKE1s9l+GBK5Pje6h9CrOAHaWYC7cBHngwbxXP5AdtXvUxJm4gU1iffv3+OfGJ0cw9vfe+QWiuB8J/dUHXoDuSefdCfYhGGBkCzJ4oIOLkRNhJ+t5kcsA51Eo14AjFOgJNBd402F+IH6x24iNaUALLBzyAt9BIdLH4ge0+BCLC7ugd6EwGeZQRhA9IPYjhiRqoEMJRO5wJJBJGjEAdTw8iWSLUlqppYFYYtACItts6QwPUXo5pQZiotBDmRd8QtCbcL7JpgUtlGLnnXjC0CUefPbp55+AThABADs="/>
            <?= gettext("Export CSV") ?>
        </a>
    </div>
    <?php endif ?>

    <?php if ($this->FG_EXPORT_XML): ?>
    <div class="col-auto">
        <a href="export_csv.php?var_export=<?= $this->FG_EXPORT_SESSION_VAR ?>&amp;var_export_type=type_xml" target="_blank" class="text-decoration-none">
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
