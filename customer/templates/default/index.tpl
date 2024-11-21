<HTML>
<HEAD>
	<link rel="shortcut icon" href="templates/{$SKIN_NAME}/images/a2billing-icon-32x32.ico">
	<title>..:: {$CCMAINTITLE} ::..</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		{if ($CSS_NAME!="" && $CSS_NAME!="default")}
			   <link href="templates/default/css/{$CSS_NAME}.css" rel="stylesheet" type="text/css">
		{else}
			   <link href="templates/default/css/main.css" rel="stylesheet" type="text/css">
			   <link href="templates/default/css/menu.css" rel="stylesheet" type="text/css">
			   <link href="templates/default/css/style-def.css" rel="stylesheet" type="text/css">
		{/if}
         <script type="text/javascript" src="./javascript/jquery/jquery-1.2.6.min.js"></script>
</HEAD>

<BODY leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">

{literal}
<script LANGUAGE="JavaScript">
<!--
	function test()
	{
		if(document.form.pr_login.value=="" || document.form.pr_password.value=="") {
			alert("You must enter an user and a password!" + document.form.pr_password.value);
			return false;
		} else {
			return true;
		}
	}
-->
</script>

{/literal}

	<form name="form" method="POST" action="userinfo.php" onsubmit="return test()">
	<input type="hidden" name="done" value="submit_log">


    <div id="login-wrapper" class="login-border-up">
	<div class="login-border-down">
	<div class="login-border-center">
	<center>
	<table border="0" cellpadding="3" cellspacing="12">
	<tr>
		<td class="login-title" colspan="2">
			 {_("AUTHENTICATION")}
		</td>
	</tr>
	<tr>
		<td ><img src="templates/{$SKIN_NAME}/images/kicons/lock_bg.png"></td>
		<td align="center" style="padding-right: 10px">
			<table width="90%">
			<tr align="center">
				<td align="left"><font size="2" face="Arial, Helvetica, Sans-Serif"><b>{_("User")}:</b></font></td>
				<td><input class="form_input_text" type="text" name="pr_login" size="15" value="{$username}"></td>
			</tr>
			<tr align="center">
				<td align="left"><font face="Arial, Helvetica, Sans-Serif" size="2"><b>{_("Password")}:</b></font></td>
				<td><input class="form_input_text" type="password" name="pr_password" size="15" value="{$password}"></td>
			</tr>
			</tr><tr >
                <td colspan="2"> &nbsp;</td>
            </tr>
			<tr align="right" >
                <td>
                    <select name="ui_language"  id="ui_language" class="icon-menu form_input_select">
						<option value="english" {if LANGUAGE === "english"}selected="selected"{/if}>🇬🇧 {_("English")}</option>
						<option value="spanish" {if LANGUAGE === "spanish"}selected="selected"{/if}>🇪🇸 {_("Spanish")}</option>
						<option value="french" {if LANGUAGE === "french"}selected="selected"{/if}>🇫🇷 {_("French")}</option>
						<option value="german" {if LANGUAGE === "german"}selected="selected"{/if}>🇩🇪 {_("German")}</option>
						<option value="portuguese" {if LANGUAGE === "portuguese"}selected="selected"{/if}>🇵🇹 {_("Portuguese")}</option>
						<option value="brazilian" {if LANGUAGE === "brazilian"}selected="selected"{/if}>🇧🇷 {_("Brazilian")}</option>
						<option value="italian" {if LANGUAGE === "italian"}selected="selected"{/if}>🇮🇹 {_("Italian")}</option>
						<option value="chinese" {if LANGUAGE === "chinese"}selected="selected"{/if}>🇨🇳 {_("Chinese")}</option>
						<option value="romanian" {if LANGUAGE === "romanian"}selected="selected"{/if}>🇷🇴 {_("Romanian")}</option>
						<option value="polish" {if LANGUAGE === "polish"}selected="selected"{/if}>🇵🇱 {_("Polish")}</option>
						<option value="russian" {if LANGUAGE === "russian"}selected="selected"{/if}>🇷🇺 {_("Russian")}</option>
						<option value="turkish" {if LANGUAGE === "turkish"}selected="selected"{/if}>🇹🇷 {_("Turkish")}</option>
						<option value="urdu" {if LANGUAGE === "urdu"}selected="selected"{/if}>🇵🇰 {_("Urdu")}</option>
						<option value="ukrainian" {if LANGUAGE === "ukrainian"}selected="selected"{/if}>🇺🇦 {_("Ukrainian")}</option>
						<option value="greek" {if LANGUAGE === "greek"}selected="selected"{/if}>🇬🇷 {_("Greek")}</option>
						<option value="indonesian" {if LANGUAGE === "indonesian"}selected="selected"{/if}>🇮🇩 {_("Indonesian")}</option>
                    </select>
                </td>
				<td><input type="submit" name="submit" value="{_("LOGIN")}" class="form_input_button"></td>
			</tr>
			</table>
		</td>
	</tr>
	<tr align="center">
		<td colspan="2"><font class="fontstyle_007">{_("Forgot your password ?")} <a href="forgotpassword.php">{_("Click here")}</a></font>.</td>
    </tr>
	<tr align="center">
        <td colspan="2"><font class="fontstyle_007">{_("To sign up")} <a href="signup.php">{_("Click here")}</a></font>.</td>
    </tr>
  	</table>
  	</center>
  	</div>
  	</div>

  	<div style="color:#BC2222;font-family:Arial,Helvetica,sans-serif;font-size:11px;font-weight:bold;padding-left:10px;" >
  	{if ($error == 1)}
		{_("AUTHENTICATION REFUSED : please check your user/password!")}
    {elseif ($error==2)}
		{_("INACTIVE ACCOUNT : Your account need to be activated!")}
    {elseif ($error==3)}
		{_("BLOCKED ACCOUNT : Please contact the administrator!")}
    {elseif ($error==4)}
		{_("NEW ACCOUNT : Your account has not been validate yet!")}
    {/if}
    </div>
    <div id="footer_index"><div style=" border: solid 1px #F4F4F4; text-align:center;">{$COPYRIGHT}</div></div>

  	</div>
	</form>
{literal}
<script>
$(function() {
	$("#ui_language").change(e => self.location.href = "index.php?ui_language=" + $("#ui_language option:selected").val());
});
</script>
{/literal}
