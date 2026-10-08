<div class="row">
<div class="col-sm-12">
<?php
    $categories = Category::objects()
        ->exclude(Q::any(array(
            'ispublic'=>Category::VISIBILITY_PRIVATE,
            'faqs__ispublished'=>FAQ::VISIBILITY_PRIVATE,
        )))
        ->annotate(array('faq_count'=>SqlAggregate::COUNT('faqs')))
        ->filter(array('faq_count__gt'=>0));
    if ($categories->exists(true)) { ?>
        <div class="panel-heading"><?php echo __('Click on the category to browse FAQs.'); ?></div>
		<div class="panel-body">
		<div class="col-sm-8">
        <ul id="kb">
<?php
        foreach ($categories as $C) { ?>
            <li style="display: -webkit-box;"><i class="fa fa-folder-open fa-2x text-info"></i>
            <div style="margin-left:10px">
            <h4><?php echo sprintf('<a href="faq.php?cid=%d">%s (%d)</a>',
                $C->getId(), Format::htmlchars($C->getLocalName()), $C->faq_count); ?></h4>
            <div class="text-muted" style="margin:10px 0;font-size:14px">
                <?php echo Format::safe_html($C->getLocalDescriptionWithImages()); ?>
            </div>
<?php       foreach ($C->faqs
                    ->exclude(array('ispublished'=>FAQ::VISIBILITY_PRIVATE))
                    ->limit(5) as $F) { ?>
                <div style="font-weight:500;font-size:14px"><i class="fa fa-file-text-o"></i>
                <a href="faq.php?id=<?php echo esc_attr($F->getId()); ?>">
                <?php echo $F->getLocalQuestion() ?: esc_html($F->getQuestion()); ?>
                </a></div>
<?php       } ?>
            </div>
            </li>
			<hr/>
<?php   } ?>
       </ul>
	   </div>
	   
	   <div class="col-sm-4">
			<div class="sidebar">
			<div class="searchbar">
				<form method="get" action="faq.php">
				<input type="hidden" name="a" value="search"/>
				<select name="cid" class="form-control" 
					onchange="javascript:this.form.submit();">
					<option value="">—<?php echo __("Browse by Category"); ?>—</option>
		<?php
		$categories = Category::objects()
			->annotate(array('has_faqs'=>SqlAggregate::COUNT('faqs')))
			->filter(array('has_faqs__gt'=>0));
		foreach ($categories as $C) { ?>
			<option value="<?php echo esc_attr($C->getId()); ?>"><?php echo esc_html($C->getName());
				?></option>
		<?php } ?>
				</select>
				</form>
			</div>
			<br/>
			<div class="panel">
			<div class="panel-heading"><?php echo __('Other Resources'); ?></div>
				<section style="padding:10px 20px;">
					
				</section>
			</div>
			</div>
		</div>
	   	</div>

<?php
    } else {
        echo __('NO FAQs found');
    }
?>
</div>
</div>
