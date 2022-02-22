{include file="header.tpl"}


{if (!$popupwindow)}
<svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
	<symbol id="home" viewBox="0 0 16 16">
		<path d="M8.354 1.146a.5.5 0 0 0-.708 0l-6 6A.5.5 0 0 0 1.5 7.5v7a.5.5 0 0 0 .5.5h4.5a.5.5 0 0 0 .5-.5v-4h2v4a.5.5 0 0 0 .5.5H14a.5.5 0 0 0 .5-.5v-7a.5.5 0 0 0-.146-.354L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293L8.354 1.146zM2.5 14V7.707l5.5-5.5 5.5 5.5V14H10v-4a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5v4H2.5z"></path>
	</symbol>
	<symbol id="speedometer2" viewBox="0 0 16 16">
		<path d="M8 4a.5.5 0 0 1 .5.5V6a.5.5 0 0 1-1 0V4.5A.5.5 0 0 1 8 4zM3.732 5.732a.5.5 0 0 1 .707 0l.915.914a.5.5 0 1 1-.708.708l-.914-.915a.5.5 0 0 1 0-.707zM2 10a.5.5 0 0 1 .5-.5h1.586a.5.5 0 0 1 0 1H2.5A.5.5 0 0 1 2 10zm9.5 0a.5.5 0 0 1 .5-.5h1.5a.5.5 0 0 1 0 1H12a.5.5 0 0 1-.5-.5zm.754-4.246a.389.389 0 0 0-.527-.02L7.547 9.31a.91.91 0 1 0 1.302 1.258l3.434-4.297a.389.389 0 0 0-.029-.518z"></path>
		<path fill-rule="evenodd" d="M0 10a8 8 0 1 1 15.547 2.661c-.442 1.253-1.845 1.602-2.932 1.25C11.309 13.488 9.475 13 8 13c-1.474 0-3.31.488-4.615.911-1.087.352-2.49.003-2.932-1.25A7.988 7.988 0 0 1 0 10zm8-7a7 7 0 0 0-6.603 9.329c.203.575.923.876 1.68.63C4.397 12.533 6.358 12 8 12s3.604.532 4.923.96c.757.245 1.477-.056 1.68-.631A7 7 0 0 0 8 3z"></path>
	</symbol>
	<symbol id="table" viewBox="0 0 16 16">
		<path d="M0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2zm15 2h-4v3h4V4zm0 4h-4v3h4V8zm0 4h-4v3h3a1 1 0 0 0 1-1v-2zm-5 3v-3H6v3h4zm-5 0v-3H1v2a1 1 0 0 0 1 1h3zm-4-4h4V8H1v3zm0-4h4V4H1v3zm5-3v3h4V4H6zm4 4H6v3h4V8z"></path>
	</symbol>
	<symbol id="people-circle" viewBox="0 0 16 16">
		<path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"></path>
		<path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1z"></path>
	</symbol>
	<symbol id="grid" viewBox="0 0 16 16">
		<path d="M1 2.5A1.5 1.5 0 0 1 2.5 1h3A1.5 1.5 0 0 1 7 2.5v3A1.5 1.5 0 0 1 5.5 7h-3A1.5 1.5 0 0 1 1 5.5v-3zM2.5 2a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zm6.5.5A1.5 1.5 0 0 1 10.5 1h3A1.5 1.5 0 0 1 15 2.5v3A1.5 1.5 0 0 1 13.5 7h-3A1.5 1.5 0 0 1 9 5.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zM1 10.5A1.5 1.5 0 0 1 2.5 9h3A1.5 1.5 0 0 1 7 10.5v3A1.5 1.5 0 0 1 5.5 15h-3A1.5 1.5 0 0 1 1 13.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zm6.5.5A1.5 1.5 0 0 1 10.5 9h3a1.5 1.5 0 0 1 1.5 1.5v3a1.5 1.5 0 0 1-1.5 1.5h-3A1.5 1.5 0 0 1 9 13.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3z"></path>
	</symbol>
