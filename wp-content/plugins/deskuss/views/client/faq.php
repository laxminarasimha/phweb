<?php
if(!defined('DSKCLIENTINC') || !$faq  || !$faq->isPublished()) die('Access Denied');

$category=$faq->getCategory();

?>
<div class="panel" style="display:flow-root;">

<div class="panel-heading">
<div class="panel-title table-caption"><?php echo __('Frequently Asked Questions');?></div>

</div>
<div class="col-sm-12">

</div>
<div class="panel-body">

	<div class="breadcrumb">
			<a class="text-primary" href="index.php"><?php echo __('All Categories');?></a>
			&raquo; <a class="text-primary" href="faq.php?cid=<?php echo esc_attr($category->getId()); ?>"><?php echo esc_html($category->getName()); ?></a>
		</div>

	<div class="col-sm-8">
	
		<div class="panel">
		<div class="panel-heading">
			<div class="panel-title flush-left">
			<strong> <?php echo $faq->getLocalQuestion() ?>  - </strong>
			
		
		<span class="text-mute"><?php echo sprintf(__('Last Updated %s'),
			Format::relativeTime(Misc::db2gmtime($category->getUpdateDate()))); ?></span>
			</div>
		</div>
		<div class="panel-body bleed">
		<?php echo $faq->getLocalAnswerWithImages(); ?>
		</div>
		</div>
	</div>

	<div class="col-sm-4">
		<div class="sidebar">
		<div class="searchbar">
			<form method="get" action="faq.php">
			<input type="hidden" name="a" value="search"/>
			<input type="text" name="q" class="search form-control" placeholder="<?php
				echo __('Search our knowledge base'); ?>"/>
			<input type="submit" style="display:none" value="search"/>
			</form>
		</div>
		<br/>
		<div class="panel"><?php
			if ($attachments = $faq->getLocalAttachments()->all()) { ?>
			<div class="panel-heading">
			<strong><?php echo __('Attachments');?>:</strong>
			</div>
		<section style="padding:10px 20px;">
			
		<?php foreach ($attachments as $att) { ?>
			<div>
			<a href="<?php echo esc_url($att->file->getDownloadUrl()); ?>" class="no-pjax">
				<i class="fa fa-file"></i>
				<?php echo Format::htmlchars($att->getFilename()); ?>
			</a>
			</div>
		<?php } ?>
		</section>
		<?php }
		?></div>
		</div>
	</div>

</div>
</div>
