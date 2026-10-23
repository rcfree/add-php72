<?php include ADD::sys()->getWebAppPath("views/header.php"); ?>
<script>
$(document).ready(function() {
	ADD.init();
});
</script>
<div class="container">
<div class="page-header">
<h3>
Roles
<?php if ($currRole): ?><small>(<?php echo $currRole->name ?>)</small><?php endif ?>
</h3>
</div>
<?php if (!empty($message)): ?>
<div class="alert alert-block alert-error"><?php echo $message;?></div>
<?php endif ?>
<form action="/user/create_role" class="form-inline">
<label>Add new role</label>
<input type="text" value="" name="role_name"/>
<button class="btn" name="create"><i class="icon-plus"></i>&nbsp;Create</button>
</fieldset>
</form>
<table class="table table-striped table-condensed">
    <thead>
    <tr>
        <th>Role name</th>
		<th>Role members</th>
        <th></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach($allRoles as $role):?>
        <tr>
            <td><?php echo $role->name;?></td>
			<td><?php echo implode(", ",$roleMembers[$role->id]); ?></td>
            <td><?php echo anchor("user/edit_role/".$role->id, 'Edit role..') ;?></td>
        </tr>
    <?php endforeach;?>
    </tbody>
</table>
</div></div>
<?php include ADD::sys()->getWebAppPath("views/footer.php"); ?>