<?php include ADD::sys()->getWebAppPath("views/header.php"); ?>
<div class="container">

<div class="page-header">
<h3><?php echo lang('index_heading');?></h3>
<?php if ($message): ?>
<div class="alert alert-error"><?php echo $message;?></div>
<?php endif ?>

<div class="alert alert-info"><?php echo lang('index_subheading');?></div>
</div>
<p><?php echo anchor('auth/create_user', "<i class='icon-plus'></i>&nbsp;".lang('index_create_user_link'),array("class"=>"btn"))?></p>
<table class="table table-striped table-condensed">
    <thead>
    <tr>
        <th>Username</th>
        <th><?php echo lang('index_fname_th');?></th>
        <th><?php echo lang('index_lname_th');?></th>
        <th><?php echo lang('index_email_th');?></th>
        <th>Roles</th>
        <th><?php echo lang('index_status_th');?></th>
        <th><?php echo lang('index_action_th');?></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($users as $user):?>
        <tr>
            <td><?php echo $user->username;?></td>
            <td><?php echo $user->first_name;?></td>
            <td><?php echo $user->last_name;?></td>
            <td><?php echo $user->email;?></td>
            <td>
                <?php echo $userRoles[$user->id] ;?>
            </td>
            <td><?php echo ($user->active) ? anchor("auth/deactivate/".$user->id, lang('index_active_link')) : anchor("auth/activate/". $user->id, lang('index_inactive_link'));?></td>
            <td><?php echo anchor("auth/edit_user/".$user->id, 'Edit user..') ;?></td>
        </tr>
    <?php endforeach;?>
    </tbody>
</table>

</div>
<?php include ADD::sys()->getWebAppPath("views/footer.php"); ?>