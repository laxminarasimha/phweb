<?php
if(!defined('DSKADMININC') || !$thisstaff || !$thisstaff->isAdmin()) die('Access Denied');
$info = $qs = $forms = array();
if($dept && $_REQUEST['a']!='add') {
    //Editing Department.
    $title=__('Update Department');
    $action='update';
    $submit_text=__('Save Changes');
    $info = $dept->getInfo();
    $info['id'] = $dept->getId();
    $qs += array('id' => $dept->getId());
    $forms = $dept->getForms();
} else {
    if (!$dept)
        $dept = Dept::create();
    $title=__('Add New Department');
    $action='create';
    $submit_text=__('Create Dept');
    $info['ispublic']=isset($info['ispublic'])?$info['ispublic']:1;
    $info['ticket_auto_response']=isset($info['ticket_auto_response'])?$info['ticket_auto_response']:1;
    $info['message_auto_response']=isset($info['message_auto_response'])?$info['message_auto_response']:1;
    if (!isset($info['group_membership']))
        $info['group_membership'] = 1;

    $qs += array('a' => $_REQUEST['a']);
    $forms = TicketForm::objects();
}

$info = Format::htmlchars(($errors && $_POST) ? $_POST : $info);
?>
<form action="departments.php?<?php echo Http::build_query($qs); ?>" method="post" class="save">
<?php csrf_token(); ?>
<input type="hidden" name="do" value="<?php echo esc_attr($action); ?>">
<input type="hidden" name="a" value="<?php echo esc_attr(Format::htmlchars($_REQUEST['a'])); ?>">
<input type="hidden" name="id" value="<?php echo esc_attr($info['id']); ?>">
<div class="panel">
<div class="panel-heading">
	<div class="panel-title table-caption">
		<?php echo esc_html($title); ?>
	</div>
</div>
<div class="panel-body">
<div class="panel-heading">
	<div class="panel-title table-caption">
		<?php echo __('Department Information');?>
	</div>
