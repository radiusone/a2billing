<?php

use A2billing\A2Billing;

/**
 * @return string[]
 */
function getMsgTypeList(): array
{
    return [
        _("INFO"),
        _("SUCCESS"),
        _("WARNING"),
        _("ERROR"),
    ];
}

/**
 * @return array<string,string>
 */
function getLanguages(): array
{
    return [
        "en" => _("ENGLISH"),
        "es" => _("SPANISH"),
        "fr" => _("FRENCH"),
        "ru" => _("RUSSIAN"),
        "br" => _("BRAZILIAN"),
    ];
}

/**
 * @return string[]
 */
function getRestrictionList(): array
{
    return [
        _("UNRESTRICTED"),
        _("CAN'T CALL RESTRICTED NUMBERS"),
        _("CAN ONLY CALL RESTRICTED NUMBERS"),
    ];
}

/**
 * @return string[]
 */
function getYesNoList(): array
{
    return [
        1 => _("Yes"),
        0 => _("No"),
    ];
}

/**
 * @return string[]
 */
function getActivationList(): array
{
    return [
        _("Inactive"),
        _("Active"),
    ];
}

/**
 * @return array<string,string>
 */
function getActivationTrueFalseList(): array
{
    return [
        "t" => _("Active"),
        "f" => _("Inactive"),
    ];
}

/**
 * @return string[]
 */
function getBillingTypeList(): array
{
    return [
        _("Fix per month + dialoutrate"),
        _("Fix per month"),
        _("Only dialout rate"),
        _("Free"),
    ];
}

/**
 * @return string[]
 */
function getBillingTypeShortList(): array
{
    return [
        _("Fix+Dial"),
        _("Fix"),
        _("Dial"),
        _("Free"),
    ];
}

/**
 * @return string[]
 */
function getPaidTypeList(): array
{
    return [
        _("PREPAID CARD"),
        _("POSTPAID CARD"),
    ];
}

/**
 * @return string[]
 */
function getInvoiceStatusList(): array
{
    return [
        _('OPEN'),
        _('CLOSED'),
    ];
}

/**
 * @return string[]
 */
function getInvoicePaidStatusList(): array
{
    return [
        _('UNPAID'),
        _('PAID'),
    ];
}

/**
 * @return string[]
 */
function getPaymentStateList(): array
{
    return [
        _("New"),
        _("Proceed"),
        _("In Process"),
    ];
}

/**
 * @return string[]
 */
function getPackagesTypeList(): array
{
    return [
        _("Unlimited calls"),
        _("Number of Free calls"),
        _("Free seconds"),
    ];
}

/**
 * @return string[]
 */
function getTicketViewedList(): array
{
    return [
        '<span class="badge text-bg-primary">' . _('VIEWED') . '</span>',
        '<span class="badge text-bg-danger">' . _('NEW') . '</span>',
    ];
}

/**
 * @return string[]
 */
function getDialStatusList(): array
{
    return [
        _("UNKNOWN"),
        _("ANSWER"),
        _("BUSY"),
        _("NOANSWER"),
        _("CANCEL"),
        _("CONGESTION"),
        _("CHANUNAVAIL"),
        _("DONTCALL"),
        _("TORTURE"),
        _("INVALIDARGS"),
    ];
}

/**
 * @return string[]
 */
function getCardStatus_List(): array
{
    return [
        1 => _("ACTIVE"),
        0 => _("CANCELLED"),
        2 => _("NEW"),
        3 => _("WAITING-MAILCONFIRMATION"),
        4 => _("RESERVED"),
        5 => _("EXPIRED"),
        6 => _("SUSPENDED FOR UNDERPAYMENT"),
        7 => _("SUSPENDED FOR LITIGATION"),
        8 => _("WAITING SUBSCRIPTION PAYMENT"),
    ];
}

/**
 * @return string[]
 */
function getCardStatus_Acronym_List(): array
{
    return [
        1 => abbr(_("ACT"), _("ACTIVE")),
        0 => abbr(_("CANC"), _("CANCELLED")),
        2 => _("NEW"),
        3 => abbr(_("WAIT"), _("WAITING-MAILCONFIRMATION")),
        4 => abbr(_("RES"), _("RESERVED")),
        5 => abbr(_("EXP"), _("EXPIRED")),
        6 => abbr(_("SUS-PAY"), _("SUSPENDED FOR UNDERPAYMENT")),
        7 => abbr(_("SUS-LIT"), _("SUSPENDED FOR LITIGATION")),
        8 => abbr(_("WAIT-PAY"), _("WAITING SUBSCRIPTION PAYMENT")),
    ];
}

/**
 * @return string[]
 */
function getCardAccess_List(): array
{
    return [
        1 => _("SIMULTANEOUS ACCESS"),
        0 => _("INDIVIDUAL ACCESS"),
    ];
}

function getCardExpire_List(): array
{
    return [
        _("NO EXPIRY"),
        _("EXPIRE DATE"),
        _("EXPIRE DAYS SINCE FIRST USE"),
        _("EXPIRE DAYS SINCE CREATION"),
    ];
}

/**
 * @return string[]
 */
function getRefillType_List(): array
{
    return [
        _("AMOUNT"),
        _("CORRECTION"),
        _("EXTRA FEE"),
        _("AGENT REFUND"),
    ];
}

/**
 * @return string[]
 */
function getRemittanceType_List(): array
{
    return [
        _("TO BALANCE"),
        _("TO BANK"),
    ];
}

/**
 * @return string[]
 */
function getRemittanceStatus_List(): array
{
    return [
        _("WAITING"),
        _("ACCEPTED"),
        _("REFUSED"),
        _("CANCELLED"),
    ];
}

/**
 * @return string[]
 */
function getInvoiceDay_List(): array
{
    return array_combine(
        range(1, 28),
        array_map(fn ($v) => sprintf("%02d", $v), range(1, 28))
    );
}

/**
 * @return string[]
 */
function getDiscount_List(): array
{
    $discount_list = array_combine(
        range(0, 99),
        array_map(fn ($v) => "%v%", range(0, 99))
    );
    $discount_list[0] = _("NO DISCOUNT");

    return $discount_list;
}

/**
 * @param A2Billing $A2B
 * @return string[]
 */
function getLimitNotify_List(A2Billing $A2B): array
{
    // Possible value to notify the user
    $limits_notify = explode(":", $A2B->config['notifications']['values_notifications']);
    $limits_notify[-1] = _("NOT DEFINED");

    return $limits_notify;
}

/**
 * @param A2Billing $A2B
 * @return string[]
 */
function getMusicOnHold_List(A2Billing $A2B): array
{
    $ct = $A2B->config['webui']['num_musiconhold_class'];
    $musiconhold_list = ["No MusicOnHold"];
    for ($i = 1; $i <= $ct; $i++) {
        $musiconhold_list["acc_$i"]  = "MUSICONHOLD CLASS ACC_$i";
    }

    return $musiconhold_list;
}
