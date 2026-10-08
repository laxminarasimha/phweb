<?php
// If the form was removed using the trashcan option, and there was some
// other validation error, don't render the deleted form the second time
if (isset($options['entry']) && ($options['mode'] ?? '') == 'edit'
    && $_POST
    && ($_POST['forms'] && !in_array($options['entry']->getId(), $_POST['forms']))
)
    return;

if (isset($options['entry']) && ($options['mode'] ?? '') == 'edit') { ?>
<div>
<?php } ?>
     
<?php
// Keep up with the entry id in a hidden field to decide what to add and
// delete when the parent form is submitted
if (isset($options['entry']) && ($options['mode'] ?? '') == 'edit') { ?>
    <input type="hidden" name="forms[]" value="<?php
        echo $options['entry']->getId(); ?>" />
<?php } ?>
<?php if ($form->getTitle()) { ?>
	<div class="panel-heading">
<?php if (($options['mode'] ?? '') == 'edit') { ?>
        <div class="pull-right">
    <?php if (!empty($options['entry'])
                && $options['entry']->getDynamicForm()->get('type') == 'G') { ?>
            <a href="#" title="Delete Entry" onclick="javascript:
                $(this).closest('div').remove();
                return false;"><i class="fa fa-trash"></i></a>&nbsp;
    <?php } ?>
            <i class="fa fa-sort" title="Drag to Sort"></i>
        </div>
<?php } ?>
	<div class="panel-title table-caption">
        <?php echo Format::htmlchars($form->getTitle()); ?>
	</div>
	</div>
	<?php 
		if(Format::htmldecode($form->getInstructions())){
		?>
			<div class="col-sm-12">
				<em style="color:gray;display:inline-block">
					<?php echo Format::htmldecode($form->getInstructions()); ?>
				</em>
			</div>
		<?php
		}
    }
	echo '<div class="panel-body">';
    foreach ($form->getFields() as $field) {
        try {
            if (!$field->isEnabled())
                continue;
            if (isset($options['mode']) && $options['mode'] == 'edit' && !$field->isEditableToStaff())
                continue;
        }
        catch (Exception $e) {
            // Not connected to a DynamicFormField
        }
        ?>
		<div class="row">
		<div class="<?php echo $field->ht['type'] == 'thread' ? 'col-sm-12' : 'col-sm-6' ?> form-group">
		<?php //if ($field->isBlockLevel()) { ?>
                <?php
            //}
            //else { ?>
				<label for="<?php echo '_'.$field->getFormName(); ?>" class="<?php if ($field->isRequiredForStaff() || $field->isRequiredForClose()) echo 'required'; ?>"><?php echo Format::htmlchars($field->getLocal('label')); ?></label>
            <?php
            //}
            if ($field->get('hint') && !$field->isBlockLevel()) { ?>
                &nbsp; <em style="color:gray;display:inline-block"><?php
                    echo Format::viewableImages($field->getLocal('hint')); ?></em>
            <?php
            }
			
            $field->render($options);
			
            if ($field->isStorable() && ($a = $field->getAnswer()) && $a->isDeleted()) {
                ?><a class="action-button float-right danger overlay" title="Delete this data"
                    href="#delete-answer"
                    onclick="javascript:if (confirm('<?php echo __('You sure?'); ?>'))
                        $.ajax({
                            url: 'ajax.php/form/answer/'
                                +$(this).data('entryId') + '/' + $(this).data('fieldId'),
                            type: 'delete',
                            success: $.proxy(function() {
                                $(this).closest('div').fadeOut();
                            }, this)
                        });"
                    data-field-id="<?php echo $field->getAnswer()->get('field_id');
                ?>" data-entry-id="<?php echo $field->getAnswer()->get('entry_id');
                ?>"> <i class="fa fa-trash"></i> </a><?php
            }
            if ($a && !$a->getValue() && $field->isRequiredForClose()) {
?><i class="fa fa-warning help-tip warning"
    data-title="<?php echo __('Required to close ticket'); ?>"
    data-content="<?php echo __('Data is required in this field in order to close the related ticket'); ?>"
/></i><?php
            }
            foreach ($field->errors() as $e) { ?>
                <div class="alert-danger"><?php echo Format::htmlchars($e); ?></div>
            <?php } ?>
		</div>
		</div>
    <?php }
	echo '</div>';
if (isset($options['entry']) && ($options['mode'] ?? '') == 'edit') { ?>
</div>
<?php } ?>
