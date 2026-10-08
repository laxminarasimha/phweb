<?php
//Note that ticket obj is initiated in tickets.php.
if(!defined('DSKADMININC') || !$thisstaff || !is_object($ticket) || !$ticket->getId()) die('Invalid path');

//Make sure the staff is allowed to access the page.
if(!@$thisstaff->isStaff() || !$ticket->checkStaffPerm($thisstaff)) die('Access Denied');

//Re-use the post info on error...savekeyboards.org (Why keyboard? -> some people care about objects than users!!)
$info=($_POST && $errors)?Format::input($_POST):array();

//Get the goodies.
$dept  = $ticket->getDept();  //Dept
$role  = $thisstaff->getRole($dept);
$staff = $ticket->getStaff(); //Assigned or closed by..
$user  = $ticket->getOwner(); //Ticket User (EndUser)
$team  = $ticket->getTeam();  //Assigned team.
$sla   = $ticket->getSLA();
$lock  = $ticket->getLock();  //Ticket lock obj
if (!$lock && $cfg->getTicketLockMode() == Lock::MODE_ON_VIEW)
    $lock = $ticket->acquireLock($thisstaff->getId());
$mylock = ($lock && $lock->getStaffId() == $thisstaff->getId()) ? $lock : null;
$id    = $ticket->getId();    //Ticket ID.
$msgId = $ticket->getLastMsgId();

//Useful warnings and errors the user might want to know!
if ($ticket->isClosed() && !$ticket->isReopenable())
    $warn = sprintf(
            __('Current ticket status (%s) does not allow the end user to reply.'),
            $ticket->getStatus());
elseif ($ticket->isAssigned()
        && (($staff && $staff->getId()!=$thisstaff->getId())
            || ($team && !$team->hasMember($thisstaff))
        ))
    $warn.= sprintf('&nbsp;&nbsp;<span class="Icon assignedTicket">%s</span>',
            sprintf(__('Ticket is assigned to %s'),
                implode('/', $ticket->getAssignees())
                ));

if (empty($errors['err'])) {

    if ($lock && $lock->getStaffId()!=$thisstaff->getId())
        $errors['err'] = sprintf(__('%s is currently locked by %s'),
                __('This ticket'),
                $lock->getStaffName());
    elseif (($emailBanned=Banlist::isBanned($ticket->getEmail())))
        $errors['err'] = __('Email is in banlist! Must be removed before any reply/response');
    elseif (!Validator::is_valid_email($ticket->getEmail()))
        $errors['err'] = __('EndUser email address is not valid! Consider updating it before responding');
}

$unbannable=($emailBanned) ? BanList::includes($ticket->getEmail()) : false;

if($ticket->isOverdue())
    $warn.='<br /><span class="label label-danger">'.__('Marked overdue!').'</span>';

