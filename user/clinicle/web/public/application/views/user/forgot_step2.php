<?php include ADD::sys()->getWebAppPath("views/header.php"); ?>
<div class="container">
<div class="row">
	<div class="span12">
		
<p class="lead">Forgotten password (step 2)</p>
<?php if (isset($message)): ?>
<div class="alert alert-error"><?php echo $message;?></div>
<?php endif ?>
<?php if (isset($info)) echo "<p class='text text-error' style='font-size:120%'><b>$info</b></p>"; ?>
<?php echo form_open("user/forgotten2");?>
<input type="hidden" autocomplete="off" id="identity" name="identity" value="<?php echo set_value("identity"); ?>"/>
<label>Answer your chosen security question, then click 'Reset password' to be emailed a temporary password</label>
<div class="disabled-select"><?php echo $reset_question->question_text ?></div>
  <input type="password" autocomplete="off" id="reset_answer" name="reset_answer" value=""/>
 <br/>
<button class="btn btn-primary" name="reset" value="1"><i class="icon-lock icon-white"></i>&nbsp;Reset password</button>
  
<?php echo form_close();?>

</div>
</div>
</div>
<?php include ADD::sys()->getWebAppPath("views/footer.php"); ?>
