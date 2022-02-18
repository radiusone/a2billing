{include file="header.tpl"}


{if (!$popupwindow)}
	<div id="top_menu">
		<ul id="menu_horizontal">
			<li class="topmenu-left-button" style="border:none;">
				<div style="width:100%;height:100%;text-align:center;" >
					<a href="PP_intro.php">
						<strong> {_("HOME")}</strong>&nbsp;
						<img style="vertical-align:bottom;" src="templates/{$SKIN_NAME}/images/house.png">
					</a>
				</div>
			</li>
			{if ($ACXDASHBOARD > 0) }
			<li class="topmenu-left-button" >
				<div style="width:100%;height:100%;text-align:center;" >
					<a href="dashboard.php" >
						<strong> {_("DASHBOARD")}</strong>&nbsp;
						<img style="vertical-align:bottom;" src="templates/{$SKIN_NAME}/images/chart_bar.png">
					</a>
				</div>
			</li>
			{/if}
			<li class="topmenu-left-button">
				<div style="width:100%;height:100%;text-align:center;" >
					<a href="A2B_notification.php">
						<strong > {_("NOTIFICATION")}</strong>&nbsp;
						<img style="vertical-align:bottom;" src="templates/{$SKIN_NAME}/images/email.png">
						{if ($NEW_NOTIFICATION > 0) }
						<strong style="font-size:8px; color:red;"> NEW</strong>
						{else}
						<strong style="font-size:8px;">&nbsp;</strong>
						{/if}
				  	</a>
				</div>
			</li>
			<li class="topmenu-right-button" style="border-left: 1px solid #AAA;">
				<div style="width:90%;height:100%;text-align:center;" >
					<a href="logout.php?logout=true" target="_top">
						<font color="#EC3F41">
							<b>&nbsp;&nbsp;{_("LOGOUT")}</b>
						</font>
						<img style="vertical-align:bottom;" src="templates/{$SKIN_NAME}/images/logout.png">
					</a>
				</div>
			</li>
		</ul>
	</div>

<div id="left-sidebar">
<div id="leftmenu-top">
<div id="leftmenu-down">
<div id="leftmenu-middle">

