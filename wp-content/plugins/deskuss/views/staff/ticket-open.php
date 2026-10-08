<?php
if (!defined('DSKADMININC') || !$thisstaff
        || !$thisstaff->hasPerm(TicketModel::PERM_CREATE, false))
        die('Access Denied');

$info=array();
$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);

if (!$info['deptId'])
    $info['deptId'] = $cfg->getDefaultDeptId() ?: 0;

$forms = array();
if ($info['deptId'] && ($dept=Dept::lookup($info['deptId']))) {
    foreach ($dept->getForms() as $F) {
        if (!$F->hasAnyVisibleFields())
            continue;
        if ($_POST) {
            $F = $F->instanciate();
            $F->isValidForClient();
        }
        $forms[] = $F;
    }
}

if ($_POST)
    $info['duedate'] = Format::date(strtotime($info['duedate']), false, false, 'UTC');
?>
<div class="panel">
	<form action="tickets.php?a=open" method="post" class="save"  enctype="multipart/form-data">
	<?php csrf_token(); ?>
		<input type="hidden" name="do" value="create">
		<input type="hidden" name="a" value="open">
			<div class="panel-heading">
				<div class="panel-title table-caption"><?php echo __('Open a New Ticket');?></div>
			</div>
	<div class="panel-body">
			<div class="panel-heading"><div class="panel-title table-caption"><?php echo __('User Information'); ?>:</div></div>
			<div class="panel-body">
			<?php
				if(!empty($errors['user'])){
					echo '<div class="alert-danger">&nbsp;'.$errors['user'].'</div>';
				}
				?>
				<?php
				if ($user) { ?>
			<div class="form-group row">
				<div class="col-md-3">
					<label class="control-label"><?php echo __('User'); ?>:</label>
				</div>
				<div class="col-md-9">
					<div id="user-info">
						<input type="hidden" name="uid" id="uid" value="<?php echo $user->getId(); ?>" />
						<a href="#" onclick="javascript: $.userLookup('ajax.php/users/<?php echo $user->getId(); ?>/edit',
								function (user) {
									$('#user-name').text(user.name);
									$('#user-email').text(user.email);
								});
						return false;
						"><i class="fa fa-user"></i>
						<span id="user-name"><?php echo Format::htmlchars($user->getName()); ?></span>
						&lt;<span id="user-email"><?php echo $user->getEmail(); ?></span>&gt;
						</a>
						<a class="btn inline button" style="overflow:inherit" href="#"
							onclick="javascript:
							$.userLookup('ajax.php/users/select/'+$('input#uid').val(),
								function(user) {
									$('input#uid').val(user.id);
									$('#user-name').text(user.name);
									$('#user-email').text('<'+user.email+'>');
							});
							return false;
						"><i class="fa fa-retweet"></i> <?php echo __('Change'); ?></a>
					</div>
				</div>
			</div>
			<?php
			} else { //Fallback: Just ask for email and name
				?>
			<div class="col-md-6">
				<div class="form-group row form-inline">
					<div class="col-md-4">
						<label class="control-label required"> <?php echo __('Email Address'); ?>: </label>
					</div>
					<div class="col-md-8">
						<input type="text" size=30 name="email" id="user-email" class="attached form-control basic-search" autocomplete="off" autocorrect="off" value="<?php echo $info['email']; ?>" />
						<?php
							if(!empty($errors['email'])){
								echo '<div class="alert-danger">&nbsp;'.$errors['email'].'</div>';
							}
						?>
						<a href="?a=open&amp;uid={id}" data-dialog="ajax.php/users/lookup/form" class="attached button"><i class="fa fa-search"></i></a>
					</div>
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-group row">
					<div class="col-md-3">
						<label class="control-label required"> <?php echo __('Full Name'); ?>: </label>
					</div>
					<div class="col-md-9">
						<span style="display:inline-block;">
							<input class="form-control" type="text" size=35 name="name" id="user-name" value="<?php echo $info['name']; ?>" /> </span>
						<?php
							if(!empty($errors['name'])){
								echo '<div class="alert-danger">&nbsp;'.$errors['name'].'</div>';
							}
						?>
					</div>
				</div>
			</div>
			<?php
			} ?>

			<?php
			if($cfg->notifyONNewStaffTicket()) {  ?>
			<div class="form-group form-inline row col-md-6">
				<div class="col-md-3">
					<label class="control-label"><?php echo __('Ticket Notice'); ?>:</label>
				</div>
				<div class="checkbox col-md-9">
					<label>
						<input type="checkbox" name="alertuser" <?php echo (!$errors || $info['alertuser'])? 'checked="checked"': ''; ?>>&nbsp;&nbsp;<?php
						echo __('Send alert to user.'); ?>
					</label>
				</div>
			</div>
			<?php
			} ?>
		</div>
			<div class="panel-heading">
				<div class="panel-title table-caption">
				<?php echo __('Ticket Information and Options');?>:</div></div>
			<div class="panel-body">
			<div class="row">
			<div class="col-md-6">
			<div class="form-group row">
				<div class="col-md-3">
					<label class="control-label required"><?php echo __('Ticket Source');?>:</label>
				</div>
				<div class="col-md-9">
					<span style="display:inline-block;">
					<select class="form-control" name="source">
						<?php
						$source = $info['source'] ?: 'Phone';
						$sources = Ticket::getSources();
						unset($sources['Web'], $sources['API']);
						foreach ($sources as $k => $v)
							echo sprintf('<option value="%s" %s>%s</option>',
									$k,
									($source == $k ) ? 'selected="selected"' : '',
									$v);
						?>
					</select>
					<?php
							if(!empty($errors['source'])){
								echo '<div class="alert-danger">&nbsp;'.$errors['source'].'</div>';
							}
						?>
					</span>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-3">
					<label class="control-label required"><?php echo __('Department'); ?>:</label>
				</div>
				<div class="col-md-9">
					<span style="display:inline-block;">
					<select class="form-control" name="deptId" onchange="javascript:
							var data = $(':input[name]', '#dynamic-form').serialize();
							$.ajax(
							'ajax.php/form/department/' + this.value,
							{
								data: data,
								dataType: 'json',
								success: function(json) {
								$('#dynamic-form').empty().append(json.html);
								$(document.head).append(json.media);
								load_summernote();
								}
							});">
						<?php
						if($depts=Dept::getDepartments(array('dept_id' => $thisstaff->getDepts()))) {
							foreach($depts as $id =>$name) {
								if (!($role = $thisstaff->getRole($id))
									|| !$role->hasPerm(Ticket::PERM_CREATE)
								) {
									// No access to create tickets in this dept
									continue;
								}
								echo sprintf('<option value="%d" %s>%s</option>',
										$id, ($info['deptId']==$id)?'selected="selected"':'',$name);
							}
						}
						?>
					</select>
					<?php
							if(!empty($errors['deptId'])){
								echo '<div class="alert-danger">&nbsp;'.$errors['deptId'].'</div>';
							}
						?>
					</span>
				</div>
			</div>
		</div>
	<div class="col-md-6">
			<div class="form-group row">
				<div class="col-md-3">
					<label class="control-label"><?php echo __('SLA Plan');?>:</label>
				</div>
				<div class="col-md-9">
					<span style="display:inline-block;">
					<select class="form-control" name="slaId">
						<option value="0" selected="selected" >&mdash; <?php echo __('System Default');?> &mdash;</option>
						<?php
						if($slas=SLA::getSLAs()) {
							foreach($slas as $id =>$name) {
								echo sprintf('<option value="%d" %s>%s</option>',
										$id, ($info['slaId']==$id)?'selected="selected"':'',Format::htmlchars($name));
							}
						}
						?>
					</select>
					<?php
						if(!empty($errors['slaId'])){
							echo '<div class="alert-danger">&nbsp;'.$errors['slaId'].'</div>';
						}
					?>
					</span>
				</div>
			</div>

			<?php
			if($thisstaff->hasPerm(TicketModel::PERM_ASSIGN, false)) { ?>
			<div class="form-group row">
				<div class="col-md-3">
					<label class="control-label"><?php echo __('Assign To');?>:</label>
				</div>
				<div class="col-md-9">
					<span style="display:inline-block;">
					<select class="form-control" id="assignId" name="assignId">
						<option value="0" selected="selected">&mdash; <?php echo __('Select an Agent OR a Team');?> &mdash;</option>
						<?php
						if(($users=Staff::getAvailableStaffMembers())) {
							echo '<OPTGROUP label="'.sprintf(__('Agents (%d)'), count($users)).'">';
							foreach($users as $id => $name) {
								$k="s$id";
								echo sprintf('<option value="%s" %s>%s</option>',
											$k,(($info['assignId']==$k)?'selected="selected"':''),$name);
							}
							echo '</OPTGROUP>';
						}

						if(($teams=Team::getActiveTeams())) {
							echo '<OPTGROUP label="'.sprintf(__('Teams (%d)'), count($teams)).'">';
							foreach($teams as $id => $name) {
								$k="t$id";
								echo sprintf('<option value="%s" %s>%s</option>',
											$k,(($info['assignId']==$k)?'selected="selected"':''),$name);
							}
							echo '</OPTGROUP>';
						}
						?>
					</select>
					<?php
						if(!empty($errors['assignId'])){
							echo '<div class="alert-danger">&nbsp;'.$errors['assignId'].'</div>';
						}
					?>
					</span>
				</div>
			</div>
			<?php } ?>

			<div class="form-group row form-inline">
			<div class="col-md-3">
				<label class="control-label"><?php echo __('Due Date');?>:</label>
			</div>
			<div class="col-md-9">
				<span style="display:inline-block;">
				<input class="dp form-control" id="duedate" name="duedate" value="<?php echo Format::htmlchars($info['duedate']); ?>" size="12" autocomplete=OFF>

				<?php
					if(!empty($errors['duedate'])){
						echo '<div class="alert-danger">&nbsp;'.$errors['duedate'].'</div>';
					}
				?>
				<?php
					if(!empty($errors['time'])){
						echo '<div class="alert-danger">&nbsp;'.$errors['time'].'</div>';
					}
				?>
				&nbsp;&nbsp;
				</span>
				<?php
				$min=$hr=null;
				if($info['time'])
					list($hr, $min)=explode(':', $info['time']);

				echo Misc::timeDropdown($hr, $min, 'time');
				?>
				<br/>
				<em class="text-muted"><?php echo __('Time is based on your time zone');?> (GMT <?php echo Format::date(false, false, 'ZZZ'); ?>)</em>
			</div>
		</div>
		</div>
	</div>
			</div>
			<div class="form-group" id="dynamic-form">
				<?php
					foreach ($forms as $form) {
						print $form->getForm()->getMedia();
						include(STAFFINC_DIR .  'templates/dynamic-form.tmpl.php');
					}
				?>
			</div>
				<?php
				//is the user allowed to post replies??
				if ($thisstaff->getRole()->hasPerm(TicketModel::PERM_REPLY)) { ?>
						<div class="panel-heading">
							<div class="panel-title table-caption"><?php echo __('Response');?>:&nbsp;
								<small class="text-muted"><em><?php echo __('Optional response to the above issue.');?></em></small>
							</div>
						</div>
				<div class="panel-body">
				<div class="form-inline">
					<?php
					if(($cannedResponses=Canned::getCannedResponses())) {
						?>
					<div style="margin-top:0.3em;margin-bottom:0.5em">
						<label class="control-label"> <?php echo __('Canned Response');?>:&nbsp;</label>
						<select class="form-control" id="cannedResp" name="cannedResp">
							<option value="0" selected="selected">&mdash; <?php echo __('Select a canned response');?> &mdash;</option>
							<?php
							foreach($cannedResponses as $id =>$title) {
								echo sprintf('<option value="%d">%s</option>',$id,$title);
							}
							?>
						</select>
						&nbsp;&nbsp;
						<div class="checkbox">
							<label>
								<input type='checkbox' value='1' name="append" id="append" checked="checked">&nbsp;&nbsp;<?php echo __('Append');?>
							</label>
						</div>
					</div>
				</div>
				<?php
				}
					$signature = '';
					if ($thisstaff->getDefaultSignatureType() == 'mine')
						$signature = $thisstaff->getSignature(); ?>
				<div class="form-group">
					<textarea
						class="form-control <?php if ($cfg->isRichTextEnabled()) echo 'summernote-base';
							?> draft draft-delete" data-signature="<?php
							echo Format::htmlchars(Format::viewableImages($signature)); ?>"
						data-signature-field="signature" data-dept-field="deptId"
						placeholder="<?php echo __('Initial response for the ticket'); ?>"
						name="response" id="response" cols="21" rows="8"
						style="width:80%;" <?php
					list($draft, $attrs) = Draft::getDraftAndDataAttrs('ticket.staff.response', false, $info['response']);
					echo $attrs; ?>><?php echo $_POST ? $info['response'] : $draft;
					?>
					</textarea>
				</div>
				<div class="attachments">
					<?php
					print $response_form->getField('attachments')->render();
					?>
				</div>
			<br/>
				<div class="form-group row form-inline">
				<div class="col-md-6">
					<div class="col-md-4">
						<label class="control-label"><?php echo __('Ticket Status');?>:</label>
					</div>
					<div class="col-md-8">
						<select class="form-control" name="statusId">
						<?php
						$statusId = $info['statusId'] ?? $cfg->getDefaultTicketStatusId();
						$states = array('open');
						if ($thisstaff->hasPerm(TicketModel::PERM_CLOSE, false))
							$states = array_merge($states, array('closed'));
						foreach (TicketStatusList::getStatuses(
									array('states' => $states)) as $s) {
							if (!$s->isEnabled()) continue;
							$selected = ($statusId == $s->getId());
							echo sprintf('<option value="%d" %s>%s</option>',
									$s->getId(),
									$selected
									? 'selected="selected"' : '',
									__($s->getName()));
						}
						?>
						</select>
					</div>
					</div>
				<div class="col-md-6">
					<div class="col-md-3">
						<label class="control-label"><?php echo __('Signature');?>:</label>
					</div>
					<div class="col-md-9">
						<?php
						$info['signature'] = $info['signature'] ?? $thisstaff->getDefaultSignatureType();
						?>
						<label>
							<input type="radio" name="signature" value="none" checked="checked"> <?php echo __('None');?>
						</label>&nbsp;&nbsp;
						<?php
						if($thisstaff->getSignature()) { ?>
						<label>
							<input type="radio" name="signature" value="mine"
								<?php echo ($info['signature']=='mine')?'checked="checked"':''; ?>> <?php echo __('My Signature');?>
							</label>
						<?php
						} ?>
						<label>
							<input type="radio" name="signature" value="dept"
							<?php echo ($info['signature']=='dept')?'checked="checked"':''; ?>> <?php echo sprintf(__('Department Signature (%s)'), __('if set')); ?>
						</label>
					</div>
					</div>
				</div>
			<?php
			} //end canPostReply
			?>
		</div>
			<div class="panel-heading"><div class="panel-title table-caption"><?php echo __('Internal Note');?></div></div>
		<div class="panel-body">
				<?php
					if(!empty($errors['note'])){
						echo '<div class="alert-danger">&nbsp;'.$errors['note'].'</div>';
					}
				?>
			<div class="form-group">
				<textarea
					class="form-control <?php if ($cfg->isRichTextEnabled()) echo 'summernote-base';
					?> draft draft-delete"
					placeholder="<?php echo __('Optional internal note (recommended on assignment)'); ?>"
					name="note" cols="21" rows="6" style="width:80%;" <?php
				list($draft, $attrs) = Draft::getDraftAndDataAttrs('ticket.staff.note', false, ($info['note'] ?? ''));
				echo $attrs; ?>><?php echo $_POST ? $info['note'] : $draft;
					?>
				</textarea>
			</div>
		</div>
			<div style="text-align:center;">
				<input type="submit" class="btn btn-success" name="submit" value="<?php echo _P('action-button', 'Open');?>">
				<input type="reset" class="btn btn-info"  name="reset"  value="<?php echo __('Reset');?>">
				<input type="button" class="btn" name="cancel" value="<?php echo __('Cancel');?>" onclick="javascript:
					$('.richtext').each(function() {
						var redactor = $(this).data('redactor');
						if (redactor && redactor.opts.draftDelete)
							redactor.deleteDraft();
					});
					window.location.href='tickets.php';
				">
			</div>
		</form>
	</div>
</div>
<script type="text/javascript">

$(function() {
    $('input#user-email').typeahead({
        source: function (typeahead, query) {
            $.ajax({
                url: "ajax.php/users?q="+query,
                dataType: 'json',
                success: function (data) {
                    typeahead.process(data);
                }
            });
        },
        onselect: function (obj) {
            $('#uid').val(obj.id);
            $('#user-name').val(obj.name);
            $('#user-email').val(obj.email);
        },
        property: "/bin/true"
    });

   <?php
    // Popup user lookup on the initial page load (not post) if we don't have a
    // user selected
    if (!$_POST && !$user) {?>
    setTimeout(function() {
      $.userLookup('ajax.php/users/lookup/form', function (user) {
        window.location.href = window.location.href+'&uid='+user.id;
      });
    }, 100);
    <?php
    } ?>
});
</script>