</div>
<div class="panel-body">
	<div class="row">
		<div class="col-sm-6 form-group">
			<label for="name" class="required"><?php echo __('Name');?>:</label>
		<input class="form-control" data-translate-tag="<?php echo esc_attr($dept ? $dept->getTranslateTag() : ''); ?>" 
			type="text" size="30" name="name" id="name" value="<?php echo esc_attr($info['name']); ?>" autofocus>
			<?php if(!empty($errors['name'])){ ?>
				<div class="alert-danger"><?php echo $errors['name']; ?></div>
			<?php } ?>
		</div>
		<div class="col-sm-6 form-group">
			<label for="ispublic" class="required" style="display:block;"><?php echo __('Type');?>:&nbsp;<i class="help-tip fa fa-question-circle" href="#type"></i></label>
			<label class="radio-inline">
				<input type="radio" name="ispublic" id="ispublic" value="1" <?php echo $info['ispublic']?'checked="checked"':''; ?>><strong><?php echo __('Public');?></strong>
			</label>
			<label class="radio-inline">
				<input type="radio" name="ispublic" value="0" <?php echo !$info['ispublic']?'checked="checked"':''; ?>><strong><?php echo __('Private');?></strong> <?php echo mb_convert_case(__('(internal)'), MB_CASE_TITLE);?>
			</label>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-6 form-group">
			<label for="pid"><?php echo __('Parent');?>:</label>
			<select class="form-control" name="pid" id="pid">
				<option value="">&mdash; <?php echo __('Top-Level Department'); ?> &mdash;</option>
				<?php
				foreach (Dept::getDepartments() as $id=>$name) {
					if ($info['id'] && $id == $info['id'])
						continue; ?>
					<option value="<?php echo esc_attr($id); ?>"<?php
						if($info['pid'] == $id) echo 'selected="selected"';
						?>><?php echo esc_html($name); ?></option>
			<?php } ?>
		</select>
		<?php if(!empty($errors['pid'])){ ?>
				<div class="alert-danger"><?php echo $errors['pid']; ?></div>
		<?php } ?>
		</div>
		<div class="col-sm-6 form-group">
			<label for="sla_id"><?php echo __('SLA'); ?>:&nbsp;<i class="help-tip fa fa-question-circle" href="#sla"></i></label>
			<select  class="form-control" name="sla_id" id="sla_id">
				<option value="0">&mdash; <?php echo __('System Default'); ?> &mdash;</option>
				<?php
				if($slas=SLA::getSLAs()) {
				foreach($slas as $id =>$name) {
					echo sprintf('<option value="%d" %s>%s</option>',
							$id, ($info['sla_id']==$id)?'selected="selected"':'',Format::htmlchars($name));
				}
			}
			?>
		</select>
		<?php if(!empty($errors['sla_id'])){ ?>
				<div class="alert-danger"><?php echo $errors['sla_id']; ?></div>
		<?php } ?>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-6 form-group">
			<label for="manager_id"><?php echo __('Manager'); ?>:&nbsp;<i class="help-tip fa fa-question-circle" href="#manager"></i></label>
			<select class="form-control" name="manager_id" id="manager_id">
				<option value="0">&mdash; <?php echo __('None'); ?> &mdash;</option>
				<?php
				$sql='SELECT staff_id,CONCAT_WS(", ",lastname, firstname) as name '
					.' FROM '.STAFF_TABLE.' staff '
					.' ORDER by name';
				if(($res=db_query($sql)) && db_num_rows($res)) {
					while(list($id,$name)=db_fetch_row($res)){
						$selected=($info['manager_id'] && $id==$info['manager_id'])?'selected="selected"':'';
						echo sprintf('<option value="%d" %s>%s</option>',$id,$selected,Format::htmlchars($name));
					}
				}
				?>
			</select>
			<?php if(!empty($errors['manager_id'])){ ?>
				<div class="alert-danger"><?php echo $errors['manager_id']; ?></div>
			<?php } ?>
		</div>
		<div class="col-sm-6 form-group">
			<label for="assign_members_only" style="display:block;"><?php echo __('Ticket Assignment'); ?>:&nbsp;<i class="help-tip fa fa-question-circle" href="#sandboxing"></i></label>
			<input type="checkbox" name="assign_members_only" id="assign_members_only" <?php echo
				$info['assign_members_only']?'checked="checked"':''; ?>>
				<?php echo __('Restrict ticket assignment to department members'); ?>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-6 form-group">
			<label for="disable_auto_claim" style="display:block;"><?php echo __('Claim on Response'); ?>:&nbsp;
				<i class="help-tip fa fa-question-circle" href="#disable_auto_claim"></i>
			</label>
			<input type="checkbox" name="disable_auto_claim" id="disable_auto_claim" <?php echo
				$info['disable_auto_claim'] ? 'checked="checked"' : ''; ?>>
				<?php echo sprintf('<strong>%s</strong> %s',
				__('Disable'),
				__('auto claim')); ?>
		</div>
	</div>
</div>
<div class="panel-heading">
	<div class="panel-title table-caption">
		<?php echo __('Outgoing Email Settings');?>
	</div>
