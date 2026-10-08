<div class="panel panel-primary panel-dark m-b-0">
	<div class="panel-heading">
		<div class="drag-handle panel-title table-caption"><?php echo __('Resend Entry'); ?><a class="close_me pull-right" href="#"><i class="fa fa-remove"></i></a></div>
	</div>

	<div class="panel-body">
		<form method="post" action="<?php echo $this->getAjaxUrl(true); ?>">

		<div class="thread-body" style="background-color: transparent; max-height: 150px; width: 100%; overflow: scroll;">
			<?php echo $this->entry->getBody()->toHtml(); ?>
		</div>

		<?php if ($this->entry->type == 'R') { ?>
		<div style="margin:10px 0;"><strong><?php echo __('Signature'); ?>:</strong>
			<label><input type="radio" name="signature" value="none" checked="checked"> <?php echo __('None');?></label>
			<?php
			if ($poster
				&& $poster->getId() != $thisstaff->getId()
				&& $poster->getSignature()
			) { ?>
			<label><input type="radio" name="signature" value="theirs"
				<?php echo ($info['signature']=='theirs')?'checked="checked"':''; ?>> <?php echo __('Their Signature');?></label>
			<?php
			}
			if ($thisstaff->getSignature()) {?>
			<label><input type="radio" name="signature" value="mine"
				<?php echo ($info['signature']=='mine')?'checked="checked"':''; ?>> <?php echo __('My Signature');?></label>
			<?php
			} ?>
			<?php
			if ($dept && $dept->canAppendSignature()) { ?>
			<label><input type="radio" name="signature" value="dept"
				<?php echo ($info['signature']=='dept')?'checked="checked"':''; ?>>
				<?php echo sprintf(__('Department Signature (%s)'), Format::htmlchars($dept->getName())); ?></label>
			<?php
			} ?>
		</div>
		<?php } # end of type == 'R' ?>

		<div class="full-width" style="margin-top:20px;">
			<input type="button" name="cancel" class="close_me btn" value="<?php echo __('Cancel'); ?>">
			<input type="submit" name="save" class="btn btn-success" value="<?php echo __('Resend'); ?>">
		</div>

		</form>
	</div>
</div>
