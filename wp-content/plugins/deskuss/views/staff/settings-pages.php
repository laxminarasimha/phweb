<?php
if(!defined('DSKADMININC') || !$thisstaff || !$thisstaff->isAdmin() || !$config) die('Access Denied');
$pages = Page::getPages();
?>
<div class="panel">
<div class="panel-heading">
	<div class="panel-title table-caption">
		<?php echo __('Company Profile'); ?>
	</div>
</div>
<div class="panel-body">
<form action="settings.php?t=pages" method="post" class="save" id="save" enctype="multipart/form-data">
<?php csrf_token(); ?>
<input type="hidden" name="t" value="pages">

<?php
	$form = $dsk->company->getForm();
	$form->addMissingFields();
	$form->render();
?>

<div class="panel-heading">
	<div class="panel-title table-caption">
		<?php echo __('Site Pages'); ?>
	</div>
</div>
<div class="panel-body">
	<div class="col-sm-12">
		<em><?php echo sprintf(__(
			'To edit or add new pages go to %s Manage &gt; Site Pages %s'),
			'<a href="pages.php">','</a>'); ?>
		</em>
		<br /><br />
	</div>
	<div class="row">
		<div class="col-sm-6 form-group">
			<label class="required">
				<?php echo __('Landing Page'); ?>:&nbsp;
				<i class="help-tip fa fa-question-circle" href="#landing_page"></i>
			</label>
			<select class="form-control" name="landing_page_id">
				<option value="">&mdash; <?php echo __('Select Landing Page'); ?> &mdash;</option>
				<?php
				foreach($pages as $page) {
					if(strcasecmp($page->getType(), 'landing')) continue;
					echo sprintf('<option value="%d" %s>%s</option>',
							$page->getId(),
							($config['landing_page_id']==$page->getId())?'selected="selected"':'',
							$page->getName());
				} ?>
			</select>
			<?php if(!empty($errors['landing_page_id'])){ ?>
				<div class="alert-danger"><?php echo $errors['landing_page_id']; ?></div>
			<?php } ?>
		</div>
		<div class="col-sm-6 form-group">
			<label class="required">
				<?php echo __('Offline Page'); ?>:&nbsp;
				<i class="help-tip fa fa-question-circle" href="#offline_page"></i>
			</label>
			<select class="form-control" name="offline_page_id">
				<option value="">&mdash; <?php echo __('Select Offline Page');
					?> &mdash;</option>
				<?php
				foreach($pages as $page) {
					if(strcasecmp($page->getType(), 'offline')) continue;
					echo sprintf('<option value="%d" %s>%s</option>',
							$page->getId(),
							($config['offline_page_id']==$page->getId())?'selected="selected"':'',
							$page->getName());
				} ?>
			</select>
			<?php if(!empty($errors['offline_page_id'])){ ?>
				<div class="alert-danger"><?php echo $errors['offline_page_id']; ?></div>
			<?php } ?>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-6 form-group">
			<label class="required">
				<?php echo __('Default Thank You Page'); ?>:&nbsp;
				<i class="help-tip fa fa-question-circle" href="#default_thank_you_page"></i>
			</label>
			<select class="form-control" name="thank-you_page_id">
				<option value="">&mdash; <?php
					echo __('Select Thank You Page'); ?> &mdash;</option>
				<?php
				foreach($pages as $page) {
					if(strcasecmp($page->getType(), 'thank-you')) continue;
					echo sprintf('<option value="%d" %s>%s</option>',
							$page->getId(),
							($config['thank-you_page_id']==$page->getId())?'selected="selected"':'',
							$page->getName());
				} ?>
			</select>
			<?php if(!empty($errors['thank-you_page_id'])){ ?>
				<div class="alert-danger"><?php echo $errors['thank-you_page_id']; ?></div>
			<?php } ?>
		</div>
	</div>
</div>

<div class="panel-heading">
	<div class="panel-title table-caption">
		<?php echo __('Login Backdrop'); ?>&nbsp;<i class="help-tip fa fa-question-circle" href="#backdrops"></i>
	</div>