</div>
<div class="panel-body">
	<div class="row">
		<div class="col-sm-6 form-group">
			<label for="email_id"><?php echo __('Outgoing Email'); ?>:&nbsp;<i class="help-tip fa fa-question-circle" href="#email"></i></label>
			<select class="form-control" name="email_id" id="email_id">
				<option value="0">&mdash; <?php echo __('System Default'); ?> &mdash;</option>
				<?php
				$sql='SELECT email_id,email,name FROM '.EMAIL_TABLE.' email ORDER by name';
				if(($res=db_query($sql)) && db_num_rows($res)){
					while(list($id,$email,$name)=db_fetch_row($res)){
						$selected=($info['email_id'] && $id==$info['email_id'])?'selected="selected"':'';
						if($name)
							$email=Format::htmlchars("$name <$email>");
						echo sprintf('<option value="%d" %s>%s</option>',$id,$selected,$email);
					}
				}
				?>
			</select>
			<?php if(!empty($errors['email_id'])){ ?>
				<div class="alert-danger"><?php echo $errors['email_id']; ?></div>
			<?php } ?>
		</div>
		<div class="col-sm-6 form-group">
			<label for="tpl_id"><?php echo __('Template Set'); ?>:&nbsp;<i class="help-tip fa fa-question-circle" href="#template"></i></label>
			<select class="form-control" name="tpl_id" id="tpl_id">
				<option value="0">&mdash; <?php echo __('System Default'); ?> &mdash;</option>
				<?php
				$sql='SELECT tpl_id,name FROM '.EMAIL_TEMPLATE_GRP_TABLE.' tpl WHERE isactive=1 ORDER by name';
				if(($res=db_query($sql)) && db_num_rows($res)){
					while(list($id,$name)=db_fetch_row($res)){
						$selected=($info['tpl_id'] && $id==$info['tpl_id'])?'selected="selected"':'';
						echo sprintf('<option value="%d" %s>%s</option>',$id,$selected,Format::htmlchars($name));
					}
				}
				?>
			</select>
			<?php if(!empty($errors['tpl_id'])){ ?>
				<div class="alert-danger"><?php echo $errors['tpl_id']; ?></div>
			<?php } ?>
		</div>
	</div>
</div>
<div class="panel-heading">
	<div class="panel-title table-caption">
		<?php echo __('Autoresponder Settings');?>&nbsp;<i class="help-tip fa fa-question-circle" href="#auto_response_settings"></i>
	</div>
</div>
<div class="panel-body">
	<div class="row">
		<div class="col-sm-6 form-group">
			<label for="ticket_auto_response" style="display:block;"><?php echo __('New Ticket');?>:&nbsp;
				<i class="help-tip fa fa-question-circle" href="#new_ticket"></i>
			</label>
			<input type="checkbox" name="ticket_auto_response" id="ticket_auto_response" value="0" <?php 
				echo !$info['ticket_auto_response']?'checked="checked"':''; ?>>
				<?php echo sprintf(__('<strong>Disable</strong> for %s'), __('this department')); ?>
		</div>
		<div class="col-sm-6 form-group">
			<label for="message_auto_response" style="display:block;"><?php echo __('New Message');?>:&nbsp;
				<i class="help-tip fa fa-question-circle" href="#new_message"></i>
			</label>
			<input type="checkbox" name="message_auto_response" id="message_auto_response" value="0" <?php echo !$info['message_auto_response']?'checked="checked"':''; ?> >
				<?php echo sprintf(__('<strong>Disable</strong> for %s'), __('this department')); ?>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-6 form-group">
			<label for="autoresp_email_id"><?php echo __('Auto-Response Email'); ?>:&nbsp;<i class="help-tip fa fa-question-circle" href="#auto_response_email"></i></label>
			<select class="form-control" name="autoresp_email_id" id="autoresp_email_id">
				<option value="0" selected="selected">&mdash; <?php echo __('Department Email'); ?> &mdash;</option>
				<?php
				$sql='SELECT email_id,email,name FROM '.EMAIL_TABLE.' email ORDER by name';
				if(($res=db_query($sql)) && db_num_rows($res)){
					while(list($id,$email,$name)=db_fetch_row($res)){
						$selected = (isset($info['autoresp_email_id'])
								&& $id == $info['autoresp_email_id'])
							? 'selected="selected"' : '';
						if($name)
							$email=Format::htmlchars("$name <$email>");
						echo sprintf('<option value="%d" %s>%s</option>',$id,$selected,$email);
					}
				}
				?>
			</select>
			<?php if(!empty($errors['autoresp_email_id'])){ ?>
				<div class="alert-danger"><?php echo $errors['autoresp_email_id']; ?></div>
			<?php } ?>
		</div>
	</div>
</div>
<div class="panel-heading">
	<div class="panel-title table-caption">
		<?php echo __('Ticket Defaults');?>&nbsp;<i class="help-tip fa fa-question-circle" href="#ticket_defaults"></i>
	</div>
