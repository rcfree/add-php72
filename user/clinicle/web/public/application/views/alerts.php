<?php
function draw_messages($type,$items) {
	$html = "";
	if (!$items) return "";
	if (count($items)==0) return "";
	$html.="<div class='alert alert-{$type}'>";
	$html.='<button type="button" class="close" data-dismiss="alert">&times;</button>';
	$html.="<ul>";
	foreach($items as $item) {
		$html.="<li>".$item."</li>";
	}
	$html.="</ul>";
	$html.="</div>";
	return $html;
}
?>

<div id="messages">
<?php
echo draw_messages("error",$web->getErrors());
echo draw_messages("warning",$web->getWarnings());
echo draw_messages("success",$web->getMessages());
?>
</div>