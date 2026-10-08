<?php
$qs = array();
$select = 'SELECT user.*, email.address as email, account.status as status, account.id as account_id ';

$from = 'FROM '.USER_TABLE.' user '
      . 'LEFT JOIN '.USER_EMAIL_TABLE.' email ON (user.id = email.user_id) '
      . 'LEFT JOIN '.USER_ACCOUNT_TABLE.' account ON (user.id = account.user_id) ';

$where = ' WHERE user.org_id='.db_input($org->getId());

$sortOptions = array('name' => 'user.name',
                     'email' => 'email.address',
                     'create' => 'user.created',
                     'update' => 'user.updated',
                     'status' => 'account.status');
$orderWays = array('DESC'=>'DESC','ASC'=>'ASC');
$sort= ($_REQUEST['sort'] && $sortOptions[strtolower($_REQUEST['sort'])]) ? strtolower($_REQUEST['sort']) : 'name';
//Sorting options...
if ($sort && $sortOptions[$sort])
    $order_column =$sortOptions[$sort];

$order_column = $order_column ?: 'user.name';

if ($_REQUEST['order'] && $orderWays[strtoupper($_REQUEST['order'])])
    $order = $orderWays[strtoupper($_REQUEST['order'])];

$order=$order ?: 'ASC';
if ($order_column && strpos($order_column,','))
    $order_column = str_replace(','," $order,",$order_column);

$x=$sort.'_sort';
$$x=' class="'.strtolower($order).'" ';
$order_by="$order_column $order ";

$total=db_count('SELECT count(DISTINCT user.id) '.$from.' '.$where);
$page=($_GET['p'] && is_numeric($_GET['p']))?$_GET['p']:1;
$pageNav=new Pagenate($total,$page,PAGE_LIMIT);
$qstr = '&amp;'. Http::build_query($qs);
$qs += array('sort' => $_REQUEST['sort'], 'order' => $_REQUEST['order']);

$pageNav->setURL('users.php', $qs);
//Ok..lets roll...create the actual query
$qstr .= '&amp;order='.($order=='DESC' ? 'ASC' : 'DESC');

$select .= ', count(DISTINCT ticket.ticket_id) as tickets ';

$from .= ' LEFT JOIN '.TICKET_TABLE.' ticket ON (ticket.user_id = user.id) ';


$query="$select $from $where GROUP BY user.id ORDER BY $order_by LIMIT ".$pageNav->getStart().",".$pageNav->getLimit();
//echo $query;

$showing = $search ? __('Search Results').': ' : '';
$res = db_query($query);
if($res && ($num=db_num_rows($res)))
    $showing .= $pageNav->showing();
else
    $showing .= __("This organization doesn't have any users yet");

