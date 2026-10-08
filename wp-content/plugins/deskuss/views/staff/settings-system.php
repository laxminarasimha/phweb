<?php
if(!defined('DSKADMININC') || !$thisstaff || !$thisstaff->isAdmin() || !$config) die('Access Denied');

$gmtime = Misc::gmtime();
?>
<div class="panel">
<div class="panel-heading">
	<div class="panel-title table-caption">
		<?php echo __('System Settings and Preferences');?>
	</div>
</div>
<form action="settings.php?t=system" method="post" role="form" class="save">
<div class="panel-body">
<?php csrf_token(); ?>
<input type="hidden" name="t" value="system" >

<div class="panel-heading">
	<div class="panel-title table-caption">
		<?php echo __('General Settings'); ?>
	</div>
</div>
<div class="panel-body">
	<div class="row">
		<div class="col-sm-6 form-group">
			<label style="display:block;">
				<?php echo __('Helpdesk Status');?>:&nbsp;
				<i class="help-tip fa fa-question-circle" href="#helpdesk_status"></i>
			</label>
			<span>
			<label>
				<input type="radio" name="isonline"  value="1" <?php echo $config['isonline']?'checked="checked"':''; ?> />&nbsp;<b><?php echo __('Online'); ?></b>&nbsp;
			</label>
			<label>
				<input type="radio" name="isonline" value="0" <?php echo !$config['isonline']?'checked="checked"':''; ?> />&nbsp;<b><?php echo __('Offline'); ?></b>
			</label>
			&nbsp;&nbsp;<font class="text-danger"><?php echo !$config['isonline']?'Deskuss '.__('Offline'):''; ?></font>
			</span>
		</div>
		<div class="col-sm-6 form-group">
			<label class="required">
				<?php echo __('Helpdesk URL');?>:&nbsp;
				<i class="help-tip fa fa-question-circle" href="#helpdesk_url"></i>
			</label>
			<input type="text" class="form-control" size="10" name="helpdesk_url" value="<?php echo esc_attr($config['helpdesk_url']); ?>">
			<?php if(!empty($errors['helpdesk_url'])){ ?>
				<div class="alert-danger"><?php echo $errors['helpdesk_url']; ?></div>
			<?php } ?>
		</div>
	</div>
	   
	<div class="row">
		<div class="col-sm-6 form-group">
			<label class="required">
				<?php echo __('Helpdesk Name/Title');?>:&nbsp;
				<i class="help-tip fa fa-question-circle" href="#helpdesk_name_title"></i>
			</label>
			<input type="text" class="form-control" size="20" name="helpdesk_title" value="<?php echo esc_attr($config['helpdesk_title']); ?>">
			<?php if(!empty($errors['helpdesk_title'])){ ?>
				<div class="alert-danger"><?php echo $errors['helpdesk_title']; ?></div>
			<?php } ?>
		</div>
		<div class="col-sm-6 form-group">
			<label class="required">
				<?php echo __('Default Department');?>:&nbsp;
				<i class="help-tip fa fa-question-circle" href="#default_department"></i>
			</label>
			<select class="form-control" name="default_dept_id" data-quick-add="department">
				<option value="">&mdash; <?php echo __('Select Default Department');?> &mdash;</option>
				<?php
				if (($depts=Dept::getPublicDepartments())) {
					foreach ($depts as $id => $name) {
						$selected = ($config['default_dept_id']==$id)?'selected="selected"':''; ?>
						<option value="<?php echo esc_attr($id); ?>"<?php echo $selected; ?>><?php echo esc_html($name); ?></option>
					<?php
					}
				} ?>
				<option value="0" data-quick-add>&mdash; <?php echo __('Add New');?> &mdash;</option>
			</select>
			<?php if(!empty($errors['default_dept_id'])){ ?>
				<div class="alert-danger"><?php echo esc_html($errors['default_dept_id']); ?></div>
			<?php } ?>
		</div>
	</div>
		
	<div class="row">
		<div class="col-sm-6 form-group">
			<label>
				<?php echo __('Collision Avoidance Duration'); ?>:
			</label>
			<div class="input-group">
				<input type="text" class="form-control" name="autolock_minutes" size=4 value="<?php 
					echo esc_attr($config['autolock_minutes']); ?>">
				<div class="input-group-addon">
					<span><?php echo __('minutes'); ?></span>
				</div>
			</div>
			<?php if(!empty($errors['autolock_minutes'])){ ?>
				<div class="alert-danger"><?php echo esc_html($errors['autolock_minutes']); ?></div>
			<?php } ?>
		</div>
		<div class="col-sm-6 form-group">
			<label>
				<?php echo __('Default Page Size');?>:
				&nbsp;<i class="help-tip fa fa-question-circle" href="#default_page_size"></i>
			</label>
			<select class="form-control" name="max_page_size">
				<?php
				$pagelimit=$config['max_page_size'];
				for($i = 5; $i <= 50; $i += 5){
					?>
					<option <?php echo $config['max_page_size']==$i?'selected="selected"':''; ?> value="<?php echo $i; ?>"><?php echo $i; ?></option>
					<?php
				} ?>
			</select>
		</div> 
	</div>
	<div class="row">
		<div class="col-sm-6 form-group">
			<label>
				<?php echo __('Default Log Level');?>:&nbsp;
				<i class="help-tip fa fa-question-circle" href="#default_log_level"></i>
			</label>
			<select class="form-control" name="log_level">
				<option value=0 <?php echo $config['log_level'] == 0 ? 'selected="selected"':''; ?>><?php echo __('None (Disable Logger)');?></option>
				<option value=3 <?php echo $config['log_level'] == 3 ? 'selected="selected"':''; ?>> <?php echo __('DEBUG');?></option>
				<option value=2 <?php echo $config['log_level'] == 2 ? 'selected="selected"':''; ?>> <?php echo __('WARN');?></option>
				<option value=1 <?php echo $config['log_level'] == 1 ? 'selected="selected"':''; ?>> <?php echo __('ERROR');?></option>
			</select>
			<?php if(!empty($errors['log_level'])){ ?>
				<div class="alert-danger"><?php echo esc_html($errors['log_level']); ?></div>
			<?php } ?>
		</div>
		<div class="col-sm-6 form-group">
			<label>
				<?php echo __('Purge Logs');?>:&nbsp;
				<i class="help-tip fa fa-question-circle" href="#purge_logs"></i>
			</label>
			<select class="form-control" name="log_graceperiod">
				<option value=0 selected><?php echo __('Never Purge Logs');?></option>
				<?php
				for ($i = 1; $i <=12; $i++) {
					?>
					<option <?php echo $config['log_graceperiod']==$i?'selected="selected"':''; ?> value="<?php echo $i; ?>">
						<?php echo sprintf(_N('After %d month', 'After %d months', $i), $i);?>
					</option>
					<?php
				} ?>
			</select>	
		</div>
	</div>
	<div class="row">
		<div class="col-sm-6 form-group">
			<label style="display:block;">
				<?php echo __('Show Avatars'); ?>:&nbsp;
				<i class="help-tip fa fa-question-circle" href="#enable_avatars"></i>
			</label>
			<input type="checkbox" name="enable_avatars" <?php
				echo $config['enable_avatars'] ? 'checked="checked"' : ''; ?>>
				<?php echo __('Show Avatars on thread view.'); ?>
		</div>
		<div class="col-sm-6 form-group">
			<label style="display:block;">
				<?php echo __('Enable Rich Text'); ?>:&nbsp;
				<i class="help-tip fa fa-question-circle" href="#enable_richtext"></i>
			</label>
			<input type="checkbox" name="enable_richtext" <?php
				echo $config['enable_richtext'] ? 'checked="checked"' : ''; ?>>
				<?php echo __('Enable html in thread entries and email correspondence.'); ?>
		</div>
	</div>
