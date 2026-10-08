<?php
if(!defined('DSKADMININC') || !$thisstaff) die('Access Denied');

$order = $_REQUEST['order'] ?? 'ASC';
$qs = array();
$sql='SELECT canned.*, count(attach.file_id) as files, dept.name as department '.
     ' FROM '.CANNED_TABLE.' canned '.
     ' LEFT JOIN '.DEPT_TABLE.' dept ON (dept.id=canned.dept_id) '.
     ' LEFT JOIN '.ATTACHMENT_TABLE.' attach
            ON (attach.object_id=canned.canned_id AND attach.`type`=\'C\' AND NOT attach.inline)';
$sql.=' WHERE 1';

$sortOptions=array('title'=>'canned.title','status'=>'canned.isenabled','dept'=>'department','updated'=>'canned.updated');
$orderWays=array('DESC'=>'DESC','ASC'=>'ASC');
$sort=($_REQUEST['sort'] ?? '' && $sortOptions[strtolower($_REQUEST['sort'] ?? '')])?strtolower($_REQUEST['sort'] ?? ''):'title';
//Sorting options...
if($sort && $sortOptions[$sort]) {
    $order_column =$sortOptions[$sort];
}

$order_column=$order_column?$order_column:'canned.title';

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

$total=db_count('SELECT count(*) FROM '.CANNED_TABLE.' canned ');
$page=(!empty($_GET['p']) && is_numeric($_GET['p']))?$_GET['p']:1;
$pageNav=new Pagenate($total, $page, PAGE_LIMIT);
$qstr = '&amp;'. Http::build_query($qs);
$qs += array('sort' => $_REQUEST['sort'] ?? '', 'order' => $_REQUEST['order'] ?? '');
$pageNav->setURL('canned.php', $qs);
//Ok..lets roll...create the actual query
$qstr .= '&order='.($order=='DESC'?'ASC':'DESC');
$query="$sql GROUP BY canned.canned_id ORDER BY $order_by LIMIT ".$pageNav->getStart().",".$pageNav->getLimit();
$res=db_query($query);
if($res && ($num=db_num_rows($res)))
    $showing=$pageNav->showing().' '._N('premade response', 'premade responses',
        $total);
else
    $showing=__('No premade responses found!');

?>
<div class="panel">
<form action="canned.php" method="POST" name="canned">
	<div class="panel-heading">
		<div class="panel-title table-caption">          
			<?php echo __('Canned Responses');?>
		</div>
	</div>
<div class="panel-body">
	<div class="pull-right form-group">
		<a href="canned.php?a=add" class="btn btn-success btn-outline"><i class="fa fa-plus-circle"></i> <?php
		echo __('Add New Response'); ?></a>
		<div class="btn-group">
		  <button type="button" class="btn btn-outline dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i>&nbsp;<?php echo __('More');?></button>
		  <ul class="dropdown-menu dropdown-menu-right" id="actions">
		  
				<li>
					<a class="confirm" data-name="enable" href="canned.php?a=enable">
						<i class="fa fa-check"></i>
						<?php echo __('Enable'); ?>
					</a>
				</li>
				<li>
					<a class="confirm" data-name="disable" href="canned.php?a=disable">
						<i class="fa fa-ban"></i>
						<?php echo __('Disable'); ?>
					</a>
				</li>
				<li class="danger">
					<a class="confirm" data-name="delete" href="canned.php?a=delete">
						<i class="fa fa-trash"></i>
						<?php echo __('Delete'); ?>
					</a>
				</li>
			</ul>
		</div>
		<div class="form-group">
			<?php
				if($res && $num){ //Show options..
			?>
			</br>
			<div class="pull-right">
				<?php
					echo __('Page').':'.$pageNav->getPageLinks();
				}
				?>
			</div>
		</div>
	</div>
<br /><br />
<?php csrf_token(); ?>
<input type="hidden" name="do" value="mass_process" >
<input type="hidden" id="action" name="a" value="" >
<div class="row">
	<div class="col-md-12">
	<div class="table-light table-responsive">
	<table class="table table-bordered list" border="0" cellspacing="1" cellpadding="0" width="940">
	<thead>
		<tr>
			<th width="4%" class="text-center"><?php if($res && $num){ ?>
			<input type="checkbox" id="selectToggle" class="ckb"><?php }else{ echo __(''); } ?></th>
			<th width="46%"><a <?php echo $title_sort ?? ""; ?> href="canned.php?<?php echo $qstr; ?>&sort=title"><?php echo __('Title');?></a></th>
			<th width="10%"><a <?php echo $status_sort ?? ""; ?> href="canned.php?<?php echo $qstr; ?>&sort=status"><?php echo __('Status');?></a></th>
			<th width="20%"><a <?php echo $dept_sort ?? ""; ?> href="canned.php?<?php echo $qstr; ?>&sort=dept"><?php echo __('Department');?></a></th>
			<th width="20%" nowrap><a  <?php echo $updated_sort ?? ""; ?>href="canned.php?<?php echo $qstr; ?>&sort=updated"><?php echo __('Last Updated');?></a></th>
		</tr>
	</thead>
	<tbody>
	<?php
		$total=0;
		$ids=($errors && is_array($_POST['ids']))?$_POST['ids']:null;
		if($res && db_num_rows($res)):
			while ($row = db_fetch_array($res)) {
				$sel=false;
				if($ids && in_array($row['canned_id'],$ids))
					$sel=true;
				$files=$row['files']?'<span class="Icon file">&nbsp;</span>':'';
				?>
			<tr id="<?php echo esc_attr($row['canned_id']); ?>">
				<td align="center">
				  <input type="checkbox" name="ids[]" value="<?php echo esc_attr($row['canned_id']); ?>" class="ckb"
							<?php echo $sel?'checked="checked"':''; ?> />
				</td>
			<td>
				<a href="canned.php?id=<?php echo esc_attr($row['canned_id']); ?>"><?php echo Format::truncate($row['title'],200); echo "&nbsp;$files"; ?></a>&nbsp;
			</td>
			<td><?php echo $row['isenabled']?__('Active'):'<b>'.__('Disabled').'</b>'; ?></td>
			<td><?php echo $row['department']?esc_html($row['department']):'&mdash; '.__('All Departments').' &mdash;'; ?></td>
				<td>&nbsp;<?php echo Format::datetime($row['updated']); ?></td>
			</tr>
			<?php
			} //end of while.
		endif; ?>
	</tbody>
	<?php 
	if(!$res || !$num){
		echo '<tfoot><tr><td colspan="5">'.sprintf(__('No canned responses &mdash; %s add one %s!'),
						'<a href="canned.php?a=add">','</a>').'</td></tr></tfoot>';
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
</form>
</div>

<div style="display:none;border:none" class="dialog panel panel-primary panel-dark" id="confirm-action">
	<div class="panel-heading">
		<div class="drag-handle panel-title"><?php echo __('Please Confirm');?>
		<a class="close_me pull-right" href=""><i class="fa fa-close"></i></a>
		</div>
	</div>
	<div class="panel-body">
	<p class="confirm-action" style="display:none;" id="enable-confirm">
		<?php echo sprintf(__('Are you sure you want to <b>enable</b> %s?'),
            _N('selected canned response', 'selected canned responses', 2));?>
	</p>
	<p class="confirm-action" style="display:none;" id="disable-confirm">
		<?php echo sprintf(__('Are you sure you want to <b>disable</b> %s?'),
            _N('selected canned response', 'selected canned responses', 2));?>
	</p>
	<p class="confirm-action" style="display:none;" id="delete-confirm">
		<font class="text-danger"><?php echo sprintf(__('Are you sure you want to DELETE %s?'),
            _N('selected canned response', 'selected canned responses', 2)); ?></font>
		<br><br><?php echo __('Deleted data CANNOT be recovered, including any associated attachments.'); ?>
	</p>
	<div><?php echo __('Please confirm to continue.'); ?></div>
	<br/>
	<p class="full-width">
		<span class="buttons pull-left">
			<input type="button" value="<?php echo __('No, Cancel'); ?>" class="close_me btn btn-success">
		</span>
		<span class="buttons pull-right">
			<input type="button" value="<?php echo __('Yes, Do it!'); ?>" class="confirm btn btn-info">
		</span>
	</p>
	</div>
</div>
</div>
