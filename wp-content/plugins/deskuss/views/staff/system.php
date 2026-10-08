<?php
if(!defined('DSKADMININC') || !$thisstaff || !$thisstaff->isAdmin()) die('Access Denied');

$commit = (defined('GIT_VERSION') && GIT_VERSION && GIT_VERSION != '$git') ? GIT_VERSION : '?';

$extensions = array(
        'gd' => array(
            'name' => 'gdlib',
            'desc' => __('Used for image manipulation')
            ),
        'imap' => array(
            'name' => 'imap',
            'desc' => __('Used for email fetching')
            ),
        'xml' => array(
            'name' => 'xml',
            'desc' => __('XML API')
            ),
        'dom' => array(
            'name' => 'xml-dom',
            'desc' => __('Used for HTML email processing')
            ),
        'json' => array(
            'name' => 'json',
            'desc' => __('Improves performance creating and processing JSON')
            ),
        'mbstring' => array(
            'name' => 'mbstring',
            'desc' => __('Highly recommended for non western european language content')
            ),
        'phar' => array(
            'name' => 'phar',
            'desc' => __('Highly recommended for plugins and language packs')
            ),
        'intl' => array(
            'name' => 'intl',
            'desc' => __('Highly recommended for non western european language content')
            ),
        'fileinfo' => array(
            'name' => 'fileinfo',
            'desc' => __('Used to detect file types for uploads')
            ),
        'apcu' => array(
            'name' => 'APCu',
            'desc' => __('Improves overall performance')
            ),
        'Zend Opcache' => array(
            'name' => 'Zend Opcache',
            'desc' => __('Improves overall performance')
            ),
        );

?>
<div class="panel">
<div class="panel-heading">
	<div class="panel-title table-caption">
		<?php echo __('About this Deskuss Installation'); ?>
	</div>
</div>
<div class="panel-body">
<div class="table-responsive">
<table class="list table table-striped table-bordered" width="100%";>

<thead>
<tr>
	<th colspan="2" class="active">
		<?php echo __('Server Information'); ?>
	</th>
</tr>
</thead>

<tbody>
	<tr role="row">
		<td width="20%">
			<?php echo __('Deskuss Version'); ?>
		</td>
		<td>
			<span class="ltr">
				<?php echo sprintf("%s (%s)", esc_html(THIS_VERSION), esc_html(trim($commit))); ?>
			</span>
		</td>
	</tr>
	<tr>
		<td><?php echo __('Web Server Software'); ?></td>
		<td><span class="ltr"><?php echo esc_html($_SERVER['SERVER_SOFTWARE']); ?></span></td>
	</tr>
	<tr>
		<td><?php echo __('MySQL Version'); ?></td>
		<td><span class="ltr"><?php echo esc_html(db_version()); ?></span></td>
	</tr>
	<tr>
		<td><?php echo __('PHP Version'); ?></td>
		<td><span class="ltr"><?php echo esc_html(phpversion()); ?></span></td>
	</tr>
</tbody>
</table>

<table class="list table table-striped table-bordered" width="80%">

<thead>
	<tr>
		<th colspan="2" class="active"><?php echo __('PHP Extensions'); ?></th>
	</tr>
</thead>

<tbody>
	<?php
	foreach($extensions as $ext => $info) { ?>
		<tr>
			<td width="20%"><?php echo esc_html($info['name']); ?></td>
			<td><?php
				echo sprintf('<i class="fa fa-%s"></i> %s',
						extension_loaded($ext) ? 'check' : 'warning', 
						esc_html($info['desc']));
				?>
			</td>
		</tr>
	<?php
	} ?>
</tbody>
</table>

<style>
.fa-warning{
	color:red;
}
.fa-check{
	color:green;
}
</style>

<table class="list table table-striped table-bordered">
<thead>
	<tr>
		<th colspan="2" class="active"><?php echo __('PHP Settings'); ?></th>
	</tr>