</div>
<div class="panel-body">
	<div class="table-light table-responsive">
		<table class="table table-bordered list" border="0" cellspacing="1" cellpadding="0" width="940">
			<thead>
				<th width="10%"><?php echo __('Staff Panel'); ?></th>
				<th><?php echo __('Backdrop'); ?></th>
				<th width="6%"><?php echo __('Delete'); ?></th>
			</thead>
			<tbody>
				<tr>
					<td style="text-align:center;">
						<input type="radio" name="selected-backdrop" value="0"
							<?php if (!$dsk->getConfig()->getStaffLogoId())
							echo 'checked="checked"'; ?>/>
					</td>
					<td>
						<img src="<?php echo DESKUSS_ROOT_PATH; ?>assets/admin/images/login-headquarters.png" alt="Default Backdrop" 
							valign="middle" style="box-shadow: 0 0 0.4em rgba(0,0,0,0.5); margin: 0.5em; height: 6em;
							vertical-align: middle" />
						&nbsp;<span class="label label-info" style="vertical-align:top;margin: 0.5em;"><?php echo __('Trail - Javascript based backdrop'); ?></span><span class="label label-warning" style="vertical-align:top;margin: 0.5em;"><?php echo __('System Default'); ?></span>
					</td>
					<td style="text-align:center;">&nbsp;</td>
					<?php
					$current = $dsk->getConfig()->getStaffLoginBackdropId();
					foreach (AttachmentFile::allBackdrops() as $logo) { ?>
					<tr>
						<td style="text-align:center;">
							<input type="radio" name="selected-backdrop" value="<?php echo $logo->getId(); ?>" <?php
								if ($logo->getId() == $current)
									echo 'checked="checked"'; ?> />
						</td>
						<td>
							<img src="<?php echo $logo->getDownloadUrl(); ?>" alt="Custom Backdrop" valign="middle"
								style="box-shadow: 0 0 0.4em rgba(0,0,0,0.5); margin: 0.5em; 
								height: 6em; vertical-align: middle;" />
						</td>
						<td style="text-align:center;">
							<?php if ($logo->getId() != $current) { ?>
								<input type="checkbox" name="delete-backdrop[]" value="<?php echo $logo->getId(); ?>"/>
							<?php }else{
								echo '&nbsp;';
							}
							?>
						</td>
					</tr>
					<?php } ?>
					<tr>
						<td colspan="3">
							<label><?php echo __('Upload a new backdrop'); ?>:</label>
							<input type="file" name="backdrop[]" size="30" value="" />
							<?php if(!empty($errors['backdrop'])){ ?>
								<div class="alert-danger"><?php echo $errors['backdrop']; ?></div>
							<?php } ?>
						</td>
					</tr>
			</tbody>
		</table>
	</div>
</div>

<div class="panel-heading">
	<div class="panel-title table-caption">
		<?php echo __('Logos'); ?>&nbsp;<i class="help-tip fa fa-question-circle" href="#logos"></i>
	</div>
