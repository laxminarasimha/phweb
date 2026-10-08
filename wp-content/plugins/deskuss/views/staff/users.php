<?php
if(!defined('DSKADMININC') || !$thisstaff) die('Access Denied');

// Ensure cdata
UserForm::ensureDynamicDataView();

$qs = array();
$users = User::objects()
    ->annotate(array('ticket_count'=>SqlAggregate::COUNT('tickets')));

if (!empty($_REQUEST['query'])) {
    $search = $_REQUEST['query'];
    $users->filter(Q::any(array(
        'emails__address__contains' => $search,
        'name__contains' => $search,
        'org__name__contains' => $search,
        'cdata__phone__contains' => $search,
        // TODO: Add search for cdata
    )));
    $qs += array('query' => $_REQUEST['query']);
}

$sortOptions = array('name' => 'name',
                     'email' => 'emails__address',
                     'status' => 'account__status',
                     'create' => 'created',
                     'update' => 'updated');
$orderWays = array('DESC'=>'-','ASC'=>'');
$sort= (!empty($_REQUEST['sort']) && $sortOptions[strtolower($_REQUEST['sort'] ?? '')]) ? strtolower($_REQUEST['sort'] ?? '') : 'name';
//Sorting options...
if ($sort && $sortOptions[$sort])
    $order_column =$sortOptions[$sort];

$order_column = $order_column ?: 'name';

if (!empty($_REQUEST['order']) && $orderWays[strtoupper($_REQUEST['order'])])
    $order = $orderWays[strtoupper($_REQUEST['order'])];

if ($order_column && strpos($order_column,','))
    $order_column = str_replace(','," $order,",$order_column);

$x=$sort.'_sort';
$$x=' class="'.(($order ?? '') == '' ? 'asc' : 'desc').'" ';

$total = $users->count();
$page=(!empty($_GET['p']) && is_numeric($_GET['p']))?$_GET['p']:1;
$pageNav=new Pagenate($total,$page,PAGE_LIMIT);
$pageNav->paginate($users);

$qstr = '&amp;'. Http::build_query($qs);
$qs += array('sort' => $_REQUEST['sort'] ?? '', 'order' => $_REQUEST['order'] ?? '');
$pageNav->setURL('users.php', $qs);
$qstr.='order='.(($order ?? '') =='-' ? 'ASC' : 'DESC');

//echo $query;
$_SESSION[':Q:users'] = $users;

$users->values('id', 'name', 'default_email__address', 'account__id',
    'account__status', 'created', 'updated');
if (!isset($order)) $order = '';
if (!isset($order_column)) $order_column = '';
$users->order_by($order . $order_column);

$showing = !empty($search) ? __('Search Results').': ' : '';
if($users->exists(true))
    $showing .= $pageNav->showing();
else
    $showing .= __('No users found!');

//csrf_token();

?>

