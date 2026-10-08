<div class="panel panel-primary panel-dark m-b-0">
<div class="panel-heading">
<div class="drag-handle panel-title"><i class="fa fa-clipboard"></i> <?php echo __('Manage Forms'); ?></i><a class="close_me pull-right" href="#"><i class="fa fa-remove"></i></a></div>
</div>
<div class="panel-body">
<?php echo __(
'Sort the forms on this ticket by click and dragging on them. Use the box below the forms list to add new forms to the ticket.'
); ?>
<br/>
<br/>
<form method="post" action="<?php echo $info['action']; ?>">
<div id="ticket-entries" class="form-group">
<?php
$current_list = array();
foreach ($forms as $e) { ?>
<div class="sortable row-item drag-handle" data-id="<?php echo $e->get('id'); ?>" style="border:1px solid #cac9c9 ;padding:9px">
    <input class="form-control" type="hidden" name="forms[]" value="<?php echo $e->get('form_id'); ?>" />
    <i class="fa fa-bars"></i> <?php echo $e->getTitle();
    $current_list[] = $e->get('form_id');
    if ($e->getDynamicForm()->get('type') == 'G') { ?>
    <div class="button-group pull-right">
		<div class="delete"><a href="#" onclick="$(this).closest('div.row-item').remove();$('#delete-warning').show();"><i class="fa fa-trash-o"></i></a></div>
    </div>
    <?php } ?>
</div>
<?php } ?>
</div>
<div>
<div class="form-group form-inline">
<i class="fa fa-plus"></i>&nbsp;
<select class="form-control" name="new-form" onchange="javascript:
    $(this).parent().find('button').trigger('click');">
<option selected="selected" disabled="disabled"><?php
    echo __('Add a form'); ?></option>
<?php foreach (DynamicForm::objects()
    ->filter(array('type'=>'G'))
    ->exclude(array('flags__hasbit' => DynamicForm::FLAG_DELETED))
    as $f) {
    if (in_array($f->get('id'), $current_list))
        continue;
    ?><option value="<?php echo $f->get('id'); ?>"><?php
    echo $f->getTitle(); ?></option><?php
} ?>
</select>
<button type="button" class="btn btn-outline btn-success" onclick="javascript:
    var select = $(this).parent().find('select'),
        $sel = select.find('option:selected'),
        id = $sel.val();
    if (!id || !parseInt(id))
        return;
    if ($sel.prop('disabled'))
        return;
    $('#ticket-entries').append($('<div></div>').addClass('sortable drag-handle row-item').attr('style','border:1px solid #cac9c9 ;padding:9px')
        .text(' '+$sel.text())
        .data('id', id)
        .prepend($('<i>').addClass('fa fa-bars'))
        .append($('<input/>').attr({name:'forms[]', type:'hidden'}).val(id))
        .append($('<div></div>').addClass('button-group pull-right')
          .append($('<div></div>').addClass('delete')
            .append($('<a href=\'#\'>')
              .append($('<i>').addClass('fa fa-trash-o'))
              .click(function() {
                $sel.prop('disabled',false);
                $(this).closest('div.row-item').remove();
                $('#delete-warning').show();
                return false;
              })
            )
        ))
    );
    $sel.prop('disabled',true);"><i class="fa fa-plus"></i>
<?php echo __('Add'); ?></button>
</div>
</div>

<div id="delete-warning" style="display:none">
    <div id="msg_warning"><?php echo __(
    'Clicking <strong>Save Changes</strong> will permanently delete data associated with the deleted forms'
    ); ?>
    </div>
</div>
    <div class="form-group" style="margin-top:20px;">
            <input type="submit" class="btn btn-success" value="<?php echo __('Save Changes'); ?>">
            <input type="reset" class="btn btn-info" value="<?php echo __('Reset'); ?>">
            <input type="button" name="cancel" class="btn <?php
                echo $user ? 'cancel' : 'close_me' ?>" value="<?php echo __('Cancel'); ?>">
     </div>
	 </form>
</div>	 
</div>
<script type="text/javascript">
$(function() {
    $('#ticket-entries').sortable();
});
</script>