<ul id="nav">

  	{if ($ACXCUSTOMER > 0) }
  	<div class="toggle_menu">
		<li>
			<a href="javascript:;" class="toggle_menu" target="_self">
				<div>
					<div id="menutitlebutton">
						<img id="img1" {if ($section == "1")} src="templates/{$SKIN_NAME}/images/minus.gif" {else} src="templates/{$SKIN_NAME}/images/plus.gif" {/if} onmouseover="this.style.cursor='hand';" >
					</div>
					<div id="menutitlesection">
						<strong>{_("CUSTOMERS")}</strong>
					</div>
				</div>
			</a>
		</li>
	</div>
	<div class="tohide" {if ($section !="1")} style="display:none;" {/if}>
		<ul>
			<li>
				<ul>
					<li><a href="A2B_entity_card.php?section=1">{_("Add :: Search")}</a></li>
					<li><a href="CC_card_import.php?section=1">{_("Import")}</a></li>
					<li><a href="A2B_entity_friend.php?atmenu=sip&section=1">{_("VoIP Settings")}</a></li>
					<li><a href="A2B_entity_callerid.php?atmenu=callerid&section=1">{_("Caller-ID")}</a></li>
					<li><a href="A2B_notifications.php?section=1">{_("Credit Notification")}</a></li>
					<li><a href="A2B_entity_card_group.php?section=1">{_("Groups")}</a></li>
					<li><a href="A2B_entity_card_seria.php?section=1">{_("Card series")}</a></li>
					<li><a href="A2B_entity_speeddial.php?atmenu=speeddial&section=1">{_("Speed Dial")}</a></li>
					<li><a href="card-history.php?atmenu=cardhistory&section=1">{_("History")}</a></li>
					<li><a href="A2B_entity_statuslog.php?atmenu=statuslog&section=1">{_("Status")}</a></li>
				</ul>
			</li>
		</ul>
	</div>
	{/if}

	{if ($ACXADMINISTRATOR  > 0)}
	<div class="toggle_menu">
		<li>
			<a href="javascript:;" class="toggle_menu" target="_self">
				<div>
					<div id="menutitlebutton">
						<img id="img2" {if ($section == "2")} src="templates/{$SKIN_NAME}/images/minus.gif" {else} src="templates/{$SKIN_NAME}/images/plus.gif" {/if} onmouseover="this.style.cursor='hand';" >
					</div>
					<div id="menutitlesection">
						<strong>{_("AGENTS")}</strong>
					</div>
				</div>
			</a>
		</li>
	</div>
	<div class="tohide" {if ($section !="2")} style="display:none;" {/if}>
		<ul>
			<li>
				<ul>
					<li><a href="A2B_entity_agent.php?atmenu=user&section=2">{_("Add :: Search")}</a></li>
					<li><a href="A2B_entity_signup_agent.php?atmenu=user&section=2">{_("Signup URLs")}</a></li>
				</ul>
			</li>
		</ul>
	</div>
	{/if}


	{if ($ACXADMINISTRATOR  > 0)}
	<div class="toggle_menu">
		<li>
			<a href="javascript:;" class="toggle_menu" target="_self">
				<div>
					<div id="menutitlebutton">
						<img id="img3" {if ($section == "3")} src="templates/{$SKIN_NAME}/images/minus.gif" {else} src="templates/{$SKIN_NAME}/images/plus.gif" {/if} onmouseover="this.style.cursor='hand';" >
					</div>
					<div id="menutitlesection">
						<strong>{_("ADMINS")}</strong>
					</div>
				</div>
			</a>
		</li>
	</div>
	<div class="tohide" {if ($section !="3")} style="display:none;" {/if}>
		<ul>
			<li>
				<ul>
					<li><a href="A2B_entity_user.php?atmenu=user&groupID=0&section=3">{_("Add :: Search")}</a></li>
					<li><a href="A2B_entity_user.php?atmenu=user&groupID=1&section=3">{_("Access Control")}</a></li>
				</ul>
			</li>
		</ul>
	</div>
	{/if}

	{if ($ACXSUPPORT > 0)}
	<div class="toggle_menu">
		<li>
			<a href="javascript:;" class="toggle_menu" target="_self">
				<div>
					<div id="menutitlebutton">
						<img id="img4" {if ($section == "4")} src="templates/{$SKIN_NAME}/images/minus.gif" {else} src="templates/{$SKIN_NAME}/images/plus.gif" {/if} onmouseover="this.style.cursor='hand';" >
					</div>
					<div id="menutitlesection">
						<strong>{_("SUPPORT")}</strong>
					</div>
				</div>
			</a>
		</li>
	</div>
	<div class="tohide" {if ($section !="4")} style="display:none;" {/if}>
		<ul>
			<li>
				<ul>
					<li><a href="CC_ticket.php?section=4">{_("Customer Tickets")}</a></li>
					<li><a href="A2B_ticket_agent.php?section=4">{_("Agent Tickets")}</a></li>
					<li><a href="CC_support_component.php?section=4">{_("Ticket Components")}</a></li>
					<li><a href="CC_support.php?section=4">{_("Support Boxes")}</a></li>
				</ul>
			</li>
		</ul>
	</div>
	{/if}

	{if ($ACXCALLREPORT > 0)}
	<div class="toggle_menu">
		<li>
			<a href="javascript:;" class="toggle_menu" target="_self">
				<div>
					<div id="menutitlebutton">
						<img id="img5" {if ($section == "5")} src="templates/{$SKIN_NAME}/images/minus.gif" {else} src="templates/{$SKIN_NAME}/images/plus.gif" {/if} onmouseover="this.style.cursor='hand';" >
					</div>
					<div id="menutitlesection">
						<strong>{_("CALL REPORTS")}</strong>
					</div>
				</div>
			</a>
		</li>
	</div>
	<div class="tohide" {if ($section !="5")} style="display:none;" {/if}>
		<ul>
			<li>
				<ul>
					<li><a href="call-log-customers.php?nodisplay=1&posted=1&section=5">{_("CDRs")}</a></li>
					<li><a href="call-count-reporting.php?nodisplay=1&posted=1&section=5">{_("Call Count")}</a></li>
					<li><a href="A2B_trunk_report.php?section=5">{_("Trunk")}</a></li>
					<li><a href="call-dnid.php?nodisplay=1&posted=1&section=5">{_("DNID")}</a></li>
					<li><a href="call-pnl-report.php?section=5">{_("PNL")}</a></li>
					<li><a href="call-comp.php?section=5">{_("Compare Calls")}</a></li>
					<li><a href="call-daily-load.php?section=5">{_("Daily Traffic")}</a></li>
					<li><a href="call-last-month.php?section=5">{_("Monthly Traffic")}</a></li>
				</ul>
			</li>
		</ul>
	</div>
	{/if}

	{if ($ACXRATECARD > 0)}
	<div class="toggle_menu">
		<li>
			<a href="javascript:;" class="toggle_menu" target="_self">
				<div>
					<div id="menutitlebutton">
						<img id="img6" {if ($section == "6")} src="templates/{$SKIN_NAME}/images/minus.gif" {else} src="templates/{$SKIN_NAME}/images/plus.gif" {/if} onmouseover="this.style.cursor='hand';" >
					</div>
					<div id="menutitlesection">
						<strong>{_("RATES")}</strong>
					</div>
				</div>
			</a>
		</li>
	</div>
	<div class="tohide" {if ($section !="6")} style="display:none;" {/if}>
		<ul>
			<li>
				<ul>
					<li><a href="A2B_entity_tariffgroup.php?atmenu=tariffgroup&section=6">{_("Call Plan")}</a></li>
					<li><a href="A2B_entity_tariffplan.php?atmenu=tariffplan&section=6">{_("RateCards")}</a></li>
					<li><a href="CC_ratecard_import.php?atmenu=ratecard&section=6">»» {_("Import")}</a></li>
					<li><a href="CC_ratecard_merging.php?atmenu=ratecard&section=6">»» {_("Merge")}</a></li>
					<li><a href="CC_entity_sim_ratecard.php?atmenu=ratecard&section=6">»» {_("Simulator")}</a></li>
					<li><a href="A2B_entity_def_ratecard.php?atmenu=ratecard&section=6">{_("Rates")}</a></li>
				</ul>
			</li>
		</ul>
	</div>
	{/if}

	{if ($ACXTRUNK > 0)}
	<div class="toggle_menu">
		<li>
			<a href="javascript:;" class="toggle_menu" target="_self">
				<div>
					<div id="menutitlebutton">
						<img id="img7" {if ($section == "7")} src="templates/{$SKIN_NAME}/images/minus.gif" {else} src="templates/{$SKIN_NAME}/images/plus.gif" {/if} onmouseover="this.style.cursor='hand';" >
					</div>
					<div id="menutitlesection">
						<strong>{_("PROVIDERS")}</strong>
					</div>
				</div>
			</a>
		</li>
	</div>
	<div class="tohide" {if ($section !="7")} style="display:none;" {/if}>
		<ul>
			<li>
				<ul>
					<li><a href="A2B_entity_provider.php?section=7">{_("Providers")}</a></li>
					<li><a href="A2B_entity_trunk.php?section=7">{_("Trunks")}</a></li>
					<li><a href="A2B_entity_prefix.php?section=7">{_("Prefixes")}</a></li>
				</ul>
			</li>
		</ul>
	</div>
	{/if}

	{if ($ACXDID > 0)}
	<div class="toggle_menu">
		<li>
			<a href="javascript:;" class="toggle_menu" target="_self">
				<div>
					<div id="menutitlebutton">
						<img id="img8" {if ($section == "8")} src="templates/{$SKIN_NAME}/images/minus.gif" {else} src="templates/{$SKIN_NAME}/images/plus.gif" {/if} onmouseover="this.style.cursor='hand';" >
					</div>
					<div id="menutitlesection">
						<strong>{_("INBOUND DID")}</strong>
					</div>
				</div>
			</a>
		</li>
	</div>
	<div class="tohide" {if ($section !="8")} style="display:none;" {/if}>
		<ul>
			<li>
				<ul>
					<li><a href="A2B_entity_did.php?section=8">{_("Add :: Search")}</a></li>
					<li><a href="A2B_entity_didgroup.php?section=8">{_("Groups")}</a>
					<li><a href="A2B_entity_did_destination.php?section=8">{_("Destination")}</a></li>
					<li><a href="A2B_entity_did_import.php?section=8">{_("Import [CSV]")}</a></li>
					<li><a href="A2B_entity_didx.php?section=8">{_("Import [DIDX]")}</a></li>
					<li><a href="A2B_entity_did_use.php?atmenu=did_use&section=8">{_("Usage")}</a></li>
					<li><a href="A2B_entity_did_billing.php?atmenu=did_billing&section=8">{_("Billing")}</a></li>
				</ul>
			</li>
		</ul>
	</div>
	{/if}


	{if ($ACXOUTBOUNDCID > 0)}
	<div class="toggle_menu">
		<li>
			<a href="javascript:;" class="toggle_menu" target="_self">
				<div>
					<div id="menutitlebutton">
						<img id="img9" {if ($section == "9")} src="templates/{$SKIN_NAME}/images/minus.gif" {else} src="templates/{$SKIN_NAME}/images/plus.gif" {/if} onmouseover="this.style.cursor='hand';" >
					</div>
					<div id="menutitlesection">
						<strong>{_("OUTBOUND CID")}</strong>
					</div>
				</div>
			</a>
		</li>
	</div>
	<div class="tohide"
	{if ($section !="9")} style="display:none;" {/if}>
		<ul>
			<li>
				<ul>
					<li><a href="A2B_entity_outbound_cid.php?atmenu=cid&section=9">{_("Add")}</a></li>
					<li><a href="A2B_entity_outbound_cidgroup.php?atmenu=cidgroup&section=9">{_("Groups")}</a></li>
				</ul>
			</li>
		</ul>
	</div>
	{/if}


	{if ($ACXBILLING > 0)}
	<div class="toggle_menu">
		<li>
			<a href="javascript:;" class="toggle_menu" target="_self">
				<div>
					<div id="menutitlebutton">
						<img id="img10" {if ($section == "10")} src="templates/{$SKIN_NAME}/images/minus.gif" {else} src="templates/{$SKIN_NAME}/images/plus.gif" {/if} onmouseover="this.style.cursor='hand';">
					</div>
					<div id="menutitlesection">
						<strong>{_("BILLING")}</strong>
					</div>
				</div>
			</a>
		</li>
	</div>
	<div class="tohide" {if ($section !="10")} style="display:none;" {/if}>
		<ul>
			<li>
				<ul>
					<li><a href="A2B_entity_voucher.php?section=10">{_("Vouchers")}</a></li>
					<li><a href="A2B_entity_moneysituation.php?atmenu=moneysituation&section=10">{_("Customers Balance")}</a></li>
					<li><a href="A2B_entity_transactions.php?atmenu=payment&section=10">»» {_("Transactions")}</a></li>
					<li><a href="A2B_entity_billing_customer.php?atmenu=payment&section=10">»» {_("Billings")}</a></li>
					<li><a href="A2B_entity_logrefill.php?atmenu=payment&section=10">»» {_("Refills")}</a></li>
					<li><a href="A2B_entity_payment.php?atmenu=payment&section=10">»» {_("Payments")}</a></li>
					<li><a href="A2B_entity_paymentlog.php?section=10">»» {_("E-Payment Log")}</a></li>
					<li><a href="A2B_entity_charge.php?section=10">»» {_("Charges")}</a></li>
					<li><a href="A2B_entity_agentsituation.php?atmenu=agentsituation&section=10">{_("Agents Balance")}</a></li>
					<li><a href="A2B_entity_commission_agent.php?atmenu=payment&section=10">»» {_("Commissions")}</a></li>
					<li><a href="A2B_entity_remittance_request.php?atmenu=payment&section=10">»» {_("Remittance Request")}</a></li>
					<li><a href="A2B_entity_transactions_agent.php?atmenu=payment&section=10">»» {_("Transactions")}</a></li>
					<li><a href="A2B_entity_logrefill_agent.php?atmenu=payment&section=10">»» {_("Refills")}</a></li>
					<li><a href="A2B_entity_payment_agent.php?atmenu=payment&section=10">»» {_("Payments")}</a></li>
					<li><a href="A2B_entity_paymentlog_agent.php?section=10">»» {_("E-Payment Log")}</a></li>
					<li><a href="A2B_entity_payment_configuration.php?atmenu=payment&section=10">{_("Payment Methods")}</a></li>
					<li><a href="A2B_currencies.php?section=10">{_("Currency List")}</a></li>
				</ul>
			</li>
		</ul>
	</div>
	{/if}


	{if ($ACXINVOICING > 0)}
	<div class="toggle_menu">
		<li>
			<a href="javascript:;" class="toggle_menu" target="_self">
				<div>
					<div id="menutitlebutton">
						<img id="img11" {if ($section == "11")} src="templates/{$SKIN_NAME}/images/minus.gif" {else} src="templates/{$SKIN_NAME}/images/plus.gif"{/if} onmouseover="this.style.cursor='hand';" >
					</div>
					<div id="menutitlesection">
						<strong>{_("INVOICES")}</strong>
					</div>
				</div>
			</a>
		</li>
	</div>
	<div class="tohide" {if ($section !="11")} style="display:none;" {/if}>
		<ul>
			<li>
				<ul>
					<li><a href="A2B_entity_receipt.php?atmenu=payment&section=11">{_("Receipts")}</a></li>
					<li><a href="A2B_entity_invoice.php?atmenu=payment&section=11">{_("Invoices")}</a></li>
					<li><a href="A2B_entity_invoice_conf.php?atmenu=payment&section=11">»» {_("Configuration")}</a></li>
				</ul>
			</li>
		</ul>
	</div>
	{/if}


	{if ($ACXPACKAGEOFFER > 0)}
	<div class="toggle_menu">
		<li>
			<a href="javascript:;" class="toggle_menu" target="_self">
				<div>
					<div id="menutitlebutton">
						<img id="img12" {if ($section == "12")} src="templates/{$SKIN_NAME}/images/minus.gif" {else} src="templates/{$SKIN_NAME}/images/plus.gif" {/if} onmouseover="this.style.cursor='hand';" >
					</div>
					<div id="menutitlesection">
						<strong>{_("PACKAGE OFFER")}</strong>
					</div>
				</div>
			</a>
		</li>
	</div>
	<div class="tohide"
	{if ($section !="12")} style="display:none;" {/if}>
		<ul>
			<li>
				<ul>
					<li><a href="A2B_entity_package.php?atmenu=package&section=12">{_("Add")}</a></li>
					<li><a href="A2B_detail_package.php?section=12">{_("Details")}</a></li>
				</ul>
			</li>
		</ul>
	</div>
	{/if}


	{if ($ACXCRONTSERVICE  > 0)}
	<div class="toggle_menu">
		<li>
			<a href="javascript:;" class="toggle_menu" target="_self">
				<div>
					<div id="menutitlebutton">
						<img id="img13" {if ($section == "13")} src="templates/{$SKIN_NAME}/images/minus.gif" {else} src="templates/{$SKIN_NAME}/images/plus.gif" {/if} onmouseover="this.style.cursor='hand';" >
					</div>
					<div id="menutitlesection">
						<strong>{_("RECUR SERVICE")}</strong>
					</div>
				</div>
			</a>
		</li>
	</div>
	<div class="tohide"{if ($section !="13")} style="display:none;" {/if}>
		<ul>
			<li>
				<ul>
					<li><a href="A2B_entity_service.php?section=13">{_("Account Service")}</a></li>
					<li><a href="A2B_entity_subscription.php?section=13">{_("Subscriptions Service")}</a></li>
					<li><a href="A2B_entity_subscriber_signup.php?section=13">{_("Subscriptions SIGNUP")}</a></li>
					<li><a href="A2B_entity_subscriber.php?section=13">{_("Subscribers")}</a></li>
					<li><a href="A2B_entity_autorefill.php?section=13">{_("AutoRefill Report")}</a></li>
				</ul>
			</li>
		</ul>
	</div>
	{/if}


	{if ($ACXCALLBACK  > 0)}
	<div class="toggle_menu">
		<li>
			<a href="javascript:;" class="toggle_menu" target="_self">
				<div>
					<div id="menutitlebutton">
						<img id="img14" {if ($section == "14")} src="templates/{$SKIN_NAME}/images/minus.gif" {else} src="templates/{$SKIN_NAME}/images/plus.gif" {/if} onmouseover="this.style.cursor='hand';" >
					</div>
					<div id="menutitlesection">
						<strong>{_("CALLBACK")}</strong>
					</div>
				</div>
			</a>
		</li>
	</div>
	<div class="tohide" {if ($section !="14")} style="display:none;" {/if}>
		<ul>
			<li>
				<ul>
					<li><a href="A2B_entity_callback.php?section=14">{_("Add")}</a></li>
					<li><a href="A2B_entity_server_group.php?section=14">{_("Server Group")}</a></li>
					<li><a href="A2B_entity_server.php?section=14">{_("Server")}</a></li>
				</ul>
			</li>
		</ul>
	</div>
	{/if}

	{if ($ACXPREDICTIVEDIALER  > 0)}
	<div class="toggle_menu">
		<li>
			<a href="javascript:;" class="toggle_menu" target="_self">
				<div>
					<div id="menutitlebutton">
						<img id="img15" {if ($section == "15")} src="templates/{$SKIN_NAME}/images/minus.gif" {else} src="templates/{$SKIN_NAME}/images/plus.gif" {/if} onmouseover="this.style.cursor='hand';" >
					</div>
					<div id="menutitlesection">
						<strong>{_("CAMPAIGNS")}</strong>
					</div>
				</div>
			</a>
		</li>
	</div>
	<div class="tohide" {if ($section !="15")} style="display:none;">{/if}
		<ul>
			<li>
				<ul>
					<li><a href="A2B_entity_campaign.php?section=15">{_("Autodialer")}</a></li>
				</ul>
			</li>
		</ul>
	</div>
	{/if}


	{if ($ACXMAINTENANCE  > 0)}
	<div class="toggle_menu">
		<li>
			<a href="javascript:;" class="toggle_menu" target="_self">
				<div>
					<div id="menutitlebutton">
						<img id="img16" {if ($section == "16")} src="templates/{$SKIN_NAME}/images/minus.gif" {else} src="templates/{$SKIN_NAME}/images/plus.gif" {/if} onmouseover="this.style.cursor='hand';" >
					</div>
					<div id="menutitlesection">
						<strong>{_("MAINTENANCE")}</strong>
					</div>
				</div>
			</a>
		</li>
	</div>
	<div class="tohide" {if ($section !="16")} style="display:none;" {/if}>
		<ul>
			<li>
				<ul>
					<li><a href="A2B_entity_alarm.php?section=16"> {_("Alarms")}</a></li>
					<li><a href="A2B_entity_log_viewer.php?section=16">{_("Users Activity")}</a></li>
					<li><a href="A2B_entity_backup.php?form_action=ask-add&section=16">{_("Database Backup")}</a></li>
					<li><a href="A2B_entity_restore.php?section=16">{_("Database Restore")}</a></li>
					<li><a href="CC_musiconhold.php?section=16">{_("MusicOnHold")}</a></li>
					<li><a href="CC_upload.php?section=16">{_("Upload File")}</a></li>
					<li><a href="A2B_logfile.php?section=16">{_("Watch Log files")}</a></li>
					<li><a href="A2B_data_archiving.php?section=16">{_("Archiving")}</a></li>
					<li><a href="A2B_asteriskinfo.php?section=16">{"Asterisk Info"}</a></li>
					<li><a href="A2B_phpsysinfo.php?section=16">{"phpSysInfo"}</a></li>
					<li><a href="A2B_phpinfo.php?section=16">{"phpInfo"}</a></li>
					<li><a href="A2B_entity_monitor.php?section=16"> {_("Monitoring")}</a></li>
				</ul>
			</li>
		</ul>
	</div>
	{/if}


	{if ($ACXMAIL  > 0)}
	<!-- Disabled Mail feature -->
	<div class="toggle_menu">
		<li>
			<a href="javascript:;" class="toggle_menu" target="_self">
				<div>
					<div id="menutitlebutton">
						<img id="img17" {if ($section == "17")} src="templates/{$SKIN_NAME}/images/minus.gif" {else} src="templates/{$SKIN_NAME}/images/plus.gif" {/if} onmouseover="this.style.cursor='hand';" >
					</div>
					<div id="menutitlesection">
						<strong>{_("MAIL")}</strong>
					</div>
				</div>
			</a>
		</li>
	</div>
	<div class="tohide" {if ($section !="17")} style="display:none;" {/if}>
		<ul>
			<li>
				<ul>
					<li><a href="A2B_entity_mailtemplate.php?atmenu=mailtemplate&section=17&languages=en">{_("Mail templates")}</a></li>
					<li><a href="A2B_mass_mail.php?section=17">{_("Mass Mail")}</a></li>
				</ul>
			</li>
		</ul>
	</div>
	{/if}


	{if ($ACXSETTING  > 0)}
	<div class="toggle_menu">
		<li>
			<a href="javascript:;" class="toggle_menu" target="_self">
				<div>
					<div id="menutitlebutton">
						<img id="img18" {if ($section == "18")} src="templates/{$SKIN_NAME}/images/minus.gif" {else} src="templates/{$SKIN_NAME}/images/plus.gif" {/if} onmouseover="this.style.cursor='hand';" >
					</div>
					<div id="menutitlesection">
						<strong>{_("SYSTEM SETTINGS")}</strong>
					</div>
				</div>
			</a>
		</li>
	</div>
	<div class="tohide" {if ($section !="18")} style="display:none;" {/if}>
		<ul>
			<li>
				<ul>
					<li><a href="A2B_entity_config.php?form_action=list&atmenu=config&section=18">{_("Global List")}</a></li>
					<li><a href="A2B_entity_config_group.php?form_action=list&atmenu=configgroup&section=18">{_("Group List")}</a></li>
					<li><a href="A2B_entity_config_generate_confirm.php?section=18">{_("Add agi-conf")}</a></li>
					<li><a href="phpconfig.php?dir=/etc/asterisk&section=18">{_("* Config Editor")}</a></li>
					{if ($ASTERISK_GUI_LINK)}
						<li><a href="http://{$HTTP_HOST}:8088/asterisk/static/config/index.html" target="_blank">{_("Asterisk GUI")}</a></li>
					{/if}
				</ul>
			</li>
		</ul>
	</div>
	{/if}

