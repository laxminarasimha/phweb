<?php
if (!$info['title'])
    $info['title'] = Format::htmlchars($org->getName());
?>
<div class="panel panel-primary panel-dark m-b-0">
	<div class="panel-heading">	
		<div class="drag-handle panel-title"><?php echo $info['title']; ?>
		<a class="close_me pull-right" href="#"><i class="fa fa-remove"></i></a></div>
	</div>

	<div class="panel-body">
		<?php
		if ($info['error']) {
			echo sprintf('<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">×</button><i class="fa fa-exclamation-triangle"></i>&nbsp;&nbsp;%s</div>', $info['error']);
		} elseif ($info['msg']) {
			echo sprintf('<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert">×</button><i class="fa fa-check"></i>&nbsp;&nbsp;%s</div>', $info['msg']);
		} ?>
	<div id="org-profile" style="display:<?php echo $forms ? 'none' : 'block'; ?>;margin:5px;">
		<i class="fa fa-users fa-4x fa-pull-left fa-border"></i>
		<?php if ($user) { ?>
		<a class="action-button btn btn-outline pull-right user-action"
			href="#users/<?php echo $user->getId(); ?>/org/<?php echo $org->getId(); ?>" ><i class="fa fa-user"></i>
			<?php echo __('Change'); ?>
		</a>
		<a class="action-button btn btn-outline pull-right" href="orgs.php?id=<?php echo $org->getId(); ?>"><i class="fa fa-share-square-o"></i>
			<?php echo __('Manage'); ?>
		</a>
		<?php
		} ?>
		<div class="form-group">
			<a href="#" id="editorg"><i class="fa fa-pencil-square-o"></i>&nbsp;<?php
			echo Format::htmlchars($org->getName()); ?></a>
		</div>
		<fieldset class="form-group">
			<?php foreach ($org->getDynamicData() as $entry) { ?>
					<label class="control-label">
						<?php echo $entry->getTitle(); ?>
					</label>
			<?php foreach ($entry->getAnswers() as $a) { ?>
				<div class="form-group">
					<div class="col-md-5">
						<?php echo Format::htmlchars($a->getField()->get('label'));?>:
					</div>
					<div class="col-md-7">
						<?php echo $a->display(); ?>
					</div>
				</div>	
			<?php }
			}
			?>
		</fieldset>
			<div class="panel-footer">
				Last updated : <b><?php echo Format::datetime($org->getUpdateDate()); ?> </b>
			</div>
	</div>
	<div id="org-form" style="display:<?php echo $forms ? 'block' : 'none'; ?>;">
		<div>
			<p id="msg_info"><i class="fa fa-info-circle"></i>&nbsp; <?php echo __(
		'Please note that updates will be reflected system-wide.'); ?></p>
		</div>
		<?php
		$action = $info['action'] ? $info['action'] : ('#orgs/'.$org->getId());
		if ($ticket && $ticket->getOwnerId() == $user->getId())
			$action = '#tickets/'.$ticket->getId().'/user';
		?>
		<form method="post" class="org" action="<?php echo $action; ?>">
			<input type="hidden" name="id" value="<?php echo $org->getId(); ?>" />
			<div class="form-group">
			<?php
				if (!$forms) $forms = $org->getForms();
				foreach ($forms as $form)
					$form->render();
			?>
			</div>
			<div class="form-group" style="margin-top:20px">
				<input type="submit" class="btn btn-success" value="<?php echo __('Update Organization'); ?>">
				<input type="reset" class="btn btn-info" value="<?php echo __('Reset'); ?>">
				<input type="button" name="cancel" class="btn <?php
				echo $account ? 'cancel' : 'close_me'; ?>"  value="<?php echo __('Cancel'); ?>">
			 </div>
		</form>
	</div>
</div>
</div>
<script type="text/javascript">
$(function() {
    $('a#editorg').click( function(e) {
        e.preventDefault();
        $('div#org-profile').hide();
        $('div#org-form').fadeIn();
        return false;
     });

    $(document).on('click', 'form.org input.cancel', function (e) {
        e.preventDefault();
        $('div#org-form').hide();
        $('div#org-profile').fadeIn();
        return false;
     });
});
</script>
