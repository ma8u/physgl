<?php
include('config_paths.php');

PRINT<<<END
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>PhysGL: Physics Graphics Language</title>
	<link rel="stylesheet" type="text/css" href="$base_css"/>
	<script src="$jquery_url"></script>
	<script src="$threejs_path"></script>
	<script src="$graphicslib_path"></script>
	<script src="$functionlib_path"></script>
	<script src="$wrappers_path"></script>
	<script src="$font_path"></script>
	<script src="$jspreprocess_path"></script>
	<script src="$toprefix_path"></script>
	<script src="$errorcheck_path"></script>
	<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>
  <script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>
   <script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script src="$codemirror_path/codemirror.js"></script>
<link rel="stylesheet" href="$codemirror_path/codemirror.css">
<script src="$codemirror_path/lua.js"></script>
</head>

<body>
 <div id="top-bar">
 <span><a href="$home_url" id="logo" class="logo">PhysGL</a></span>
 <span id="login_line">

END;
if ($login_form == 'yes')
{
	echo form_open("welcome/authenticate");
	echo "Username: ";
	echo "<input type=text name=username>  ";
	echo "Password: ";
	echo "<input type=password name=password>  ";
	echo "<input type=submit value=\"Sign in\">";
	echo "  " . anchor("welcome/create_account","Create account");
}

if ($login_form == 'no')
	{
		echo " ($username) ";
		echo anchor("welcome/logout","Logout");
	}
?>
 | <a href=http://www.github.com/tbensky/physgl>Download</a>
 | <a href=https://docs.google.com/document/d/1nkw-9IEpItmFiob5mt_Z3ZEc6cm6MHx40X0s1fcgTQc/edit>Docs</a>
<?php
	if ($login_form == 'yes')
		echo form_close();
?>
</span>
</span>
</div>
<p/>
<div id="page_outer"><div id="page_inner">

