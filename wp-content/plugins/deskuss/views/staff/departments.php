<?php
if (!defined('DSKADMININC') || !$thisstaff->isAdmin())
    die('Access Denied');

$qs = array();
$sortOptions=array(
    'name' => 'name',
    'type' => 'ispublic',
    'members'=> 'members_count',
    'email'=> 'email__name',
    'manager'=>'manager__lastname'
    );

$orderWays = array('DESC'=>'DESC', 'ASC'=>'ASC');
$sort = ($_REQUEST['sort'] ?? '' && $sortOptions[strtolower($_REQUEST['sort'] ?? '')]) ? strtolower($_REQUEST['sort'] ?? '') : 'name';
if ($sort && $sortOptions[$sort]) {
    $order_column = $sortOptions[$sort];
}

$order_column = $order_column ? $order_column : 'name';

if (!empty($_REQUEST['order'] ?? '') && isset($orderWays[strtoupper($_REQUEST['order'] ?? '')])) {
    $order = $orderWays[strtoupper($_REQUEST['order'] ?? '')];
} else {
    $order = 'ASC';
}

if ($order_column && strpos($order_column,',')) {
    $order_column=str_replace(','," $order,",$order_column);
}
$x=$sort.'_sort';
$$x=' class="'.strtolower($order).'" ';
$page = (!empty($_GET['p']) && is_numeric($_GET['p'])) ? $_GET['p'] : 1;
$count = Dept::objects()->count();
$pageNav = new Pagenate($count, $page, PAGE_LIMIT);
$qstr = '&amp;'. Http::build_query($qs);
$qstr .= '&amp;order='.($order=='DESC' ? 'ASC' : 'DESC');
$qs += array('sort' => $_REQUEST['sort'] ?? '' ?? '', 'order' => $_REQUEST['order'] ?? '' ?? '');
$pageNav->setURL('departments.php', $qs);
$showing = $pageNav->showing().' '._N('department', 'departments', $count);
?>
<div class="panel">
<div class="panel-heading">
	<div class="panel-title table-caption"><?php echo __('Departments');?></div>
</div>
<div class="panel-body">
<form action="departments.php" method="POST" name="depts">
<div class="pull-right">
	<a href="departments.php?a=add" class="btn btn-success btn-outline"><i class="fa fa-plus-circle"></i> <?php echo __('Add New Department'); ?></a>
	<div class="btn-group" id="action-dropdown-more">
	<button type="button" class="btn btn-outline dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i>&nbsp;<?php echo __('More');?></button>
	<ul class="dropdown-menu dropdown-menu-right" id="actions">
		<li>
			<a class="confirm" data-name="delete" href="departments.php?a=delete">
				<i class="fa fa-trash"></i>
				<?php echo __('Delete'); ?>
			</a>
		</li>
	</ul>
	</div>
</div>