</div>
<div class="panel-body">
	<div class="row">
		<div class="col-sm-6 form-group">
			<label for="priority_id"><?php echo __('Default Priority');?>:</label>
			<select class="form-control" name="priority_id" id="priority_id">
				<option value="0">&mdash; <?php echo __('System Default'); ?> &mdash;</option>
				<?php
			if (($priorities = Priority::getPriorities())) {
				foreach ($priorities as $id => $name) {
					echo sprintf('<option value="%d" %s>%s</option>',
							$id, ($info['priority_id']==$id)?'selected="selected"':'', Format::htmlchars($name));
				}
			}
				?>
			</select>
		</div>
		<div class="col-sm-6 form-group">
			<label for="status_id"><?php echo __('Default Ticket Status');?>:</label>
			<select class="form-control" name="status_id" id="status_id">
				<option value="0">&mdash; <?php echo __('System Default'); ?> &mdash;</option>
				<?php
			if (($statuses = TicketStatusList::getStatuses())) {
				foreach ($statuses as $s) {
					echo sprintf('<option value="%d" %s>%s</option>',
							$s->getId(), ($info['status_id']==$s->getId())?'selected="selected"':'', Format::htmlchars($s->getName()));
				}
			}
				?>
			</select>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-6 form-group">
			<label for="staff_id"><?php echo __('Auto-assign To');?>:&nbsp;<i class="help-tip fa fa-question-circle" href="#auto_assign"></i></label>
			<select class="form-control" name="staff_id" id="staff_id">
				<option value="0">&mdash; <?php echo __('None'); ?> &mdash;</option>
				<?php
				$sql='SELECT staff_id,CONCAT_WS(", ",lastname, firstname) as name '
					.' FROM '.STAFF_TABLE.' staff WHERE isactive=1 ORDER by name';
			if(($res=db_query($sql)) && db_num_rows($res)) {
				while(list($id,$name)=db_fetch_row($res)){
					$selected=($info['staff_id'] && $id==$info['staff_id'])?'selected="selected"':'';
					echo sprintf('<option value="%d" %s>%s</option>',$id,$selected,Format::htmlchars($name));
				}
			}
				?>
			</select>
		</div>
		<div class="col-sm-6 form-group">
			<label for="team_id"><?php echo __('Auto-assign Team');?>:&nbsp;<i class="help-tip fa fa-question-circle" href="#auto_assign_team"></i></label>
			<select class="form-control" name="team_id" id="team_id">
				<option value="0">&mdash; <?php echo __('None'); ?> &mdash;</option>
				<?php
			if(($teams=Team::getTeams())) {
				foreach($teams as $id =>$name) {
					echo sprintf('<option value="%d" %s>%s</option>',
							$id, ($info['team_id']==$id)?'selected="selected"':'', Format::htmlchars($name));
				}
			}
				?>
			</select>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-6 form-group">
			<label for="page_id"><?php echo __('Thank-You Page');?>:</label>
			<select class="form-control" name="page_id" id="page_id">
				<option value="0">&mdash; <?php echo __('System Default'); ?> &mdash;</option>
				<?php
			if (($pages = Page::getPages())) {
				foreach ($pages as $page) {
					echo sprintf('<option value="%d" %s>%s</option>',
							$page->getId(), ($info['page_id']==$page->getId())?'selected="selected"':'', Format::htmlchars($page->getName()));
				}
			}
				?>
			</select>
		</div>
		<div class="col-sm-6 form-group">
			<label style="display:block;"><?php echo __('Auto-Response');?>:&nbsp;<i class="help-tip fa fa-question-circle" href="#noautoresp"></i></label>
			<input type="checkbox" name="noautoresp" value="1" <?php echo $info['noautoresp']?'checked="checked"':''; ?>>
				<?php echo sprintf(__('<strong>Disable</strong> for %s'), __('tickets in this department')); ?>
		</div>
	</div>
