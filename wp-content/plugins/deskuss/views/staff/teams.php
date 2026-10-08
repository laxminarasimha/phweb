<?php
if(!defined('DSKADMININC') || !$thisstaff || !$thisstaff->isAdmin()) die('Access Denied');

$qs = array();
$sortOptions=array(
        'name' => 'name',
        'status' => 'isenabled',
        'members' => 'members_count',
        'lead' => 'lead__lastname',
        'created' => 'created',
        'updated' => 'updated',
        );

$orderWays = array('DESC'=>'DESC', 'ASC'=>'ASC');
$sort = ($_REQUEST['sort'] ?? '' && $sortOptions[strtolower($_REQUEST['sort'] ?? '')]) ? strtolower($_REQUEST['sort'] ?? '') : 'name';

//Sorting options...
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
$count = Team::objects()->count();
$pageNav = new Pagenate($count, $page, PAGE_LIMIT);
$qstr = '&amp;'. Http::build_query($qs);
$qs += array('sort' => $_REQUEST['sort'] ?? '' ?? '', 'order' => $_REQUEST['order'] ?? '' ?? '');
$pageNav->setURL('teams.php', $qs);
$showing = $pageNav->showing().' '._N('team', 'teams', $count);
$qstr .= '&amp;order='.urlencode($order=='DESC' ? 'ASC' : 'DESC');


?>
<div class="panel">
		<div class="panel-heading">
			<div class="panel-title"><?php echo __('Teams');?>
			<i class="help-tip fa fa-question-circle notsticky" href="#teams"></i>
			</div>
		</div>
