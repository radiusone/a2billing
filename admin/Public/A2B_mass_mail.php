<?php

use A2billing\A2bMailException;
use A2billing\Admin;
use A2billing\Forms\FormHandler;
use A2billing\Mail;
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

$menu_section = 17;
require_once __DIR__ . "/../../common/lib/admin.defines.php";

Admin::checkPageAccess(Admin::ACX_MAIL);

getpost_ifset(["id", "subject", "message", "from", "fromname", "submit"]);
/**
 * @var numeric-string|null $id
 * @var string|null $subject
 * @var string|null $message
 * @var string|null $from
 * @var string|null $fromname
 * @var numeric-string|null $submit
 */

$HD_Form = new FormHandler("cc_card", "Card");
$HD_Form->search_session_key = 'entity_card_selection_mail';
$HD_Form->init();
$instance_cus_table = new Table("cc_card", ["id", "email"]);

$cardstatus_list = getCardStatus_List();
$currencies_list = getCurrenciesList();
$simultaccess_list = getCardAccess_List();
$language_list = getLanguages();

$HD_Form->search_form_enabled = true;
$HD_Form->search_delete_enabled = false;
$HD_Form->search_form_title = gettext('Define specific criteria to search for cards created.');
$HD_Form->AddSearchDateInput(_("Creation date"), "creationdate");
$HD_Form->AddSearchTextInput(gettext("ACCOUNT NUMBER"), 'username');
$HD_Form->AddSearchTextInput(gettext("LASTNAME"), 'lastname');
$HD_Form->AddSearchTextInput(gettext("LOGIN"), 'useralias');
$HD_Form->AddSearchTextInput(gettext("MACADDRESS"), 'mac_addr');
$HD_Form->AddSearchTextInput(gettext("EMAIL"), 'email');
$HD_Form->AddSearchComparisonInput(gettext("CUSTOMER ID (SERIAL)"), 'id1', 'id2', 'id');
$HD_Form->AddSearchComparisonInput(gettext("CREDIT"), 'credit1', 'credit2', 'credit');
$HD_Form->AddSearchComparisonInput(gettext("INUSE"), 'inuse1', 'inuse2', 'inuse');

$HD_Form->AddSearchSelectInput(gettext("SELECT LANGUAGE"), "language", $language_list);
$HD_Form->AddSearchSqlSelectInput(gettext("SELECT TARIFF"), "tariff", new Table("cc_tariffgroup", ["id", "tariffgroupname"]), "tariffgroupname");
$HD_Form->AddSearchSelectInput(gettext("SELECT STATUS"), "status", $cardstatus_list);
$HD_Form->AddSearchSelectInput(gettext("SELECT ACCESS"), "simultaccess", $simultaccess_list);
$HD_Form->AddSearchSelectInput(gettext("SELECT CURRENCY"), "currency", $currencies_list);
$HD_Form->prepare_list_subselection('list');

$limit_massmail = 2000;
$HD_Form->FG_INTRO_TEXT_EDITION = sprintf(
    _("The mass mail tool is limited to %d mails. You can use the search module to send on different group of customer and overpass this limit."),
    $limit_massmail
);
$HD_Form->help_text = _("Here you can email a message to all of your users. To do this, an email will be sent out to the administrative email address supplied, with a blind carbon copy sent to all recipients. If you are emailing a large group of people please be patient after submitting and do not stop the page halfway through. It is normal for a mass emailing to take a long time and you will be notified when the script has completed.");

$conditions = $HD_Form->list_query_conditions;
$conditions["email"] = ["<>", ""];
if (isset($id)) {
    $conditions["id"] = $id;
}
$list_customer = $instance_cus_table->getRows($HD_Form->DBHandle, $conditions, [], "ASC", [], $limit_massmail);

$nb_customer = sizeof($list_customer);

if (isset($submit)) {
    $start = hrtime(true);
    $error_msg = "\n";
    $sent = 0;
    $err_sent = 0;

    foreach ($list_customer as $cc_customer) {
        $id_card = $cc_customer["id"];
        try {
            $mail = new Mail(null,$id_card,null,$message,$subject);
            $mail ->setFromName($fromname);
            $mail ->setFromEmail($from);

            if (MAILQUEUE_THROTTLE) {
                sleep(MAILQUEUE_THROTTLE);
            } elseif (MAILQUEUE_BATCH_SIZE && $sent > 10) {
                $totaltime = (hrtime(true) - $start) / 1e9; // time since start of loop in seconds
                $msgperhour = (3600 / $totaltime) * $sent; // 7200
                $msgpersec = $msgperhour / 3600; // 2
                $secpermsg = $totaltime / $sent; // 0.5
                $target = MAILQUEUE_BATCH_SIZE / MAILQUEUE_BATCH_PERIOD; // 0.5
                $actual = $sent / $totaltime;
                $delay = $actual - $target;
                //echo ("totaltime=$totaltime - Sent: $sent ; mph $msgperhour ; mps $msgpersec ; secpm $secpermsg ; target $target ; actual $actual ; delay $delay <br/>");
                if ($delay > 0) {
                    // $expected = MAILQUEUE_BATCH_PERIOD / $secpermsg;
                    // $delay = MAILQUEUE_BATCH_SIZE / $expected;
                    //echo ("waiting for $delay seconds to make sure we don't exceed our limit of ".MAILQUEUE_BATCH_SIZE." messages in ".MAILQUEUE_BATCH_PERIOD."seconds <br/><br/>");

                    $delay = $delay * 1000000;
                    usleep($delay);
                }
            }

            // SEND MAIL
            $mail ->send();
            $sent++;
        } catch (A2bMailException $e) {
            $err_sent++;
            $error_msg .= $e->getMessage() . "\n";
        }
    }
}

