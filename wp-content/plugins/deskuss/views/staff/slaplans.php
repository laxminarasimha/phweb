<?php
if(!defined('DSKADMININC') || !$thisstaff->isAdmin()) die('Access Denied');

$qs = array();
$sortOptions=array(
        'name' => 'name',
        'status' => 'isactive',
        'period' => 'grace_period',
        'created' => 'created',
        'updated' => 'updated'
        );

$orderWays = array('DESC'=>'DESC', 'ASC'=>'ASC');
$sort = ($_REQUEST['sort'] ?? '' && $sortOptions[strtolower($_REQUEST['sort'] ?? '')]) ? strtolower($_REQUEST['sort'] ?? '') : 'name';
if ($sort && $sortOptions[$sort]) {
    $order_column = $sortOptions[$sort];
}

$order_column = $order_column ? $order_column : 'name';

if ($_REQUEST['order'] ?? '' ?? '' && isset($orderWays[strtoupper($_REQUEST['order'] ?? '' ?? '')])) {
    $order = $orderWays[strtoupper($_REQUEST['order'] ?? '' ?? '')];
} else {
    $order = 'ASC';
}

if ($order_column && strpos($order_column,',')) {
    $order_column=str_replace(','," $order,",$order_column);
}
$x=$sort.'_sort';
$$x=' class="'.strtolower($order).'" ';
$page = (!empty($_GET['p']) && is_numeric($_GET['p'])) ? $_GET['p'] : 1;
$count = SLA::objects()->count();
$qstr = '&amp;'. Http::build_query($qs);
$qs += array('sort' => $_REQUEST['sort'] ?? '', 'order' => $_REQUEST['order'] ?? '' ?? '');

$pageNav = new Pagenate($count, $page, PAGE_LIMIT);
$pageNav->setURL('slas.php', $qs);
$showing = $pageNav->showing().' '._N('Service Level Agreement', 'Service Level Agreements', $count);
$qstr .= '&amp;order='.($order=='DESC' ? 'ASC' : 'DESC');
?>
<div class="panel">
<form action="slas.php" method="POST" name="slas">
<div class="panel-heading">
	<div class="panel-title table-caption"><?php echo __('Service Level Agreements');?>
	</div>
</div>
  
