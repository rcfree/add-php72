<?php include ADD::sys()->getWebAppPath("views/header.php"); ?>
<style>
	.accordion-heading .accordion-toggle {
		padding-top:0px;
		padding-bottom:0px;
	}
</style>
<script>
function selectAll(modName) {
	$("."+modName+"-perm").prop("checked",true);
}
function unselectAll(modName) {
	$("."+modName+"-perm").prop("checked",false);
}
$(document).ready(function() {
	ADD.init();
});
</script>
<div class="container">
<div class="page-header">
<h3>
	Edit role: <span class="muted"><?php echo $currRole->name ?></span>
</h3>
</div>
	
<?php if (!empty($message)): ?>
<div class="alert alert-block alert-error"><?php echo $message;?></div>
<?php endif ?>
<form method="post" action="/user/store_role/<?php echo $currRoleId ?>">
	<div style="padding-bottom:8px">
<button class="btn btn-primary"><i class="icon-ok icon-white"></i>&nbsp;Save role</button>
		<a href="/user/roles" class="btn"><i class="icon-list"></i>&nbsp;Go to roles list..</a>
	</div>
<div id="roles">
<?php
foreach($uniquePerms as $modName => $uniqueModPerms):
?>
<div class="accordion-group">
	<div id="<?php echo $modName; ?>-heading" class="accordion-heading">
	<h5 href="#<?php echo $modName; ?>-roles" class="accordion-toggle roles" data-parent="#roles" data-toggle="collapse">
		<span class="right-caret"></span>
		<?php $mod = $add->getModule($modName); echo $mod->getSetting('title',$mod->getAlias()); ?></h5>
	</div>
	<div id="<?php echo $modName; ?>-roles" class="accordion-body collapse" data-parent="#roles">
		<div style="padding:8px">
<button class="btn btn-primary"><i class="icon-ok icon-white"></i>&nbsp;Save role</button>

<button class="btn" type="button" onclick="selectAll('<?php echo $modName ?>')" /><i class="icon-star"></i>&nbsp;Select All</button>
<button class="btn" type="button" onclick="unselectAll('<?php echo $modName ?>')"/><i class="icon-star-empty"></i>&nbsp;Unselect All</button>	

<table class="table table-striped table-condensed">
	<thead>
		<tr>
			<th style="width:8em">Page</th>
			<?php foreach($uniqueModPerms as $uniquePerm => $flag): ?>
				<th style="width:3em"><?php echo ADD_Util::nameToLabel($uniquePerm); ?></th>
			<?php endforeach; #uniquePerms header ?>
		</tr>
	</thead>
	<tbody>
<?php 

$modPerms = ADD_Util::getArrayValue($modName,$actualPerms,array());

foreach($modPerms as $pageName => $pagePerms):
?>
		<tr>
			<td style="width:20em">
				<?php $page = $mod->getPageInStructure($pageName); echo $page->getSetting('Permission.title',$page->getSetting('title',$pageName)); ?>
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
</div>
	</div>
</div>
<?php endforeach;  #modules ?>

<input name="postback" value="1" type="hidden"/>
</form>

</div>
<?php include ADD::sys()->getWebAppPath("views/footer.php"); ?>