<div class="panel">
	<div class="panel-heading">
		<div class="panel-title table-caption"><?php echo __('Users'); ?></div>
	</div>

	<div class="panel-body">
		<?php
		if(!empty($_REQUEST['added'])){
			?>
			<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert">×</button><i class="fa fa-check"></i>&nbsp;&nbsp;<?php echo __('User Added Successfully !');?></div>
			<?php
		}
		?>
		<form action="users.php" method="GET" name="filter" class="form-inline">
			<?php csrf_token(); ?>
			<input type="hidden" name="a" value="search">
			<div class="form-group">
			<input type="text" id="basic-user-search" name="query" class="form-control" value="<?php echo Format::htmlchars($_REQUEST['query'] ?? ''); ?>" placeholder="<?php echo __('Search Users');?>" autocomplete="off" autocorrect="off" autocapitalize="off">
			</div>
			<button type="submit" class="btn btn-primary"><?php echo __('Filter');?></button>
			<div class="pull-right">
			<?php if ($thisstaff->hasPerm(User::PERM_CREATE)) { ?>
			<a href="#users/add" class="popup-dialog">
				<button type="button" class="btn btn-success btn-outline"><i class="fa fa-user-plus"></i>&nbsp;<?php echo __('Add User'); ?></button>
			</a>
			<a href="#users/import" class="popup-dialog">
				<button type="button" class="btn btn-primary btn-outline"><i class="fa fa-upload"></i>&nbsp;<?php echo __('Import'); ?></button>
			</a>
			<a href="users.php?a=export&qh=<?php echo $qhash ?? '';?>">
				<button type="button" class="btn btn-primary btn-outline"><i class="fa fa-download"></i>&nbsp;<?php echo __('Export'); ?></button>
			</a>
			<?php } ?>
				<div class="btn-group">
				  <button type="button" class="btn btn-outline dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i>&nbsp;<?php echo __('More');?></button>
				  <ul class="dropdown-menu dropdown-menu-right">
					<?php if ($thisstaff->hasPerm(User::PERM_EDIT)) { ?>
					<li><a href="#add-to-org" class="users-action">
						<i class="fa fa-university"></i>&nbsp;
						<?php echo __('Add to Organization'); ?></a></li>
					<?php
					}
					if ('disabled' != $cfg->getClientRegistrationMode()) { ?>
					<li><a href="#reset" class="users-action">
						<i class="fa fa-envelope"></i>&nbsp;
						<?php echo __('Send Password Reset Email'); ?></a></li>
					<?php if ($thisstaff->hasPerm(User::PERM_MANAGE)) { ?>
					<li><a href="#register" class="users-action">
						<i class="fa fa-id-card-o"></i>&nbsp;
						<?php echo __('Register'); ?></a></li>
					<li><a href="#lock" class="users-action">
						<i class="fa fa-lock"></i>&nbsp;
						<?php echo __('Lock'); ?></a></li>
					<li><a href="#unlock" class="users-action">
						<i class="fa fa-unlock"></i>&nbsp;
						<?php echo __('Unlock'); ?></a></li>
					<?php }
					if ($thisstaff->hasPerm(User::PERM_DELETE)) { ?>
					<li class="danger"><a href="#delete" class="users-action">
						<i class="fa fa-trash"></i>&nbsp;
						<?php echo __('Delete'); ?></a></li>
					<?php }
					} # end of registration-enabled? ?>
				  </ul>
				</div>
			</div>
			<div class="pd-bt-10 pd-tp-10">
				<?php
					if ($total) {
				?>
				<div class="pull-right">
					<?php
						echo sprintf(__('Page').': %s',$pageNav->getPageLinks());
						}
					?>
				</div>
			</div>	
		</form>
	
	<form id="users-list" action="users.php" method="POST" name="staff" >
	
	<br />
	<?php csrf_token(); ?>
	<input type="hidden" name="do" value="mass_process" >
	<input type="hidden" id="action" name="a" value="" >
	<input type="hidden" id="selected-count" name="count" value="" >
	<input type="hidden" id="org_id" name="org_id" value="" >
	
	<div class="row">
	  <div class="col-md-12">
		<div class="table-light table-responsive">
		  <table class="table table-bordered" border="0" cellspacing="1" cellpadding="0" width="940">
			<thead>
				<tr>
					<th nowrap width="4%" style="text-align:center;"><input type="checkbox" id="selectToggle" class="ckb" /></th>
					<th><a <?php echo $name_sort ?? ''; ?> href="users.php?<?php
						echo $qstr; ?>&sort=name"><?php echo __('Name'); ?></a></th>
					<th><a  <?php echo $status_sort ?? ''; ?> href="users.php?<?php
						echo $qstr; ?>&sort=status"><?php echo __('Status'); ?></a></th>
					<th><a <?php echo $create_sort ?? ''; ?> href="users.php?<?php
						echo $qstr; ?>&sort=create"><?php echo __('Created'); ?></a></th>
					<th><a <?php echo $update_sort ?? ''; ?> href="users.php?<?php
						echo $qstr; ?>&sort=update"><?php echo __('Updated'); ?></a></th>
				</tr>
			</thead>
			<tbody>
			<?php
				$ids=($errors && is_array($_POST['ids']))?$_POST['ids']:null;
				foreach ($users as $U) {
						// Default to email address mailbox if no name specified
						if (!$U['name'])
							list($name) = explode('@', $U['default_email__address']);
						else
							$name = new UsersName($U['name']);

						// Account status
						if ($U['account__id'])
							$status = new UserAccountStatus($U['account__status']);
						else
							$status = __('Guest');

						$sel=false;
						if($ids && in_array($U['id'], $ids))
							$sel=true;
						?>
					   <tr id="<?php echo $U['id']; ?>">
						<td nowrap align="center">
							<input type="checkbox" value="<?php echo $U['id']; ?>" class="ckb mass nowarn"/>
						</td>
						<td>&nbsp;
							<a class="preview"
								href="users.php?id=<?php echo $U['id']; ?>"
								data-preview="#users/<?php echo $U['id']; ?>/preview"><?php
								echo Format::htmlchars($name); ?></a>
							&nbsp;
							<?php
							if ($U['ticket_count'])
								 echo sprintf('<span class="label label-info">%d '.__('Ticket(s)').'</span>', $U['ticket_count']);
							?>
						</td>
						<td><?php echo $status; ?></td>
						<td><?php echo Format::datetime($U['created']); ?></td>
						<td><?php echo Format::datetime($U['updated']); ?>&nbsp;</td>
					   </tr>
			<?php   } //end of foreach. ?>
			</tbody>
		 
	   </table>
		</div>
		</div>
  </div>
	<?php
				if ($total) {
			?>	
			<div class="pull-right">
				<span class="faded"><?php echo $pageNav->showing(); ?>&nbsp;&mdash;&nbsp;</span>
				<?php
					echo sprintf(__('Page').': %s',$pageNav->getPageLinks());
					}
				else
				{
					echo '<i>';
					echo __('Query returned 0 results.');
					echo '</i>';
				}
				?>
		  </div>
