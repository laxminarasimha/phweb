<?php
if(!defined('DSKCLIENTINC') || !$category || !$category->isPublic()) die('Access Denied');
?>

<div class="panel" style="display:flow-root	">
	<div class="panel-heading">
    <div class="panel-title table-caption"><?php echo __('Frequently Asked Questions');?>
	</div>
	</div>
	
<div class="panel-body">
<div class="col-sm-8">
<div class="panel">
	<div class="panel-heading">
    <div class="panel-title table-caption"> <?php echo $category->getLocalName() ?></div>
	</div>
	<div class="panel-body">
	<p>
	<?php echo Format::safe_html($category->getLocalDescriptionWithImages()); ?>
	</p>
	<br/>
	<?php
	$faqs = FAQ::objects()
		->filter(array('category'=>$category))
		->exclude(array('ispublished'=>FAQ::VISIBILITY_PRIVATE))
		->annotate(array('has_attachments' => SqlAggregate::COUNT(SqlCase::N()
			->when(array('attachments__inline'=>0), 1)
			->otherwise(null)
		)))
		->order_by('-ispublished', 'question');

	if ($faqs->exists(true)) {
		echo '
			 <h3>'.__('Further Articles').'</h3>
			 
			 <div id="faq">
				<ol>';
	foreach ($faqs as $F) {
			$attachments=$F->has_attachments?'<span class="fa fa-file-o"></span>':'';
			echo sprintf('
				<li><a href="faq.php?id=%d" ><i class="fa fa-file-text-o" style="font-size:13px;color:#575757"></i> %s &nbsp;%s</a></li>',
				$F->getId(),Format::htmlchars($F->question), $attachments);
		}
		echo '  </ol>
			 </div>';
	}else {
		echo '<strong>'.__('This category does not have any FAQs.').' <a href="index.php">'.__('Back To Index').'</a></strong>';
	}
	?>
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
		</div>
	</div>
</div>
</div>
