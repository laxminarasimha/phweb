<?php
if(!defined('DSKADMININC') || !$thisstaff || !$thisstaff->isAdmin()) die('Access Denied');
$info = $members = $qs = array();
if ($team && $_REQUEST['a']!='add') {
    //Editing Team
    $title=__('Update Team');
    $action='update';
    $submit_text=__('Save Changes');
    $trans['name'] = $team->getTranslateTag('name');
    $members = $team->getMembers();
    $qs += array('id' => $team->getId());
} else {
    $title=__('Add New Team');
    $action='create';
    $submit_text=__('Create Team');
    if (!$team) {
        $team = Team::create(array(
            'flags' => Team::FLAG_ENABLED,
        ));
    }
    $qs += array('a' => $_REQUEST['a']);
}

$info = $team->getInfo();
?>
<div class="panel">
	<form action="teams.php?<?php echo Http::build_query($qs); ?>" method="post"	class="save">
	<?php csrf_token(); ?>
		 <input type="hidden" name="do" value="<?php echo $action; ?>">
		 <input type="hidden" name="a" value="<?php echo Format::htmlchars($_REQUEST['a']); ?>">
		 <input type="hidden" name="id" value="<?php echo $team->getId(); ?>">
			 <div class="panel-heading">
				 <div class="panel-title table-caption"><?php echo $title; ?>
					<?php if (isset($team->name)) { ?><small>
					— <?php echo $team->getName(); ?></small>
					<?php } ?>
					<i class="help-tip fa fa-question-circle" href="#teams"></i>
				</div>
			</div>
			<br>
		<div class="panel-body">	
			<div class="panel-heading">
				<div class="panel-title table-caption"><?php echo __('Team Information'); ?></div>
			</div>
			<div class="panel-body">
				<div id="team" class="row">
				<div class="col-md-6">
				<fieldset class="form-group row form-inline">
						<div class="col-md-3">
							<label class="control-label required"><?php echo __('Name');?>:</label>
						</div>
						<div class="col-md-9">
							<input class="form-control" type="text" size="30" name="name" value="<?php echo Format::htmlchars($team->name); ?>"
								autofocus data-translate-tag="<?php echo $trans['name']; ?>"/>
							&nbsp;
							<?php
								if(!empty($errors['name'])){
									echo '<div class="alert-danger">&nbsp;'.$errors['name'].'</div>';
								}
							?>
						</div>
				</fieldset>
				<fieldset class="form-group row form-inline">	
						<div class="col-md-3">
							<label class="control-label required"><?php echo __('Status');?>:</label>
						</div>
						<div class="col-md-9">
							<span>
							<input type="radio" name="isenabled" value="1" <?php echo $team->isEnabled()?'checked="checked"':''; ?>><strong><?php echo __('Active');?></strong>
							&nbsp;
							<input type="radio" name="isenabled" value="0" <?php echo !$team->isEnabled()?'checked="checked"':''; ?>><?php echo __('Disabled');?>
							&nbsp;
							<i class="help-tip fa fa-question-circle" href="#status"></i>
							</span>
						</div>
				</fieldset>	
				</div>
				<div class="col-md-6">
				<fieldset class="form-group row form-inline">	
						<div class="col-md-3">
							<?php echo __('Team Lead');?>: &nbsp;<i class="help-tip fa fa-question-circle" href="#lead"></i>
						</div>
						<div class="col-md-9">
							<span>
							<select class="form-control" id="team-lead-select" name="lead_id" data-quick-add="staff">
								<option value="0">&mdash; <?php echo __('None');?> &mdash;</option>
			<?php               if ($members) {
									foreach($members as $k=>$staff){
										$selected=($team->lead_id && $staff->getId()==$team->lead_id)?'selected="selected"':'';
										echo sprintf('<option value="%d" %s>%s</option>',$staff->getId(),$selected,$staff->getName());
									}
								} ?>
								<option value="0" data-quick-add>&mdash; <?php echo __('Add New');?> &mdash;</option>
							</select>
							<?php
								if(!empty($errors['lead_id'])){
									echo '<div class="alert-danger">&nbsp;'.$errors['lead_id'].'</div>';
								}
							?>
							</span>
						</div>
				</fieldset>	
					<fieldset class="form-group row form-inline">
						<div class="col-md-3">
							<?php echo __('Assignment Alert');?>:
						</div>
						<div class="col-md-9 checkbox">
						<label>
							<input type="checkbox" name="noalerts" value="1" <?php echo !$team->alertsEnabled()?'checked="checked"':''; ?> >
							<?php echo sprintf(__('<strong>Disable</strong> for %s'), __('this team')); ?>
							<i class="help-tip fa fa-question-circle" href="#assignment_alert"></i>
						</label>	
						</div>
					</fieldset>
				</div>	
			</div>
		</div>	
		<div class="panel-heading">
			<div class="panel-title table-caption"><?php echo __('Team Members'); ?></div>
		</div>
		<div class="panel-body">
			
			<?php
			$agents = Staff::getStaffMembers();
			foreach ($members as $m)
				unset($agents[$m->staff_id]);
			?>
			
			<div id="members">
				<fieldset class="form-group">
					<div class="form-group">
						<div>
							<p>
								<?php echo sprintf(__('Agents who are members of %s'), __('this team')); ?>&nbsp;<i class="help-tip fa fa-question-circle" href="#members"></i>
							</p>
						</div>
					</div>
				
					<div class="form-group form-inline" id="add_member">
						<i class="fa fa-plus-circle"></i>
						<select class="form-control" id="add_access" data-quick-add="staff">
							<option value="0">&mdash; <?php echo __('Select Agent');?> &mdash;</option>
							<?php
							foreach ($agents as $id=>$name) {
							echo sprintf('<option value="%d">%s</option>',$id,Format::htmlchars($name));
							}
							?>
							<option value="0" data-quick-add>&mdash; <?php echo __('Add New');?> &mdash;</option>
						</select>
						<button type="button" class="action-button btn btn-success">
							<?php echo __('Add'); ?>
						</button>
					</div>
				</fieldset> 
				<fieldset class="form-group">
					<div style="border-bottom:1px solid #D3D3D3;" id="member_template" class="hidden form-group row">
						<div class="col-md-6">
							<input type="hidden" data-name="members[]" value="" />
						</div>
						<div class="col-md-6">
							<label>
								<input type="checkbox" data-name="member_alerts" value="1" />
								<?php echo __('Alerts'); ?>
							</label>
							<a data-type="warning" data-message='You must click "<?php echo $submit_text; ?>" to apply the new changes' href="#" class=" growl_popup pull-right drop-membership" title="<?php 	echo __('Delete');?>"><i class="fa fa-trash"></i>
							</a>
						</div>
					</div>
				</fieldset>
			</div>
			
		</div>
		
			<div class="form-group">
				<div class="panel-heading">
					<a class="internal_note"><div class="panel-title table-caption"><i class="fa fa-plus-square"></i>&nbsp;<?php echo __('Admin Notes');?>:<small class="text-muted"><em> <?php echo __('Internal notes viewable by all admins.');?>&nbsp;</em></small></div>
					</a>
				</div>
				<div class="panel-body notes" style="display:none;">
					<textarea  class="summernote-base no-bar" name="notes" cols="21" rows="8" style="width: 80%;"><?php echo Format::htmlchars($team->notes); ?></textarea>
				</div>
			</div>

			<div class="form-group" style="text-align:center;margin-top:20px;">
				<input type="submit" class="btn btn-success" name="submit" value="<?php echo $submit_text; ?>">
				<input type="reset" class="btn btn-info"  name="reset"  value="<?php echo __('Reset');?>">
				<input type="button" class="btn" name="cancel" value="<?php echo __('Cancel');?>" onclick='window.location.href="?"'>
			</div>
			</div>
		</form>
