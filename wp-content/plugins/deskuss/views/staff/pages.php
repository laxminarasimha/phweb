<?php
if(!defined('DSKADMININC') || !$thisstaff->isAdmin()) die('Access Denied');

$pages = Page::objects()
    ->filter(array('type__in'=>array('other','landing','thank-you','offline')))
    ->annotate(array('depts'=>SqlAggregate::COUNT('depts')));
$qs = array();
$sortOptions=array(
        'name'=>'name', 'status'=>'isactive',
        'created'=>'created', 'updated'=>'updated',
        'type'=>'type');

$orderWays=array('DESC'=>'-','ASC'=>'');
$sort=($_REQUEST['sort'] ?? '' && $sortOptions[strtolower($_REQUEST['sort'] ?? '')])?strtolower($_REQUEST['sort'] ?? ''):'name';
$order = $_REQUEST['order'] ?? 'ASC';
//Sorting options...
if($sort && $sortOptions[$sort]) {
    $pages = $pages->order_by(
        ($orderWays[strtoupper($order)] ?? '') . $sortOptions[$sort]);
}

$x=$sort.'_sort';
$$x=' class="'.strtolower($order ?? 'ASC').'" ';

$total = $pages->count();
$page=(!empty($_GET['p']) && is_numeric($_GET['p']))?$_GET['p']:1;
$pageNav=new Pagenate($total, $page, PAGE_LIMIT);
$qstr = '&amp;'. Http::build_query($qs);
$qstr .= 'order='.($_REQUEST['order'] ?? ''=='DESC' ? 'ASC' : 'DESC');
$qs += array('sort' => $_REQUEST['sort'] ?? '', 'order' => $_REQUEST['order'] ?? '');
$pageNav->setURL('pages.php', $qs);
//Ok..lets roll...create the actual query
if ($total)
    $showing=$pageNav->showing().' '._N('site page','site pages', ($num ?? $total));
else
    $showing=__('No pages found!');

?>
<div class="panel">
<form action="pages.php" method="POST" name="tpls">
<div class="panel-heading">
	<div class="panel-title"><?php echo __('Site Pages'); ?>
		<i class="help-tip fa fa-question-circle notsticky" href="#site_pages"></i>
	</div>
</div>
<div class="panel-body">	
<div class="pull-right form-group">
	<a href="pages.php?a=add" class="btn btn-success btn-outline"><i class="fa fa-plus-circle"></i> <?php echo __('Add New Page'); ?></a>
	<div class="btn-group">
	<button type="button" class="btn btn-outline dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i>&nbsp;<?php echo __('More');?></button>
	<ul class="dropdown-menu dropdown-menu-right" id="actions">
		<li>
			<a class="confirm" data-name="enable" href="pages.php?a=enable">
				<i class="fa fa-check"></i>
				<?php echo __( 'Enable'); ?>
			</a>
		</li>
		<li>
			<a class="confirm" data-name="disable" href="pages.php?a=disable">
				<i class="fa fa-ban"></i>
				<?php echo __( 'Disable'); ?>
			</a>
		</li>
		<li class="danger">
			<a class="confirm" data-name="delete" href="pages.php?a=delete">
				<i class="fa fa-trash"></i>
				<?php echo __( 'Delete'); ?>
			</a>
		</li>
	</ul>
	</div>
	<div class="form-group pd-tp-10">
		<?php
			if($total){ //Show options..
		?>
		<div class="pull-right">
			<?php
				echo __('Page').':'.$pageNav->getPageLinks();
				}
			?>
		</div>
	</div>
