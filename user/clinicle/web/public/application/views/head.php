<!DOCTYPE html>
<?php 
$add = ADD::sys();
$web = ADD::web();
$site_path = $add->config('site_path');
$siteName = $add->getConfig()->getSetting('site_name','ADD');
$am = $add->getAccessManager();
$ts = ADD_Field_Util::timestamp();
?>
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=9; IE=8; IE=7; IE=EDGE" />
<?php if ($add->hasState('autorefresh')): ?>
<meta http-equiv="refresh" content="<?php echo $add->hasState(ADD_System::AUTOREFRESH) ?>" />
<?php endif ?>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=no" />
<meta name="apple-mobile-web-app-capable"
      content="yes" />
<meta name="apple-mobile-web-app-status-bar-style"
      content="default" />
<link rel="apple-touch-startup-image"
      href="./startup.png" />
<title><?php echo $siteName; if (!$add->config('live')) echo " [".$add->getProfile() ."]" ?> - <?php echo isset($title) ? $title : $web->getTitle(); ?></title>
<link rel="shortcut icon"
 href="<?php echo $site_path ?>/favicon.ico" />
 
<!-- Bootstrap  -->
<link href="<?php echo $site_path ?>/css/clinicle.min.css?<?php echo $ts; ?>" rel="stylesheet">
<link href="<?php echo $site_path ?>/css/font-awesome.min.css?<?php echo $ts; ?>" rel="stylesheet">
<script type="text/javascript" src="<?php echo $site_path ?>/js/clinicle.min.js?<?php echo $ts; ?>"></script>
    
<!--[if lt IE 9]>
    <script src="<?php echo $site_path ?>/js/html5shiv.js"></script>   
<![endif]-->
<?php
$javascriptFiles = $web->getJavascriptFiles();
foreach($javascriptFiles as $javascriptFile):
?>
        <script type="text/javascript" src="<?php echo $javascriptFile ?>?<?php echo ADD_Field_Util::timestamp(); ?>"></script>
<?php endforeach; ?>
<script type="text/javascript">
<?php
$jsList = $web->getJavascript();

echo "jQuery(document).ready(function() {\n";
if (isset($jsList['document'])):
	foreach($jsList['document'] as $jsItem) echo $jsItem."\n";
endif;

if ($add -> config('auto_logout') && $am -> isLoggedIn()) {
   echo "ADD.User.AutoLogout.setup(" . $add -> config('auto_logout') . ");\n";
}
echo "});";


if (isset($jsList['head'])) foreach($jsList['head'] as $jsItem) echo $jsItem."\n";

?>
</script>
<?php if (!$add->config('live') && $add->getProfile()!="uhl-live"): ?>
<style>
.navbar-inner {
	background:none;
	filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='/images/header-gradient-test.png',sizingMethod='crop');
	background:url('/images/header-gradient-test.png') repeat-x left top
}
</style>
<?php endif ?>
</head>