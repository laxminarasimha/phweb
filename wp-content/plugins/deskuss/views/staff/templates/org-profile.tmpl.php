<?php
$info=($_POST && $errors)?Format::input($_POST):@Format::htmlchars($org->getInfo());

if (!$info['title'])
    $info['title'] = Format::htmlchars($org->getName());
?>
<div class="panel panel-primary panel-dark m-b-0">
	<div class="panel-heading">
		<div class="drag-handle panel-title">
			<?php echo $info['title']; ?><a class="close_me pull-right" href="#"><i class="fa fa-remove"></i></a>
		</div>
	</div>

	<div class="panel-body perfectScrollbar" style="height: 400px;position: relative;">
		<?php
		if ($info['error']) {
			echo sprintf('<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">×</button><i class="fa fa-exclamation-triangle"></i>&nbsp;&nbsp;%s</div>', $info['error']);
		} elseif ($info['msg']) {
			echo sprintf('<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert">×</button><i class="fa fa-check"></i>&nbsp;&nbsp;%s</div>', $info['msg']);
		} ?>
		<ul class="nav nav-tabs" id="orgprofile">
			<li class="active">
				<a data-toggle="tab" href="#profile"
				><i class="fa fa-check-square-o"></i>&nbsp;<?php echo __('Fields'); ?></a>
			</li>
			<li>
				<a data-toggle="tab" href="#contact-settings"
				><i class="fa fa-cogs"></i>&nbsp;<?php
				echo __('Settings'); ?></a>
			</li>
		</ul>
		<form method="post" class="org" action="<?php echo $action; ?>" data-tip-namespace="org">
		<div id="orgprofile_container" class="tab-content tab-content-bordered">
			<div class="tab-pane fade in active" id="profile" style="margin:5px;">
			<?php
			$action = $info['action'] ? $info['action'] : ('#orgs/'.$org->getId());
			if ($ticket && $ticket->getOwnerId() == $user->getId())
				$action = '#tickets/'.$ticket->getId().'/user';
			?>
				<input type="hidden" name="id" value="<?php echo $org->getId(); ?>" />
				<?php
					if (!$forms) $forms = $org->getForms();
					foreach ($forms as $form)
						$form->render();
				?>
			</div>
			<div class="tab-pane fade" id="contact-settings" style="margin:5px;">
				<fieldset class="form-group form-inline row">
					<div class="col-md-3">
						<?php echo __('Account Manager'); ?>:
					</div>
					<div class="col-md-9">
						<select class="form-control" name="manager">
							<option value="0" selected="selected">&mdash; <?php
								echo __('None'); ?> &mdash;</option><?php
							if ($users=Staff::getAvailableStaffMembers()) { ?>
								<optgroup label="<?php
									echo sprintf(__('Agents (%d)'), count($users)); ?>">
	<?php                       foreach($users as $id => $name) {
									$k = "s$id";
									echo sprintf('<option value="%s" %s>%s</option>',
										$k,(($info['manager']==$k)?'selected="selected"':''),$name);
								}
								echo '</optgroup>';
							}
	
							if ($teams=Team::getActiveTeams()) { ?>
								<optgroup label="<?php echo sprintf(__('Teams (%d)'), count($teams)); ?>">
	<?php                       foreach($teams as $id => $name) {
									$k="t$id";
									echo sprintf('<option value="%s" %s>%s</option>',
										$k,(($info['manager']==$k)?'selected="selected"':''),$name);
								}
								echo '</optgroup>';
							} ?>
						</select>
						<?php
							if(!empty($errors['manager'])){
								echo '<div class="alert-danger">&nbsp;'.$errors['manager'].'</div>';
							}
						?>
					</div>
				</fieldset>
				<fieldset class="form-group row">
					<div class="col-md-3 form-group">
						<?php echo __('Auto-Assignment'); ?>:
					</div>
					<div class="col-md-9 form-group">
						<input type="checkbox" name="assign-am-flag" value="1" <?php echo $info['assign-am-flag']?'checked="checked"':''; ?>>
						<?php echo __(
						'Assign tickets from this organization to the <em>Account Manager</em>'); ?>
					</div>	
				</fieldset>
				<fieldset class="form-group row">
					<div class="col-md-3 form-group">
						<?php echo __('Primary Contacts'); ?>:
					</div>
					<div class="col-md-9 form-group">
						<select class="form-control" name="contacts[]" id="primary_contacts" multiple="multiple"
							data-placeholder="<?php echo __('Select Contacts'); ?>">
							<option value=""></option>
			<?php               foreach ($org->allMembers() as $u) { ?>
							<option value="<?php echo $u->id; ?>" <?php
								if ($u->isPrimaryContact())
								echo 'selected="selected"'; ?>><?php echo $u->getName(); ?></option>
			<?php               } ?>
						</select>
						<?php
							if(!empty($errors['contacts'])){
								echo '<div class="alert-danger">&nbsp;'.$errors['contacts'].'</div>';
							}
						?>
					</div>
				</fieldset>
				<fieldset class="form-group row">
					<div class="col-md-3 form-group">
						<?php echo __('Ticket Sharing'); ?>:
					</div>
					<div class="col-md-9 form-group">
						<select class="form-control" name="sharing">
							<option value=""><?php echo __('Disable'); ?></option>
							<option value="sharing-primary" <?php echo $info['sharing-primary'] ? 'selected="selected"' : '';
								?>><?php echo __('Primary contacts see all tickets'); ?></option>
							<option value="sharing-all" <?php echo $info['sharing-all'] ? 'selected="selected"' : '';
								?>><?php echo __('All members see all tickets'); ?></option>
						</select>
						<i class="help-tip fa fa-question-circle" href="#org_sharing"></i>
					</div>
				</fieldset>
				<fieldset class="form-group">
					<label class="control-label">
						<?php echo __('Automated Collaboration'); ?>:
					</label>	
				</fieldset>
				<fieldset class="form-group row">
					<div class="col-md-3 form-group">
						<?php echo __('Primary Contacts'); ?>:
					</div>
					<div class="col-md-9 form-group">
						<input type="checkbox" name="collab-pc-flag" value="1" <?php echo $info['collab-pc-flag']?'checked="checked"':''; ?>>
						<?php echo __('Add to all tickets from this organization'); ?>
					</div>
				</fieldset>
				<fieldset class="form-group row">
					<div class="col-md-3 form-group">
						<?php echo __('Organization Members'); ?>:
					</div>
					<div class="col-md-9 form-group">
						<input type="checkbox" name="collab-all-flag" value="1" <?php echo $info['collab-all-flag']?'checked="checked"':''; ?>>
						<?php echo __('Add to all tickets from this organization'); ?>
					</div>
				</fieldset>
				<fieldset class="form-group">
					<label class="control-label">
						<?php echo __('Email Domain'); ?>
						<i class="help-tip fa fa-question-circle" href="#email_domain"></i>
					</label>
				</fieldset>
				<fieldset class="form-group form-inline row">
					<div class="col-md-3 form-group">
						<?php echo __('Auto Add Members From'); ?>:
					</div>
					<div class="col-md-9 form-group">
						<input type="text" size="40" maxlength="60" name="domain"
							value="<?php echo $info['domain']; ?>" />
							<?php
							if(!empty($errors['domain'])){
								echo '<div class="alert-danger">&nbsp;'.$errors['domain'].'</div>';
							}
						?>
					</div>
				</fieldset>
			</div>
		</div>
		<div class="form-group" style="margin-top:20px;">
			<input type="submit" class="btn btn-success" value="<?php echo __('Update Organization'); ?>">
			<input type="reset" class="btn btn-info" value="<?php echo __('Reset'); ?>">
			<input type="button" name="cancel" class="btn <?php
				echo $account ? 'cancel' : 'close_me'; ?>"  value="<?php echo __('Cancel'); ?>">
		</div>
		</form>
	</div>
</div>
<script type="text/javascript">
$(document).ready(function (){
              $('.perfectScrollbar').perfectScrollbar();
          });

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
    $("#primary_contacts").select2({width: '300px'});
});
</script>
