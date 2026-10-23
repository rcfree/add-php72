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
include ADD::sys()->getWebAppPath("views/header.php");

?>

<div class="container">  
<div class="row">
<div class="span3" style="padding-top:4px" id="side-content">     
</div>
<div ng-class="isPatientSelected() ? 'span9' : 'span12'">
		<div ng-show="isPanelLoading('main')==true">Loading.. <img src="/module/clinicle/client/images/ajax-loader.gif"></img></div>
		<div id="main-content"></div>		
</div>
<?php include ADD::sys()->getWebAppPath("views/footer.php"); ?>