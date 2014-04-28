<?php

include('config_paths.php');

echo "<div id=\"wrapper\">";
echo "<h1>Create an Account</h1>";

echo <<<EOT

The code editor on PhysGL is in "read-only" mode for anonymous users.  Using
PhysGL with your own account allows you to create graphics programs, which are
saved "in the cloud" (i.e. on our server) for future use and access.  Each of
your programs also has a "share" link, allowing you to share your programs with
others.

EOT;
echo form_open("welcome/incoming_account");

$publickey = "6LeTGNsSAAAAACOCCdVsTP2P3YI-8U-OGT-KxgXf"; // you got this from the signup page

//echo validation_errors();

echo "<h2>1.  Create a user name</h2>";
echo "Your user name must be your valid email address.<p/>";
echo "<input id=account_input type=text name=email value=\"" . set_value('email') . "\" size=30>";
echo form_error('email');

echo "<p/>";

echo "<h2>2.  Create a good password</h2>";
echo "Your password must have at least 4 characters. Your password can not contain your username or spaces.<p/>";
echo "<input id=account_input type=password name=password size=30>";
echo form_error('password');

echo "<p/>";

echo "<h2>2.  Re-type your password</h2>";
echo "<input id=account_input type=password name=password_confirm size=30>";
echo form_error('password_confirm');

echo "<p/>";

echo "<h2>3.  Please prove that you are human</h2>";

if (!empty($captcha_error))
	echo "<div id=\"error_message\">$captcha_error</div><p/>";

echo recaptcha_get_html($publickey);
echo "<p/>";
echo "<input type=submit value=\"Create account\">  ";
echo anchor("welcome/","Cancel");

echo form_close();

echo "</div>";

?>


