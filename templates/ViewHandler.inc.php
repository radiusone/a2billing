<?php

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
getpost_ifset(array('stitle', 'letter', 'current_page', 'popup_select'));
?>

<?php if( $popup_select < 1 && ($this->FG_LIST_ADDING_BUTTON1 || $this->FG_LIST_ADDING_BUTTON2)): ?>
<table align="right">
    <tr align="right">
        <td align="right">
        <?php if($this->FG_LIST_ADDING_BUTTON1): ?>
            <a href="<?= $this->FG_LIST_ADDING_BUTTON_LINK1 ?>">
                <?= $this->FG_LIST_ADDING_BUTTON_MSG1 ?>
                &nbsp;&nbsp;
                <img src="<?= $this->FG_LIST_ADDING_BUTTON_IMG1 ?>" border="0" title="<?= $this->FG_LIST_ADDING_BUTTON_ALT1 ?>" alt="<?= $this->FG_LIST_ADDING_BUTTON_ALT1 ?>">
            </a>
        <?php endif ?>
        &nbsp;
        <?php if($this->FG_LIST_ADDING_BUTTON2): ?>
            <a href="<?= $this->FG_LIST_ADDING_BUTTON_LINK2 ?>">
                <?= $this -> FG_LIST_ADDING_BUTTON_MSG2?>
                &nbsp;&nbsp;
                <img src="<?= $this->FG_LIST_ADDING_BUTTON_IMG2?>" border="0" title="<?= $this->FG_LIST_ADDING_BUTTON_ALT2 ?>" alt="<?= $this->FG_LIST_ADDING_BUTTON_ALT2 ?>">
            </a>
        <?php endif ?>
        </td>
    </tr>
</table>
<?php endif ?>
<br/>

<?php if (empty($list)): ?>
    <br/><br/>
    <div align="center">
        <table width="80%" border="0" align="center">
            <tr>
                <td align="center">
                    <?= $this -> CV_NO_FIELDS;?><br>
                </td>
            </tr>
        </table>
    </div>
    <br/><br/>
    <?php return ?>
<?php endif ?>

<script>
function openURLFilter(link) {
    if(document.theFormFilter.choose_list.selectedIndex === 0){
        return false;
    }
    this.location.href = link + document.theFormFilter.choose_list.options[selInd].value;
}
</script>

<img alt="" src="data:image/gif;base64,R0lGODlhAQABAIAAAOZ3fP///yH5BAEAAAEALAAAAAABAAEAAAICTAEAOw=="/>


