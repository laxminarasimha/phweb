<?php
if(!defined('DSKCLIENTINC') || !$thisclient || !$ticket || !$ticket->checkUserAccess($thisclient)) die('Access Denied!');

$info=($_POST && $errors)?Format::htmlchars($_POST):array();

$dept = $ticket->getDept();

if ($ticket->isClosed() && !$ticket->isReopenable())
    $warn = sprintf(__('%s is marked as closed and cannot be reopened.'), __('This ticket'));

//Making sure we don't leak out internal dept names
if(!$dept || !$dept->isPublic())
    $dept = $cfg->getDefaultDept();

if(!empty($tmsg)){
	?>
	<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert">×</button><i class="fa fa-check"></i>&nbsp;&nbsp;<?php echo esc_html($tmsg); ?></div>
	<?php
}

if(!empty($tfail)){
	?>
	<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">×</button><i class="fa fa-check"></i>&nbsp;&nbsp;<?php echo esc_html($tfail); ?></div>
	<?php
}

?>
<div class="panel">
<?php
if ($thisclient && $thisclient->isGuest()
    && $cfg->isClientRegistrationEnabled()) { ?>

<div id="msg_info" class="panel-body">
    <i class="fa fa-compass fa-2x pull-left text-info"></i>
    <strong><?php echo __('Looking for your other tickets?'); ?></strong><br />
    <a href="<?php echo esc_url(DESKUSS_ROOT_PATH); ?>login.php?e=<?php
        echo urlencode($thisclient->getEmail());
    ?>" style="text-decoration:underline"><?php echo __('Sign In'); ?></a>
    <?php echo sprintf(__('or %s register for an account %s for the best experience on our help desk.'),
        '<a href="account.php?do=create" style="text-decoration:underline">','</a>'); ?>
    </div>

<?php } ?>

<div class="panel-heading">
	<div class="panel-title">
		<a href="tickets.php?id=<?php echo esc_attr($ticket->getId()); ?>" title="<?php echo __('Reload'); ?>"><i class="refresh fa fa-refresh text-primary"></i></a>
			<b>
			<?php $subject_field = TicketForm::getInstance()->getField('subject');
					echo $subject_field->display($ticket->getSubject()); ?>
				</b>
				<small>#<?php echo esc_html($ticket->getNumber()); ?></small>
				
		<div class="pull-right">
		<a class="btn btn-info" href="tickets.php?a=print&id=<?php
			echo esc_attr($ticket->getId()); ?>"><i class="fa fa-print"></i> <?php echo __('Print'); ?></a>
		<?php if ($ticket->hasClientEditableFields()
				// Only ticket owners can edit the ticket details (and other forms)
				&& $thisclient->getId() == $ticket->getUserId()) { ?>
					&nbsp;&nbsp;<a class="btn btn-info" href="tickets.php?a=edit&id=<?php
						 echo esc_attr($ticket->getId()); ?>"><i class="fa fa-edit"></i> <?php echo __('Edit'); ?></a>
		<?php } ?>
		<?php if ($ticket->isOpen()
				// Only ticket owners can edit the ticket details (and other forms)
				&& $thisclient->getId() == $ticket->getUserId()) { ?>
					&nbsp;&nbsp;<a class="btn btn-danger" href="tickets.php?a=close&id=<?php
						 echo esc_attr($ticket->getId()); ?>"><i class="fa fa-ban"></i> <?php echo __('Close'); ?></a>
		<?php } ?>
		</div>
</div>
</div>
<div class="panel-body">
<div class="row">
	  <div class="col-md-6">
		<div class="table-info table-responsive">
            <table class="table" cellspacing="1" cellpadding="3" width="100%" border="0">
                <thead>
                    <tr><th colspan="2">
                        <?php echo __('Basic Ticket Information'); ?>
                    </th></tr>
                </thead>
                <tr>
                    <th width="100"><?php echo __('Ticket Status');?>:</th>
                    <td><?php echo ($S = $ticket->getStatus()) ? esc_html($S->getLocalName()) : ''; ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Department');?>:</th>
                    <td><?php echo Format::htmlchars($dept instanceof Dept ? $dept->getName() : ''); ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Create Date');?>:</th>
                    <td><?php echo Format::datetime($ticket->getCreateDate()); ?></td>
                </tr>
           </table>
		</div>
		</div>
		<div class="col-md-6">
		<div class="table-info table-responsive">
           <table class="table" cellspacing="1" cellpadding="3" width="100%" border="0">
                <thead>
                    <tr><td class="headline" colspan="2">
                        <?php echo __('User Information'); ?>
                    </td></tr>
                </thead>
               <tr>
                   <th width="100"><?php echo __('Name');?>:</th>
                   <td><?php echo mb_convert_case(Format::htmlchars($ticket->getName()), MB_CASE_TITLE); ?></td>
               </tr>
               <tr>
                   <th width="100"><?php echo __('Email');?>:</th>
                   <td><?php echo Format::htmlchars($ticket->getEmail()); ?></td>
               </tr>
               <tr>
                   <th><?php echo __('Phone');?>:</th>
                    <td><?php echo esc_html($ticket->getPhoneNumber()); ?></td>
               </tr>
            </table>
		</div>	
		</div>	
    <div class="col-md-12">
		<div class="table-info table-responsive">
   <table class="table" cellspacing="1" cellpadding="3" width="100%" border="0"> 
    <tr>
        <td colspan="2">