?>
<div class="panel">
   <div class="panel-heading">
		<div class="pull-left flush-left">	
			 <div class="panel-title table-caption"><a href="tickets.php?id=<?php echo esc_attr($ticket->getId()); ?>"
			 title="<?php echo __('Reload'); ?>"><i class="fa fa-refresh text-primary"></i>
			 </a><?php echo sprintf(__('Ticket #%s'), esc_html($ticket->getNumber())); ?>
			</div>
		</div>
	<div class="panel-heading-controls">
		<?php
		if ($thisstaff->hasPerm(Email::PERM_BANLIST)
				|| $role->hasPerm(TicketModel::PERM_EDIT)
				|| ($dept && $dept->isManager($thisstaff))) { ?>
		<div class="btn-group">		
			<button type="button" class="btn btn-outline action-button pull-right dropdown-toggle" data-toggle="dropdown" title="<?php echo __('More');?>">
				<i class="fa fa-caret-down pull-right"></i>
				<span ><i class="fa fa-cog"></i></span>
			</button>
		<ul class="dropdown-menu">
			<?php
			 if ($role->hasPerm(TicketModel::PERM_EDIT)) { ?>
				<li>
				<a class="change-user" href="#tickets/<?php
				echo esc_attr($ticket->getId()); ?>/change-user"><i class="fa fa-user"></i> <?php
					echo __('Change Owner'); ?></a>
				</li>
			<?php
			 }

			 if($ticket->isOpen() && ($dept && $dept->isManager($thisstaff))) {

				if($ticket->isAssigned()) { ?>
					<li>
						<a  class="confirm-action" id="ticket-release" href="#release"><i class="fa fa-user"></i> <?php
						echo __('Release (unassign) Ticket'); ?></a>
					</li>
				<?php
				}

				if(!$ticket->isOverdue()) { ?>
					<li>
						<a class="confirm-action" id="ticket-overdue" href="#overdue"><i class="fa fa-bell"></i> <?php
						echo __('Mark as Overdue'); ?></a>
					</li>
				<?php
				}

				if($ticket->isAnswered()) { ?>
					<li>
						<a class="confirm-action" id="ticket-unanswered" href="#unanswered"><i class="fa fa-arrow-circle-left"></i> <?php
							echo __('Mark as Unanswered'); ?></a>
					</li>
				<?php
				} else { ?>
					<li>
						<a class="confirm-action" id="ticket-answered" href="#answered"><i class="fa fa-arrow-circle-right"></i> <?php
							echo __('Mark as Answered'); ?></a>
					</li>
				<?php
				}
			} ?>
			<?php
			if ($role->hasPerm(Ticket::PERM_EDIT)) { ?>
				<li>
				<a href="#ajax.php/tickets/<?php echo esc_attr($ticket->getId());
					?>/forms/manage" onclick="javascript:
					$.dialog($(this).attr('href').substr(1), 201);
					return false"
					><i class="fa fa-clipboard"></i> <?php echo __('Manage Forms'); ?></a>
				</li>
			<?php
			} ?>

			<?php if ($thisstaff->hasPerm(Email::PERM_BANLIST)) {
				 if(!$emailBanned) {?>
				<li>
					<a class="confirm-action" id="ticket-banemail" href="#banemail"	><i class="fa fa-ban"></i> <?php echo sprintf( Format::htmlchars(__('Ban Email <%s>')), $ticket->getEmail()); ?>
					</a>
				</li>
			<?php
				 } elseif($unbannable) { ?>
					<li><a  class="confirm-action" id="ticket-banemail"
						href="#unbanemail"><i class="fa fa-undo"></i> <?php echo sprintf(
							Format::htmlchars(__('Unban Email <%s>')),
							$ticket->getEmail()); ?></a></li>
				<?php
				 }
			  }
			  if ($role->hasPerm(TicketModel::PERM_DELETE)) {
				 ?>
				<li class="danger">
					<a class="ticket-action" href="#tickets/<?php
					echo esc_attr($ticket->getId()); ?>/status/delete"
					data-redirect="tickets.php"><i class="fa fa-trash"></i> <?php
					echo __('Delete Ticket'); ?></a>
				</li>
			<?php
			 }
			?>
		  </ul>
	  </div>
		<?php
		}
		if ($role->hasPerm(TicketModel::PERM_EDIT)) { ?>
			<a class="btn btn-outline action-button pull-right" data-placement="bottom" data-toggle="tooltip" title="<?php echo __('Edit'); ?>" href="tickets.php?id=<?php echo esc_attr($ticket->getId()); ?>&a=edit"><i class="fa fa-pencil-square-o"></i></a>
		<?php
		} ?>
		<div class="btn-group">
			<button type="button" class="btn btn-outline action-button pull-right dropdown-toggle" data-toggle="dropdown" title="<?php echo __('Print'); ?>">
				<i class="fa fa-caret-down pull-right"></i>
				<a id="ticket-print" href="tickets.php?id=<?php echo esc_attr($ticket->getId()); ?>&a=print"><i class="fa fa-print"></i></a>
			</button>
		  <ul class="dropdown-menu">
			 <li>
				<a class="no-pjax" target="_blank" href="tickets.php?id=<?php echo esc_attr($ticket->getId()); ?>&a=print&notes=0"><i class="fa fa-file-o"></i> <?php echo __('Ticket Thread'); ?>
				</a>
			</li>	
			 <li>
				<a class="no-pjax" target="_blank" href="tickets.php?id=<?php echo esc_attr($ticket->getId()); ?>&a=print&notes=1"><i class="fa fa-file-text-o"></i> <?php echo __('Thread + Internal Notes'); ?>
				</a>
			</li>	
		  </ul>
		</div>
		<?php
		// Transfer
		if ($role->hasPerm(TicketModel::PERM_TRANSFER)) {?>
		<a class="btn action-button pull-right ticket-action" id="ticket-transfer" data-placement="bottom" data-toggle="tooltip" title="<?php echo __('Transfer'); ?>" data-redirect="tickets.php" href="#tickets/<?php echo esc_attr($ticket->getId()); ?>/transfer"><i class="fa fa-share-square-o"></i></a>
		<?php
		} ?>

		<?php
		// Assign
		if ($ticket->isOpen() && $role->hasPerm(TicketModel::PERM_ASSIGN)) {?>
		<div class="btn-group">
			<button type="button" class="btn btn-outline pull-right dropdown-toggle" data-toggle="dropdown" title=" <?php echo $ticket->isAssigned() ? __('Assign') : __('Reassign'); ?>"><i class="fa fa-caret-down pull-right"></i><a class="ticket-action" id="ticket-assign" data-redirect="tickets.php" href="#tickets/<?php echo esc_attr($ticket->getId()); ?>/assign"><i class="fa fa-user"></i></a>
			</button>
			<ul class="dropdown-menu">
			<?php
			// Agent can claim team assigned ticket
			if (!$ticket->getStaff()
					&& (!$dept->assignMembersOnly()
						|| $dept->isMember($thisstaff))
					) { ?>
				 <li>
					<a class="no-pjax ticket-action" data-redirect="tickets.php"
					href="#tickets/<?php echo esc_attr($ticket->getId()); ?>/claim"><i class="fa fa-chevron-circle-down"></i> <?php echo __('Claim'); ?>
					</a>
				</li>
				<?php
				} ?>
				 <li>
					<a class="no-pjax ticket-action" data-redirect="tickets.php"
					href="#tickets/<?php echo esc_attr($ticket->getId()); ?>/assign/agents"><i class="fa fa-user"></i> <?php echo __('Agent'); ?></a>
				</li>
				 <li>
					<a class="no-pjax ticket-action" data-redirect="tickets.php"
					href="#tickets/<?php echo esc_attr($ticket->getId()); ?>/assign/teams"><i class="fa fa-users"></i> <?php echo __('Team'); ?></a>
				</li>
			</ul>
		  
		</div>
		<?php
		} ?>
			<?php
			if ($role->hasPerm(TicketModel::PERM_REPLY)) { ?>
			<a href="#post-reply" class="btn btn-outline post-response action-button"
			data-placement="bottom" data-toggle="tooltip"
			title="<?php echo __('Post Reply'); ?>"><i class="fa fa-reply"></i></a>
			<?php
			} ?>
			<a href="#post-note" id="post-note" class="btn btn-outline post-response action-button"
			data-placement="bottom" data-toggle="tooltip"
			title="<?php echo __('Post Internal Note'); ?>"><i class="fa fa-file-text-o"></i></a>
			<?php // Status change options
			echo TicketStatus::status_options();
			?>
	   </div>
	  </div> 
	<div class="panel-body"> 
		<div class="col-sm-12 font-size-14 m-b-2 font-weight-semibold bg-white darken p-y-1">
			<div>Subject : 
			<?php $subject_field = TicketForm::getInstance()->getField('subject');
				echo $subject_field->display($ticket->getSubject()); ?>
			</div>
		</div>
    <div class="row">
        <div class="col-md-6">
			<fieldset style="margin-bottom:5px;">
				<div class="col-md-3">
					<label class="control-label"><?php echo __('Status');?>:</label>
				</div>
				<div class="col-md-9">
					<?php echo ($S = $ticket->getStatus()) ? $S->display() : ''; ?>
				</div>
			</fieldset>
			<fieldset style="margin-bottom:5px;">
				<div class="col-md-3">
					<label class="control-label"><?php echo __('Priority');?>:</label>
				</div>
				<div class="col-md-9">
					<?php echo $ticket->getPriority(); ?>
				</div>
			</fieldset>
			<fieldset style="margin-bottom:5px;">
				<div class="col-md-3">
					<label class="control-label"><?php echo __('Department');?>:</label>
				</div>
				<div class="col-md-9">
					<?php echo Format::htmlchars($ticket->getDeptName()); ?>
				</div>
			</fieldset>
			<fieldset style="margin-bottom:5px;">
				<div class="col-md-3">
					<label class="control-label"><?php echo __('Create Date');?>:</label>
				</div>
				<div class="col-md-9">
					<?php echo Format::datetime($ticket->getCreateDate()); ?>
				</div>
			</fieldset>
        </div>
        <div class="col-md-6">
                <fieldset style="margin-bottom:5px;">
                    <div class="col-md-3">
						<label class="control-label"><?php echo __('User'); ?>:</label>
					</div>
                    <div class="col-md-9">
						<a href="#tickets/<?php echo esc_attr($ticket->getId()); ?>/user"
                        onclick="javascript:
                            $.userLookup('ajax.php/tickets/<?php echo esc_js($ticket->getId()); ?>/user',
                                    function (user) {
                                        $('#user-'+user.id+'-name').text(user.name);
                                        $('#user-'+user.id+'-email').text(user.email);
                                        $('#user-'+user.id+'-phone').text(user.phone);
                                        $('select#emailreply option[value=1]').text(user.name+' <'+user.email+'>');
                                    });
                            return false;
                            "><i class="fa fa-user"></i> <span id="user-<?php echo esc_attr($ticket->getOwnerId()); ?>-name"
                            ><?php echo Format::htmlchars($ticket->getName());
                        ?></span>
						</a>
                        <?php
                        if ($user) { ?>
						<div class="btn-group">
                            <a class="button" href="tickets.php?<?php echo Http::build_query(array(
                                'status'=>'open', 'a'=>'search', 'uid'=> $user->getId()
                            )); ?>" title="<?php echo __('Related Tickets'); ?>" data-toggle="dropdown">
                            (<b><?php echo $user->getNumTickets(); ?></b>)
                            </a>
                                <ul class="dropdown-menu">
                                    <?php
                                    if(($open=$user->getNumOpenTickets()))
                                        echo sprintf('<li><a href="tickets.php?a=search&status=open&uid=%s"><i class="fa fa-folder-open"></i> %s</a></li>',
                                                esc_attr($user->getId()), sprintf(_N('%d Open Ticket', '%d Open Tickets', $open), $open));

                                    if(($closed=$user->getNumClosedTickets()))
                                        echo sprintf('<li><a href="tickets.php?a=search&status=closed&uid=%d"><i class="fa fa-folder"></i> %s</a></li>',
                                                esc_attr($user->getId()), sprintf(_N('%d Closed Ticket', '%d Closed Tickets', $closed), $closed));
                                    ?>
                                    <li><a href="tickets.php?a=search&uid=<?php echo esc_attr($ticket->getOwnerId()); ?>"><i class="fa fa-angle-double-right"></i> <?php echo __('All Tickets'); ?></a></li>
			<?php   if ($thisstaff->hasPerm(User::PERM_DIRECTORY)) { ?>
									<li><a href="users.php?id=<?php echo esc_attr($user->getId()); ?>"><i class="fa fa-user"></i> <?php echo __('Manage User'); ?></a></li>
						<?php   } ?>
							</ul>
						</div>
			<?php   } # end if ($user) ?>
                    </div>
                </fieldset>
                <fieldset style="margin-bottom:5px;">
                    <div class="col-md-3">
						<label class="control-label"><?php echo __('Email'); ?>:</label>
					</div>
                    <div class="col-md-9">
                        <span id="user-<?php echo esc_attr($ticket->getOwnerId()); ?>-email"><?php echo esc_html($ticket->getEmail()); ?></span>
                    </div>
                </fieldset>
			<?php   if ($user->getOrganization()) { ?>
                <fieldset style="margin-bottom:5px;">
                    <div class="col-md-3">
						<label class="control-label"><?php echo __('Organization'); ?>:</label>
					</div>
                    <div class="col-md-9">
						<i class="fa fa-building"></i>
						<?php echo Format::htmlchars($user->getOrganization()->getName()); ?>
					<div class="btn-group">
                            <a class="button" href="tickets.php?<?php echo Http::build_query(array(
                                'status'=>'open', 'a'=>'search', 'orgid'=> $user->getOrgId()
                            )); ?>" title="<?php echo __('Related Tickets'); ?>"data-toggle="dropdown"> (<b><?php echo esc_html($user->getNumOrganizationTickets()); ?></b>)
                        </a>
							<ul class="dropdown-menu">
			<?php   if ($open = $user->getNumOpenOrganizationTickets()) { ?>
								<li>
									<a href="tickets.php?<?php echo Http::build_query(array(
                                        'a' => 'search', 'status' => 'open', 'orgid' => $user->getOrgId()
                                    )); ?>"><i class="fa fa-folder-open"></i>
                                    <?php echo sprintf(_N('%d Open Ticket', '%d Open Tickets', $open), $open); ?>
                                    </a>
								</li>
			<?php   }
				if ($closed = $user->getNumClosedOrganizationTickets()) { ?>
								<li>
									<a href="tickets.php?<?php echo Http::build_query(array(
									'a' => 'search', 'status' => 'closed', 'orgid' => $user->getOrgId()
								)); ?>"><i class="fa fa-folder"></i>
								<?php echo sprintf(_N('%d Closed Ticket', '%d Closed Tickets', $closed), $closed); ?>
									</a>
								</li>
								<li>
									<a href="tickets.php?<?php echo Http::build_query(array(
									'a' => 'search', 'orgid' => $user->getOrgId()
								)); ?>"><i class="fa fa-angle-double-right"></i> <?php echo __('All Tickets'); ?>
									</a>
								</li>
			<?php   }
				if ($thisstaff->hasPerm(User::PERM_DIRECTORY)) { ?>
								<li><a href="orgs.php?id=<?php echo esc_attr($user->getOrgId()); ?>"><i class="fa fa-building"></i> <?php
									echo __('Manage Organization'); ?>
									</a>
								</li>
			<?php   } ?>
							</ul>
						</div>
					</div>
				</fieldset>
			<?php   } # end if (user->org) ?>
                <fieldset style="margin-bottom:5px;">
                    <div class="col-md-3">
						<label class="control-label"><?php echo __('Source'); ?>:</label>
					</div>
                    <div class="col-md-9"><?php
                        echo Format::htmlchars($ticket->getSource());

                        if (!strcasecmp($ticket->getSource(), 'Web') && $ticket->getIP())
                            echo '&nbsp;&nbsp; <span class="faded">('.Format::htmlchars($ticket->getIP()).')</span>';
                        ?>
                    </div>
                </fieldset>
			</div>
		</div>
	<div class="row">
		<div class="col-md-6">
			<?php if($ticket->isOpen()) { ?>
			<fieldset style="margin-bottom:5px;">
				<div class="col-md-3">
					<label class="control-label"><?php echo __('Assigned To');?>:</label>
				</div>
				<div class="col-md-9">
					<?php
					if($ticket->isAssigned())
						echo Format::htmlchars(implode('/', $ticket->getAssignees()));
					else
						echo '<span class="faded">&mdash; '.__('Unassigned').' &mdash;</span>';
					?>
				</div>
			</fieldset>
			<?php } else { ?>
			<fieldset style="margin-bottom:5px;">
				<div class="col-md-3">
					<label class="control-label"><?php echo __('Closed By');?>:</label>
				</div>
				<div class="col-md-9">
					<?php
					if(($staff = $ticket->getStaff()))
						echo Format::htmlchars($staff->getName());
					else
						echo '<span class="faded">&mdash; '.__('Unknown').' &mdash;</span>';
					?>
				</div>
			</fieldset>
			<?php } ?>
			<fieldset style="margin-bottom:5px;">
				<div class="col-md-3">
					<label class="control-label"><?php echo __('SLA Plan');?>:</label>
				</div>
				<div class="col-md-9">
					<?php echo $sla?Format::htmlchars($sla->getName()):'<span class="faded">&mdash; '.__('None').' &mdash;</span>'; ?>
				</div>
			</fieldset>
			<?php if($ticket->isOpen()){ ?>
			<fieldset style="margin-bottom:5px;">
				<div class="col-md-3">
					<label class="control-label"><?php echo __('Due Date');?>:</label>
				</div>
				<div class="col-md-9">
					<?php echo Format::datetime($ticket->getEstDueDate()); ?>
				</div>
			</fieldset>
			<?php }else { ?>
			<fieldset style="margin-bottom:5px;">
				<div class="col-md-3">
					<label class="control-label"><?php echo __('Close Date');?>:</label>
				</div>
				<div class="col-md-9">
					<?php echo Format::datetime($ticket->getCloseDate()); ?>
				</div>
			</fieldset>
			<?php	} ?>
		</div>
		<div class="col-md-6">
			<fieldset style="margin-bottom:5px;">
				<div class="col-md-3">
					<label class="control-label"><?php echo __('Department');?>:</label>
				</div>
				<div class="col-md-9">
					<?php echo Format::htmlchars($ticket->getDeptName()); ?>
				</div>
			</fieldset>
			<fieldset style="margin-bottom:5px;">
				<div class="col-md-3">
					<label class="control-label"><?php echo __('Last Message');?>:</label>
				</div>
				<div class="col-md-9">
				<?php echo Format::datetime($ticket->getLastMsgDate()); ?>
				</div>
			</fieldset>
			<fieldset style="margin-bottom:5px;">
				<div class="col-md-3" style="margin-bottom:5px;">
					<label class="control-label"><?php echo __('Last Response');?>:</label>
				</div>
				<div class="col-md-9" style="margin-bottom:5px;">
					<?php echo (!empty($ticket->getLastRespDate()) ? Format::datetime($ticket->getLastRespDate()) : __('&mdash; No response yet &mdash;')); ?>
				</div>
			</fieldset>
		</div>
	</div>

