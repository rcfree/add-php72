</div>
<hr/>

<footer>

    <p style="text-align:center"><?php echo ADD::sys()->config("site_name")." ".ADD::sys()->getInstalledVersion() ?> &copy; 2016 Leicester Institute for Lung Health</p>
	
    <?php if($this->ion_auth->is_admin()): ?>
    	<p style="float:left" class="text-info">
	
    		<b>Database:</b> <?php echo ADD::db()->getDSN() ?><br/>
    		<b>ADD path:</b> <?php echo ADD::sys()->getPath() . " as ". ADD::sys()->getProfile() ?>
    		</p>
    <?php endif ?>
			<p class="muted pull-right">Powered by</br><img style="width:80px" src="/images/clinicle-logo.png"></img>
</p>   



</footer>
<?php if (ADD::sys()->config('debug')): ?>
<div class="debug">
	<?php echo ADD::outputDebug(); ?>
</div>
<?php endif; ?>
</body>
</html>
