<?php
$web = ADD::web();
if(!ADD::sys()->hasState('no_modal')):
?>
<style>
	form#modal-form {
		margin:0;
		padding:0;
	}
</style>
<form id="modal-form">

<?php 
include ADD::sys()->getWebAppPath("views/alerts.php");
endif;
echo $web->getContent('main'); 
?>

<?php if(!ADD::sys()->hasState('no_modal')): ?>
</form>
<?php endif; ?>
<script type="text/javascript">
<?php
$jsList = $web->getJavascript();

if (isset($jsList['document'])):
echo "ADD.Form.onModalReady['".ADD::sys()->getParam('pim')."']=function() {";
	foreach($jsList['document'] as $jsItem) echo $jsItem."\n";
echo "};";
endif;
echo "</script>";
?>
