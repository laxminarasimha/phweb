<?php
if(!defined('DSKCLIENTINC')) die('Access Denied!');
$info=array();
if($thisclient && $thisclient->getId()) {
    $info=array('name'=>$thisclient->getName(),
                'email'=>$thisclient->getEmail(),
                'phone'=>$thisclient->getPhoneNumber());
}

$info=($_POST && $errors)?Format::htmlchars($_POST):$info;

$form = null;
if (!$info['deptId']) {
    if (array_key_exists('deptId',$_GET) && preg_match('/^\d+$/',$_GET['deptId']) && Dept::lookup($_GET['deptId']))
        $info['deptId'] = intval($_GET['deptId']);
    else
        $info['deptId'] = $cfg->getDefaultDeptId() ?: 0;
}

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

?>
<div class="panel col-sm-8" style="float:none; margin:auto;padding:0;">
<div class="panel-heading">
	<div class="panel-title"><span class="table-caption"><?php echo __('Open a New Ticket');?></span>
	- <?php echo __('Please fill in the form below to open a new ticket');?>
	</div>
</div>
<div class="panel-body">
<form id="ticketForm" method="post" action="open.php" enctype="multipart/form-data">
  <?php csrf_token(); ?>
  <input type="hidden" name="a" value="open">
      	
	<div class="row">
	  <div class="col-md-12">
		<div class="table-light table-responsive">
	 <table class="table" cellpadding="1" cellspacing="0" border="0">
    <tbody>
<?php
        if (!$thisclient) {
            $uform = UserForm::getUserForm()->getForm($_POST);
            if ($_POST) $uform->isValid();
            $uform->render(false);
        }
        else { ?>
        <tr><td><?php echo __('Email'); ?>:</td><td><?php
            echo esc_html($thisclient->getEmail()); ?></td></tr>
        <tr><td><?php echo __('Client'); ?>:</td><td><?php
            echo Format::htmlchars($thisclient->getName()); ?></td></tr>
        <?php } ?>
    </tbody>
	
    <tbody>
    <?php
    if($cfg && $cfg->isCaptchaEnabled() && (!$thisclient || !$thisclient->getId())) {
        if($_POST && $errors && !$errors['captcha'])
            $errors['captcha']=__('Please re-enter the text again');
        ?>
    <tr class="captchaRow">
        <td><label class="required"><?php echo __('CAPTCHA Text');?>:</label></td>
        <td>
            <span class="captcha"><img src="captcha.php" border="0" align="left"></span>
            &nbsp;&nbsp;
            <input id="captcha" type="text" name="captcha" size="6" autocomplete="off">
            <em><?php echo __('Enter the text shown on the image.');?></em>
			<?php
			if(!empty($errors['captcha'])){
					echo '<div class="alert-danger">&nbsp;'.$errors['captcha'].'</div>';
			}
			?>
        </td>
    </tr>
    <?php
    } ?>
    <tr><td colspan=2></td></tr>
    </tbody>
  </table>
  </div>
  </div>
  </div>
  
  <div class="row">
	<div class="form-group form-group-lg" style="padding:0 20px;float:none">
        <label for="deptId" style="text-align:left;" class="required"><?php echo __('Department'); ?>:
        </label>
		<select id="deptId" class="form-control" name="deptId" onchange="javascript:
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
                <option value="" selected="selected">&mdash; <?php echo __('Select a Department');?> &mdash;</option>
                <?php
                if($depts=Dept::getPublicDepartments()) {
                    foreach($depts as $id =>$name) {
                        echo sprintf('<option value="%d" %s>%s</option>',
                                $id, ($info['deptId']==$id)?'selected="selected"':'', esc_html($name));
                    }
                } else { ?>
                    <option value="0" ><?php echo __('General Inquiry');?></option>
                <?php
                } ?>
            </select>
			<?php
			if(!empty($errors['deptId'])){
					echo '<div class="alert-danger">&nbsp;'.$errors['deptId'].'</div>';
			}
			?>
    </div>
    </div>
	
<div id="dynamic-form">
	<?php foreach ($forms as $form) {
		include(CLIENTINC_DIR . 'templates/dynamic-form.tmpl.php');
	} ?>
</div>



<p class="buttons" style="text-align:center;">
	<input type="submit" class="btn btn-success" value="<?php echo __('Create Ticket');?>">
	<input type="reset" class="btn btn-info" name="reset" value="<?php echo __('Reset');?>">
	<input type="button" class="btn btn-default" name="cancel" value="<?php echo __('Cancel'); ?>" onclick="javascript:
	$('.richtext').each(function() {
		var redactor = $(this).data('redactor');
		if (redactor && redactor.opts.draftDelete)
			redactor.deleteDraft();
	});
	window.location.href='index.php';">
</p>
</form>
</div>
</div>