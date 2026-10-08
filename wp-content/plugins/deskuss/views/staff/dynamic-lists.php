<div class="panel">
<form action="lists.php" method="POST" name="lists">
	<div class="panel-heading">
		<div class="panel-title table-caption"><?php echo __('Custom Lists');?>
		</div>
	</div>
    <div class="panel-body">
		<div class="pull-right">
			<a href="lists.php?a=add" class="btn btn-success btn-outline"><i class="fa fa-plus-circle"></i> <?php
					echo __('Add New Custom List'); ?></a>					
			<div class="btn-group">
				<button type="button" class="btn btn-outline dropdown-toggle action-button" data-toggle="dropdown"><i class="fa fa-cog"></i>&nbsp;<?php echo __('More');?></button>
				<ul class="dropdown-menu dropdown-menu-right" id="actions">
					<li class="danger">
						<a class="confirm" data-name="delete" href="lists.php?a=delete">
							<i class="fa fa-trash"></i>
							<?php echo __( 'Delete'); ?>
						</a>
					</li>
				</ul>
			</div>
		</div>	
<br />
<br />

<?php
$page = (!empty($_GET['p']) && is_numeric($_GET['p'])) ? $_GET['p'] : 1;
$count = DynamicList::objects()->count();
$pageNav = new Pagenate($count, $page, PAGE_LIMIT);
$pageNav->setURL('lists.php');
$showing=$pageNav->showing().' '._N('custom list', 'custom lists', $count);

?>
<?php csrf_token(); ?>
<input type="hidden" name="do" value="mass_process" >
<input type="hidden" id="action" name="a" value="" >
<div class="form-group pd-bt-10">
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
<div class="row">
<div class="col-md-12">
<div class="table-light table-responsive">
<table class="table table-bordered list" border="0" cellspacing="1" cellpadding="0" width="940">
	<thead>
		<tr>
			<th width="4%" style="text-align:center"><?php if($count){ ?><input type="checkbox" id="selectToggle" class="ckb"><?php }else{ echo __(''); }?></th>
			<th width="32%"><?php echo __('List Name'); ?></th>
			<th width="32%"><?php echo __('Created') ?></th>
			<th width="32%"><?php echo __('Last Updated'); ?></th>
		</tr>
	</thead>
	<tbody>
	<?php foreach (DynamicList::objects()->order_by('-type', 'name')
                ->limit($pageNav->getLimit())
                ->offset($pageNav->getStart()) as $list) {
            $sel = false;
            if (!empty($ids) && in_array($form->get('id'),$ids))
                $sel = true; ?>
		<tr>
			<td align="center">
                <?php
                if ($list->isDeleteable()) { ?>
                <input width="7" type="checkbox" class="ckb" name="ids[]"
                value="<?php echo esc_attr($list->getId()); ?>"
                    <?php echo $sel?'checked="checked"':''; ?>>
                <?php
                } else {
                    echo '&nbsp;';
                }
                ?>
			</td>
		<td><a href="?id=<?php echo esc_attr($list->getId()); ?>"><?php echo
		esc_html($list->getPluralName() ?: $list->getName()); ?></a></td>
		<td><?php echo esc_html($list->get('created')); ?></td>
		<td><?php echo esc_html($list->get('updated')); ?></td>
		</tr>
	<?php }
	?>
	</tbody>
	<?php 
	if(!$count){
		echo '<tfoot><tr><td colspan="4">'.sprintf(__('No custom lists defined yet &mdash; %s add one %s!'),
                    '<a href="lists.php?a=add">','</a>').'</td></tr></tfoot>';
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
		<div class="drag-handle panel-title"><?php echo __('Please Confirm'); ?>
			<a class="close_me pull-right" href=""><i class="fa fa-close"></i></a>
		</div>
    </div>
	<div class="panel-body">
    <p class="confirm-action" style="display:none;" id="delete-confirm">
       <font class="text-danger"><?php echo sprintf(
        __('Are you sure you want to DELETE %s?'),
        _N('selected custom list', 'selected custom lists', 2)); ?></font>
        <br><br><?php echo __('Deleted data CANNOT be recovered.'); ?>
    </p>
    <div><?php echo __('Please confirm to continue.'); ?></div>
    <br>
    <p class="full-width">
        <span class="buttons pull-left">
            <input type="button" value="No, Cancel" class="close_me btn btn-success">
        </span>
        <span class="buttons pull-right">
            <input type="button" value="Yes, Do it!" class="confirm btn btn-info">
        </span>
    </p>
    <div class="clear"></div>
</div>
</div>
