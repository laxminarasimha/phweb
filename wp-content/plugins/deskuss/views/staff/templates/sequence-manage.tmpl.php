<div class="panel panel-primary panel-dark m-b-0">
<div class="panel-heading">
<div class="drag-handle panel-title"><?php echo __('Manage Sequences'); ?><a class="close_me pull-right" href="#"><i class="fa fa-remove"></i></a></div>
</div>
<div class="panel-body">
<p><?php echo __(
'Sequences are used to generate sequential numbers. Various sequences can be used to generate sequences for different purposes.'); ?>
</p>
<br />
<form method="post" action="<?php echo $info['action']; ?>">
<div id="sequences">
<?php
$current_list = array();
foreach ($sequences as $e) {
    $field = function($field, $name=false) use ($e) { ?>
    <input class="f<?php echo $field; ?>" type="hidden" name="seq[<?php echo $e->id;
        ?>][<?php echo $name ?: $field; ?>]" value="<?php echo $e->{$field}; ?>"/>&nbsp;
<?php }; ?>
    <div class="row-item row form-inline m-t-1 b-b-1" style="border-color: rgba(0, 0, 0, 0.07);">
        <?php echo $field('name'); echo $field('current', 'next'); echo $field('increment'); echo $field('padding'); ?>
        <input type="hidden" class="fdeleted form-control" name="seq[<?php echo $e->get('id'); ?>][deleted]" value="0"/>
		<i class="fa fa-sort-numeric-asc pull-left"></i> &nbsp;
        <div class="name col-sm-6"><?php echo $e->getName(); ?> </div>
        <div class="manage-buttons col-sm-4">
            <span class="faded"><?php echo __('next'); ?></span>
            <span class="current"><?php echo $e->current(); ?></span>
        </div>
        <div class="button-group col-sm-1 pull-right">
            <div class="manage" style="display:inline;"><a href="#"><i class="fa fa-cog"></i></a></div>
            <div class="delete" style="display:inline;"><?php if (!$e->hasFlag(Sequence::FLAG_INTERNAL)) { ?>
                <a href="#"><i class="fa fa-trash"></i></a><?php } ?></div>
        </div>
        <div class="management hidden col-sm-12 m-t-1" data-id="<?php echo $e->id; ?>">
            <table width="100%" class="form-inline"><tbody>
                <tr><td><label><?php echo __('Increment'); ?>:
                    <input class="-increment form-control" type="text" size="4" value="<?php echo Format::htmlchars($e->increment); ?>"/>
                    </label></td>
                    <td><label><?php echo __('Padding Character'); ?>:
                    <input class="-padding form-control" maxlength="1" type="text" size="4" value="<?php echo Format::htmlchars($e->padding); ?>"/>
                    </label></td></tr>
            </tbody></table>
        </div>
    </div>
<?php } ?>
</div>

<div class="row-item row form-inline m-t-1 b-b-1 hidden" id="template" style="border-color: rgba(0, 0, 0, 0.07);">
    <i class="fa fa-sort-numeric-asc pull-left"></i> &nbsp;
	<div class="name col-sm-6">
		<?php echo __('New Sequence'); ?>
	</div>
    <div class="manage-buttons  col-sm-4">
        <span class="faded">next</span>
        <span class="next">1</span>
    </div>
    <div class="button-group col-sm-1 pull-right">
        <div class="manage" style="display:inline;"><a href="#"><i class="fa fa-cog"></i></a>&nbsp;</div>
        <div class="delete new" style="display:inline;"><a href="#"><i class="fa fa-trash"></i></a></div>
    </div>
    <div class="management hidden col-sm-12 m-t-1" data-id="<?php echo $e->id; ?>">
		<table width="100%" class="form-inline">
			<tbody>
				<tr>
					<td>
						<label><?php echo __('Increment'); ?>:
						<input class="-increment form-control" type="text" size="4" value="1"/>
						</label>
					</td>
					<td>
						<label><?php echo __('Padding Character'); ?>:
						<input class="-padding form-control" maxlength="1" type="text" size="4" value="0"/>
						</label>
					</td>
				</tr>
			</tbody>
		</table>
    </div>
</div>
<br />
<button onclick="javascript:
  var id = ++$.uid, base = 'seq[new-'+id+']';
  var clone = $('.row-item#template').clone()
    .appendTo($('#sequences'))
    .removeClass('hidden')
    .removeAttr('id')
    .append($('<input>').attr({type:'hidden',class:'fname',name:base+'[name]',value:'<?php echo __('New Sequence'); ?>'}))
    .append($('<input>').attr({type:'hidden',class:'fcurrent',name:base+'[current]',value:'1'}))
    .append($('<input>').attr({type:'hidden',class:'fincrement',name:base+'[increment]',value:'1'}))
    .append($('<input>').attr({type:'hidden',class:'fpadding',name:base+'[padding]',value:'0'})) ;
  clone.find('.manage a').trigger('click');
  return false;
  " class="btn btn-success btn-outline"><i class="fa fa-plus"></i> <?php echo __('Add New Sequence'); ?></button>
<div id="delete-warning" style="display:none">
    <div id="msg_warning" class="text-danger"><br /><?php echo __(
		'Clicking <strong>Save Changes</strong> will permanently remove the
		deleted sequences.'); ?>
	<br /><br />
    </div>
</div>
<div>
    <span class="buttons pull-right">
		<input type="submit" class="btn btn-success" value="<?php echo __('Save Changes'); ?>" onclick="javascript:
				$('#sequences .save a').each(function() { $(this).trigger('click'); });">
		<input type="button" name="cancel" class="close_me btn"
        value="<?php echo __('Cancel'); ?>" />
    </span>
</div>
</form>
</div>
</div>

<script type="text/javascript">
$(function() {
	var remove = function() {
		if (!$(this).parent().hasClass('new')) {
		  $('#delete-warning').show();
		  $(this).closest('.row-item').hide()
			.find('input.fdeleted').val('1');
		}else{
		  $(this).closest('.row-item').remove();
		}
		return false;
	}
	
	var manage = function() {
		var top = $(this).closest('.row-item');
		top.find('.management').show(200).removeClass('hidden');
		top.find('.name').empty().append($('<input class="-name form-control" type="text" size="30">')
			.val(top.find('input.fname').val())
		);
		top.find('.current').empty().append($('<input class="-current form-control" type="text" size="10">')
			.val(top.find('input.fcurrent').val())
		);
		$(this).find('i').attr('class','fa fa-save');
		$(this).parent().attr('class','save');
		return false;
	}
	var save = function() {
		var top = $(this).closest('.row-item');
		top.find('.management').hide(200).addClass('hidden');
		$.each(['name', 'current'], function(i, t) {
			var val = top.find('input.-'+t).val();
			top.find('.'+t).empty().text(val);
			top.find('input.f'+t).val(val);
		});
		$.each(['increment', 'padding'], function(i, t) {
			top.find('input.f'+t).val(top.find('input.-'+t).val());
		});
		$(this).find('i').attr('class','fa fa-cog');
		$(this).parent().attr('class','manage');
		return false;
	};
	$(document).on('click.seq', '#sequences .manage a', manage);
	$(document).on('click.seq', '#sequences .save a', save);
	$(document).on('click.seq', '#sequences .delete a', remove);
	$('.close, input:submit').click(function() {
	  $(document).off('click.seq');
	});
});
</script>
