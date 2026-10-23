<?php

$add = ADD::sys();
$web = ADD::web();
$site_path = $add->config('site_path');
if(ADD::web()->getErrors()):
?>
<div class="alert alert-error">
	<ul>
<?php foreach(ADD::web()->getErrors() as $error): ?>
		<li><?php echo $error; ?></li>
<?php endforeach; ?>
	</ul>
</div>
<?php
	exit;
endif;
include ADD::web()->getThemeView("header.php",true);

?>

<div class="container-fluid">
<div class="row-fluid">
<div class="span3" ng-if="isPatientSelected()">
	<div ng-show="isPanelLoading('side')">Working.. <img src="/module/clinicle/client/images/ajax-loader.gif"></img></div>
	<div id="side-panel"></div>
</div>
<div ng-class="isPatientSelected() ? 'span9' : 'span12'">
		<div ng-show="isPanelLoading('main')">Working.. <img src="/module/clinicle/client/images/ajax-loader.gif"></img></div>
		<div id="main-panel"></div>		
</div>
<?php include ADD::sys()->getWebAppPath("views/footer.php"); ?>