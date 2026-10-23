<?php include ADD::sys()->getWebAppPath("views/header.php"); ?>
<div class="container">
<div class="row">
	<div class="span12">
		
<p class="lead">Change password</p>
<?php if ($message): ?>
<div class="alert alert-error"><?php echo $message;?></div>
<?php endif ?>
<?php if (isset($info)) echo "<p class='text text-error' style='font-size:120%'><b>$info</b></p>"; ?>
<?php echo form_open("user/change");?>

  <label>Enter your current password</label>
  <input type="password" autocomplete="off" id="current_password" name="current_password" value="<?php echo set_value('current_password'); ?>"/>
<label>Enter a new password</label>
  <input type="password" autocomplete="off" id="new_password" name="new_password" value="<?php echo set_value('new_password'); ?>"/>
<p class="text-info">The new password should be at least 8 characters and contain 1 number and 1 capital letter.</p>

  <label>Confirm your new password</label>
  <input type="password" autocomplete="off" id="confirm_password" name="confirm_password" value="<?php echo set_value('confirm_password'); ?>"/>
  <p><button class="btn btn-primary">Change password</button></p>
  
<?php echo form_close();?>

</div>
</div>
</div>
<?php include ADD::sys()->getWebAppPath("views/footer.php"); ?>