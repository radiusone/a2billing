<?php

use A2billing\Agent;

require_once("header.php");
require_once __DIR__ . "/../../common/lib/agent.defines.php";
/**
 * @var string $popup_select
 * @var int|null $menu_section
 */
$menu_section ??= 0;
?>

<?php if (empty($popup_select)): ?>
<header>
    <div class="px-3 py-2 bg-dark text-white">
        <div class="container-fluid">
            <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-md-end">
                <h4 class="d-flex align-items-center my-2 my-md-0 me-md-auto text-white text-decoration-none">
                    A2Billing
                </h4>
                <ul class="nav col-12 col-lg-auto my-2 justify-content-center my-md-0 text-small">
                    <li>
                        <a href="PP_intro.php" class="nav-link text-secondary">
                            <svg class="bi d-block mx-auto mb-1" width="24" height="24">
                                <use xlink:href="#home"></use>
                            </svg>
                            <?= _("Home") ?>
                        </a>
                    </li>
                    <li class="dropdown">
                        <a href="#" id="dropdownUser" class="nav-link text-white dropdown-toggle" data-bs-toggle="dropdown">
                            <svg class="bi d-block mx-auto mb-1" width="24" height="24">
                                <use xlink:href="#people-circle"></use>
                            </svg>
                            <?= _("Account") ?>
                        </a>
                        <ul class="dropdown-menu shadow" aria-labelledby="dropdownUser">
                        <?php if (Agent::allowed(Agent::ACX_MYACCOUNT)): ?>
                            <li>
                                <a href="agentinfo.php" class="dropdown-item">
                                    <?= _("Account Information") ?>
                                </a>
                            </li>
                            <li>
                                <a href="A2B_entity_remittance_request.php" class="dropdown-item">
                                    <?= _("Historic Remittance") ?>
                                </a>
                            </li>
                        <?php endif ?>
                            <li>
                                <a href="A2B_update_password.php?form_action=ask-edit" class="dropdown-item">
                                    <?= _("Change Password") ?>
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php?logout=true"><?= _("Logout") ?></a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</header>

