<?php
$web = ADD::web();
if(!ADD::sys()->hasState('no_modal')):
?>

<?php 
include ADD::sys()->getWebAppPath("views/alerts.php");
endif;
echo $web->getContent('main'); 
?>

<?php if(!ADD::sys()->hasState('no_modal')): ?>

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