<div class="panel-body">
	<form action="teams.php" method="POST" name="teams">
	<div class="pull-right">
		<a href="teams.php?a=add" class="btn btn-outline btn-success button action-button"><i class="fa fa-plus-circle"></i> <?php echo __('Add New Team');?>
		</a>
		<div class="btn-group">
			<button class="btn btn-outline action-button dropdown-toggle" data-toggle="dropdown">
				<i class="fa fa-caret-down"></i>
				<span ><i class="fa fa-cog"></i> <?php echo __('More');?></span>
			</button>
			<ul id="actions" class="dropdown-menu dropdown-menu-right">
				<li><a class="confirm" data-name="enable" href="teams.php?a=enable">
					<i class="fa fa-check-circle"></i>
					<?php echo __('Enable'); ?></a></li>
				<li><a class="confirm" data-name="disable" href="teams.php?a=disable">
					<i class="fa fa-ban"></i>
					<?php echo __('Disable'); ?></a></li>
				<li class="danger"><a class="confirm" data-name="delete" href="teams.php?a=delete">
					<i class="fa fa-trash"></i>
					<?php echo __('Delete'); ?></a>
				</li>
			</ul>
		</div>
		<div class="pd-tp-10 pd-bt-10">
			<div class="pull-right">
				<?php
					if ($count){ 
						echo __('Page').':'.$pageNav->getPageLinks();
					}
				?>
			</div>
		</div>
		<br/>
	</div>	

	<?php csrf_token(); ?>
	<input type="hidden" name="do" value="mass_process" >
	<input type="hidden" id="action" name="a" value="" >
	<div class="row">
	<div class="col-md-12">
	<div class="table-light table-responsive">
	<table class="table table-bordered">
		<thead>
			<tr>
				<th style="text-align:center;" width="4%"><input type="checkbox" id="selectToggle" class="ckb"></th>
				<th width="25%"><a <?php echo $name_sort ?? ''; ?> href="teams.php?<?php echo $qstr; ?>&sort=name"><?php echo __('Team Name');?></a></th>
				<th width="8%"><a  <?php echo $status_sort ?? ''; ?> href="teams.php?<?php echo $qstr; ?>&sort=status"><?php echo __('Status');?></a></th>
				<th width="8%"><a  <?php echo $members_sort ?? ''; ?>href="teams.php<?php echo $qstr; ?>&sort=members"><?php echo __('Members');?></a></th>
				<th width="20%"><a  <?php echo $lead_sort ?? ''; ?> href="teams.php?<?php echo $qstr; ?>&sort=lead"><?php echo __('Team Lead');?></a></th>
				<th width="15%"><a  <?php echo $created_sort ?? ''; ?> href="teams.php?<?php echo $qstr; ?>&sort=created"><?php echo __('Created');?></a></th>
				<th width="20%"><a  <?php echo $updated_sort ?? ''; ?> href="teams.php?<?php echo $qstr; ?>&sort=updated"><?php echo __('Last Updated');?></a></th>
			</tr>
		</thead>
		<tbody>
		<?php
			$ids= ($errors && is_array($_POST['ids'])) ? $_POST['ids'] : null;
			if ($count) {
				$teams = Team::objects()
					->annotate(array(
							'members_count'=>SqlAggregate::COUNT('members__staff', true),
					))
					->order_by(sprintf('%s%s',
								strcasecmp($order, 'DESC') ? '' : '-',
								$order_column))
					->limit($pageNav->getLimit())
					->offset($pageNav->getStart());

				foreach ($teams as $team) {
					$id = $team->getId();
					$sel=false;
					if ($ids && in_array($id, $ids))
						$sel=true;
					?>
				<tr id="<?php echo $id; ?>">
					<td align="center">
					  <input type="checkbox" class="ckb" name="ids[]"
					  value="<?php echo $id; ?>"
								<?php echo $sel ? 'checked="checked"' : ''; ?>> </td>
					<td><a href="teams.php?id=<?php echo $id; ?>"><?php echo
					$team->getName(); ?></a> &nbsp;</td>
					<td>&nbsp;<?php echo $team->isActive() ? __('Active') : '<b>'.__('Disabled').'</b>'; ?></td>
					<td style="text-align:right;padding-right:25px">&nbsp;&nbsp;
						<?php if ($team->members_count > 0) { ?>
							<a href="staff.php?tid=<?php echo $id; ?>"><?php
								echo $team->members_count; ?></a>
						<?php } else { ?> 0
						<?php } ?>
						&nbsp;
					</td>
					<td><a href="staff.php?id=<?php
						echo $team->getLeadId(); ?>"><?php echo $team->lead ?: ''; ?>&nbsp;</a></td>
					<td><?php echo Format::date($team->created); ?>&nbsp;</td>
					<td><?php echo Format::datetime($team->updated); ?>&nbsp;</td>
				</tr>
				<?php
				} //end of foreach
			}?>
			</tbody>
			<?php 
			if(!$count){
				echo '<tfoot><tr><td colspan="7">'.__('No teams found!').'</td></tr></tfoot>';
			}
			?>
		</table>
		<br/>
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
</div>
</div>
</div>
	<div style="display:none;" class="dialog panel panel-primary panel-dark" id="confirm-action">
		<div class="panel-heading">
			<div class="panel-title"><?php echo __('Please Confirm');?>
			<a class="close_me pull-right" href=""><i class="fa fa-remove"></i></a>
			</div>
		</div>	
		<div class="panel-body">
			<p class="confirm-action" style="display:none;" id="enable-confirm">
				<?php echo sprintf(__('Are you sure you want to <b>enable</b> %s?'),
					_N('selected team', 'selected teams', 2));?>
			</p>
			<p class="confirm-action" style="display:none;" id="disable-confirm">
				<?php echo sprintf(__('Are you sure you want to <b>disable</b> %s?'),
					_N('selected team', 'selected teams', 2));?>
			</p>
			<p class="confirm-action" style="display:none;" id="delete-confirm">
				<font color="red"><strong><?php echo sprintf(__('Are you sure you want to DELETE %s?'),
					_N('selected team', 'selected teams', 2));?></strong></font>
				<br><br><?php echo __('Deleted data CANNOT be recovered.'); ?>
			</p>
			<div><?php echo __('Please confirm to continue.');?></div>
			<div class="form-group" style="margin-top:20px;">
				<input type="button" value="<?php echo __('Yes, Do it!');?>" class="btn btn-success confirm">
				<input type="button" value="<?php echo __('No, Cancel');?>" class="btn close_me">
			 </div>
		 </div>
		</div>

