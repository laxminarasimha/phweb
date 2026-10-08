<?php
if(!defined('DSKSTAFFINC') || !$thisstaff || !$thisstaff->isStaff()) die('Access Denied');
$qs = array();

$agents = Staff::objects()
    ->select_related('dept');

// Sanitize 'order' param To Escape XSS
if ($_REQUEST['order'])
    $_REQUEST['order'] = Format::sanitize($_REQUEST['order']);

if($_REQUEST['q']) {
    $searchTerm=$_REQUEST['q'];
    if($searchTerm){
        if(is_numeric($searchTerm)){
            $agents->filter(Q::any(array(
                'phone__contains'=>$searchTerm,
                'phone_ext__contains'=>$searchTerm,
                'mobile__contains'=>$searchTerm,
            )));
        }elseif(strpos($searchTerm,'@') && Validator::is_email($searchTerm)){
            $agents->filter(array('email'=>$searchTerm));
        }else{
            $agents->filter(Q::any(array(
                'email__contains'=>$searchTerm,
                'lastname__contains'=>$searchTerm,
                'firstname__contains'=>$searchTerm,
            )));
        }
    }
}

if($_REQUEST['did'] && is_numeric($_REQUEST['did'])) {
    $agents->filter(array('dept'=>$_REQUEST['did']));
    $qs += array('did' => $_REQUEST['did']);
}

$sortOptions=array('name'=>array('firstname','lastname'),'email'=>'email','dept'=>'dept__name',
                   'phone'=>'phone','mobile'=>'mobile','ext'=>'phone_ext',
                   'created'=>'created','login'=>'lastlogin');
$orderWays=array('DESC'=>'-','ASC'=>'');

switch ($cfg->getAgentNameFormat()) {
case 'last':
case 'lastfirst':
case 'legal':
    $sortOptions['name'] = array('lastname', 'firstname');
    break;
// Otherwise leave unchanged
}

$sort=($_REQUEST['sort'] && $sortOptions[strtolower($_REQUEST['sort'])])?strtolower($_REQUEST['sort']):'name';
//Sorting options...
if($sort && $sortOptions[$sort]) {
    $order_column =$sortOptions[$sort];
}
$order_column = $order_column ?: 'firstname,lastname';

if($_REQUEST['order'] && $orderWays[strtoupper($_REQUEST['order'])]) {
    $order=$orderWays[strtoupper($_REQUEST['order'])];
}

$x=$sort.'_sort';
$$x=' class="'.strtolower($_REQUEST['order'] ?: 'desc').'" ';
foreach ((array) $order_column as $C) {
    $agents->order_by($order.$C);
}

$total=$agents->count();
$page=($_GET['p'] && is_numeric($_GET['p']))?$_GET['p']:1;
$pageNav=new Pagenate($total, $page, PAGE_LIMIT);
$qstr = '&amp;'. Http::build_query($qs);
$qs += array('sort' => $_REQUEST['sort'], 'order' => $_REQUEST['order']);
$pageNav->setURL('directory.php', $qs);
$pageNav->paginate($agents);

//Ok..lets roll...create the actual query
$qstr.='order='.($_REQUEST['order']=='DESC' ? 'ASC' : 'DESC');

?>

<?php
if ($agents->exists(true))
	$showing=$pageNav->showing();
else
	$showing=__('No agents found!');
?>

<div class="panel">
	<div class="panel-heading">
		<div class="panel-title table-caption"><?php echo __('Agents');?></div>
	</div>

	<div class="panel-body">
		<form action="directory.php" method="GET" name="filter" class="form-inline">
			<div class="form-group">
			<input type="text" id="search_q" name="q" class="form-control" value="<?php echo Format::htmlchars($_REQUEST['q']); ?>" placeholder="<?php echo __('Search Agents');?>">
			</div>
			<div class="form-group">
			<select class="form-control" name="did" id="did">
				<option value="0"><?php echo __('All Departments');?></option>
				 <?php
					foreach (Dept::getDepartments(array('nonempty'=>1)) as $id=>$name) {
						$sel=($_REQUEST['did'] && $_REQUEST['did']==$id)?'selected="selected"':'';
						echo sprintf('<option value="%d" %s>%s</option>',$id,$sel,$name);
					}
				 ?>
			  </select>
			</div>
			<button type="submit" class="btn btn-primary"><?php echo __('Filter');?></button>
		</form>
	</div>

    <div class="panel-body">
	<div class="row">
	  <div class="col-md-12">
		<div class="table-light table-responsive">
		  <table class="table table-bordered">
			<thead>
			  <tr>
				<th><a <?php echo $name_sort; ?> href="directory.php?<?php echo $qstr; ?>&sort=name"><?php echo __('Name');?></a></th>
				<th><a  <?php echo $dept_sort; ?>href="directory.php?<?php echo $qstr; ?>&sort=dept"><?php echo __('Department');?></a></th>
				<th><a  <?php echo $email_sort; ?>href="directory.php?<?php echo $qstr; ?>&sort=email"><?php echo __('Email Address');?></a></th>
				<th><a <?php echo $phone_sort; ?> href="directory.php?<?php echo $qstr; ?>&sort=phone"><?php echo __('Phone Number');?></a></th>
				<th><a <?php echo $ext_sort; ?> href="directory.php?<?php echo $qstr; ?>&sort=ext"><?php echo __('Extension');?></a></th>
				<th><a <?php echo $mobile_sort; ?> href="directory.php?<?php echo $qstr; ?>&sort=mobile"><?php echo __('Mobile Number');?></a></th>
			  </tr>
			</thead>
			<tbody>
			<?php
				$ids=($errors && is_array($_POST['ids']))?$_POST['ids']:null;
				foreach ($agents as $A) { ?>
				   <tr id="<?php echo $A->staff_id; ?>">
						<td>&nbsp;<?php echo Format::htmlchars($A->getName()); ?></td>
						<td>&nbsp;<?php echo Format::htmlchars((string) $A->dept); ?></td>
						<td>&nbsp;<?php echo Format::htmlchars($A->email); ?></td>
						<td>&nbsp;<?php echo Format::phone($A->phone); ?></td>
						<td>&nbsp;<?php echo $A->phone_ext; ?></td>
						<td>&nbsp;<?php echo Format::phone($A->mobile); ?></td>
				   </tr>
					<?php
					} // end of foreach
				?>
			</tbody>
		  </table>
		  <div class="table-footer">
			<?php if ($agents->exists(true)) {
				echo '<div>&nbsp;'.__('Page').':'.$pageNav->getPageLinks().'&nbsp;</div>';
				?>
			<?php } else {
				echo __('No agents found!');
			} ?>
		  </div>
		</div>
	  </div>
  </div>
</div>
</div>