</div>

<div class="panel-heading">
	<div class="panel-title table-caption">
		<?php echo __('Date and Time Options'); ?>&nbsp;
		<i class="help-tip fa fa-question-circle" href="#date_time_options"></i>
	</div>
</div>
<div class="panel-body">
<?php if (extension_loaded('intl')) { ?>
	<div class="row">
		<div class="col-sm-6 form-group">
			<label><?php echo __('Default Locale');?>:</label>
			<select class="form-control" name="default_locale">
				<option value=""><?php echo __('Use Language Preference'); ?></option>
				<?php
				foreach (Internationalization::allLocales() as $code=>$name) { ?>
					<option value="<?php echo esc_attr($code); ?>" <?php
					if ($code == $config['default_locale'])
						echo 'selected="selected"';
					?>><?php echo esc_html($name); ?></option>
					<?php
				} ?>
			</select>
		</div>
	</div>
<?php } ?>
	<div class="row">
		<div class="col-sm-6 form-group">
			<label>
				<?php echo __('Default Time Zone');?>:
			</label>
			<?php
			$TZ_TIMEZONE = $config['default_timezone'];
			$TZ_NAME = 'default_timezone';
			$TZ_ALLOW_DEFAULT = false;
			include STAFFINC_DIR.'templates/timezone.tmpl.php'; ?>
			<?php if(!empty($errors['default_timezone'])){ ?>
				<div class="alert-danger"><?php echo esc_html($errors['default_timezone']); ?></div>
			<?php } ?>
		</div>
		<div class="col-sm-6 form-group">
			<label><?php echo __('Date and Time Format');?>: </label>
			<select class="form-control" name="date_formats" onchange="javascript:
					$('#advanced-time').toggle($(this).find(':selected').val() == 'custom');
				">
				<?php foreach (array(
					'' => __('Locale Defaults'),
					'24' => __('Locale Defaults, 24-hour Time'),
					'custom' => '— '.__("Advanced").' —',
					) as $v=>$name) { ?>
						<option value="<?php echo esc_attr($v); ?>" <?php
						if ($v == $config['date_formats'])
							echo 'selected="selected"';
						?>><?php echo esc_html($name); ?></option>
				<?php } ?>
			</select>
		</div> 
	</div>