<?php
foreach (DynamicFormEntry::forTicket($ticket->getId()) as $form) {
    // Skip core fields shown earlier in the ticket view
    // TODO: Rewrite getAnswers() so that one could write
    //       ->getAnswers()->filter(not(array('field__name__in'=>
    //           array('email', ...))));
    $answers = $form->getAnswers()->exclude(Q::any(array(
        'field__flags__hasbit' => DynamicFormField::FLAG_EXT_STORED,
        'field__name__in' => array('subject', 'priority')
    )));
    $displayed = array();
    foreach($answers as $a) {
        if (!($v = $a->display()))
            continue;
        $displayed[] = array($a->getLocal('label'), $v);
    }
    if (count($displayed) == 0)
        continue;
    ?>
	<div class="table-light table-responsive">
		<table class="table table-bordered custom-data" cellspacing="0" cellpadding="0" width="940" border="0">
			<thead>
				<tr><th colspan="2"><?php echo Format::htmlchars($form->getTitle()); ?></th></tr>
			</thead>
			<tbody>
				<?php
					foreach ($displayed as $stuff) {
						list($label, $v) = $stuff;
				?>
						<tr>
							<th width="200"><?php
				echo Format::htmlchars($label);
							?></th>
							<td><?php
				echo $v;
							?></td>
						</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<?php } ?>
	
	<?php
		$tcount = $ticket->getThreadEntries($types ?? false)->count();
	
	?>
	<ul class="nav nav-tabs" id="ticket_tabs" >
		<li class="active">
			<a data-toggle="tab" id="ticket-thread-tab" href="#ticket_thread"><?php
			echo sprintf(__('Ticket Thread (%d)'), $tcount); ?>
			</a>
		</li>
	</ul>

	<div id="ticket_tabs_container" class="tab-content">
	<div id="ticket_thread" class="tab-pane fade in active">
	<?php
		// Render ticket thread
		$ticket->getThread()->render(
				array('M', 'R', 'N'),
				array(
					'html-id'   => 'ticketThread',
					'mode'      => Thread::MODE_STAFF,
					'sort'      => $thisstaff->thread_view_order
					)
				);
	?>
	<?php
	if (!empty($errors['err']) && isset($_POST['a'])) {
		// Reflect errors back to the tab.
		$errors[$_POST['a']] = $errors['err'];
	} elseif($msg) { ?>
		<div id="msg_notice"><?php echo $msg; ?></div>
	<?php
	} elseif($warn) { ?>
		<div id="msg_warning"><?php $warn ?></div>
	<?php
	} ?>
	
<br/>

<div class="sticky bar stop actions" id="response_options">

		<ul class="nav nav-tabs" id="response-tabs">
			<?php
			if ($role->hasPerm(TicketModel::PERM_REPLY)) { ?>
			<li class="active <?php 
					echo isset($errors['reply']) ? 'text-white bg-danger' : ''; ?>">
				<a data-toggle="tab" href="#reply" id="post-reply-tab"><?php echo __('Post Reply');?>
				</a>
			</li>
				<?php
				} ?>
			<li>
				<a data-toggle="tab" href="#note" <?php
				echo isset($errors['postnote']) ?  'class="text-white bg-danger"' : ''; ?>
				id="post-note-tab"><?php echo __('Post Internal Note');?>
				</a>
			</li>
		</ul>
		<?php
		if ($role->hasPerm(TicketModel::PERM_REPLY)) { ?>
		<div class="tab-content">
		<form id="reply" class="tab-pane fade in exclusive active"
			data-lock-object-id="ticket/<?php echo esc_attr($ticket->getId()); ?>"
			data-lock-id="<?php echo esc_attr($mylock ? $mylock->getId() : ''); ?>"
			action="tickets.php?id=<?php
			echo esc_attr($ticket->getId()); ?>#reply" name="reply" method="post" enctype="multipart/form-data">
			<?php csrf_token(); ?>
			<input type="hidden" name="id" value="<?php echo esc_attr($ticket->getId()); ?>">
			<input type="hidden" name="msgId" value="<?php echo esc_attr($msgId); ?>">
			<input type="hidden" name="a" value="reply">
			<input type="hidden" name="lockCode" value="<?php echo esc_attr($mylock ? $mylock->getCode() : ''); ?>">
			<?php
				if(!empty($errors['reply'])){
					echo '<div class="alert-danger">&nbsp;'.esc_html($errors['reply']).'</div>';
				}
				?>
				<fieldset class="form-group form-inline">
					<div class="col-md-2">
						<label class="control-label"><?php echo __('To'); ?>:</label>
					</div>
					<div class="col-md-10">
						<?php
						# XXX: Add user-to-name and user-to-email HTML ID#s
						$to =sprintf('%s &lt;%s&gt;',
								Format::htmlchars($ticket->getName()),
								esc_html($ticket->getReplyToEmail()));
						$emailReply = (!isset($info['emailreply']) || $info['emailreply']);
						?>
						<select class="form-control" id="emailreply" name="emailreply">
							<option value="1" <?php echo $emailReply ?  'selected="selected"' : ''; ?>><?php echo $to; ?></option>
							<option value="0" <?php echo !$emailReply ? 'selected="selected"' : ''; ?>
							>&mdash; <?php echo __('Do Not Email Reply'); ?> &mdash;</option>
						</select>
					</div>
				</fieldset>
				<?php
				if(1) { //Make CC optional feature? NO, for now.
					?>
				<div id="cc_sec" style="display:<?php echo $emailReply?  'block':'none'; ?>;">
				 <fieldset class="form-group">
					<div class="col-md-2">
						<label class="control-label"><?php echo __('Collaborators'); ?>:</label>
					</div>
					<div class="col-md-10">
						<input type='checkbox' value='1' name="emailcollab"
						id="t<?php echo esc_attr($ticket->getThreadId()); ?>-emailcollab"
							<?php echo ((empty($info['emailcollab']) && !$errors) || isset($info['emailcollab']))?'checked="checked"':''; ?>
							style="display:<?php echo $ticket->getThread()->getNumCollaborators() ? 'inline-block': 'none'; ?>;"
							>
						<?php
						$recipients = __('Add Recipients');
						if ($ticket->getThread()->getNumCollaborators())
							$recipients = sprintf(__('Recipients (%d of %d)'),
									$ticket->getThread()->getNumActiveCollaborators(),
									$ticket->getThread()->getNumCollaborators());

						echo sprintf('<span><a class="collaborators preview"
								href="#thread/%d/collaborators"><span id="t%d-recipients">%s</span></a></span>',
								$ticket->getThreadId(),
								$ticket->getThreadId(),
								$recipients);
					   ?>
					</div>
				 </fieldset>
				</div>
				<?php
				} ?>
				<div id="resp_sec">
				<fieldset class="form-group form-inline">
					<div class="col-md-2">
						<label class="control-label"><?php echo __('Response');?>:</label>
					</div>
					<div class="col-md-10">
		<?php if ($cfg->isCannedResponseEnabled()) { ?>
						<select class="form-control" id="cannedResp" name="cannedResp">
							<option value="0" selected="selected"><?php echo __('Select a canned response');?></option>
							<option value='original'><?php echo __('Original Message'); ?></option>
							<option value='lastmessage'><?php echo __('Last Message'); ?></option>
							<?php
							if(($cannedResponses=Canned::responsesByDeptId($ticket->getDeptId()))) {
								echo '<option value="0" disabled="disabled">
									------------- '.__('Premade Replies').' ------------- </option>';
								foreach($cannedResponses as $id =>$title)
									echo sprintf('<option value="%d">%s</option>',$id,Format::htmlchars($title));
							}
							?>
						</select>
						<?php
							if(!empty($errors['response'])){
								echo '<div class="alert-danger">&nbsp;'.esc_html($errors['response']).'</div>';
							}
						?>
						<br /><br />
	<?php } # endif (canned-resonse-enabled)
						$signature = '';
						switch ($thisstaff->getDefaultSignatureType()) {
						case 'dept':
							if ($dept && $dept->canAppendSignature())
							   $signature = $dept->getSignature();
						   break;
						case 'mine':
							$signature = $thisstaff->getSignature();
							break;
						} ?>
						<input type="hidden" name="draft_id" value=""/>
					<textarea name="response" id="response" cols="50"
                        data-signature-field="signature" data-dept-id="<?php echo $dept->getId(); ?>"
                        data-signature="<?php
                            echo Format::htmlchars(Format::viewableImages($signature)); ?>"
                        placeholder="<?php echo __(
                        'Start writing your response here. Use canned responses from the drop-down above'
                        ); ?>"
                        rows="9" wrap="soft"
                        class="<?php if ($cfg->isRichTextEnabled()) echo 'summernote-base';
                            ?> draft draft-delete" <?php
    list($draft, $attrs) = Draft::getDraftAndDataAttrs('ticket.response', $ticket->getId(), $info['response'] ?? null);
    echo $attrs; ?>><?php echo $_POST ? ($info['response'] ?? '') : $draft;
                    ?></textarea>
	
					<div id="reply_form_attachments" class="attachments">
					<?php
						print $response_form->getField('attachments')->render();
					?>
					</div>
					</div>
				</fieldset>
				<fieldset>
					 <div class="col-md-2">
						<label for="signature" class="control-label left"><?php echo __('Signature');?>:</label>
					</div>
					<div class="col-md-10">
						<?php
						$info['signature']=$info['signature']??$thisstaff->getDefaultSignatureType();
						?>
						<label><input type="radio" name="signature" value="none" checked="checked"> <?php echo __('None');?></label>
						<?php
						if($thisstaff->getSignature()) {?>
						<label><input type="radio" name="signature" value="mine"
							<?php echo ($info['signature']=='mine')?'checked="checked"':''; ?>> <?php echo __('My Signature');?></label>
						<?php
						} ?>
						<?php
						if($dept && $dept->canAppendSignature()) { ?>
						<label><input type="radio" name="signature" value="dept"
							<?php echo ($info['signature']=='dept')?'checked="checked"':''; ?>>
							<?php echo sprintf(__('Department Signature (%s)'), Format::htmlchars($dept->getName())); ?></label>
						<?php
						} ?>
					</div>
				</fieldset>
				<br />
				<fieldset class="form-group form-inline">
					<div class="col-md-2" style="vertical-align:top">
						<label class="control-label"><?php echo __('Ticket Status');?>:</label>
					</div>
					<div class="col-md-10">
						<?php
						$outstanding = false;
						if ($role->hasPerm(TicketModel::PERM_CLOSE)
								&& is_string($warning=$ticket->isCloseable())) {
							$outstanding =  true;
							echo sprintf('<div class="warning-banner">%s</div>', $warning);
						} ?>
						<select class="form-control" name="reply_status_id">
						<?php
						$statusId = $info['reply_status_id'] ?? $ticket->getStatusId();
						$states = array('open', 'pending');
						if ($role->hasPerm(TicketModel::PERM_CLOSE) && !$outstanding)
							$states = array_merge($states, array('closed'));

						foreach (TicketStatusList::getStatuses(
									array('states' => $states)) as $s) {
							if (!$s->isEnabled()) continue;
							$selected = ($statusId == $s->getId());
							echo sprintf('<option value="%d" %s>%s%s</option>',
									$s->getId(),
									$selected
									 ? 'selected="selected"' : '',
									__($s->getName()),
									$selected
									? (' ('.__('current').')') : ''
									);
						}
						?>
						</select>
					</div>
				</fieldset>
			 </div>
			<div  class="form-group" style="text-align:center;margin-top:20px;">
				<input class="btn btn-success save pending" type="submit" value="<?php echo __('Post Reply');?>">
				<input class="btn btn-info" type="reset" value="<?php echo __('Reset');?>">
			</div>
		</form>
	<?php	} ?>
		<form id="note" class="tab-pane fade exclusive"
			data-lock-object-id="ticket/<?php echo esc_attr($ticket->getId()); ?>"
			data-lock-id="<?php echo esc_attr($mylock ? $mylock->getId() : ''); ?>"
			action="tickets.php?id=<?php echo esc_attr($ticket->getId()); ?>#note"
			name="note" method="post" enctype="multipart/form-data">
			<?php csrf_token(); ?>
			<input type="hidden" name="id" value="<?php echo esc_attr($ticket->getId()); ?>">
			<input type="hidden" name="locktime" value="<?php echo esc_attr($cfg->getLockTime() * 60); ?>">
			<input type="hidden" name="a" value="postnote">
			<input type="hidden" name="lockCode" value="<?php echo esc_attr($mylock ? $mylock->getCode() : ''); ?>">
			<div>
			<?php
				if(!empty($errors['postnote'])){
					echo '<div class="alert-danger">&nbsp;'.esc_html($errors['postnote']).'</div>';
				}
			?>
				<fieldset class="form-group form-inline">
					<div class="col-md-2">
						<label class="control-label required"><?php echo __('Internal Note'); ?>:</label>
					</div>
					<div class="col-md-10">
							<div class="faded" style="padding-left:0.15em"><?php
							echo __('Note title - summary of the note (optional)'); ?></div>
							<input class="form-control" type="text" name="title" id="title" size="60" value="<?php echo esc_attr($info['title'] ?? ''); ?>" >
							<br/>
							<?php
								if(!empty($errors['title'])){
									echo '<div class="alert-danger">&nbsp;'.esc_html($errors['title']).'</div>';
								}
							?>
						<br/>
						<?php
							if(!empty($errors['note'])){
								echo '<div class="alert-danger">&nbsp;'.esc_html($errors['note']).'</div>';
							}
						?>
						<textarea name="note" cols="80"
							placeholder="<?php echo __('Note details'); ?>"
							rows="9" wrap="soft"
							class="<?php if ($cfg->isRichTextEnabled()) echo 'summernote-base';
								?> draft draft-delete" <?php
		list($draft, $attrs) = Draft::getDraftAndDataAttrs('ticket.note', $ticket->getId(), $info['note'] ?? null);
		echo $attrs; ?>><?php echo $_POST ? ($info['note'] ?? '') : $draft;
							?></textarea>
					<div class="attachments">
					<?php
						print $note_form->getField('attachments')->render();
					?>
					</div>
					</div>
				</fieldset>
				<fieldset class="form-group form-inline">
					<div class="col-md-2">
						<label class="control-label required"><?php echo __('Ticket Status');?>:</label>
					</div>
					<div class="col-md-10">
						<div class="faded"></div>
						<select class="form-control" name="note_status_id">
							<?php
							$statusId = $info['note_status_id'] ?? $ticket->getStatusId();
							$states = array('open');
							if ($ticket->isCloseable() === true
									&& $role->hasPerm(TicketModel::PERM_CLOSE))
								$states = array_merge($states, array('closed'));
							foreach (TicketStatusList::getStatuses(
										array('states' => $states)) as $s) {
								if (!$s->isEnabled()) continue;
								$selected = $statusId == $s->getId();
								echo sprintf('<option value="%d" %s>%s%s</option>',
										$s->getId(),
										$selected ? 'selected="selected"' : '',
										__($s->getName()),
										$selected ? (' ('.__('current').')') : ''
										);
							}
							?>
						</select>
						<?php
							if(!empty($errors['note_status_id'])){
								echo '<div class="alert-danger">&nbsp;'.esc_html($errors['note_status_id']).'</div>';
							}
						?>
					</div>
				</fieldset>
			</div>

		   <div class="form-group" style="text-align:center;margin-top:20px;">
			   <input class="btn btn-success save pending" type="submit" value="<?php echo __('Post Note');?>">
			   <input class="btn btn-info" type="reset" value="<?php echo __('Reset');?>">
		   </div>
	   </form>
	 </div>
 </div>
 </div>
