<?php
include getenv('ADD_PATH')."/core/web/application/views/header.php";
 ?>
 <div class="container">

<div class="page-header">
<h3><?php echo lang('forgot_password_heading');?></h3>
<p><?php echo sprintf(lang('forgot_password_subheading'), $identity_label);?></p>

<div class="alert alert-error"><?php echo $message;?></div>

<?php echo form_open("auth/forgot_password");?>
      <p>
      	<label for="email"><?php echo sprintf(lang('forgot_password_email_label'), $identity_label);?></label> <br />
      	<?php echo form_input($email);?>
      </p>

      <p><?php echo form_submit('submit', lang('forgot_password_submit_btn'));?></p>

<?php echo form_close();?>
</div>
<?php
    include getenv('ADD_PATH')."/core/web/application/views/footer.php";
 ?>