<div class="container-fluid">
    <div class="row">
        <nav class="col-md-3 col-lg-2 flex-shrink-0 p-3 bg-light text-capitalize">
            <ul class="list-unstyled ps-0">

                <?php if (Agent::allowed(Agent::ACX_CUSTOMER)): ?>
                <li class="mb-1">
                    <button class="btn btn-toggle align-items-center rounded collapsed" data-bs-toggle="collapse" data-bs-target="#customer-collapse" aria-expanded="<?= $menu_section === 1 ? "true" : "false" ?>">
                        <?= _("Customers") ?>
                    </button>
                    <div class="collapse <?= $menu_section === 1 ? "show" : "" ?>" id="customer-collapse">
                        <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
                            <li><a class="link-dark rounded" href="A2B_entity_card.php"><?= _("List Customers") ?></a></li>
                            <li><a class="link-dark rounded" href="A2B_entity_callerid.php"><?= _("Caller-ID") ?></a></li>
                            <?php if (Agent::allowed(Agent::ACX_CALL_REPORT)): ?>
                            <li><a class="link-dark rounded" href="A2B_report_card_history.php"><?= _("Card History") ?></a></li>
                            <?php endif ?>
                            <?php if (Agent::allowed(Agent::ACX_VOIPCONF)): ?>
                            <li><a class="link-dark rounded" href="A2B_entity_friend.php?voip_conf=sip"><?= _("VoIP Settings") ?></a></li>
                            <?php endif ?>
                        </ul>
                    </div>
                </li>
                <?php endif ?>

                <?php if (Agent::allowed(Agent::ACX_SIGNUP)): ?>
                <li class="mb-1">
                    <button class="btn btn-toggle align-items-center rounded collapsed" data-bs-toggle="collapse" data-bs-target="#agent-collapse" aria-expanded="<?= $menu_section === 8 ? "true" : "false" ?>">
                        <?= _("Signup") ?>
                    </button>
                    <div class="collapse <?= $menu_section === 8 ? "show" : "" ?>" id="agent-collapse">
                        <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
                            <li><a class="link-dark rounded" href="A2B_entity_signup_agent.php"><?= _("Signup URLs") ?></a></li>
                            <li><a class="link-dark rounded" href="A2B_signup_agent.php"><?= _("Add New Signup URL") ?></a></li>
                        </ul>
                    </div>
                </li>
                <?php endif ?>

                <?php if (Agent::allowed(Agent::ACX_BILLING)): ?>
                <li class="mb-1">
                    <button class="btn btn-toggle align-items-center rounded collapsed" data-bs-toggle="collapse" data-bs-target="#admin-collapse" aria-expanded="<?= $menu_section === 2 ? "true" : "false" ?>">
                        <?= _("Billing") ?>
                    </button>
                    <div class="collapse <?= $menu_section === 2 ? "show" : "" ?>" id="admin-collapse">
                        <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
                            <li><a class="link-dark rounded" href="A2B_entity_moneysituation.php"><?= _("Account Balance") ?></a></li>
                            <li><a class="link-dark rounded" href="A2B_entity_logrefill_agent.php"><?= _("Own Refills") ?></a></li>
                            <li><a class="link-dark rounded" href="A2B_entity_payment_agent.php"><?= _("Own Payments") ?></a></li>
                            <li><a class="link-dark rounded" href="A2B_entity_logrefill.php"><?= _("Customer Refills") ?></a></li>
                            <li><a class="link-dark rounded" href="A2B_entity_payment.php"><?= _("Customer Payments") ?></a></li>
                            <li><a class="link-dark rounded" href="A2B_entity_paymentlog.php"><?= _("Payment Log") ?></a></li>
                            <li><a class="link-dark rounded" href="A2B_entity_commission.php"><?= _("Commissions") ?></a></li>
                        </ul>
                    </div>
                </li>
                <?php endif ?>


                <?php if (Agent::allowed(Agent::ACX_RATECARD)): ?>
                <li class="mb-1">
                    <button class="btn btn-toggle align-items-center rounded collapsed" data-bs-toggle="collapse" data-bs-target="#rate-collapse" aria-expanded="<?= $menu_section === 3 ? "true" : "false" ?>">
                        <?= _("Rates") ?>
                    </button>
                    <div class="collapse <?= $menu_section === 3 ? "show" : "" ?>" id="rate-collapse">
                        <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
                            <li>
                                <a class="link-dark rounded" href="A2B_entity_def_ratecard.php"><?= _("Browse Rates") ?></a>
                            </li>
                        </ul>
                    </div>
                </li>
                <?php endif ?>


                <?php if (Agent::allowed(Agent::ACX_CALL_REPORT)): ?>
                <li class="mb-1">
                    <button class="btn btn-toggle align-items-center rounded collapsed" data-bs-toggle="collapse" data-bs-target="#report-collapse" aria-expanded="<?= $menu_section === 6 ? "true" : "false" ?>">
                        <?= _("Call Reports") ?>
                    </button>
                    <div class="collapse <?= $menu_section === 6 ? "show" : "" ?>" id="report-collapse">
                        <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
                            <li><a class="link-dark rounded" href="A2B_report_calls.php"><?= _("CDRs") ?></a></li>
                            <li><a class="link-dark rounded" href="A2B_report_monthly.php"><?= _("Monthly Traffic") ?></a></li>
                        </ul>
                    </div>
                </li>
                <?php endif ?>

                <?php if (Agent::allowed(Agent::ACX_SUPPORT)): ?>
                <li class="mb-1">
                    <button class="btn btn-toggle align-items-center rounded collapsed" data-bs-toggle="collapse" data-bs-target="#support-collapse" aria-expanded="<?= $menu_section === 7 ? "true" : "false" ?>">
                        <?= _("Support") ?>
                    </button>
                    <div class="collapse <?= $menu_section === 7 ? "show" : "" ?>" id="support-collapse">
                        <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
                            <li><a class="link-dark rounded" href="A2B_ticket.php"><?= _("Customer Tickets") ?></a></li>
                            <li><a class="link-dark rounded" href="A2B_support.php"><?= _("View and Create Tickets") ?></a></li>
                        </ul>
                    </div>
                </li>
                <?php endif ?>

                <li class="mb-1">
                    <button class="btn btn-toggle align-items-center rounded collapsed" data-bs-toggle="collapse" data-bs-target="#language-collapse" aria-expanded="false">
                        <?= _("Language") ?>
                    </button>
                    <div class="collapse" id="language-collapse">
                        <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
                            <li><a class="link-dark rounded" href="PP_intro.php?ui_language=english">🇬🇧 English</a></li>
                            <li><a class="link-dark rounded" href="PP_intro.php?ui_language=brazilian">🇧🇷 Brazilian</a></li>
                            <li><a class="link-dark rounded" href="PP_intro.php?ui_language=romanian">🇷🇴 Romanian</a></li>
                            <li><a class="link-dark rounded" href="PP_intro.php?ui_language=french">🇫🇷 French</a></li>
                            <li><a class="link-dark rounded" href="PP_intro.php?ui_language=spanish">🇪🇸 Spanish</a></li>
                            <li><a class="link-dark rounded" href="PP_intro.php?ui_language=greek">🇬🇷 Greek</a></li>
                            <li><a class="link-dark rounded" href="PP_intro.php?ui_language=italian">🇮🇹 Italian</a></li>
                            <li><a class="link-dark rounded" href="PP_intro.php?ui_language=chinese">🇨🇳 Chinese</a></li>
                        </ul>
                    </div>
                </li>
            </ul>
        </nav>
        <main id="main-content" class="col-md-9 col-lg-10 pt-3">

<?php else: ?>
<div class="container-fluid">
    <div class="row">
        <main id="main-content" class="col m-1">
<?php endif ?>

