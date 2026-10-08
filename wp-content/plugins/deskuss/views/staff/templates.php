<?php
if(!defined('DSKADMININC') || !$thisstaff->isAdmin()) die('Access Denied');

$order = $_REQUEST['order'] ?? 'ASC';
$qs = array();
$sql='SELECT tpl.*,count(dept.tpl_id) as depts '.
     'FROM '.EMAIL_TEMPLATE_GRP_TABLE.' tpl '.
     'LEFT JOIN '.DEPT_TABLE.' dept USING(tpl_id) '.
     'WHERE 1 ';
$sortOptions=array('name'=>'tpl.name','status'=>'tpl.isactive','created'=>'tpl.created','updated'=>'tpl.updated');
$orderWays=array('DESC'=>'DESC','ASC'=>'ASC');
$sort=($_REQUEST['sort'] ?? '' && $sortOptions[strtolower($_REQUEST['sort'] ?? '')])?strtolower($_REQUEST['sort'] ?? ''):'name';
//Sorting options...
if($sort && $sortOptions[$sort]) {
    $order_column =$sortOptions[$sort];
}
$order_column=$order_column?$order_column:'tpl.name';

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

$total=db_count('SELECT count(*) FROM '.EMAIL_TEMPLATE_GRP_TABLE.' tpl ');
$page=(!empty($_GET['p']) && is_numeric($_GET['p']))?$_GET['p']:1;
$pageNav=new Pagenate($total, $page, PAGE_LIMIT);
$qstr = '&amp;'. Http::build_query($qs);
$qs += array('sort' => $_REQUEST['sort'] ?? '', 'order' => $_REQUEST['order'] ?? '');
$pageNav->setURL('templates.php', $qs);
$qstr .= '&amp;order='.($order=='DESC' ? 'ASC' : 'DESC');
$query="$sql GROUP BY tpl.tpl_id ORDER BY $order_by LIMIT ".$pageNav->getStart().",".$pageNav->getLimit();
$res=db_query($query);
if($res && ($num=db_num_rows($res)))
    $showing=$pageNav->showing().' '._N('template', 'templates', $num);
else
    $showing=__('No templates found!');

?>
<div class="panel">
<form action="templates.php" method="POST" name="tpls">

<div class="panel-heading">
	<div class="panel-title table-caption"><?php echo __('Email Template Sets'); ?></div>
</div>
	
<div class="panel-body">
	<div class="pull-right form-group">
		<a href="templates.php?a=add" class="btn btn-success btn-outline"><i class="fa fa-plus-circle"></i> <?php echo __('Add New Template Set'); ?></a>
		
		<div class="btn-group">
			<button type="button" class="btn btn-outline dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i>&nbsp;<?php echo __('More');?></button>
			<ul class="dropdown-menu dropdown-menu-right" id="actions">
				<li>
					<a class="confirm" data-name="enable" href="templates.php?a=enable">
						<i class="fa fa-check"></i>
						<?php echo __( 'Enable'); ?>
					</a>
				</li>
				<li>
					<a class="confirm" data-name="disable" href="templates.php?a=disable">
						<i class="fa fa-ban"></i>
						<?php echo __( 'Disable'); ?>
					</a>
				</li>
				<li class="danger">
					<a class="confirm" data-name="delete" href="templates.php?a=delete">
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
					echo __('Page').':'.$pageNav->getPageLinks();
					}
				?>
			</div>
		</div>
	</div>