<!-- Custom Data -->
<?php
$sections = array();
foreach (DynamicFormEntry::forTicket($ticket->getId()) as $i=>$form) {
    // Skip core fields shown earlier in the ticket view
    $answers = $form->getAnswers()->exclude(Q::any(array(
        'field__flags__hasbit' => DynamicFormField::FLAG_EXT_STORED,
        'field__name__in' => array('subject', 'priority'),
        Q::not(array('field__flags__hasbit' => DynamicFormField::FLAG_CLIENT_VIEW)),
    )));
    // Skip display of forms without any answers
    foreach ($answers as $j=>$a) {
        if ($v = $a->display())
            $sections[$i][$j] = array($v, $a);
    }
}
foreach ($sections as $i=>$answers) {
    ?>
        <div class="table-light table-responsive">
		<table class="table table-bordered custom-data" cellspacing="0" cellpadding="4" width="100%" border="0">
		<thead>
			<tr><th colspan="2" class="headline flush-left"><?php echo esc_html($form->getTitle()); ?></th></tr>
		</thead>
		<tbody>
<?php foreach ($answers as $A) {
    list($v, $a) = $A; ?>
        <tr>
            <th><?php
echo esc_html($a->getField()->get('label'));
            ?></th>
            <td><?php
echo $v;
            ?></td>
        </tr>
<?php } ?>
		</tbody>
        </table>
        </div>
    <?php
} ?>
    </td>
</tr>
</table>
</div>
</div>
</div>
<br>

<?php
    $ticket->getThread()->render(array('M', 'R'), array(
                'mode' => Thread::MODE_CLIENT,
                'html-id' => 'ticketThread')
            );
?>

<div class="clear" style="padding-bottom:10px;"></div>
<?php if($errors['err']) { ?>
    <div id="msg_error" class="alert alert-danger"><i class="fa fa-exclamation"> </i> <?php echo $errors['err']; ?></div>
<?php }elseif($msg) { ?>
    <div id="msg_notice" class="alert alert-success"><i class="fa fa-check"> </i> <?php echo $msg; ?></div>
<?php }elseif($warn) { ?>
    <div id="msg_warning" class="alert alert-warning"><i class="fa fa-warning"> </i> <?php echo $warn; ?></div>
<?php }

if (!$ticket->isClosed() || $ticket->isReopenable()) { ?>
<form id="reply" action="tickets.php?id=<?php echo esc_attr($ticket->getId());
?>#reply" name="reply" method="post" enctype="multipart/form-data">
    <?php csrf_token(); ?>
	<div class="panel-heading">
    <h3><?php echo __('Post a Reply');?></h3>
	
    <input type="hidden" name="id" value="<?php echo esc_attr($ticket->getId()); ?>">
    <input type="hidden" name="a" value="reply">
    <div>
        <p><em><?php
         echo __('To best assist you, we request that you be specific and detailed'); ?></em>
		<?php
			if(!empty($errors['message'])){
				echo '<div class="alert-danger">&nbsp;'.esc_html($errors['message']).'</div>';
			}
		 ?>
        </p>
        <textarea name="message" id="message" cols="50" rows="9" wrap="soft"
            class="<?php if ($cfg->isRichTextEnabled()) echo 'summernote-base';
                ?> draft" <?php
list($draft, $attrs) = Draft::getDraftAndDataAttrs('ticket.client', $ticket->getId(), $info['message']);
echo $attrs; ?>><?php echo $draft ?: $info['message'];
            ?></textarea>
    <?php
    if ($messageField->isAttachmentsEnabled()) {
        print $attachments->render(array('client'=>true));
    } ?>
    </div>
<?php if ($ticket->isClosed()) { ?>
	<div class="alert alert-warning"><i class="fa fa-exclamation-triangle"></i>&nbsp;&nbsp;<?php echo __('Ticket will be reopened on message post'); ?></div>
<?php } ?>
    <p style="text-align:center">
        <input type="submit" class="btn btn-success" value="<?php echo __('Post Reply');?>">
        <input type="reset" class="btn btn-info" value="<?php echo __('Reset');?>">
        <input type="button" class="btn" value="<?php echo __('Cancel');?>" onClick="history.go(-1)">
    </p>
	</div>
</form>
<?php
} ?>

</div>
</div>
<script type="text/javascript">
<?php
// Hover support for all inline images
$urls = array();
foreach (AttachmentFile::objects()->filter(array(
    'attachments__thread_entry__thread__id' => $ticket->getThreadId(),
    'attachments__inline' => true,
)) as $file) {
    $urls[strtolower($file->getKey())] = array(
        'download_url' => $file->getDownloadUrl(),
        'filename' => $file->name,
    );
} ?>
showImagesInline(<?php echo JsonDataEncoder::encode($urls); ?>);
</script>

