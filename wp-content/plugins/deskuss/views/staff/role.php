<?php

$info=array();
if ($role) {
    $title = __('Update Role');
    $action = 'update';
    $submit_text = __('Save Changes');
    $info = $role->getInfo();
    $trans['name'] = $role->getTranslateTag('name');
    $newcount=2;
} else {
    $title = __('Add New Role');
    $action = 'add';
    $submit_text = __('Add Role');
    $newcount=4;
}

$info = Format::htmlchars(($errors && $_POST) ? array_merge($info, $_POST) : $info);

?>
<div class="panel">
<form action="" method="post" class="save">
<?php csrf_token(); ?>
<input type="hidden" name="do" value="<?php echo esc_attr($action); ?>">
<input type="hidden" name="a" value="<?php echo Format::htmlchars($_REQUEST['a']); ?>">
<input type="hidden" name="id" value="<?php echo esc_attr($info['id']); ?>">
<div class="panel-heading">
	<div class="panel-title table-caption">
		<?php echo esc_html($title); ?> — 
		<small class="text-muted"><em>
			<?php echo __('Roles are used to define agents\' permissions'); ?>&nbsp;
			<i class="help-tip fa fa-question-circle" href="#roles"></i>
		</em></small>
	</div>
</div>
<div class="panel-body">
	<div class="row">
	<div class="col-sm-6">
		<div class="col-sm-12 panel-heading">
			<div class="panel-title table-caption">
				<?php echo __('Definition'); ?>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-12 form-group">	
			<div class="panel-body">
				<label class="required"><?php echo __('Name'); ?>: </label>&nbsp;&nbsp;
				<input size="50" type="text" class="form-control" name="name" value="<?php echo esc_attr($info['name']); ?>" data-translate-tag="<?php echo esc_attr($trans['name']); ?>"
				 autofocus/>
				<?php
				if(!empty($errors['name'])){
					echo '<div class="alert-danger">&nbsp;'.$errors['name'].'</div>';
				}
				?>
			</div>
			</div>
			
			<div class="col-sm-12">
				<div class="panel-heading">
					<a class="internal_note"><div class="panel-title table-caption"><i class="fa fa-plus-square"></i>&nbsp;<em><strong><?php echo __('Internal Notes'); ?></strong> </em></div></a>
				</div>
			</div>
			<div class="col-sm-12 panel-body notes" style="display:none;">
				<textarea name="notes" class="summernote-base richtext no-bar" rows="6" cols="80"><?php echo $info['notes']; ?></textarea>
			</div>
		</div>
	</div>
	<div class="col-sm-6">
		<div class="col-sm-12 panel-heading m-b-3">
			<div class="panel-title table-caption">
				<?php echo __('Permissions'); ?>
			</div>
		</div>
		<?php
			$setting = $role ? $role->getPermissionInfo() : array();
			// Eliminate groups without any department-specific permissions
			$buckets = array();
			foreach (RolePermission::allPermissions() as $g => $perms) {
				foreach ($perms as $k => $v) {
				if ($v['primary'])
					continue;
					$buckets[$g][$k] = $v;
			}
		} ?>
		<ul class="nav nav-tabs nav-tabs-simple">
			<?php
				$first = true;
				foreach ($buckets as $g => $perms) { ?>
					<li <?php if ($first) { echo 'class="active"'; $first=false; } ?>>
						<a href="#<?php echo Format::slugify($g); ?>" data-toggle="tab"><?php echo Format::htmlchars(__($g));?></a>
					</li>
			<?php } ?>
		</ul>
		<div class="tab-content">
		<?php
		$first = true;
		
		foreach ($buckets as $g => $perms) { ?>

		<div class="tab-pane fade <?php if ($first) { echo 'active in';$first = false; }
			?>" id="<?php echo Format::slugify($g); ?>">
			<table class="table">
				<?php foreach ($perms as $k => $v) { ?>
				<tr>
					<td>
						<label>
							<?php
							echo sprintf('<input type="checkbox" name="perms[]" value="%s" %s />',
							$k, (isset($setting[$k]) && $setting[$k]) ?  'checked="checked"' : ''); ?>
							&nbsp;
							<?php echo Format::htmlchars(__($v['title'])); ?>
							—
							<small class="text-muted"><?php echo Format::htmlchars(__($v['desc']));
							?></small>
						</label>
					</td>
				</tr>
				<?php } ?>
			</table>
		</div>
	  
		<?php } ?>
		</div>
	</div>
	<br/>
	</div>
	
	<div class="row">
	<div class="col-sm-12">
		<p class="text-center">
			<input type="submit" class="btn btn-success" name="submit" value="<?php echo esc_attr($submit_text); ?>">
			<input type="reset" class="btn btn-info" name="reset"  value="<?php echo __('Reset'); ?>">
			<input type="button" class="btn btn-default" name="cancel" value="<?php echo __('Cancel'); ?>"
				onclick='window.location.href="?"'>
		</p>
	</div>
	</div>
</form>
</div>
</div>
