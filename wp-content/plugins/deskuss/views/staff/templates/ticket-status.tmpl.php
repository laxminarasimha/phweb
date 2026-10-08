<?php
global $cfg;

if (!$info['title'])
    $info['title'] = __('Change Tickets Status');

?>
<div class="panel panel-primary panel-dark m-b-0">
	<div class="panel-heading">
		<div class="drag-handle panel-title">
			<?php echo $info['title']; ?><a class="close_me pull-right" href="#"><i class="fa fa-remove"></i></a>
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
		} elseif ($info['notice']) {
		   echo sprintf('<div class="alert alert-info"><button type="button" class="close" data-dismiss="alert">×</button><i class="fa fa-info-circle"></i>&nbsp;&nbsp;%s</div>',
				   $info['notice']);
		}


		$action = $info['action'] ?: ('#tickets/status/'. $state);
		?>
 	<div style="display:block; margin:5px;">
		<form method="post" name="status" id="status"
			action="<?php echo $action; ?>">
			<?php csrf_token(); ?>
			<div class="form-group">
				<?php
				if ($info['extra']) {
					?>
				<div class="form-group">
					<label class="control-label"><?php echo $info['extra'];?></label>
				</div>
				<?php
				}

				$verb = '';
				if ($state) {
					$statuses = TicketStatusList::getStatuses(array('states'=>array($state)))->all();
					$verb = TicketStateField::getVerb($state);
				}

				if ($statuses) {
				?>
				<div class="form-group form-inline">
					<span>
					<?php if (count($statuses) > 1) { ?>
						<label class="control-label required"><?php echo __('Status') ?>:&nbsp;</label>
						<select class="form-control" name="status_id">
						<?php
						foreach ($statuses as $s) {
							echo sprintf('<option value="%d" %s>%s</option>',
									$s->getId(),
									($info['status_id'] == $s->getId())
									 ? 'selected="selected"' : '',
									$s->getName()
									);
						}
						?>
						</select>
						<?php
							if(!empty($errors['status_id'])){
								echo '<div class="alert-danger">&nbsp;'.$errors['status_id'].'</div>';
							}
						?>
					<?php
					} elseif ($statuses[0]) {
						echo  "<input type='hidden' name='status_id' value={$statuses[0]->getId()} />";
					} ?>
					</span>
				</div>
				<?php
				} ?>
				<div class="form-group form-inline">
					<?php
						$placeholder = $info['placeholder'] ?: __('Optional reason for status change (internal note)');
					?>
					<textarea  name="comments" id="comments"cols="50" rows="3" wrap="soft" style="width:100%" class="<?php if ($cfg->isRichTextEnabled()) echo 'summernote-base'; ?> no-bar small form-control" placeholder="<?php echo $placeholder; ?>"><?php echo $info['comments']; ?></textarea>
				</div>
			</div>
			<div style="margin-top:20px;">
				<input type="submit" class="btn btn-success" value="<?php
				echo $verb ?: __('Submit'); ?>">
				<input type="reset" class="btn btn-info" value="<?php echo __('Reset'); ?>">
				<input type="button" name="cancel" class="close_me btn"
					value="<?php echo __('Cancel'); ?>">
			 </div>
		</form>
	</div>
	</div>
</div>
<script type="text/javascript">
$(function() {
    // Copy checked tickets to status form.
    $('form#tickets input[name="tids[]"]:checkbox:checked')
    .each(function() {
        $('<input>')
        .prop('type', 'hidden')
        .attr('name', 'tids[]')
        .val($(this).val())
        .appendTo('form#status');
    });
});

</script>

