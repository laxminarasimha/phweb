<?php
if(!defined('DSKADMININC') || !$thisstaff) die('Access Denied');

OrganizationForm::ensureDynamicDataView();

$qs = array();
$orgs = Organization::objects()
    ->annotate(array('user_count'=>SqlAggregate::COUNT('users')));

if ($_REQUEST['query']) {
    $search = $_REQUEST['query'];
    $orgs->filter(Q::any(array(
        'name__contains' => $search,
        // TODO: Add search for cdata
    )));
    $qs += array('query' => $_REQUEST['query']);
}

$sortOptions = array(
        'name' => 'name',
        'users' => 'users',
        'create' => 'created',
        'update' => 'updated'
        );

$orderWays = array('DESC' => '-', 'ASC' => '');
$sort= ($_REQUEST['sort'] && $sortOptions[strtolower($_REQUEST['sort'])]) ? strtolower($_REQUEST['sort']) : 'name';
//Sorting options...
if ($sort && $sortOptions[$sort])
    $order_column = $sortOptions[$sort];

$order_column = $order_column ?: 'name';

if ($_REQUEST['order'] && $orderWays[strtoupper($_REQUEST['order'])])
    $order = $orderWays[strtoupper($_REQUEST['order'])];

if ($order_column && strpos($order_column,','))
    $order_column = str_replace(','," $order,",$order_column);

$x=$sort.'_sort';
$$x=' class="'.($order == '' ? 'asc' : 'desc').'" ';
$order_by="$order_column $order ";

$total = $orgs->count();
$page=($_GET['p'] && is_numeric($_GET['p']))? $_GET['p'] : 1;
$pageNav=new Pagenate($total, $page, PAGE_LIMIT);
$pageNav->paginate($orgs);

$qstr = '&amp;'. Http::build_query($qs);
$qs += array('sort' => $_REQUEST['sort'], 'order' => $_REQUEST['order']);
$pageNav->setURL('orgs.php', $qs);
$qstr.='&amp;order='.($order=='-' ? 'ASC' : 'DESC');

//echo $query;
$_SESSION[':Q:orgs'] = $orgs;

$orgs->values('id', 'name', 'created', 'updated');
$orgs->order_by($order . $order_column);
?>
<div class="panel">
	<div class="panel-heading">
		<div class="panel-title table-caption"><?php echo __('Organizations');?></div>
	</div>
	<div class="panel-body">
		<?php
			if(!empty($_REQUEST['added'])){
				?>
				<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert">×</button><i class="fa fa-check"></i>&nbsp;&nbsp;<?php echo __('Organization Added Successfully !');?></div>
				<?php
			}
		?>
        <form action="orgs.php" method="get" class="form-inline">
            <?php csrf_token(); ?>
			<div class="form-group">
            <input type="hidden" name="a" value="search">
			</div>
			<div class="form-group">
            <input type="text" class="form-control" placeholder="Search Organization" id="basic-org-search" name="query" value="<?php echo Format::htmlchars($_REQUEST['query']); ?>" autofocus autocomplete="off" autocorrect="off" autocapitalize="off">
			</div>
                <button type="submit" class="btn btn-primary">Filter</button>
            <!-- <td>&nbsp;&nbsp;<a href="" id="advanced-user-search">[advanced]</a></td> -->
			<div class="pull-right">
			<?php if ($thisstaff->hasPerm(Organization::PERM_CREATE)) { ?>
            <a class="popup-dialog" href="#orgs/add">
				<button type="button" class="btn btn-success btn-outline"><i class="fa fa-user-plus"></i>&nbsp;<?php echo __('Add Organization'); ?></button>
			</a>
			<?php } ?>
			<a href="orgs.php?a=export" class="no-pjax">
				<button type="button" class="btn btn-outline"><i class="fa fa-download"></i>&nbsp;<?php echo __('Export'); ?></button>
			</a>
			<?php if ($thisstaff->hasPerm(Organization::PERM_DELETE)) { ?>
			<div class="btn-group">
				<button type="button" class="btn btn-outline dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i>&nbsp;<?php echo __('More'); ?></button>
				<ul class="dropdown-menu dropdown-menu-right">
				<li><a href="#delete" class="orgs-action">
					<i class="fa fa-trash"></i>&nbsp;
					<?php echo __('Delete'); ?></a></li>
				</ul>
			</div>
			<?php } ?>
        </div>
		<div>
			<div class="pull-right pd-bt-10 pd-tp-10">
				<?php	
					if ($total){				
						echo sprintf(__('Page').': %s', $pageNav->getPageLinks());
					}
				?>
			</div>
		</div>
	</form>

