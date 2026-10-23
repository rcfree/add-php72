<?php include ADD::web()->getWebAppPath("views/header.php"); ?>
<div class="container">  
<div class="row">
	<div class="span12">
<div class="alert alert-error">
The requested page was not found in ADD.
</div>
<?php if(ADD::sys()->config('debug')): ?>
            <div class="well">
Expected pages in modules:<br/>
<?php
	$add = ADD::sys();
	$modPages = $add->getPagesInModules();
	foreach($modPages as $modName => $pages) {
            if (count($pages)==0) continue;
		echo "<h4>Module:$modName</h4><ul>";
               
		foreach($pages as $name => $page) {
                    $menuPath = $page->createMenuPath();
                    if (!$menuPath) continue;
			echo "<li>$name = ".$page->createMenuPath()."</li>";
		}
		echo "</ul><br/>";
	}
?>
            </div>
        </div>
</div>
<?php endif ?>
</div>