<?php
if(!defined('DSKADMININC') || !$thisstaff || !$thisstaff->isAdmin()) die('Access Denied');

$qs = array();
if(!empty($_REQUEST['type'])) {
    $qs += array('type' => $_REQUEST['type']);
}
$type=null;
switch(strtolower($_REQUEST['type'] ?? '')){
    case 'error':
        $title=__('Errors');
        $type=$_REQUEST['type'];
        break;
    case 'warning':
        $title=__('Warnings');
        $type=$_REQUEST['type'];
        break;
    case 'debug':
        $title=__('Debug logs');
        $type=$_REQUEST['type'];
        break;
    default:
        $type=null;
        $title=__('All logs');
}

$qwhere =' WHERE 1';
//Type
if($type)
    $qwhere.=' AND log_type='.db_input($type);

//dates
$startTime  =(!empty($_REQUEST['startDate']) && (strlen($_REQUEST['startDate'])>=8))?strtotime($_REQUEST['startDate']):0;
$endTime    =(!empty($_REQUEST['endDate']) && (strlen($_REQUEST['endDate'])>=8))?strtotime($_REQUEST['endDate']):0;
if( ($startTime && $startTime>time()) or ($startTime>$endTime && $endTime>0)){
    $errors['err']=__('Entered date span is invalid. Selection ignored.');
    $startTime=$endTime=0;
}else{
    if($startTime){
        $qwhere.=' AND created>=FROM_UNIXTIME('.$startTime.')';
        $qs += array('startDate' => $_REQUEST['startDate']);
    }
    if($endTime){
        $qwhere.=' AND created<=FROM_UNIXTIME('.$endTime.')';
        $qs += array('endDate' => $_REQUEST['endDate']);
    }
}
$sortOptions=array('id'=>'log.log_id', 'title'=>'log.title','type'=>'log_type','ip'=>'log.ip_address'
                    ,'date'=>'log.created','created'=>'log.created','updated'=>'log.updated');
$orderWays=array('DESC'=>'DESC','ASC'=>'ASC');
$sort=($_REQUEST['sort'] ?? '' && $sortOptions[strtolower($_REQUEST['sort'] ?? '')])?strtolower($_REQUEST['sort'] ?? ''):'id';
//Sorting options...
if($sort && $sortOptions[$sort]) {
    $order_column =$sortOptions[$sort];
}
$order_column=$order_column?$order_column:'log.created';

$order = null;
if(($_REQUEST['order'] ?? '') && $orderWays[strtoupper($_REQUEST['order'] ?? '')]) {
    $order=$orderWays[strtoupper($_REQUEST['order'] ?? '')] ?? 'DESC';
}
$order=$order?$order:'DESC';

if($order_column && strpos($order_column,',')){
    $order_column=str_replace(','," $order,",$order_column);
}
$x=$sort.'_sort';
$$x=' class="'.strtolower($order).'" ';
$order_by="$order_column $order ";

$qselect = 'SELECT log.* ';
$qfrom=' FROM '.SYSLOG_TABLE.' log ';
$total=db_count("SELECT count(*) $qfrom $qwhere");
$page = (!empty($_GET['p']) && is_numeric($_GET['p']))?$_GET['p']:1;
//pagenate
$pageNav=new Pagenate($total, $page, PAGE_LIMIT);
$qstr = '&amp;'. Http::build_query($qs);
$qstr .= 'order='.($_REQUEST['order'] ?? ''=='DESC' ? 'ASC' : 'DESC');
$qs += array('sort' => $_REQUEST['sort'] ?? '', 'order' => $_REQUEST['order'] ?? '');
$pageNav->setURL('logs.php',$qs);
$query="$qselect $qfrom $qwhere ORDER BY $order_by LIMIT ".$pageNav->getStart().",".$pageNav->getLimit();
$res=db_query($query);
if($res && ($num=db_num_rows($res)))
    $showing=$pageNav->showing().' '.$title;
else
    $showing=__('No logs found');
?>

<div class="panel">
<div class="panel-heading">
	<div class="panel-title table-caption"><?php echo __('System Logs');?>&nbsp;<i class="help-tip fa fa-question-circle" href="#system_logs"></i></div>