<br/><br/>
<?php csrf_token(); ?>
<input type="hidden" name="do" value="mass_process" >
<input type="hidden" id="action" name="a" value="" >
	<div class="row">
	<div class="col-md-12">
	<div class="table-light table-responsive">
	<table class="table table-bordered list" border="0" cellspacing="1" cellpadding="0" width="940">
	<thead>
		<tr>
			<th width="4%" class="text-center"><?php if($res && $num){ ?><input type="checkbox" id="selectToggle" class="ckb"><?php }else{ echo __(''); }?></th>
			<th width="46%"><a <?php echo $name_sort ?? ""; ?> href="templates.php?<?php echo $qstr; ?>&sort=name"><?php echo __('Name'); ?></a></th>
			<th width="10%"><a  <?php echo $status_sort ?? ""; ?> href="templates.php?<?php echo $qstr; ?>&sort=status"><?php echo __('Status'); ?></a></th>
			<th width="10%"><a <?php echo $inuse_sort ?? ""; ?> href="templates.php?<?php echo $qstr; ?>&sort=inuse"><?php echo __('In-Use'); ?></a></th>
			<th width="10%" nowrap><a  <?php echo $created_sort ?? ""; ?>href="templates.php?<?php echo $qstr; ?>&sort=created"><?php echo __('Date Added'); ?></a></th>
			<th width="20%" nowrap><a  <?php echo $updated_sort ?? ""; ?>href="templates.php?<?php echo $qstr; ?>&sort=updated"><?php echo __('Last Updated'); ?></a></th>
		</tr>
	</thead>
	<tbody>
    <?php
        $total=0;
        $ids=($errors && is_array($_POST['ids']))?$_POST['ids']:null;
        if($res && db_num_rows($res)):
            $defaultTplId=$cfg->getDefaultTemplateId();
            while ($row = db_fetch_array($res)) {
                $inuse=($row['depts'] || $row['tpl_id']==$defaultTplId);
                $sel=false;
                if($ids && in_array($row['tpl_id'],$ids))
                    $sel=true;

                $default=($defaultTplId==$row['tpl_id'])?'<small class="fadded">('.__('System Default').')</small>':'';
                ?>
            <tr id="<?php echo esc_attr($row['tpl_id']); ?>">
                <td align="center">
                  <input type="checkbox" class="ckb" name="ids[]" value="<?php echo esc_attr($row['tpl_id']); ?>"
                            <?php echo $sel?'checked="checked"':''; ?> <?php echo $default?'disabled="disabled"':''; ?> >
                </td>
                <td>&nbsp;<a href="templates.php?tpl_id=<?php echo esc_attr($row['tpl_id']); ?>"><?php echo Format::htmlchars($row['name']); ?></a>
                &nbsp;<?php echo $default; ?></td>
                <td>&nbsp;<?php echo $row['isactive']?__('Active'):'<b>'.__('Disabled').'</b>'; ?></td>
                <td>&nbsp;&nbsp;<?php echo ($inuse)?'<b>'.__('Yes').'</b>':__('No'); ?></td>
                <td>&nbsp;<?php echo Format::date($row['created']); ?></td>
                <td>&nbsp;<?php echo Format::datetime($row['updated']); ?></td>
            </tr>
            <?php
            } //end of while.
        endif; ?>
	</tbody>
	<?php 
	if(!$res || !$num){
		echo '<tfoot><tr><td colspan="6">'.__('No templates found!').'</td></tr></tfoot>';
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
		_N('selected template set', 'selected template sets', 2));?>
	</p>
	<p class="confirm-action" style="display:none;" id="disable-confirm">
		<?php echo sprintf(__('Are you sure you want to <b>disable</b> %s?'),
		_N('selected template set', 'selected template sets', 2));?>
	</p>
	<p class="confirm-action" style="display:none;" id="delete-confirm">
		<font class="text-danger"><?php echo sprintf(__('Are you sure you want to DELETE %s?'),
		_N('selected template set', 'selected template sets', 2));?></font>
		<br><br><?php echo __('Deleted data CANNOT be recovered.'); ?>
	</p>
	<div><?php echo __('Please confirm to continue.');?></div>
	<br/>
	<p class="full-width">
		<span class="buttons pull-left">
			<input type="button" value="<?php echo __('No, Cancel');?>" class="close_me btn btn-success">
		</span>
		<span class="buttons pull-right">
			<input type="button" value="<?php echo __('Yes, Do it!');?>" class="confirm btn btn-info">
		</span>
	</p>
	</div>
</div>
