<?php
if(!defined('DSKADMININC') || !$thisstaff || !$thisstaff->isAdmin()) die('Access Denied');
$info = $qs = array();
if($sla && $_REQUEST['a']!='add'){
    $title=__('Update SLA Plan' /* SLA is abbreviation for Service Level Agreement */);
    $action='update';
    $submit_text=__('Save Changes');
    $info=$sla->getInfo();
    $info['id']=$sla->getId();
    $trans['name'] = $sla->getTranslateTag('name');
    $qs += array('id' => $sla->getId());
}else {
    $title=__('Add New SLA Plan' /* SLA is abbreviation for Service Level Agreement */);
    $action='add';
    $submit_text=__('Add Plan');
    $info['isactive']=isset($info['isactive'])?$info['isactive']:1;
    $info['enable_priority_escalation']=isset($info['enable_priority_escalation'])?$info['enable_priority_escalation']:1;
    $info['disable_overdue_alerts']=isset($info['disable_overdue_alerts'])?$info['disable_overdue_alerts']:0;
    $qs += array('a' => $_REQUEST['a']);
}
$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);
?>
<div class="panel">
<form action="slas.php?<?php echo Http::build_query($qs); ?>" method="post" class="save">
<?php csrf_token(); ?>
<input type="hidden" name="do" value="<?php echo $action; ?>">
<input type="hidden" name="a" value="<?php echo Format::htmlchars($_REQUEST['a']); ?>">
<input type="hidden" name="id" value="<?php echo $info['id']; ?>">

<div class="panel-heading">
	<div class="panel-title table-caption"><?php echo $title; ?> — 
		<small class="text-muted"><em>
			<?php echo __('Tickets are marked overdue on grace period violation'); ?>
		</em></small>
	</div>
</div>

<div class="panel-body" style="padding-bottom:0px">
	<div class="row">
		<div class="col-sm-6 form-group">
			<label class="required"><?php echo __('Name'); ?>:
			&nbsp;<i class="help-tip fa fa-question-circle" href="#name"></i>
			</label>
			<input type="text" size="30" class="form-control" name="name" value="<?php echo $info['name']; ?>"
					autofocus data-translate-tag="<?php echo $trans['name']; ?>"/>
			<?php
			if(!empty($errors['name'])){
				echo '<div class="alert-danger">&nbsp;'.$errors['name'].'</div>';
			}
			?>
		</div>
		<div class="col-sm-6 form-group">
			<label class="required"><?php echo __('Grace Period'); ?>: &nbsp;
			<i class="help-tip fa fa-question-circle" href="#grace_period"></i>
			</label>
			<div class="input-group">
				<input type="text" size="10" class="form-control" name="grace_period" value="<?php echo $info['grace_period']; ?>">
				<span class="input-group-addon">hours</span>
			</div> 
			<?php
			if(!empty($errors['grace_period'])){
				echo '<div class="alert-danger">&nbsp;'.$errors['grace_period'].'</div>';
			}
			?>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-6 form-group">
			<label class="required" style="display:block"><?php echo __('Status'); ?>:
			</label>

			<input type="radio" name="isactive" value="1" <?php echo $info['isactive']?'checked="checked"':''; ?>>&nbsp;<strong><?php echo __('Active');?></strong>&nbsp;
			<input type="radio" name="isactive" value="0" <?php echo !$info['isactive']?'checked="checked"':''; ?>>&nbsp;<strong><?php echo __('Disabled');?></strong>
			<?php
			if(!empty($errors['isactive'])){
				echo '<div class="alert-danger">&nbsp;'.$errors['isactive'].'</div>';
			}
			?>
		</div>
		<div class="col-sm-6 form-group">
			<label style="display:block"><?php echo __('Transient'); ?>:
			&nbsp;<i class="help-tip fa fa-question-circle" href="#transient"></i>

			</label>
			<input type="checkbox" name="transient" value="1" <?php echo $info['transient']?'checked="checked"':''; ?> >
				<?php echo __('SLA can be overridden on ticket transfer or department change'); ?>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-6 form-group">
			<label style="display:block"><?php echo __('Ticket Overdue Alerts'); ?>:
			&nbsp;<i class="help-tip fa fa-question-circle" href="#transient"></i>

			</label>
			<input type="checkbox" name="disable_overdue_alerts" value="1" <?php echo $info['disable_overdue_alerts']?'checked="checked"':''; ?> >
				<?php echo __('<strong>Disable</strong> overdue alerts notices.'); ?>
				<em><?php echo __('(Override global setting)'); ?></em>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-12 form-group">
			<div class="panel-heading">
				<a class="internal_note"><div class="panel-title table-caption">
					<i class="fa fa-plus-square"></i>&nbsp;<em><strong><?php echo __('Internal Notes');?></strong>:<small class="text-muted"> <?php echo __("Be liberal, they're internal");?></small></em></div>
				</a>
			</div>
			<div class="panel-body notes" style="display:none">
				<textarea class="summernote-base no-bar " name="notes" cols="21" rows="8" style="width: 100%;"><?php echo $info['notes']; ?></textarea>
			</div>	
		</div>
	</div>
</div>

<div class="form-group" style="text-align:center; margin-top:20px;">
    <input type="submit" name="submit" class="btn btn-success" value="<?php echo $submit_text; ?>">
    <input type="reset"  name="reset"  class="btn btn-info" value="<?php echo __('Reset');?>">
    <input type="button" name="cancel" class="btn btn-default" value="<?php echo __('Cancel');?>" onclick='window.location.href="slas.php"'>
</div>
</form>
</div>
