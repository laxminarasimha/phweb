<?php
if(!defined('DSKADMININC') || !$thisstaff->isAdmin()) die('Access Denied');
$order = $_REQUEST['order'] ?? 'ASC';
$targets = Filter::getTargets();
$qs = array();
$sql='SELECT filter.*,count(rule.id) as rules '.
     'FROM '.FILTER_TABLE.' filter '.
     'LEFT JOIN '.FILTER_RULE_TABLE.' rule ON(rule.filter_id=filter.id) '.
     "WHERE filter.`name` <> 'SYSTEM BAN LIST' ".
     'GROUP BY filter.id';
$sortOptions=array('name'=>'filter.name','status'=>'filter.isactive','order'=>'filter.execorder','rules'=>'rules',
                   'target'=>'filter.target', 'created'=>'filter.created','updated'=>'filter.updated');
$orderWays=array('DESC'=>'DESC','ASC'=>'ASC');
$sort=($_REQUEST['sort'] ?? '' && $sortOptions[strtolower($_REQUEST['sort'] ?? '')])?strtolower($_REQUEST['sort'] ?? ''):'name';
//Sorting options...
if($sort && $sortOptions[$sort]) {
    $order_column =$sortOptions[$sort];
}
$order_column=$order_column?$order_column:'filter.name';

if(($_REQUEST['order'] ?? '') && $orderWays[strtoupper($_REQUEST['order'] ?? '')]) {
    $order=$orderWays[strtoupper($_REQUEST['order'] ?? '')];
}
$order=$order?$order:'ASC';

if($order_column && strpos($order_column,',')){
    $order_column=str_replace(','," $order,",$order_column);
}
$x=$sort.'_sort';
$$x=' class="'.strtolower($order).'" ';
$order_by="$order_column $order ";