</div>
<div class="panel-heading">
	<div class="panel-title table-caption">
		<?php echo __('Alerts and Notices'); ?>: &nbsp;<i class="help-tip fa fa-question-circle" href="#group_membership"></i>
	</div>
</div>
<div class="panel-body">
	<div class="row">
		<div class="col-sm-6 form-group">
			<label for="group_membership"><?php echo __('Recipients'); ?>:&nbsp;<i class="help-tip fa fa-question-circle" href="#group_membership"></i></label>
			<select class="form-control" name="group_membership" id="group_membership">
			<?php foreach (array(
				Dept::ALERTS_DISABLED =>        __("No one (disable Alerts and Notices)"),
				Dept::ALERTS_DEPT_ONLY =>       __("Department members only"),
				Dept::ALERTS_DEPT_AND_EXTENDED => __("Department and extended access members"),
			) as $mode=>$desc) { ?>
			<option value="<?php echo esc_attr($mode); ?>" <?php
				if ($info['group_membership'] == $mode) echo 'selected="selected"';
				?>><?php echo esc_html($desc); ?></option><?php
			} ?>
			</select>
		</div>
	</div>
</div>
<div class="panel-heading">
	<div class="panel-title table-caption">
		<?php echo __('Department Signature'); ?>:&nbsp;<i class="help-tip fa fa-question-circle" href="#department_signature"></i>
	</div>
</div>
<div class="panel-body">
	<div class="row">
		<div class="col-sm-12 form-group">
			<?php if(!empty($errors['signature'])){ ?>
				<div class="alert-danger"><?php echo $errors['signature']; ?></div>
			<?php } ?>
			<textarea class="summernote-base no-bar" id="signature" name="signature" cols="21"
					rows="5" height="100"><?php echo $info['signature']; ?></textarea>
		</div>
	</div>
</div>
<div class="panel-heading">
	<div class="panel-title table-caption">
		<?php echo __('Department Members'); ?>
	</div>
</div>
<div class="panel-body">
<div class="table-light table-responsive">
	<table class="table table-bordered list" border="0" cellspacing="1" cellpadding="0" width="940">
		<thead>
			<th width="45%"><?php echo __('Agent'); ?></th>
			<th width="45%"><?php echo __('Role'); ?></th>
			<th width="6%"><?php echo __('Alerts'); ?></th>
			<th width="4%">&nbsp;</th>
		</thead>
		<tbody>
			<tr id="primary-members">
			</tr>
			<tr id="extended-access-members">
			</tr>
			<?php
			$agents = Staff::getStaffMembers();
			if($action != 'create'){
				foreach ($dept->getMembers() as $member) {
					unset($agents[$member->getId()]);
				}
			} ?>
			<tr id="add_extended_access">
				<td colspan="4" class="form-inline">
					<select class="form-control" id="add_access" data-quick-add="staff">
						<option value="0">&mdash; <?php echo __('Select Agent');?> &mdash;</option>
						<?php
						foreach ($agents as $id=>$name) {
							echo sprintf('<option value="%d">%s</option>',$id,Format::htmlchars($name));
						}
						?>
						<option value="0" data-quick-add>&mdash; <?php echo __('Add New');?> &mdash;</option>
					</select>
					<button type="button" class="btn btn-success action-button">
						<?php echo __('Add Agent to Department'); ?>
					</button>
				</td>
			</tr>
		</tbody>
		<tbody>
		<tr id="member_template" class="hidden">
			<td style="vertical-align:middle;">
				<input class="form-control" type="hidden" data-name="members[]" value="" />
			</td>
			<td>
				<select class="form-control" data-name="member_role" data-quick-add="role">
					<option value="0">&mdash; <?php echo __('Select Role');?> &mdash;</option>
					<?php
					foreach (Role::getRoles() as $id=>$name) {
						echo sprintf('<option value="%d" %s>%s</option>',$id,$sel,Format::htmlchars($name));
					}
					?>
					<option value="0" data-quick-add>&mdash; <?php echo __('Add New');?> &mdash;</option>
				</select>
			</td>
			<td style="text-align:center;vertical-align:middle;">
				<input type="checkbox" data-name="member_alerts" value="1" />
			</td>
			<td style="text-align:center;vertical-align:middle;">
				<a href="#" class="drop-membership" title="<?php 
					echo __('Delete'); ?>"><i class="fa fa-trash"></i></a>
			</td>
		</tr>
		</tbody>
	  </table>