</ul>

<br/>
<ul id="nav">
	<li>
		<ul>
			<li>
				<a href="A2B_entity_password.php?atmenu=password&form_action=ask-edit"><strong>{_("Change Password")}</strong>
					<img style="vertical-align:bottom;" src="templates/{$SKIN_NAME}/images/key.png">
				</a>
			</li>
		</ul>
	</li>
</ul>

</div>
</div>
</div>



<table width="100%" cellspacing="15">
<tr>
	<td>
		<a href="PP_intro.php?ui_language=english" target="_parent"><img src="templates/{$SKIN_NAME}/images/flags/gb.gif" border="0" title="English" alt="English"></a>
		<a href="PP_intro.php?ui_language=brazilian" target="_parent"><img src="templates/{$SKIN_NAME}/images/flags/br.gif" border="0" title="Brazilian" alt="Brazilian"></a>
		<a href="PP_intro.php?ui_language=romanian" target="_parent"><img src="templates/{$SKIN_NAME}/images/flags/ro.gif" border="0" title="Romanian" alt="Romanian"></a>
		<a href="PP_intro.php?ui_language=french" target="_parent"><img src="templates/{$SKIN_NAME}/images/flags/fr.gif" border="0" title="French" alt="French"></a>
		<a href="PP_intro.php?ui_language=spanish" target="_parent"><img src="templates/{$SKIN_NAME}/images/flags/es.gif" border="0" title="Spanish" alt="Spanish"></a>
		<a href="PP_intro.php?ui_language=greek" target="_parent"><img src="templates/{$SKIN_NAME}/images/flags/gr.gif" border="0" title="Greek" alt="Greek"></a>
		<a href="PP_intro.php?ui_language=italian" target="_parent"><img src="templates/{$SKIN_NAME}/images/flags/it.gif" border="0" title="Italian" alt="Italian"></a>
		<a href="PP_intro.php?ui_language=chinese" target="_parent"><img src="templates/{$SKIN_NAME}/images/flags/cn.gif" border="0" title="Chinese" alt="Chinese"></a>
	</td>
