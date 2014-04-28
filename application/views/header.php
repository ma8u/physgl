<?php
include('config_paths.php');


PRINT<<<END
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>PhysGL: Easy 3D graphics and animation in the cloud</title>
	<link rel="stylesheet" type="text/css" href="$base_css"/>

	<script src="$threejs_path"></script>
	<script src="$graphicslib_path"></script>
	<script src="$functionlib_path"></script>
	<script src="$wrappers_path"></script>
	<script src="$font_path"></script>
	<script src="$jspreprocess_path"></script>
	<script src="$toprefix_path"></script>
	<script src="$errorcheck_path"></script>
	
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
	
	<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>
  <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
  <script src="$dialog_extend"></script>
   <script type="text/javascript" src="https://www.google.com/jsapi"></script>
  
   
<script src="$codemirror_path/codemirror.js"></script>
<link rel="stylesheet" href="$codemirror_path/codemirror.css">
<script src="$codemirror_path/lua.js"></script>
<script type="text/javascript" src="http://cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-AMS-MML_HTMLorMML"></script>
<script type="text/javascript" src="https://www.dropbox.com/static/api/1/dropins.js" id="dropboxjs" data-app-key="n76hc0aaeft0sui"></script>

<script type="text/x-mathjax-config">
  MathJax.Hub.Config({
    extensions: ["tex2jax.js"],
    jax: ["input/TeX", "output/HTML-CSS"],
    tex2jax: {
      inlineMath: [ ['$','$'] ],
      displayMath: [ ['$$','$$']],
      processEscapes: true
    },
    "HTML-CSS": { availableFonts: ["TeX"] }
  });
</script>

<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-37461438-1']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
</head>

<body>
 <div id="top-bar">
 <img src="$base_url/logo.png">
 <span id="login_line">

END;

if (empty($home_link))
	$home_link = false;

if ($login_form == 'yes')
{
	echo form_open("welcome/authenticate");
	echo "Username: ";
	echo "<input type=text name=username>  ";
	echo "Password: ";
	echo "<input type=password name=password>  ";
	echo "<input type=submit value=\"Sign in\">";
	echo "  " . anchor("welcome/create_account","Create account",Array("id" => "nav"));
}

if ($home_link === true)
	{
		echo anchor("welcome/","Home",Array("id" => "nav"));
	}
if ($login_form == 'no')
	{
		echo " ($username) ";
		echo anchor("welcome/logout","Logout",Array("id" => "nav"));
	}
?>
 | <a href=http://www.github.com/tbensky/physgl target="_blank" id="nav">Download</a>
 | <a href=https://docs.google.com/document/d/1nkw-9IEpItmFiob5mt_Z3ZEc6cm6MHx40X0s1fcgTQc/edit target="_blank" id="nav">Docs</a>
 | <a href=http://www.physgl.org/tutorial target="_blank" id="nav">Tutorial</a>
 | <?php echo anchor("welcome/about","About",Array("id" => "nav")); ?>
 | <a href="https://groups.google.com/forum/#!forum/physgl-site" id="nav">Group</a>
<?php
	if ($login_form == 'yes')
		echo form_close();
?>
</span>
</span>
</div>
<p/>
<div id="page_outer"><div id="page_inner">

