<?php

use A2billing\Admin;
use A2billing\Connection;
use A2billing\Customer;
use A2billing\Forms\FormHandler;
use Illuminate\Database\Query\Builder;

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

$menu_section = 16;
require_once __DIR__ . "/../../common/lib/admin.defines.php";

Admin::checkPageAccess(Admin::ACX_MAINTENANCE);

getpost_ifset([
    "posted_search", "posted_archive", "enable_starttime_start", "starttime_start", "enable_starttime_end",
    "starttime_end", "enable_starttime_end_relative", "starttime_end_relative", "card_id", "id_provider",
    "id_tariffgroup", "id_trunk", "id_ratecard", "dst", "dsttype", "src", "srctype", "calltype"
]);
/**
 * @var bool|string $posted_search whether the user has clicked the search button
 * @var bool|string $posted_archive whether the user has clicked the archive button
 * @var bool|string $enable_starttime_start
 * @var string $starttime_start
 * @var bool|string $enable_starttime_end
 * @var string $starttime_end
 * @var bool|string $enable_starttime_end_relative
 * @var string $starttime_end_relative
 * @var string $card_id
 * @var string $id_provider
 * @var string $id_tariffgroup
 * @var string $id_trunk
 * @var string $id_ratecard
 * @var string $dst
 * @var string $dsttype
 * @var string $src
 * @var string $srctype
 * @var string $calltype
 */

$posted_search = isset($posted_search);
$posted_archive = isset($posted_archive);
$enable_starttime_start = isset($enable_starttime_start);
$enable_starttime_end = isset($enable_starttime_end);
$enable_starttime_end_relative = isset($enable_starttime_end_relative);
$card_id = (int)($card_id ?? 0);
$id_provider = (int)($id_provider ?? 0);
$id_tariffgroup = (int)($id_tariffgroup ?? 0);
$id_trunk = (int)($id_trunk ?? 0);
$id_ratecard = (int)($id_ratecard ?? 0);
$form_action ??= "list";

$HD_Form = new FormHandler(
    "cc_call",
    _("Calls"),
    "cc_call.id",
    Connection::getConnection("cc_call")
        ->leftJoin("cc_trunk", "cc_call.id_trunk", "cc_trunk.id_trunk")
        ->leftJoin("cc_prefix", "cc_call.destination", "cc_prefix.prefix")
        ->latest("starttime")
        ->limit(30)
);

$HD_Form->list_message_empty = _("No matching calls found; use the fields above to refine your search.");

$HD_Form->AddListValue(_("Calldate"), "starttime");
$HD_Form->AddListValue(_("CalledNumber"), "calledstation", "format_phone_number");
$HD_Form->AddListValue(_("Destination"), "cc_prefix.destination", "format_phone_number");
$HD_Form->AddListValue(_("Duration"), "real_sessiontime", "get_minute");
$HD_Form->AddListValue(_("Card Used"), "card_id", [Customer::class, "getUsername"]);
$HD_Form->AddListMapping(_("Disposition"), "terminatecauseid", getDialStatusList());
$HD_Form->AddListMapping(_("IAX/SIP"), "sipiax", getYesNoList());
$HD_Form->AddListValue(_("Cost"), "sessionbill", "get_money_precise");

if (($calltype ?? "answered") === "answered") {
    $HD_Form->query_builder->where("terminatecauseid", 1);
}

$archive_message = "";
if ($posted_archive === true) {
    try {
        $HD_Form->query_builder->limit = null;
        Connection::getConnection("cc_call_archive")
            ->transaction(function (Builder $builder) use ($HD_Form) {
                $builder->insertUsing(['*'], $HD_Form->query_builder);
                $HD_Form->query_builder->delete();
            });
        $HD_Form->list_message_empty = _("The data has been successfully archived");
    } catch (Throwable) {
        $archive_message = _("There was an error archiving the data");
    }
}

$HD_Form->SetSearchType(text: _('Define specific criteria to search for call records'));

$HD_Form->AddSearchRelativeDateInput(_("Dates"), "starttime");
$HD_Form->AddSearchDateInput(_("Dates"), "starttime");
$HD_Form->AddSearchPopupInput(_("Customer ID"), "card_id", "A2B_entity_card.php");
$HD_Form->AddSearchPopupInput(_("Call Plan"), "id_tariffgroup", "A2B_entity_tariffgroup.php", 2);
$HD_Form->AddSearchPopupInput(_("Provider"), "id_provider", "A2B_entity_provider.php", 2);
$HD_Form->AddSearchPopupInput(_("Trunk"), "id_trunk", "A2B_entity_trunk.php", 2);
$HD_Form->AddSearchPopupInput(_("Rate"), "id_ratecard", "A2B_entity_def_ratecard.php", 2);

$HD_Form->AddSearchTextInput(_("Destination"), "dst");
$HD_Form->AddSearchTextInput(_("Source"), "src");

$HD_Form->AddSearchSelectInput(_("Disposition"), "calltype", ["answered" => _("Answered Only"), "all" => _("All Calls")], process: false);

if ($posted_search === true && $posted_archive === false) {
    $HD_Form->AddSearchButton(
        "Archive Displayed Calls",
        "posted_archive",
        "true"
    );
}

require_once __DIR__ . "/templates/main.php";

if ($archive_message) {
    print "<div class='row'><div class='col text-center'>$archive_message</div></div>";
}

$list = $HD_Form->perform_action($form_action);
$HD_Form->create_form($form_action, $list) ;
?>
<script>
document.getElementById("posted_archive")?.addEventListener("click", function (e) {
    if (!confirm('This action will archive the selected calls. Are you sure?')) {
        e.preventDefault();
        e.stopPropagation();
        return false;
    }
});
</script>
<?php
require_once __DIR__ . "/templates/footer.php";
