<?php
if(!defined('DSKADMININC') || !$thisstaff || !$thisstaff->isAdmin() || !$filter) die('Access Denied');

$order = $_REQUEST['order'] ?? 'ASC';
$qs = array();
$select='SELECT rule.* ';
$from='FROM '.FILTER_RULE_TABLE.' rule ';
$where='WHERE rule.filter_id='.db_input($filter->getId());
$search=false;
if(!empty($_REQUEST['q']) && strlen($_REQUEST['q'])>3) {
    $search=true;
    if(strpos($_REQUEST['q'],'@') && Validator::is_email($_REQUEST['q']))
        $where.=' AND rule.val='.db_input($_REQUEST['q']);
    else
        $where.=' AND rule.val LIKE "%'.db_input($_REQUEST['q'],false).'%"';

}elseif(!empty($_REQUEST['q'])) {
    $errors['q']=__('Term too short!');
}

$sortOptions=array('email'=>'rule.val','status'=>'isactive','created'=>'rule.created','created'=>'rule.updated');
$orderWays=array('DESC'=>'DESC','ASC'=>'ASC');
$sort=($_REQUEST['sort'] ?? '' && $sortOptions[strtolower($_REQUEST['sort'] ?? '')])?strtolower($_REQUEST['sort'] ?? ''):'email';
//Sorting options...
if($sort && $sortOptions[$sort]) {
    $order_column =$sortOptions[$sort];
}
$order_column=$order_column?$order_column:'rule.val';

if($_REQUEST['order'] ?? '' && $orderWays[strtoupper($_REQUEST['order'] ?? '')]) {
    $order=$orderWays[strtoupper($_REQUEST['order'] ?? '')];
}

$order=$order?$order:'ASC';
if($order_column && strpos($order_column,',')){
    $order_column=str_replace(','," $order,",$order_column);
}
$x=$sort.'_sort';
$$x=' class="'.strtolower($order).'" ';
$order_by="$order_column $order ";

