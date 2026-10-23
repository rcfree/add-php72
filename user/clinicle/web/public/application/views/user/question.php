<?php include ADD::sys()->getWebAppPath("views/header.php"); ?>
<div class="container">
<div class="row">
	<div class="span12">
		
<p class="lead">Password reset question</p>
<?php if ($message): ?>
<div class="alert alert-error"><?php echo $message;?></div>
<?php endif ?>
<?php if (isset($info)) echo "<p class='text text-error' style='font-size:120%'><b>$info</b></p>"; ?>
<?php echo form_open("user/question");?>
  
  <label>Password reset question</label>
  <p class="muted">Choosing a question and answer will allow you to reset your password without contacting the administrator.</p>

  <select name="reset_question" style="width:400px">
      <option value="">- choose a question -</option>
      <?php foreach($questions as $question): ?>
      <option value="<?php echo $question->id; ?>"
        <?php if (set_value('reset_question') == $question->id): ?>selected="selected"<?php endif; ?>       
      >
      <?php echo $question->question_text; ?>
      </option>
      <?php endforeach; ?>
  </select>
  
  <label>Enter an answer to this question</label>
  
  <input type="password" autocomplete="off" id="reset_answer" name="reset_answer" value="<?php echo set_value('reset_answer'); ?>"/>
  <p class="text-info">The reset answer should be between 6 and 30 characters.</p>

  <p><button class="btn btn-primary">Update answer</button></p>
  
<?php echo form_close();?>

</div>
</div>
</div>
<?php include ADD::sys()->getWebAppPath("views/footer.php"); ?>