require_once __DIR__ . "/../templates/main.php";

$HD_Form->create_search_form(true);
?>

<?php if (isset($submit)): ?>
    <div class="alert alert-success" role="alert"><?= sprintf(_("The email has been sent to %d customers"), $sent) ?></div>
    <?php if ($err_sent): ?>
    <div class="alert alert-danger" role="alert"><?= sprintf(_("The following errors occurred during sending: %s"), nl2br($error_msg)) ?></div>
    <?php endif ?>
<?php elseif ($list_customer): ?>
<div class="row mb-3 align-items-end">
    <div class="col-auto">
        <button type="button" id="loadtmpl" class="btn btn-primary btn-sm"><?= _("Load Template") ?></button>
    </div>
</div>

<form method="post">
    <div class="row mb-3">
        <label class="col-3 col-form-label"><?= _("To") ?></label>
        <div class="col">
    <?php if (isset($id)): ?>
            <input type="hidden" name="id" value="<?= $id ?>"/>
    <?php endif ?>
    <?php for ($i = 0; $i < count($list_customer) && $i <= 100; $i++): ?>
            <a href="A2B_entity_card.php?form_action=ask-edit&id=<?= $list_customer[$i]["id"] ?>" target="_blank"><?= $list_customer[$i]["email"] ?></a><?= $i + 1 !== count($list_customer) ? ", " : "" ?>
        <?php if ($i === 100 && count($list_customer) > 100): ?>
            <a href="A2B_entity_card.php" target="_blank"><?= _("Click on customer list to see them all") ?></a>
        <?php endif ?>
    <?php endfor ?>
        </div>
    </div>

    <div class="row mb-3">
        <label class="col-3 col-form-label" for="from"><?= _("From Email") ?></label>
        <div class="col">
            <input name="from" id="from" type="email" class="form-control" value="<?= EMAIL_ADMIN ?>"/>
        </div>
    </div>

    <div class="row mb-3">
        <label class="col-3 col-form-label" for="fromname"><?= _("From Name") ?></label>
        <div class="col">
            <input name="fromname" id="fromname" class="form-control" maxlength="64"/>
        </div>
    </div>

    <div class="row mb-3">
        <label class="col-3 col-form-label" for="subject"><?= _("Subject") ?></label>
        <div class="col">
            <input name="subject" id="subject" class="form-control" maxlength="255"/>
        </div>
    </div>

    <div class="row mb-3">
        <label class="col-3 col-form-label" for="msg_mail"><?= _("Message") ?></label>
        <div class="col">
            <textarea name="message" id="msg_mail" class="form-control" rows="10"></textarea>
        </div>
    </div>

    <div class="row my-4 justify-content-end">
        <div class="col-auto">
            <button type="submit" name="submit" class="btn btn-primary" value="1"><?= _("Email") ?></button>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <?= _("The followings tags will be replaced in the message by the value in the database.") ?>
            <dl>
                <dt>$email$</dt> <dd><?= _('email of the customer') ?></dd>
                <dt>$firstname$</dt> <dd><?= _('firstname of the customer') ?></dd>
                <dt>$lastname$</dt> <dd><?= _('lastname of the customer') ?></dd>
                <dt>$credit$</dt> <dd><?= _('credit of the customer in the system currency') ?></dd>
                <dt>$creditcurrency$</dt> <dd><?= _('credit of the customer in the own currency') ?></dd>
                <dt>$currency$</dt> <dd><?= _('currency of the customer') ?></dd>
                <dt>$cardnumber$</dt> <dd><?= _('card number of the customer') ?></dd>
                <dt>$password$</dt> <dd><?= _('password of the customer') ?></dd>
                <dt>$login$</dt> <dd><?= _('login of the customer') ?></dd>
                <dt>$credit_notification$</dt> <dd><?= _('credit notification of the customer') ?></dd>
                <dt>$base_currency$</dt> <dd><?= _('base currency of system') ?></dd>
            </dl>
        </div>
    </div>
</form>
<?php else: ?>
    <div class="alert alert-info" role="alert"><?= _("No Record Found!") ?></div>
<?php endif ?>
<script>
    document.getElementById("loadtmpl").addEventListener("click", function() {
        window.open('A2B_entity_mailtemplate.php?popup_select=1', '', 'scrollbars=yes,resizable=yes,width=700,height=500');
    })
</script>

<?php

require_once __DIR__ . "/../templates/footer.php";