</div>
 </div>
</div>
<div style="display:none;" class="dialog panel panel-primary panel-dark" id="confirm-action">
	<div class="panel-heading">
		<div class="panel-title"><?php echo __('Please Confirm');?>
		<a class="close_me pull-right" href=""><i class="fa fa-remove"></i></a></div>
	</div>
	<div class="panel-body">	
    <p class="confirm-action" style="display:none;" id="claim-confirm">
        <?php echo sprintf(__('Are you sure you want to <b>claim</b> (self assign) %s?'), __('this ticket'));?>
    </p>
    <p class="confirm-action" style="display:none;" id="answered-confirm">
        <?php echo __('Are you sure you want to flag the ticket as <b>answered</b>?');?>
    </p>
    <p class="confirm-action" style="display:none;" id="unanswered-confirm">
        <?php echo __('Are you sure you want to flag the ticket as <b>unanswered</b>?');?>
    </p>
    <p class="confirm-action" style="display:none;" id="overdue-confirm">
        <?php echo __('Are you sure you want to flag the ticket as <font color="red"><b>overdue</b></font>?');?>
    </p>
    <p class="confirm-action" style="display:none;" id="banemail-confirm">
        <?php echo sprintf(__('Are you sure you want to <b>ban</b> %s?'), $ticket->getEmail());?> <br><br>
        <?php echo __('New tickets from the email address will be automatically rejected.');?>
    </p>
    <p class="confirm-action" style="display:none;" id="unbanemail-confirm">
        <?php echo sprintf(__('Are you sure you want to <b>remove</b> %s from ban list?'), $ticket->getEmail()); ?>
    </p>
    <p class="confirm-action" style="display:none;" id="release-confirm">
        <?php echo sprintf(__('Are you sure you want to <b>unassign</b> ticket from <b>%s</b>?'), esc_html($ticket->getAssigned())); ?>
    </p>
    <p class="confirm-action" style="display:none;" id="changeuser-confirm">
        <span id="msg_warning" style="display:block;vertical-align:top">
        <?php echo sprintf(Format::htmlchars(__('%s <%s> will longer have access to the ticket')),
            '<b>'.Format::htmlchars($ticket->getName()).'</b>', Format::htmlchars($ticket->getEmail())); ?>
        </span>
        <?php echo sprintf(__('Are you sure you want to <b>change</b> ticket owner to %s?'),
            '<b><span id="newuser">this guy</span></b>'); ?>
    </p>
    <p class="confirm-action" style="display:none;" id="delete-confirm">
        <font color="red"><strong><?php echo sprintf(
            __('Are you sure you want to DELETE %s?'), __('this ticket'));?></strong></font>
        <br><br><?php echo __('Deleted data CANNOT be recovered, including any associated attachments.');?>
    </p>
    <div><?php echo __('Please confirm to continue.');?></div>
    <form action="tickets.php?id=<?php echo esc_attr($ticket->getId()); ?>" method="post" id="confirm-form" name="confirm-form">
        <?php csrf_token(); ?>
        <input type="hidden" name="id" value="<?php echo esc_attr($ticket->getId()); ?>">
        <input type="hidden" name="a" value="process">
        <input type="hidden" name="do" id="action" value="">
        <div class="form-group" style="margin-top:20px;">
                <input type="submit" class="btn btn-success" value="<?php echo __('OK');?>">
                <input type="button" class="btn close_me" value="<?php echo __('Cancel');?>">
         </div>
    </form>
	</div>
