{include file="header.tpl"}


{if (!$popupwindow)}
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
							<svg class="bi d-block mx-auto mb-1" width="24" height="24"><use xlink:href="#home"></use></svg>
							{_("Home")}
						</a>
					</li>
					<li>
						<a href="dashboard.php" class="nav-link text-white">
							<svg class="bi d-block mx-auto mb-1" width="24" height="24"><use xlink:href="#speedometer2"></use></svg>
							{_("Dashboard")}
						</a>
					</li>
					<li>
						<a href="A2B_notification.php" class="nav-link text-white">
							<svg class="bi d-block mx-auto mb-1" width="24" height="24"><use xlink:href="#notify"></use></svg>
							{_("Notification")}
						</a>
					</li>
					<li class="dropdown">
						<a href="#" id="dropdownUser" class="nav-link text-white dropdown-toggle" data-bs-toggle="dropdown">
							<svg class="bi d-block mx-auto mb-1" width="24" height="24"><use xlink:href="#people-circle"></use></svg>
							{_("Account")}
						</a>
						<ul class="dropdown-menu shadow" aria-labelledby="dropdownUser">
							<li>
								<a href="A2B_entity_password.php?atmenu=password&amp;form_action=ask-edit" class="dropdown-item">
									{_("Change Password")}
								</a>
							</li>
							<li><hr class="dropdown-divider"></li>
							<li><a class="dropdown-item" href="logout.php?logout=true">{_("Logout")}</a></li>
						</ul>
					</li>
				</ul>
			</div>
		</div>
	</div>
</header>

