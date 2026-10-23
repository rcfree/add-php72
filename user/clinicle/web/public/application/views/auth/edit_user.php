<?php include ADD::sys()->getWebAppPath("views/header.php"); ?>
 <div class="container">

<h1><?php echo lang('edit_user_heading');?></h1>
<p><?php echo lang('edit_user_subheading');?></p>
<?php if ($message): ?>
<div class="alert alert-error"><?php echo $message;?></div>
<?php endif ?>
<?php echo form_open(uri_string());?>

      <p>
            <label>User name:</label>
            <?php echo form_input($username);?>
      </p>
     
      <p>
            <?php echo lang('edit_user_fname_label', 'first_name');?> 
            <?php echo form_input($first_name);?>
      </p>

      <p>
            <?php echo lang('edit_user_lname_label', 'last_name');?>
            <?php echo form_input($last_name);?>
      </p>
	  
	   
      <p>
            <?php echo lang('create_user_email_label', 'email');?>
            <?php echo form_input($email);?>
      </p>
      
<label>Roles</label>
<?php foreach($roles as $label => $role): ?>
<?php echo form_checkbox($role); ?>&nbsp;
<?php echo $label; ?>
<br />
<?php endforeach; ?>
<br/>
      <?php echo form_hidden('id', $user->id);?>
      <?php echo form_hidden($csrf); ?>

      <p><?php echo form_button(array("type"=>'submit',"content"=>"<i class='icon-ok icon-white'></i>&nbsp;".lang('edit_user_submit_btn'),"name"=>"submit","class"=>"btn btn-primary"));?>
            <?php echo form_button($reset_password);?>
      </p>
<?php echo form_close();?>
<?php include ADD::sys()->getWebAppPath("views/footer.php"); ?>
