<?php include ADD::sys()->getWebAppPath("views/header.php"); ?>
 <div class="container">
<h1><?php echo lang('deactivate_heading');?></h1>
<p><?php echo sprintf(lang('deactivate_subheading'), $user->username);?></p>

<?php echo form_open("auth/deactivate/".$user->id);?>

 
  	Yes <input type="radio" name="confirm" value="yes" checked="checked" />
    No <input type="radio" name="confirm" value="no" />


  <?php echo form_hidden($csrf); ?>
  <?php echo form_hidden(array('id'=>$user->id)); ?>

  <p><?php echo form_submit('submit', lang('deactivate_submit_btn'));?></p>

<?php echo form_close();?>
</div>
<?php include ADD::sys()->getWebAppPath("views/footer.php"); ?>