<?php

if (!$info['title'])
    $info['title'] = sprintf(__('Delete %s'), Format::htmlchars($org->getName()));

$info['warn'] = __('Deleted organization CANNOT be recovered');

?>
<div class="panel panel-primary panel-dark m-b-0">
	<div class="panel-heading">
		<div class="drag-handle panel-title"><?php echo $info['title']; ?><a class="close_me pull-right" href="#"><i class="fa fa-remove"></i></a>
		</div>
	</div>

	<div class="panel-body">
		<?php

		if ($info['error']) {
			echo sprintf('<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">×</button><i class="fa fa-exclamation-triangle"></i>&nbsp;&nbsp;%s</div>', $info['error']);
		} elseif ($info['warn']) {
			echo sprintf('<div class="alert alert-warning"><button type="button" class="close" data-dismiss="alert">×</button><i class="fa fa-exclamation-triangle"></i>&nbsp;&nbsp;%s</div>', $info['warn']);
		} elseif ($info['msg']) {
			echo sprintf('<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert">×</button><i class="fa fa-check"></i>&nbsp;&nbsp;%s</div>', $info['msg']);
		} ?>

	<div id="org-profile" style="margin:5px;">
		<i class="fa fa-users fa-4x fa-pull-left fa-border"></i>
		<div>
			<label class="control-label"> <?php echo Format::htmlchars($org->getName()); ?></label>
		</div>
		<fieldset class="form-group">
	<?php foreach ($org->getDynamicData() as $entry) { ?> 
			<label class="control-label">
				<?php echo $entry->getTitle(); ?>
			</label>
	<?php foreach ($entry->getAnswers() as $a) { ?>
			<div class="form-group">
				<div class="col-md-3">
					<?php echo Format::htmlchars($a->getField()->get('label'));?>:
				</div>
				<div class="col-md-9">
					<?php echo $a->display(); ?>
				</div>
			</div>
	<?php } }	?>
		</fieldset>
		<?php
		if (($users=$org->users->count())) { ?>
		<div class="form-group">&nbsp;
			<label class="control-label"><?php echo sprintf(__(
				'%s assigned to this organization will be orphaned.'),
				sprintf(_N('One user', '%d users', $users), $users)); ?>
			</label>
		</div>
		<?php } ?>
		<form method="delete" class="org"
			action="#orgs/<?php echo $org->getId(); ?>/delete">
			<input type="hidden" name="id" value="<?php echo $org->getId(); ?>" />
			<div class="form-group" style="margin-top:20px;">
				<input type="submit" class="btn btn-success" value="<?php echo __('Yes, Delete'); ?>">
				<input type="reset" class="btn btn-info" value="<?php echo __('Reset'); ?>">
				<input type="button" name="cancel" class="btn close_me"
					value="<?php echo __('No, Cancel'); ?>">
			</div>
		</form>
	</div>
	</div>
</div>