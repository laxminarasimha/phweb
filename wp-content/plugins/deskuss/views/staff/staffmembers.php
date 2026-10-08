<?php
if (!defined('DSKADMININC') || !$thisstaff || !$thisstaff->isAdmin())
    die('Access Denied');

$qstr='';
$qs = array();
$sortOptions = array(
        'name' => array('firstname', 'lastname'),
        'username' => 'username',
        'status' => 'isactive',
        'dept' => 'dept__name',
        'created' => 'created',
        'login' => 'lastlogin',
        'role' => 'isadmin'
        );

$orderWays = array('DESC'=>'DESC', 'ASC'=>'ASC');
$sort = (!empty($_REQUEST['sort']) && isset($sortOptions[strtolower($_REQUEST['sort'])])) ? strtolower($_REQUEST['sort']) : 'name';

global $wpdb;
$results = array();
if ($wpdb) {
    $prefix = $wpdb->prefix;
    $custom_query = "SELECT u.*
        FROM {$prefix}users u
        JOIN {$prefix}usermeta um ON u.ID = um.user_id
        WHERE um.meta_key = '{$prefix}capabilities'
        AND (um.meta_value LIKE '%deskuss_staff%' OR um.meta_value LIKE '%administrator%')
        AND u.user_login NOT IN (SELECT username FROM {$prefix}dk_staff)";
    $results = $wpdb->get_results($custom_query);
}

switch ($cfg->getAgentNameFormat()) {
case 'last':
case 'lastfirst':
case 'legal':
    $sortOptions['name'] = array('lastname', 'firstname');
    break;
// Otherwise leave unchanged
}

if ($sort && $sortOptions[$sort]) {
    $order_column = $sortOptions[$sort];
}

$order_column = $order_column ?: array('firstname', 'lastname');

if (!empty($_REQUEST['order']) && isset($orderWays[strtoupper($_REQUEST['order'])])) {
    $order = $orderWays[strtoupper($_REQUEST['order'])];
} else {
    $order = 'ASC';
}

$x=$sort.'_sort';
$$x=' class="'.strtolower($order).'" ';

// Initialise all sort_* variables to empty string so headers don't trigger notices
// when the user is not currently sorting by that column.
$sortVars = array('name_sort', 'username_sort', 'status_sort', 'dept_sort', 'created_sort', 'login_sort', 'role_sort');
foreach($sortVars as $sv){
    if(!isset($$sv)){
        $$sv = '';
    }
}

//Filers
$filters = array();
if (!empty($_REQUEST['did']) && is_numeric($_REQUEST['did'])) {
    $filters += array('dept_id' => $_REQUEST['did']);
    $qs += array('did' => $_REQUEST['did']);
}

if (!empty($_REQUEST['tid']) && is_numeric($_REQUEST['tid'])) {
    $filters += array('teams__team_id' => $_REQUEST['tid']);
    $qs += array('tid' => $_REQUEST['tid']);
}

//agents objects
$agents = Staff::objects()
    ->annotate(array(
        'teams_count' => SqlAggregate::COUNT('teams', true),
    ))
    ->select_related('dept', 'group');

$order = strcasecmp($order, 'DESC') ? '' : '-';
foreach ((array) $order_column as $C) {
    $agents->order_by($order.$C);
}

if ($filters)
    $agents->filter($filters);

// paginate
$page = (!empty($_GET['p']) && is_numeric($_GET['p'])) ? (int)$_GET['p'] : 1;
$count = $agents->count();
$pageNav = new Pagenate($count, $page, PAGE_LIMIT);
$qs += array('sort' => isset($_REQUEST['sort']) ? $_REQUEST['sort'] : '', 'order' => isset($_REQUEST['order']) ? $_REQUEST['order'] : '');
$pageNav->setURL('staff.php', $qs);
$showing = $pageNav->showing().' '._N('agent', 'agents', $count);
$qstr = '&amp;'. Http::build_query($qs);
$qstr .= '&amp;order='.($order=='-' ? 'ASC' : 'DESC');

// Count super admins (respects any active filters except pagination)
$admin_count = Staff::objects()->filter(array('isadmin' => 1) + (($filters) ? $filters : array()))->count();

// add limits.
$agents->limit($pageNav->getLimit())->offset($pageNav->getStart());
?>
<div class="panel">
<div class="panel-heading">
	<div class="panel-title table-caption"><?php echo __('Agents');?></div>
