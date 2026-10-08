<?php
if(!defined('DSKSTAFFINC') || !$faq || !$thisstaff) die('Access Denied');

$category=$faq->getCategory();

?>
<div class="panel">
<div class="panel-heading">
	<div class="pull-left">
		<div class="panel-title table-caption"><?php echo __('Frequently Asked Questions');?></div>
	</div>
	<div class="pull-right flush-right">
	<?php
	$query = array();
	parse_str($_SERVER['QUERY_STRING'], $query);
	$query['a'] = 'print';
	$query['id'] = $faq->getId();
	$query = http_build_query($query); ?>
		<a class="btn" href="faq.php?<?php echo $query; ?>" class="no-pjax action-button">
		<i class="fa fa-print"></i>
			<?php echo __('Print'); ?>
		</a>&nbsp;
	<?php
	if ($thisstaff->hasPerm(FAQ::PERM_MANAGE)) { ?>
		<a class="btn" href="faq.php?id=<?php echo esc_attr($faq->getId()); ?>&a=edit" class="action-button">
		<i class="fa fa-edit"></i>
			<?php echo __('Edit FAQ'); ?>
		</a>
	<?php } ?>
	</div>

</div>
<div class="panel-body">
<div id="breadcrumbs">
    <a href="kb.php"><?php echo __('All Categories');?></a>
    &raquo; <a href="kb.php?cid=<?php echo esc_attr($category->getId()); ?>"><?php echo esc_html($category->getName()); ?></a>
    &nbsp;&mdash;&nbsp;<span class="faded">(<?php echo $category->isPublic()?__('Public'):__('Internal'); ?>)</span>
</div>
<br/>
<div class="row">
<div class="pull-right sidebar faq-meta col-sm-3">
<div class="panel">
	<?php if ($attachments = $faq->getLocalAttachments()->all()) { ?>
	<section>
		<div class="panel-heading"><?php echo __('Attachments');?>:</div>
			<div style="padding:10px;">
		<?php foreach ($attachments as $att) { ?>
		<div>
			<i class="fa af-paperclip pull-left"></i>
		<a target="_blank" href="<?php echo esc_url($att->file->getDownloadUrl()); ?>"
			class="attachment no-pjax">
				<?php echo Format::htmlchars($att->getFilename()); ?>
			</a>
		</div>
		<?php } ?>
		</div>
	</section>
	<?php } ?>

	<?php
	$displayLang = $faq->getDisplayLang();
	$otherLangs = array();
	if ($cfg->getPrimaryLanguage() != $displayLang)
		$otherLangs[] = $cfg->getPrimaryLanguage();
	foreach ($faq->getAllTranslations() as $T) {
		if ($T->lang != $displayLang)
			$otherLangs[] = $T->lang;
	}
	if ($otherLangs) { ?>
	<section>
		<div class="panel-heading"><?php echo __('Other Languages'); ?></div>
		<div style="padding:10px;">
	<?php
		foreach ($otherLangs as $lang) { ?>
		<div><a href="faq.php?kblang=<?php echo esc_attr($lang); ?>&id=<?php echo esc_attr($faq->getId()); ?>">
			<?php echo Internationalization::getLanguageDescription($lang); ?>
		</a></div>
		<?php } ?>
		</div>
	</section>
	<?php } ?>

	<section>
	<div class="panel-heading">
		<?php echo $faq->isPublished()?__('Published'):__('Internal'); ?>
	</div>
	<div style="padding:10px;">
	<a data-dialog="ajax.php/kb/faq/<?php echo esc_attr($faq->getId()); ?>/access" href="#"><?php echo __('Manage Access'); ?></a>
	</div>
	</section>

</div>
</div>

<div class="faq-content col-sm-9">
	<div class="panel">
		<div class="panel-heading flush-left">
			<div class="panel-title table-caption"><?php echo $faq->getLocalQuestion() ?> &nbsp;
				<em><small class="text-muted"><?php echo __('Last Updated');?>
					<?php echo Format::relativeTime(Misc::db2gmtime($faq->getUpdateDate())); ?>
				</small></em>
			</div>
		</div>
		<br/>
		<div class="panel-body bleed">
		<?php echo $faq->getLocalAnswerWithImages(); ?>
		</div>
	</div>
</div>
</div>

<?php
if ($thisstaff->hasPerm(FAQ::PERM_MANAGE)) { ?>
<form action="faq.php?id=<?php echo esc_attr($faq->getId()); ?>" method="post">
    <?php csrf_token(); ?>
    <input type="hidden" name="do" value="manage-faq">
    <input type="hidden" name="id" value="<?php echo esc_attr($faq->getId()); ?>">
    <button name="a" class="btn btn-danger" value="delete"><?php echo __('Delete FAQ'); ?></button>
</form>
<?php }
?>	
</div>
</div>
