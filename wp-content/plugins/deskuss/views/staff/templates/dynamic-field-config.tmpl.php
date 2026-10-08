<div class="panel panel-primary panel-dark" style="border:none;">
	<div class="panel-heading">
		<div class="drag-handle panel-title"><?php echo __('Field Configuration'); ?> &mdash; <?php echo $field->get('label') ?>
		<a class="close_me pull-right" href=""><i class="fa fa-close"></i></a>
		</div>
	</div>
    
    <form method="post" class="panel-body" action="#form/field-config/<?php
		echo $field->get('id'); ?>">
	
	<div class="panel-heading">
		<div class="panel-title table-caption"><?php echo __('Settings'); ?>
		</div>
	</div>

	<div class="panel-body" id="visibility">
		<div class="row">
			<div class="col-sm-4">
				<div><strong><?php echo __('Enabled'); ?></strong>
				<i class="help-tip fa fa-question-circle"  data-title="<?php echo __('Enabled'); ?>" data-content="<?php echo __('This field can be disabled which will remove it from the form for new entries, but will preserve the data on all current entries.'); ?>"></i>
				</div>
			</div>
			<div class="col-sm-6">
				<input type="checkbox" name="flags[]" value="<?php
					echo DynamicFormField::FLAG_ENABLED; ?>" <?php
					if ($field->hasFlag(DynamicFormField::FLAG_ENABLED)) echo 'checked="checked"';
					if ($field->hasFlag(DynamicFormField::FLAG_MASK_DISABLE)) echo ' disabled="disabled"';
					?>> <?php echo __('Enabled'); ?><br/>
			</div>
		</div>
		<hr />
		<div class="row">
			<div class="col-sm-4">
				<div><strong><?php echo __('Visible'); ?></strong>
				<i class="help-tip fa fa-question-circle" data-title="<?php echo __('Visible'); ?>"	data-content="<?php echo __('Making fields <em>visible</em> allows agents and endusers to view and create information in this field.'); ?>"></i>
				</div>
			</div>
			<div class="col-sm-3">
				<input type="checkbox" name="flags[]" value="<?php
					echo DynamicFormField::FLAG_CLIENT_VIEW; ?>" <?php
					if ($field->hasFlag(DynamicFormField::FLAG_CLIENT_VIEW)) echo 'checked="checked"';
					if ($field->isPrivacyForced()) echo ' disabled="disabled"';
					?>> <?php echo __('For EndUsers'); ?><br/>
			</div>
			<div class="col-sm-3">
				<input type="checkbox" name="flags[]" value="<?php
					echo DynamicFormField::FLAG_AGENT_VIEW; ?>" <?php
					if ($field->hasFlag(DynamicFormField::FLAG_AGENT_VIEW)) echo 'checked="checked"';
					if ($field->isPrivacyForced()) echo ' disabled="disabled"';
					?>> <?php echo __('For Agents'); ?><br/>
			</div>
		</div>

		<?php if ($field->getImpl()->hasData()) { ?>
			<hr />
			<div class="row">
				<div class="col-sm-4">
					<div><strong><?php echo __('Required'); ?></strong>
					<i class="help-tip fa fa-question-circle"
						data-title="<?php echo __('Required'); ?>"
						data-content="<?php echo __('New entries cannot be created unless all <em>required</em> fields have valid data.'); ?>"></i>
					</div>
				</div>
				<div class="col-sm-3">
					<input type="checkbox" name="flags[]" value="<?php
						echo DynamicFormField::FLAG_CLIENT_REQUIRED; ?>" <?php
						if ($field->hasFlag(DynamicFormField::FLAG_CLIENT_REQUIRED)) echo 'checked="checked"';
						if ($field->isRequirementForced()) echo ' disabled="disabled"';
						?>> <?php echo __('For EndUsers'); ?><br/>
				</div>
				<div class="col-sm-3">
					<input type="checkbox" name="flags[]" value="<?php
						echo DynamicFormField::FLAG_AGENT_REQUIRED; ?>" <?php
						if ($field->hasFlag(DynamicFormField::FLAG_AGENT_REQUIRED)) echo 'checked="checked"';
						if ($field->isRequirementForced()) echo ' disabled="disabled"';
						?>> <?php echo __('For Agents'); ?><br/>
				</div>
			</div>
			<hr />
			<div class="row">
				<div class="col-sm-4">
					<div><strong>Editable</strong>
					<i class="help-tip fa fa-question-circle"
						data-content="<?php echo __('Fields marked editable allow agents and endusers to update the content of this field after the form entry has been created.'); ?>"
						data-title="<?php echo __('Editable'); ?>"></i>
					</div>
				</div>

				<div class="col-sm-3">
					<input type="checkbox" name="flags[]" value="<?php
						echo DynamicFormField::FLAG_CLIENT_EDIT; ?>" <?php
						if ($field->hasFlag(DynamicFormField::FLAG_CLIENT_EDIT)) echo 'checked="checked"';
						?>> <?php echo __('For EndUsers'); ?><br/>
				</div>
				<div class="col-sm-3">
					<input type="checkbox" name="flags[]" value="<?php
						echo DynamicFormField::FLAG_AGENT_EDIT; ?>" <?php
						if ($field->hasFlag(DynamicFormField::FLAG_AGENT_EDIT)) echo 'checked="checked"';
						?>> <?php echo __('For Agents'); ?><br/>
				</div>
			</div>

			<?php if (in_array($field->get('form')->get('type'), array('G', 'T', 'A'))) { ?>
			
			<hr />
			<div class="row">
				<div class="col-sm-4">
					<div><strong><?php echo __('Data Integrity'); ?></strong>
					<i class="help-tip fa fa-question-circle"
						data-title="<?php echo __('Required to close a thread'); ?>"
						data-content="<?php echo __('Optionally, this field can prevent closing a thread until it has valid data.'); ?>"></i>
					</div>
				</div>
				<div class="col-sm-6">
					<input type="checkbox" name="flags[]" value="<?php
						echo DynamicFormField::FLAG_CLOSE_REQUIRED; ?>" <?php
						if ($field->hasFlag(DynamicFormField::FLAG_CLOSE_REQUIRED)) echo 'checked="checked"';
						?>> <?php echo __('Require entry to close a thread'); ?><br/>
				</div>
			</div>
			<?php } ?>
		<?php } ?>
	</div>
	
	<div class="panel-heading">
		<div class="panel-title table-caption"><?php echo __('Field Setup'); ?>
		</div>
	</div>

	<div class="panel-body" id="config">
		<div class="row">
			<?php
			echo csrf_token();
			$form = $field->getConfigurationForm();
			echo $form->getMedia();
			foreach ($form->getFields() as $name=>$f) { ?>
				<div class="flush-left custom-field" id="field<?php echo $f->getWidget()->id;
					?>" <?php if (!$f->isVisible()) echo 'style="display:none;"'; ?>>
					<div class="field-label">
						<label for="<?php echo $f->getWidget()->name; ?>" <?php if ($f->get('required')) echo 'class="required"'; ?>>
							<?php echo Format::htmlchars($f->getLocal('label')); ?>:
						</label>
						<?php
						if ($f->get('hint')) { ?>
							<br/><em style="color:gray;display:inline-block"><?php
								echo Format::viewableImages($f->get('hint')); ?></em>
						<?php
						} ?>
					</div>
					<div>
						<?php
						$f->render();
						?>
					</div>
					<?php
					foreach ($f->errors() as $e) { ?>
						<div class="alert-danger"><?php echo $e; ?></div>
					<?php } ?>
				</div>
			<?php }
			?>
			<div class="flush-left custom-field">
				<div class="field-label">
					<label for="hint"><?php echo __('Help Text') ?>:</label>
					<br />
					<em style="color:gray;display:inline-block">
						<?php echo __('Help text shown with the field'); ?></em>
				</div>
				<div class="form-group">
					<textarea width:calc(100% - 20px)" name="hint" rows="3" cols="40"
						class="summernote-base small no-bar form-control"
						data-translate-tag="<?php echo $field->getTranslateTag('hint'); ?>"><?php
						echo Format::htmlchars($field->get('hint')); ?></textarea>
				</div>
			</div>
		</div>
	</div>
	
	<div class="panel-body">
		<span class="buttons">
			<input type="submit" class="btn btn-success" value="<?php echo __('Save'); ?>">
			<input type="reset" class="btn btn-info" value="<?php echo __('Reset'); ?>">
			<input type="button" class="close_me btn btn-default" value="<?php echo __('Cancel'); ?>">
		</span>
	</div>
    </form>
</div>
<script type="text/javascript">
   // Make translatable fields translatable
   $('input[data-translate-tag]').translatable();
   
   $(document).ready(function(){
	  
		load_help_tip();
   });
	
</script>