</div>
<div class="panel-body">
<div id="basic_search">
    <div style="min-height:25px;">
        <div class="form-inline">
            <form action="staff.php" method="GET" name="filter">
                <input type="hidden" name="a" value="filter">
                <select class="form-control" name="did" id="did">
                    <option value="0">&mdash;
                        <?php echo __( 'All Departments');?> &mdash;</option>
                    <?php if (($depts=Dept::getDepartments())) { foreach ($depts as $id=> $name) { $sel=(!empty($_REQUEST['did']) && $_REQUEST['did']==$id)?'selected="selected"':''; echo sprintf('
                    <option value="%d" %s>%s</option>',$id,$sel,$name); } } ?>
                </select>
                <select class="form-control" name="tid" id="tid">
                    <option value="0">&mdash;
                        <?php echo __( 'All Teams');?> &mdash;</option>
                    <?php if (($teams=Team::getTeams())) { foreach ($teams as $id=> $name) { $sel=(!empty($_REQUEST['tid']) && $_REQUEST['tid']==$id)?'selected="selected"':''; echo sprintf('
                    <option value="%d" %s>%s</option>',$id,$sel,$name); } } ?>
                </select>
                <input type="submit" name="submit" class="button btn btn-success muted" value="<?php echo __('Apply');?>" />
				<div class="pull-right">
					<a class="btn btn-outline btn-success button action-button" href="staff.php?a=add">
						<i class="fa fa-plus-circle"></i>
						<?php echo __( 'Add New Agent'); ?>
					</a>
					<div class="btn-group">
						<button class="btn btn-outline action-button dropdown-toggle" data-toggle="dropdown">
						<i class="fa fa-caret-down pull-right"></i>
						<span ><i class="fa fa-cog"></i> <?php echo __('More');?></span>
						</button>
					
						<ul class="dropdown-menu dropdown-menu-right" id="actions">
							<li>
								<a class="confirm" data-form-id="mass-actions" data-name="enable" href="staff.php?a=enable">
									<i class="fa fa-check"></i>
									<?php echo __( 'Enable'); ?>
								</a>
							</li>
							<li>
								<a class="confirm" data-form-id="mass-actions" data-name="disable" href="staff.php?a=disable">
									<i class="fa fa-ban"></i>
									<?php echo __( 'Disable'); ?>
								</a>
							</li>
							<li>
								<a class="dialog-first" data-action="permissions" href="#staff/reset-permissions">
									<i class="fa fa-sitemap"></i>
									<?php echo __( 'Reset Permissions'); ?>
								</a>
							</li>
							<li>
								<a class="dialog-first" data-action="department" href="#staff/change-department">
									<i class="fa fa-truck"></i>
									<?php echo __( 'Change Department'); ?>
								</a>
							</li>
							<!-- TODO: Implement "Reset Access" mass action
						<li><a class="dialog-first" href="#staff/reset-access">
						<i class="icon-puzzle-piece icon-fixed-width"></i>
							<?php echo __('Reset Access'); ?></a></li>
						-->
							<li class="danger">
								<a class="confirm" data-form-id="mass-actions" data-name="delete" href="staff.php?a=delete">
									<i class="fa fa-trash"></i>
									<?php echo __( 'Delete'); ?>
								</a>
							</li>
						</ul>
					</div>
				</div>
				<div>
					<div class="pull-right pd-bt-10 pd-tp-10">
						<?php
						if ($count) {
							echo __('Page').':'.$pageNav->getPageLinks();
						}
						?>
					</div>
				</div>
            </form>
        </div>
    </div>
</div>

<div style="padding:10px 15px;border-bottom:1px solid #e2e8f0;background:#f8fafc;font-size:13px;">
    <i class="fa fa-users" style="color:#64748b;"></i>
    <span class="faded" style="color:#475569;">
        <?php echo sprintf(
            _n('%1$d total agent · %2$d super admin · %3$d regular agent',
               '%1$d total agents · %2$d super admins · %3$d regular agents',
               $count,
               'deskuss'),
            $count,
            $admin_count,
            max(0, $count - $admin_count)
        ); ?>
    </span>
</div>

<br />
<form id="mass-actions" action="staff.php" method="POST" name="staff" >

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
            <th width="24%"><a <?php echo $name_sort; ?> href="staff.php?<?php echo $qstr; ?>&sort=name"><?php echo __('Name');?></a></th>
            <th width="14%"><a <?php echo $username_sort; ?> href="staff.php?<?php echo $qstr; ?>&sort=username"><?php echo __('Username');?></a></th>
            <th width="8%"><a  <?php echo $status_sort; ?> href="staff.php?<?php echo $qstr; ?>&sort=status"><?php echo __('Status');?></a></th>
            <th width="10%"><a <?php echo $role_sort; ?> href="staff.php?<?php echo $qstr; ?>&sort=role"><?php echo __('Role');?></a></th>
            <th width="14%"><a  <?php echo $dept_sort; ?>href="staff.php?<?php echo $qstr; ?>&sort=dept"><?php echo __('Department');?></a></th>
            <th width="12%"><a <?php echo $created_sort; ?> href="staff.php?<?php echo $qstr; ?>&sort=created"><?php echo __('Created');?></a></th>
            <th width="16%"><a <?php echo $login_sort; ?> href="staff.php?<?php echo $qstr; ?>&sort=login"><?php echo __('Last Login');?></a></th>
        </tr>
    </thead>
    <tbody>
    <?php
        if ($count):
            $ids = ($errors && is_array($_POST['ids'])) ? $_POST['ids'] : null;
            foreach ($agents as $agent) {
                $id = $agent->getId();
                $sel=false;
                if ($ids && in_array($id, $ids))
                    $sel=true;
                ?>
               <tr id="<?php echo esc_attr($id); ?>">
                <td align="center">
                  <input type="checkbox" class="ckb" name="ids[]"
                  value="<?php echo esc_attr($id); ?>" <?php echo $sel ? 'checked="checked"' : ''; ?> >
                <td><a href="staff.php?id=<?php echo esc_attr($id); ?>"><?php echo
                Format::htmlchars((string) $agent->getName()); ?></a></td>
                <td><?php echo esc_html($agent->getUserName()); ?></td>
                <td><?php echo $agent->isActive() ? __('Active') :'<b>'.__('Locked').'</b>'; ?><?php
                    echo $agent->onvacation ? ' <small>(<i>'.__('vacation').'</i>)</small>' : ''; ?></td>
                <td>
                    <?php if($agent->isAdmin()): ?>
                        <span class="label label-danger dsk-role-badge dsk-role-admin" title="<?php echo esc_attr__('Super Admin','deskuss'); ?>" style="background:#dc3545;color:#fff;padding:3px 8px;border-radius:3px;font-size:11px;font-weight:600;display:inline-block;white-space:nowrap;">
                            <i class="fa fa-shield"></i> <?php echo __('Super Admin'); ?>
                        </span>
                    <?php else: ?>
                        <span class="label label-default dsk-role-badge dsk-role-agent" title="<?php echo esc_attr__('Agent','deskuss'); ?>" style="background:#6c757d;color:#fff;padding:3px 8px;border-radius:3px;font-size:11px;display:inline-block;white-space:nowrap;">
                            <i class="fa fa-user"></i> <?php echo __('Agent'); ?>
                        </span>
                    <?php endif; ?>
                </td>

                <td><a href="departments.php?id=<?php echo esc_attr($agent->getDeptId()); ?>"><?php
                    echo Format::htmlchars((string) $agent->dept); ?></a></td>
                <td><?php echo Format::date($agent->created); ?></td>
                <td><?php echo Format::relativeTime(Misc::db2gmtime($agent->lastlogin)) ?: '<em class="faded">'.__('never').'</em>'; ?></td>
               </tr>
            <?php
            } //end of foreach
        endif; ?>
    </tbody>
	<?php 
	if(!$count){
		echo '<tfoot><tr><td colspan="9">'.__('No agents found!').'</td></tr></tfoot>';
	}
	?>
</table>
<?php if(!empty($results)): ?>
        <div class="panel-title table-caption" style="padding:15px 0px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
            <span><?php echo __('Below agents are added through wordpress you can add them as Deskuss agent');?></span>
            <?php
            $has_admin_in_list = false;
            foreach($results as $_r){
                if(user_can($_r->ID, 'administrator')){ $has_admin_in_list = true; break; }
            }
            if($has_admin_in_list):
            ?>
            <form method="post" action="staff.php" style="display:inline-block;margin:0;" onsubmit="return confirm('<?php echo esc_js(__('Auto-sync all listed WordPress administrators as Deskuss super admins?','deskuss')); ?>');">
                <?php csrf_token(); ?>
		<?php wp_nonce_field("deskuss_admin_action", "deskuss_admin_nonce"); ?>
                <input type="hidden" name="do" value="sync_wp_admins">
                <button type="submit" class="btn btn-success btn-sm"><i class="fa fa-refresh"></i> <?php echo __('Auto-Sync Admins','deskuss'); ?></button>
            </form>
            <?php endif; ?>
        </div>
<table class="table table-bordered">
    <thead>
        <tr>
            <th width="20%"><?php echo __('Name');?></th>
            <th width="16%"><?php echo __('Username');?></th>
            <th width="12%"><?php echo __('WP Role');?></th>
            <th width="8%"><?php echo __('Status');?></th>
            <th width="14%"><?php echo __('Department');?></th>
            <th width="14%"><?php echo __('Created');?></th>
            <th width="16%"><?php echo __('Last Login');?></th>
        </tr>
    </thead>
    <tbody>
        <?php
           foreach($results as $result){
            $custom_id = $result->ID;
            ?>
            <tr id="<?php echo esc_attr($custom_id); ?>">
                <td><a href="staff.php?a=wordpress_user&wp_id=<?php echo esc_attr($custom_id); ?>"><?php echo Format::htmlchars((string) $result->display_name); ?></a></td>
                <td><?php echo esc_html($result->user_login); ?></td>
                <td>
                    <?php
                    if(user_can($result->ID, 'administrator')){
                        echo '<span class="label label-danger dsk-role-badge dsk-role-admin" title="'.esc_attr__('WordPress Administrator','deskuss').'" style="background:#dc3545;color:#fff;padding:3px 8px;border-radius:3px;font-size:11px;font-weight:600;display:inline-block;white-space:nowrap;"><i class="fa fa-shield"></i> '.__('Administrator').'</span>';
                    } elseif(user_can($result->ID, 'deskuss_staff')){
                        echo '<span class="label label-info dsk-role-badge dsk-role-staff" title="'.esc_attr__('Deskuss Staff','deskuss').'" style="background:#17a2b8;color:#fff;padding:3px 8px;border-radius:3px;font-size:11px;display:inline-block;white-space:nowrap;"><i class="fa fa-user"></i> '.__('Staff').'</span>';
                    } else {
                        echo '<em class="faded">'.__('—').'</em>';
                    }
                    ?>
                </td>
                <td></td>
                <td></td>
                <td><?php echo Format::date($result->user_registered); ?></td>
                <td><?php echo Format::relativeTime(Misc::db2gmtime($agent->lastlogin)) ?: '<em class="faded">'.__('never').'</em>'; ?></td>
            </tr>
               <?php
           };
        ?>
    </tbody>
</table>
<?php endif; ?>
<br/>
	<?php
	if ($count) { //Show options..
	?>
	<div class="pull-right">
		<span class="faded"><?php echo $pageNav->showing(); ?>&nbsp;&mdash;&nbsp;</span>
		<?php
			echo __('Page').':'.$pageNav->getPageLinks();
		}?>
	</div>
</div>
</div>
</div>
</form>
</div>
</div>

<div style="display:none;" class="dialog panel panel-primary panel-dark m-b-0" id="confirm-action">
<div class="panel-heading">
    <div class="panel-title"><?php echo __('Please Confirm');?>
    <a class="close_me pull-right" href=""><i class="fa fa-remove"></i></a></div></div>
	<div class="panel-body">
    <p class="confirm-action" style="display:none;" id="enable-confirm">
        <?php echo sprintf(__('Are you sure you want to <b>enable</b> (unlock) %s?'),
            _N('selected agent', 'selected agents', 2));?>
    </p>
    <p class="confirm-action" style="display:none;" id="disable-confirm">
        <?php echo sprintf(__('Are you sure you want to <b>disable</b> (lock) %s?'),
            _N('selected agent', 'selected agents', 2));?>
        <br><br><?php echo __("Locked staff won't be able to login to Staff Control Panel.");?>
    </p>
    <p class="confirm-action" style="display:none;" id="delete-confirm">
        <font color="red"><strong><?php echo sprintf(__('Are you sure you want to DELETE %s?'),
            _N('selected agent', 'selected agents', 2));?></strong></font>
        <br><br><?php echo __('Deleted data CANNOT be recovered.');?>
    </p>
    <div><?php echo __('Please confirm to continue.');?></div>
    <div class="form-group" style="margin-top:20px;">
            <input type="button" value="<?php echo __('Yes, Do it!');?>" class="btn btn-success confirm">
            <input type="button" value="<?php echo __('No, Cancel');?>" class="btn close_me">
     </div>
	</div>
</div>
<script type="text/javascript">
$(document).on('click', 'a.dialog-first', function(e) {
    e.preventDefault();
    var action = $(this).data('action'),
        $form = $('form#mass-actions');
    if ($(':checkbox.ckb:checked', $form).length == 0) {
        $.sysAlert(__('Oops'),
            __('You need to select at least one item'));
        return false;
    }
    ids = $form.find('.ckb');
    $.dialog('ajax.php/' + $(this).attr('href').substr(1), 201, function (xhr, data) {
        $form.find('#action').val(action);
        data = JSON.parse(data);
        if (data)
            $.each(data, function(k, v) {
              if (v.length) {
                  $.each(v, function() {
                      $form.append($('<input type="hidden">').attr('name', k+'[]').val(this));
                  })
              }
              else {
                  $form.append($('<input type="hidden">').attr('name', k).val(v));
              }
          });
          $form.submit();
    }, { data: ids.serialize()});
    return false;
});
</script>
