<?php
if(!defined('DSKADMININC') || !$thisstaff || !$thisstaff->isAdmin()) die('Access Denied');

$info=$qs = array();
if($api && $_REQUEST['a']!='add'){
    $title=__('Update API Key');
    $action='update';
    $submit_text=__('Save Changes');
    $info=$api->getHashtable();
    $qs += array('id' => $api->getId());
}else {
    $title=__('Add New API Key');
    $action='add';
    $submit_text=__('Add Key');
    $info['isactive']=isset($info['isactive'])?$info['isactive']:1;
    $qs += array('a' => $_REQUEST['a']);
}
$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);
?>
<div class="panel">
<form action="apikeys.php?<?php echo Http::build_query($qs); ?>" method="post" class="save">
 <?php csrf_token(); ?>
  <input type="hidden" name="do" value="<?php echo esc_attr($action); ?>">
  <input type="hidden" name="a" value="<?php echo esc_attr(Format::htmlchars($_REQUEST['a'])); ?>">
  <input type="hidden" name="id" value="<?php echo esc_attr($info['id']); ?>">
<div class="panel-heading">
  <div class="panel-title table-caption"><?php echo esc_html($title); ?>
    <?php if (isset($info['ipaddr'])) { ?><small>
    — <?php echo esc_html($info['ipaddr']); ?></small>
    <?php } ?>
    <i class="help-tip fa fa-question-circle" href="#api_key"></i>
  </div>
</div>
<div class="panel-body">
<div class="panel">
<div class="panel-heading">
	<div class="panel-title">
		<em><strong><?php echo __('API Key is auto-generated. Delete and re-add to change the key.');?></strong></em>
	</div>
</div>

<div class="panel-body" style="padding-bottom:0px">
	<div class="row">
		<div class="col-sm-2 form-group">
		  <label class="required" style="display:block"><?php echo __('Status'); ?>:
		 </label>
		 </div>
		 <div class="col-sm-4 form-group">
		 <input type="radio" name="isactive" value="1" <?php echo dsk_POSTradio('isactive', 1, $info['isactive']); ?> ><strong>&nbsp;<?php echo __('Active');?></strong>&nbsp;
		 <input type="radio" name="isactive" value="0" <?php echo dsk_POSTradio('isactive', 0, $info['isactive']); ?> ><strong>&nbsp;<?php echo __('Disabled');?></strong>
		</div>
	</div>
		<?php if($api){ ?>
	<div class="row">
		<div class="col-sm-2 form-group">
			<label><?php echo __('IP Address'); ?>: &nbsp; <i class="help-tip fa fa-question-circle" href="#ip_addr"></i></label>
		</div>
		<div class="col-sm-4 form-group">
		<?php echo esc_html($api->getIPAddr()); ?>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-2 form-group">
		<label><?php echo __('API Key'); ?>:</label>
		</div>
		<div class="col-sm-4 form-group">
		<?php echo esc_html($api->getKey()); ?>
		</div>
	</div>
		<?php }else{ ?>
	<div class="row">
		<div class="col-sm-2 form-group">
			<label class="required"><?php echo __('IP Address'); ?>: &nbsp;<i class="help-tip fa fa-question-circle" href="#ip_addr"></i>
			</label>
		</div>
		<div class="col-sm-4 form-group">
			<input type="text" class="form-control" size="30" name="ipaddr" value="<?php echo esc_attr(dsk_POSTval('ipaddr', $info['ipaddr'])); ?>"i
                    autofocus>
			<?php
			if(!empty($errors['ipaddr'])){
				echo '<div class="alert-danger">&nbsp;'.$errors['ipaddr'].'</div>';
			}
			?>
		</div>
	</div>
	<?php } ?>
</div>

<div class="row">
	<div class="col-sm-12 form-group">
	<div class="panel-heading">
	  <div class="panel-title"><em><strong><?php echo __('Services');?>:</strong> <?php echo __('Check applicable API services enabled for the key.');?></em>
	  </div>  
	</div>
	</div>
</div>

<div class="panel-body" style="padding-bottom:0px">
	<div class="row">
		<div class="col-sm-6 form-group">
		  <label>
			<input type="checkbox" name="can_create_tickets" value="1" <?php echo dsk_POSTchecked('can_create_tickets', $info['can_create_tickets']); ?> >
			<?php echo __('Can Create Tickets <em>(XML/JSON/EMAIL)</em>');?>
          </label>              
		</div>
	</div>
	<div class="row">
		<div class="col-sm-6 form-group">
		  <label>
			<input type="checkbox" name="can_exec_cron" value="1" <?php echo dsk_POSTchecked('can_exec_cron', $info['can_exec_cron']); ?> >
                  <?php echo __('Can Execute Cron');?>
		  </label>              
		</div>
	</div>
	
	<div class="row">
		<div class="col-sm-12 form-group">
		<div class="panel-heading">
		  <a class="internal_note"><div class="panel-title">
			<i class="fa fa-plus-square"></i>&nbsp;<em><strong><?php echo __('Internal Notes');?></strong>: <?php echo __("Be liberal, they're internal");?></em></div></a>
		</div>	
		<div class="panel-body notes" style="display:none">	
		<textarea  class="summernote-base no-bar" name="notes" cols="21" rows="8" style=""><?php echo esc_html(dsk_optPOST('notes') ? dsk_optPOST('notes') : $info['notes']); ?></textarea>
		 </div> 
	</div>
	
</div>
</div>

<div class="form-group" style="text-align:center; margin-top:20px;">
    <input type="submit" name="submit" class="btn btn-success"  value="<?php echo esc_attr($submit_text); ?>">
    <input type="reset"  name="reset" class="btn btn-info"  value="<?php echo __('Reset');?>">
    <input type="button" name="cancel" class="btn btn-default"  value="<?php echo __('Cancel');?>" onclick='window.location.href="apikeys.php"'>
</div>
</div>
</form>
</div>
</div>