</svg>
<header>
	<div class="px-3 py-2 bg-dark text-white">
		<div class="container-fluid">
			<div class="d-flex flex-wrap align-items-center justify-content-center justify-content-md-end">
				<span class="d-flex align-items-center my-2 my-md-0 me-md-auto text-white text-decoration-none">
					A2Billing
				</span>
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
							<svg class="bi d-block mx-auto mb-1" width="24" height="24"><use xlink:href="#table"></use></svg>
							{_("Notification")}
						</a>
					</li>
					<li>
						<a href="logout.php?logout=true" class="nav-link text-white">
							<svg class="bi d-block mx-auto mb-1" width="24" height="24"><use xlink:href="#people-circle"></use></svg>
							{_("Logout")}
						</a>
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
							<li><a class="link-dark rounded" href="A2B_entity_friend.php?atmenu=sip&section=1">{_("VoIP Settings")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_callerid.php?atmenu=callerid&section=1">{_("Caller-ID")}</a></li>
							<li><a class="link-dark rounded" href="A2B_notifications.php?section=1">{_("Credit Notification")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_card_group.php?section=1">{_("Groups")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_card_seria.php?section=1">{_("Card series")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_speeddial.php?atmenu=speeddial&section=1">{_("Speed Dial")}</a></li>
							<li><a class="link-dark rounded" href="card-history.php?atmenu=cardhistory&section=1">{_("History")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_statuslog.php?atmenu=statuslog&section=1">{_("Status")}</a></li>
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
							<li><a class="link-dark rounded" href="A2B_entity_agent.php?atmenu=user&section=2">{_("Add :: Search")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_signup_agent.php?atmenu=user&section=2">{_("Signup URLs")}</a></li>
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
							<li><a class="link-dark rounded" href="A2B_entity_user.php?atmenu=user&groupID=0&section=3">{_("Add :: Search")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_user.php?atmenu=user&groupID=1&section=3">{_("Access Control")}</a></li>
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
							<li><a class="link-dark rounded" href="call-log-customers.php?nodisplay=1&posted=1&section=5">{_("CDRs")}</a></li>
							<li><a class="link-dark rounded" href="call-count-reporting.php?nodisplay=1&posted=1&section=5">{_("Call Count")}</a></li>
							<li><a class="link-dark rounded" href="A2B_trunk_report.php?section=5">{_("Trunk")}</a></li>
							<li><a class="link-dark rounded" href="call-dnid.php?nodisplay=1&posted=1&section=5">{_("DNID")}</a></li>
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
							<li><a class="link-dark rounded" href="A2B_entity_tariffgroup.php?atmenu=tariffgroup&section=6">{_("Call Plan")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_tariffplan.php?atmenu=tariffplan&section=6">{_("RateCards")}</a></li>
							<li><a class="link-dark rounded" href="CC_ratecard_import.php?atmenu=ratecard&section=6">»» {_("Import")}</a></li>
							<li><a class="link-dark rounded" href="CC_ratecard_merging.php?atmenu=ratecard&section=6">»» {_("Merge")}</a></li>
							<li><a class="link-dark rounded" href="CC_entity_sim_ratecard.php?atmenu=ratecard&section=6">»» {_("Simulator")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_def_ratecard.php?atmenu=ratecard&section=6">{_("Rates")}</a></li>
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
							<li><a class="link-dark rounded" href="A2B_entity_did_use.php?atmenu=did_use&section=8">{_("Usage")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_did_billing.php?atmenu=did_billing&section=8">{_("Billing")}</a></li>
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
							<li><a class="link-dark rounded" href="A2B_entity_outbound_cid.php?atmenu=cid&section=9">{_("Add")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_outbound_cidgroup.php?atmenu=cidgroup&section=9">{_("Groups")}</a></li>
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
							<li><a class="link-dark rounded" href="A2B_entity_moneysituation.php?atmenu=moneysituation&section=10">{_("Customers Balance")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_transactions.php?atmenu=payment&section=10">»» {_("Transactions")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_billing_customer.php?atmenu=payment&section=10">»» {_("Billings")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_logrefill.php?atmenu=payment&section=10">»» {_("Refills")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_payment.php?atmenu=payment&section=10">»» {_("Payments")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_paymentlog.php?section=10">»» {_("E-Payment Log")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_charge.php?section=10">»» {_("Charges")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_agentsituation.php?atmenu=agentsituation&section=10">{_("Agents Balance")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_commission_agent.php?atmenu=payment&section=10">»» {_("Commissions")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_remittance_request.php?atmenu=payment&section=10">»» {_("Remittance Request")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_transactions_agent.php?atmenu=payment&section=10">»» {_("Transactions")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_logrefill_agent.php?atmenu=payment&section=10">»» {_("Refills")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_payment_agent.php?atmenu=payment&section=10">»» {_("Payments")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_paymentlog_agent.php?section=10">»» {_("E-Payment Log")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_payment_configuration.php?atmenu=payment&section=10">{_("Payment Methods")}</a></li>
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
							<li><a class="link-dark rounded" href="A2B_entity_receipt.php?atmenu=payment&section=11">{_("Receipts")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_invoice.php?atmenu=payment&section=11">{_("Invoices")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_invoice_conf.php?atmenu=payment&section=11">{_("Configuration")}</a></li>
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
							<li><a class="link-dark rounded" href="A2B_entity_package.php?atmenu=package&section=12">{_("Add")}</a></li>
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
							<li><a class="link-dark rounded" href="A2B_entity_backup.php?form_action=ask-add&section=16">{_("Database Backup")}</a></li>
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
							<li><a class="link-dark rounded" href="A2B_entity_mailtemplate.php?atmenu=mailtemplate&section=17&languages=en">{_("Mail templates")}</a></li>
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
							<li><a class="link-dark rounded" href="A2B_entity_config.php?form_action=list&atmenu=config&section=18">{_("Global List")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_config_group.php?form_action=list&atmenu=configgroup&section=18">{_("Group List")}</a></li>
							<li><a class="link-dark rounded" href="A2B_entity_config_generate_confirm.php?section=18">{_("Add agi-conf")}</a></li>
							<li><a class="link-dark rounded" href="phpconfig.php?dir=/etc/asterisk&section=18">{_("* Config Editor")}</a></li>
							{if ($ASTERISK_GUI_LINK)}
								<li><a class="link-dark rounded" href="http://{$HTTP_HOST}:8088/asterisk/static/config/index.html" target="_blank">{_("Asterisk GUI")}</a></li>
							{/if}
						</ul>
					</div>
				</li>
			{/if}

				<li class="border-top my-3"></li>
				<li class="mb-1">
					<a href="A2B_entity_password.php?atmenu=password&form_action=ask-edit" class="btn btn-sm rounded">
						{_("Change Password")}
					</a>
				</li>
			</ul>
		</nav>
		<div id="main-content" class="col-md-9 col-lg-10">





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

{else}
<div>
{/if}

{if ($LCMODAL  > 0)}
<script>
    new bootstrap.Modal(document.getElementById("license-modal")).show();
</script>
{/if}

{$MAIN_MSG}

