<?php include_once "head.php" ?>

<?php 
$add = ADD::sys();
$web = ADD::web();
$site_path = $add->config('site_path') ;

?>

<body ng-app="clinicle" ng-controller="RootController" pid="<?php echo $add->getPatientId() ?>" pim="<?php $cpage = $add->getCurrentPage(); if($cpage) { echo $cpage->getModule()->getAlias(). ":".$cpage->getName(); } else { echo "options:home"; } ?>">
    
	<div class="navbar navbar-fixed-top">
	   
    
      <div class="navbar-inner">
                  <div class="container-fluid">

       <div class="row-fluid">
       	<div class="span3">
       	    <img src="<?php echo $site_path ?>/images/ilh_logo.gif" style="position:absolute;top:5px;left:5px"/>

        	<div style="position:absolute;top:-10px;left:90px;">
           <a style="color:white" href="<?php echo $site_path ?>/<?php if ($add->getPatientId()) echo "?pid=".$add->getPatientId()."&t=".ADD::db('token')->generate() ?>"><h2 class="sys-title"><?php if ($add->config('site_name')): echo $add->config('site_name'); else: echo "ADD"; endif;?></h2><?php if ($add->getProfile()!="uhl-live"): ?><p class="sys-tag"><?php echo $add->config('site_tag') ?></p><?php endif ?></a>
         <div class="btn-group btn-mini">
<?php echo $web->getContent('commonOptions'); ?>

</div>
         <?php if ($add->config('maintenance')): ?><p class="text-warning">MAINTENANCE MODE</p><?php endif ?>
         </div>
         </div>
         <div class="span5">
          <div class="nav-collapse collapse"  style="background-color:darkgreen">
            <div style="width:100%;float:left;padding-top:10px;">
				<div ng-show="isPanelLoading('top')==true"><img src="/module/clinicle/client/images/ajax-loader.gif"></img></div>
				<div id="top-content"></div>	
</div>

</div>
</div>
		   <?php
if ($this->ion_auth->logged_in()) {
?>
<div class="span4 navbar-inverse">
	<ul class="nav pull-right">
		<li class="dropdown">
			<a href="#" class="dropdown-toggle" data-toggle="dropdown">Options <b class="caret"></b></a>
<?php echo $web->getContent('optionMenu'); ?>
              </li>
              <li><a href="<?php echo $site_path ?>/auth/logout">Logout <?php echo $this->ion_auth->user()->row()->first_name; ?></a></li>
           	<input name="username" type="hidden" value="<?php echo $this->ion_auth->user()->row()->username; ?>"/>
             </ul>
<?php if ($this->ion_auth && ($this->ion_auth->is_admin() || $this->ion_auth->in_group('search_patient_search'))): ?>
            <form class="navbar-form pull-right" method="get" action="<?php echo $site_path ?>/search" ng-submit="save($event)" onsubmit="return false;">
              <input class="span2" name="terms" type="text" placeholder="Enter patient no"/>
              <button type="submit" class="btn"><i class="icon-search"></i>&nbsp;Search</button>
            </form>
<?php endif ?>
     		
 <?php } ?>
     </div>
      </div>
   </div>
</div>
	</div>
   </div>
   <div id="content">