<div align="center" style="">
    <table width="<?= $this->FG_VIEW_TABLE_WITDH ?>" align="center" border="0" cellpadding="0" cellspacing="0">
    <?php if($this->CV_DISPLAY_LINE_TITLE_ABOVE_TABLE): ?>
        <tr>
            <td class="tdstyle_002"><span>
                <b><?= $this -> CV_TEXT_TITLE_ABOVE_TABLE ?></b></span>
            </td>
        </tr>
    <?php endif ?>
    <?php if ($this->CV_DO_ARCHIVE_ALL): ?>
        <tr>
            <td class="viewhandler_filter_td1">
                <form name="theFormFilter" action="">
                    <input type="hidden" name="atmenu" value="<?= $processed['atmenu'] ?>"/>
                    <input type="hidden" name="popup_select" value="<?= $processed['popup_select'] ?>"/>
                    <input type="hidden" name="popup_formname" value="<?= $processed['popup_formname'] ?>"/>
                    <input type="hidden" name="popup_fieldname" value="<?= $processed['popup_fieldname'] ?>"/>
                    <input type="hidden" name="archive" value="true"/>
                    <input type="submit" value="<?= gettext("Archiving All ");?>" class="form_input_button" onclick="return confirm('This action will archive the data, Are you sure?')"/>
                </form>
            </td>
        </tr>
    <?php endif ?>
    <?php if ($this->CV_DISPLAY_FILTER_ABOVE_TABLE): ?>
        <tr>
            <td class="tdstyle_002">
                <form NAME="theFormFilter">
                    <input type="hidden" name="popup_select" value="<?= $processed['popup_select']?>"/>
                    <input type="hidden" name="popup_formname" value="<?= $processed['popup_formname']?>"/>
                    <input type="hidden" name="popup_fieldname" value="<?= $processed['popup_fieldname']?>"/>
                    <select name="choose_list" size="1" class="form_input_select" style="width: 185px;" onchange="openURLFilter('<?= $this->CV_FILTER_ABOVE_TABLE_PARAM ?>')">
                        <option><?= gettext("Sort") ?></option>

                    <?php foreach ($list as $recordset): ?>
                        <option class="input" value="<?= $recordset[0]?>">
                            <?= $recordset[1] ?>
                        </option>
                    <?php endforeach ?>
                    </select>
                </form>
            </td>
        </tr>
    <?php endif ?>

        <tr>
            <td class="viewhandler_table2_td3">
                <table border="0" cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                        <td>
                            <span class="viewhandler_span1"> - <?= strtoupper($this->CV_TITLE_TEXT) ?>  - </span>
                            <span class="viewhandler_span1"> <?= $this->FG_NB_RECORD ?>  <?= gettext("Records") ?></span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <?php if ($this -> FG_FILTER_APPLY || $this -> FG_FILTER_APPLY2): ?>
        <tr>
            <td class="viewhandler_filter_td1">
            <form name="theFormFilter" action="">
                <input type="hidden" name="atmenu" value="<?= $processed['atmenu'] ?>"/>
                <input type="hidden" name="popup_select" value="<?= $processed['popup_select'] ?>"/>
                <input type="hidden" name="popup_formname" value="<?= $processed['popup_formname'] ?>"/>
                <input type="hidden" name="popup_fieldname" value="<?= $processed['popup_fieldname'] ?>"/>
                <input type="hidden" name="form_action" value="<?= $this->FG_FILTER_FORM_ACTION ?>"/>
                <input type="hidden" name="filterfield" value="<?= $this->FG_FILTERFIELD?>"/>
                <?php foreach ($processed as $key => $val): ?>
                    <?php if (!empty($key) && $key !== 'current_page' && $key !== 'id'): ?>
                        <input type="hidden" name="<?= $key?>" value="<?= $val?>"/>
                    <?php endif ?>
                <?php endforeach ?>

                <?php if ($this->FG_FILTER_APPLY): ?>
                    <label for="filterprefix" class="viewhandler_filter_on">
                        <?= gettext("FILTER ON ") ?>
                        <?= strtoupper($this->FG_FILTERFIELDNAME)?> :
                    </label>
                    <input type="text" id="filterprefix" name="filterprefix" value="<?php if(!empty($processed['filterprefix'])) echo $processed['filterprefix']; ?>" class="form_input_text">

                    <?php if ($this -> FG_FILTERTYPE === 'POPUPVALUE'): ?>
                    <a href="#" onclick="window.open('<?= $this->FG_FILTERPOPUP[0]?>popup_formname=theFormFilter&popup_fieldname=filterprefix' <?= $this->FG_FILTERPOPUP[1]?>);">
                        <img alt="" src="data:image/gif;base64,R0lGODlhDwAPAMQYAP+yPf+fEv+qLP+3Tf+pKv++Xf/Gcv+mJP+tNf+tMf+kH/+/YP+oJv+wO/+jHP/Ohf/WmP+vOv/cpv+kHf+iGf+jG/////Hw7P///wAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAABgALAAAAAAPAA8AAAVjIHaNZEmKF6auLJpiEvQYxQAgiTpiMm0Tk4pigsLMag2Co8KkFA0Lm8XCbBajDcFkWnXuBlkFk1vxpgACcYVcLqbHVKaDuFNXqwxGkUK5VyYMEQhFGAGGhxQHOS4tjTsmkDshADs="/>
                    </a>
                    <?php endif ?>
                <?php endif ?>

                <?php if ($this->FG_FILTER_APPLY2): ?>
                    &nbsp;&nbsp;&nbsp;&nbsp; - &nbsp;&nbsp;&nbsp;&nbsp;
                    <label for="filterprefix2" class="viewhandler_filter_on">
                        <?= gettext("FILTER ON");?>
                        <?= strtoupper($this->FG_FILTERFIELDNAME2)?> :
                    </label>
                    <input type="text" id="filterprefix2" name="filterprefix2" value="" class="form_input_text">
                    <input type="hidden" name="filterfield2" value="<?= $this->FG_FILTERFIELD2?>"/>
                    <?php if ($this->FG_FILTERTYPE2 === 'POPUPVALUE'): ?>
                    <a href="#" onclick="window.open('<?= $this->FG_FILTERPOPUP2[0]?>popup_formname=theFormFilter&popup_fieldname=filterprefix2' <?= $this->FG_FILTERPOPUP2[1]?>);">
                        <img alt="" src="data:image/gif;base64,R0lGODlhDwAPAMQYAP+yPf+fEv+qLP+3Tf+pKv++Xf/Gcv+mJP+tNf+tMf+kH/+/YP+oJv+wO/+jHP/Ohf/WmP+vOv/cpv+kHf+iGf+jG/////Hw7P///wAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAABgALAAAAAAPAA8AAAVjIHaNZEmKF6auLJpiEvQYxQAgiTpiMm0Tk4pigsLMag2Co8KkFA0Lm8XCbBajDcFkWnXuBlkFk1vxpgACcYVcLqbHVKaDuFNXqwxGkUK5VyYMEQhFGAGGhxQHOS4tjTsmkDshADs="/>
                    </a>
                    <?php endif ?>
                <?php endif ?>
                    <input type="submit" value="<?= gettext("APPLY FILTER ") ?>" class="form_input_button"/>
            </form>
            </td>
        </tr>
        <?php endif ?>

        <tr>
            <td>
                <table border="0" cellPadding="2" cellSpacing="2" width="100%">
                    <tr class="form_head">
                    <?php foreach ($this->FG_TABLE_COL as $row): ?>
                        <td class="tableBody" style="padding: 2px; font-weight: bold" align="center" width="<?= $row[2] ?>">
                        <?php if (strtoupper($row[4]) === "SORT"): ?>
                            <a style="color: #fff" href="<?= "?stitle=$stitle&atmenu=$processed[atmenu]&current_page=$current_page&letter=$letter&popup_select=$processed[popup_select]&order=$row[1]&sens=" . ($this->FG_SENS === "ASC" ? "DESC" : "ASC") . $this-> CV_FOLLOWPARAMETERS ?>">
                        <?php endif ?>
                                <?= $row[0] ?>
                        <?php if ($this->FG_ORDER === $row[1] && $this->FG_SENS === "ASC"): ?>
                                &nbsp;<img alt="asc" src="data:image/gif;base64,R0lGODlhDAAMALMPAO3y+u3x+cnX8Pf5/eDo9tTf8/f6/eHo9tTe8snW78DP7MLR7f///3+ZzAAzmf///yH5BAEAAA8ALAAAAAAMAAwAAAQ48LlJq2wq69xmY2AIds4HTiHZGEbZGYMKuE0XqMfhOgehIogdoqBKJCoJgWrBbDJVtajUIalYHxEAOw=="/>
                        <?php elseif ($this->FG_ORDER === $row[1] && $this->FG_SENS === "DESC"): ?>
                                &nbsp;<img alt="desc" src="data:image/gif;base64,R0lGODlhDAAMAIQMAO3y+u3x+cnX8Pf5/eDo9tTf8/f6/eHo9tTe8snW78DP7MLR7f///3+ZzAAzmf///////////////////////////////////////////////////////////////////yH5BAEKAA8ALAAAAAAMAAwAAAU84OOMZCk2aKqOzeK+bsMKSZkkstMUiC4jiFyDcPA5DgdhwIdyAIQDg9FgEDKuoyvDqu0KFeAwWCYqmR8hADs=">
                        <?php endif ?>
                        <?php if (strtoupper($row[4]) === "SORT"): ?>
                            </a>
                        <?php endif?>
                        </td>
                    <?php endforeach ?>

                    <?php if ($this->FG_DELETION || $this->FG_INFO || $this->FG_EDITION || $this->FG_OTHER_BUTTON1 || $this->FG_OTHER_BUTTON2 || $this->FG_OTHER_BUTTON3 || $this->FG_OTHER_BUTTON4 || $this->FG_OTHER_BUTTON5): ?>
                        <td width="<?= $this->FG_ACTION_SIZE_COLUMN?>" align="center" class="tableBody" >
                            <strong> <?= gettext("ACTION") ?></strong>
                        </td>
                    <?php endif ?>
                    </tr>
        <?php /**********************   START BUILDING THE TABLE WITH BROWSING VALUES ************************/ ?>
        <?php foreach ($list as $num=>$item): ?>
                    <tr bgcolor="<?= $this->FG_TABLE_ALTERNATE_ROW_COLOR[$num % 2]?>" onmouseover="bgColor='#FFDEA6'" onmouseout="bgColor='<?= $this->FG_TABLE_ALTERNATE_ROW_COLOR[$num % 2]?>'">
                <?php $k=0 ?>
                <?php foreach($this->FG_TABLE_COL as $j=>$row): ?>
                <?php
                    if (str_starts_with($row[6], "lie")) {
                        $options = (new Table($row[7], $row[8]))->get_list($this->DBHandle, str_replace("%id", $item[$j - $k], $row[9]), null, null, null, null, null, null, null, 10);
                        $field_list_sun = explode(",", $row[8]);
                        $record_display = $row[10];
                        if ($row[6] === "lie") {
                            if (is_array($options)) {
                                for ($l=1; $l <= count($field_list_sun); $l++) {
                                    $record_display = str_replace("%$l", $options[0][$l - 1], $record_display);
                                }
                            } else {
                                $record_display = "";
                            }
                        } elseif($row[6] === "lie_link") {
                            $record_display = "";
                            if (is_array($options)) {
                                $link = $row[12];
                                if (!str_contains($row[12], 'form_action')) {
                                    $link .= "?form_action=ask-edit&";
                                }
                                else {
                                    $link .= "?";
                                }
                                $link .= "id=" . $options[0][1];
                                for ($l = 1; $l <= count($field_list_sun); $l++) {
                                    $val = str_replace("%$l", $options[0][$l - 1], $record_display);
                                    $record_display = "<a href='$link'>$val</a>";
                                }
                            }
                        }
                    } elseif ($row[6]=="eval") {

                        $string_to_eval = $row[7]; // %4-%3
                        for ($ll = 15; $ll >= 0; $ll--) {
                            if ($item[$ll] === '') {
                                $item[$ll] = 0;
                            }
                            $string_to_eval = str_replace("%$ll", $item[$ll], $string_to_eval);
                        }
                        // WTAF
                        $record_display = ("return $string_to_eval;");

                    } elseif ($row[6]=="list") {

                        $select_list = $row[7];
                        $record_display = $select_list[$item[$j-$k]][0];

                    } elseif ($row[6]=="list-conf") {

                        $select_list = $row[7];
                        $key_config =  $item[$j-$k + 3];
                        $record_display = $select_list[$key_config][0];

                    } elseif ($row[6]=="value") {

                        $record_display = $row[7];
                        $k++;

                    } else {

                        $record_display = $item[$j-$k];

                    }

                    /**********************   IF LENGHT OF THE VALUE IS TOO LONG IT MIGHT BE CUT ************************/
                    if (is_numeric($row[5]) && (strlen($record_display) > $row[5])) {
                        $record_display = substr($record_display, 0, $row[5]);
                    }
                    ?>
                        <td valign="top" align="<?= $row[3] ?>" class="tableBody">
                            <?php
                            $origlist[$num][$j-$k] = $item[$j-$k];
                            $item[$j-$k] = $record_display;

                            if (isset ($row[11]) && strlen($row[11])>1) {
                                print call_user_func($row[11], $record_display);
                            } else {
                                echo stripslashes($record_display);
                            }
                            ?>
                        </td>

                <?php endforeach //$this->FG_TABLE_COL ?>

                <?php $extra_col = 1 ?>
                <?php if($this->FG_EDITION  || $this->FG_INFO || $this->FG_DELETION || $this->FG_OTHER_BUTTON1 || $this->FG_OTHER_BUTTON2 || $this->FG_OTHER_BUTTON3 || $this->FG_OTHER_BUTTON4 || $this->FG_OTHER_BUTTON5): ?>
                    <?php $extra_col = 0 ?>
                        <td align="center" valign=top class=tableBodyRight>
                    <?php if($this->FG_INFO): ?>
                        &nbsp;
                        <a href="<?= $this->FG_INFO_LINK?>
                            <?= $item[$this->FG_NB_TABLE_COL]?>">
                            <img border="0" title="<?= gettext("VIEW") ?>" alt="<?= gettext("VIEW") ?>" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAJGSURBVDjLjdJLSNRBHMDx78yqLZaKS75DPdgDDaFDbdJmde5QlhCJGxgpRJfqEEKnIsJLB7skQYQKZaSmdLaopPCgEvSCShCMzR5a7oq7/3l12RVtjfzBMA/4fWZ+MyOccwBM3g8HEbIdfCEhfAFnLVapOa28Uevpjrqz/WOsERJgsu9Uq5CZQzgqrJfo9BajNd5irEYn4p3OUiFExtCLmw2tawFi4l5zUMjMIau9u7K+qxeoAcoAA0wDb2OPwmfA16LiiaOHLj1edRLpkO3WmIis7+oBDgJbgQ2AH6gC6jY19N62RkcctKeVIJAhp9QgUA3kJXdONZVcq9JxPSgQoXRAyIDRth8oAXQyKdWnoCKrTD9CBv4GMqx1WGNZkeRWJKbG2hiD1Cb9FbTnzWFdY/LCdLKlgNQ84gyNKqHm0gDjqVHnxDHgA/B9RQkpaB6YklkZl62np9KBhOqwjpKFgeY2YAz4BESBWHI8Hhs6PVVSvc3v98ye4fP7T676B845nt040ip98qpWJmI9PWiU6bfWgXGN2YHcKwU7tsuc4kpUPMbU0+f8+vKt+Pitl7PLAMDI9cNBoB0hQwICzjqUp6MZvsy8yvp95BRuQUjJ75mPvH4wYo1NlJ64Mza7DPwrhi8cCOeXl/aUB4P4c/NJxKLMvpngycCrzxVFG2v/CwAMnguF80oLe8p27cQh+fnpPV/fTc95S6piXQDAw7a9YbWkezZXFbAwMx/xPFXb1D3+Y90AQF/L7kAsri9mZ4lrTd0TcYA/Kakr+x2JSPUAAAAASUVORK5CYII=">
                        </a>
                    <?php endif ?>
                    <?php if($this->FG_EDITION): ?>
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
                            $this->FG_EDITION_CONDITION
                        );
                        ?>
                        <?php if($check): ?>
                        &nbsp;
                        <a href="<?= $this->FG_EDITION_LINK?><?= $item[$this->FG_NB_TABLE_COL]?>">
                            <img src="<?= Images_Path_Main;?>/<?= $this->FG_EDITION_IMG?>" border="0" title="<?= $this->FG_EDIT_ALT?>" alt="<?= $this->FG_EDIT_ALT?>">
                        </a>
                        <?php endif ?>
                    <?php endif ?>
                    <?php if($this->FG_DELETION && !in_array($item[$this->FG_NB_TABLE_COL], $this->FG_DELETION_FORBIDDEN_ID)): ?>
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
                            $this->FG_DELETION_CONDITION
                        );
                        ?>
                        <?php if ($check): ?>
                        &nbsp;
                        <a href="<?= $this->FG_DELETION_LINK?><?= $item[$this->FG_NB_TABLE_COL]?>">
                            <img border="0" title="<?= $this->FG_DELETE_ALT?>" alt="<?= $this->FG_DELETE_ALT?>" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAIhSURBVDjLlZPrThNRFIWJicmJz6BWiYbIkYDEG0JbBiitDQgm0PuFXqSAtKXtpE2hNuoPTXwSnwtExd6w0pl2OtPlrphKLSXhx07OZM769qy19wwAGLhM1ddC184+d18QMzoq3lfsD3LZ7Y3XbE5DL6Atzuyilc5Ciyd7IHVfgNcDYTQ2tvDr5crn6uLSvX+Av2Lk36FFpSVENDe3OxDZu8apO5rROJDLo30+Nlvj5RnTlVNAKs1aCVFr7b4BPn6Cls21AWgEQlz2+Dl1h7IdA+i97A/geP65WhbmrnZZ0GIJpr6OqZqYAd5/gJpKox4Mg7pD2YoC2b0/54rJQuJZdm6Izcgma4TW1WZ0h+y8BfbyJMwBmSxkjw+VObNanp5h/adwGhaTXF4NWbLj9gEONyCmUZmd10pGgf1/vwcgOT3tUQE0DdicwIod2EmSbwsKE1P8QoDkcHPJ5YESjgBJkYQpIEZ2KEB51Y6y3ojvY+P8XEDN7uKS0w0ltA7QGCWHCxSWWpwyaCeLy0BkA7UXyyg8fIzDoWHeBaDN4tQdSvAVdU1Aok+nsNTipIEVnkywo/FHatVkBoIhnFisOBoZxcGtQd4B0GYJNZsDSiAEadUBCkstPtN3Avs2Msa+Dt9XfxoFSNYF/Bh9gP0bOqHLAm2WUF1YQskwrVFYPWkf3h1iXwbvqGfFPSGW9Eah8HSS9fuZDnS32f71m8KFY7xs/QZyu6TH2+2+FAAAAABJRU5ErkJggg==">
                        </a>
                        <?php endif ?>
                    <?php endif ?>
                    <?php for ($b = 1; $b <= 5; $b++):
                        if (property_exists($this, "FG_OTHER_BUTTON$b")):
                            $check = true;
                            $condition_eval = preg_replace_callback(
                                "/col([0-9])/i",
                                function ($m) use ($item, &$check) {
                                    $check = false;
                                    $tmp = str_replace("col$m[1]", $item[$m[1]], $m[0]);
                                    $check = eval("return $tmp;");
                                    return $tmp;
                                },
                                $this->{"FG_OTHER_BUTTON{$b}_CONDITION"}
                            );
                            $new_link = $this->{"FG_OTHER_BUTTON{$b}_LINK"};
                            if ($check) {
                                $new_link = str_replace(
                                    ["|param|", "|param1|"],
                                    [$item[$this->FG_NB_TABLE_COL], $item[$this->FG_NB_TABLE_COL - 1]],
                                    $new_link
                                );
                            }
                            $new_link = preg_replace_callback(
                        "/\|col([0-9])\|/i",
                                function ($m) use ($item) {
                                    return str_replace("col$m[1]", $item[$m[1]], $m[0]);
                                },
                                $new_link
                            );
                            $extra_html = "";
                            $id = $this->{"FG_OTHER_BUTTON{$b}_HTML_ID"};
                            if (!empty($id)) {
                                for ($h = count($item); $h >= 0; $h--) {
                                    $id = str_replace("|col$h|", $origlist[$num][$h], $id);
                                }
                                $extra_html .= " id='$id' ";
                            }

                            $class = $this->{"FG_OTHER_BUTTON{$b}_HTML_CLASS"};
                            if (!empty($class)) {
                                $extra_html .= " class='$class' ";
                            }

                            if (substr($new_link, -1) === "=") {
                                $new_link .= $item[$this->FG_NB_TABLE_COL];
                            }

                            $img = $this->{"FG_OTHER_BUTTON{$b}_IMG"};
                            ?>
                            <a href="<?= $new_link ?>" <?= $extra_html ?>>
                                <?php if (empty($img)): ?>
                                <span class="cssbutton"><?= $this->{"FG_OTHER_BUTTON{$b}_ALT"} ?></span>
                                <?php else: ?>
                                <img src="<?= $this->{"FG_OTHER_BUTTON{$b}_IMG"} ?>" border="0" title="<?= $this->{"FG_OTHER_BUTTON{$b}_ALT"} ?>" alt="<?= $this->{"FG_OTHER_BUTTON{$b}_ALT"} ?>">
                                <?php endif ?>
                            </a>
                        <?php endif ?>
                    <?php endfor ?>
                        </td>
                <?php endif ?>

                    </tr>
                <?php endforeach; //  for ($num=0;$num<count($list);$num++)?>
                <?php for (; $num < 7; $num++): ?>
                    <tr bgcolor="<?= $this->FG_TABLE_ALTERNATE_ROW_COLOR[$num % 2]?>">
                        <?php for($j = 0; $j < $this->FG_NB_TABLE_COL - $extra_col; $j++): ?>
                         <td valign=top class="tableBody">&nbsp;</td>
                         <?php endfor ?>
                         <td align="center" valign=top class="tableBodyRight">&nbsp;</td>
                    </tr>
                <?php endfor ?>
                    <tr>
                        <td class="tableDivider" colspan=<?= $this->FG_TOTAL_TABLE_COL?>>
                            <img alt="" src="data:image/gif;base64,R0lGODlhAQABAIAAAOZ3fP///yH5BAEAAAEALAAAAAABAAEAAAICTAEAOw=="/>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <?php if ($this->CV_DISPLAY_BROWSE_PAGE): ?>
        <tr>
            <td height=16 style="PADDING-LEFT: 5px; PADDING-RIGHT: 3px">
                <table border=0 cellPadding=0 cellSpacing=0 width="100%">
                    <tr>
                        <td align="right" valign="bottom">
                            <span class="viewhandler_span2">
                                <?php $this->printPages($this->CV_CURRENT_PAGE + 1, $this->FG_NB_RECORD_MAX, "?stitle=$stitle&atmenu=$processed[atmenu]&current_page=%s&filterprefix=$processed[filterprefix]&order=$processed[order]&sens=$processed[sens]&mydisplaylimit=$processed[mydisplaylimit]&popup_select=$processed[popup_select]&letter=$letter$this->CV_FOLLOWPARAMETERS") ?>
                            </span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <?php endif ?>

        <tr>
            <td>
                <form name="otherForm2" action="">
                <?php if ($this->CV_DISPLAY_RECORD_LIMIT): ?>
                    <?= gettext("DISPLAY");?>
                    <input type="hidden" name="id" value="<?= $processed["id"] ?>"/>
                    <input type="hidden" name="stitle" value="<?= $stitle ?>"/>
                    <input type="hidden" name="form_action" value="edit"/>
                    <input type="hidden" name="current_page" value="0"/>
                    <?php foreach ($processed as $key => $val): ?>
                        <?php if ($key !== 'current_page' && $key !== 'id'): ?>
                            <input type="hidden" name="<?= $key ?>" value="<?= $val ?>">
                        <?php endif ?>
                    <?php endforeach ?>
                    <select name="mydisplaylimit" size="1" class="form_input_select">
                        <option value="10" <?php if($_SESSION["$this->FG_TABLE_NAME-displaylimit"] < 50) echo "selected" ?>>10</option>
                        <option value="50" <?php if($_SESSION["$this->FG_TABLE_NAME-displaylimit"] === 50 ) echo "selected" ?>>50</option>
                        <option value="100" <?php if($_SESSION["$this->FG_TABLE_NAME-displaylimit"] === 100 ) echo "selected" ?>>100</option>
                        <option value="ALL" <?php if($_SESSION["$this->FG_TABLE_NAME-displaylimit"] > 100 ) echo "selected" ?>>All</option>
                    </select>
                    <input class="form_input_button"  value=" <?= gettext("GO");?> " type="SUBMIT"/>
                    &nbsp; &nbsp; &nbsp;
                <?php endif ?>
                <?php if ($this->FG_EXPORT_CSV): ?>
                    - &nbsp; &nbsp;
                    <a href="export_csv.php?var_export=<?= $this->FG_EXPORT_SESSION_VAR ?>&var_export_type=type_csv" target="_blank" >
                        <img border="0" height="30" alt="" src="data:image/gif;base64,R0lGODlhQABFAOMIADF+VEaKY3CdgZecl8bLxuLl4vL08f///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEKAAgALAAAAABAAEUAAAT+8MhJq7046827/2BljGRpnugZdsXgvnAszzBhrFtb7Hzv/8Cdy4bLtIoZw4DAvCErx6dFWTA0pZModkI9WIlY7dY7KEi+zqd42z1f1YMxt0xBw+Vnev2NW2Pbe2ArflKAgWkghE+Gh4NxeIyNiY9ykZIeikhKNikjLYIcmUVKNDNmmJRJBgUEpxtMsLGyBHosqWesBAICAb0BBB2dJjofOrC8vsm+wBzCJrVTq0y7ytXVzCEkZyS10rrI1uHWnClcwnOuBOLr60uzsBPvsK6A6uz3yu7y8fKt6PH4Avbq508CwTT1BAbE9qeWPYXsGBZyWG2XRWsWqSmTuIjiRgr+BaohBNdL3zt+8uh5VIaIJMMB1UzOQvlOZTqMIH0JSBMyJkGas2wCtObqADJsBkguq7RS2c4sAQRQePhxTMJwDAW46jnO3Agvdc7luSluKxelyWTKAirr1AiHAOLKnSvVAlWf/djGMsMt3dy/cSVyDac2lt55JeACnlvUC9pkHEGcULwYQOS7VXGgoAz4qUEKjweK9eKsb7zKAOgFUE34p8F+VUwbrPwy6tTWeV+nTOy3M0+5Wef6KgxPd03epwEXFSDXs5cAgCNjKE1s9l+GBK5Pje6h9CrOAHaWYC7cBHngwbxXP5AdtXvUxJm4gU1iffv3+OfGJ0cw9vfe+QWiuB8J/dUHXoDuSefdCfYhGGBkCzJ4oIOLkRNhJ+t5kcsA51Eo14AjFOgJNBd402F+IH6x24iNaUALLBzyAt9BIdLH4ge0+BCLC7ugd6EwGeZQRhA9IPYjhiRqoEMJRO5wJJBJGjEAdTw8iWSLUlqppYFYYtACItts6QwPUXo5pQZiotBDmRd8QtCbcL7JpgUtlGLnnXjC0CUefPbp55+AThABADs="/>
                        <?= gettext("Export CSV") ?>
                    </a>
                <?php endif ?>
                <?php if ($this->FG_EXPORT_XML): ?>
                    - &nbsp; &nbsp;
                    <a href="export_csv.php?var_export=<?= $this->FG_EXPORT_SESSION_VAR ?>&var_export_type=type_xml" target="_blank" >
                        <img border="0" height="32" alt="" src="data:image/gif;base64,R0lGODlhKgAqAOMIAPRLJHW42bOqqZ7N5vfNjtfV08/n9P///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEKAAgALAAAAAAqACoAAAT+EEl0qr34zs37rkEojuRQVF7KVUVgvEMsz0MwGJaagmQvxqHY6aDrHFovA21pCwpRRckB6OvVmq4nMXpwwZYzLPA7jCKTYFkwpLy1T2YvOv3zxr5Q1Tn5pYnaZEplenJ8bWEhBYCHglspBW6GfVQuikJKQYMekJJ8iC5HjDVPepGdh1c4oVdqcI+mp2o/bqw1rpuwsZQjaraOHZynnrIlowG3wLm6dMbIHJAHwoZpxgbHv88DFdLTTDXWzhvQFtzdYy7XpRjl3ejg2OLaFwUCBvQCigb19ZLu6a/zBAAoAKAgAH0ABL7I98JfuAnjBA40aKBgxYQHEWbM8k4dQQKcFgCALChR4UKLHE3AgyhPIsGCLw2adKmkpkp1FgoQALlzp0ifOwswvEH0oYRxFkBW2HmAZ9Od02waRYA0QwZhRBvhtMq1U9abALmKlfR1alWxVz0pmWJWHtqxMNaCxfX2LYwpvrbW7aptAL62e98CEZonWWC0fvEVBmaDmuMwhFeKuyegsuXLmDP/1bRDqOfPoEOHXrzj8N4oCCIAADs="/>
                        <?= gettext("Export XML") ?>
                    </a>
                <?php endif ?>
                </form>
            </td>
        </tr>
    </table>
</div>