<?php

use A2billing\Admin;
use A2billing\Customer;
use A2billing\Forms\FormHandler;
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

$menu_section = 16;
require_once __DIR__ . "/../../common/lib/admin.defines.php";
/**
 * @var FormHandler $HD_Form
 */

Admin::checkPageAccess(Admin::ACX_MAINTENANCE);

getpost_ifset(["posted_search", "posted_archive", "archive_all"]);
/**
 * @var bool|string $posted_search whether the user has clicked the search button
 * @var bool|string $posted_archive whether the user has clicked the archive button
 * @var bool|string $archive_all whether the user has clicked the archive all button
 */
$posted_search = (bool)($posted_search ?? false);
$posted_archive = (bool)($posted_archive ?? false);
$archive_all = (bool)($archive_all ?? false);

$HD_Form = new FormHandler("cc_card", "Customer");
$HD_Form->init();

$HD_Form->search_session_key = "entity_archiving_selection";

$language_list = getLanguages();
$simultaccess_list = getCardAccess_List();
$currency_list = getCurrenciesList();
$cardstatus_list = getCardStatus_List();
$cardstatus_list_acronym = getCardStatus_Acronym_List();
$yesno_list = getYesNoList();

$HD_Form->AddListValue(_("ID"), "id");
$HD_Form->AddListValue(_("Account number"), "id", [Customer::class, "getUsername"]);
$HD_Form->AddListValue(abbr(_("Bal"), _("Balance")), "credit", "get_money");
$HD_Form->AddListValue(_("Last name"), "lastname");
$HD_Form->AddListMapping(_("Status"), "status", $cardstatus_list_acronym);
$HD_Form->AddListValue(abbr(_("Lang"), _("Language")), "language");
$HD_Form->AddListValue(_("In use"), "inuse");
$HD_Form->AddListMapping(abbr(_("Cur"), _("Currency")), "currency", $currency_list);
$HD_Form->AddListMapping(_("SIP"), "sip_buddy", $yesno_list);
$HD_Form->AddListMapping(_("IAX"), "iax_buddy", $yesno_list);
$HD_Form->AddListValue(abbr(_("Num"), _("Number of calls")), "nbused");

$HD_Form->CV_NO_FIELDS  = _("NO CUSTOMER SEARCHED!");
$HD_Form->FG_LIST_VIEW_PAGE_SIZE = 30;

$HD_Form->search_form_enabled = true;
$HD_Form->search_form_title = _('Define specific criteria to search for cards created.');

$HD_Form->AddSearchDateInput(_("Creation date"), "creationdate", true);
$HD_Form->AddSearchDateInput(_("Creation date"), "creationdate");
$HD_Form->AddSearchDateInput(_("First use date"), "firstusedate");
$HD_Form->AddSearchTextInput(_("Account"), 'username');
$HD_Form->AddSearchTextInput(_("Last name"), 'lastname');
$HD_Form->AddSearchTextInput(_("Login"), 'useralias');
$HD_Form->AddSearchTextInput(_("MAC address"), 'mac_addr');
$HD_Form->AddSearchTextInput(_("Email"), 'email');
$HD_Form->AddSearchComparisonInput(_("Card"), 'id1', 'id2', 'id');
$HD_Form->AddSearchComparisonInput(_("Credit"), 'credit1', 'credit2', 'credit');
$HD_Form->AddSearchComparisonInput(_("In use"), 'inuse1', 'inuse2', 'inuse');

$HD_Form->AddSearchSelectInput(_("Language"), "language", $language_list);
$HD_Form->AddSearchSqlSelectInput(_("Rate plan"), "tariff", new Table("cc_tariffgroup", ["id, tariffgroupname"]), "tariffgroupname");
$HD_Form->AddSearchSelectInput(_("Status"), "status", $cardstatus_list);
$HD_Form->AddSearchSelectInput(_("Access"), "simultaccess", $simultaccess_list);
$HD_Form->AddSearchSqlSelectInput(_("Group"), "id_group", new Table("cc_card_group", ["id", "name"]), "name");
$HD_Form->AddSearchSelectInput(_("Currency"), "currency", $currency_list);

if ($posted_search === true && $posted_archive === false) {
    $HD_Form->AddSearchButton(
        "Archive Displayed Calls",
        "posted_archive",
        "true",
        "btn-secondary",
        "return confirm('This action will archive the selected customers. Are you sure?')"
    );
}

$HD_Form->prepare_list_subselection('list');

$archive_message = "";
if ($posted_archive) {
    $condition = "";
    $params = [];
    if (!$archive_all) {
        $condition = (new Table())->processWhereClauseArray($HD_Form->list_query_conditions, $params);
        $condition = " WHERE $condition";
    }
    $rec = archive_data($condition, $params);
    if ($rec) {
        $HD_Form->CV_NO_FIELDS = _("The data has been successfully archived");
    } else {
        $archive_message = _("There was an error archiving the data");
    }
}

$form_action ??= "list";
$list = $HD_Form->perform_action($form_action);

require_once __DIR__ . "/../templates/main.php";
echo create_help(_("Here you can archive the data. The Default listing will show you the previous 3 months data. But you can also search the data and archive it."));

$HD_Form->create_search_form();

?>

<div class="row pb-3">
    <div class="col">
        <form name="theFormFilter" action="">
            <input type="hidden" name="archive_all" value="true"/>
            <input type="hidden" name="posted_archive" value="true"/>
            <button type="submit" class="btn btn-primary" onclick="return confirm(<?= json_encode(_("This action will archive all cards, are you sure?")) ?>)">
                <?= _("Archive All");?>
            </button>
        </form>
    </div>
</div>
<?php

if ($archive_message) {
    print "<div class='row'><div class='col text-center'>$archive_message</div></div>";
}

$HD_Form->create_form($form_action, $list);

require_once __DIR__ . "/../templates/footer.php";

function archive_data(string $where, array $params = []): bool
{
    $handle = DbConnect();
    $handle->BeginTrans();
    $handle->Execute("INSERT INTO cc_card_archive SELECT id, creationdate, firstusedate, expirationdate, enableexpire, expiredays, username, useralias, uipass, credit, tariff, id_didgroup, status, lastname, firstname, address, city, state, country, zipcode, phone, email, fax, inuse, simultaccess, currency, lastuse, nbused, typepaid, creditlimit, voipcall, sip_buddy, iax_buddy, language, redial, runservice, nbservice, id_campaign, num_trials_done, vat, servicelastrun, initialbalance, invoiceday, autorefill, loginkey, id_timezone, tag, voicemail_permitted, voicemail_activated, last_notification, email_notification, notify_email, credit_notification, id_group, company_name, company_website, VAT_RN, traffic, traffic_target, discount, restriction, mac_addr FROM cc_card $where", $params);
    $handle->Execute("DELETE FROM cc_call $where", $params);

    return $handle->CommitTrans();
}
