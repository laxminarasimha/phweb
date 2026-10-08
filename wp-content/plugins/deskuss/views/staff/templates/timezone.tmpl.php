<?php
$TZ_NAME = $TZ_NAME ?? 'timezone';
$TZ_ALLOW_DEFAULT = $TZ_ALLOW_DEFAULT ?? true;
$TZ_PLACEHOLDER = $TZ_PLACEHOLDER ?? __('System Default');
$TZ_TIMEZONE = $TZ_TIMEZONE ?? '';
?>

<div class="input-group">
	<select class="form-control" name="<?php echo $TZ_NAME; ?>" id="timezone-dropdown"
        data-placeholder="<?php echo $TZ_PLACEHOLDER; ?>">
	<?php if($TZ_ALLOW_DEFAULT){ ?>
			<option value=""></option>
	<?php }
	foreach (DateTimeZone::listIdentifiers() as $zone) { ?>
		<option value="<?php echo $zone; ?>" <?php
		if ($TZ_TIMEZONE == $zone)
			echo 'selected="selected"';
		?>><?php echo str_replace(array('/', '_'), array(' / ', ' '), $zone); ?></option>
	<?php } ?>
    </select>
	<span class="input-group-addon">
	<input type="button" value="<?php echo __('Auto Detect'); ?>" class="btn" style="border:0px;padding:initial;vertical-align:middle;" onclick="javascript:
$('head').append($('<script>').attr('src', '<?php
    echo esc_url(DESKUSS_MEDIA_URL); ?>/js/jstz.min.js'));
var recheck = setInterval(function() {
    if (window.jstz !== undefined) {
        clearInterval(recheck);
        var zone = jstz.determine();
        $('#timezone-dropdown').val(zone.name()).trigger('change');
    }
}, 100);
return false;">&nbsp;&nbsp;<i class="fa fa-map-marker"></i> 
</span>
</div>
<script type="text/javascript">
$(function() {
    $('#timezone-dropdown').select2({
        allowClear: <?php echo $TZ_ALLOW_DEFAULT ? 'true' : 'false'; ?>,
        width: '300px'
    });
});
</script>