</div>
<div class="panel-body">
	<div class="table-light table-responsive">
		<table class="table table-bordered list" border="0" cellspacing="1" cellpadding="0" width="940">
			<thead>
				<th width="10%"><?php echo __('Client Panel'); ?></th>
				<th width="10%"><?php echo __('Staff Panel'); ?></th>
				<th><?php echo __('Logo'); ?></th>
				<th width="6%"><?php echo __('Delete'); ?></th>
			</thead>
			<tbody>
				<tr>
					<td style="text-align:center;">
						<input type="radio" name="selected-logo" value="0"
							<?php if (!$dsk->getConfig()->getClientLogoId())
								echo 'checked="checked"'; ?> />
					</td>
					<td style="text-align:center;">
						<input type="radio" name="selected-logo-admin" value="0"
							<?php if (!$dsk->getConfig()->getStaffLogoId())
								echo 'checked="checked"'; ?>/>
					</td>
					<td>
						<img src="<?php echo DESKUSS_ROOT_PATH; ?>assets/images/logo.png" alt="Default Logo" 
							valign="middle" style="box-shadow: 0 0 0.4em rgba(0,0,0,0.5); margin: 0.5em; 
							height: 5em; vertical-align: middle"/>
						<img src="<?php echo DESKUSS_ROOT_PATH; ?>assets/admin/images/deskuss-logo.png" alt="Default Logo" 
							valign="middle" style="box-shadow: 0 0 0.4em rgba(0,0,0,0.5); margin: 0.5em; 
							height: 5em; vertical-align: middle"/>
						&nbsp;<span class="label label-warning" style="vertical-align:top;margin: 0.5em;"><?php echo __('System Default'); ?></span>
					</td>
					<td style="text-align:center;">&nbsp;</td>
					<?php
					$current = $dsk->getConfig()->getClientLogoId();
					$currentadmin = $dsk->getConfig()->getStaffLogoId();
					foreach (AttachmentFile::allLogos() as $logo) { ?>
					<tr>
						<td style="text-align:center;">
							<input type="radio" name="selected-logo" value="<?php echo $logo->getId(); ?>" <?php
								if ($logo->getId() == $current)
									echo 'checked="checked"'; ?> />
						</td>
						<td style="text-align:center;">
							<input type="radio" name="selected-logo-admin" value="<?php echo $logo->getId(); ?>" <?php
								if ($logo->getId() == $currentadmin)
									echo 'checked="checked"'; ?> />
						</td>
						<td>
							<img src="<?php echo $logo->getDownloadUrl(); ?>" alt="Custom Logo" 
								valign="middle" style="box-shadow: 0 0 0.4em rgba(0,0,0,0.5); margin: 0.5em; 
								height: 5em; vertical-align: middle;" />
						</td>
						<td style="text-align:center;">
							<?php if ($logo->getId() != $current && $logo->getId() != $currentadmin) { ?>
								<input type="checkbox" name="delete-logo[]" value="<?php echo $logo->getId(); ?>"/>
							<?php }else{
								echo '&nbsp;';
							}
							?>
						</td>
					</tr>
					<?php } ?>
					<tr>
						<td colspan="3">
							<label><?php echo __('Upload a new logo'); ?>:</label>
							<input type="file" name="logo[]" size="30" value="" />
							<?php if(!empty($errors['logo'])){ ?>
								<div class="alert-danger"><?php echo $errors['logo']; ?></div>
							<?php } ?>
						</td>
					</tr>
			</tbody>
		</table>
	</div>
</div>
    
<br />
<p style="text-align:center;">
    <input class="btn btn-success" type="submit" name="submit-button" value="<?php echo __('Save Changes'); ?>">
    <input class="btn btn-info" type="reset" name="reset" value="<?php echo __('Reset Changes'); ?>">
</p>
</form>
</div>
</div>

<div style="display:none;border:none" class="dialog panel panel-primary panel-dark" id="confirm-action">
<div class="panel-heading">
    <div class="panel-title"><?php echo __('Please Confirm'); ?>
		<a class="close_me pull-right" href=""><i class="fa fa-close"></i></a>
	</div>
</div>
<div class="panel-body">
    <p class="confirm-action" style="display:none;" id="delete-confirm">
        <?php echo sprintf(
			__('Are you sure you want to DELETE %s?'),
			_N('selected image', 'selected images', 2));?>
        <br><br><?php echo __('Deleted data CANNOT be recovered.'); ?>
    </p>
    <div><?php echo __('Please confirm to continue.'); ?></div>
   
    <br/>
	<span class="buttons pull-left">
		<input type="button" value="No, Cancel" class="close_me btn btn-success">
	</span>
	<span class="buttons pull-right">
		<input type="button" value="Yes, Do it!" class="confirm btn btn-info">
	</span>
</div>
</div>

<script type="text/javascript">
$(function() {
    $('#save input:submit.btn').bind('click', function(e) {
        var formObj = $('#save');
        if ($('input:checkbox:checked', formObj).length) {
            e.preventDefault();
            $('.dialog#confirm-action').undelegate('.confirm');
            $('.dialog#confirm-action').delegate('input.confirm', 'click', function(e) {
                e.preventDefault();
                $('.dialog#confirm-action').hide();
                $('#overlay').hide();
                formObj.submit();
                return false;
            });
            $('#overlay').show();
            $('.dialog#confirm-action .confirm-action').hide();
            $('.dialog#confirm-action p#delete-confirm')
            .show()
            .parent('div').parent('div').show().trigger('click');
            return false;
        }
        else return true;
    });
});
</script>