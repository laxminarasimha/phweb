<?php


// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
//////////////////////////////////////////////////////////////
//===========================================================
// SOFTACULOUS PROJECT
//===========================================================
// Inspired by the DESIRE to be the BEST OF ALL
// ----------------------------------------------------------
// Started by: Pulkit and Brijesh
// ----------------------------------------------------------
// Please Read the Terms of use at http://deskuss.com
// ----------------------------------------------------------
//===========================================================
// (c)Softaculous Ltd.
//===========================================================
//////////////////////////////////////////////////////////////

require('admin.inc.php');
include_once(INCLUDE_DIR.'class.email.php');
include_once(INCLUDE_DIR.'class.csrf.php');
$info=array();
$info['subj']='Deskuss test email';

if($_POST){
    $errors=array();
    $email=null;
    if(!$_POST['email_id'] || !($email=Email::lookup($_POST['email_id'])))
        $errors['email_id']=__('Select from email address');

    if(!$_POST['email'] || !Validator::is_valid_email($_POST['email']))
        $errors['email']=__('Valid recipient email address required');

    if(!$_POST['subj'])
        $errors['subj']=__('Subject required');

    if(!$_POST['message'])
        $errors['message']=__('Message required');

    if(!$errors && $email){
        if($email->send($_POST['email'],$_POST['subj'],
                Format::sanitize($_POST['message']),
                null, array('reply-tag'=>false))) {
            $msg=Format::htmlchars(sprintf(__('Test email sent successfully to <%s>'),
                $_POST['email']));
            Draft::deleteForNamespace('email.diag');
        }
        else
            $errors['err']=sprintf('%s - %s', __('Error sending email'), __('Please try again!'));
    }elseif($errors['err']){
        $errors['err']=sprintf('%s - %s', __('Error sending email'), __('Please try again!'));
    }
}
$action = $_REQUEST['do'] ?? '';
$tip_namespace = $tip_namespace ?? 'emails.diagnostic';
$nav->setTabActive('emails','emailtest.php');
$dsk->addExtraHeader('<meta name="tip-namespace" content="emails.diagnostic" />',
    "$('#content').data('tipNamespace', '".$tip_namespace."');");
require deskuss_get_header();

$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);
?>
<div class="panel">
<form action="emailtest.php" method="post" class="save">
 <?php csrf_token(); ?>
 <input type="hidden" name="do" value="<?php echo $action; ?>">
<div class="panel-heading">
 <div class="panel-title table-caption"><?php echo __('Test Outgoing Email');?></div>
</div>
<div class="panel-body">
	<div class="row">
		<div class="col-sm-12" style="padding-bottom:10px;">
			<em><?php echo __('Use the following form to test whether your <strong>Outgoing Email</strong> settings are properly established.');
				?>&nbsp;<i class="help-tip fa fa-question-circle" href="#test_outgoing_email"></i></em>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-6 form-group">
			<label class="required" for="email_id"><?php echo __('From'); ?>: </label>
			<select class="form-control" name="email_id" id="email_id">
				<option value="0">&mdash; <?php echo __('Select FROM Email');?> &mdash;</option>
				<?php
				$sql='SELECT email_id,email,name,smtp_active FROM '.EMAIL_TABLE.' email ORDER by name';
				if(($res=db_query($sql)) && db_num_rows($res)){
					while(list($id,$email,$name,$smtp)=db_fetch_row($res)){
						$selected=((isset($info['email_id']) && $info['email_id']) && $id==$info['email_id'])?'selected="selected"':'';
						if($name)
							$email=Format::htmlchars("$name <$email>");
						if($smtp)
							$email.=' ('.__('SMTP').')';

						echo sprintf('<option value="%d" %s>%s</option>',$id,$selected,$email);
					}
				}
				?>
			</select>
			<?php
			if(!empty($errors['email_id'])){
				echo '<div class="alert-danger">&nbsp;'.$errors['email_id'].'</div>';
			}
			?>
		</div>
		<div class="col-sm-6 form-group">
			<label class="required" for="email"><?php echo __('To'); ?>: </label>
			<input type="text" class="form-control" size="60" name="email" id="email" value="<?php echo $info['email'] ?? ''; ?>" autofocus>
			<?php
			if(!empty($errors['email'])){
				echo '<div class="alert-danger">&nbsp;'.$errors['email'].'</div>';
			}
			?>
		</div>
	</div>
	
	<div class="row">
		<div class="col-sm-12 form-group">
			<label class="required" for="subj"><?php echo __('Subject'); ?>: </label>
			<input type="text" class="form-control" size="60" name="subj" id="subj" value="<?php echo $info['subj'] ?? ''; ?>">
			<?php
			if(!empty($errors['subj'])){
				echo '<div class="alert-danger">&nbsp;'.$errors['subj'].'</div>';
			}
			?>
		</div>
	</div>
	
	<div class="row">
		<div class="col-sm-12 form-group">
			<label class="required" for="message"><?php echo __('Message');?>: <em><?php echo __('email message to send.');?></em></label>
			<?php
			if(!empty($errors['message'])){
				echo '<div class="alert-danger">&nbsp;'.$errors['message'].'</div>';
			}
			?>
		    <textarea class="summernote-base draft draft-delete" name="message" id="message" cols="21"
                    rows="10" style="width: 90%;" <?php
    list($draft, $attrs) = Draft::getDraftAndDataAttrs('email.diag', false, $info['message'] ?? '');
    echo $attrs; ?>><?php echo $draft ?: ($info['message'] ?? '');
                 ?></textarea>
		</div>
	</div>

<p style="text-align:center;">
    <input type="submit" class="btn btn-success" name="submit" value="<?php echo __('Send Message');?>">
    <input type="reset" class="btn btn-info" name="reset"  value="<?php echo __('Reset');?>">
    <input type="button" class="btn btn-default" name="cancel" value="<?php echo __('Cancel');?>" onclick='window.location.href="emails.php"'>
</p>

</div>
</form>
</div>
<?php
require deskuss_get_footer();
?>
