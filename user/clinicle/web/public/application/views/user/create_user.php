<?php include ADD::sys()->getWebAppPath("views/header.php"); ?>
 <div class="container">

<div class="page-header">
<h3><?php echo lang('create_user_heading');?></h3>
<p><?php echo lang('create_user_subheading');?></p>
<?php if ($message): ?>
<div class="alert alert-error"><?php echo $message;?></div>
<?php endif ?>
<?php echo form_open("auth/create_user");?>
      <p>
            <label>Username:</label>
            <?php echo form_input($username);?>
      </p>
      
      <p>
            <?php echo lang('create_user_fname_label', 'first_name');?>
            <?php echo form_input($first_name);?>
      </p>

      <p>
            <?php echo lang('create_user_lname_label', 'last_name');?>
            <?php echo form_input($last_name);?>
      </p>

      <p>
            <?php echo lang('create_user_email_label', 'email');?>
            <?php echo form_input($email);?>
      </p>
      
      <p>
            <?php echo lang('create_user_email_msg', 'email_msg');?>
            <?php echo form_textarea($email_msg);?>
      </p>
<label>Roles</label>
<?php foreach($roles as $label => $role): ?>
<?php echo form_checkbox($role); ?>&nbsp;
<?php echo $label; ?>
<br />
<?php endforeach; ?>
<br/>
      <p><?php echo form_button(array("type"=>'submit',"content"=>"<i class='icon-ok icon-white'></i>&nbsp;".lang('create_user_submit_btn'),"name"=>"submit","class"=>"btn btn-primary"));?></p>

<?php echo form_close();?>
</div>
<?php include ADD::sys()->getWebAppPath("views/footer.php"); ?>
 