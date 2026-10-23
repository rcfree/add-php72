<?php include ADD::sys()->getWebAppPath("views/header.php"); ?>
<div class="container">
<div class="row">
	<div class="span12">
		
<p class="lead"><?php echo lang('login_heading');?></p>
<?php if (isset($notify)): ?><span class="label label-warning"><b>ADVANCE WARNING</b><br/><?php echo $notify; ?></span><?php endif ?>

<?php echo form_open();?>
<?php if ($message): ?>
<div class="alert alert-block alert-error"><?php echo $message;?></div>
<p class="text-info" style="font-size:120%"><b><?php echo lang('login_subheading');?></b></p>

<?php endif ?>
  <p>
    <?php echo lang('login_identity_label', 'identity');?>
    <?php echo form_input($identity);?>
  </p>

  <p>
    <?php echo lang('login_password_label', 'password');?>
    <?php echo form_input($password);?>
  </p>
  <?php echo form_input($override);?>
  <p><button type="submit" class="btn btn-primary"><?php echo lang('login_submit_btn') ?></button></p>
<input type="hidden" value="<?php echo $this->input->get('dest') ?>" name="dest"/>
<?php echo form_close();?>
<div class="alert alert-info">
If you do not have access to ADD and would like to get it please email <a href="mailto:robert.free@uhl-tr.nhs.uk">Dr. Rob Free</a> (Respiratory BRU Informatics Manager) with your request.
</div>

<!--
<p><a href="forgot_password"><?php echo lang('login_forgot_password');?></a></p>
-->
</div>
<?php include ADD::sys()->getWebAppPath("views/footer.php"); ?>
</div>
</div>