$total=db_count('SELECT count(*) FROM '.FILTER_TABLE.' filter
					WHERE filter.`name` <> "SYSTEM BAN LIST"');
$page=(!empty($_GET['p']) && is_numeric($_GET['p']))?$_GET['p']:1;
$pageNav=new Pagenate($total, $page, PAGE_LIMIT);
$qstr = '&amp;'. Http::build_query($qs);
$qs += array('sort' => $_REQUEST['sort'] ?? '', 'order' => $_REQUEST['order'] ?? '' ?? '');
$pageNav->setURL('filters.php', $qs);
$qstr.='&amp;order='.($order=='DESC' ? 'ASC' : 'DESC');
$query="$sql ORDER BY $order_by LIMIT ".$pageNav->getStart().",".$pageNav->getLimit();
$res=db_query($query);
if($res && ($num=db_num_rows($res)))
    $showing=$pageNav->showing().' '._N('filter', 'filters', $num);
else
    $showing=__('No filters found');

?>
<div class="panel">
<form action="filters.php" method="POST" name="filters">
<div class="panel-heading">
	<div class="panel-title table-caption"><?php echo __('Ticket Filters');?>
	</div>
</div>
<div class="panel-body">
<div class="pull-right form-group">	
	<a href="filters.php?a=add" class="btn btn-success btn-outline"><i class="fa fa-plus-circle"></i> <?php echo __('Add New Filter');?>
	</a>
	<div class="btn-group">
		<button type="button" class="btn btn-outline dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i>&nbsp;<?php echo __('More');?></button>
		<ul class="dropdown-menu dropdown-menu-right" id="actions">
			<li>
				<a class="confirm" data-name="enable" href="filters.php?a=enable">
					<i class="fa fa-check"></i>
					<?php echo __( 'Enable'); ?>
				</a>
			</li>
			<li>
				<a class="confirm" data-name="disable" href="filters.php?a=disable">
					<i class="fa fa-ban"></i>
					<?php echo __( 'Disable'); ?>
				</a>
			</li>
			<li class="danger">
				<a class="confirm" data-name="delete" href="filters.php?a=delete">
					<i class="fa fa-trash"></i>
					<?php echo __( 'Delete'); ?>
				</a>
			</li>
		</ul>
	</div>
	<div class="form-group pd-tp-10">
		<?php
			if($res && $num){ //Show options..
		?>
		<div class="pull-right">
			<?php
				echo '<div>&nbsp;'.__('Page').':'.$pageNav->getPageLinks().'&nbsp;</div>';
			}?>
		</div>
	</div>
</div>

<?php csrf_token(); ?>
<input type="hidden" name="do" value="mass_process" >
<input type="hidden" id="action" name="a" value="" >
<div class="row">
	<div class="col-md-12">
	<div class="table-light table-responsive">
	<table class="table table-bordered list" border="0" cellspacing="1" cellpadding="0" width="940">
		<thead>
		<tr>
			<th width="4%" style="text-align:center"><input type="checkbox" id="selectToggle" class="ckb"></th>
			<th width="32%"><a <?php echo $name_sort ?? ""; ?> href="filters.php?<?php echo $qstr; ?>&sort=name"><?php echo __('Name');?></a></th>
			<th width="8%"><a  <?php echo $status_sort ?? ""; ?> href="filters.php?<?php echo $qstr; ?>&sort=status"><?php echo __('Status');?></a></th>
			<th width="8%" style="text-align:center;"><a  <?php echo $order_sort ?? ""; ?> href="filters.php?<?php echo $qstr; ?>&sort=order"><?php echo __('Order');?></a></th>
			<th width="8%" style="text-align:center;"><a  <?php echo $rules_sort ?? ""; ?> href="filters.php?<?php echo $qstr; ?>&sort=rules"><?php echo __('Rules');?></a></th>
			<th width="10%"><a  <?php echo $target_sort ?? ""; ?> href="filters.php?<?php echo $qstr; ?>&sort=target"><?php echo __('Target');?></a></th>
			<th width="12%" nowrap><a  <?php echo $created_sort ?? ""; ?>href="filters.php?<?php echo $qstr; ?>&sort=created"><?php echo __('Date Added');?></a></th>
			<th width="18%" nowrap><a  <?php echo $updated_sort ?? ""; ?>href="filters.php?<?php echo $qstr; ?>&sort=updated"><?php echo __('Last Updated');?></a></th>
		</tr>
		</thead>
		<tbody>
		<?php
			$total=0;
			$ids=($errors && is_array($_POST['ids']))?$_POST['ids']:null;
			if($res && db_num_rows($res)):
				while ($row = db_fetch_array($res)) {
					$sel=false;
					if($ids && in_array($row['id'],$ids))
						$sel=true;
					?>
			<tr id="<?php echo esc_attr($row['id']); ?>">
				<td align="center">
				  <input type="checkbox" class="ckb" name="ids[]" value="<?php echo esc_attr($row['id']); ?>"
							<?php echo $sel?'checked="checked"':''; ?>>
				</td>
				<td>&nbsp;<a href="filters.php?id=<?php echo esc_attr($row['id']); ?>"><?php echo Format::htmlchars($row['name']); ?></a></td>
				<td><?php echo $row['isactive']?__('Active'):'<b>'.__('Disabled').'</b>'; ?></td>
				<td style="text-align:right;padding-right:25px;"><?php echo esc_html($row['execorder']); ?>&nbsp;</td>
				<td style="text-align:right;padding-right:25px;"><?php echo esc_html($row['rules']); ?>&nbsp;</td>
					<td>&nbsp;<?php echo Format::htmlchars($targets[$row['target']]); ?></td>
					<td>&nbsp;<?php echo Format::date($row['created']); ?></td>
					<td>&nbsp;<?php echo Format::datetime($row['updated']); ?></td>
				</tr>
				<?php
				} //end of while.
			endif; ?>
		</tbody>
		<?php 
		if(!$res || !$num){
			echo '<tfoot><tr><td colspan="8">'.__('No filters found').'</td></tr></tfoot>';
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
		}?>
	</div>
</form>
</div>
</div>

<div style="display:none;border:none" class="dialog panel panel-primary panel-dark" id="confirm-action">
	<div class="panel-heading">
		<div class="drag-handle panel-title"><span><?php echo __('Please Confirm');?></span><a class="close_me pull-right" href=""><i class="fa fa-remove"></i></a>
		</div>
	</div>
	<div class="panel-body">
	<p class="confirm-action" style="display:none;" id="enable-confirm">
		<?php echo sprintf(__('Are you sure you want to <b>enable</b> %s?'),
			_N('selected filter', 'selected filters', 2));?>
	</p>
	<p class="confirm-action" style="display:none;" id="disable-confirm">
		<?php echo sprintf(__('Are you sure you want to <b>disable</b> %s?'),
			_N('selected filter', 'selected filters', 2));?>
	</p>
	<p class="confirm-action" style="display:none;" id="delete-confirm">
		<font class="text-danger"><?php echo sprintf(__('Are you sure you want to DELETE %s?'),
			_N('selected filter', 'selected filters', 2));?></font>
		<br><br><?php echo __('Deleted data CANNOT be recovered, including any associated rules.');?>
	</p>
	<div><?php echo __('Please confirm to continue.');?></div>
	<br/>
		<span class="buttons pull-left">
			<input type="button" value="<?php echo __('No, Cancel');?>" class="close_me btn btn-info">
		</span>
		<span class="buttons pull-right">
			<input type="button" value="<?php echo __('Yes, Do it!');?>" class="confirm btn btn-success ">
		</span>
	</div>
</div>

