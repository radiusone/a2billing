<!DOCTYPE html>
<html>
<head>
	<link rel="shortcut icon" href="data:image/gif;base64,R0lGODlhIAAgAOMIAA9ztTSKwUef0Gyw2I/D4rLW6s7m8/X6/AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEKAAgALAAAAAAgACAAAAT+EB1Dhrg46y0GMQeCGBZnnh1Ioiw3HEQra0U5z7ZLFDHm5RtgZleobQgVk/BC5C13y2cxeYJyAIFhcakZbDOBAGCc9Ro7XCbvIsaO34WVgJj2GgRv9ztAwQbichphfwcDeW1jAgaIBQcHeG9mbQQHBYd5O3mNj5qOhgAvBpdvFZ2PARYAm5+hiIgABK+bArOrY5Sie3kDso60vrawE6NkvY+1nqQExHlwwM+FzK/Nwou4cxOQ1Hrbxd5+3WTdqNPh1OWgKp/h2syYjpW8zaix2+iJIPAGRYDL4+YA5uSbUOPeN4CDDObpALDhtg8KHTYTEOKOxIaKJEyIkbCjx48E2CREAAA7"/>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<link href="../lib/bootstrap/css/bootstrap.css" rel="stylesheet" type="text/css"/>
	<script src="../lib/bootstrap/js/bootstrap.js"></script>
	<script src="./javascript/jquery/jquery.js"></script>
	<title>{$CCMAINTITLE}</title>
</head>
<body>
	<form method="post" action="PP_intro.php">
		<input type="hidden" name="done" value="submit_log"/>
		<div class="modal show d-block" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="authTitle" aria-hidden="false">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content">
					<div class="modal-header">
						<h3 class="modal-title" id="authTitle">{_("Authentication")}</h3>
					</div>
					<div class="modal-body">
						<div class="container-fluid">
							{if !empty($error)}
							<div class="row pb-3 bg-danger">
								{if $error == 1}
									{_("AUTHENTICATION REFUSED, please check your user/password!")}
								{elseif $error == 2}
									{_("INACTIVE ACCOUNT, Please activate your account!")}
								{elseif $error == 3}
									{_("BLOCKED ACCOUNT, Please contact the administrator!")}
								{/if}
							</div>
							{/if}
							<div class="row pb-3">
								<label class="col-4 col-form-label" for="pr_login">{_("User")}</label>
								<div class="col">
									<input type="text" name="pr_login" id="pr_login" autofocus="autofocus" autocomplete="on" class="form-control"/>
								</div>
							</div>
							<div class="row pb-3">
								<label class="col-4 col-form-label" for="pr_password">{_("Password")}</label>
								<div class="col">
									<input type="password" name="pr_password" id="pr_password" class="form-control"/>
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer justify-content-between">
						<select name="ui_language" id="ui_language" class="form-select w-50">
							<option value="english" {if LANGUAGE === "english"}selected="selected"{/if}>🇬🇧 {_("English")}</option>
							<option value="brazilian" {if LANGUAGE === "brazilian"}selected="selected"{/if}>🇧🇷 {_("Brazilian")}</option>
							<option value="romanian" {if LANGUAGE === "romanian"}selected="selected"{/if}>🇷🇴 {_("Romanian")}</option>
							<option value="french" {if LANGUAGE === "french"}selected="selected"{/if}>🇫🇷 {_("French")}</option>
							<option value="spanish" {if LANGUAGE === "spanish"}selected="selected"{/if}>🇪🇸 {_("Spanish")}</option>
							<option value="greek" {if LANGUAGE === "greek"}selected="selected"{/if}>🇬🇷 {_("Greek")}</option>
							<option value="italian" {if LANGUAGE === "italian"}selected="selected"{/if}>🇮🇹 {_("Italian")}</option>
							<option value="chinese" {if LANGUAGE === "chinese"}selected="selected"{/if}>🇨🇳 {_("Chinese")}</option>
						</select>
						<button type="submit" class="btn btn-primary">{_("Log In")}</button>
					</div>
				</div>
			</div>
		</div>
	</form>
	<script>
		$("#ui_language").live("change", function () {
			self.location.href = "?ui_language=" + $("#ui_language option:selected").val();
		});
	</script>
</body>
</html>