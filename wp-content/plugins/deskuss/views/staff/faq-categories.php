<?php
if(!defined('DSKSTAFFINC') || !$thisstaff) die('Access Denied');

?>


<form id="kbSearch" action="kb.php" method="get">
<input type="hidden" name="a" value="search">
<input type="hidden" name="cid" value="<?php echo Format::htmlchars($_REQUEST['cid'] ?? ''); ?>"/>

<div class="panel">
<div class="panel-heading">
	<div class="pull-left panel-title table-caption">
	<?php echo __('Frequently Asked Questions');?>
	</div>
	<div class="pull-right">
	<div id="category-dropdown" class="action-dropdown pull-right">
		<button type="button" class="action-button muted btn dropdown-toggle" data-toggle="dropdown" 
			data-dropdown="#category-dropdown" style="margin-left:5%">
			<i class="fa fa-filter"></i>
			<?php echo __('Category'); ?>
		</button>
		<ul class="dropdown-menu">
		<?php
		$total = FAQ::objects()->count();

		$categories = Category::objects()
			->annotate(array('faq_count' => SqlAggregate::COUNT('faqs')))
			->filter(array('faq_count__gt' => 0))
			->order_by('name')
			->all();
		array_unshift($categories, new Category(array('id' => 0, 'name' => __('All Categories'), 'faq_count' => $total)));
		foreach ($categories as $C) {
				$active = ($_REQUEST['cid'] ?? null) == $C->getId(); ?>
				<li <?php if ($active) echo 'class="active"'; ?>>
					<a href="#" data-cid="<?php echo $C->getId(); ?>" onclick="javascript:var form = $(this).closest('form');
						form.find('[name=cid]').val($(event.target).data('cid'));
						form.submit();">
						<i class="fa fa-hand-o-right <?php
						if ($active) echo 'fa-hand-o-right'; ?>"></i>&nbsp;
						<?php echo sprintf('%s (%d)',
							Format::htmlchars($C->getLocalName()),
							$C->faq_count); ?></a>
				</li> <?php
		} ?>
		</ul>
	</div>
	</div>
</div>
</form>
   
<br />
<div class="panel-body">
	<div class="input-group">
		<input class="form-control" id="query" type="text" placeholder="Search FAQs" style="width:80%" name="q" autofocus
		value="<?php echo Format::htmlchars($_REQUEST['q'] ?? ''); ?>">
		<button class="attached button btn" id="searchSubmit" type="submit">
			<i class="fa fa-search"></i>
		</button>
	</div>
<br />
<div class="col-sm-12">
<?php
if(!empty($_REQUEST['q']) || !empty($_REQUEST['cid'])) { //Search.
    $faqs = FAQ::objects()
        ->annotate(array(
            'attachment_count'=>SqlAggregate::COUNT('attachments'),
        ))
        ->order_by('question');

    if ($_REQUEST['cid'])
        $faqs->filter(array('category_id'=>$_REQUEST['cid']));

    if ($_REQUEST['q'])
        $faqs->filter(Q::ANY(array(
            'question__contains'=>$_REQUEST['q'],
            'answer__contains'=>$_REQUEST['q'],
            'keywords__contains'=>$_REQUEST['q'],
            'category__name__contains'=>$_REQUEST['q'],
            'category__description__contains'=>$_REQUEST['q'],
        )));

    echo '<div class="panel-heading"><div class="panel-title table-caption">'.__('Search Results').'</div></div>';
    if ($faqs->exists(true)) {
        echo '<div id="faq">
                <ol>';
        foreach ($faqs as $F) {
            echo sprintf(
                '<li><a href="faq.php?id=%d" class="previewfaq">%s</a> - <span>%s</span></li>',
                $F->getId(), $F->getLocalQuestion(), $F->getVisibilityDescription());
        }
        echo '  </ol>
             </div>';
    } else {
        echo '<div class="faded">'.__('The search did not match any FAQs.').'</div><br>';
    }
} else { //Category Listing.
    $categories = Category::objects()
        ->annotate(array('faq_count'=>SqlAggregate::COUNT('faqs')));

    if (count($categories)) {
        $categories->sort(function($a) { return $a->getLocalName(); });
        echo '<div class="row">'.__('Click on the category to browse FAQs or manage its existing FAQs.').'<br/><br/>
                <div id="kb">';
        foreach ($categories as $C) {
			echo sprintf('<div class="row">
				<div class="col-sm-12">
					<i class="fa fa-folder-open"></i>
					<a class="truncate font-size-14" href="kb.php?cid=%d">%s (%d)</a> - %s
					<span>%s</span>
				</div>
			</div>',
			$C->getId(),$C->getLocalName(),$C->faq_count,
			$C->getVisibilityDescription(),
			Format::safe_html($C->getLocalDescriptionWithImages()));
			
        } 
        echo '</div></div>';
    } else {
        echo __('NO FAQs found');
    }
}
?>
</div>
</div>
</div>