</div>
<script type="text/javascript">
$(function() {
    $(document).on('click', 'a.change-user', function(e) {
        e.preventDefault();
        var tid = <?php echo $ticket->getOwnerId(); ?>;
        var cid = <?php echo $ticket->getOwnerId(); ?>;
        var url = 'ajax.php/'+$(this).attr('href').substr(1);
        $.userLookup(url, function(user) {
            if(cid!=user.id
                    && $('.dialog#confirm-action #changeuser-confirm').length) {
                $('#newuser').html(user.name +' &lt;'+user.email+'&gt;');
                $('.dialog#confirm-action #action').val('changeuser');
                $('#confirm-form').append('<input type=hidden name=user_id value='+user.id+' />');
                $('#overlay').show();
                $('.dialog#confirm-action .confirm-action').hide();
                $('.dialog#confirm-action p#changeuser-confirm')
                .show()
                .parent('div').parent('div').show().trigger('click');
            }
        });
    });

    // Post Reply or Note action buttons.
    $('a.post-response').click(function (e) {
        var $r = $('ul.nav.nav-tabs > li > a'+$(this).attr('href')+'-tab');
        if ($r.length) {
            // Make sure ticket thread tab is visiable.
            var $t = $('ul#ticket_tabs > li > a#ticket-thread-tab');
            if ($t.length && !$t.hasClass('active'))
                $t.trigger('click');
            // Make the target response tab active.
            if (!$r.hasClass('active'))
                $r.trigger('click');

            // Scroll to the response section.
            var $stop = $(document).height();
            var $s = $('div#response_options');
            if ($s.length)
                $stop = $s.offset().top-125

            $('html, body').animate({scrollTop: $stop}, 'fast');
        }

        return false;
    });

});

</script>