</div>
<br/>
<br/>
<form action="pages.php" method="POST" name="tpls">
<?php csrf_token(); ?>
<input type="hidden" name="do" value="mass_process" >
<input type="hidden" id="action" name="a" value="" >
<div class="row">
<div class="col-md-12">
<div class="table-light table-responsive">
<table class="table table-bordered list" border="0" cellspacing="1" cellpadding="0" width="940">
    <thead>
	<tr>
		<th width="4%" class="text-center" ><?php if($total){ ?><input type="checkbox" id="selectToggle" class="ckb"><?php }else{ echo __(''); }?></th>
		<th width="30%"><a <?php echo $name_sort ?? ""; ?> href="pages.php?<?php echo $qstr; ?>&sort=name"><?php echo __('Name'); ?></a></th>
		<th width="5%"><?php echo __('View'); ?></th>
		<th width="10%"><a  <?php echo $type_sort ?? ""; ?> href="pages.php?<?php echo $qstr; ?>&sort=type"><?php echo __('Type'); ?></a></th>
		<th width="16%"><a  <?php echo $status_sort ?? ""; ?> href="pages.php?<?php echo $qstr; ?>&sort=status"><?php echo __('Status'); ?></a></th>
		<th width="15%" nowrap><a  <?php echo $created_sort ?? ""; ?>href="pages.php?<?php echo $qstr; ?>&sort=created"><?php echo __('Date Added'); ?></a></th>
		<th width="20%" nowrap><a  <?php echo $updated_sort ?? ""; ?>href="pages.php?<?php echo $qstr; ?>&sort=updated"><?php echo __('Last Updated'); ?></a></th>
	</tr>
    </thead>
    <tbody>
    <?php
	$ids=($errors && is_array($_POST['ids']))?$_POST['ids']:null;
	$defaultPages=$cfg->getDefaultPages();
	foreach($pages as $page){
		$sel=false;
		if($ids && in_array($row['id'], $ids))
			$sel=true;
		$inuse = ($page->depts || in_array($page->id, $defaultPages));
		?>
		<tr id="<?php echo $page->id; ?>">
			<td align="center">
			  <input type="checkbox" class="ckb" name="ids[]" value="<?php echo $page->id; ?>"
						<?php echo $sel?'checked="checked"':''; ?>>
			</td>
			<td>&nbsp;<a href="pages.php?id=<?php echo $page->id; ?>"><?php echo Format::htmlchars($page->getLocalName() ?: $page->getName()); ?></a></td>
			<td style="text-align: center;"><?php if($page->type == 'other' && $page->isActive()){ ?>
				<a href="<?php echo sprintf("%s/pages/%s",
					$dsk->getConfig()->getBaseUrl(), urlencode(Format::slugify($page->name)));
					?>">
					<i class="fa fa-eye"></i>
				</a>
			<?php } ?>
			</td>
			<td class="faded"><?php echo $page->type; ?></td>
			<td>
				&nbsp;<?php echo $page->isActive()?__('Active'):'<b>'.__('Disabled').'</b>'; ?>
				&nbsp;&nbsp;<?php echo $inuse?'<em>'.__('(in-use)').'</em>':''; ?>
			</td>
			<td>&nbsp;<?php echo Format::date($page->created); ?></td>
			<td>&nbsp;<?php echo Format::datetime($page->updated); ?></td>
		</tr>
		<?php
	} //end of foreach. ?>
    </tbody>
	<?php 
	if(!$total){
		echo '<tfoot><tr><td colspan="6">'.__('No pages found!').'</td></tr></tfoot>';
	}
	?>
</table>
</div>
</div>
</div>
	<?php
		if($total){ //Show options..
	?>
	<div class="pull-right">
		<span class="faded"><?php echo $pageNav->showing(); ?>&nbsp;&mdash;&nbsp;</span>
		<?php
			echo __('Page').':'.$pageNav->getPageLinks();
			}
		?>
	</div>
</div>
</form>
</div>

<div style="display:none;border:none" class="dialog panel panel-primary panel-dark" id="confirm-action">
<div class="panel-heading">
    <div class="panel-title"><?php echo __('Please Confirm'); ?>
    <a class="close_me pull-right" href=""><i class="fa fa-close"></i></a>
	</div>
</div>
<div class="panel-body">
    <p class="confirm-action" style="display:none;" id="enable-confirm">
        <?php echo sprintf(__('Are you sure you want to <b>enable</b> %s?'),
            _N('selected site page', 'selected site pages', 2));?>
    </p>
    <p class="confirm-action" style="display:none;" id="disable-confirm">
        <?php echo sprintf(__('Are you sure you want to <b>disable</b> %s?'),
            _N('selected site page', 'selected site pages', 2));?>
    </p>
    <p class="confirm-action" style="display:none;" id="delete-confirm">
        <font class="text-danger"><?php echo sprintf(
        __('Are you sure you want to DELETE %s?'),
        _N('selected site page', 'selected site pages', 2));?></font>
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
