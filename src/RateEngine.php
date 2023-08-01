<?php

namespace A2billing;

use A2billing\PhpAgi\Agi;

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of A2Billing (http://www.a2billing.net/)
 *
 * A2Billing, Commercial Open Source Telecom Billing platform,
 * powered by Star2billing S.L. <http://www.star2billing.com/>
 *
 * @copyright   Copyright © 2004-2015 - Star2billing S.L.
 * @copyright   Copyright © 2022 RadiusOne Inc.
 * @author      Belaid Arezqui <areski@gmail.com>
 * @author      Michael Newton <mnewton@goradiusone.com>
 * @license     http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @package     A2Billing
 * @contributor Steve Dommett <steve@st4vs.net>
 *              Belaid Rachid <rachid.belaid@gmail.com>
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

class RateEngine
{
    public bool $debug_st = false;

    public array $ratecard_obj = [];

    public array $freetimetocall_left = [];
    public array $freecall = [];
    public array $package_to_apply = [];

    public int $number_trunk        = 0;
    public float $lastcost          = 0;
    public float $lastbuycost       = 0;
    public int $answeredtime        = 0;
    public int $real_answeredtime   = 0;
    public string $dialstatus       = "";
    public int $usedratecard        = 0;
    public bool $webui              = true;
    public int $usedtrunk           = 0;
    public int $freetimetocall_used = 0;

    /* CONSTRUCTOR */
    public function __construct()
    {
    }

    /* Reinit */
    public function Reinit()
    {
        $this->number_trunk = 0;
        $this->answeredtime = 0;
        $this->real_answeredtime = 0;
        $this->dialstatus = '';
        $this->usedratecard = 0;
        $this->usedtrunk = 0;
        $this->lastcost = 0;
        $this->lastbuycost = 0;

    }

    /*
        RATE ENGINE
        CALCUL THE RATE ACCORDING TO THE RATEGROUP, LCR - RATECARD
    */
    public function rate_engine_findrates(A2Billing $A2B, string $phonenumber, int $tariffgroupid): int
    {
        global $agi;

        // Check if we want to force the call plan
        if (is_numeric($A2B->agiconfig['force_callplan_id']) && ($A2B->agiconfig['force_callplan_id'] > 0)) {
            $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "force the call plan : " . $A2B->agiconfig['force_callplan_id']);
            $tariffgroupid = $A2B->tariff = $A2B->agiconfig['force_callplan_id'];
        }

        if ($this->webui) {
            $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[CC_asterisk_rate-engine: ($tariffgroupid, $phonenumber)]");
        }

        /***  0 ) CODE TO RETURN THE DAY OF WEEK + CREATE THE CLAUSE  ***/

        $daytag = date("w"); // Day of week ( Sunday = 0 .. Saturday = 6 )
        $hours = date("G"); // Hours in 24h format ( 0-23 )
        $minutes = date("i"); // Minutes (00-59)
        $daytag = $daytag === "0" ? 6 : (int)$daytag - 1;
        if ($this->debug_st) {
            echo "$daytag $hours $minutes <br>";
        }
        // Race condiction on $minutes ?!
        $minutes_since_monday = ($daytag * 1440) + ($hours * 60) + $minutes;
        if ($this->debug_st) {
            echo "$minutes_since_monday<br> ";
        }

        $mydnid = $mycallerid = "";
        if (strlen($A2B->dnid)) {
            $mydnid = $A2B->dnid;
        }
        if (strlen($A2B->CallerID)) {
            $mycallerid = $A2B->CallerID;
        }

        if ($this->webui) {
            $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[CC_asterisk_rate-engine - CALLERID : " . $A2B->CallerID . "]");
        }

        // $prefixclause to allow good DB servers to use an index rather than sequential scan
        // justification at http://forum.asterisk2billing.org/viewtopic.php?p=9620#9620
        $max_len_prefix = min(strlen($phonenumber), 15); // don't match more than 15 digits (the most I have on my side is 8 digit prefixes)
        $prefix_params = [];
        $prefixclause = '(';
        while ($max_len_prefix > 0) {
            $prefixclause .= "dialprefix=? OR ";
            $prefix_params[] = substr($phonenumber, 0, $max_len_prefix);
            $max_len_prefix--;
        }
        $prefixclause .= "dialprefix='defaultprefix')";

        // match Asterisk/POSIX regex prefixes,  rewrite the Asterisk '_XZN.' characters to
        // POSIX equivalents, and test each of them against the dialed number
        $prefixclause .= " OR (dialprefix LIKE '&_%' ESCAPE '&' AND ? ";
        $prefixclause .= "REGEXP REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(CONCAT('^', dialprefix, '$'), ";
        $prefixclause .= "'X', '[0-9]'), 'Z', '[1-9]'), 'N', '[2-9]'), '.', '.+'), '_', ''))";
        $prefix_params[] = $phonenumber;

        // select group by 5 ... more easy to count
        $QUERY = "SELECT
        tariffgroupname, lcrtype, idtariffgroup, cc_tariffgroup_plan.idtariffplan, tariffname,
        destination, cc_ratecard.id AS ratecard_id, dialprefix, destination, buyrate,
        buyrateinitblock, buyrateincrement, rateinitial, initblock, billingblock,
        connectcharge, disconnectcharge, stepchargea, chargea, timechargea,
        billingblocka, stepchargeb, chargeb, timechargeb, billingblockb,
        stepchargec, chargec, timechargec, billingblockc,
        cc_tariffplan.id_trunk AS tp_id_trunk,
        tp_trunk.trunkprefix AS tp_trunkprefix, tp_trunk.providertech AS tp_providertech, tp_trunk.providerip AS tp_providerip, tp_trunk.removeprefix AS tp_removeprefix,
        cc_ratecard.id_trunk AS rt_id_trunk,
        rt_trunk.trunkprefix AS rt_trunkprefix, rt_trunk.providertech AS rt_providertech, rt_trunk.providerip AS rt_providerip, rt_trunk.removeprefix AS rt_removeprefix, musiconhold,
        tp_trunk.failover_trunk AS tp_failover_trunk, rt_trunk.failover_trunk AS rt_failover_trunk, tp_trunk.addparameter AS tp_addparameter_trunk, rt_trunk.addparameter AS rt_addparameter_trunk, id_outbound_cidgroup,
        id_cc_package_offer, tp_trunk.status, rt_trunk.status AS rt_status, tp_trunk.inuse AS tp_inuse, rt_trunk.inuse AS rt_inuse,
        tp_trunk.maxuse AS tp_maxuse, rt_trunk.maxuse AS rt_maxuse, tp_trunk.if_max_use AS tp_if_max_use, rt_trunk.if_max_use AS rt_if_max_use, cc_ratecard.rounding_calltime AS rounding_calltime,
        cc_ratecard.rounding_threshold AS rounding_threshold, cc_ratecard.additional_block_charge AS additional_block_charge, cc_ratecard.additional_block_charge_time AS additional_block_charge_time,
        cc_ratecard.additional_grace AS additional_grace, cc_ratecard.minimal_cost AS minimal_cost, disconnectcharge_after, announce_time_correction

        FROM cc_tariffgroup
        RIGHT JOIN cc_tariffgroup_plan ON cc_tariffgroup_plan.idtariffgroup = cc_tariffgroup.id
        INNER JOIN cc_tariffplan ON (cc_tariffplan.id = cc_tariffgroup_plan.idtariffplan)
        LEFT JOIN cc_ratecard ON cc_ratecard.idtariffplan = cc_tariffplan.id
        LEFT JOIN cc_trunk AS rt_trunk ON cc_ratecard.id_trunk = rt_trunk.id_trunk
        LEFT JOIN cc_trunk AS tp_trunk ON cc_tariffplan.id_trunk = tp_trunk.id_trunk

        WHERE ($prefixclause) AND cc_tariffgroup.id = ?
        AND startingdate <= CURRENT_TIMESTAMP AND (expirationdate > CURRENT_TIMESTAMP OR expirationdate IS NULL)
        AND startdate <= CURRENT_TIMESTAMP AND (stopdate > CURRENT_TIMESTAMP OR stopdate IS NULL)
        AND (starttime <= ? AND endtime >= ?)
        AND idtariffgroup = ?
        AND (dnidprefix = SUBSTRING(?, 1, length(dnidprefix)) OR (dnidprefix = 'all' AND 0 = (SELECT COUNT(dnidprefix) FROM cc_tariffgroup_plan RIGHT JOIN cc_tariffplan ON cc_tariffgroup_plan.idtariffplan = cc_tariffplan.id WHERE dnidprefix = SUBSTRING(?, 1, length(dnidprefix)) AND idtariffgroup = ?)))
        AND (calleridprefix = SUBSTRING(?, 1, length(calleridprefix)) OR (calleridprefix = 'all' AND 0 = (SELECT count(calleridprefix) FROM cc_tariffgroup_plan RIGHT JOIN cc_tariffplan ON cc_tariffgroup_plan.idtariffplan = cc_tariffplan.id WHERE calleridprefix = SUBSTRING(?, 1, length(calleridprefix)) AND idtariffgroup = ?)))
        ORDER BY LENGTH(dialprefix) DESC";

        $params = array_merge($prefix_params, [$tariffgroupid, $minutes_since_monday, $minutes_since_monday, $tariffgroupid, $mydnid, $mydnid, $tariffgroupid, $mycallerid, $tariffgroupid, $mycallerid]);
        $result = $A2B->DBHandle->GetAll($QUERY, $params);

        if ($result === false || $result === []) {

            return 0; // NO RATE FOR THIS NUMBER
        }

        if ($this->debug_st) {
            echo "::> Count Total result " . count($result) . "\n\n";
        }
        if ($this->webui) {
            $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[rate-engine: Count Total result " . count($result) . "]");
        }

        // CHECK IF THERE IS OTHER RATE THAT 'DEFAULT', IF YES REMOVE THE DEFAULT RATES
        // NOT NOT REMOVE SHIFT THEM TO THE END :P
        $ind_stop_default = -1;
        foreach ($result as $i => $row) {
            if ($row["dialprefix"] !== 'defaultprefix') {
                $ind_stop_default = $i;
                break;
            }
        }

        // IMPORTANT TO CUT THE PART OF THE defaultprefix CAUSE WE WILL APPLY THE SORT ACCORDING TO THE RATE
        // DEFAULPERFIX IS AN ESCAPE IN CASE OF NO RATE IS DEFINED, NOT BE COUNT WITH OTHER DURING THE SORT OF RATE
        if ($ind_stop_default > 0) {
            $result_defaultprefix = array_slice($result, 0, $ind_stop_default);
            $result = array_slice($result, $ind_stop_default, count($result) - $ind_stop_default);
        } else {
            $result_defaultprefix = [];
        }

        if ($A2B->agiconfig['lcr_mode'] == 0) {
            //1) REMOVE THOSE THAT HAVE A SMALLER DIALPREFIX
            $max_len_prefix = strlen($result[0]["dialprefix"]);
            for ($i = 1; $i < count($result); $i++) {
                if (strlen($result[$i]["dialprefix"]) < $max_len_prefix) {
                    break;
                }
            }
            $result = array_slice($result, 0, $i);
        } elseif ($A2B->agiconfig['lcr_mode'] == 1) {
            //1) REMOVE THOSE THAT HAVE THE LOWEST COST
            if ($this->webui) {
                $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[rate-engine: MYRESULT before sort \n" . json_encode($result));
            }
            // 3 - tariff plan, 5 - dialprefix
            // uh no, 5 is destination; 7 is dialprefix
            $sorted_result = $this->array_csort($result, '3', SORT_NUMERIC, '5', SORT_NUMERIC, SORT_DESC);
            if ($this->webui) {
                $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[rate-engine: MYRESULT after sort \n" . json_encode($sorted_result));
            }
            $mysearchvalue = [];
            $countdelete = 0;
            $resultcount = 0;
            for ($ii = 0; $ii < count($result) - 1; $ii++) {
                if (empty($sorted_result[$ii])) {
                    if ($this->webui) {
                        $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[rate-engine: Skipping for ii value " . $ii . " due to missing value]");
                    }
                    continue;
                }
                $row = $sorted_result[$ii];
                $mysearchvalue[$resultcount] = $row;
                if ($this->webui) {
                    $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[rate-engine: Begin for ii value " . $ii . "]");
                    $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[rate-engine: MYSEARCHCVALUE \n" . json_encode($mysearchvalue) . "]");
                }
                if (count($sorted_result) > 0) {
                    foreach ($sorted_result as $j=>$i) {
                        if ($this->webui) {
                            $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[rate-engine: foreach J=$j]");
                            $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[rate-engine:mysearchvalue[4]=$row[tariffname], i[4]=$i[tariffname]");
                            $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[rate-engine:mysearchvalue[3]=$row[idtariffplan], i[3]=$i[idtariffplan]");
                            $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[rate-engine:mysearchvalue[7]=$row[dialprefix], i[7]=$i[dialprefix]");
                        }
                        if ($row["idtariffplan"] === $i["idtariffplan"]) {
                            if (strlen($row["dialprefix"]) !== strlen($i["dialprefix"])) {
                                unset($sorted_result[$j]);
                                $countdelete = $countdelete + 1;
                                if ($this->webui) {
                                    $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[rate-engine: foreach: COUNTDELETE: " . $countdelete . "]");
                                    $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[rate-engine: foreach: MYRESULT count after delete: " . count($sorted_result) . "]");
                                }
                            }
                        }
                    } //end foreach
                    $sorted_result = array_values($sorted_result);
                    $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[rate-engine: MYRESULT  after foreach \n" . json_encode($sorted_result));
                    $resultcount++;
                    if ($this->webui) {
                        $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[rate-engine: Count MYRESULT after foreach=" . count($sorted_result) . "]");
                        $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[rate-engine: RESULTCOUNT=" . $resultcount . "]");
                    }
                }
                if ($this->webui) {
                    $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[rate-engine: End for II value " . $ii . "]");
                }
            }  //end for
            if ($this->webui) {
                $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[rate-engine: COUNTDELETE=" . $countdelete . "]");
                $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[rate-engine: MYRESULT  before unset \n" . json_encode($sorted_result));
            }
            if (count($result) > 1 and $countdelete != 0) {
                if ($this->webui) {
                    $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[rate-engine: LAST UNSET");
                }
                unset($mysearchvalue[$resultcount]);
                foreach ($mysearchvalue as $key => $value) {
                    if (is_null($value) or $value === "") {
                        unset($mysearchvalue[$key]);
                    }
                }
                $mysearchvalue = array_values($mysearchvalue);
                unset($sorted_result);
            }
            if ($this->webui) {
                $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[rate-engine: RESULTCOUNT" . $resultcount . "]");
                $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[rate-engine: MYRESULT  after delete \n" . json_encode($sorted_result ?? null));
                $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[rate-engine: MYSEARCHVALUE after delete \n" . json_encode($mysearchvalue));
                $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[rate-engine: Count Total result after 4 " . count($sorted_result ?? []) . "]");
            }
            if (count($result) > 1 and $countdelete != 0) {
                $result = $mysearchvalue;
            }
            if ($this->webui) {
                $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[rate-engine: RESULT  after delete \n" . json_encode($result));
            }
        }

        //2) TAKE THE VALUE OF LCTYPE
        //LCR : According to the buyer price -0 buyrate [col 6]
        //LCD : According to the seller price -1 rateinitial [col 9]

        // Thanks for the fix from the wiki :D next time email me, lol
        if (empty($result[0]["lcrtype"])) {
            $result = $this->array_csort($result, '9', SORT_ASC); //1
        } else {
            $result = $this->array_csort($result, '12', SORT_ASC); //1
        }

        // WE ADD THE DEFAULTPREFIX WE REMOVE BEFORE
        if ($ind_stop_default > 0) {
            $result = array_merge($result, $result_defaultprefix);
        }

        // 3) REMOVE THOSE THAT USE THE SAME TRUNK - MAKE A DISTINCT
        //    AND THOSE THAT ARE DISABLED.
        $distinct_result = [];
        $mylistoftrunk = [];
        foreach ($result as $i => $row) {
            if ((int)$row["rt_id_trunk"] === -1) {
                $status = (int)$row["status"];
                $mycurrenttrunk = (int)$row["tp_id_trunk"];
            } else {
                $status = (int)$row["rt_status"];
                $mycurrenttrunk = (int)$row["rt_id_trunk"];
            }

            // Check if we already have the same trunk in the ratecard
            if (($i === 0 || !in_array($mycurrenttrunk, $mylistoftrunk)) && $status === 1) {
                $distinct_result[] = $row;
            }

            if ($status === 1) {
                $mylistoftrunk[] = $mycurrenttrunk;
            }
        }

        $this->ratecard_obj = $distinct_result;
        $this->number_trunk = count($distinct_result);

        // if an extracharge DID number was called increase rates with the extracharge fee
        if (strlen($A2B->dnid) > 1 && is_array($A2B->agiconfig['extracharge_did']) && in_array($A2B->dnid, $A2B->agiconfig['extracharge_did'])) {
            $fee = $A2B->agiconfig['extracharge_fee'][array_search($A2B->dnid, $A2B->agiconfig['extracharge_did'])];
            $buyfee = $A2B->agiconfig['extracharge_buyfee'][array_search($A2B->dnid, $A2B->agiconfig['extracharge_did'])];
            $A2B->debug(A2Billing::INFO, $agi, __FILE__, __LINE__, "[CC_asterisk_rate-engine: Extracharge DID found: " . $A2B->dnid . ", extra fee: " . $fee . ", extra buy fee: " . $buyfee . "]");
            for ($i = 0; $i < count($this->ratecard_obj); $i++) {
                $this->ratecard_obj[$i]["buyrate"] += $buyfee;
                $this->ratecard_obj[$i]["rateinitial"] += $fee;
                $this->ratecard_obj[$i]["chargea"] += $fee;
                $this->ratecard_obj[$i]["chargeb"] += $fee;
                $this->ratecard_obj[$i]["chargec"] += $fee;
            }
        }

        if ($this->debug_st) {
            echo "::> Count Total distinct_result " . count($distinct_result) . "\n\n";
        }
        if ($this->webui) {
            $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[CC_asterisk_rate-engine: Count Total result " . count($distinct_result) . "]");
            $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[CC_asterisk_rate-engine: number_trunk " . $this->number_trunk . "]");
        }
        return 1;
    }


    /*
        RATE ENGINE - CALCUL TIMEOUT
        * CALCUL THE DURATION ALLOWED FOR THE CALLER TO THIS NUMBER
    */
    public function rate_engine_all_calcultimeout(A2Billing $A2B, int $credit): bool
    {
        global $agi;

        if ($this->webui) {
            $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[CC_RATE_ENGINE_ALL_CALCULTIMEOUT ($credit)]");
        }
        if (count($this->ratecard_obj) === 0) {
            return false;
        }

        for ($k = 0; $k < count($this->ratecard_obj); $k++) {
            $res_calcultimeout = $this->rate_engine_calcultimeout($A2B, $credit, $k);
            if ($this->webui) {
                $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[CC_RATE_ENGINE_ALL_CALCULTIMEOUT: k=$k - res_calcultimeout:$res_calcultimeout]");
            }

            if (substr($res_calcultimeout, 0, 5) == 'ERROR') {
                return false;
            }
        }

        return true;
    }

    /**
     *   RATE ENGINE - CALCUL TIMEOUT
     * CALCUL THE DURATION ALLOWED FOR THE CALLER TO THIS NUMBER
     */
    public function rate_engine_calcultimeout(A2Billing $A2B, int $credit, int $K = 0)
    {
        global $agi;

        $ratecard                 = $this->ratecard_obj[$K];
        $rateinitial              = a2b_round(abs($ratecard["rateinitial"]));
        $initblock                = (int)$ratecard["initblock"];
        $billingblock             = (int)$ratecard["billingblock"];
        $connectcharge            = a2b_round(abs($ratecard["connectcharge"]));
        $disconnectcharge         = a2b_round(abs($ratecard["disconnectcharge"]));
        $disconnectcharge_after   = (int)$ratecard["disconnectcharge_after"];
        $stepchargea              = a2b_round(abs($ratecard["stepchargea"]));
        $chargea                  = a2b_round(abs($ratecard["chargea"]));
        $timechargea              = (int)$ratecard["timechargea"];
        $billingblocka            = (int)$ratecard["billingblocka"];
        $stepchargeb              = a2b_round(abs($ratecard["stepchargeb"]));
        $chargeb                  = a2b_round(abs($ratecard["chargeb"]));
        $timechargeb              = (int)$ratecard["timechargeb"];
        $billingblockb            = (int)$ratecard["billingblockb"];
        $stepchargec              = a2b_round(abs($ratecard["stepchargec"]));
        $chargec                  = a2b_round(abs($ratecard["chargec"]));
        $timechargec              = (int)$ratecard["timechargec"];
        $billingblockc            = (int)$ratecard["billingblockc"];
        // ****************  PACKAGE PARAMETERS ****************
        $id_cc_package_offer      = (int)$ratecard["id_cc_package_offer"];
        $id_rate                  = (int)$ratecard["ratecard_id"];
        $initial_credit           = $credit;
        // CHANGE THIS - ONLY ALLOW FREE TIME FOR CUSTOMER THAT HAVE MINIMUM CREDIT TO CALL A DESTINATION



        $this->freetimetocall_left[$K] = 0;
        $this->freecall[$K] = false;
        $this->package_to_apply[$K] = null;

        //CHECK THE PACKAGES TO APPLY TO THIS RATES
        if ($id_cc_package_offer != -1) {

            $query_pakages = "SELECT cc_package_offer.id, packagetype, billingtype, startday, freetimetocall ".
                                "FROM cc_package_offer, cc_package_rate WHERE cc_package_offer.id = ?" .
                                " AND cc_package_offer.id = cc_package_rate.package_id AND cc_package_rate.rate_id = ?" .
                                " ORDER BY packagetype ASC";
            $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[PACKAGE IN:$query_pakages ]");
            $result_packages = $A2B->DBHandle->GetAll($query_pakages, [$id_cc_package_offer, $id_rate]);
            $idx_pack = 0;

            if ($result_packages !== false && $result_packages !== []) {
                $package_selected = false;

                while (!$package_selected && $idx_pack < count($result_packages)) {

                    $freetimetocall      = (int)$result_packages[$idx_pack]["freetimetocall"];
                    $packagetype         = (int)$result_packages[$idx_pack]["packagetype"];
                    $billingtype         = (int)$result_packages[$idx_pack]["billingtype"];
                    $startday            = (int)$result_packages[$idx_pack]["startday"];
                    $id_cc_package_offer = (int)$result_packages[$idx_pack]["id"];

                    $A2B->debug(A2Billing::INFO, $agi, __FILE__, __LINE__, "[ID PACKAGE  TO APPLY=$id_cc_package_offer - packagetype=$packagetype]");
                    switch ($packagetype) {
                        // 0 : UNLIMITED PACKAGE
                        //IF PACKAGE IS "UNLIMITED" SO WE DON'T NEED TO CALCULATE THE USED TIMES
                        case 0 : $this->freecall[$K] = true;
                                $package_selected = true;
                                $this->package_to_apply[$K] = ["id"=>$id_cc_package_offer, "label"=>"Unlimited calls", "type"=>$packagetype];
                                $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[Unlimited calls]");
                                break;
                        // 1 : FREE CALLS
                        //IF PACKAGE IS "NUMBER OF FREE CALLS"  AND WE CAN USE IT ELSE WE CHECK THE OTHERS PACKAGE LIKE FREE TIMES
                        case 1 :
                            if ($freetimetocall > 0) {
                                $number_calls_used =$A2B->free_calls_used($A2B->id_card, $id_cc_package_offer, $billingtype, $startday, "count");
                                if ($number_calls_used < $freetimetocall) {
                                    $this->freecall[$K] = true;
                                    $package_selected = true;
                                    $this->package_to_apply[$K] = ["id"=>$id_cc_package_offer, "label"=> "Number of Free calls", "type"=>$packagetype];
                                    $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[Number of Free calls]");
                                }
                            }
                            break;
                        //2 : FREE TIMES
                        case 2 :
                            // CHECK IF WE HAVE A FREETIME THAT CAN APPLY FOR THIS DESTINATION
                            if ($freetimetocall > 0) {
                                // WE NEED TO RETRIEVE THE AMOUNT OF USED MINUTE FOR THIS CUSTOMER ACCORDING TO BILLINGTYPE (Monthly ; Weekly) & STARTDAY
                                $this->freetimetocall_used = $A2B->free_calls_used($A2B->id_card, $id_cc_package_offer, $billingtype, $startday);
                                $this->freetimetocall_left[$K] = $freetimetocall - $this->freetimetocall_used;
                                if ($this->freetimetocall_left[$K] < 0) {
                                    $this->freetimetocall_left[$K] = 0;
                                }
                                if ($this->freetimetocall_left[$K] > 0) {
                                    $package_selected = true;
                                    $this->package_to_apply[$K] = ["id"=>$id_cc_package_offer, "label"=> "Free minutes", "type"=>$packagetype];
                                    $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[Free minutes - freetimetocall_used=$this->freetimetocall_used]");
                                }
                            }
                            $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[Free minutes - Break (freetimetocall_left=" . $this->freetimetocall_left[$K] . ")]");
                            break;
                    }
                    $idx_pack++;
                }
            }
        }

        $credit -= $connectcharge;
        if ($disconnectcharge_after === 0) {
            $credit -= $disconnectcharge;
            //no disconnenct charge on timeout if disconnectcharge_after is set
        }

        $callbackrate = [];
        if (($A2B->mode == 'cid-callback') || ($A2B->mode == 'all-callback')) {
            $callbackrate = [
                "ri"   => $rateinitial,
                "ib"   => $initblock,
                "bb"   => $billingblock,
                "cc"   => $connectcharge,
                "dc"   => $disconnectcharge,
                "sc_a" => $stepchargea,
                "tc_a" => $timechargea,
                "c_a"  => $chargea,
                "bb_a" => $billingblocka,
                "sc_b" => $stepchargeb,
                "tc_b" => $timechargeb,
                "c_b"  => $chargeb,
                "bb_b" => $billingblockb,
                "sc_c" => $stepchargec,
                "tc_c" => $timechargec,
                "c_c"  => $chargec,
                "bb_c" => $billingblockc,
            ];
        }

        $this->ratecard_obj[$K]['callbackrate'] = $callbackrate;
        $this->ratecard_obj[$K]['timeout'] = 0;
        $this->ratecard_obj[$K]['timeout_without_rules'] = 0;
        // used for the simulator
        $this->ratecard_obj[$K]['freetime_include_in_timeout'] = $this->freetimetocall_left[$K];

        // CHECK IF THE USER IS ALLOW TO CALL WITH ITS CREDIT AMOUNT
        /*
        Comment from Abdoulaye Siby
        This following "if" statement used to verify the minimum credit to call can be improved.
        This mininum credit should be calculated based on the destination, and the minimum billing block.
        */
        if ($credit < $A2B->agiconfig['min_credit_2call'] && $this->freecall[$K] === false && $this->freetimetocall_left[$K] <= 0) {
            $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[NO ENOUGH CREDIT TO CALL THIS NUMBER - ERROR CT1]");

            return "ERROR CT1";  //NO ENOUGH CREDIT TO CALL THIS NUMBER
        }

        $TIMEOUT = null;
        $answeredtime_1st_leg = 0;

        if ($rateinitial <= 0) {
            $TIMEOUT = (int)$A2B->agiconfig['maxtime_tocall_negatif_free_route'];
        } elseif ($this->freecall[$K]) {
            if ($this->package_to_apply[$K]["type"] == 0) {
                $TIMEOUT = (int)$A2B->agiconfig['maxtime_tounlimited_calls'];
            } else {
                $TIMEOUT = (int)$A2B->agiconfig['maxtime_tofree_calls'];
            }
            $this->ratecard_obj[$K]['freetime_include_in_timeout'] = $TIMEOUT;
        } elseif ($credit < $A2B->agiconfig['min_credit_2call'] && $this->freetimetocall_left[$K] > 0) {
            $TIMEOUT = $this->freetimetocall_left[$K];
        }
        if (!is_null($TIMEOUT)) {
            $this->ratecard_obj[$K]['timeout'] = $TIMEOUT;
            $this->ratecard_obj[$K]['timeout_without_rules'] = $TIMEOUT;
            if ($this->debug_st) {
                print_r($this->ratecard_obj[$K]);
            }

            return $TIMEOUT;
        }

        $calling_party_connectcharge = 0;
        $calling_party_disconnectcharge = 0;
        $calling_party_rateinitial = 0;
        if ($A2B->mode === 'callback') {
            $calling_party_rateinitial      = (int)$agi->get_variable('RI', true);
            $calling_party_connectcharge    = (int)$agi->get_variable('CC', true);
            $calling_party_disconnectcharge = (int)$agi->get_variable('DC', true);
        }

        // 2 KIND OF CALCULATION : PROGRESSIVE RATE & FLAT RATE
        // IF FLAT RATE
        if (empty($chargea) || empty($timechargea)) {

            if ($A2B->mode === 'callback') {
                /*
                Comment from Abdoulaye Siby
                In all-callback or cid-callback mode, the number of minutes for the call must be calculated
                according to the rates of both legs of the call.
                */

                $credit -= $calling_party_connectcharge;
                $credit -= $calling_party_disconnectcharge;
                $num_min = $credit / ($rateinitial + $calling_party_rateinitial);
                //I think that the answered time is in seconds
                $answeredtime_1st_leg = intval($agi->get_variable('ANSWEREDTIME', true));
            } else {
                $num_min = $credit / $rateinitial;
            }

            if ($this->debug_st) {
                echo "num_min:$num_min ($credit / $rateinitial)\n";
            }
            $num_sec = intval($num_min * 60) - $answeredtime_1st_leg;
            if ($this->debug_st) {
                echo "num_sec:$num_sec \n";
            }

            if ($billingblock > 0) {
                $mod_sec = $num_sec % $billingblock;
                $num_sec = $num_sec - $mod_sec;
            }

            $TIMEOUT = $num_sec;

        // IF PROGRESSIVE RATE
        } else {
            if ($this->debug_st) {
                echo "CYCLE A    TIMEOUT:$TIMEOUT\n";
            }
            // CYCLE A
            $credit -= $stepchargea;

            //if ($credit<=0) return "ERROR CT2"; //NO ENOUGH CREDIT TO CALL THIS NUMBER
            if ($credit <= 0) {
                if ($this->freetimetocall_left[$K] > 0) {
                    $this->ratecard_obj[$K]['timeout'] = $this->freetimetocall_left[$K];
                    if ($this->debug_st) {
                        print_r($this->ratecard_obj[$K]);
                    }
                    return $this->freetimetocall_left[$K];
                } else {
                    return "ERROR CT2"; //NO ENOUGH CREDIT TO CALL THIS NUMBER
                }
            }
            if ($chargea <= 0) {
                return "ERROR CHARGEA($chargea)";
            }
            $num_min = $credit / $chargea;
            if ($this->debug_st) {
                echo "            CYCLEA num_min:$num_min ($credit/$chargea)\n";
            }
            $num_sec = intval($num_min * 60);
            if ($this->debug_st) {
                echo "            CYCLEA num_sec:$num_sec \n";
            }
            if ($billingblocka > 0) {
                $mod_sec = $num_sec % $billingblocka;
                $num_sec = $num_sec - $mod_sec;
            }

            if ($num_sec > $timechargea && !empty($chargeb) && !empty($timechargeb)) {
                $TIMEOUT += $timechargea;
                $credit -= ($chargea / 60) * $timechargea;

                if ($this->debug_st) {
                    echo "        CYCLE B        TIMEOUT:$TIMEOUT\n";
                }
                // CYCLE B
                $credit -= $stepchargeb;
                if ($credit <= 0) {
                    $this->ratecard_obj[$K]['timeout'] = $TIMEOUT + $this->freetimetocall_left[$K];

                    return $TIMEOUT + $this->freetimetocall_left[$K]; //NO ENOUGH CREDIT TO GO TO THE CYCLE B
                }

                if ($chargeb <= 0) {
                    return "ERROR CHARGEB($chargeb)";
                }
                $num_min = $credit / $chargeb;
                if ($this->debug_st) {
                    echo "            CYCLEB num_min:$num_min ($credit/$chargeb)\n";
                }
                $num_sec = intval($num_min * 60);
                if ($this->debug_st) {
                    echo "            CYCLEB num_sec:$num_sec \n";
                }
                if ($billingblockb > 0) {
                    $mod_sec = $num_sec % $billingblockb;
                    $num_sec = $num_sec - $mod_sec;
                }

                if ($num_sec > $timechargeb && !empty($chargec) && !empty($timechargec)) {
                    $TIMEOUT += $timechargeb;
                    $credit -= ($chargeb / 60) * $timechargeb;

                    if ($this->debug_st) {
                        echo "                CYCLE C        TIMEOUT:$TIMEOUT\n";
                    }
                    // CYCLE C
                    $credit -= $stepchargec;
                    if ($credit <= 0) {
                        $this->ratecard_obj[$K]['timeout'] = $TIMEOUT + $this->freetimetocall_left[$K];

                        return $TIMEOUT + $this->freetimetocall_left[$K]; //NO ENOUGH CREDIT TO GO TO THE CYCLE C
                    }

                    if ($chargec <= 0) {
                        return "ERROR CHARGEC($chargec)";
                    }
                    $num_min = $credit / $chargec;
                    if ($this->debug_st) {
                        echo "            CYCLEC num_min:$num_min ($credit / $chargec)\n";
                    }
                    $num_sec = intval($num_min * 60);
                    if ($this->debug_st) {
                        echo "            CYCLEC num_sec:$num_sec \n";
                    }
                    if ($billingblockc > 0) {
                        $mod_sec = $num_sec % $billingblockc;
                        $num_sec = $num_sec - $mod_sec;
                    }

                    if ($num_sec > $timechargec) {
                        if ($this->debug_st) {
                            echo "        OUT CYCLE C        TIMEOUT:$TIMEOUT\n";
                        }
                        $TIMEOUT += $timechargec;
                        $credit -= ($chargec / 60) * $timechargec;

                        // IF CYCLE C IS FINISH USE THE RATEINITIAL
                        $num_min = $credit / $rateinitial;
                        if ($this->debug_st) {
                            echo "            OUT CYCLEC num_min:$num_min ($credit/$rateinitial)\n";
                        }
                        $num_sec = intval($num_min * 60);
                        if ($this->debug_st) {
                            echo "            OUT CYCLEC num_sec:$num_sec \n";
                        }
                        if ($billingblock > 0) {
                            $mod_sec = $num_sec % $billingblock;
                            $num_sec = $num_sec - $mod_sec;
                        }
                        // THIS IS THE END
                    }

                } elseif ($num_sec > $timechargeb) {
                    $TIMEOUT += $timechargeb;
                    if ($this->debug_st) {
                        echo "        OUT CYCLE B        TIMEOUT:$TIMEOUT\n";
                    }
                    $credit -= ($chargeb / 60) * $timechargeb;

                    // IF CYCLE B IS FINISH USE THE RATEINITIAL
                    $num_min = $credit / $rateinitial;
                    if ($this->debug_st) {
                        echo "            OUT CYCLEB num_min:$num_min ($credit/$rateinitial)\n";
                    }
                    $num_sec = intval($num_min * 60);
                    if ($this->debug_st) {
                        echo "            OUT CYCLEB num_sec:$num_sec \n";
                    }
                    if ($billingblock > 0) {
                        $mod_sec = $num_sec % $billingblock;
                        $num_sec = $num_sec - $mod_sec;
                    }
                    // THIS IS THE END
                }

            } elseif (($num_sec > $timechargea)) {
                $TIMEOUT += $timechargea;
                if ($this->debug_st) {
                    echo "        OUT CYCLE A        TIMEOUT:$TIMEOUT\n";
                }
                $credit -= ($chargea / 60) * $timechargea;

                // IF CYCLE A IS FINISH USE THE RATEINITIAL
                $num_min = $credit / $rateinitial;
                if ($this->debug_st) {
                    echo "            OUT CYCLEA num_min:$num_min ($credit/$rateinitial)\n";
                }
                $num_sec = intval($num_min * 60);
                if ($this->debug_st) {
                    echo "            OUT CYCLEA num_sec:$num_sec \n";
                }
                if ($billingblock > 0) {
                    $mod_sec = $num_sec % $billingblock;
                    $num_sec = $num_sec - $mod_sec;
                }
                // THIS IS THE END
            }
            $TIMEOUT += $num_sec;
        }
        //Call time to speak without rate rules... idiot rules
        $num_min_WR = $initial_credit / $rateinitial;
        $num_sec_WR = intval($num_min_WR * 60);
        $this->ratecard_obj[$K]['timeout_without_rules'] = $num_sec_WR + $this->freetimetocall_left[$K];

        $this->ratecard_obj[$K]['timeout'] = $TIMEOUT + $this->freetimetocall_left[$K];
        if ($this->debug_st) {
            print_r($this->ratecard_obj[$K]);
        }
        return $TIMEOUT + $this->freetimetocall_left[$K];
    }

    /*
     * RATE ENGINE - CALCUL COST OF THE CALL
     * - calcul the credit consumed by the call
     */
    public function rate_engine_calculcost(A2Billing $A2B, int $callduration, Agi $agi)
    {
        $K = $this->usedratecard;
        $used_ratecard = $this->ratecard_obj[$this->usedratecard];

        $buyrate                      = a2b_round(abs($used_ratecard["buyrate"]));
        $buyrateinitblock             = (int)$used_ratecard["buyrateinitblock"];
        $buyrateincrement             = (int)$used_ratecard["buyrateincrement"];

        $rateinitial                  = a2b_round(abs($used_ratecard["rateinitial"]));
        $initblock                    = (int)$used_ratecard["initblock"];
        $billingblock                 = (int)$used_ratecard["billingblock"];
        $connectcharge                = a2b_round(abs($used_ratecard["connectcharge"]));
        $disconnectcharge             = a2b_round(abs($used_ratecard["disconnectcharge"]));
        $disconnectcharge_after       = (int)$used_ratecard["disconnectcharge_after"];
        $stepchargea                  = a2b_round(abs($used_ratecard["stepchargea"]));
        $chargea                      = a2b_round(abs($used_ratecard["chargea"]));
        $timechargea                  = (int)$used_ratecard["timechargea"];
        $billingblocka                = (int)$used_ratecard["billingblocka"];
        $stepchargeb                  = a2b_round(abs($used_ratecard["stepchargeb"]));
        $chargeb                      = a2b_round(abs($used_ratecard["chargeb"]), 4);
        $timechargeb                  = (int)$used_ratecard["timechargeb"];
        $billingblockb                = (int)$used_ratecard["billingblockb"];
        $stepchargec                  = a2b_round(abs($used_ratecard["stepchargec"]));
        $chargec                      = a2b_round(abs($used_ratecard["chargec"]), 4);
        $timechargec                  = (int)$used_ratecard["timechargec"];
        $billingblockc                = (int)$used_ratecard["billingblockc"];
        // Initialization rounding calltime and rounding threshold variables
        $rounding_calltime            = (int)$used_ratecard["rounding_calltime"];
        $rounding_threshold           = (int)$used_ratecard["rounding_threshold"];
        // Initialization additional block charge and additional block charge time variables
        $additional_block_charge      = a2b_round(abs($used_ratecard["additional_block_charge"]));
        $additional_block_charge_time = (int)$used_ratecard["additional_block_charge_time"];
        $additional_grace_time        = (int)$used_ratecard["additional_grace"];
        $minimal_call_cost            = a2b_round(abs($used_ratecard["minimal_cost"]));

        $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[CC_RATE_ENGINE_CALCULCOST: K=$K - CALLDURATION:$callduration - freetimetocall_used=$this->freetimetocall_used - freetimetocall_left=" . $this->freetimetocall_left[$K] . "]");

        $cost = 0;
        $cost -= $connectcharge;
        if ($disconnectcharge_after <= $callduration || $disconnectcharge_after === 0) {
            $cost -= $disconnectcharge;
        }

        $this->real_answeredtime = $callduration;
        $callduration = $callduration + $additional_grace_time;

        /*
         * In following condition callduration will be updated
         * according to the the rounding_calltime and rounding_threshold
         * Reference to the TODO : ADDITIONAL CHARGES ON REALTIME BILLING - 1
         */
        if ($rounding_calltime > 0 && $rounding_threshold > 0 && $callduration > $rounding_threshold && $rounding_calltime > $callduration) {
            $callduration = $rounding_calltime;
            // RESET THE SESSIONTIME FOR CDR
            $this->answeredtime = $rounding_calltime;
        }

        /*
         * Following condition will append cost of call
         * according to the the additional_block_charge and additional_block_charge_time
         */
        // If call duration is greater then block charge time
        if ($callduration >= $additional_block_charge_time && $additional_block_charge_time > 0) {
            $block_charge = intval($callduration / $additional_block_charge_time);
            $cost -= $block_charge * $additional_block_charge;
        }

        // #### CALCUL BUYRATE COST #####
        $buyratecallduration = $this->real_answeredtime + $additional_grace_time;

        $buyratecost = 0;
        if ($buyratecallduration < $buyrateinitblock) {
            $buyratecallduration = $buyrateinitblock;
        }
        if (($buyrateincrement > 0) && ($buyratecallduration > $buyrateinitblock)) {
            $mod_sec = $buyratecallduration % $buyrateincrement; // 12 = 30 % 18
            if ($mod_sec > 0) {
                $buyratecallduration += ($buyrateincrement - $mod_sec);
            } // 30 += 18 - 12
        }
        $buyratecost -= ($buyratecallduration / 60) * $buyrate;
        if ($this->debug_st) {
            echo "1. cost: $cost\n buyratecost:$buyratecost\n";
        }

        // IF IT S A FREE CALL, WE CAN STOP HERE COST = 0
        if ($this->freecall[$K]) {
            $this->lastcost = 0;
            $this->lastbuycost = $buyratecost;
            if ($this->debug_st) {
                echo "FINAL COST: $cost\n\n";
            }
            $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[CC_RATE_ENGINE_CALCULCOST: K=$K - BUYCOST: $buyratecost - SELLING COST: $cost]");

            return;
        }

        // #### CALCUL SELLRATE COST #####
        if ($callduration < $initblock) {
            $callduration = $initblock;
        }

        // 2 KIND OF CALCULATION : PROGRESSIVE RATE & FLAT RATE
        // IF FLAT RATE
        if (empty($chargea) || empty($timechargea)) {

            if ($billingblock > 0 && $callduration > $initblock) {
                $mod_sec = $callduration % $billingblock;
                if ($mod_sec > 0) {
                    $callduration += ($billingblock - $mod_sec);
                }
            }

            if ($this->freetimetocall_left[$K] >= $callduration) {
                $this->freetimetocall_used = $callduration;
                $callduration = 0;
            }

            // Fix for promotion package causing balance to go negative.
            if ($this ->freetimetocall_used <= $callduration){
                $callduration = $callduration - $this->freetimetocall_used;
            }

            $cost -= ($callduration / 60) * $rateinitial;
            if ($this->debug_st) {
                echo "1.a cost: $cost\n";
            }
            $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[TEMP - CC_RATE_ENGINE_CALCULCOST: 1. COST: $cost]:[ ($callduration/60) * $rateinitial ]");

        // IF PROGRESSIVE RATE
        } else {

            if ($this->freetimetocall_left[$K] >= $callduration) {
                $this->freetimetocall_used = $callduration;
                $callduration = 0;
            }

            if ($this->debug_st) {
                echo "CYCLE A    COST:$cost\n";
            }
            // CYCLE A
            $cost -= $stepchargea;
            if ($this->debug_st) {
                echo "1.A cost: $cost\n\n";
            }

            $duration_report = 0;
            if ($callduration > $timechargea) {
                $duration_report = $callduration - $timechargea;
                $callduration = $timechargea;
            }

            if ($billingblocka > 0) {
                $mod_sec = $callduration % $billingblocka;
                if ($mod_sec > 0) {
                    $callduration += ($billingblocka - $mod_sec);
                }
            }
            $cost -= ($callduration / 60) * $chargea;

            if ($duration_report > 0 && !empty($chargeb) && !empty($timechargeb)) {
                $callduration = $duration_report;
                $duration_report = 0;

                // CYCLE B
                $cost -= $stepchargeb;
                if ($this->debug_st) {
                    echo "1.B cost: $cost\n\n";
                }

                if ($callduration > $timechargeb) {
                    $duration_report = $callduration - $timechargeb;
                    $callduration = $timechargeb;
                }

                if ($billingblockb > 0) {
                    $mod_sec = $callduration % $billingblockb;
                    if ($mod_sec > 0) {
                        $callduration += ($billingblockb - $mod_sec);
                    }
                }
                $cost -= ($callduration / 60) * $chargeb; // change chargea->chargeb thanks to Abbas :D

                if ($duration_report > 0 && !empty($chargec) && !empty($timechargec)) {
                    $callduration = $duration_report;
                    $duration_report = 0;

                    // CYCLE C
                    $cost -= $stepchargec;
                    if ($this->debug_st) {
                        echo "1.C cost: $cost\n\n";
                    }

                    if ($callduration > $timechargec) {
                        $duration_report = $callduration - $timechargec;
                        $callduration = $timechargec;
                    }

                    if ($billingblockc > 0) {
                        $mod_sec = $callduration % $billingblockc;
                        if ($mod_sec > 0) {
                            $callduration += ($billingblockc - $mod_sec);
                        }
                    }
                    $cost -= ($callduration / 60) * $chargec;
                }
            }

            if ($duration_report > 0) {

                if ($billingblock > 0) {
                    $mod_sec = $duration_report % $billingblock;
                    if ($mod_sec > 0) {
                        $duration_report += ($billingblock - $mod_sec);
                    }
                }
                $cost -= ($duration_report / 60) * $rateinitial;
                $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[TEMP - CC_RATE_ENGINE_CALCULCOST: 2. DURATION_REPORT:$duration_report - COST: $cost]");
            }
        }
        $cost = a2b_round($cost);
        if ($this->debug_st) {
            echo "FINAL COST: $cost\n\n";
        }
        $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[CC_RATE_ENGINE_CALCULCOST: K=$K - BUYCOST:$buyratecost - SELLING COST:$cost]");

        if ($cost > (0 - $minimal_call_cost)) {
            $this->lastcost = 0 - $minimal_call_cost;
        } else {
            $this->lastcost = $cost;
        }
        $this->lastbuycost = $buyratecost;
    }

    /* WTAF??? TODO: sort out this mess
        SORT_ASC : Tri en ordre ascendant
        SORT_DESC : Tri en ordre descendant
    */
    public function array_csort()
    {
        $args = func_get_args();
        $marray = array_shift($args);
        $i = 0;
        $msortline = "return(array_multisort(";
        foreach ($args as $arg) {
            $i++;
            if (is_string($arg)) {
                foreach ($marray as $row) {
                    $sortarr[$i][] = $row[$arg];
                }
            } else {
                $sortarr[$i] = $arg;
            }
            $msortline .= "\$sortarr[" . $i . "],";
        }
        $msortline .= "\$marray));";

        eval($msortline);

        return $marray;
    }

    /*
     * RATE ENGINE - UPDATE SYSTEM (DURATIONCALL)
     * Calcul the duration allowed for the caller to this number
     */
    public function rate_engine_updatesystem(A2Billing $A2B, Agi $agi, string $calledstation, bool $doibill = true, bool $didcall = false, bool $callback = false)
    {
        $K = $this->usedratecard;

        // ****************  PACKAGE PARAMETERS ****************
        $id_cc_package_offer = (int)$this->ratecard_obj[$K]["id_cc_package_offer"];
        $additional_grace_time = (int)$this->ratecard_obj[$K]["additional_grace"];
        $id_card_package_offer = null;

        $sessiontime = $this->answeredtime;
        $dialstatus = $this->dialstatus;

        // add grace time if the call is Answered
        if ($this->dialstatus === "ANSWER" && $additional_grace_time > 0) {
            $sessiontime += $additional_grace_time;
        }

        $A2B->debug(A2Billing::INFO, $agi, __FILE__, __LINE__, ":[sessiontime:$sessiontime - id_cc_package_offer:$id_cc_package_offer - package2apply:" . $this ->package_to_apply[$K] . "]\n\n");

        if ($sessiontime > 0) {
            // HANDLE FREETIME BEFORE CALCULATE THE COST
            $this->freetimetocall_used = 0;
            if ($this->debug_st) {
                print_r($this->freetimetocall_left[$K]);
            }

            if ($id_cc_package_offer !== -1 && isset($this->package_to_apply[$K])) {
                $id_package_offer = $this->package_to_apply[$K]["id"];
                // type is 0=unlimited; 1=# free calls; 2=#free seconds
                $this->freetimetocall_used = ($this->package_to_apply[$K]["type"] == 2)
                    ? min($sessiontime, $this->freetimetocall_left[$K])
                    : $sessiontime;

                $this->rate_engine_calculcost($A2B, $sessiontime, $agi);

                $query = "INSERT INTO cc_card_package_offer (id_cc_card, id_cc_package_offer, used_secondes) VALUES (?, ?, ?)";
                $id_card_package_offer = $A2B->DBHandle->Execute($query, [$A2B->id_card, $id_package_offer, $this->freetimetocall_used]);
                $A2B->debug(
                    A2Billing::INFO,
                    $agi,
                    __FILE__,
                    __LINE__,
                    ":[ID_CARD_PACKAGE_OFFER CREATED : " . ($id_card_package_offer instanceof \ADORecordSet ? "ok" : $A2B->DBHandle->ErrorMsg()) . "]"
                );

            } else {

                $this->rate_engine_calculcost($A2B, $sessiontime, $agi);
                // rate_engine_calculcost could have change the duration of the call

                $sessiontime = $this->answeredtime;
                if ($sessiontime > 0 && $additional_grace_time > 0) {
                    $sessiontime = $sessiontime + $additional_grace_time;
                }
            }

        } else {
            $sessiontime = 0;
        }

        $calldestination = $this->ratecard_obj[$K]["destination"];
        $id_tariffgroup  = (int)$this->ratecard_obj[$K]["idtariffgroup"];
        $id_tariffplan   = (int)$this->ratecard_obj[$K]["idtariffplan"];
        $id_ratecard     = (int)$this->ratecard_obj[$K]["ratecard_id"];

        if (!$doibill || $sessiontime < $A2B->agiconfig['min_duration_2bill']) {
            $cost = 0;
        } else {
            $cost = $this->lastcost;
        }
        $buycost = abs($this->lastbuycost);

        if ($cost < 0) {
            $signe = '-';
            $signe_cc_call = '+';
        } else {
            $signe = '+';
            $signe_cc_call = '-';
        }

        $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[CC_RATE_ENGINE_UPDATESYSTEM: usedratecard K=$K - (sessiontime=$sessiontime :: dialstatus=$dialstatus :: buycost=$buycost :: cost=$cost : signe_cc_call=$signe_cc_call: signe=$signe)]");

        $dialstatus_rev_list = ["ANSWER" => 1, "BUSY" => 2, "NOANSWER" => 3, "CANCEL" => 4, "CONGESTION" => 5, "CHANUNAVAIL" => 6, "DONTCALL" => 7, "TORTURE" => 8, "INVALIDARGS" => 9];
        $terminatecauseid = $dialstatus_rev_list[$dialstatus] ?? 0;

        // CALLTYPE -  0 = NORMAL CALL ; 1 = VOIP CALL (SIP/IAX) ; 2= DIDCALL + TRUNK ; 3 = VOIP CALL DID ; 4 = CALLBACK call
        if ($didcall) {
            $calltype = 2;
        } elseif ($callback) {
            $calltype = 4;
            $terminatecauseid = 1;
        } else {
            $calltype = 0;
        }

        $card_id = $A2B->id_card < 1 ? '-1' : $A2B->id_card;
        $real_sessiontime = $this->real_answeredtime;
        $id_tariffgroup = $id_tariffgroup < 1 ? null : $id_tariffgroup;
        $id_tariffplan = $id_tariffplan ? null : $id_tariffplan;
        $id_ratecard = $id_ratecard < 1 ? null : $id_ratecard;
        $trunk_id = $this->usedtrunk < 1 ? null : $this->usedtrunk;
        $id_card_package_offer = $id_card_package_offer < 1 ? null : $id_card_package_offer;
        $calldestination = (!is_numeric($calldestination)) ? 'DEFAULT' : $calldestination;

        if ($A2B->config["global"]['cache_enabled']) {
            // sqlite syntax
            $starttime = " datetime(strftime('%s', 'now') - ?, 'unixepoch', 'localtime')";
            $stoptime = "datetime('now', 'localtime')";
        } else {
            $starttime = "SUBDATE(CURRENT_TIMESTAMP, INTERVAL ? SECOND) ";
            $stoptime = "now()";
        }
        $QUERY_COLUMN = "uniqueid, sessionid, card_id, nasipaddress, starttime, sessiontime, real_sessiontime, calledstation, terminatecauseid, stoptime, sessionbill, id_tariffgroup, id_tariffplan, id_ratecard, id_trunk, src, sipiax, buycost, id_card_package_offer, dnid, destination $A2B->CDR_CUSTOM_SQL";
        $QUERY = "INSERT INTO cc_call ($QUERY_COLUMN) VALUES ";
        $QUERY .= "(?, ?, ?, ?, $starttime, ?, ?, ?, ?, $stoptime, ? * ${signe_cc_call}1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? $A2B->CDR_CUSTOM_VAL)";
        $params = [$A2B->uniqueid, $A2B->channel, $card_id, $A2B->hostname, $sessiontime, $sessiontime, $real_sessiontime, $calledstation, $terminatecauseid, a2b_round(abs($cost)), $id_tariffgroup, $id_tariffplan, $id_ratecard, $trunk_id, $A2B->CallerID, $calltype, $buycost, $id_card_package_offer, $A2B->dnid, $calldestination];

        if ($A2B->config["global"]['cache_enabled']) {
             //insert query in the cache system
            $db = NewADOConnection("pdo");
            if ($db->Connect("sqlite:" . $A2B->config["global"]["cache_path"])) {
                if (!file_exists($A2B->config["global"]['cache_path'])) {
                    $db->Execute("CREATE TABLE cc_call ($QUERY_COLUMN)");
                }
                $db->Execute($QUERY);
            } else {
                $A2B->debug(A2Billing::ERROR, $agi, __FILE__, __LINE__, "[Error to connect to cache : " . $db->ErrorMsg() . "]\n");
            }
        } else {
            $result = $A2B->DBHandle->Execute($QUERY, $params);
            $A2B->debug(
                A2Billing::INFO,
                $agi,
                __FILE__,
                __LINE__,
                "[CC_asterisk_stop : SQL: DONE : result=" . ($result instanceof \ADORecordSet ? "ok" : $A2B->DBHandle->ErrorMsg()) . "]"
            );
            $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[CC_asterisk_stop : SQL: $QUERY]");
        }

        if ($sessiontime > 0) {

            $params = [a2b_round($cost)];
            if (!$didcall && !$callback) {
                $redial_clause = ", redial=?";
                $params[] = $calledstation;
            } else {
                $redial_clause = '';
            }
            $firstuse_clause = $A2B->nbused > 0 ? "" : ", firstusedate = NOW(),";
            $params[] = $A2B->username;

            //Update the global credit
            $A2B->credit = $A2B->credit + $cost;

            $QUERY = "UPDATE cc_card SET credit = credit + ? $redial_clause, lastuse = NOW() $firstuse_clause, nbused = nbused + 1 WHERE username = ?";
            $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[CC_asterisk_stop 1.2: SQL: $QUERY]");
            $A2B->DBHandle->Execute($QUERY, $params);

            $QUERY = "UPDATE cc_trunk SET secondusedreal = secondusedreal + ? WHERE id_trunk = ?";
            $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, $QUERY);
            $A2B->DBHandle->Execute($QUERY, [$sessiontime, $this->usedtrunk]);

            $QUERY = "UPDATE cc_tariffplan SET secondusedreal = secondusedreal + ? WHERE id = ?";
            $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, $QUERY);
            $A2B->DBHandle->Execute($QUERY, [$sessiontime, $id_tariffplan]);
        }
    }

    /*
     * function would set when the trunk is used or when it release
     */
    public function trunk_start_inuse(Agi $agi, A2Billing $A2B, bool $inuse): void
    {
        if ($inuse) {
            $QUERY = "UPDATE cc_trunk SET inuse = inuse + 1 WHERE id_trunk = ?";
        } else {
            $QUERY = "UPDATE cc_trunk SET inuse = inuse - 1 WHERE id_trunk = ?";
        }

        $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[TRUNK STATUS UPDATE : $QUERY]");
        $A2B->DBHandle->Execute($QUERY, [$this->usedtrunk]);
    }

    /*
        RATE ENGINE - PERFORM CALLS
    */
    public function rate_engine_performcall(Agi $agi, A2Billing $A2B, string $destination): bool
    {
        $max_long = 36000000; //Maximum 10 hours
        $old_destination = $destination;
        $k = 0;
        $loop_failover = 0;

        foreach ($this->ratecard_obj as $k => $ratecard) {

            $destination = $old_destination;
            $this->usedtrunk = (int)$ratecard["rt_id_trunk"];
            if ($this->usedtrunk !== -1) {
                $prefix         = $ratecard["rt_trunkprefix"];
                $tech           = $ratecard["rt_providertech"];
                $ipaddress      = $ratecard["rt_providerip"];
                $removeprefix   = $ratecard["rt_removeprefix"];
                $failover_trunk = (int)$ratecard["rt_failover_trunk"];
                $addparameter   = $ratecard["rt_addparameter_trunk"];
                $inuse          = (int)$ratecard["rt_inuse"];
                $maxuse         = (int)$ratecard["rt_maxuse"];
                $ifmaxuse       = (int)$ratecard["rt_if_max_use"];
            } else {
                $this->usedtrunk = (int)$ratecard["tp_id_trunk"];
                $prefix         = $ratecard["tp_trunkprefix"];
                $tech           = $ratecard["tp_providertech"];
                $ipaddress      = $ratecard["tp_providerip"];
                $removeprefix   = $ratecard["tp_removeprefix"];
                $failover_trunk = (int)$ratecard["tp_failover_trunk"];
                $addparameter   = $ratecard["tp_addparameter_trunk"];
                $inuse          = (int)$ratecard["tp_inuse"];
                $maxuse         = (int)$ratecard["tp_maxuse"];
                $ifmaxuse       = (int)$ratecard["tp_if_max_use"];
            }

            $timeout        = $ratecard["timeout"];
            $musiconhold    = $ratecard["musiconhold"];
            $cidgroupid     = $ratecard["id_outbound_cidgroup"];

            if (str_starts_with($destination, $removeprefix)) {
                $destination = substr($destination, strlen($removeprefix));
            }

            //$dialparams = "|30|HS($timeout)"; // L(" . $timeout*1000 . ":61000:30000)
            $dialparams = str_replace(
                ["%timeout%", "%timeoutsec%"],
                [min($timeout * 1000, $max_long), min($timeout, $max_long)],
                $A2B->agiconfig['dialcommand_param']
            );

            if (strlen($musiconhold) > 0 && $musiconhold !== "selected") {
                $dialparams .= "m";
                $agi->exec("SETMUSICONHOLD $musiconhold");
                $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "EXEC SETMUSICONHOLD $musiconhold");
            }

            if ($A2B->agiconfig['record_call'] == 1) {
                $command_mixmonitor = "MixMonitor $A2B->uniqueid.{$A2B->agiconfig['monitor_formatfile']},b";
                $agi->exec($command_mixmonitor);
                $A2B->debug(A2Billing::INFO, $agi, __FILE__, __LINE__, $command_mixmonitor);
            }

            $ipaddress = str_replace(
                ["%cardnumber%", "%dialingnumber%"],
                [$A2B->cardnumber, "$prefix$destination"],
                $ipaddress
            );

            if (str_contains($ipaddress, "%dialingnumber%")) {
                $dialstr = "$tech/$ipaddress" . $dialparams;
            } elseif ($A2B->agiconfig['switchdialcommand'] == 1) {
                $dialstr = "$tech/$prefix$destination@$ipaddress" . $dialparams;
            } else {
                $dialstr = "$tech/$ipaddress/$prefix$destination" . $dialparams;
            }

            //ADDITIONAL PARAMETER             %dialingnumber%, %cardnumber%
            $dialstr .= str_replace(
                ["%cardnumber%", "%dialingnumber%"],
                [$A2B->cardnumber, "$prefix$destination"],
                $addparameter
            );

            $A2B->debug(A2Billing::INFO, $agi, __FILE__, __LINE__, "app_callingcard: Dialing '$dialstr' with timeout of '$timeout'.\n");

            //# Channel: technology/number@ip_of_gw_to PSTN
            //# Channel: SIP/3465078XXXXX@11.150.54.xxx   /     SIP/phone1@192.168.1.6
            // exten => 1879,1,Dial(SIP/34650XXXXX@255.XX.7.XX,20,tr)
            // Dial(IAX2/guest@misery.digium.com/s@default)
            //$myres = $agi->agi_exec("EXEC DIAL SIP/3465078XXXXX@254.20.7.28|30|HL(" . ($timeout * 60 * 1000) . ":60000:30000)");

            $QUERY = "SELECT cid FROM cc_outbound_cid_list WHERE activated = 1 AND outbound_cid_group = ? ORDER BY RAND() LIMIT 1";
            $outcid = $A2B->DBHandle->GetOne($QUERY, [$cidgroupid]) ?: 0;
            if ($outcid) {
                # Uncomment this line if you want to save the outbound_cid in the CDR
                //$A2B->CallerID = $outcid;
                $agi->set_callerid($outcid);
                $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[EXEC SetCallerID : $outcid]");
            }
            $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "app_callingcard: CIDGROUPID='$cidgroupid' OUTBOUND CID SELECTED IS '$outcid'.");

            if ($maxuse === -1 || $inuse < $maxuse) {
                // Count this call on the trunk
                $this->trunk_start_inuse($agi, $A2B, true);

                $agi->exec("DIAL $dialstr");
                //exec('Dial', trim("$type/$identifier|$timeout|$options|$url", '|'));

                $A2B->debug(A2Billing::INFO, $agi, __FILE__, __LINE__, "DIAL $dialstr");

                // check connection after dial(long pause)
                $A2B->DbReConnect($agi);

                // Count this call on the trunk
                $this->trunk_start_inuse($agi, $A2B, false);
            } elseif ($ifmaxuse === 1) {
                $A2B->debug(A2Billing::WARN, $agi, __FILE__, __LINE__, "This trunk cannot be used because maximum number of connections is reached. Now use next trunk\n");
                continue;
            } else {
                $A2B->debug(A2Billing::WARN, $agi, __FILE__, __LINE__, "This trunk cannot be used because maximum number of connections is reached. Now use failover trunk\n");
            }

            if ($A2B->agiconfig['record_call'] == 1) {
                $agi->exec("StopMixMonitor");
                $A2B->debug(A2Billing::INFO, $agi, __FILE__, __LINE__, "EXEC StopMixMonitor (" . $A2B->uniqueid . ")");
            }

            $this->real_answeredtime = $this->answeredtime = (int)$agi->get_variable("ANSWEREDTIME", true);
            $this->dialstatus = $agi->get_variable("DIALSTATUS", true);

            // LOOOOP FOR THE FAILOVER LIMITED TO failover_recursive_limit
            $loop_failover = 0;
            while ($loop_failover <= $A2B->agiconfig['failover_recursive_limit'] && $failover_trunk >= 0 && ($this->dialstatus == "CHANUNAVAIL" || $this->dialstatus == "CONGESTION" || ($inuse >= $maxuse && $maxuse != -1))) {
                $loop_failover++;
                $this->real_answeredtime = $this->answeredtime = 0;
                $this->usedtrunk = $failover_trunk;

                $A2B->debug(A2Billing::INFO, $agi, __FILE__, __LINE__, "[K=$k]:[ANSWEREDTIME=" . $this->answeredtime . "-DIALSTATUS=" . $this->dialstatus . "]");

                $destination = $old_destination;

                $QUERY = "SELECT trunkprefix, providertech, providerip, removeprefix, failover_trunk, status, inuse, maxuse, if_max_use FROM cc_trunk WHERE id_trunk = ?";
                $row = $A2B->DBHandle->GetRow($QUERY, [$failover_trunk]);

                if ($row !== false && $row !== []) {

                    //DO SELECT WITH THE FAILOVER_TRUNKID
                    $prefix              = $row["trunkprefix"];
                    $tech                = $row["providertech"];
                    $ipaddress           = $row["providerip"];
                    $removeprefix        = $row["removeprefix"];
                    $failover_trunk      = (int)$row["failover_trunk"];
                    $status              = (int)$row["status"];
                    $inuse               = (int)$row["inuse"];
                    $maxuse              = (int)$row["maxuse"];
                    $ifmaxuse            = (int)$row["if_max_use"];

                    if (strncmp($destination, $removeprefix, strlen($removeprefix)) == 0) {
                        $destination = substr($destination, strlen($removeprefix));
                    }

                    // Check if we will be able to use this route:
                    //  if the trunk is activated and
                    //  if there are less connection than it can support or there is an unlimited number of connections
                    // If not, use the next failover trunk or next trunk in list
                    if ($status === 0) {
                        $A2B->debug(A2Billing::WARN, $agi, __FILE__, __LINE__, "Failover trunk cannot be used because it is disabled. Now use next trunk\n");
                        continue 2;
                    }

                    if ($maxuse !== -1 && $inuse >= $maxuse) {
                        $A2B->debug(A2Billing::WARN, $agi, __FILE__, __LINE__, "Failover trunk cannot be used because maximum number of connections on this trunk is already reached.\n");

                        // use failover trunk
                        if ($ifmaxuse === 0) {
                            $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "Now using its failover trunk\n");
                            continue;
                        } else {
                            $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "Now using next trunk\n");
                            continue 2;
                        }
                    }

                    $pos_dialingnumber = strpos($ipaddress, '%dialingnumber%');

                    $ipaddress = str_replace(
                        ["%cardnumber%", "%dialingnumber%"],
                        [$A2B->cardnumber, "$prefix$destination"],
                        $ipaddress
                    );

                    if ($pos_dialingnumber !== false) {
                        $dialstr = "$tech/$ipaddress" . $dialparams;
                    } elseif ($A2B->agiconfig['switchdialcommand'] == 1) {
                        $dialstr = "$tech/$prefix$destination@$ipaddress" . $dialparams;
                    } else {
                        $dialstr = "$tech/$ipaddress/$prefix$destination" . $dialparams;
                    }

                    $A2B->debug(A2Billing::INFO, $agi, __FILE__, __LINE__, "FAILOVER app_callingcard: Dialing '$dialstr' with timeout of '$timeout'.\n");

                    // Count this call on the trunk
                    $this->trunk_start_inuse($agi, $A2B, true);

                    $agi->exec("DIAL $dialstr");
                    $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "DIAL FAILOVER $dialstr");

                    // check connection after dial(long pause)
                    $A2B->DbReConnect($agi);

                    // Count this call on the trunk
                    $this->trunk_start_inuse($agi, $A2B, false);

                    $this->real_answeredtime = $this->answeredtime = (int)$agi->get_variable("ANSWEREDTIME", true);
                    $this->dialstatus = $agi->get_variable("DIALSTATUS", true);

                    $A2B->debug(A2Billing::INFO, $agi, __FILE__, __LINE__, "[FAILOVER K=$k]:[ANSTIME=" . $this->answeredtime . "-DIALSTATUS=" . $this->dialstatus . "]");
                }
                // If the failover trunk is same as the actual trunk we break
                if ($this->usedtrunk === $failover_trunk) {
                    break;
                }

            } // END FOR LOOP FAILOVER

            //# Ooh, something actually happened!
            if ($this->dialstatus === "BUSY") {
                $this->real_answeredtime = $this->answeredtime = 0;
                if ($A2B->agiconfig['busy_timeout'] > 0) {
                    $agi->exec("Busy " . $A2B->agiconfig['busy_timeout']);
                }
                $agi->stream_file('prepaid-isbusy', '#');
            } elseif ($this->dialstatus === "NOANSWER") {
                $this->real_answeredtime = $this->answeredtime = 0;
                $agi->stream_file('prepaid-noanswer', '#');
            } elseif ($this->dialstatus === "CANCEL") {
                $this->real_answeredtime = $this->answeredtime = 0;
            } elseif ($this->dialstatus === "CHANUNAVAIL" || $this->dialstatus == "CONGESTION") {
                $this->real_answeredtime = $this->answeredtime = 0;
                // Check if we will failover for LCR/LCD prefix - better false for an exact billing on resell
                if ($A2B->agiconfig['failover_lc_prefix']) {
                    continue;
                }
                $this->usedratecard = $k;
                return false;
            } elseif ($this->dialstatus == "ANSWER") {
                $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "-> dialstatus : " . $this->dialstatus . ", answered time is " . $this->answeredtime . " \n");
            }

            $this->usedratecard = $k;
            $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[USEDRATECARD=" . $this->usedratecard . "]");

            return true;
        } // End for

        $this->usedratecard = $k - $loop_failover;
        $A2B->debug(A2Billing::DEBUG, $agi, __FILE__, __LINE__, "[USEDRATECARD - FAIL =" . $this->usedratecard . "]");

        return false;
    }
}
