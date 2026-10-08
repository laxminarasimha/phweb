<div class="panel">
<form action="forms.php" method="POST" name="forms">
 <div class="panel-heading">
	<div class="panel-title table-caption"><?php echo __('Custom Forms');?>
	</div>
</div>
<div class="panel-body">
<div class="pull-right form-group">
	<a href="forms.php?a=add" class="btn btn-success btn-outline"><i class="fa fa-plus-circle"></i> <?php
	echo __('Add New Custom Form'); ?></a>
	&nbsp;
	<div class="btn-group">
		<button type="button" class="btn btn-outline dropdown-toggle action-button" data-toggle="dropdown"><i class="fa fa-cog"></i>&nbsp;<?php echo __('More');?></button>
		<ul class="dropdown-menu dropdown-menu-right" id="actions">
			<li class="danger">
				<a class="confirm" data-name="delete" href="forms.php?a=delete">
					<i class="fa fa-trash"></i>
					<?php echo __( 'Delete'); ?>
				</a>
			</li>
		</ul>
	</div>	
</div>  
<br/>
<br/>

<?php
$other_forms = DynamicForm::objects()
    ->filter(array('type'=>'G'))
    ->exclude(array('flags__hasbit' => DynamicForm::FLAG_DELETED));

$page = (!empty($_GET['p']) && is_numeric($_GET['p'])) ? $_GET['p'] : 1;
$count = $other_forms->count();
$pageNav = new Pagenate($count, $page, PAGE_LIMIT);
$pageNav->setURL('forms.php');
$showing=$pageNav->showing().' '._N('form','forms',$count);
?>

<?php csrf_token(); ?>
<input type="hidden" name="do" value="mass_process" >
<input type="hidden" id="action" name="a" value="" >
<div class="row">
<div class="col-md-12">
<div class="table-light table-responsive">
<table class="table table-bordered list" border="0" cellspacing="1" cellpadding="0" width="940">
	<thead>
		<tr>
			<th width="4%">&nbsp;</th>
			<th width="50%"><?php echo __('Built-in Forms'); ?></th>
			<th><?php echo __('Last Updated'); ?></th>
		</tr>
	</thead>
	<tbody>
    <?php
    $forms = array(
        'U' => 'fa fa-user',
        'T' => 'fa fa-ticket',
        'A' => 'fa fa-tasks',
        'C' => 'fa fa-building',
        'O' => 'fa fa-group',
    );
    foreach (DynamicForm::objects()
            ->filter(array('type__in'=>array_keys($forms)))
            ->order_by('type', 'title') as $form) { ?>
		<tr>
			<td align="center"><i class="<?php echo $forms[$form->get('type')]; ?>"></i></td>
			<td><a href="?id=<?php echo $form->get('id'); ?>">
				<?php echo $form->get('title'); ?></a></td>
			<td><?php echo $form->get('updated'); ?></td>
		</tr>
    <?php } ?>
	</tbody>
	<tbody>
	<thead>
		<tr>
			<th width="4%" style="text-align:center"><?php if($count){ ?><input type="checkbox" id="selectToggle" class="ckb"><?php }else{ echo __(''); }?></th>
			<th><?php echo __('Custom Forms'); ?></th>
			<th><?php echo __('Last Updated'); ?></th>
		</tr>
	</thead>
	<tbody>
<?php foreach ($other_forms->order_by('title')
                ->limit($pageNav->getLimit())
                ->offset($pageNav->getStart()) as $form) {
            $sel=false;
            if($ids && in_array($form->get('id'),$ids))
                $sel=true; ?>
		<tr>
			<td align="center"><?php if ($form->isDeletable()) { ?>
				<input type="checkbox" class="ckb" name="ids[]" value="<?php echo $form->get('id'); ?>"
				<?php echo $sel?'checked="checked"':''; ?>>
				<?php } ?></td>
			<td><a href="?id=<?php echo $form->get('id'); ?>"><?php echo $form->get('title'); ?></a></td>
			<td><?php echo $form->get('updated'); ?></td>
		</tr>
    <?php }
    ?>
	</tbody>
	<?php 
	if(!$count){
		echo '<tfoot><tr><td colspan="3">'.sprintf(__(
                    'No extra forms defined yet &mdash; %s add one! %s'),
                    '<a href="forms.php?a=add">','</a>').'</td></tr></tfoot>';
	}
	?>
</table>
</div>
</div>
</div>
	<?php
		if ($count){ //Show options..
	?>
	<div class="pull-left">
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
		<div class="drag-handle panel-title"><?php echo __('Please Confirm');?>
			<a class="close_me pull-right" href=""><i class="fa fa-close"></i></a>
		</div>
	</div>
	<div class="panel-body">
		<p class="confirm-action" style="display:none;" id="delete-confirm">
			<font class="text-danger"><?php echo sprintf(__(
			'Are you sure you want to DELETE %s?'),
			_N('selected custom form', 'selected custom forms', 2));?></font>
			<br><br><?php echo __('Deleted data CANNOT be recovered.'); ?>
		</p>
		<div><?php echo __('Please confirm to continue.'); ?></div>
		<br />
		<p class="full-width">
			<span class="buttons pull-left">
				<input type="button" value="No, Cancel" class="close_me btn btn-success">
			</span>
			<span class="buttons pull-right">
				<input type="button" value="Yes, Do it!" class="confirm btn btn-info">
			</span>
		</p>
	</div>
</div>