</thead>
<tbody>
	<tr>
		<td width="20%"><span class="ltr">cgi.fix_pathinfo</span></td>
		<td><i class="fa fa-<?php
				echo ini_get('cgi.fix_pathinfo') == 1 ? 'check' : 'warning'; ?>"></i>
				<span class="faded"><?php echo __('"1" is recommended if AJAX is not working'); ?></span>
		</td>
	</tr>
	<tr>
		<td><span class="ltr">date.timezone</span></td>
		<td><i class="fa fa-<?php
				echo ini_get('date.timezone') ? 'check' : 'warning'; ?>"></i>
				<span class="faded"><?php
					echo ini_get('date.timezone')
					?: __('Setting default timezone is highly recommended');
				?></span>
		</td>
	</tr>
</tbody>
</table>

<table class="list table table-striped table-bordered" width="100%";>

<thead>
	<tr>
		<th colspan="2" class="active"><?php echo __('Database Information and Usage'); ?></th>
	</tr>
</thead>

<tbody>
	<tr>
		<td width="20%"><?php echo __('Schema'); ?></td>
		<td><?php echo sprintf('<span class="ltr">%s (%s)</span>', esc_html(DB_NAME), esc_html(DB_HOST)); ?> </td>
	</tr>
	<tr>
		<td><?php echo __('Schema Signature'); ?></td>
		<td><?php echo esc_html($cfg->getSchemaSignature()); ?> </td>
	</tr>
	<tr>
		<td><?php echo __('Space Used'); ?></td>
		<td><?php
			$sql = 'SELECT sum( data_length + index_length ) / 1048576 total_size
				FROM information_schema.TABLES WHERE table_schema = '
				.db_input(DB_NAME);
			$space = db_result(db_query($sql));
			echo sprintf('%.2f MiB', $space); ?>
		</td>
	</tr>
	<tr>
		<td><?php echo __('Space for Attachments'); ?></td>
		<td><?php
			$sql = 'SELECT
						(DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024
					FROM
						information_schema.TABLES
					WHERE
						TABLE_SCHEMA = "'.DB_NAME.'"
					AND
						TABLE_NAME = "'.FILE_CHUNK_TABLE.'"
					ORDER BY
						(DATA_LENGTH + INDEX_LENGTH)
					DESC';
			$space = db_result(db_query($sql));
			echo sprintf('%.2f MiB', $space); ?>
		</td>
	</tr>
	<tr>
		<td><?php echo __('Timezone'); ?></td>
		<td><?php echo esc_html($dbtz = db_timezone());
			if($cfg->getDbTimezone() != $dbtz){
				echo ' ('.sprintf(__('Interpreted as %s'), esc_html($cfg->getDbTimezone())).')';
			}
			?>
		</td>
	</tr>
</tbody>
</table>
</div>
</div>
</div>
<br/>
<div class="panel">
<div class="panel-heading">
	<div class="panel-title table-caption">
		<?php echo __('Installed Language Packs'); ?>
	</div>
</div>
<div class="panel-body">
<?php
	foreach(Internationalization::availableLanguages() as $info){
		//dsk_r_print($info);
		$p = $info['path'];
		if ($info['phar'])
			$p = 'phar://' . $p;
		$manifest = (file_exists($p . '/MANIFEST.php')) ? (include $p . '/MANIFEST.php') : null;
		//dsk_r_print($manifest); ?>
		<div>
			<strong><?php echo esc_html(Internationalization::getLanguageDescription($info['code'])); ?></strong>
			<?php if ($manifest) { ?>
			&mdash; <?php echo esc_html($manifest['Language']); ?>
			<?php } ?>

		</div>
		<div>
			<?php echo sprintf('<code>%s</code> — %s', esc_html($info['code']),
				esc_html(str_replace(ROOT_DIR, '', $info['path']))); ?>
			<?php if($manifest){ ?>
				<br />
				<strong><?php echo __('Version'); ?>:</strong> <?php echo esc_html($manifest['Version']);
				?>, <?php echo sprintf(__('for version %s'),
				'v'.($manifest['Phrases-Version'] ?: '1.9')); ?>
				<br />
				<strong><?php echo __('Built'); ?>:</strong> <?php echo esc_html($manifest['Build-Date']); ?>
			<?php } ?>
		</div>
		<hr />
	<?php
	} ?>
</div>
</div>
