<?php
if (isset($header)) echo $header;
$add = ADD::sys();
$site_path = $add->config('site_path');
?>

<div class="container">  
<div class="row">
	<div class="span12" style="padding-top:4px">
	<div class="tool-block tool-important">
		<div class="tool-block-title">MAINTENANCE</div>
			<div class="tool-block-content">
			<?php echo $message ?>
			<br/>
			Please contact <a href="mailto:rcf8@le.ac.uk">Rob Free</a> if you would like more information.
			</div>
		</div>
	</div>
</div>
</div>
<?php include ADD::sys()->getWebAppPath("views/footer.php"); ?>