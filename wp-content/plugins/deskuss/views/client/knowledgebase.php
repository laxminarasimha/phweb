<?php
if(!defined('DSKCLIENTINC')) die('Access Denied');

?>
<div class="panel" style="display:flow-root;">
<?php
if($_REQUEST['q'] || $_REQUEST['cid']) { //Search
    $faqs = FAQ::allPublic()
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

    include CLIENTINC_DIR . 'kb-search.php';

} else { //Category Listing.
    include CLIENTINC_DIR . 'kb-categories.php';
}
?>
</div>