</div>
<div class="panel-body">
	<form action="logs.php" method="get" name="filter" class="form-inline col-md-9">
		<div class="form-group">
			<label for="sd"><?php echo __('Choose Date'); ?>:</label>
			<div class="input-daterange datepicker-range input-group" id="datepicker-range">
			  <input type="text" class="form-control" id="sd" name="startDate" placeholder="<?php echo __('Start Date'); ?>" value="<?php echo Format::htmlchars($_REQUEST['startDate'] ?? ''); ?>" autocomplete=OFF>
			  <span class="input-group-addon">to</span>
			  <input type="text" class="form-control" id="ed" name="endDate" placeholder="<?php echo __('End Date'); ?>" value="<?php echo Format::htmlchars($_REQUEST['endDate'] ?? ''); ?>" autocomplete=OFF>
			</div>
		</div>
		&nbsp;
		<div class="form-group">
			<label for="type_input"><?php echo __('Log Level'); ?>:</label>
			<select class="form-control"  id="type_input" name="type" >
				<option value="" selected><?php echo __('All');?></option>
				<option value="Error" <?php echo ($type=='Error')?'selected="selected"':''; ?>><?php echo __('Error');?></option>
				<option value="Warning" <?php echo ($type=='Warning')?'selected="selected"':''; ?>><?php echo __('Warning');?></option>
				<option value="Debug" <?php echo ($type=='Debug')?'selected="selected"':''; ?>><?php echo __('Debug');?></option>
			</select>
		</div>
		<button type="submit" class="btn btn-primary"><?php echo __('Filter');?></button>
		</form>
		<form action="logs.php" method="POST" name="logs" class="form-inline">
		<div id="actions" class="pull-right">
			<a href="javascript:void(0)">
				<button type="submit" name="delete" class="button btn btn-danger"><i class="fa fa-trash"></i>&nbsp;
					<?php echo __('Delete Selected Entries');?>
				</button>
			</a>
		</div>
		
	<br/>
	

<?php csrf_token(); ?>
<input type="hidden" name="do" value="mass_process">
<input type="hidden" id="action" name="a" value="">
<br/>
	<div class="form-group pull-right">
		<?php
			if($res && $num){//Show options..
		?>
		<div class="pull-right">
			<?php
				echo __('Page').':'.$pageNav->getPageLinks();
				}
			?>
		</div>
	</div>
<div class="row">
<div class="col-md-12">
<div class="table-light table-responsive">
<br/>
<table class="table table-bordered list" border="0" cellspacing="1" cellpadding="0" width="940">
	<thead>
	<tr>
		<th width="4%" class="text-center" ><?php if($res && $num){ ?><input type="checkbox" id="selectToggle" class="ckb"><?php }else{ echo __(''); }?></th>
		<th><a <?php echo $title_sort ?? ''; ?> href="logs.php?<?php echo $qstr; ?>&sort=title"><?php echo __('Log Title');?></a></th>
		<th><a  <?php echo $type_sort ?? ''; ?> href="logs.php?<?php echo $qstr; ?>&sort=type"><?php echo __('Log Type');?></a></th>
		<th nowrap><a  <?php echo $date_sort ?? ''; ?>href="logs.php<?php echo $qstr; ?>&sort=date"><?php echo __('Log Date');?></a></th>
		<th><a  <?php echo $ip_sort ?? ''; ?> href="logs.php?<?php echo $qstr; ?>&sort=ip"><?php echo __('IP Address');?></a></th>
	</tr>
	</thead>
	<tbody>
	<?php
		$total=0;
		$ids=($errors && is_array($_POST['ids']))?$_POST['ids']:null;
		if($res && db_num_rows($res)):
			while ($row = db_fetch_array($res)) {
				$sel=false;
				if($ids && in_array($row['log_id'],$ids))
					$sel=true;
				?>
		<tr id="<?php echo esc_attr($row['log_id']); ?>">
			<td align="center" nowrap>
			  <input type="checkbox" class="ckb" name="ids[]" value="<?php echo esc_attr($row['log_id']); ?>"
						<?php echo $sel?'checked="checked"':''; ?>></td>
			<td>&nbsp;
				<a class="ajax-tooltip" href="#log/<?php echo esc_attr($row['log_id']); ?>" data-container="body" data-toggle="popover" data-placement="right" data-id="<?php echo esc_attr($row['log_id']); ?>">
				<?php echo Format::htmlchars($row['title']); ?></a>
			</td>
			<td><?php echo esc_html($row['log_type']); ?></td>
			<td>&nbsp;<?php echo Format::daydatetime($row['created']); ?></td>
			<td><?php echo Format::htmlchars($row['ip_address']); ?></td>
			</tr>
			<?php
			} //end of while.
		endif; ?>
	</tbody>
	<?php 
	if(!$res || !$num){
		echo '<tfoot><tr><td colspan="6">'.__('No logs found!').'</td></tr></tfoot>';
	}
	?>
</table>
</div>
	<?php
		if($res && $num){//Show options..
	?>
	<div class="pull-right">
		<span class="faded"><?php echo $pageNav->showing(); ?>&nbsp;&mdash;&nbsp;</span>
		<?php
			echo __('Page').':'.$pageNav->getPageLinks();
			}
		?>
	</div>
</div>
</div>
</form>
</div>
</div>

<div style="display:none;border:none" class="dialog panel panel-primary panel-dark" id="confirm-action">
	<div class="panel-heading">
		<div class="panel-title"><?php echo __('Please Confirm');?>
		<a class="close_me pull-right" href=""><i class="fa fa-close"></i></a>
		</div>
	</div>
	<div class="panel-body">
		<p class="confirm-action" style="display:none;" id="delete-confirm">
			<font class="text-danger"><?php echo sprintf(__('Are you sure you want to DELETE %s?'),
			_N('selected log entry', 'selected log entries', 2));?></font>
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