</div>
</div>

<div class="row">
	<div class="col-sm-12 panel-heading m-t-2 m-b-2">
		<div class="panel-title table-caption">
			<?php echo __('Forms'); ?>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-12">
		<div class="table-light table-responsive">
			<table id="dept-forms" class="table table-bordered" border="0" cellspacing="0" cellpadding="2">
				<?php
				$current_forms = array();
				foreach ($forms as $F) {
				$current_forms[] = $F->id; ?>
				<tbody data-form-id="<?php echo $F->get('id'); ?>" class="sortable-rows">
					<tr>
						<td class="handle" colspan="6">
							<input type="hidden" name="forms[]" value="<?php echo $F->get('id'); ?>" />
							<div class="pull-right">
								<i class="fa fa-arrows fa-lg"></i>
								<?php if ($F->get('type') != 'T') { ?>
								<a href="#" title="<?php echo __('Delete'); ?>" onclick="javascript:
								if (confirm(__('You sure?')))
									var tbody = $(this).closest('tbody');
									tbody.fadeOut(function(){this.remove()});
									$(this).closest('form')
										.find('[name=form_id] [value=' + tbody.data('formId') + ']')
										.prop('disabled', false);
								return false;"><i class="fa fa-2x fa-trash valign-middle"></i></a>
								<?php } ?>
							</div>
							<div><strong><?php echo Format::htmlchars($F->getLocal('title')); ?></strong></div>
							<div><?php echo Format::htmldecode($F->getLocal('instructions')); ?></div>
						</td>
					</tr>
					<tr class="header">
						<th><?php echo __('Enable'); ?></th>
						<th><?php echo __('Label'); ?></th>
						<th><?php echo __('Type'); ?></th>
						<th><?php echo __('Visibility'); ?></th>
						<th><?php echo __('Variable'); ?></th>
					</tr>
					<?php
					foreach ($F->getDynamicFields() as $f) { ?>
						<tr>
							<td><input type="checkbox" name="fields[]" value="<?php
								echo $f->get('id'); ?>" <?php
								if ($f->isEnabled()) echo 'checked="checked"'; ?>/></td>
							<td><?php echo $f->get('label'); ?></td>
							<td><?php $t=FormField::getFieldType($f->get('type')); echo __($t[0]); ?></td>
							<td><?php echo $f->getVisibilityDescription(); ?></td>
							<td><?php echo $f->get('name'); ?></td>
						</tr>
					<?php } ?>
				</tbody>
				<?php } ?>
			</table>
		</div>
	</div>
</div>
<div class="row form-inline">
	<div class="col-md-12">
		<strong><?php echo __('Add Custom Form'); ?></strong>: &nbsp;
		<select name="form_id" id="newform" class="form-control">
			<option value=""><?php echo '&mdash; '.__('Add a custom form') . ' &mdash;'; ?></option>
			<?php foreach (DynamicForm::objects()
				->filter(array('type'=>'G'))
				->exclude(array('flags__hasbit' => DynamicForm::FLAG_DELETED))
				as $F) { ?>
					<option value="<?php echo $F->get('id'); ?>"
					   <?php if (in_array($F->id, $current_forms))
						   echo 'disabled="disabled"'; ?>
					   <?php if ($F->get('id') == $info['form_id'])
							echo 'selected="selected"'; ?>>
					   <?php echo $F->getLocal('title'); ?>
					</option>
			<?php } ?>
		</select>
		<?php
		if(!empty($errors['form_id'])){
			echo '<div class="alert-danger">&nbsp;'.$errors['form_id'].'</div>';
		}
		?>
		&nbsp; <i class="help-tip fa fa-question-circle" href="#custom_form"></i>
	</div>