?>
	<form action="orgs.php?id=<?php echo $org->getId(); ?>" method="POST" name="users" >

		<div style="margin-top:5px;" class="pull-left">
			<div><?php echo $showing; ?></div>
		</div>
	<?php if ($thisstaff->hasPerm(User::PERM_EDIT)) { ?>
		<div class="pull-right flush-right" style="margin-bottom:10px;">
			<a href="#orgs/<?php echo $org->getId(); ?>/add-user" class="btn btn-success btn-outline button action-button add-user"
				><i class="fa fa-plus"></i> <?php echo __('Add User'); ?></a>
			<a href="#orgs/<?php echo $org->getId(); ?>/import-users" class="button btn action-button add-user"><i class="fa fa-cloud-upload"></i>
			<?php echo __('Import'); ?></a>
			<button id="actions" class="btn button" type="submit" name="remove-users">
				<i class="fa fa-trash"></i> <?php echo __('Remove'); ?>
			</button>
		</div>
	<?php } ?>
	<?php
	if ($num) { ?>
	 <?php csrf_token(); ?>
	 <input type="hidden" name="do" value="mass_process" >
	 <input type="hidden" id="id" name="id" value="<?php echo $org->getId(); ?>" >
	 <input type="hidden" id="action" name="a" value="" >
	 <div class="col-md-12 row">
	 <div class="table-light table-responsive">
		 <table class="table table-bordered">
			<thead>
				<tr>
					<th style="text-align:center;" width="4%"><input type="checkbox" id="selectToggle" class="ckb"></th>
					<th width="30%"><?php echo __('Name'); ?></th>
					<th width="33%"><?php echo __('Email'); ?></th>
					<th width="18%"><?php echo __('Status'); ?></th>
					<th width="15%"><?php echo __('Created'); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php
				if($res && db_num_rows($res)):
					$ids=($errors && is_array($_POST['ids']))?$_POST['ids']:null;
					while ($row = db_fetch_array($res)) {

						$name = new UsersName($row['name']);
						if (!$row['account_id'])
							$status = __('Guest');
						else
							$status = new UserAccountStatus($row['status']);
						$sel=false;
						if($ids && in_array($row['id'], $ids))
							$sel=true;
						?>
					   <tr id="<?php echo $row['id']; ?>">
							<td align="center">
							  <input type="checkbox" class="ckb" name="ids[]"
								value="<?php echo $row['id']; ?>" <?php echo $sel?'checked="checked"':''; ?> >
							</td>
							<td>&nbsp;
								<a class="preview"
									href="users.php?id=<?php echo $row['id']; ?>"
									data-preview="#users/<?php
									echo $row['id']; ?>/preview" ><?php
									echo Format::htmlchars($name); ?></a>
								&nbsp;
								<?php
								if ($row['tickets'])
									 echo sprintf('<i class="fa fa-file-text-o fa-fw"></i>
										 <small>(%d)</small>', $row['tickets']);
								?>
							</td>
							<td><?php echo Format::htmlchars($row['email']); ?></td>
							<td><?php echo $status; ?></td>
							<td><?php echo Format::date($row['created']); ?></td>
					   </tr>
					<?php
					} //end of while.
				endif; ?>
			</tbody>
			<?php 
			if(!$res || !$num){
				echo '<tfoot><tr><td colspan="5">'.__('No users found!').'</td></tr></tfoot>';
			}
			?>
			
		</table>

			<?php
			if ($res && $num) { //Show options..
				echo '<div>&nbsp;'.__('Page').':'.$pageNav->getPageLinks().'&nbsp;</div>';

				?>

			<?php	}	?>
			</div>
		</div>
	</form>
	<?php  } ?>

	<div style="display:none;" class="dialog panel panel-primary panel-dark" id="confirm-action">
		<div class="panel-heading">
			<div class="drag-handle panel-title">
				<?php echo __('Please Confirm'); ?>
			<a class="close_me" href=""><i class="fa fa-remove"></i></a>
			</div>
		</div>
		<div class="panel-body">
			<p class="confirm-action" style="display:none;" id="remove-users-confirm">
				<?php echo sprintf(__(
				'Are you sure you want to <b>REMOVE</b> %1$s from <strong>%2$s</strong>?'),
				_N('selected user', 'selected users', 2),
				$org->getName()); ?>
			</p>
			<div><?php echo __('Please confirm to continue.'); ?></div>
			<div class="form-group" style="margin-top:20px;">
				<input type="button" value="<?php echo __('Yes, Do it!'); ?>" class="btn btn-success confirm">
				<input type="button" value="<?php echo __('No, Cancel'); ?>" class="close_me btn">
			 </div>
		 </div>
	</div>

<script type="text/javascript">
	$(function() {
		$(document).on('click', 'a.add-user', function(e) {
			e.preventDefault();
			$.userLookup('ajax.php/' + $(this).attr('href').substr(1), function (user) {
				if (user && user.id)
					window.location.href = 'orgs.php?id=<?php echo $org->getId(); ?>'
				else
				  $.pjax({url: window.location.href, container: '#pjax-container'})
			});
			return false;
		 });
	});
</script>