</tr>
</table>

<div id="osx-modal-content">
	<div id="osx-modal-title">Dear A2Billing Administrator</div>
	<div id="osx-modal-data">
		<h2>Licence Violation!</h2>
		<p>Thank you for using A2Billing. However, we have detected that you have edited the Author’s names, Copyright or licensing information in the A2Billing Management Interface.</p>
		<p>The <a href="http://www.fsf.org/licensing/licenses/agpl-3.0.html" target="_blank">AGPL 3</a> license under which you are allowed to use A2Billing requires that the original copyright and license must be displayed and kept intact. Without this information being displayed, you do not have a right to use the software.</p>
		<p>However, if it is important to you that the Author’s names, Copyright and License information is not displayed, possibly for publicity purposes; then we can offer you additional permissions to use and convey A2Billing, with these items removed, for a fee that will be used to help sponsor the continued development of A2Billing.</p>
		<p>For more information, please go to <a target="_blank" href="http://www.asterisk2billing.org/pricing/rebranding/">http://www.asterisk2billing.org/pricing/rebranding/</a>.</p>
		<p>Yours,<br/>
		The A2Billing Team<br/>
		Star2Billing S.L</p>
		<p><button class="simplemodal-close">Close</button></p>
	</div>
</div>


</div> <!-- #left-sidebar -->

<div id="main-content">
	<br/>
{else}
<div>
{/if}

{if ($LCMODAL  > 0)}
<script>
    loadLicenceModal();
</script>
{/if}

{$MAIN_MSG}