<br/>
<br/>
<?php csrf_token(); ?>
<input type="hidden" name="do" value="mass_process">
<input type="hidden" id="action" name="a" value="">
<div class="form-group pd-bt-10">
	<?php
		if ($count){
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
<table class="table table-bordered list" border="0" cellspacing="1" cellpadding="0" width="940">
	<thead>
	<tr>
		<th width="4%" class="text-center" ><?php if($count){ ?><input type="checkbox" id="selectToggle" class="ckb"><?php }else{ echo __(''); }?></th>
		<th><a <?php echo $name_sort ?? ''; ?> href="departments.php?<?php echo $qstr; ?>&sort=name"><?php echo __('Name');?></a></th>
		<th><a  <?php echo $type_sort ?? ''; ?> href="departments.php?<?php echo $qstr; ?>&sort=type"><?php echo __('Type');?></a></th>
		<th width="8%"><a  <?php echo $users_sort ?? ''; ?>href="departments.php?<?php echo $qstr; ?>&sort=users"><?php echo __('Agents');?></a></th>
		<th width="30%"><a  <?php echo $email_sort ?? ''; ?> href="departments.php?<?php echo $qstr; ?>&sort=email"><?php echo __('Email Address');?></a></th>
		<th width="22%"><a  <?php echo $manager_sort ?? ''; ?> href="departments.php<?php echo $qstr; ?>&sort=manager"><?php echo __('Manager');?></a></th>
	</tr>
	</thead>
	<tbody>
	<?php
	$ids= ($errors && is_array($_POST['ids'])) ? $_POST['ids'] : null;
	if ($count) {
		$depts = Dept::objects()
			->annotate(array(
					'members_count' => SqlAggregate::COUNT('members', true),
			))
			->order_by(sprintf('%s%s',
						strcasecmp($order, 'DESC') ? '' : '-',
						$order_column))
			->limit($pageNav->getLimit())
			->offset($pageNav->getStart());
		$defaultId=$cfg->getDefaultDeptId();
		$defaultEmailId = $cfg->getDefaultEmailId();
		$defaultEmailAddress = (string) $cfg->getDefaultEmail();
		foreach ($depts as $dept) {
			$id = $dept->getId();
			$sel=false;
			if($ids && in_array($dept->getId(), $ids))
				$sel=true;

			if ($dept->email) {
				$email = (string) $dept->email;
				$emailId = $dept->email->getId();
			} else {
				$emailId = $defaultEmailId;
				$email = $defaultEmailAddress;
			}

			$default= ($defaultId == $dept->getId()) ?' <small>'.__('(Default)').'</small>' : '';
			?>
			<tr id="<?php echo esc_attr($id); ?>">
			<td align="center">
			  <input type="checkbox" class="ckb" name="ids[]"
			  value="<?php echo esc_attr($id); ?>"
			  <?php echo $sel? 'checked="checked"' : ''; ?>
			  <?php echo $default? 'disabled="disabled"' : ''; ?> >
			</td>
			<td><a href="departments.php?id=<?php echo esc_attr($id); ?>"><?php
			echo esc_html(Dept::getNameById($id)); ?></a>&nbsp;<?php echo $default; ?></td>
			<td><?php echo $dept->isPublic() ? __('Public') :'<b>'.__('Private').'</b>'; ?></td>
			<td>&nbsp;&nbsp;
				<b>
				<?php if ($dept->members_count) { ?>
					<a href="staff.php?did=<?php echo esc_attr($id); ?>"><?php echo esc_html($dept->members_count); ?></a>
				<?php }else{ ?> 0
				<?php } ?>
				</b>
			</td>
		<td><span class="ltr"><a href="emails.php?id=<?php echo esc_attr($emailId); ?>"><?php
			echo Format::htmlchars($email); ?></a></span></td>
		<td><a href="staff.php?id=<?php echo esc_attr($dept->manager_id); ?>"><?php
			echo $dept->manager_id ? esc_html($dept->manager) : ''; ?>&nbsp;</a></td>
			</tr>
			<?php
		} //end of foreach.
	} ?>
	</tbody>
	<?php 
	if(!$count){
		echo '<tfoot><tr><td colspan="6">'.__('No departments found!').'</td></tr></tfoot>';
	}
	?>
</table>
</div>
</div>
</div>
</form>
	<?php
		if ($count){
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

<div style="display:none;border:none" class="dialog panel panel-primary panel-dark" id="confirm-action">
<div class="panel-heading">
    <div class="panel-title"><?php echo __('Please Confirm'); ?>
		<a class="close_me pull-right" href=""><i class="fa fa-close"></i></a>
	</div>
</div>
<div class="panel-body">
    <p class="confirm-action" style="display:none;" id="make_public-confirm">
        <?php echo sprintf(__('Are you sure you want to make %s <b>public</b>?'),
		_N('selected department', 'selected departments', 2));?>
    </p>
    <p class="confirm-action" style="display:none;" id="make_private-confirm">
        <?php echo sprintf(__('Are you sure you want to make %s <b>private</b> (internal)?'),
		_N('selected department', 'selected departments', 2));?>
    </p>
    <p class="confirm-action" style="display:none;" id="delete-confirm">
        <font class="text-danger"><?php echo sprintf(__('Are you sure you want to DELETE %s?'),
		_N('selected department', 'selected departments', 2));?></font>
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
