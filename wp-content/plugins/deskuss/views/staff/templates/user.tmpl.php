<?php
if (!isset($info['title']))
    $info['title'] = Format::htmlchars($user->getName());

if ($info['title']) { ?>
<div class="panel m-b-0">
	<div class="panel-heading" style="background-color:#2a94db;">
		<div class="drag-handle panel-title" style="color:white;">
			<?php echo $info['title']; ?>
		<a class="close_me pull-right" href="#"><i class="fa fa-remove"></i></a>
		</div>
	</div>
	<div class="panel-body perfectScrollbar" style="height: 300px;position: relative;">
		<?php
		} else {
			echo '<div class="clear"></div>';
		}
		if ($info['error']) {
			echo sprintf('<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">×</button><i class="fa fa-exclamation-triangle"></i>&nbsp;&nbsp;%s</div>', $info['error']);
		} elseif ($info['msg']) {
			echo sprintf('<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert">×</button><i class="fa fa-check"></i>&nbsp;&nbsp;%s</div>', $info['msg']);
		} ?>
	<div id="user-profile" style="display:<?php echo $forms ? 'none' : 'block'; ?>;margin:5px;">
		<div class="avatar pull-left col-md-2">
		<?php echo $user->getAvatar(); ?>
		</div>
		<?php
		if ($ticket) { ?>
		<a class="btn btn-outline btn-primary action-button pull-right change-user" style="overflow:inherit"
			href="#tickets/<?php echo $ticket->getId(); ?>/change-user" ><i class="fa fa-user"></i>&nbsp;<?php echo __('Change User'); ?></a>
		<?php
		} ?>
		<div class="col-md-10 row">
			<b><?php
			echo Format::htmlchars($user->getName()->getOriginal()); ?></b>
		</div>
		<div class="col-md-10 row">
			&lt;<?php echo $user->getEmail(); ?>&gt;
		</div>
		<?php
		if (($org=$user->getOrganization())) { ?>
		<div style="margin-top: 7px;">
			<?php echo $org->getName(); ?>
		</div>
		<?php
		} ?>
	<div class="col-md-12">
		<ul class="nav nav-tabs" style="margin-top:5px">
			<li class="active">
				<a data-toggle="tab" href="#info-tab"
				><i class="fa fa-info-circle"></i>&nbsp;<?php echo __('User'); ?></a>
			</li>
		<?php if ($org) { ?>
			<li>
				<a data-toggle="tab" href="#org-tab"
				><i class="fa fa-building-o"></i>&nbsp;<?php echo __('Organization'); ?></a>
			</li>
		<?php }
			$ext_id = "U".$user->getId();
			$notes = QuickNote::forUser($user, $org)->all(); ?>
			<li>
				<a data-toggle="tab" href="#notes-tab"
				><i class="fa fa-thumb-tack"></i>&nbsp;<?php echo __('Notes'); ?></a>
			</li>
		</ul>

	<div id="user_tabs_container" class="tab-content">
		<div class="tab-pane fade in active" id="info-tab">
			<div class="floating-options pull-right">
			<?php if ($thisstaff->hasPerm(User::PERM_EDIT)) { ?>
				<a href="<?php echo $info['useredit'] ?: '#'; ?>" id="edituser" class="action btn" title="<?php echo __('Edit'); ?>"><i class="fa fa-pencil-square-o"></i></a>
			<?php }
				  if ($thisstaff->hasPerm(User::PERM_DIRECTORY)) { ?>
				<a href="users.php?id=<?php echo $user->getId(); ?>" title="<?php
					echo __('Manage User'); ?>" class="action btn"><i class="fa fa-share-square"></i></a>
			<?php } ?>
			</div>
			<div class="form-group">
			<?php foreach ($user->getDynamicData() as $entry) {
			?>
					<div class="form-group">
						<label class="control-label"><?php echo $entry->getTitle(); ?></label>
					 </div>
				<?php foreach ($entry->getAnswers() as $a) { ?>
					<div class="form-group">
						<div class="col-md-12 row"><?php echo Format::htmlchars($a->getField()->get('label'));
						 ?>:</div>
						<div class="col-md-12 row"><?php echo $a->display(); ?></div>
					</div>
				<?php }
				}
				?>
			</div>
		</div>

	<?php if ($org) { ?>
	<div class="tab-pane fade" id="org-tab">
	<?php if ($thisstaff->hasPerm(User::PERM_DIRECTORY)) { ?>
		<div class="floating-options">
			<a href="orgs.php?id=<?php echo $org->getId(); ?>" title="<?php
			echo __('Manage Organization'); ?>" class="action"><i class="fa fa-share-square-o"></i></a>
		</div>
	<?php } ?>
		<div class="form-group">
	<?php foreach ($org->getDynamicData() as $entry) {
	?>
			<div class="form-group">
				<label class="control-label"><?php echo $entry->getTitle(); ?></label>
			</div>
		<?php foreach ($entry->getAnswers() as $a) { ?>
			<div class="form-group">
				<div class="col-md-6"><?php echo Format::htmlchars($a->getField()->get('label'));
				 ?>:</div>
				<div class="col-md-6"><?php echo $a->display(); ?></div>
			</div>
	<?php }
	}
	?>
		</div>
	</div>
	<?php } # endif ($org) ?>

		<div class="tab-pane fade" id="notes-tab">
		<div id="quick-notes">
		<?php $show_options = true;
		foreach ($notes as $note)
			include STAFFINC_DIR . 'templates/note.tmpl.php';
		?>
		</div>
			<div id="new-note-box">
				<div class="quicknote no-options" id="new-note"
					data-url="users/<?php echo $user->getId(); ?>/note">
					<div class="body">
						<a class="btn btn-outline btn-success" href="#" style="box-sizing: border-box; text-align:left;"><i class="fa fa-plus"></i> &nbsp;
						<?php echo __('Create a new note'); ?></a>
					</div>
				</div>
			</div>
		</div>
	</div>
	</div>

	</div>
	<div id="user-form" style="display:<?php echo $forms ? 'block' : 'none'; ?>;">
	<div><p id="msg_info"><i class="fa fa-info-circle"></i>&nbsp; <?php echo __(
	'Please note that updates will be reflected system-wide.'
	); ?></p></div>
	<?php
	$action = $info['action'] ? $info['action'] : ('#users/'.$user->getId());
	if ($ticket && $ticket->getOwnerId() == $user->getId())
		$action = '#tickets/'.$ticket->getId().'/user';
	?>
	<form method="post" class="user" action="<?php echo $action; ?>">
		<?php csrf_token(); ?>
		<input type="hidden" name="uid" value="<?php echo $user->getId(); ?>" />
		<div class="form-group">
		<?php
			if (!$forms) $forms = $user->getForms();
			foreach ($forms as $form)
				$form->render();
		?>
		</div>
		<div class="form-group" style="margin-top:20px;">
				<input type="submit" class="btn btn-success" value="<?php echo __('Update User'); ?>">
				<input type="reset" class="btn btn-info" value="<?php echo __('Reset'); ?>">
				<input type="button" name="cancel" class="btn <?php
		echo ($ticket && $user) ? 'close_me' : 'close_me' ?>"  value="<?php echo __('Cancel'); ?>">
		 </div>
	</form>
	</div>
	</div>
</div>
<script type="text/javascript">
$(document).ready(function (){
	$('.perfectScrollbar').perfectScrollbar();
});
		  
$(function() {
    $('a#edituser').click( function(e) {
        e.preventDefault();
        if ($(this).attr('href').length > 1) {
            var url = 'ajax.php/'+$(this).attr('href').substr(1);
            $.dialog(url, [201, 204], function (xhr) {
                window.location.href = window.location.href;
            }, {
                onshow: function() { $('#user-search').focus(); }
            });
        } else {
            $('div#user-profile').hide();
            $('div#user-form').fadeIn();
        }

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