$total=db_count('SELECT count(DISTINCT rule.id) '.$from.' '.$where);
$page=(!empty($_GET['p']) && is_numeric($_GET['p']))?$_GET['p']:1;
$pageNav=new Pagenate($total, $page, PAGE_LIMIT);
$qstr = '&amp;'. Http::build_query($qs);
$qs += array('sort' => $_REQUEST['sort'] ?? '', 'order' => $_REQUEST['order'] ?? '');
$pageNav->setURL('banlist.php', $qs);
$qstr.='&amp;order='.($order=='DESC'?'ASC':'DESC');
$query="$select $from $where ORDER BY $order_by LIMIT ".$pageNav->getStart().",".$pageNav->getLimit();
//echo $query;
?>
<div class="panel">
   
	<div class="panel-heading">
		<div class="panel-title table-caption">
			<?php echo __('Banned Email Addresses');?>
				<i class="help-tip fa fa-question-circle" href="#ban_list"></i>
		</div>
	</div>
	
	<div class="panel-body">
	<div id="basic_search" class="pull-left">
		<div style="height:25px;max-width:250px;">
			<form action="banlist.php" method="GET" name="filter">
				<input type="hidden" name="a" value="filter" >
				<div class="input-group">
					<input name="q" class="form-control" type="text" class="basic-search" size="30" autofocus
						   value="<?php echo Format::htmlchars($_REQUEST['q'] ?? ''); ?>">
					<span class="input-group-btn">
					<button type="submit" class="btn"><i class="fa fa-search"></i></button>
					</span>
				</div>
			</form>
		</div>
	</div>
		
	<form action="banlist.php" method="POST" name="banlist">
	<div class="pull-right">
	<a href="banlist.php?a=add" class="btn btn-danger btn-outline">
		<i class="fa fa-ban"></i> <?php echo __('Ban New Email');?>
	</a>
	
	<div class="btn-group">
		<button type="button" class="btn btn-outline dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i>&nbsp;<?php echo __('More');?></button>
		<ul class="dropdown-menu dropdown-menu-right" id="actions">
			<li><a class="confirm" data-name="enable" href="banlist.php?a=enable">
				<i class="fa fa-check"></i>
				<?php echo __('Enable'); ?></a>
			</li>
			<li><a class="confirm" data-name="disable" href="banlist.php?a=disable">
				<i class="fa fa-ban"></i>
				<?php echo __('Disable'); ?></a>
			</li>
			<li><a class="confirm" data-name="delete" href="banlist.php?a=delete"> <i class="fa fa-trash"></i>
				<?php echo __('Remove'); ?></a>
			</li>
		</ul>
	</div>
	</div>
	
	<br/><br/>

    <?php
    if(($res=db_query($query)) && ($num=db_num_rows($res)))
        $showing=$pageNav->showing();
    else
        $showing=__('No banned emails matching the query found!');

    if($search)
        $showing=__('Search Results').': '.$showing;

    ?>
    <?php csrf_token(); ?>
    <input type="hidden" name="do" value="mass_process" >
    <input type="hidden" id="action" name="a" value="" >
	<div class="form-group">
		<div class="pull-right pd-bt-10">
			<?php
			if($res && $num){ 
				echo __('Page').':'.$pageNav->getPageLinks();
			}
			?>
		</div>
	</div>
	<div class="row">
	<div class="col-md-12">
		<div class="table-light table-responsive">
			<table class="table table-bordered list" border="0" cellspacing="1" cellpadding="0" width="940">
			<thead>
				<tr>
					<th width="4%" class="text-center"><?php if($res && $num){ ?><input type="checkbox" id="selectToggle" class="ckb"><?php }else{ echo __(''); }?></th>
					<th width="56%"><a <?php echo $email_sort ?? ""; ?> href="banlist.php?<?php echo $qstr; ?>&sort=email"><?php echo __('Email Address');?></a></th>
					<th width="10%"><a  <?php echo $status_sort ?? ""; ?> href="banlist.php?<?php echo $qstr; ?>&sort=status"><?php echo __('Ban Status');?></a></th>
					<th width="10%"><a <?php echo $created_sort ?? ""; ?> href="banlist.php?<?php echo $qstr; ?>&sort=created"><?php echo __('Date Added');?></a></th>
					<th width="20%"><a <?php echo $updated_sort ?? ""; ?> href="banlist.php?<?php echo $qstr; ?>&sort=updated"><?php echo __('Last Updated');?></a></th>
				</tr>
			</thead>
			<tbody>
				<?php
				if($res && db_num_rows($res)):
					$ids=($errors && is_array($_POST['ids']))?$_POST['ids']:null;
				while ($row = db_fetch_array($res)) {
					$sel=false;
				if($ids && in_array($row['id'],$ids))
					$sel=true;
				?>
				<tr id="<?php echo esc_attr($row['id']); ?>">
					<td align="center">
					<input type="checkbox" class="ckb" name="ids[]" value="<?php echo esc_attr($row['id']); ?>" <?php echo $sel?'checked="checked"':''; ?>>
					</td>
					<td>&nbsp;<a href="banlist.php?id=<?php echo esc_attr($row['id']); ?>"><?php echo Format::htmlchars($row['val']); ?></a></td>
					<td>&nbsp;&nbsp;<?php echo $row['isactive']?__('Active'):'<b>'.__('Disabled').'</b>'; ?></td>
					<td><?php echo Format::date($row['created']); ?></td>
					<td><?php echo Format::datetime($row['updated']); ?>&nbsp;</td>
				</tr>
				<?php
				} //end of while.
				endif; ?>
			</tbody>
			<?php 
			if(!$res || !$num){
				echo '<tfoot><tr><td colspan="5">'.__('No banned emails found!').'</td></tr></tfoot>';
			}
			?>
			</table>
		</div>
	</div>
	</div>
		<?php
			if($res && $num){ //Show options..
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
		<div class="panel-title table-caption"><?php echo __('Please Confirm');?>
		<a class="close_me pull-right" href=""><i class="fa fa-close"></i></a>
		</div>
	</div>
	<div class="panel-body">
		<p class="confirm-action" style="display:none;" id="enable-confirm">
			<?php echo sprintf(__('Are you sure you want to <b>enable</b> %s?'),
			_N('selected ban rule', 'selected ban rules', 2));?>
		</p>
		<p class="confirm-action" style="display:none;" id="disable-confirm">
			<?php echo sprintf(__('Are you sure you want to <b>disable</b> %s?'),
			_N('selected ban rule', 'selected ban rules', 2));?>
		</p>
		<p class="confirm-action" style="display:none;" id="delete-confirm">
			<font class="text-danger"><?php echo sprintf(__('Are you sure you want to DELETE %s?'),
			_N('selected ban rule', 'selected ban rules', 2));?></font>
		</p>
		<div><?php echo __('Please confirm to continue.');?></div>
		<br/>

		<span class="buttons pull-left">
			<input type="button" value="<?php echo __('No, Cancel');?>" class="close_me btn btn-success">
		</span>
		<span class="buttons pull-right">
			<input type="button" value="<?php echo __('Yes, Do it!');?>" class="confirm btn btn-info">
		</span>
	</div>
</div>

