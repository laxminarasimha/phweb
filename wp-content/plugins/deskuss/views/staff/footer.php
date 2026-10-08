
	<script language="javascript" src="<?php echo esc_url(dsk_js_url('select2.min.js', 'jquery.translatable.js', 'bootstrap-typeahead.js', 'bootstrap.min.js', 'deskuss.js', 'popover.js', 'tooltip.js', 'modal.js', 'file.js', 'navbar.js', 'footer.js', 'perfect-scrollbar.js', 'datepicker.js', 'summernote.js', 'summernote-extensions.js', 'dropzone.js', 'growl.js', 'admin.js', 'filedrop.field.js', 'nivo-lightbox.min.js')); ?>." type="text/javascript"> </script>
	<script src="<?php echo esc_url(DESKUSS_MEDIA_URL); ?>/admin/js/chartjs.min.js?<?php echo esc_attr(THIS_VERSION);?>"></script>
	

	
	<script>
	pxInit.push(function() {
		load_summernote();
		$('.datepicker-range').datepicker();
	});
    </script>

	<script>
		var ajax_nonce = "<?php echo esc_js(defined('DESKUSS_AJAX_NONCE') ? DESKUSS_AJAX_NONCE : wp_create_nonce('deskuss_nonce')); ?>";
		var ajaxurl = "<?php echo esc_js(defined('DESKUSS_AJAX_URL') ? DESKUSS_AJAX_URL : admin_url('admin-ajax.php')); ?>";
	</script>

	<?php if (!isset($_SERVER['HTTP_X_PJAX'])) { ?>

	<hr class="page-wide-block">
	<footer>
		<?php wp_print_footer_scripts(); ?>
		<span class="text-muted">Copyright &copy; <?php echo (date('Y') != '2018') ? '2018-' : ''; ?><?php echo date('Y'); ?>&nbsp;<a href="https://deskuss.com" target="_blank"><?php echo esc_html( (string) $dsk->company ?: 'deskuss.com' ); ?></a> - All Rights Reserved.</span>
	</footer>
	
<?php

if(is_object($thisstaff) && $thisstaff->isStaff()) { ?>
    <div>
        <!-- Do not remove <img src="autocron.php" alt="" width="1" height="1" border="0" /> or your auto cron will cease to function -->
        <img src="<?php echo esc_url(DESKUSS_ROOT_PATH); ?>admin/autocron.php" alt="" width="1" height="1" border="0" />
        <!-- Do not remove <img src="autocron.php" alt="" width="1" height="1" border="0" /> or your auto cron will cease to function -->
    </div>
<?php
} 

?>
</div>
<div id="overlay"></div>
<div id="loading">
    <h3 class="m-y-1 font-weight-normal"><?php echo __('Loading ...');?>
	<br /><img src="<?php echo esc_url(DESKUSS_MEDIA_URL.'/images/loading.gif'); ?>" width="120" /><br />
	</h3>
</div>
<div class="dialog draggable" style="display:none;" id="popup">
    <div class="body"></div>
</div>
<div style="display:none;" class="dialog panel panel-danger" id="alert">
    <div class="panel-heading">
		<div class="drag-handle panel-title" ><i class="fa fa-exclamation-triangle">&nbsp;</i> <span id="title"></span><a class="close_me pull-right" href=""><i class="fa fa-remove"></i></a>
		</div>
	</div>
	<div class="panel-body">
		<div id="body" style="min-height: 20px;"></div>
        <span class="buttons pull-right">
            <input type="button" value="<?php echo __('OK');?>" class="close_me btn ok">
        </span>
	 </div>
</div>

<script type="text/javascript">
pxInit.unshift(function() {
  $('#px-nav').pxNav();
  $('#px-footer').pxFooter();
});

for (var i = 0, len = pxInit.length; i < len; i++) {
  pxInit[i].call(null);
}
</script>

<script type="text/javascript">
    getConfig().resolve(<?php
        include INCLUDE_DIR . 'ajax.config.php';
        $api = new ConfigAjaxAPI();
        print $api->admin(false);
    ?>);
	
	// Heartbeat
	function heartbeat(){
		$.ajax({
			'url': 'heartbeat.php',
			'success': function(data){
				if(data && data == 1){
					setTimeout(heartbeat, 60 * 1000); // Every 60 seconds
				}
			}
		});
	}

	heartbeat(); // initialize it
</script>
<?php
if ($thisstaff
        && ($lang = $thisstaff->getLanguage())
        && 0 !== strcasecmp($lang, 'en_US')) { ?>
    <script type="text/javascript" src="ajax.php/i18n/<?php
        echo esc_attr($thisstaff->getLanguage()); ?>/js"></script>
<?php } ?>
<?php wp_enqueue_script('wp-color-picker');?>
</body>
</html>
<?php } # endif X_PJAX ?>
