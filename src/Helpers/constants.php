<?php

use A2billing\A2Billing;

    function getMsgTypeList(): array
    {
        return [
            [_("INFO"), "0", "msg_info"],
            [_("SUCCESS"), "1", "msg_success"],
            [_("WARNING"), "2", "msg_warning"],
            [_("ERROR"), "3", "msg_error"],
        ];
    }

    function getLanguagesList(): array
    {
        return [
            [_("ENGLISH"), "en"],
            [_("SPANISH"), "es"],
            [_("FRENCH"),  "fr"],
            [_("RUSSIAN"), "ru"],
            [_("BRAZILIAN"), "br"],
        ];
    }

    function getLanguages(): array
    {
        return [
            "en" => [_("ENGLISH")],
            "es" => [_("SPANISH")],
            "fr" => [_("FRENCH")],
            "ru" => [_("RUSSIAN")],
            "br" => [_("BRAZILIAN")],
        ];
    }

    function getRestrictionList(): array
    {
        return [
            [_("UNRESTRICTED"), "0"],
            [_("CAN'T CALL RESTRICTED NUMBERS"), "1"],
            [_("CAN ONLY CALL RESTRICTED NUMBERS"),  "2"],
        ];
    }

    function getYesNoList(): array
    {
        return [
            1 => [_("Yes"), "1"],
            0 => [_("No"), "0"],
        ];
    }

    function getActivationList(): array
    {
        return [
            [_("Inactive"), "0"],
            [_("Active"), "1"],
        ];
    }

    function getActivationTrueFalseList(): array
    {
        return [
            "t" => [_("Active"), "t"],
            "f" => [_("Inactive"), "f"],
        ];
    }

    function getBillingTypeList(): array
    {
        return [
            [_("Fix per month + dialoutrate"), "0"],
            [_("Fix per month"), "1"],
            [_("Only dialout rate"), "2"],
            [_("Free"), "3"],
        ];
    }

    function getBillingTypeShortList(): array
    {
        return [
            [_("Fix+Dial"), "0"],
            [_("Fix"), "1"],
            [_("Dial"), "2"],
            [_("Free"), "3"],
        ];
    }

    function getPaidTypeList(): array
    {
        return [
            [_("PREPAID CARD"), "0"],
            [_("POSTPAID CARD"), "1"],
        ];
    }

    function getInvoiceStatusList(): array
    {
        return [
            [_('OPEN'), '0'],
            [_('CLOSE'), '1'],
        ];
    }

    function getInvoicePaidStatusList(): array
    {
        return [
            [_('UNPAID'), '0'],
            [_('PAID'), '1'],
        ];
    }

    function getMonthList(): array
    {
        return [
            1 => [_('January'), '1'],
            2 => [_('February'), '2'],
            3 => [_('March'), '3'],
            4 => [_('April'), '4'],
            5 => [_('May'), '5'],
            6 => [_('June'), '6'],
            7 => [_('July'), '7'],
            8 => [_('August'), '8'],
            9 => [_('September'), '9'],
            10 => [_('October'), '10'],
            11 => [_('November'), '11'],
            12 => [_('December'), '12'],
        ];
    }

    function getPaymentStateList(): array
    {
        return [
            [_("New"), "0"],
            [_("Proceed"), "1"],
            [_("In Process"), "2"],
        ];
    }

    function getPackagesTypeList(): array
    {
        return [
            [_("Unlimited calls"), "0"],
            [_("Number of Free calls"), "1"],
            [_("Free seconds"), "2"],
        ];
    }

    function getTicketPriorityList(): array
    {
        return [
            [_("NONE"), "0"],
            [_("LOW"), "1"],
            [_("MEDIUM"), "2"],
            [_("HIGH"), "3"],
        ];
    }

    function getTicketViewedList(): array
    {
        return [
            [_('VIEWED'), "0"],
            ['<strong style="font-size:8px; color:#B00000; background-color:white; border:solid 1px;"> &nbsp;'._('NEW').'&nbsp;</strong>', '1'],
        ];
    }

    function getDialStatusList(): array
    {
        return [
            [_("UNKNOWN"), "0"],
            [_("ANSWER"), "1"],
            [_("BUSY"), "2"],
            [_("NOANSWER"), "3"],
            [_("CANCEL"), "4"],
            [_("CONGESTION"), "5"],
            [_("CHANUNAVAIL"), "6"],
            [_("DONTCALL"), "7"],
            [_("TORTURE"), "8"],
            [_("INVALIDARGS"), "9"],
        ];
    }

    function getCardStatus_List(): array
    {
        return [
            1 => [_("ACTIVE"), "1"],
            0 => [_("CANCELLED"), "0"],
            2 => [_("NEW"), "2"],
            3 => [_("WAITING-MAILCONFIRMATION"), "3"],
            4 => [_("RESERVED"), "4"],
            5 => [_("EXPIRED"), "5"],
            6 => [_("SUSPENDED FOR UNDERPAYMENT"), "6"],
            7 => [_("SUSPENDED FOR LITIGATION"), "7"],
            8 => [_("WAITING SUBSCRIPTION PAYMENT"), "8"],
        ];
    }

    function getCardStatus_Acronym_List(): array
    {
        return [
            1 => ["acronym title='" . _("ACTIVE") . "'>" . _("ACTIVE") . "</acronym>", "1"],
            0 => ["acronym title='" . _("CANCELLED") . "'>" . _("CANCEL") . "</acronym>", "0"],
            2 => ["acronym title='" . _("NEW") . "'>" . _("NEW") . "</acronym>", "2"],
            3 => ["acronym title='" . _("WAITING-MAILCONFIRMATION") . "'>" . _("WAITING") . "</acronym>", "3"],
            4 => ["acronym title='" . _("RESERVED") . "'>" . _("RESERVED") . "</acronym>", "4"],
            5 => ["acronym title='" . _("EXPIRED") . "'>" . _("EXPIRED") . "</acronym>", "5"],
            6 => ["acronym title='" . _("SUSPENDED FOR UNDERPAYMENT") . "'>" . _("SUS-PAY") . "</acronym>", "6"],
            7 => ["acronym title='" . _("SUSPENDED FOR LITIGATION") . "'>" . _("SUS-LIT") . "</acronym>", "7"],
            8 => ["acronym title='" . _("WAITING SUBSCRIPTION PAYMENT") . "'>" . _("WAIT-PAY") . "</acronym>", "8"],
        ];
    }

    function getCardAccess_List(): array
    {
        return [
            1 => [_("SIMULTANEOUS ACCESS"), "1"],
            0 => [_("INDIVIDUAL ACCESS"), "0"],
        ];
    }

    function getCardExpire_List(): array
    {
        return [
            [_("NO EXPIRY"), "0"],
            [_("EXPIRE DATE"), "1"],
            [_("EXPIRE DAYS SINCE FIRST USE"), "2"],
            [_("EXPIRE DAYS SINCE CREATION"), "3"],
        ];
    }

    function getRefillType_List(): array
    {
        return [
            [_("AMOUNT"), "0"],
            [_("CORRECTION"), "1"],
            [_("EXTRA FEE"), "2"],
            [_("AGENT REFUND"), "3"],
        ];
    }

    function getRemittanceType_List(): array
    {
        return [
            [_("TO BALANCE"), "0"],
            [_("TO BANK"), "1"],
        ];
    }

    function getRemittanceStatus_List(): array
    {
        return [
            [_("WAITING"), "0"],
            [_("ACCEPTED"), "1"],
            [_("REFUSED"), "2"],
            [_("CANCELLED"), "3"],
        ];
    }

    function getInvoiceDay_List(): array
    {
        $invoiceday_list = [];
        for ($k = 1; $k <= 28; $k++) {
            $invoiceday_list[$k]  = [sprintf("%02d", $k), "$k"];
        }

        return $invoiceday_list;
    }

    function getDiscount_List(): array
    {
        $discount_list  = [];
        $discount_list["0.00"] = [_("NO DISCOUNT"), "0.00"];
        for ($i = 1; $i <= 99; $i++) {
            $discount_list["$i.00"] = ["$i%","$i.00"];
        }

        return $discount_list;
    }

    function getLimitNotify_List(A2Billing $A2B): array
    {
        // Possible value to notify the user
        $limits_notify = [];
        $values = explode(":", $A2B->config['notifications']['values_notifications']);
        foreach ($values as $val) {
             $limits_notify[] = [$val, $val];
        }

        return $limits_notify;
    }

    function getMusicOnHold_List(A2Billing $A2B): array
    {
        $ct = $A2B->config['webui']['num_musiconhold_class'];
        $musiconhold_list = [["No MusicOnHold", ""]];
        for ($i = 1; $i <= $ct; $i++) {
            $musiconhold_list[]  = ["MUSICONHOLD CLASS ACC_$i", "acc_$i"];
        }

        return $musiconhold_list;
    }
