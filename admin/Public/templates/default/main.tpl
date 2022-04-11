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
							{if $NEW_NOTIFICATION}
								<svg class="bi d-block mx-auto mb-1" width="24" height="24"><use xlink:href="#notify"></use></svg>
							{else}
								<svg class="bi d-block mx-auto mb-1" width="24" height="24"><use xlink:href="#notify"></use></svg>
							{/if}
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
								<a href="A2B_entity_password.php?form_action=ask-edit" class="dropdown-item">
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

			{if ($ACXCUSTOMER) }
				<li class="mb-1">
					<button class="btn btn-toggle align-items-center rounded collapsed" data-bs-toggle="collapse" data-bs-target="#customer-collapse" aria-expanded="{if ($menu_section === 1)}true{else}false{/if}">
						{_("Customers")}
					</button>
					<div class="collapse {if ($menu_section === 1)}show{/if}" id="customer-collapse">
						<ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
							<li><a class="link-dark rounded" href="A2B_entity_card.php">{_("Add :: Search")}</a></li>
							<li><a class="link-dark rounded" href="CC_card_import.php">{_("Import")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_friend.php?voip_conf=sip">{_("VoIP Settings")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_callerid.php">{_("Caller-ID")}</a></li>
							<li><a class="link-dark rounded" href="A2B_notifications.php">{_("Credit Notification")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_card_group.php">{_("Groups")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_card_seria.php">{_("Card series")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_speeddial.php">{_("Speed Dial")}</a></li>
							<li><a class="link-dark rounded" href="card-history.php">{_("History")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_statuslog.php">{_("Status")}</a></li>
						</ul>
					</div>
				</li>
			{/if}

			{if ($ACXADMINISTRATOR)}
				<li class="mb-1">
					<button class="btn btn-toggle align-items-center rounded collapsed" data-bs-toggle="collapse" data-bs-target="#agent-collapse" aria-expanded="{if ($menu_section === 2)}true{else}false{/if}">
						{_("Agents")}
					</button>
					<div class="collapse {if ($menu_section === 2)}show{/if}" id="agent-collapse">
						<ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
							<li><a class="link-dark rounded" href="A2B_entity_agent.php">{_("Add :: Search")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_signup_agent.php">{_("Signup URLs")}</a></li>
						</ul>
					</div>
				</li>
			{/if}


			{if ($ACXADMINISTRATOR)}
				<li class="mb-1">
					<button class="btn btn-toggle align-items-center rounded collapsed" data-bs-toggle="collapse" data-bs-target="#admin-collapse" aria-expanded="{if ($menu_section === 3)}true{else}false{/if}">
						{_("Admins")}
					</button>
					<div class="collapse {if ($menu_section === 3)}show{/if}" id="admin-collapse">
						<ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
							<li><a class="link-dark rounded" href="A2B_entity_user.php?groupID=0">{_("Add :: Search")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_user.php?groupID=1">{_("Access Control")}</a></li>
						</ul>
					</div>
				</li>
			{/if}

			{if ($ACXSUPPORT)}
				<li class="mb-1">
					<button class="btn btn-toggle align-items-center rounded collapsed" data-bs-toggle="collapse" data-bs-target="#support-collapse" aria-expanded="{if ($menu_section === 4)}true{else}false{/if}">
						{_("Support")}
					</button>
					<div class="collapse {if ($menu_section === 4)}show{/if}" id="support-collapse">
						<ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
							<li><a class="link-dark rounded" href="CC_ticket.php">{_("Customer Tickets")}</a></li>
							<li><a class="link-dark rounded" href="A2B_ticket_agent.php">{_("Agent Tickets")}</a></li>
							<li><a class="link-dark rounded" href="CC_support_component.php">{_("Ticket Components")}</a></li>
							<li><a class="link-dark rounded" href="CC_support.php">{_("Support Boxes")}</a></li>
						</ul>
					</div>
				</li>
			{/if}

			{if ($ACXCALLREPORT)}
				<li class="mb-1">
					<button class="btn btn-toggle align-items-center rounded collapsed" data-bs-toggle="collapse" data-bs-target="#report-collapse" aria-expanded="{if ($menu_section === 5)}true{else}false{/if}">
						{_("Call Reports")}
					</button>
					<div class="collapse {if ($menu_section === 5)}show{/if}" id="report-collapse">
						<ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
							<li><a class="link-dark rounded" href="call-log-customers.php?nodisplay=1&amp;posted=1">{_("CDRs")}</a></li>
							<li><a class="link-dark rounded" href="call-count-reporting.php?nodisplay=1&amp;posted=1">{_("Call Count")}</a></li>
							<li><a class="link-dark rounded" href="A2B_trunk_report.php">{_("Trunk")}</a></li>
							<li><a class="link-dark rounded" href="call-dnid.php?nodisplay=1&amp;posted=1">{_("DNID")}</a></li>
							<li><a class="link-dark rounded" href="call-pnl-report.php">{_("PNL")}</a></li>
							<li><a class="link-dark rounded" href="call-comp.php">{_("Compare Calls")}</a></li>
							<li><a class="link-dark rounded" href="call-daily-load.php">{_("Daily Traffic")}</a></li>
							<li><a class="link-dark rounded" href="call-last-month.php">{_("Monthly Traffic")}</a></li>
						</ul>
					</div>
				</li>
			{/if}

			{if ($ACXRATECARD)}
				<li class="mb-1">
					<button class="btn btn-toggle align-items-center rounded collapsed" data-bs-toggle="collapse" data-bs-target="#rate-collapse" aria-expanded="{if ($menu_section === 6)}true{else}false{/if}">
						{_("Rates")}
					</button>
					<div class="collapse {if ($menu_section === 6)}show{/if}" id="rate-collapse">
						<ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
							<li><a class="link-dark rounded" href="A2B_entity_tariffgroup.php">{_("Call Plan")}</a></li>
							<li>
								<a class="link-dark rounded" href="A2B_entity_tariffplan.php">{_("RateCards")}</a>
								<ul class="list-unstyled fw-normal ps-3 pb-1">
									<li><a class="link-dark rounded" href="CC_ratecard_import.php">{_("Import")}</a>
									<li><a class="link-dark rounded" href="CC_ratecard_merging.php">{_("Merge")}</a></li>
									<li><a class="link-dark rounded" href="CC_entity_sim_ratecard.php">{_("Simulator")}</a></li>
								</ul>
							</li>
							<li><a class="link-dark rounded" href="A2B_entity_def_ratecard.php">{_("Rates")}</a></li>
						</ul>
					</div>
				</li>
			{/if}

			{if ($ACXTRUNK)}
				<li class="mb-1">
					<button class="btn btn-toggle align-items-center rounded collapsed" data-bs-toggle="collapse" data-bs-target="#provider-collapse" aria-expanded="{if ($menu_section === 7)}true{else}false{/if}">
						{_("Providers")}
					</button>
					<div class="collapse {if ($menu_section === 7)}show{/if}" id="provider-collapse">
						<ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
							<li><a class="link-dark rounded" href="A2B_entity_provider.php">{_("Providers")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_trunk.php">{_("Trunks")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_prefix.php">{_("Prefixes")}</a></li>
						</ul>
					</div>
				</li>
			{/if}

			{if ($ACXDID)}
				<li class="mb-1">
					<button class="btn btn-toggle align-items-center rounded collapsed" data-bs-toggle="collapse" data-bs-target="#did-collapse" aria-expanded="{if ($menu_section === 8)}true{else}false{/if}">
						{_("Inbound DID")}
					</button>
					<div class="collapse {if ($menu_section === 8)}show{/if}" id="did-collapse">
						<ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
							<li><a class="link-dark rounded" href="A2B_entity_did.php">{_("Add :: Search")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_didgroup.php">{_("Groups")}</a>
							<li><a class="link-dark rounded" href="A2B_entity_did_destination.php">{_("Destination")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_did_import.php">{_("Import [CSV]")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_did_use.php">{_("Usage")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_did_billing.php">{_("Billing")}</a></li>
						</ul>
					</div>
				</li>
			{/if}


			{if ($ACXOUTBOUNDCID)}
				<li class="mb-1">
					<button class="btn btn-toggle align-items-center rounded collapsed" data-bs-toggle="collapse" data-bs-target="#cid-collapse" aria-expanded="{if ($menu_section === 9)}true{else}false{/if}">
						{_("Outbound CID")}
					</button>
					<div class="collapse {if ($menu_section === 9)}show{/if}" id="cid-collapse">
						<ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
							<li><a class="link-dark rounded" href="A2B_entity_outbound_cid.php">{_("Add")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_outbound_cidgroup.php">{_("Groups")}</a></li>
						</ul>
					</div>
				</li>
			{/if}


			{if ($ACXBILLING)}
				<li class="mb-1">
					<button class="btn btn-toggle align-items-center rounded collapsed" data-bs-toggle="collapse" data-bs-target="#billing-collapse" aria-expanded="{if ($menu_section === 10)}true{else}false{/if}">
						{_("Billing")}
					</button>
					<div class="collapse {if ($menu_section === 10)}show{/if}" id="billing-collapse">
						<ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
							<li><a class="link-dark rounded" href="A2B_entity_voucher.php">{_("Vouchers")}</a></li>
							<li>
								<a class="link-dark rounded" href="A2B_entity_moneysituation.php">{_("Customers Balance")}</a>
								<ul class="list-unstyled fw-normal ps-3 pb-1">
									<li><a class="link-dark rounded" href="A2B_entity_transactions.php">{_("Transactions")}</a></li>
									<li><a class="link-dark rounded" href="A2B_entity_billing_customer.php">{_("Billings")}</a></li>
									<li><a class="link-dark rounded" href="A2B_entity_logrefill.php">{_("Refills")}</a></li>
									<li><a class="link-dark rounded" href="A2B_entity_payment.php">{_("Payments")}</a></li>
									<li><a class="link-dark rounded" href="A2B_entity_paymentlog.php">{_("E-Payment Log")}</a></li>
									<li><a class="link-dark rounded" href="A2B_entity_charge.php">{_("Charges")}</a></li>
								</ul>
							</li>
							<li>
								<a class="link-dark rounded" href="A2B_entity_agentsituation.php">{_("Agents Balance")}</a>
								<ul class="list-unstyled fw-normal ps-3 pb-1">
									<li><a class="link-dark rounded" href="A2B_entity_commission_agent.php">{_("Commissions")}</a></li>
									<li><a class="link-dark rounded" href="A2B_entity_remittance_request.php">{_("Remittance Request")}</a></li>
									<li><a class="link-dark rounded" href="A2B_entity_transactions_agent.php">{_("Transactions")}</a></li>
									<li><a class="link-dark rounded" href="A2B_entity_logrefill_agent.php">{_("Refills")}</a></li>
									<li><a class="link-dark rounded" href="A2B_entity_payment_agent.php">{_("Payments")}</a></li>
									<li><a class="link-dark rounded" href="A2B_entity_paymentlog_agent.php">{_("E-Payment Log")}</a></li>
								</ul>
							</li>
							<li><a class="link-dark rounded" href="A2B_entity_payment_configuration.php">{_("Payment Methods")}</a></li>
							<li><a class="link-dark rounded" href="A2B_currencies.php">{_("Currency List")}</a></li>
						</ul>
					</div>
				</li>
			{/if}


			{if ($ACXINVOICING)}
				<li class="mb-1">
					<button class="btn btn-toggle align-items-center rounded collapsed" data-bs-toggle="collapse" data-bs-target="#invoice-collapse" aria-expanded="{if ($menu_section === 11)}true{else}false{/if}">
						{_("Invoices")}
					</button>
					<div class="collapse {if ($menu_section === 11)}show{/if}" id="invoice-collapse">
						<ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
							<li><a class="link-dark rounded" href="A2B_entity_receipt.php">{_("Receipts")}</a></li>
							<li>
								<a class="link-dark rounded" href="A2B_entity_invoice.php">{_("Invoices")}</a>
								<ul class="list-unstyled fw-normal ps-3 pb-1">
									<li><a class="link-dark rounded" href="A2B_entity_invoice_conf.php">{_("Configuration")}</a></li>
								</ul>
							</li>
						</ul>
					</div>
				</li>
			{/if}


			{if ($ACXPACKAGEOFFER)}
				<li class="mb-1">
					<button class="btn btn-toggle align-items-center rounded collapsed" data-bs-toggle="collapse" data-bs-target="#package-collapse" aria-expanded="{if ($menu_section === 12)}true{else}false{/if}">
						{_("Package Offer")}
					</button>
					<div class="collapse {if ($menu_section === 12)}show{/if}" id="package-collapse">
						<ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
							<li><a class="link-dark rounded" href="A2B_entity_package.php">{_("Add")}</a></li>
							<li><a class="link-dark rounded" href="A2B_detail_package.php">{_("Details")}</a></li>
						</ul>
					</div>
				</li>
			{/if}


			{if ($ACXCRONTSERVICE)}
				<li class="mb-1">
					<button class="btn btn-toggle align-items-center rounded collapsed" data-bs-toggle="collapse" data-bs-target="#cron-collapse" aria-expanded="{if ($menu_section === 13)}true{else}false{/if}">
						{_("Recur Service")}
					</button>
					<div class="collapse {if ($menu_section === 13)}show{/if}" id="cron-collapse">
						<ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
							<li><a class="link-dark rounded" href="A2B_entity_service.php">{_("Account Service")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_subscription.php">{_("Subscriptions Service")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_subscriber_signup.php">{_("Subscriptions SIGNUP")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_subscriber.php">{_("Subscribers")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_autorefill.php">{_("AutoRefill Report")}</a></li>
						</ul>
					</div>
				</li>
			{/if}


			{if ($ACXCALLBACK)}
				<li class="mb-1">
					<button class="btn btn-toggle align-items-center rounded collapsed" data-bs-toggle="collapse" data-bs-target="#callback-collapse" aria-expanded="{if ($menu_section === 14)}true{else}false{/if}">
						{_("Callback")}
					</button>
					<div class="collapse {if ($menu_section === 14)}show{/if}" id="callback-collapse">
						<ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
							<li><a class="link-dark rounded" href="A2B_entity_callback.php">{_("Add")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_server_group.php">{_("Server Group")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_server.php">{_("Server")}</a></li>
						</ul>
					</div>
				</li>
			{/if}


			{if ($ACXMAINTENANCE)}
				<li class="mb-1">
					<button class="btn btn-toggle align-items-center rounded collapsed" data-bs-toggle="collapse" data-bs-target="#maintenance-collapse" aria-expanded="{if ($menu_section === 16)}true{else}false{/if}">
						{_("Maintenance")}
					</button>
					<div class="collapse {if ($menu_section === 16)}show{/if}" id="maintenance-collapse">
						<ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
							<li><a class="link-dark rounded" href="A2B_entity_alarm.php"> {_("Alarms")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_log_viewer.php">{_("Users Activity")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_backup.php?form_action=ask-add">{_("Database Backup")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_restore.php">{_("Database Restore")}</a></li>
							<li><a class="link-dark rounded" href="CC_musiconhold.php">{_("MusicOnHold")}</a></li>
							<li><a class="link-dark rounded" href="CC_upload.php">{_("Upload File")}</a></li>
							<li><a class="link-dark rounded" href="A2B_logfile.php">{_("Watch Log files")}</a></li>
							<li><a class="link-dark rounded" href="A2B_data_archiving.php">{_("Archiving")}</a></li>
							<li><a class="link-dark rounded" href="A2B_asteriskinfo.php">{"Asterisk Info"}</a></li>
							<li><a class="link-dark rounded" href="A2B_phpsysinfo.php">{"phpSysInfo"}</a></li>
							<li><a class="link-dark rounded" href="A2B_phpinfo.php">{"phpInfo"}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_monitor.php"> {_("Monitoring")}</a></li>
						</ul>
					</div>
				</li>
			{/if}


			{if ($ACXMAIL)}
				<li class="mb-1">
					<button class="btn btn-toggle align-items-center rounded collapsed" data-bs-toggle="collapse" data-bs-target="#mail-collapse" aria-expanded="{if ($menu_section === 17)}true{else}false{/if}">
						{_("Mail")}
					</button>
					<div class="collapse {if ($menu_section === 17)}show{/if}" id="mail-collapse">
						<ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
							<li><a class="link-dark rounded" href="A2B_entity_mailtemplate.php?languages=en">{_("Mail templates")}</a></li>
							<li><a class="link-dark rounded" href="A2B_mass_mail.php">{_("Mass Mail")}</a></li>
						</ul>
					</div>
				</li>
			{/if}


			{if ($ACXSETTING)}
				<li class="mb-1">
					<button class="btn btn-toggle align-items-center rounded collapsed" data-bs-toggle="collapse" data-bs-target="#setting-collapse" aria-expanded="{if ($menu_section === 18)}true{else}false{/if}">
						{_("System Settings")}
					</button>
					<div class="collapse {if ($menu_section === 18)}show{/if}" id="setting-collapse">
						<ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
							<li><a class="link-dark rounded" href="A2B_entity_config.php?form_action=list">{_("Global List")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_config_group.php?form_action=list">{_("Group List")}</a></li>
							<li><a class="link-dark rounded" href="phpconfig.php?dir=/etc/asterisk">{_("* Config Editor")}</a></li>
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

