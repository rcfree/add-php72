<?php
include getenv('ADD_PATH')."/web/application/views/header.php";
 ?>
<script>
function toggleAdmin() {
	var adminChecked = $("#user_is_admin").is(':checked');

	if (adminChecked) {
		$(".admin-toggle").hide();
	}
	else {
		$(".admin-toggle").show();
	}
}

function selectAll(modName) {
	$("."+modName+"-perm").prop("checked",true);
}
function unselectAll(modName) {
	$("."+modName+"-perm").prop("checked",false);
}

</script>
<div class="container">
<div class="page-header">
<h3>User permissions
<small>(<?php echo $currUser->username ?>)</small>
</h3>
</div>
<form method="get">
<fieldset>

<label>Change user</label>
<select name="user">
	<option value="">- choose a user -</option>
<?php
foreach($allUsers as $user):
?>
	<option 
		<?php if ($user->id == $currUserId): ?>
		selected="selected"
		<?php endif; ?>
		value="<?php echo $user->id ?>"><?php echo $user->username ?></option>
<?php
endforeach;
?>
</select>
<button class="btn btn-primary">Edit Permissions</button>
</fieldset>
</form>
<form method="post">
<fieldset>
<label class="checkbox"><input name="user_is_admin" id="user_is_admin" onclick="toggleAdmin()" type="checkbox" <?php if ($isAdmin): ?>checked="checked"<?php endif ?>/>User is an administrator</label>
<button class="btn btn-primary">Save permissions</button>

</fieldset>
<?php
foreach($uniquePerms as $modName => $uniqueModPerms):
?>
<fieldset style="margin-top:2em<?php if ($isAdmin): ?>;display:none<?php endif ?>" class="admin-toggle">
<legend><?php $mod = $add->getModule($modName); echo $mod->getName(); ?></legend>

<button class="btn btn-primary">Save permissions</button>

<button class="btn" type="button" onclick="selectAll('<?php echo $modName ?>')" />Select All</button>
<button class="btn" type="button" onclick="unselectAll('<?php echo $modName ?>')"/>Unselect All</button>	

<table class="table table-striped table-condensed">
	<thead>
		<tr>
			<th style="width:8em">Page</th>
			<?php foreach($uniqueModPerms as $uniquePerm => $flag): ?>
				<th style="width:3em"><?php echo ucfirst($uniquePerm); ?></th>
			<?php endforeach; #uniquePerms header ?>
		</tr>
	</thead>
	<tbody>
<?php 

$modPerms = ADD_Util::getArrayValue($modName,$actualPerms,array());

foreach($modPerms as $pageName => $pagePerms):
?>
		<tr>
			<td style="width:8em">
				<?php $page = $mod->getPage($pageName); echo $page->getSetting('title',$pageName); ?>
			</td>
			
			<?php foreach($uniquePerms[$modName] as $uniquePerm => $flag): ?>
				<td style="width:3em">
				<?php if (ADD_Util::getArrayValue($uniquePerm,$pagePerms)): 
					$permType = $uniquePerm."_".$modName."_".$pageName; ?>
					<input 
					<?php if (isset($currGroups[$permType])): ?>checked="checked"<?php endif; ?> 
					name="<?php echo $permType ?>" 
					class="<?php echo $modName."-perm" ?>" 
					type="checkbox" 
				<?php endif; ?>
				</td>
				<?php endforeach; ?>
		</tr>
<?php endforeach; #cells for each module permission ?>
</tbody>
</table>
</fieldset>

<?php endforeach;  #modules ?>
<input name="postback" value="1" type="hidden"/>
</form>
</div>
<?php
	include getenv('ADD_PATH')."/web/application/views/footer.php";
 ?>
 