<form id="orgs-list" action="orgs.php" method="POST" name="staff" >
 
<?php
$showing = $search ? __('Search Results').': ' : '';
if ($orgs->exists(true))
    $showing .= $pageNav->showing();
else
    $showing .= __('No organizations found!');

?>

 <?php csrf_token(); ?>
 <br/>
 <input type="hidden" name="a" value="mass_process" >
 <input type="hidden" id="action" name="do" value="" >
 <input type="hidden" id="selected-count" name="count" value="" >
 <div class="row">
 <div class="col-md-12">
 <div class="table-light table-responsive">
 <table class="table table-bordered" border="0" cellspacing="1" cellpadding="0" width="940">
    <thead>
        <tr>
            <th nowrap width="4%" style="text-align:center;"><input type="checkbox" id="selectToggle" class="ckb" /></th>
            <th width="45%"><a <?php echo $name_sort; ?> href="orgs.php?<?php echo $qstr; ?>&sort=name"><?php echo __('Name'); ?></a></th>
            <th width="11%"><a <?php echo $users_sort; ?> href="orgs.php?<?php echo $qstr; ?>&sort=users"><?php echo __('Users'); ?></a></th>
            <th width="20%"><a <?php echo $create_sort; ?> href="orgs.php?<?php echo $qstr; ?>&sort=create"><?php echo __('Created'); ?></a></th>
            <th width="20%"><a <?php echo $update_sort; ?> href="orgs.php?<?php echo $qstr; ?>&sort=update"><?php echo __('Last Updated'); ?></a></th>
        </tr>
    </thead>
    <tbody>
    <?php
        $ids=($errors && is_array($_POST['ids']))?$_POST['ids']:null;
        foreach ($orgs as $org) {

            $sel=false;
            if($ids && in_array($org['id'], $ids))
                $sel=true;
            ?>
           <tr id="<?php echo $org['id']; ?>">
            <td nowrap align="center">
                <input type="checkbox" value="<?php echo $org['id']; ?>" class="ckb mass nowarn"/>
            </td>
            <td>&nbsp; <a href="orgs.php?id=<?php echo $org['id']; ?>"><?php
            echo $org['name']; ?></a> </td>
            <td>&nbsp;<?php echo $org['user_count']; ?></td>
            <td><?php echo Format::date($org['created']); ?></td>
            <td><?php echo Format::datetime($org['updated']); ?>&nbsp;</td>
           </tr>
        <?php
        }
        ?>
    </tbody>
		
</table>
</div>
</div>
</div>
<?php
	if ($total){
?>
<div class="pull-right">
	<span class="faded"><?php echo $pageNav->showing(); ?>&nbsp;&mdash;&nbsp;</span>
	<?php			
		echo sprintf(__('Page').': %s', $pageNav->getPageLinks());
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
    $('input#basic-org-search').typeahead({
        source: function (typeahead, query) {
            $.ajax({
                url: "ajax.php/orgs/search?q="+query,
                dataType: 'json',
                success: function (data) {
                    typeahead.process(data);
                }
            });
        },
        onselect: function (obj) {
            window.location.href = 'orgs.php?id='+obj.id;
        },
        property: "/bin/true"
    });

    $(document).on('click', 'a.popup-dialog', function(e) {
        e.preventDefault();
        $.orgLookup('ajax.php/orgs/add', function (org) {
            var url = 'orgs.php?added=' + org.id;
            window.location = url;
            return false;
         });

        return false;
     });

    var goBaby = function(action) {
        var ids = [],
            $form = $('form#orgs-list');
        $(':checkbox.mass:checked', $form).each(function() {
            ids.push($(this).val());
        });
        if (ids.length) {
          var submit = function() {
            $form.find('#action').val(action);
            $.each(ids, function() { $form.append($('<input type="hidden" name="ids[]">').val(this)); });
            $form.find('#selected-count').val(ids.length);
            $form.submit();
          };
          $.confirm(__('You sure?')).then(submit);
        }
        else if (!ids.length) {
            $.sysAlert(__('Oops'),
                __('You need to select at least one item'));
        }
    };
    $(document).on('click', 'a.orgs-action', function(e) {
        e.preventDefault();
        goBaby($(this).attr('href').substr(1));
        return false;
    });
});
</script>
