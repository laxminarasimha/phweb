<?php
if(!defined('DSKADMININC') || !$thisstaff) die('Access Denied');
$info=$qs = array();
if($canned && $_REQUEST['a']!='add'){
    $title=__('Update Canned Response');
    $action='update';
    $submit_text=__('Save Changes');
    $info=$canned->getInfo();
    $info['id']=$canned->getId();
    $qs += array('id' => $canned->getId());
    // Replace cid: scheme with downloadable URL for inline images
    $info['response'] = $canned->getResponseWithImages();
    $info['notes'] = Format::viewableImages($info['notes']);
}else {
    $title=__('Add New Canned Response');
    $action='create';
    $submit_text=__('Add Response');
    $info['isenabled']=isset($info['isenabled'])?$info['isenabled']:1;
    $qs += array('a' => $_REQUEST['a']);
}
$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);

?>
<div class="panel">
<form action="canned.php?<?php echo Http::build_query($qs); ?>" method="post" class="save" enctype="multipart/form-data">
<?php csrf_token(); ?>
<input type="hidden" name="do" value="<?php echo esc_attr($action); ?>">
<input type="hidden" name="a" value="<?php echo Format::htmlchars($_REQUEST['a']); ?>">
<input type="hidden" name="id" value="<?php echo esc_attr($info['id']); ?>">
 
<div class="panel-heading">
	<div class="panel-title table-caption">
		<?php echo esc_html($title); ?>
		<i class="help-tip fa fa-question-circle" href="#canned_response"></i>
	</div>
</div>
<div class="panel-body">
	<div class="row">
		<div class="col-sm-6 form-group">
			<label class="required"><?php echo __('Status');?>: </label>&nbsp;&nbsp;
			<input type="radio" name="isenabled" value="1" <?php
				echo $info['isenabled']?'checked="checked"':''; ?>>&nbsp;<?php echo __('Active'); ?>
			<input type="radio" name="isenabled" value="0" <?php
				echo !$info['isenabled']?'checked="checked"':''; ?>>&nbsp;<?php echo __('Disabled'); ?>
		<?php if(!empty($errors['isenabled'])){ ?>
				<div class="alert-danger"><?php echo $errors['isenabled']; ?></div>
		<?php } ?>
		</div>
	</div>
	
	<div class="row">
		<div class="col-sm-6 form-group">
			<label class="required"><?php echo __('Title');?>:</label>
		<input type="text" class="form-control" name="title" value="<?php echo esc_attr($info['title']); ?>">
		<?php if(!empty($errors['title'])){ ?>
				<div class="alert-danger"><?php echo $errors['title']; ?></div>
		<?php } ?>
		</div>
		<div class="col-sm-6 form-group">
			<label class="required"><?php echo __('Department');?>:
			</label>
			<select class="form-control" name="dept_id">
				<option value="0">&mdash; <?php echo __('All Departments');?> &mdash;</option>
				<?php
				if (($depts=Dept::getDepartments())) {
					foreach($depts as $id => $name) {
						$selected=($info['dept_id'] && $id==$info['dept_id'])?'selected="selected"':'';
						echo sprintf('<option value="%d" %s>%s</option>',$id,$selected,$name);
					}
				}
				?>
			</select>
		<?php if(!empty($errors['dept_id'])){ ?>
				<div class="alert-danger"><?php echo $errors['dept_id']; ?></div>
		<?php } ?>
		</div>
	</div>
	   
	<div class="row">
		<div class="col-sm-12 form-group">
			<label>
				<?php echo __('Canned Response'); ?>: &nbsp;
				<a class="ajax-tooltip" href="#ticket_variables.txt" data-container="body" data-toggle="popover" data-placement="right">
				<i class="fa fa-tags"></i>
				<?php echo __('Supported Variables'); ?></a>
			</label>
		<?php if(!empty($errors['response'])){ ?>
				<div class="alert-danger"><?php echo $errors['response']; ?></div>
		<?php } ?>
			<textarea name="response" data-root-context="cannedresponse" class="summernote-base draft draft-delete" <?php
			list($draft, $attrs) = Draft::getDraftAndDataAttrs('canned',
			is_object($canned) ? $canned->getId() : false, $info['response']);
			echo $attrs; ?>>
				<?php echo $draft ?: $info['response']; ?>
			</textarea>
		</div>					
	</div>
	
	<div class="row">
		<div class="col-md-12">
			<label>
				<?php echo __('Canned Attachments'); ?> <small><?php echo __('(Optional)'); ?> </small>:
				&nbsp;<i class="help-tip fa fa-question-circle" href="#canned_attachments"></i>
			<?php if(!empty($errors['files'])){ ?>
					<div class="alert-danger"><?php echo $errors['files']; ?></div>
			<?php } ?>
			</label>
			<?php
			$attachments = $canned_form->getField('attachments');
			if ($canned && $attachments) {
				$attachments->setAttachments($canned->attachments);
			}
			print $attachments->render(); ?>
		</div>
	</div>
	<br />
	<div class="panel-heading">
		<a class="internal_note"><div class="panel-title table-caption"><i class="fa fa-plus-square"></i>&nbsp;<?php echo __('Internal Notes');?></div></a>
	</div>
	<div class="panel-body notes" style="display:none;">
		<textarea class="summernote-base" name="notes"><?php echo $info['notes']; ?></textarea>
	</div>
		

<?php if($canned && $canned->getFilters()){ ?>
	<br/>
	<div class="alert alert-warning"><?php echo __('Canned response is in use by email filter(s)');?>: <?php
	echo implode(', ', $canned->getFilters()); ?></div>
<?php } ?>
<br />
<p style="text-align:center;">
    <input type="submit" class="btn btn-success" name="submit" value="<?php echo esc_attr($submit_text); ?>">
    <input type="reset" class="btn btn-info" name="reset"  value="<?php echo __('Reset'); ?>" />
    <input type="button" class="btn btn-default" name="cancel" value="<?php echo __('Cancel'); ?>" onclick='window.location.href="canned.php"'>
</p>
</div>
</form>
</div>
