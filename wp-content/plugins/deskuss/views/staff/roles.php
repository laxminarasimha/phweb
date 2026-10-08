<div class="panel">
<form action="roles.php" method="POST" name="roles">
<div class="panel-heading">
	<div class="panel-title table-caption"><?php echo __('Roles'); ?></div>
</div>

<div class="panel-body">

<div class="pull-right">	
	<a href="roles.php?a=add" class="btn btn-success btn-outline"><i class="fa fa-plus-circle"></i> <?php
	echo __('Add New Role'); ?></a>
	<div class="btn-group">
	  <button type="button" class="btn btn-outline dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i>&nbsp;<?php echo __('More');?></button>
		<ul class="dropdown-menu dropdown-menu-right" id="actions">
			<li><a class="confirm" data-name="enable" href="roles.php?a=enable">
				<i class="fa fa-check"></i>
				<?php echo __('Enable'); ?></a></li>
			<li><a class="confirm" data-name="disable" href="roles.php?a=disable">
				<i class="fa fa-ban"></i>
				<?php echo __('Disable'); ?></a></li>
			<li class="danger"><a class="confirm" data-name="delete" href="roles.php?a=delete">
				<i class="fa fa-trash"></i>
				<?php echo __('Delete'); ?></a></li>
		</ul>
	</div>
</div>

<br/><br/>
<?php
$page = (!empty($_GET['p']) && is_numeric($_GET['p'])) ? $_GET['p'] : 1;
$count = Role::objects()->count();
$pageNav = new Pagenate($count, $page, PAGE_LIMIT);
$pageNav->setURL('roles.php');
$showing=$pageNav->showing().' '._N('role', 'roles', $count);

csrf_token(); ?>
<div class="form-group pd-bt-10">
	<?php
		if ($count){ //Show options..
	?>
	<div class="pull-right">
		<?php
			echo __('Page').':'.$pageNav->getPageLinks();
			}
		?>
	</div>
</div>
<input type="hidden" name="do" value="mass_process" >
<input type="hidden" id="action" name="a" value="" >
<div class="row">
	<div class="col-md-12">
	<div class="table-light table-responsive">
	<table class="table table-bordered list" border="0" cellspacing="1" cellpadding="0" width="940">
    <thead>
	<tr>
		<th width="4%" class="text-center"><?php if($count){ ?><input type="checkbox" id="selectToggle" class="ckb"><?php }else{ echo __(''); }?></th>
		<th width="53%"><?php echo __('Name'); ?></th>
		<th width="8%"><?php echo __('Status'); ?></th>
		<th width="15%"><?php echo __('Created On') ?></th>
		<th width="20%"><?php echo __('Last Updated'); ?></th>
	</tr>
    </thead>
    <tbody>
    <?php foreach (Role::objects()->order_by('name')
                ->limit($pageNav->getLimit())
                ->offset($pageNav->getStart()) as $role) {
				$id = $role->getId();
				$sel = false;
				if (!empty($ids) && in_array($id, $ids))
					$sel = true; ?>
	<tr>
		<td align="center">
			<?php
			if ($role->isDeleteable()) { ?>
			<input width="7" type="checkbox" class="ckb" name="ids[]"
			value="<?php echo esc_attr($id); ?>" <?php echo $sel?'checked="checked"':''; ?>>
			<?php
			} else {
				echo '&nbsp;';
			}
			?>
		</td>
		<td><a href="?id=<?php echo esc_attr($id); ?>"><?php echo
		esc_html($role->getLocal('name')); ?></a></td>
		<td>&nbsp;<?php echo $role->isEnabled() ? __('Active') :
		'<b>'.__('Disabled').'</b>'; ?></td>
		<td><?php echo Format::date($role->getCreateDate()); ?></td>
		<td><?php echo Format::datetime($role->getUpdateDate()); ?></td>
	</tr>
    <?php }
    ?>
    </tbody>
	<?php 
	if(!$count){
		echo '<tfoot><tr><td colspan="5">'.sprintf(__('No roles defined yet &mdash; %s add one %s!'),
						'<a href="roles.php?a=add">','</a>').'</td></tr></tfoot>';
	}
	?>
</table>
</div>
</div>
</div>
	<?php
		if ($count){ //Show options..
	?>
	<div class="pull-right">
		<span class="faded"><?php echo $pageNav->showing(); ?>&nbsp;&mdash;&nbsp;</span>
		<?php
			echo __('Page').':'.$pageNav->getPageLinks();
			}
		?>
	</div>
</div>
</form>
</div>

<div style="display:none;border:none" class="dialog panel panel-primary panel-dark" id="confirm-action">
	<div class="panel-heading">
		<div class="drag-handle panel-title"><?php echo __('Please Confirm');?>
		<a class="close_me pull-right" href=""><i class="fa fa-close"></i></a>
		</div>
	</div>
	<div class="panel-body">
	<p class="confirm-action" style="display:none;" id="enable-confirm">
		<?php echo sprintf(__('Are you sure want to <b>enable</b> %s?'),
			_N('selected role', 'selected roles', 2));?>
	</p>
	<p class="confirm-action" style="display:none;" id="disable-confirm">
		<?php echo sprintf(__('Are you sure want to <b>disable</b> %s?'),
			_N('selected role', 'selected roles', 2));?>
	</p>
	<p class="confirm-action" style="display:none;" id="delete-confirm">
		<font class="text-danger"><?php echo sprintf(
		__('Are you sure you want to DELETE %s?'),
		_N('selected role', 'selected roles', 2)); ?></font>
		<br><br><?php echo __('Deleted roles CANNOT be recovered.'); ?>
	</p>
	<div><?php echo __('Please confirm to continue.'); ?></div>
	<br/>
	<p class="full-width">
		<span class="buttons pull-left">
			<input type="button" value="<?php echo __('No, Cancel'); ?>" class="close_me btn btn-success">
		</span>
		<span class="buttons pull-right">
			<input type="button" value="<?php echo __('Yes, Do it!'); ?>" class="confirm btn btn-info">
		</span>
	</p>
</div>
</div>
