<?php
if(!defined('DSKSTAFFINC') || !$category || !$thisstaff) die('Access Denied');

?>

<div class="panel">
<div class="panel-heading">
	<div class="panel-title table caption">
	<?php echo __('Frequently Asked Questions');?>

	<?php if ($thisstaff->hasPerm(FAQ::PERM_MANAGE)) {
	echo sprintf('<div class="pull-right flush-right">
		<a class="btn btn-success btn-outline" href="faq.php?cid=%d&a=add">'.__('Add New FAQ').'</a>
		<div class="btn-group">
			<button type="button" class="btn btn-outline dropdown-toggle" data-toggle="dropdown" style="vertical-align:top; margin-bottom:0">
			<i class="fa fa-cog"></i>&nbsp;'. __('More').'</button>

			<ul class="dropdown-menu dropdown-menu-right">
				<li><a class="user-action" href="categories.php?id=%d">
					<i class="fa fa-pencil fa-fw"></i>&nbsp;'
					.__('Edit Category').'</a>
				</li>
				<li>
					<a class="user-action" href="categories.php">
					<i class="fa fa-trash-o fa-fw"></i>&nbsp;'
					.__('Delete Category').'</a>
				</li>
			</ul>
		</div>
	</div>', $category->getId(), $category->getId());
	} ?>
	</div>
</div>

<div class="panel-body">
<div class="faq-category">
	<div style="margin-bottom:10px;">
	<div class="faq-title font-size-14">
		<b><?php echo $category->getName() ?></b>&nbsp;&mdash;&nbsp;
		(<?php echo $category->isPublic()?__('Public'):__('Internal'); ?>)
	</div>
	<em class="text-muted"> <?php echo __('Last Updated').' '. Format::daydatetime($category->getUpdateDate()); ?></em>
	</div>
	<div class="cat-desc has_bottom_border">
		<?php echo Format::display($category->getDescription()); ?>
	</div>
	<hr/>
	<?php

	$faqs = $category->faqs
		->constrain(array('attachments__inline' => 0))
		->annotate(array('attachments' => SqlAggregate::COUNT('attachments')));
	if ($faqs->exists(true)) {
		echo '<div class="row" id="faq">
				<ol>';
		foreach ($faqs as $faq) {
			echo sprintf('
				<li><strong><a href="faq.php?id=%d" class="previewfaq">%s <span>- %s</span></a> %s</strong></li>',
				$faq->getId(),$faq->getQuestion(),$faq->isPublished() ? __('Published'):__('Internal'),
				$faq->attachments ? '<i class="fa fa-paperclip"></i>' : ''
			);
		}
		echo '</ol>
		</div>';
	}else {
		echo '<em>'.__('Category does not have FAQs').'</em>';
	}
	?>
</div>
</div>
</div>