</div>

<script type="text/javascript">
var addMember = function(staffid, name, alerts, error) {
  if (!staffid) return;
  var copy = $('#member_template').clone();

  copy.find('[data-name=members\\[\\]]')
    .attr('name', 'members[]')
    .val(staffid);
  copy.find('[data-name^=member_alerts]')
    .attr('name', 'member_alerts['+staffid+']')
    .prop('checked', alerts);
  copy.find('div:first').append(document.createTextNode(name));
  copy.attr('id', '').show().insertBefore($('#add_member'));
  copy.removeClass('hidden')
  if (error)
      $('<div class="error">').text(error).appendTo(copy.find('div:last')).addClass('alert-danger');
};

$('#add_member').find('button').on('click', function() {
  var selected = $('#add_access').find(':selected'),
      id = parseInt(selected.val());
  if (!id)
    return;
  addMember(id, selected.text(), true);
  if ($('#team-lead-select option[value='+id+']').length === 0) {
    $('#team-lead-select').find('option[data-quick-add]')
    .before(
      $('<option>').val(selected.val()).text(selected.text())
    );
  }
  selected.remove();
  return false;
});

$(document).on('click', 'a.drop-membership', function() {
  var tr = $(this).parent('div').parent('div'),
      id = tr.find('input[name^=members][type=hidden]').val();
  $('#add_access').append(
    $('<option>')
    .attr('value', id)
    .text(tr.find('div:first').text())
  );
  $('#team-lead-select option[value='+id+']').remove();
  tr.fadeOut(function() { $(this).remove(); });
  return false;
});

<?php
if ($team) {
    foreach ($team->members->sort(function($a) { return $a->staff->getName(); }) as $member) {
        echo sprintf('addMember(%d, %s, %d, %s);',
            $member->staff_id,
            JsonDataEncoder::encode((string) $member->staff->getName()),
            $member->isAlertsEnabled(),
            JsonDataEncoder::encode($errors['members'][$member->staff_id])
        );
    }
}
?>
</script>