<div class="panel-body">
	<div class="pull-right form-group">
		<a href="slas.php?a=add" class="btn btn-success btn-outline action-button"><i class="fa fa-plus-circle"></i> <?php echo __('Add New SLA Plan');?></a>
		<div class="btn-group">
			<button type="button" class="btn btn-outline dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i>&nbsp;<?php echo __('More');?></button>
			<ul class="dropdown-menu dropdown-menu-right" id="actions">
			 <li>
					<a class="confirm" data-name="enable" href="slas.php?a=enable">
						<i class="fa fa-check"></i>
						<?php echo __( 'Enable'); ?>
					</a>
				</li>
				<li>
					<a class="confirm" data-name="disable" href="slas.php?a=disable">
						<i class="fa fa-ban"></i>
						<?php echo __( 'Disable'); ?>
					</a>
				</li>
				<li class="danger">
					<a class="confirm" data-name="delete" href="slas.php?a=delete">
						<i class="fa fa-trash"></i>
						<?php echo __( 'Delete'); ?>
					</a>
				</li>
			</ul>
		</div>
		<div class="form-group pd-tp-10">
			<?php
				if ($count){ //Show options..
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
	<?php csrf_token(); ?>
	<input type="hidden" name="do" value="mass_process" >
	<input type="hidden" id="action" name="a" value="" >
	<div class="row">
		<div class="col-md-12">
		<div class="table-light table-responsive">
		<table class="table table-bordered list" border="0" cellspacing="1" cellpadding="0" width="940">
		<thead>
			<tr>
				<th width="4%" class="text-center"><?php if($count){ ?><input type="checkbox" id="selectToggle" class="ckb"><?php }else{ echo __(''); }?></th>
				<th width="38%"><a <?php echo $name_sort ?? ""; ?> href="slas.php?<?php echo $qstr; ?>&sort=name"><?php echo __('Name');?></a></th>
				<th width="8%"><a <?php echo $status_sort ?? ""; ?> href="slas.php?<?php echo $qstr; ?>&sort=status"><?php echo __('Status');?></a></th>
				<th><a <?php echo $period_sort ?? ""; ?> href="slas.php?<?php echo $qstr; ?>&sort=period"><?php echo __('Grace Period (hrs)');?></a></th>
				<th width="15%" nowrap><a <?php echo $created_sort ?? ""; ?>href="slas.php?<?php echo $qstr; ?>&sort=created"><?php echo __('Date Added');?></a></th>
				<th width="20%" nowrap><a <?php echo $updated_sort ?? ""; ?>href="slas.php?<?php echo $qstr; ?>&sort=updated"><?php echo __('Last Updated');?></a></th>
			</tr>
		</thead>
		<tbody>
		<?php
			$total=0;
			$ids = ($errors && is_array($_POST['ids'])) ? $_POST['ids'] : null;
			if ($count) {
				$slas = SLA::objects()
					->order_by(sprintf('%s%s',
								strcasecmp($order, 'DESC') ? '' : '-',
								$order_column))
					->limit($pageNav->getLimit())
					->offset($pageNav->getStart());

				$defaultId = $cfg->getDefaultSLAId();
				foreach ($slas as $sla) {
					$sel=false;
					$id = $sla->getId();
					if($ids && in_array($id, $ids))
						$sel=true;

					$default = '';
					if ($id == $defaultId)
						$default = '<small><em>(Default)</em></small>';
					?>
				<tr id="<?php echo esc_attr($id); ?>">
					<td align="center">
				  <input type="checkbox" class="ckb" name="ids[]" value="<?php echo esc_attr($id); ?>"
					<?php echo $sel ? 'checked="checked"' :'' ; ?>>
					</td>
					<td>&nbsp;<a href="slas.php?id=<?php echo esc_attr($id);
						?>"><?php echo Format::htmlchars($sla->getName());
						?></a>&nbsp;<?php echo $default; ?></td>
					<td><?php echo $sla->isActive() ? __('Active') : '<b>'.__('Disabled').'</b>'; ?></td>
					<td style="text-align:right;padding-right:35px;"><?php echo esc_html($sla->getGracePeriod()); ?>&nbsp;</td>
					<td>&nbsp;<?php echo Format::date($sla->getCreateDate()); ?></td>
					<td>&nbsp;<?php echo Format::datetime($sla->getUpdateDate()); ?></td>
				</tr>
				<?php
				} //end of foreach.
			} ?>
		</tbody>
		<?php 
		if(!$count){
			echo '<tfoot><tr><td colspan="6">'.__('No SLA plans found').'</td></tr></tfoot>';
		}
		?>
		</table>
		</div>
		</div>
	</div>
	<?php
		if ($count){ //Show options..
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
    <div class="panel-title"><?php echo __('Please Confirm');?>
    <a class="close_me pull-right" href=""><i class="fa fa-close"></i></a>
	</div>
</div>
   
<div class="panel-body">
	<p class="confirm-action" style="display:none;" id="enable-confirm">
		<?php echo sprintf(__('Are you sure you want to <b>enable</b> %s?'),
			_N('selected SLA plan', 'selected SLA plans', 2));?>
	</p>
	<p class="confirm-action" style="display:none;" id="disable-confirm">
		<?php echo sprintf(__('Are you sure you want to <b>disable</b> %s?'),
			_N('selected SLA plan', 'selected SLA plans', 2));?>
	</p>
	<p class="confirm-action" style="display:none;" id="delete-confirm">
		<font class="text-danger"><?php echo sprintf(__('Are you sure you want to DELETE %s?'),
			_N('selected SLA plan', 'selected SLA plans', 2)); ?></font>
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
