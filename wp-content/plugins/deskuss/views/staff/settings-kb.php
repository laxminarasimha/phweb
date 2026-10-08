<?php
if(!defined('DSKADMININC') || !$thisstaff || !$thisstaff->isAdmin() || !$config) die('Access Denied');
?>
<div class="panel">
<div class="panel-heading">
	<div class="panel-title table-caption"><?php echo __('Knowledge Base Settings and Options');?></div>
</div>
<div class="panel-body">
<form action="settings.php?t=kb" method="post">
<?php csrf_token(); ?>
<input type="hidden" name="t" value="kb" >
	
<div class="text-muted">
	<em><?php echo __('Disabling knowledge base disables clients\' interface'); ?></em>
</div><br/>
	
<div class="row">
	<div class="form-group">
		<label class="col-md-2"><?php echo __('Knowledge Base Status'); ?>:
		</label>
		<div class="col-md-5">
			<label>
				<input type="checkbox" name="enable_kb" id="enable_kb" value="1" <?php 
				echo $config['enable_kb']?'checked="checked"':''; ?>>
				<?php echo __('Enable Knowledge Base'); ?>&nbsp;
				<i class="help-tip fa fa-question-circle" href="#knowledge_base_status"></i>
			</label>
			<?php
			if(!empty($errors['enable_kb'])){
				echo '<div class="alert-danger">&nbsp;'.$errors['enable_kb'].'</div>';
			}
			?>
		</div>
	</div>
</div>
<br />
<div class="row">
	<div class="form-group">
		<label class="col-md-2"><?php echo __('Knowledge Base Access'); ?>:
		</label>
		<div class="col-md-5">
			<label>
				<input type="checkbox" name="restrict_kb" id="restrict_kb" value="1" <?php 
				echo $config['restrict_kb']?'checked="checked"':''; ?> >
				<?php echo __('Require Client Login'); ?>&nbsp;
				<i class="help-tip fa fa-question-circle" href="#restrict_kb"></i>
			</label>
			<?php
			if(!empty($errors['restrict_kb'])){
				echo '<div class="alert-danger">&nbsp;'.$errors['restrict_kb'].'</div>';
			}
			?>
		</div>
	</div>
</div>
<br />
<div class="row">
	<div class="form-group">
		<label class="col-md-2"><?php echo __('Canned Responses'); ?>:
		</label>
		<div class="col-md-5">
			<label>
				<input type="checkbox" name="enable_premade" id="enable_premade" value="1" <?php 
					echo $config['enable_premade']?'checked="checked"':''; ?> >
					<?php echo __('Enable Canned Responses'); ?>&nbsp;
				<i class="help-tip fa fa-question-circle" href="#canned_responses"></i>
			</label>
			<?php
			if(!empty($errors['enable_premade'])){
				echo '<div class="alert-danger">&nbsp;'.$errors['enable_premade'].'</div>';
			}
			?>
		</div>
	</div>
</div>
<br />
<p style="text-align:center;">
	<input class="btn btn-success" type="submit" name="submit" value="<?php echo __('Save Changes'); ?>">
	<input class="btn btn-info" type="reset" name="reset" value="<?php echo __('Reset Changes'); ?>">
</p>
</form>
</div>
</div>