</div>

<p style="text-align:center;">
	<button type="submit" name="submit" class="btn btn-success">&nbsp;<?php echo esc_attr($submit_text); ?></button>
	<button type="reset" id="reset" name="reset" class="btn btn-info">&nbsp;<?php echo __('Reset');?></button>
	<button type="button" name="cancel" class="btn btn-default" onclick="window.location.href='?'" ><?php echo __('Cancel');?></button>
</p>
</div>
</div>
</div>
</form>
<script type="text/javascript">
var addAccess = function(staffid, name, role, alerts, primary, error) {

  if (!staffid) return;
  var copy = $('#member_template').clone();
  var target = (primary) ? 'extended-access-members' : 'add_extended_access';
  copy.find('td:first').append(name);
  if (primary) {
    copy.find('a.drop-membership').remove();
    copy.attr('bgcolor', '#E8F3FB');
  }
    copy.find('[data-name^=member_alerts]')
      .attr('name', 'member_alerts['+staffid+']')
      .prop('disabled', (primary))
      .prop('checked', primary || alerts);
    copy.find('[data-name^=member_role]')
      .attr('name', 'member_role['+staffid+']')
      .val(role || 0);
    copy.find('[data-name=members\\[\\]]')
      .attr('name', 'members[]')
      .val(staffid);

  copy.attr('id', '').show().insertBefore($('#'+target));
  copy.removeClass('hidden')
  if (error)
      $('<div class="alert-danger">').text(error).appendTo(copy.find('td:nth-child(2)'));
  copy.find('.drop-membership').click(function() {
    $('#add_access').append(
      $('<option>')
      .attr('value', copy.find('input[name^=members][type=hidden]').val())
      .text(copy.find('td:first').text())
    );
    copy.fadeOut(function() { $(this).remove(); });
    return false;
  });
};

$('#add_extended_access').find('button').on('click', function() {
  var selected = $('#add_access').find(':selected'),
      id = parseInt(selected.val());
  if (!id)
    return;
  addAccess(id, selected.text(), 0, true);
  selected.remove();
  return false;
});

<?php
if ($dept) {
    // Primary members
    foreach ($dept->getPrimaryMembers() as $member) {
        $primary = $member->dept_id == $info['id'];
        echo sprintf('addAccess(%d, %s, %d, %d, %d, %s);',
            $member->getId(),
            JsonDataEncoder::encode((string) $member->getName().'&nbsp;&nbsp;<span class="label label-warning">'.__('Primary Member').'</span>'),
            $member->role_id,
            $member->get('alerts', 0),
            ($member->dept_id == $info['id']) ? 1 : 0,
            JsonDataEncoder::encode($errors['members'][$member->staff_id])
        );
    }

    // Extended members.
    foreach ($dept->getExtendedMembers() as $member) {
        echo sprintf('addAccess(%d, %s, %d, %d, %d, %s);',
            $member->getId(),
            JsonDataEncoder::encode((string) $member->getName()),
            $member->role_id,
            $member->get('alerts', 0),
            0,
            JsonDataEncoder::encode($errors['members'][$member->staff_id])
        );
    }
}
?>
$(function() {
    $('form select#newform').change(function() {
        var $this = $(this),
            val = $this.val();
        if (!val) return;
        $.ajax({
            url: 'ajax.php/form/' + val + '/fields/view',
            dataType: 'json',
            success: function(json) {
                if (json.success) {
                    $(json.html).appendTo('#dept-forms').effect('highlight');
                    $this.find(':selected').prop('disabled', true);
                }
            }
        });
    });
    $('table#dept-forms').sortable({
      items: 'tbody',
      handle: 'td.handle',
      tolerance: 'pointer',
      forcePlaceholderSize: true,
      helper: function(e, ui) {
        ui.children().each(function() {
          $(this).children().each(function() {
            $(this).width($(this).width());
          });
        });
        ui=ui.clone().css({'background-color':'white', 'opacity':0.8});
        return ui;
      }
    }).disableSelection();
});
</script>
