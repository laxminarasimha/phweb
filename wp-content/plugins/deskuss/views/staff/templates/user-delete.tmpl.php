<?php

if (!$info['title'])
    $info['title'] = sprintf('%s: %s', __('Delete User'), Format::htmlchars($user->getName()));

$info['warn'] = __('Deleted users and tickets CANNOT be recovered');

?>
<div class="panel panel-primary panel-dark m-b-0"> 
	<div class="panel-heading">
		<div class="drag-handle panel-title">
			<?php echo $info['title']; ?>
			<a class="close_me pull-right" href="#"><i class="fa fa-remove"></i></a>
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

	<div id="user-profile" style="margin:5px;">
		<div class="row">
		<?php
		if ($user) { ?>
			<div class="avatar pull-left" style="margin: 0 10px;">
			<?php echo $user->getAvatar(); ?>
			</div>
		<?php
		}
		else { ?>
			<i class="fa fa-user fa-4x pull-left fa-border"></i>
		<?php
		}
			// TODO: Implement change of ownership
			if (0 && $user->getNumTickets()) { ?>
			<a class="action-button pull-right change-user" style="overflow:inherit"
				href="#users/<?php echo $user->getId(); ?>/replace" ><i
				class="fa fa-user"></i> <?php echo __('Change Tickets Ownership'); ?></a>
			<?php
			} ?>
			<fieldset class="form-group">
				<label class="control-label"><?php echo Format::htmlchars($user->getName()->getOriginal()); ?></label>
				<div>&lt;<?php echo $user->getEmail(); ?>&gt;</div>
			</fieldset>
			<fieldset class="form-group">
		<?php foreach ($user->getDynamicData() as $entry) {
		?>
		<?php foreach ($entry->getAnswers() as $a) { ?>
			<div class="row">
				<div class="col-md-3"><?php echo Format::htmlchars($a->getField()->get('label'));
				 ?>:</div>
				<div class="col-md-9"><?php echo $a->display(); ?></div>
			</div>
		<?php }
		}
		?>
			</fieldset>
		</div>	
			<form method="post" class="user"
				action="#users/<?php echo $user->getId(); ?>/delete">
				<?php csrf_token(); ?>
				<input type="hidden" name="id" value="<?php echo $user->getId(); ?>" />
			<?php
			if (($num=$user->tickets->count())) {
				echo '<div class="checkbox"><label><input type="checkbox" name="deletetickets" value="1" >'
					.sprintf(__('Delete %1$s %2$s %3$s and any associated attachments and data.'),
						sprintf('<a href="tickets.php?a=search&uid=%d" target="_blank">',
							$user->getId()),
						sprintf(_N('one ticket', '%d tickets', $num), $num),
						'</a>'
					)
					.'</label></div>';
			}
			?>
				<div style="margin-top:20px">
					<input type="submit" class="btn btn-success" value="<?php echo __('Yes, Delete User'); ?>">
					<input type="reset" class="btn btn-info" value="<?php echo __('Reset'); ?>">
					<input type="button" name="cancel" class="close_me btn"
						value="<?php echo __('No, Cancel'); ?>">
				</div>
			</form>
		</div>
	</div>
</div>
<script type="text/javascript">
$(function() {
    $('a#edituser').click( function(e) {
        e.preventDefault();
        $('div#user-profile').hide();
        $('div#user-form').fadeIn();
        return false;
     });

    $(document).on('click', 'form.user input.cancel', function (e) {
        e.preventDefault();
        $('div#user-form').hide();
        $('div#user-profile').fadeIn();
        return false;
     });
});
</script>