<div id="advanced-time" <?php if ($config['date_formats'] != 'custom')
	echo 'style="display:none;"'; ?>>
	<div class="row">
		<div class="col-sm-6 form-group">
			<label class="indented">
				<?php echo __('Time Format');?>:
			</label>
			<input type="text" name="time_format" value="<?php echo esc_attr($config['time_format']); ?>" class="date-format-preview form-control">
			<?php if(!empty($errors['time_format'])){ ?>
				<div class="alert-danger"><?php echo esc_html($errors['time_format']); ?></div>
			<?php } ?>
				<em><?php echo Format::time(null, false); ?></em>
			<span class="faded date-format-preview text-muted" data-for="time_format">
				<?php echo Format::time('now'); ?>
			</span>
		</div>
		<div class="col-sm-6 form-group">
			<label>
				<?php echo __('Date Format');?>:
			</label>
			<input type="text" name="date_format" value="<?php 
				echo esc_attr($config['date_format']); ?>" class="date-format-preview form-control">
			<?php if(!empty($errors['date_format'])){ ?>
				<div class="alert-danger"><?php echo esc_html($errors['date_format']); ?></div>
			<?php } ?>
			<em><?php echo Format::date(null, false); ?></em>
			<span class="faded date-format-preview text-muted" data-for="date_format">
				<?php echo Format::date('now'); ?>
			</span>
		</div>
	</div>    
	
	<div class="row">
		<div class="col-sm-6 form-group">
			<label>
				<?php echo __('Date and Time Format');?>:
			</label>
			<input type="text" name="datetime_format" value="<?php 
				echo esc_attr($config['datetime_format']); ?>" class="date-format-preview form-control">
			<?php if(!empty($errors['datetime_format'])){ ?>
				<div class="alert-danger"><?php echo esc_html($errors['datetime_format']); ?></div>
			<?php } ?>
			<em><?php echo Format::datetime(null, false); ?></em>
			<span class="faded date-format-preview text-muted" data-for="datetime_format">
				<?php echo Format::datetime('now'); ?>
			</span>
		</div>
		<div class="col-sm-6 form-group">
			<label>
				<?php echo __('Day, Date and Time Format');?>:
			</label>
			<input type="text" name="daydatetime_format" value="<?php 
				echo esc_attr($config['daydatetime_format']); ?>" class="date-format-preview form-control">
			<?php if(!empty($errors['daydatetime_format'])){ ?>
				<div class="alert-danger"><?php echo esc_html($errors['daydatetime_format']); ?></div>
			<?php } ?>
			<em><?php echo Format::daydatetime(null, false); ?></em>
			<span class="faded date-format-preview text-muted" data-for="daydatetime_format">
				<?php echo Format::daydatetime('now'); ?>
			</span>
		</div>
	</div>
