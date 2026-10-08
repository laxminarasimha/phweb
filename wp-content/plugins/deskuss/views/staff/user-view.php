<?php
if(!defined('DSKADMININC') || !$thisstaff || !is_object($user)) die('Invalid path');

$account = $user->getAccount();
$org = $user->getOrganization();


?>
<div class="panel">
	 <div class="panel-heading">
	 <span class="panel-title table-caption">
		<a href="users.php?id=<?php echo $user->getId(); ?>"
             title="Reload"><i class="fa fa-refresh text-primary"></i> </a> <?php echo Format::htmlchars($user->getName()); ?>
	 </span>
		<div class="panel-heading-controls" style="margin-bottom:5px;">
		<div class="btn-group pull-right">
		<?php if (($account && $account->isConfirmed())
			|| $thisstaff->hasPerm(User::PERM_EDIT)) { ?>
			<button class="btn btn-outline dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
				<i class="fa fa-caret-down"></i>
				<span><i class="fa fa-cog"></i> <?php echo __('More'); ?></span>
			</button>
			<ul class="dropdown-menu dropdown-menu-right">
				<?php if ($account) {
					if (!$account->isConfirmed()) { ?>
					<li>
						<a class="confirm-action" href="#confirmlink"><i class="fa fa-envelope-o"></i>
						<?php echo __('Send Activation Email'); ?></a>
					</li>
				<?php } else { ?>
					<li>
						<a class="confirm-action" href="#pwreset"><i class="fa fa-envelope-o"></i>
						<?php echo __('Send Password Reset Email'); ?></a>
					</li>
					<?php
					} ?>
	<?php if ($thisstaff->hasPerm(User::PERM_MANAGE)) { ?>
					<li>
						<a class="user-action"
						href="#users/<?php echo $user->getId(); ?>/manage/access"><i class="fa fa-lock"></i>
						<?php echo __('Manage Account Access'); ?></a>
					</li>
				<?php
	}
				} ?>
	<?php if ($thisstaff->hasPerm(User::PERM_EDIT)) { ?>
				<li>
					<a href="#ajax.php/users/<?php echo $user->getId();
						?>/forms/manage" onclick="javascript:
					$.dialog($(this).attr('href').substr(1), 201);
					return false"
					><i class="fa fa-clipboard"></i>
					<?php echo __('Manage Forms'); ?></a>
				</li>
	<?php } ?>
			</ul>
		</div>	
		<?php }
			if ($thisstaff->hasPerm(User::PERM_DELETE)) { ?>
					<a id="user-delete" class="btn btn-outline button action-button pull-right user-action"
					href="#users/<?php echo $user->getId(); ?>/delete"><i class="fa fa-trash"></i>
					<?php echo __('Delete User'); ?></a>
		<?php } ?>
		<?php if ($thisstaff->hasPerm(User::PERM_MANAGE)) { ?>
					<?php
					if ($account) { ?>
					<a id="user-manage" class="btn btn-outline action-button pull-right user-action"
					href="#users/<?php echo $user->getId(); ?>/manage"><i class="fa fa-pencil-square-o"></i>
					<?php echo __('Manage Account'); ?></a>
					<?php
					} else { ?>
					<a id="user-register" class="btn btn-outline action-button pull-right user-action"
					href="#users/<?php echo $user->getId(); ?>/register"><i class="fa fa-smile-o"></i>
					<?php echo __('Register'); ?></a>
					<?php
					} ?>
		<?php } ?>
				</div>
		</div>
	<div class="panel-body">	
		<div class="row">
		<div class="col-md-2">
			<?php echo $user->getAvatar(); ?>
		</div>
		<div class="col-md-5">
			<div class="form-group row">
				<div class="col-md-3">
					<label class="control-label"><?php echo __('Name'); ?>:</label>
				</div>
				<div class="col-md-9">	
		<?php
			if ($thisstaff->hasPerm(User::PERM_EDIT)) { ?>
					<b><a href="#users/<?php echo $user->getId();
					?>/edit" class="user-action"><i class="fa fa-pencil-square-o"></i>
				
		<?php }
					echo Format::htmlchars($user->getName()->getOriginal());
			if ($thisstaff->hasPerm(User::PERM_EDIT)) { ?>
						</a></b>
	<?php } ?> </div>
			</div>
				<div class="form-group row">
					<div class="col-md-3">	
						<label class="control-label"><?php echo __('Email'); ?>:</label>
					</div>
					<div class="col-md-9">	
							<span id="user-<?php echo $user->getId(); ?>-email"><?php echo $user->getEmail(); ?></span>
					</div>		
				</div>
				<div class="form-group row">
					<div class="col-md-3">	
						<label class="control-label"><?php echo __('Organization'); ?>:</label>
					</div>
					<div class="col-md-9">	
						<span id="user-<?php echo $user->getId(); ?>-org">
							<?php
								if ($org)
									echo sprintf('<a href="#users/%d/org" class="user-action">%s</a>',
											$user->getId(), $org->getName());
								elseif ($thisstaff->hasPerm(User::PERM_EDIT)) {
									echo sprintf(
										'<a href="#users/%d/org" class="user-action">%s</a>',
										$user->getId(),
										__('Add Organization'));
								}
							?>
						</span>
					</div>	
				</div>
			</div>	
			<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-3">	
						<label class="control-label"><?php echo __('Status'); ?>:</label>
					</div>
					<div class="col-md-9">	
						<span id="user-<?php echo $user->getId();
						?>-status"><?php echo $user->getAccountStatus(); ?></span>
					</div>	
				</div>
				<div class="form-group row">
					<div class="col-md-3">
						<label class="control-label"><?php echo __('Created'); ?>:</label>
					</div>
					<div class="col-md-9">	
						<span><?php echo Format::datetime($user->getCreateDate()); ?></span>
					</div>	
				</div>
				<div class="form-group row">
					<div class="col-md-3">
						<label class="control-label"><?php echo __('Updated'); ?>:</label>
					</div>
					<div class="col-md-9">	
						<span><?php echo Format::datetime($user->getUpdateDate()); ?></span>
					</div>	
				</div>
			</div>
		</div>
		<ul class="nav nav-tabs" id="user-view-tabs">
			<li class="active"><a href="#tickets" data-toggle="tab"><i class="fa fa-list-alt"></i>&nbsp;<?php echo __('Tickets'); ?></a></li>
			<li><a href="#notes" data-toggle="tab"><i class="fa fa-thumb-tack"></i>&nbsp;<?php echo __('Notes'); ?></a></li>
		</ul>
		<div class="tab-content">
			<div class="tab-pane fade active in" id="tickets">
			<?php
			include STAFFINC_DIR . 'templates/tickets.tmpl.php';
			?>
			</div>

			<div class="tab-pane fade" id="notes">
			<?php
			$notes = QuickNote::forUser($user);
			$create_note_url = 'users/'.$user->getId().'/note';
			include STAFFINC_DIR . 'templates/notes.tmpl.php';
			?>
			</div>
		</div>
		<div class="hidden dialog panel panel-primary panel-dark m-b-0" id="confirm-action">
			<div class="panel-heading">
				<div class="panel-title">
					<?php echo __('Please Confirm'); ?>
					<a class="close_me pull-right" href=""><i class="fa fa-remove"></i></a>
				</div>
			</div>
			<div class="panel-body">
				<p class="confirm-action" style="display:none;" id="banemail-confirm">
					<?php echo sprintf(__('Are you sure you want to <b>ban</b> %s?'), $user->getEmail()); ?>
					<br><br>
					<?php echo __('New tickets from the email address will be auto-rejected.'); ?>
				</p>
				<p class="confirm-action" style="display:none;" id="confirmlink-confirm">
					<?php echo sprintf(__(
					'Are you sure you want to send an <b>Account Activation Link</b> to <em> %s </em>?'),
					$user->getEmail()); ?>
				</p>
				<p class="confirm-action" style="display:none;" id="pwreset-confirm">
					<?php echo sprintf(__(
					'Are you sure you want to send a <b>Password Reset Link</b> to <em> %s </em>?'),
					$user->getEmail()); ?>
				</p>
				<div><?php echo __('Please confirm to continue.'); ?></div>
				<form action="users.php?id=<?php echo $user->getId(); ?>" method="post" id="confirm-form" name="confirm-form">
					<?php csrf_token(); ?>
					<input type="hidden" name="id" value="<?php echo $user->getId(); ?>">
					<input type="hidden" name="a" value="process">
					<input type="hidden" name="do" id="action" value="">
					<div class="form-group" style="margin-top:20px;">
						<input type="submit" class="btn btn-success" value="<?php echo __('OK'); ?>">
						<input type="button" value="<?php echo __('Cancel'); ?>" class="close_me btn">
					</div>
				</form>
			</div>	
		</div>
	</div>
</div>

<script type="text/javascript">
$(function() {
    $(document).on('click', 'a.user-action', function(e) {
        e.preventDefault();
        var url = 'ajax.php/'+$(this).attr('href').substr(1);
        $.dialog(url, [201, 204], function (xhr) {
            if (xhr.status == 204)
                window.location.href = 'users.php';
            else
                window.location.href = window.location.href;
            return false;
         }, {
            onshow: function() { $('#user-search').focus(); }
         });
        return false;
    });
});
</script>
