<?php

use A2billing\Admin;
use A2billing\Table;

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of A2Billing (http://www.a2billing.net/)
 *
 * A2Billing, Commercial Open Source Telecom Billing platform,
 * powered by Star2billing S.L. <http://www.star2billing.com/>
 *
 * @copyright   Copyright © 2004-2015 - Star2billing S.L.
 * @copyright   Copyright © 2022-2025 RadiusOne Inc.
 * @author      Belaid Arezqui <areski@gmail.com>
 * @author      Michael Newton <mnewton@goradiusone.com>
 * @license     http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @package     A2Billing
 *
 * Software License Agreement (GNU Affero General Public License)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *
**/

$menu_section = 6;
require_once __DIR__ . "/../../common/lib/admin.defines.php";

Admin::checkPageAccess(Admin::ACX_PACKAGEOFFER);

getpost_ifset(["id", "delallrate", "delrate", "addrate"]);
/**
 * @var numeric-string|null $id the package ID
 * @var 'true'|null $dellallrate
 * @var numeric-string[]|null $delrate
 * @var numeric-string|null $addrate
 */

$package = (new Table("cc_package_offer"))->getRow(DbConnect(), ["id" => $id ?? 0]);
if (!$package) {
    header("Location: A2B_entity_package.php");
}

// these all come from the query string via the rate popup
getpost_ifset(["addbatchrate", "id_trunk", "id_tariffplan", "tag", "prefix", "rbPrefix"]);
/**
 * @var 'true'|null $addbatchrate
 * @var numeric-string|null $id_trunk
 * @var numeric-string|null $id_tariffplan
 * @var string|null $tag
 * @var numeric-string|null $prefix
 * @var numeric-string|null $rbPrefix
 */
if ($addbatchrate ?? false) {
    $DBHandle = DbConnect();

    $rates_clauses = [];
    if ((int)($id_trunk ?? "0")) {
        $rates_clauses["id_trunk"] = $id_trunk;
    }
    if ((int)($id_tariffplan ?? "0")) {
        $rates_clauses["idtariffplan"] = $id_tariffplan;
    }
    if ($tag ?? "") {
        $rates_clauses["tag"] = $tag;
    }
    if (($prefix ?? "") && ($rbPrefix ?? "0")) {
        switch ($rbPrefix) {
            case 1: $rates_clauses["dialprefix"] = $prefix; break;
            case 2: $rates_clauses["dialprefix"] = ["LIKE", "$prefix%"]; break;
            case 3: $rates_clauses["dialprefix"] = ["LIKE", "%$prefix%"]; break;
            case 4: $rates_clauses["dialprefix"] = ["LIKE", "%$prefix"]; break;
            case 5: $rates_clauses["dialprefix"] = ["IN", split_data($prefix)]; break;
        }
    }
    (new Table("cc_package_rate", ["package_id", "rate_id"]))
        ->addRowsFromSelect($DBHandle, new Table("cc_ratecard", [$id, "id"]), $rates_clauses);
    header("Location: A2B_info_package.php?id=$id");
}

if (is_numeric($addrate ?? null)) {
    $DBHandle = DbConnect();
    (new Table("cc_package_rate"))->addRow($DBHandle, ["package_id" => $id, "rate_id" => $addrate]);
    header("Location: A2B_info_package.php?id=$id");
}

if (is_numeric($delrate ?? null)) {
    $DBHandle = DbConnect();
    (new Table("cc_package_rate"))->deleteRow($DBHandle, ["package_id" => $id, "rate_id" => ["IN", $delrate]]);
    header("Location: A2B_info_package.php?id=$id");
}

if ($delallrate ?? false) {
    $DBHandle = DbConnect();
    (new Table("cc_package_rate"))->deleteRow($DBHandle, ["package_id" => $id]);
    header("Location: A2B_info_package.php?id=$id");
}

require_once __DIR__ . "/../templates/main.php";

//load rates
$DBHandle = DbConnect();

$table_rates = new Table(
    "cc_package_rate",
    ["DISTINCT cc_ratecard.id", "cc_prefix.destination", "cc_ratecard.dialprefix"],
    [
        "cc_ratecard" => ["cc_ratecard.id", "cc_package_rate.rate_id"],
        "cc_prefix" => ["cc_prefix.prefix", "cc_ratecard.destination"],
    ]
);
$result_rates = $table_rates->getRows(DbConnect(), ["cc_package_rate.package_id" => $id]);
?>
<div class="row pb-3">
    <div class="col-2">
        <?= _("Package Name") ?>:
    </div>
    <div class="col-4">
        <?= $package["label"] ?>
    </div>
</div>
<div class="row pb-3">
    <div class="col-2">
        <?= _("Date") ?>:
    </div>
    <div class="col-4">
        <?= $package["creationdate"] ?>
    </div>
</div>
<div class="row pb-3">
    <div class="col-2">
        <?= _("Type") ?>:
    </div>
    <div class="col-4">
        <?= getPackagesTypeList()[$package["packagetype"]] ?>
    </div>
</div>
<?php if ($package["packagetype"] > 0): ?>
<div class="row pb-3">
    <div class="col-2">
        <?= _("Number") ?>:
    </div>
    <div class="col-4">
        <?php if ($package["packagetype"] == 1): ?>
            <?= sprintf(_("%d free calls per %s"), $package["freetimetocall"], $package["billingtype"] ? _("week") : _("month")) ?>
        <?php else: ?>
            <?= sprintf(_("%d free minutes per %s"), get_minute($package["freetimetocall"]), $package["billingtype"] ? _("week") : _("month")) ?>
        <?php endif ?>
    </div>
</div>
<?php endif ?>
<div class="row pb-3">
    <div class="col-4 text-center">
        <label class="form-label" for="rate"><?= _("Assigned Rates") ?></label>
        <select name="rate[]" id="rate" class="form-select" multiple="multiple" size="10">
            <?php foreach ($result_rates as $rate): ?>
            <option value="<?= $rate["id"] ?>"><?= htmlspecialchars($rate["destination"]) ?>&nbsp;:&nbsp;<?= $rate["dialprefix"] ?></option>
            <?php endforeach ?>
        </select>
        <div class="d-flex justify-content-around">
            <button class="btn btn-sm" id="addrate" aria-label="<?= _("add a rate") ?>"><span class="bi bi-16 bi-file-earmark-plus" aria-hidden="true"></span></button>
            <button class="btn btn-sm" id="delrate" aria-label="<?= _("delete a rate") ?>"><span class="bi bi-16 bi-file-earmark-minus" aria-hidden="true"></span></button>
            <button class="btn btn-sm" id="delall" aria-label="<?= _("delete all rates") ?>"><span class="bi bi-16 bi-folder-minus" aria-hidden="true"></span></button>
        </div>
    </div>
</div>

<script>
document.getElementById("addrate").addEventListener("click", function() {
    const id = "<?= (int)$id ?>";
    const url = `A2B_entity_def_ratecard.php?popup_select=1&package=${id}`;
    window.open(url, "", "scrollbars=yes,resizable=yes,width=700,height=500");
});

document.getElementById("delrate").addEventListener("click", function() {
    const id = "<?= (int)$id ?>";
    /** @var {HTMLOptionsCollection} */
    const val = document.getElementById("rate").selectedOptions;
    if (val.length) {
        const opts = Array.from(val).map(opt => opt.value);
        self.location.href = `A2B_info_package.php?id=${id}&delrate[]=` + opts.join("&delrate[]=");
    }
});

document.getElementById("delall").addEventListener("click", function() {
    const msg = <?= json_encode(_("Are you sure you want to delete all rates from this package?")) ?>;
    if (confirm(msg)) {
        const id = "<?= (int)$id ?>";
        self.location.href = `A2B_info_package.php?id=${id}&delallrate=true`;
    }
});
</script>

<?php
require_once __DIR__ . "/../templates/footer.php";