</div>
</div> 

<div class="panel-heading">
	<div class="panel-title table-caption">
		<?php echo __('System Languages'); ?>&nbsp;
                <i class="help-tip fa fa-question-circle" href="#languages"></i>
	</div>
</div>
<div class="panel-body">
	<div class="row">
		<div class="col-sm-6 form-group">
			<label><?php echo __('Primary Language'); ?>:
				<i class="help-tip fa fa-question-circle" href="#primary_language"></i>
			</label>
			<?php
			$langs = Internationalization::availableLanguages(); ?>
			<select class="form-control" name="system_language">
				<option value="">&mdash; <?php echo __('Select a Language'); ?> &mdash;</option>
				<?php foreach($langs as $l) {
					$selected = ($config['system_language'] == $l['code']) ? 'selected="selected"' : ''; ?>
					<option value="<?php echo esc_attr($l['code']); ?>" <?php echo $selected;
					?>><?php echo Internationalization::getLanguageDescription($l['code']); ?></option>
			<?php } ?>
			</select>
			<?php if(!empty($errors['system_language'])){ ?>
				<div class="alert-danger"><?php echo $errors['system_language']; ?></div>
			<?php } ?>
		</div>
		<div class="col-sm-6 form-group">
			<label><?php echo __('Secondary Languages'); ?>:&nbsp;
				<i class="help-tip fa fa-question-circle" href="#secondary_language"></i>
			</label>
			<div id="secondary_langs" ><?php
			foreach ($cfg->getSecondaryLanguages() as $lang) {
				$info = Internationalization::getLanguageInfo($lang); ?>
				<div class="secondary_lang" style="cursor:move">
					<i class="fa fa-sort"></i>&nbsp;
					<span class="flag flag-<?php echo esc_attr($info['flag']); ?>"></span>&nbsp;
					<?php echo Internationalization::getLanguageDescription($lang); ?>
					<input type="hidden" name="secondary_langs[]" value="<?php echo esc_attr($lang); ?>"/>
					<div class="pull-right">
						<a href="#<?php echo esc_attr($lang); ?>" onclick="javascript:
						if (confirm('<?php echo esc_js(__('You sure?')); ?>')) {
							$(this).closest('.secondary_lang')
								.find('input').remove();
							$(this).closest('.secondary_lang').slideUp();
						}
						return false;
						"><i class="fa fa-trash"></i></a>
					</div>
				</div>
			<?php } ?>
			</div>
			<select class="form-control" name="add_secondary_language">
				<option value="">&mdash; <?php echo __('Add a Secondary Language'); ?> &mdash;</option>
				<?php foreach($langs as $l) {
					$selected = (($config['add_secondary_language'] ?? '') == $l['code']) ? 'selected="selected"' : '';
					if (!$selected && $l['code'] == $cfg->getPrimaryLanguage())
						continue;
					if (!$selected && in_array($l['code'], $cfg->getSecondaryLanguages()))
						continue; ?>
						<option value="<?php echo $l['code']; ?>" <?php echo $selected;
						?>><?php echo Internationalization::getLanguageDescription($l['code']); ?></option>
				<?php } ?>
			</select>
			<?php if(!empty($errors['add_secondary_language'])){ ?>
				<div class="alert-danger"><?php echo $errors['add_secondary_language']; ?></div>
			<?php } ?>
		</div>
	</div>
</div>

<div class="panel-heading">
	<div class="panel-title table-caption">
		<?php echo __('Attachments Storage and Settings');?>
	</div>
