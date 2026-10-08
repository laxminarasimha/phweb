<?php
if(!defined('DSKADMININC') || !$thisstaff->isAdmin()) die('Access Denied');

$qs = array();
$sortOptions = array(
        'email' => 'email',
        'dept' => 'dept__name',
        'priority' => 'priority__priority_desc',
        'created' => 'created',
        'updated' => 'updated');


$orderWays = array('DESC'=>'DESC', 'ASC'=>'ASC');
$sort = ($_REQUEST['sort'] ?? '' && $sortOptions[strtolower($_REQUEST['sort'] ?? '')]) ?  strtolower($_REQUEST['sort'] ?? '') : 'email';
if ($sort && $sortOptions[$sort]) {
        $order_column = $sortOptions[$sort];
}

$order_column = $order_column ? $order_column : 'email';

if ($_REQUEST['order'] ?? '' && isset($orderWays[strtoupper($_REQUEST['order'] ?? '')]))
{
        $order = $orderWays[strtoupper($_REQUEST['order'] ?? '')];
} else {
        $order = 'ASC';
}

$x=$sort.'_sort';
$$x=' class="'.strtolower($order).'" ';
$page = (!empty($_GET['p']) && is_numeric($_GET['p'])) ? $_GET['p'] : 1;
$count = Email::objects()->count();
$pageNav = new Pagenate($count, $page, PAGE_LIMIT);
$qs += array('sort' => $_REQUEST['sort'] ?? '', 'order' => $_REQUEST['order'] ?? '');
$pageNav->setURL('emails.php', $qs);
$showing = $pageNav->showing().' '._N('email', 'emails', $count);
$qstr = '&amp;order='.($order=='DESC' ? 'ASC' : 'DESC');

$def_dept_id = $cfg->getDefaultDeptId();
$def_dept_name = $cfg->getDefaultDept()->getName();
$def_priority = $cfg->getDefaultPriority()->getDesc();
?>
<div class="panel">
<form action="emails.php" method="POST" name="emails">
   
	<div class="panel-heading">
		<div class="panel-title table-caption"><?php echo __('Email Addresses');?></div>
	</div>
	
	<div class="panel-body">
	<div class="pull-right form-group">
		<a href="emails.php?a=add" class="btn btn-success btn-outline"><i class="fa fa-plus-circle"></i> <?php echo __('Add New Email');?></a>
		<div class="btn-group">
			<button type="button" class="btn btn-outline dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i>&nbsp;<?php echo __('More');?></button>
			<ul class="dropdown-menu dropdown-menu-right" id="actions">
				<li class="danger">
					<a class="confirm" data-name="delete" href="emails.php?a=delete">
						<i class="fa fa-trash"></i>
						<?php echo __( 'Delete'); ?>
					</a>
				</li>
			</ul>
		</div>
		<div class="form-group pd-tp-10">
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
	</div> 
	<br>
	<br>
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
			<th width="38%"><a <?php echo $email_sort ?? ""; ?> href="emails.php?<?php echo $qstr; ?>&sort=email"><?php echo __('Email');?></a></th>
			<th width="8%"><a  <?php echo $priority_sort ?? ""; ?> href="emails.php?<?php echo $qstr; ?>&sort=priority"><?php echo __('Priority');?></a></th>
			<th width="15%"><a  <?php echo $dept_sort ?? ""; ?> href="emails.php?<?php echo $qstr; ?>&sort=dept"><?php echo __('Department');?></a></th>
			<th width="15%" nowrap><a  <?php echo $created_sort ?? ""; ?>href="emails.php?<?php echo $qstr; ?>&sort=created"><?php echo __('Created');?></a></th>
			<th width="20%" nowrap><a  <?php echo $updated_sort ?? ""; ?>href="emails.php?<?php echo $qstr; ?>&sort=updated"><?php echo __('Last Updated');?></a></th>
		</tr>
		</thead>
		<tbody>
		<?php
		
		$ids = ($errors && is_array($_POST['ids'])) ? $_POST['ids'] : null;
		if ($count):
			$defaultId=$cfg->getDefaultEmailId();
			$emails = Email::objects()
				->order_by(sprintf('%s%s',
							strcasecmp($order, 'DESC') ? '' : '-',
							$order_column))
				->limit($pageNav->getLimit())
				->offset($pageNav->getStart());

			foreach ($emails as $email) {
				$id = $email->getId();
				$sel=false;
				if ($ids && in_array($id, $ids))
					$sel=true;
				$default=($id==$defaultId);
				?>
				
         <tr id="<?php echo esc_attr($id); ?>">
			<td align="center">
			  <input type="checkbox" class="ckb" name="ids[]"
				value="<?php echo esc_attr($id); ?>"
				<?php echo $sel ? 'checked="checked" ' : ''; ?>
				<?php echo $default?'disabled="disabled" ':''; ?>>
			</td>
		<td><span class="ltr"><a href="emails.php?id=<?php echo esc_attr($id); ?>"><?php
			echo Format::htmlchars((string) $email); ?></a></span>
			<?php echo ($default) ?' <small>'.__('(Default)').'</small>' : ''; ?>
			</td>
			<td><?php echo esc_html($email->priority ?: $def_priority); ?></td>
			<td><a href="departments.php?id=<?php $email->dept_id ?: $def_dept_id; ?>"><?php
			echo esc_html($email->dept ?: $def_dept_name); ?></a></td>
			<td>&nbsp;<?php echo Format::date($email->created); ?></td>
			<td>&nbsp;<?php echo Format::datetime($email->updated); ?></td>
		</tr>
		<?php
		} //end of while.
		endif; ?>
		</tbody>
		<?php 
		if(!$count){
			echo '<tfoot><tr><td colspan="6">'.__('No emails found!').'</td></tr></tfoot>';
		}
		?>
		</table>
		</div>
		</div>
	</div>
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
</form>
</div>

<div style="display:none;border:none" class="dialog panel panel-primary panel-dark" id="confirm-action">
	<div class="panel-heading">
		<div class="panel-title table-caption"><?php echo __('Please Confirm');?>
			<a class="close_me pull-right" href=""><i class="fa fa-close"></i></a>
		</div>
	</div>
	<div class="panel-body">
		<p class="confirm-action" style="display:none;" id="delete-confirm">
			<font class="text-danger"><?php echo sprintf(__('Are you sure you want to DELETE %s?'),
				_N('selected email', 'selected emails', 2)) ;?></font>
			<br><br><?php echo __('Deleted data CANNOT be recovered.');?>
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
