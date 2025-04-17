{include file="header.tpl"}


{if ($popupwindow == 0)}
<div id="left-sidebar">
<div id="leftmenu-top">
<div id="leftmenu-down">
<div id="leftmenu-middle">

<ul id="nav">
	<li>
	<a href="PP_intro.php" target="_top"><img style="vertical-align:bottom;" src="templates/{$SKIN_NAME}/images/house.png"> <b>&nbsp;&nbsp;{_("HOME")}</b> </a>
	</li>
	{if ($ACXMYACCOUNT > 0) }
	<div class="toggle_menu"><li>
	<a href="javascript:;" class="toggle_menu" target="_self"><img id="img1"
	{if ($section == "0")}
	src="templates/{$SKIN_NAME}/images/minus.gif"
	{else}
	src="templates/{$SKIN_NAME}/images/plus.gif"
	{/if}
 onmouseover="this.style.cursor='hand';" >&nbsp; <strong>{_("MY ACCOUNT")}</strong></a></li></div>
	<div class="tohide"
	{if ($section =="4")}
	style="">
	{else}
	style="display:none;">
	{/if}
	<ul>
		<li><ul>
				<li><a href="agentinfo.php?section=4">{_("Account information")}</a></li>
				<li><a href="A2B_entity_password.php?section=4">{_("Password")}</a></li>
				<li><a href="A2B_entity_remittance_request.php?section=4">{_("Historic Remittance")}</a></li>
		</ul></li>
	</ul>
	</div>
	{/if}

	{if ($ACXCUSTOMER > 0) }
	<div class="toggle_menu"><li>
	<a href="javascript:;" class="toggle_menu" target="_self"> <div> <div id="menutitlebutton"> <img id="img1"
	{if ($section == "1")}
	src="templates/{$SKIN_NAME}/images/minus.gif"
	{else}
	src="templates/{$SKIN_NAME}/images/plus.gif"
	{/if} onmouseover="this.style.cursor='hand';" ></div> <div id="menutitlesection"><strong>{_("CUSTOMERS")}</strong></div></div></a></li></div>
		<div class="tohide"
	{if ($section =="1")}
		style="">
	{else}
	style="display:none;">
	{/if}
	<ul>
		<li><ul>
				<li><a href="A2B_entity_card.php?section=1">{_("List Customers")}</a></li>
				<li><a href="A2B_entity_callerid.php?section=1">{_("Caller-ID")}</a></li>
				{if ($ACXCALLREPORT > 0) }
				<li><a href="A2B_report_card_history?section=1">{_("Card History")}</a></li>
				{/if}
				{if ($ACXVOIPCONF > 0) }
				<li><a href="A2B_entity_friend.php?section=1">{_("VOIP Config")}</a></li>
				{/if}
		</ul></li>
	</ul>
	</div>
	{/if}


	{if ($ACXSIGNUP > 0) }
	<div class="toggle_menu"><li>
	<a href="javascript:;" class="toggle_menu" target="_self"> <div> <div id="menutitlebutton"> <img id="img8"
	{if ($section == "8")}
	src="templates/{$SKIN_NAME}/images/minus.gif"
	{else}
	src="templates/{$SKIN_NAME}/images/plus.gif"
	{/if} onmouseover="this.style.cursor='hand';" ></div> <div id="menutitlesection"><strong>{_("SIGNUP")}</strong></div></div></a></li></div>
		<div class="tohide"
	{if ($section =="8")}
		style="">
	{else}
	style="display:none;">
	{/if}
	<ul>
		<li><ul>
				<li><a href="A2B_entity_signup_agent.php?section=8">{_("Signup Url List")}</a></li>
				<li><a href="A2B_signup_agent.php?section=8">{_("Add New Signup Url")}</a></li>
		</ul></li>
	</ul>
	</div>
	{/if}


	{if ($ACXBILLING > 0)}
	<div class="toggle_menu"><li>
	<a href="javascript:;" class="toggle_menu" target="_self"> <div> <div id="menutitlebutton"> <img id="img2"
	{if ($section == "2")}
	src="templates/{$SKIN_NAME}/images/minus.gif"
	{else}
	src="templates/{$SKIN_NAME}/images/plus.gif"
	{/if} onmouseover="this.style.cursor='hand';" ></div> <div id="menutitlesection"><strong>{_("BILLING")}</strong></div></div></a></li></div>
		<div class="tohide"
	{if ($section =="2")}
		style="">
	{else}
	style="display:none;">
	{/if}
		<ul>
			<li><ul>
				<li><a href="A2B_entity_moneysituation.php?section=2">{_("Account balance")}</a></li>
				<li><a href="A2B_entity_logrefill_agent.php?section=2">{_("Own Refills")}</a></li>
				<li><a href="A2B_entity_payment_agent.php?section=2">{_("Own Payments")}</a></li>
				<li><a href="A2B_entity_logrefill.php?section=2">{_("Customer's Refills")}</a></li>
				<li><a href="A2B_entity_payment.php?section=2">{_("Customer's Payment")}</a></li>
				<li><a href="A2B_entity_paymentlog.php?section=2">{_("Payment Log")}</a></li>
				<li><a href="A2B_entity_commission.php?section=2">{_("Commission")}</a></li>
			</ul></li>
		</ul>
	</div>
	{/if}

	{if ($ACXRATECARD > 0)}
	<div class="toggle_menu"><li>
	<a href="javascript:;" class="toggle_menu" target="_self"> <div> <div id="menutitlebutton"> <img id="img3"
	{if ($section == "3")}
	src="templates/{$SKIN_NAME}/images/minus.gif"
	{else}
	src="templates/{$SKIN_NAME}/images/plus.gif"
	{/if} onmouseover="this.style.cursor='hand';" ></div> <div id="menutitlesection"><strong>{_("RATECARD")}</strong></div></div></a></li></div>
		<div class="tohide"
	{if ($section =="3")}
		style="">
	{else}
		style="display:none;">
	{/if}
		<ul>
			<li><ul>
				<li><a href="A2B_entity_def_ratecard.php?section=3">{_("Browse Rates")} </a></li>
			</ul></li>
		</ul>
	</div>
	{/if}

	{if ($ACXCALLREPORT > 0)}
	<div class="toggle_menu"><li>
	<a href="javascript:;" class="toggle_menu" target="_self"> <div> <div id="menutitlebutton"> <img id="img6"
	{if ($section == "6")}
	src="templates/{$SKIN_NAME}/images/minus.gif"
	{else}
	src="templates/{$SKIN_NAME}/images/plus.gif"
	{/if} onmouseover="this.style.cursor='hand';" ></div> <div id="menutitlesection"><strong>{_("CALL REPORT")}</strong></div></div></a></li></div>
		<div class="tohide"
	{if ($section =="6")}
		style="">
	{else}
		style="display:none;">
	{/if}
		<ul>
			<li><ul>
					<li><a href="A2B_report_calls.php?section=6">{_("CDR Report")}</a></li>
					<li><a href="call-last-month.php?section=6">{_("Monthly Traffic")}</a></li>
			</ul></li>
		</ul>
	</div>
	{/if}

	{if ($ACXSUPPORT  > 0)}
	<div class="toggle_menu"><li>
	<a href="javascript:;" class="toggle_menu" target="_self"> <div> <div id="menutitlebutton"> <img id="img7"
	{if ($section == "7")}
	src="templates/{$SKIN_NAME}/images/minus.gif"
	{else}
	src="templates/{$SKIN_NAME}/images/plus.gif"
	{/if} onmouseover="this.style.cursor='hand';" ></div> <div id="menutitlesection"><strong>{_("SUPPORT")}</strong></div></div></a></li></div>
		<div class="tohide"
	{if ($section =="7")}
		style="">
	{else}
		style="display:none;">
	{/if}
		<ul>
			<li><ul>
				<li><a href="A2B_ticket.php?section=7">{_("Customer Tickets")}</a></li>
				<li><a href="A2B_support.php">{_("View and Create Tickets")}</a></li>
			</ul></li>
		</ul>
	</div>
	{/if}


</ul>
<br>
<ul id="nav">
	<li>
	<a href="logout.php?logout=true" target="_top"><img style="vertical-align:bottom;" src="templates/{$SKIN_NAME}/images/logout.png"> <font color="#DD0000"><b>&nbsp;&nbsp;{_("LOGOUT")}</b></font> </a>
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


</div>

<div id="main-content">
<br/>
{else}
<div>
{/if}

{$MAIN_MSG}