</div>
<div class="panel-body">
	<div class="row">
		<div class="col-sm-6 form-group">
			<label class="required">
				<?php echo __('Store Attachments'); ?>:
			</label>
			<select class="form-control" name="default_storage_bk"><?php
				if (($bks = FileStorageBackend::allRegistered())) {
					foreach ($bks as $char=>$class) {
						$selected = $config['default_storage_bk'] == $char
							? 'selected="selected"' : '';
						?><option <?php echo $selected; ?> value="<?php echo $char; ?>"
						><?php echo $class::$desc; ?></option><?php
					}
				}else{
					echo sprintf('<option value="">%s</option>', __('Select Storage Backend'));
				}?>
			</select>
			<?php if(!empty($errors['default_storage_bk'])){ ?>
				<div class="alert-danger"><?php echo $errors['default_storage_bk']; ?></div>
			<?php } ?>
		</div>
		<div class="col-sm-6 form-group">
			<label><?php echo __(
				// Maximum size for agent-uploaded files (via Admin panel)
				'Agent Maximum File Size');?>:&nbsp;
				<i class="help-tip fa fa-question-circle" href="#max_file_size"></i>
			</label>
			<select class="form-control" name="max_file_size">
				<option value="262144">&mdash; <?php echo __('Small'); ?> &mdash;</option>
				<?php $next = 512 << 10;
				$max = strtoupper(ini_get('upload_max_filesize'));
				$limit = (int) $max;
				if (!$limit) $limit = 2 << 20; # 2M default value
				elseif (strpos($max, 'K')) $limit <<= 10;
				elseif (strpos($max, 'M')) $limit <<= 20;
				elseif (strpos($max, 'G')) $limit <<= 30;
				while ($next <= $limit) {
					// Select the closest, larger value (in case the
					// current value is between two)
					$diff = $next - $config['max_file_size'];
					$selected = ($diff >= 0 && $diff < $next / 2)
						? 'selected="selected"' : ''; ?>
					<option value="<?php echo $next; ?>" <?php echo $selected;
						 ?>><?php echo Format::file_size($next);
						 ?></option><?php
					$next *= 2;
				}
				// Add extra option if top-limit in php.ini doesn't fall
				// at a power of two
				if ($next < $limit * 2) {
					$selected = ($limit == $config['max_file_size'])
						? 'selected="selected"' : ''; ?>
					<option value="<?php echo $limit; ?>" <?php echo $selected;
						 ?>><?php echo Format::file_size($limit);
						 ?></option><?php
				}
				?>
			</select>
			<?php if(!empty($errors['max_file_size'])){ ?>
				<div class="alert-danger"><?php echo $errors['max_file_size']; ?></div>
			<?php } ?>	
		</div>
	</div>
	 
	<div class="row">
		<div class="col-sm-6 form-group">
			<label style="display:block;"><?php echo __('Login required');?>:
				&nbsp;<i class="help-tip fa fa-question-circle" href="#files_req_auth"></i>
			</label>
			<input type="checkbox" name="files_req_auth" <?php
				if ($config['files_req_auth']) echo 'checked="checked"';
				?> />
				<?php echo __('Require login to view any attachments'); ?>
		</div>
	</div>
</div>
</div>
<p style="text-align:center;">
	<input type="submit" class="save btn btn-success" name="submit" value="<?php echo __('Save Changes');?>">
	<input type="reset" class="btn btn-info" value="<?php echo __('Reset');?> ">
</p>
<br />
</form>
</div>

<script type="text/javascript">
$(function() {
    $('#secondary_langs').sortable({
        cursor: 'move'
    });
    var prev = [];
    $('input.date-format-preview').keyup(function() {
        var name = $(this).attr('name'),
            div = $('span.date-format-preview[data-for='+name+']'),
            current = $(this).val();
        if (prev[name] && prev[name] == current)
            return;
        prev[name] = current;
        div.text("<?php echo __('Loading...');?>");
        $.get('ajax.php/config/date-format', {format:$(this).val()})
            .done(function(html) { div.html(html); });
    });
});
</script>

