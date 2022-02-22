<!DOCTYPE html>
<html>
<head>
	<link rel="shortcut icon" href="data:image/gif;base64,R0lGODlhIAAgAOMIAA9ztTSKwUef0Gyw2I/D4rLW6s7m8/X6/AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEKAAgALAAAAAAgACAAAAT+EB1Dhrg46y0GMQeCGBZnnh1Ioiw3HEQra0U5z7ZLFDHm5RtgZleobQgVk/BC5C13y2cxeYJyAIFhcakZbDOBAGCc9Ro7XCbvIsaO34WVgJj2GgRv9ztAwQbichphfwcDeW1jAgaIBQcHeG9mbQQHBYd5O3mNj5qOhgAvBpdvFZ2PARYAm5+hiIgABK+bArOrY5Sie3kDso60vrawE6NkvY+1nqQExHlwwM+FzK/Nwou4cxOQ1Hrbxd5+3WTdqNPh1OWgKp/h2syYjpW8zaix2+iJIPAGRYDL4+YA5uSbUOPeN4CDDObpALDhtg8KHTYTEOKOxIaKJEyIkbCjx48E2CREAAA7"/>
	<title>..:: {$CCMAINTITLE} ::..</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<link href="../lib/bootstrap/css/bootstrap.css" rel="stylesheet" type="text/css"/>
	<link href="templates/{$SKIN_NAME}/css/invoice.css" rel="stylesheet" type="text/css"/>
	<link href="templates/{$SKIN_NAME}/css/receipt.css" rel="stylesheet" type="text/css"/>
	<link href="./javascript/jquery/jquery.wysiwyg.css" rel="stylesheet" type="text/css"/>
	{if ($popupwindow != 0)}
		<link href="templates/{$SKIN_NAME}/css/popup.css" rel="stylesheet" type="text/css"/>
 	{/if}
	<script>
		var IMAGE_PATH = "templates/{$SKIN_NAME}/images/";
	</script>
	<script src="../lib/bootstrap/js/bootstrap.js"></script>
	<script src="./javascript/jquery/jquery.js"></script>
	<script src="./javascript/jquery/jquery.debug.js"></script>
	<script src="./javascript/jquery/ilogger.js"></script>
	<script src="./javascript/jquery/handler_jquery.js"></script>
	<script src="./javascript/jquery/jquery.wysiwyg.js"></script>
	<script src="./javascript/jquery/jquery.simplemodal.js"></script>
    <script src="./javascript/jquery/jquery.flot.pack.js"></script>
	<script src="./javascript/misc.js"></script>

	<style>
		.bi {
			vertical-align: -.125em;
			pointer-events: none;
			fill: currentColor;
		}

		.dropdown-toggle { outline: 0; }

		.nav-flush .nav-link {
			border-radius: 0;
		}

		.btn-toggle {
			display: inline-flex;
			align-items: center;
			padding: .25rem .5rem;
			font-weight: 600;
			color: rgba(0, 0, 0, .65);
			background-color: transparent;
			border: 0;
		}
		.btn-toggle:hover,
		.btn-toggle:focus {
			color: rgba(0, 0, 0, .85);
			background-color: #d2f4ea;
		}

		.btn-toggle::before {
			width: 1.25em;
			line-height: 0;
			content: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='rgba%280,0,0,.5%29' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M5 14l6-6-6-6'/%3e%3c/svg%3e");
			transition: transform .35s ease;
			transform-origin: .5em 50%;
		}

		.btn-toggle[aria-expanded="true"] {
			color: rgba(0, 0, 0, .85);
		}
		.btn-toggle[aria-expanded="true"]::before {
			transform: rotate(90deg);
		}

		.btn-toggle-nav a {
			display: inline-flex;
			padding: .1875rem .5rem;
			margin-top: .125rem;
			margin-left: 1.25rem;
			text-decoration: none;
		}
		.btn-toggle-nav a:hover,
		.btn-toggle-nav a:focus {
			background-color: #d2f4ea;
		}

		form label {
			text-transform: capitalize;
		}
	</style>
</head>
<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
	<div id="page-wrap">
		<div id="inside">