</form>
</div>
</div>

<script type="text/javascript">

$(function() {
    $('input#basic-user-search').typeahead({
        source: function (typeahead, query) {
            $.ajax({
                url: "ajax.php/users/local?q="+query,
                dataType: 'json',
                success: function (data) {
                    typeahead.process(data);
                }
            });
        },
        onselect: function (obj) {
            window.location.href = 'users.php?id='+obj.id;
        },
        property: "/bin/true"
    });

    $(document).on('click', 'a.popup-dialog', function(e) {
        e.preventDefault();
        $.userLookup('ajax.php/' + $(this).attr('href').substr(1), function (user) {
            var url = window.location.href;
            if (user && user.id)
                url = 'users.php?added='+user.id;
			window.location = url;
            return false;
         });

        return false;
    });
    var goBaby = function(action, confirmed) {
        var ids = [],
            $form = $('form#users-list');
        $(':checkbox.mass:checked', $form).each(function() {
            ids.push($(this).val());
        });
        if (ids.length) {
          var submit = function(data) {
            $form.find('#action').val(action);
            $.each(ids, function() { $form.append($('<input type="hidden" name="ids[]">').val(this)); });
            if (data)
              $.each(data, function() { $form.append($('<input type="hidden">').attr('name', this.name).val(this.value)); });
            $form.find('#selected-count').val(ids.length);
            $form.submit();
          };
          var options = {};
          if (action === 'delete') {
              options['deletetickets']
                =  __('Also delete all associated tickets and attachments');
          }
          else if (action === 'add-to-org') {
            $.dialog('ajax.php/orgs/lookup/form', 201, function(xhr, json) {
              var $form = $('form#users-list');
              try {
                  var json = $.parseJSON(json),
                      org_id = $form.find('#org_id');
                  if (json.id) {
                      org_id.val(json.id);
                      goBaby('setorg', true);
                  }
              }
              catch (e) { }
            });
            return;
          }
          if (!confirmed)
              $.confirm(__('You sure?'), undefined, options).then(submit);
          else
              submit();
        }
        else {
            $.sysAlert(__('Oops'),
                __('You need to select at least one item'));
        }
    };
    $(document).on('click', 'a.users-action', function(e) {
        e.preventDefault();
        goBaby($(this).attr('href').substr(1));
        return false;
    });

    // Remove CSRF Token From GET Request
    document.querySelector("form[action='users.php']").onsubmit = function() {
        document.getElementsByName("__CSRFToken__")[0].remove();
    };
});
</script>

