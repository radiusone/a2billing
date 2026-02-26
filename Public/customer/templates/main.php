<?php

use A2billing\Customer;

require_once("header.php");
require_once __DIR__ . "/../../../common/lib/customer.defines.php";
/**
 * @var numeric-string $popup_select
 * @var int|null $menu_section
 */
$menu_section ??= 0;
?>

<?php if (empty($popup_select)): ?>

<div class="container-fluid">
    <div class="row">
        <nav class="col-md-3 col-lg-2 flex-shrink-0 p-3 bg-light">
            <a class="btn btn-toggle mb-1" href="A2B_info_card.php"><?= _("Account Info") ?></a>
            <?php if ($_SESSION["voicemail"] ?? false): ?>
            <a class="btn btn-toggle mb-1" href="A2B_entity_voicemail.php"><?= _("Voicemail") ?></a>
            <?php endif ?>
            <?php if (Customer::allowed(Customer::ACX_SIP_IAX)): ?>
            <a class="btn btn-toggle mb-1" href="A2B_entity_sipiax_info.php"><?= _("SIP/IAX Info") ?></a>
            <?php endif ?>
            <?php if (Customer::allowed(Customer::ACX_CALL_HISTORY)): ?>
            <a class="btn btn-toggle mb-1" href="call-history.php"><?= _("Call History") ?></a>
            <?php endif ?>
            <?php if (Customer::allowed(Customer::ACX_PAYMENT_HISTORY)): ?>
            <a class="btn btn-toggle mb-1" href="A2B_entity_payment.php"><?= _("Payment History") ?></a>
            <?php endif ?>
            <?php if (Customer::allowed(Customer::ACX_VOUCHER)): ?>
            <a class="btn btn-toggle mb-1" href="A2B_entity_voucher.php"><?= _("Vouchers") ?></a>
            <?php endif ?>
            <?php if (Customer::allowed(Customer::ACX_INVOICES)): ?>
            <button class="btn btn-toggle align-items-center rounded collapsed" data-bs-toggle="collapse" data-bs-target="#invoice-collapse" aria-expanded="<?= $menu_section === 5 ? "true" : "false" ?>">
                <?= _("Invoices") ?>
            </button>
            <div class="collapse <?= $menu_section === 5 ? "show" : "" ?>" id="invoice-collapse">
                <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
                    <li><a class="link-dark rounded" href="A2B_entity_receipt.php"><?= _("View Receipts") ?></a></li>
                    <li><a class="link-dark rounded" href="A2B_entity_invoice.php"><?= _("View Invoices") ?></a></li>
                    <li><a class="link-dark rounded" href="A2B_billing_preview.php"><?= _("Preview Next Billing") ?></a></li>
                </ul>
            </div>
            <?php endif ?>
            <?php if (Customer::allowed(Customer::ACX_DID)): ?>
            <button class="btn btn-toggle align-items-center rouded collapsed" data-bs-toggle="collapse" data-bs-target="#did-collapse" aria-expanded="<?= $menu_section === 8 ? "true" : "false" ?>">
                <?= _("DIDs") ?>
            </button>
            <div class="collapse <?= $menu_section === 8 ? "show" : "" ?>" id="did-collapse">
                <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
                    <li><a class="link-dark rounded" href="A2B_entity_did.php"><?= _("DIDs") ?></a></li>
                    <li><a class="link-dark rounded" href="A2B_entity_did_destination.php"><?= _("Inbound Destinations") ?></a></li>
                </ul>
            </div>
            <?php endif ?>
            <?php if (Customer::allowed(Customer::ACX_SPEED_DIAL)): ?>
            <a class="btn btn-toggle mb-1" href="A2B_entity_speeddial.php"><?= _("Speed Dials") ?></a>
            <?php endif ?>
            <?php if (Customer::allowed(Customer::ACX_RATECARD)): ?>
            <a class="btn btn-toggle mb-1" href="A2B_entity_ratecard.php"><?= _("Rates") ?></a>
            <?php endif ?>
            <?php if (Customer::allowed(Customer::ACX_SIMULATOR)): ?>
            <a class="btn btn-toggle mb-1" href="simulator.php"><?= _("Simulator") ?></a>
            <?php endif ?>
            <?php if (Customer::allowed(Customer::ACX_CALL_BACK)): ?>
            <a class="btn btn-toggle mb-1" href="callback.php"><?= _("Callback") ?></a>
            <?php endif ?>
            <?php if (Customer::allowed(Customer::ACX_CALLER_ID)): ?>
            <a class="btn btn-toggle mb-1" href="A2B_entity_callerid.php"><?= _("Caller IDs") ?></a>
            <?php endif ?>
            <?php if (Customer::allowed(Customer::ACX_SUPPORT)): ?>
            <a class="btn btn-toggle mb-1" href="A2B_support.php"><?= _("Support") ?></a>
            <?php endif ?>
            <?php if (Customer::allowed(Customer::ACX_NOTIFICATION)): ?>
            <a class="btn btn-toggle mb-1" href="A2B_notification.php?form_action=ask-edit&id=<?= Customer::id() ?>"><?= _("Notifications") ?></a>
            <?php endif ?>
            <a class="btn btn-toggle mb-1" href="logout.php"><?= _("Logout") ?></a>
            <button class="btn btn-toggle align-items-center rounded collapsed" data-bs-toggle="collapse" data-bs-target="#language-collapse" aria-expanded="false">
                <?= _("Language") ?>
            </button>
            <div class="collapse" id="language-collapse">
                <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
                    <li><a class="link-dark rounded" href="?ui_language=english">🇬🇧 <?= _("English") ?></a></li>
                    <li><a class="link-dark rounded" href="?ui_language=spanish">🇪🇸 <?= _("Spanish") ?></a></li>
                    <li><a class="link-dark rounded" href="?ui_language=french">🇫🇷 <?= _("French") ?></a></li>
                    <li><a class="link-dark rounded" href="?ui_language=german">🇩🇪 <?= _("German") ?></a></li>
                    <li><a class="link-dark rounded" href="?ui_language=portuguese">🇵🇹 <?= _("Portuguese") ?></a></li>
                    <li><a class="link-dark rounded" href="?ui_language=brazilian">🇧🇷 <?= _("Brazilian") ?></a></li>
                    <li><a class="link-dark rounded" href="?ui_language=italian">🇮🇹 <?= _("Italian") ?></a></li>
                    <li><a class="link-dark rounded" href="?ui_language=romanian">🇷🇴 <?= _("Romanian") ?></a></li>
                    <li><a class="link-dark rounded" href="?ui_language=chinese">🇨🇳 <?= _("Chinese") ?></a></li>
                    <li><a class="link-dark rounded" href="?ui_language=polish">🇵🇱 <?= _("Polish") ?></a></li>
                    <li><a class="link-dark rounded" href="?ui_language=russian">🇷🇺 <?= _("Russian") ?></a></li>
                    <li><a class="link-dark rounded" href="?ui_language=turkish">🇹🇷 <?= _("Turkish") ?></a></li>
                    <li><a class="link-dark rounded" href="?ui_language=urdu">🇵🇰 <?= _("Urdu") ?></a></li>
                    <li><a class="link-dark rounded" href="?ui_language=ukrainian">🇺🇦 <?= _("Ukrainian") ?></a></li>
                    <li><a class="link-dark rounded" href="?ui_language=farsi">🇮🇷 <?= _("Farsi") ?></a></li>
                    <li><a class="link-dark rounded" href="?ui_language=greek">🇬🇷 <?= _("Greek") ?></a></li>
                    <li><a class="link-dark rounded" href="?ui_language=indonesian">🇮🇩 <?= _("Indonesian") ?></a></li>
                </ul>
            </div>
        </nav>
        <main id="main-content" class="col-md-9 col-lg-10 pt-3">

<?php else: ?>
<div class="container-fluid">
    <div class="row">
        <main id="main-content" class="col m-1">
<?php endif ?>

