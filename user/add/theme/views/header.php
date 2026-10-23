<?php 
$add = ADD::sys();
include_once ADD::sys()->getWebAppPath("views/head.php");
$web = ADD::web();
$site_path = $add->config('site_path');
$isLive = $add->config('live');
if($add->config('no_patient_on_home')) {
	$home_path="/";
}
else {
	$home_path = "/".($add->getPatientId() ? "?pid=".$add->getPatientId()."&t=".ADD::db('token')->generate() : "");
}
$aconfig = $add->getConfig();
?>

<body ontouchmove="BlockMove(event);" ng-app="clinicle" ng-controller="RootController" pid="<?php echo $add->getPatientId() ?>" pim="<?php $cpage = $add->getCurrentPage(); if($cpage) { echo $cpage->getModule()->getAlias(). ":".$cpage->getName(); } else { echo "options:home"; } ?>">
    
	<div class="navbar navbar-fixed-top">
	   
    
      <div class="navbar-inner"  style="height:100px">
                  <div class="container-fluid">

       <div class="row-fluid">
       	<div class="span3">
       	    <img src="<?php echo $site_path ?>/images/ilh_logo.gif" style="position:absolute;top:5px;left:5px"/>

        	<div style="position:absolute;top:-10px;left:90px;">
           <a style="color:white" ng-click="gotoHome(<?php echo $add->getConfig()->has('no_patient_on_home') ? 1 : 0 ?>);"><h2 class="sys-title"><?php if ($add->config('site_name')): echo $add->config('site_name'); else: echo "ADD"; endif;?></h2><?php if (!$isLive): ?><p class="sys-tag"><?php echo $add->config('site_tag') ?></p><?php endif ?></a>
                <?php echo $web->getContent('sideOptions'); ?>
         <div class="btn-group btn-mini">
<?php echo $web->getContent('commonOptions'); ?>

</div>
         <?php if ($add->config('maintenance')): ?><p class="text-warning">MAINTENANCE MODE</p><?php endif ?>
         </div>
         </div>
         <div class="span5">
<?php
if ($this->ion_auth->logged_in()) {
?>
            
            <div style="width:100%;float:left;padding-top:10px;">
				<div ng-show="isPanelLoading('top')" class="muted">Working.. <img src="/module/clinicle/client/images/ajax-loader.gif"></img></div>
				<div id="top-panel"></div>	
</div>
<?php } ?>
<?php
if ($this->ion_auth->logged_in()) {
?>
</div>
<div class="span4 navbar-inverse">
    <div class="container-fluid">
        <div class="row-fluid">
	<ul class="nav pull-right">
		<li class="dropdown">
			<a href="#" class="dropdown-toggle" data-toggle="dropdown">Options <b class="caret"></b></a>
<?php echo $web->getContent('optionMenu'); ?>
              </li>
              <li><a href="<?php echo $site_path ?>/auth/logout">Logout <?php echo $this->ion_auth->user()->row()->first_name; ?></a></li>
           	<input name="username" type="hidden" value="<?php echo $this->ion_auth->user()->row()->username; ?>"/>
             </ul>
        </div>
        <div class="row-fluid">
<?php if ($this->ion_auth && ($this->ion_auth->is_admin() || $this->ion_auth->in_group('search_patient_search'))): ?>
            <form class="navbar-form pull-right" method="post" action="<?php echo $site_path ?>/search" ng-submit="save($event)" onsubmit="return false;">
              <input class="span6" name="terms" type="text" placeholder="Enter patient no"/>
              <button type="submit" class="btn"><i class="icon-search"></i>&nbsp;Search</button>
            </form>
        </div>
<?php endif ?>
     		
 <?php } ?>
     </div>
      </div>
   </div>
</div>
	</div>
   </div>
   <div id="modal-confirm" class="modal hide fade" style="width:500px">
<div class="modal-body">
<h4 class="alert-heading" id="modal-confirm-title">Title</h4>
<p id="modal-confirm-text"></p>
<p>
</div>
<div class="modal-footer">
  <a id="modal-confirm-yes" href="javascript:;" class="btn btn-primary">Yes, please</a> <a id="modal-confirm-no" href="javascript:;" onclick="$('#modal-confirm').modal('hide');" class="btn">No, thanks</a>
</div>
 </div>
   <div id="content">
