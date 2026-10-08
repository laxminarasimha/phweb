<?php
if(!defined('DSKADMININC') || !$thisstaff || !$thisstaff->isAdmin()) die('Access Denied');

$info=$qs= array();
if($rule && $_REQUEST['a']!='add'){
    $title=__('Update Ban Rule');
    $action='update';
    $submit_text=__('Update');
    $info=$rule->getInfo();
    $info['id']=$rule->getId();
    $qs += array('id' => $rule->getId());
}else {
    $title=__('Add New Email Address to Ban List');
    $action='add';
    $submit_text=__('Add');
    $info['isactive']=isset($info['isactive'])?$info['isactive']:1;
    $qs += array('a' => $_REQUEST['a']);
}

$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);
?>
<div class="panel">
<form action="banlist.php?<?php echo Http::build_query($qs); ?>" method="post" class="save">
<?php csrf_token(); ?>
<input type="hidden" name="do" value="<?php echo esc_attr($action); ?>">
<input type="hidden" name="a" value="<?php echo esc_attr(Format::htmlchars($_REQUEST['a'])); ?>">
<input type="hidden" name="id" value="<?php echo esc_attr($info['id']); ?>">
<div class="panel-heading">
    <div class="panel-title table-caption"><?php echo esc_html($title); ?>
    <i class="help-tip fa fa-question-circle" href="#ban_list"></i>
    </div>
</div>
<div class="panel-body">
	<div class="row">
		<div class="col-sm-6 form-group">
			<label class="required" for="isactive"><?php echo __('Ban Status');?>: </label>
			<p>
				<input type="radio" name="isactive" id="isactive" value="1" <?php echo dsk_POSTradio('isactive', 1, $info['isactive']); ?> >&nbsp;<strong><?php echo __('Active');?></strong>&nbsp;&nbsp;
				<input type="radio" name="isactive" id="isactive" value="0" <?php echo dsk_POSTradio('isactive', 0, $info['isactive']); ?> >&nbsp;<strong><?php echo __('Disabled');?></strong>
			</p>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-6 form-group">
			<label class="required" for="val"><?php echo __('Email Address');?>: </label>
			<input name="val" id="val" type="text" class="form-control" size="24" value="<?php echo esc_attr($info['val']); ?>">
			<?php
			if(!empty($errors['val'])){
				echo '<div class="alert-danger">&nbsp;'.$errors['val'].'</div>';
			}
			?>
		</div>
	</div>
		<div class="panel-heading">
			<a class="internal_note"><div class="panel-title table-caption"><i class="fa fa-plus-square"></i>&nbsp;<em><strong><?php echo __('Internal Notes');?></strong>: <?php echo __('Admin Notes');?>&nbsp;</em></div></a>
		</div>
		<div class="panel-body notes" style="display:none;">
			<textarea class="summernote-base no-bar" name="notes" cols="21" rows="8" style="width: 80%;"><?php echo $info['notes']; ?></textarea>	
		</div>

	<div class="form-group" style="text-align:center;margin-top:20px;">
		<input type="submit" class="btn btn-success" name="submit" value="<?php echo esc_attr($submit_text); ?>">
		<input type="reset" class="btn btn-info"  name="reset"  value="<?php echo __('Reset');?>">
		<input type="button" class="btn btn-default" name="cancel" value="<?php echo __('Cancel');?>" onclick='window.location.href="banlist.php"'>
	</div>
<br/>
</form>
</div>
</div>