<div class="container-fluid">
	<div class="row">
		<nav class="col-md-3 col-lg-2 flex-shrink-0 p-3 bg-light">
			<ul class="list-unstyled ps-0">

			{if ($ACXCUSTOMER > 0) }
				<li class="mb-1">
					<button class="btn btn-toggle align-items-center rounded collapsed" data-bs-toggle="collapse" data-bs-target="#customer-collapse" aria-expanded="{if ($section == "1")}true{else}false{/if}">
						{_("Customers")}
					</button>
					<div class="collapse {if ($section == "1")}show{/if}" id="customer-collapse">
						<ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
							<li><a class="link-dark rounded" href="A2B_entity_card.php?section=1">{_("Add :: Search")}</a></li>
							<li><a class="link-dark rounded" href="CC_card_import.php?section=1">{_("Import")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_friend.php?atmenu=sip&amp;section=1">{_("VoIP Settings")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_callerid.php?atmenu=callerid&amp;section=1">{_("Caller-ID")}</a></li>
							<li><a class="link-dark rounded" href="A2B_notifications.php?section=1">{_("Credit Notification")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_card_group.php?section=1">{_("Groups")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_card_seria.php?section=1">{_("Card series")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_speeddial.php?atmenu=speeddial&amp;section=1">{_("Speed Dial")}</a></li>
							<li><a class="link-dark rounded" href="card-history.php?atmenu=cardhistory&amp;section=1">{_("History")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_statuslog.php?atmenu=statuslog&amp;section=1">{_("Status")}</a></li>
						</ul>
					</div>
				</li>
			{/if}

			{if ($ACXADMINISTRATOR  > 0)}
				<li class="mb-1">
					<button class="btn btn-toggle align-items-center rounded collapsed" data-bs-toggle="collapse" data-bs-target="#agent-collapse" aria-expanded="{if ($section == "2")}true{else}false{/if}">
						{_("Agents")}
					</button>
					<div class="collapse {if ($section == "2")}show{/if}" id="agent-collapse">
						<ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
							<li><a class="link-dark rounded" href="A2B_entity_agent.php?atmenu=user&amp;section=2">{_("Add :: Search")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_signup_agent.php?atmenu=user&amp;section=2">{_("Signup URLs")}</a></li>
						</ul>
					</div>
				</li>
			{/if}


			{if ($ACXADMINISTRATOR  > 0)}
				<li class="mb-1">
					<button class="btn btn-toggle align-items-center rounded collapsed" data-bs-toggle="collapse" data-bs-target="#admin-collapse" aria-expanded="{if ($section == "3")}true{else}false{/if}">
						{_("Admins")}
					</button>
					<div class="collapse {if ($section == "3")}show{/if}" id="admin-collapse">
						<ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
							<li><a class="link-dark rounded" href="A2B_entity_user.php?atmenu=user&amp;groupID=0&amp;section=3">{_("Add :: Search")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_user.php?atmenu=user&amp;groupID=1&amp;section=3">{_("Access Control")}</a></li>
						</ul>
					</div>
				</li>
			{/if}

			{if ($ACXSUPPORT > 0)}
				<li class="mb-1">
					<button class="btn btn-toggle align-items-center rounded collapsed" data-bs-toggle="collapse" data-bs-target="#support-collapse" aria-expanded="{if ($section == "4")}true{else}false{/if}">
						{_("Support")}
					</button>
					<div class="collapse {if ($section == "4")}show{/if}" id="support-collapse">
						<ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
							<li><a class="link-dark rounded" href="CC_ticket.php?section=4">{_("Customer Tickets")}</a></li>
							<li><a class="link-dark rounded" href="A2B_ticket_agent.php?section=4">{_("Agent Tickets")}</a></li>
							<li><a class="link-dark rounded" href="CC_support_component.php?section=4">{_("Ticket Components")}</a></li>
							<li><a class="link-dark rounded" href="CC_support.php?section=4">{_("Support Boxes")}</a></li>
						</ul>
					</div>
				</li>
			{/if}

			{if ($ACXCALLREPORT > 0)}
				<li class="mb-1">
					<button class="btn btn-toggle align-items-center rounded collapsed" data-bs-toggle="collapse" data-bs-target="#report-collapse" aria-expanded="{if ($section == "5")}true{else}false{/if}">
						{_("Call Reports")}
					</button>
					<div class="collapse {if ($section == "5")}show{/if}" id="report-collapse">
						<ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
							<li><a class="link-dark rounded" href="call-log-customers.php?nodisplay=1&amp;posted=1&amp;section=5">{_("CDRs")}</a></li>
							<li><a class="link-dark rounded" href="call-count-reporting.php?nodisplay=1&amp;posted=1&amp;section=5">{_("Call Count")}</a></li>
							<li><a class="link-dark rounded" href="A2B_trunk_report.php?section=5">{_("Trunk")}</a></li>
							<li><a class="link-dark rounded" href="call-dnid.php?nodisplay=1&amp;posted=1&amp;section=5">{_("DNID")}</a></li>
							<li><a class="link-dark rounded" href="call-pnl-report.php?section=5">{_("PNL")}</a></li>
							<li><a class="link-dark rounded" href="call-comp.php?section=5">{_("Compare Calls")}</a></li>
							<li><a class="link-dark rounded" href="call-daily-load.php?section=5">{_("Daily Traffic")}</a></li>
							<li><a class="link-dark rounded" href="call-last-month.php?section=5">{_("Monthly Traffic")}</a></li>
						</ul>
					</div>
				</li>
			{/if}

			{if ($ACXRATECARD > 0)}
				<li class="mb-1">
					<button class="btn btn-toggle align-items-center rounded collapsed" data-bs-toggle="collapse" data-bs-target="#rate-collapse" aria-expanded="{if ($section == "6")}true{else}false{/if}">
						{_("Rates")}
					</button>
					<div class="collapse {if ($section == "6")}show{/if}" id="rate-collapse">
						<ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
							<li><a class="link-dark rounded" href="A2B_entity_tariffgroup.php?atmenu=tariffgroup&amp;section=6">{_("Call Plan")}</a></li>
							<li>
								<a class="link-dark rounded" href="A2B_entity_tariffplan.php?atmenu=tariffplan&amp;section=6">{_("RateCards")}</a>
								<ul class="list-unstyled fw-normal ps-3 pb-1">
									<li><a class="link-dark rounded" href="CC_ratecard_import.php?atmenu=ratecard&amp;section=6">{_("Import")}</a>
									<li><a class="link-dark rounded" href="CC_ratecard_merging.php?atmenu=ratecard&amp;section=6">{_("Merge")}</a></li>
									<li><a class="link-dark rounded" href="CC_entity_sim_ratecard.php?atmenu=ratecard&amp;section=6">{_("Simulator")}</a></li>
								</ul>
							</li>
							<li><a class="link-dark rounded" href="A2B_entity_def_ratecard.php?atmenu=ratecard&amp;section=6">{_("Rates")}</a></li>
						</ul>
					</div>
				</li>
			{/if}

			{if ($ACXTRUNK > 0)}
				<li class="mb-1">
					<button class="btn btn-toggle align-items-center rounded collapsed" data-bs-toggle="collapse" data-bs-target="#provider-collapse" aria-expanded="{if ($section == "7")}true{else}false{/if}">
						{_("Providers")}
					</button>
					<div class="collapse {if ($section == "7")}show{/if}" id="provider-collapse">
						<ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
							<li><a class="link-dark rounded" href="A2B_entity_provider.php?section=7">{_("Providers")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_trunk.php?section=7">{_("Trunks")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_prefix.php?section=7">{_("Prefixes")}</a></li>
						</ul>
					</div>
				</li>
			{/if}

			{if ($ACXDID > 0)}
				<li class="mb-1">
					<button class="btn btn-toggle align-items-center rounded collapsed" data-bs-toggle="collapse" data-bs-target="#did-collapse" aria-expanded="{if ($section == "8")}true{else}false{/if}">
						{_("Inbound DID")}
					</button>
					<div class="collapse {if ($section == "8")}show{/if}" id="did-collapse">
						<ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
							<li><a class="link-dark rounded" href="A2B_entity_did.php?section=8">{_("Add :: Search")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_didgroup.php?section=8">{_("Groups")}</a>
							<li><a class="link-dark rounded" href="A2B_entity_did_destination.php?section=8">{_("Destination")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_did_import.php?section=8">{_("Import [CSV]")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_didx.php?section=8">{_("Import [DIDX]")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_did_use.php?atmenu=did_use&amp;section=8">{_("Usage")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_did_billing.php?atmenu=did_billing&amp;section=8">{_("Billing")}</a></li>
						</ul>
					</div>
				</li>
			{/if}


			{if ($ACXOUTBOUNDCID > 0)}
				<li class="mb-1">
					<button class="btn btn-toggle align-items-center rounded collapsed" data-bs-toggle="collapse" data-bs-target="#cid-collapse" aria-expanded="{if ($section == "9")}true{else}false{/if}">
						{_("Outbound CID")}
					</button>
					<div class="collapse {if ($section == "9")}show{/if}" id="cid-collapse">
						<ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
							<li><a class="link-dark rounded" href="A2B_entity_outbound_cid.php?atmenu=cid&amp;section=9">{_("Add")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_outbound_cidgroup.php?atmenu=cidgroup&amp;section=9">{_("Groups")}</a></li>
						</ul>
					</div>
				</li>
			{/if}


			{if ($ACXBILLING > 0)}
				<li class="mb-1">
					<button class="btn btn-toggle align-items-center rounded collapsed" data-bs-toggle="collapse" data-bs-target="#billing-collapse" aria-expanded="{if ($section == "10")}true{else}false{/if}">
						{_("Billing")}
					</button>
					<div class="collapse {if ($section == "10")}show{/if}" id="billing-collapse">
						<ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
							<li><a class="link-dark rounded" href="A2B_entity_voucher.php?section=10">{_("Vouchers")}</a></li>
							<li>
								<a class="link-dark rounded" href="A2B_entity_moneysituation.php?atmenu=moneysituation&amp;section=10">{_("Customers Balance")}</a>
								<ul class="list-unstyled fw-normal ps-3 pb-1">
									<li><a class="link-dark rounded" href="A2B_entity_transactions.php?atmenu=payment&amp;section=10">{_("Transactions")}</a></li>
									<li><a class="link-dark rounded" href="A2B_entity_billing_customer.php?atmenu=payment&amp;section=10">{_("Billings")}</a></li>
									<li><a class="link-dark rounded" href="A2B_entity_logrefill.php?atmenu=payment&amp;section=10">{_("Refills")}</a></li>
									<li><a class="link-dark rounded" href="A2B_entity_payment.php?atmenu=payment&amp;section=10">{_("Payments")}</a></li>
									<li><a class="link-dark rounded" href="A2B_entity_paymentlog.php?section=10">{_("E-Payment Log")}</a></li>
									<li><a class="link-dark rounded" href="A2B_entity_charge.php?section=10">{_("Charges")}</a></li>
								</ul>
							</li>
							<li>
								<a class="link-dark rounded" href="A2B_entity_agentsituation.php?atmenu=agentsituation&amp;section=10">{_("Agents Balance")}</a>
								<ul class="list-unstyled fw-normal ps-3 pb-1">
									<li><a class="link-dark rounded" href="A2B_entity_commission_agent.php?atmenu=payment&amp;section=10">{_("Commissions")}</a></li>
									<li><a class="link-dark rounded" href="A2B_entity_remittance_request.php?atmenu=payment&amp;section=10">{_("Remittance Request")}</a></li>
									<li><a class="link-dark rounded" href="A2B_entity_transactions_agent.php?atmenu=payment&amp;section=10">{_("Transactions")}</a></li>
									<li><a class="link-dark rounded" href="A2B_entity_logrefill_agent.php?atmenu=payment&amp;section=10">{_("Refills")}</a></li>
									<li><a class="link-dark rounded" href="A2B_entity_payment_agent.php?atmenu=payment&amp;section=10">{_("Payments")}</a></li>
									<li><a class="link-dark rounded" href="A2B_entity_paymentlog_agent.php?section=10">{_("E-Payment Log")}</a></li>
								</ul>
							</li>
							<li><a class="link-dark rounded" href="A2B_entity_payment_configuration.php?atmenu=payment&amp;section=10">{_("Payment Methods")}</a></li>
							<li><a class="link-dark rounded" href="A2B_currencies.php?section=10">{_("Currency List")}</a></li>
						</ul>
					</div>
				</li>
			{/if}


			{if ($ACXINVOICING > 0)}
				<li class="mb-1">
					<button class="btn btn-toggle align-items-center rounded collapsed" data-bs-toggle="collapse" data-bs-target="#invoice-collapse" aria-expanded="{if ($section == "11")}true{else}false{/if}">
						{_("Invoices")}
					</button>
					<div class="collapse {if ($section == "11")}show{/if}" id="invoice-collapse">
						<ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
							<li><a class="link-dark rounded" href="A2B_entity_receipt.php?atmenu=payment&amp;section=11">{_("Receipts")}</a></li>
							<li>
								<a class="link-dark rounded" href="A2B_entity_invoice.php?atmenu=payment&amp;section=11">{_("Invoices")}</a>
								<ul class="list-unstyled fw-normal ps-3 pb-1">
									<li><a class="link-dark rounded" href="A2B_entity_invoice_conf.php?atmenu=payment&amp;section=11">{_("Configuration")}</a></li>
								</ul>
							</li>
						</ul>
					</div>
				</li>
			{/if}


			{if ($ACXPACKAGEOFFER > 0)}
				<li class="mb-1">
					<button class="btn btn-toggle align-items-center rounded collapsed" data-bs-toggle="collapse" data-bs-target="#package-collapse" aria-expanded="{if ($section == "12")}true{else}false{/if}">
						{_("Package Offer")}
					</button>
					<div class="collapse {if ($section == "12")}show{/if}" id="package-collapse">
						<ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
							<li><a class="link-dark rounded" href="A2B_entity_package.php?atmenu=package&amp;section=12">{_("Add")}</a></li>
							<li><a class="link-dark rounded" href="A2B_detail_package.php?section=12">{_("Details")}</a></li>
						</ul>
					</div>
				</li>
			{/if}


			{if ($ACXCRONTSERVICE  > 0)}
				<li class="mb-1">
					<button class="btn btn-toggle align-items-center rounded collapsed" data-bs-toggle="collapse" data-bs-target="#cron-collapse" aria-expanded="{if ($section == "13")}true{else}false{/if}">
						{_("Recur Service")}
					</button>
					<div class="collapse {if ($section == "13")}show{/if}" id="cron-collapse">
						<ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
							<li><a class="link-dark rounded" href="A2B_entity_service.php?section=13">{_("Account Service")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_subscription.php?section=13">{_("Subscriptions Service")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_subscriber_signup.php?section=13">{_("Subscriptions SIGNUP")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_subscriber.php?section=13">{_("Subscribers")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_autorefill.php?section=13">{_("AutoRefill Report")}</a></li>
						</ul>
					</div>
				</li>
			{/if}


			{if ($ACXCALLBACK  > 0)}
				<li class="mb-1">
					<button class="btn btn-toggle align-items-center rounded collapsed" data-bs-toggle="collapse" data-bs-target="#callback-collapse" aria-expanded="{if ($section == "14")}true{else}false{/if}">
						{_("Callback")}
					</button>
					<div class="collapse {if ($section == "14")}show{/if}" id="callback-collapse">
						<ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
							<li><a class="link-dark rounded" href="A2B_entity_callback.php?section=14">{_("Add")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_server_group.php?section=14">{_("Server Group")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_server.php?section=14">{_("Server")}</a></li>
						</ul>
					</div>
				</li>
			{/if}


			{if ($ACXMAINTENANCE  > 0)}
				<li class="mb-1">
					<button class="btn btn-toggle align-items-center rounded collapsed" data-bs-toggle="collapse" data-bs-target="#maintenance-collapse" aria-expanded="{if ($section == "16")}true{else}false{/if}">
						{_("Maintenance")}
					</button>
					<div class="collapse {if ($section == "16")}show{/if}" id="maintenance-collapse">
						<ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
							<li><a class="link-dark rounded" href="A2B_entity_alarm.php?section=16"> {_("Alarms")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_log_viewer.php?section=16">{_("Users Activity")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_backup.php?form_action=ask-add&amp;section=16">{_("Database Backup")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_restore.php?section=16">{_("Database Restore")}</a></li>
							<li><a class="link-dark rounded" href="CC_musiconhold.php?section=16">{_("MusicOnHold")}</a></li>
							<li><a class="link-dark rounded" href="CC_upload.php?section=16">{_("Upload File")}</a></li>
							<li><a class="link-dark rounded" href="A2B_logfile.php?section=16">{_("Watch Log files")}</a></li>
							<li><a class="link-dark rounded" href="A2B_data_archiving.php?section=16">{_("Archiving")}</a></li>
							<li><a class="link-dark rounded" href="A2B_asteriskinfo.php?section=16">{"Asterisk Info"}</a></li>
							<li><a class="link-dark rounded" href="A2B_phpsysinfo.php?section=16">{"phpSysInfo"}</a></li>
							<li><a class="link-dark rounded" href="A2B_phpinfo.php?section=16">{"phpInfo"}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_monitor.php?section=16"> {_("Monitoring")}</a></li>
						</ul>
					</div>
				</li>
			{/if}


			{if ($ACXMAIL  > 0)}
				<li class="mb-1">
					<button class="btn btn-toggle align-items-center rounded collapsed" data-bs-toggle="collapse" data-bs-target="#mail-collapse" aria-expanded="{if ($section == "17")}true{else}false{/if}">
						{_("Mail")}
					</button>
					<div class="collapse {if ($section == "17")}show{/if}" id="mail-collapse">
						<ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
							<li><a class="link-dark rounded" href="A2B_entity_mailtemplate.php?atmenu=mailtemplate&amp;section=17&amp;languages=en">{_("Mail templates")}</a></li>
							<li><a class="link-dark rounded" href="A2B_mass_mail.php?section=17">{_("Mass Mail")}</a></li>
						</ul>
					</div>
				</li>
			{/if}


			{if ($ACXSETTING  > 0)}
				<li class="mb-1">
					<button class="btn btn-toggle align-items-center rounded collapsed" data-bs-toggle="collapse" data-bs-target="#setting-collapse" aria-expanded="{if ($section == "18")}true{else}false{/if}">
						{_("System Settings")}
					</button>
					<div class="collapse {if ($section == "18")}show{/if}" id="setting-collapse">
						<ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
							<li><a class="link-dark rounded" href="A2B_entity_config.php?form_action=list&amp;atmenu=config&amp;section=18">{_("Global List")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_config_group.php?form_action=list&amp;atmenu=configgroup&amp;section=18">{_("Group List")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_config_generate_confirm.php?section=18">{_("Add agi-conf")}</a></li>
							<li><a class="link-dark rounded" href="phpconfig.php?dir=/etc/asterisk&amp;section=18">{_("* Config Editor")}</a></li>
							{if ($ASTERISK_GUI_LINK)}
								<li><a class="link-dark rounded" href="http://{$HTTP_HOST}:8088/asterisk/static/config/index.html" target="_blank">{_("Asterisk GUI")}</a></li>
							{/if}
						</ul>
					</div>
				</li>
			{/if}
				<li class="mb-1">
					<button class="btn btn-toggle align-items-center rounded collapsed" data-bs-toggle="collapse" data-bs-target="#language-collapse" aria-expanded="false">
						{_("Language")}
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
		<div id="main-content" class="col-md-9 col-lg-10 pt-3">

{else}
<div class="container-fluid">
	<div class="row">
		<div class="col m-1">
{/if}

{$MAIN_MSG}

