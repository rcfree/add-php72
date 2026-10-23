<?php include ADD::sys()->getWebAppPath("views/header.php"); ?>
<div class="container">
<div class="row">
	<div class="span12">
		
<p class="lead">Forgotten password (step 1)</p>
<?php if ($message): ?>
<div class="alert alert-error"><?php echo $message;?></div>
<?php endif ?>
<?php if (isset($info)) echo "<p class='text text-error' style='font-size:120%'><b>$info</b></p>"; ?>
<?php echo form_open("user/forgotten");?>

<label>Enter your username then click 'Next'</label>
  <input type="text" autocomplete="off" id="identity" name="identity" value="<?php echo set_value('identity'); ?>"/>

<br/>
<button class="btn btn-primary" name="next" value="1"><i class="icon-forward icon-white"></i>&nbsp;Next</button>
  
<?php echo form_close();?>

</div>
</div>
</div>
<?php include ADD::sys()->getWebAppPath("views/footer.php"); ?>
