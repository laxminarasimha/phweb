	<hr class="page-wide-block">
	<footer>
		<p>Copyright &copy; <?php echo (date('Y') != '2018') ? '2018-' : ''; ?><?php echo date('Y'); ?> &nbsp; <a href="https://deskuss.com" target="_blank"><?php echo esc_html( (string) $dsk->company ?: 'deskuss.com' ); ?></a> - All rights reserved.</p>
	</footer>
</div>
<div id="overlay"></div>
	
<script language="javascript" src="<?php echo dsk_js_url('bootstrap.min.js', 'deskuss.js', 'popover.js', 'tooltip.js', 'modal.js', 'file.js', 'navbar.js', 'footer.js', 'perfect-scrollbar.js', 'datepicker.js', 'summernote.js', 'summernote-extensions.js', 'dropzone.js', 'growl.js', 'enduser.js', 'filedrop.field.js'); ?>." type="text/javascript"> </script>

<!-- <script type="text/javascript" src="<?php echo DESKUSS_ROOT_PATH; ?>assets/js/filedrop.field.js?<?php echo THIS_VERSION;?>"></script> -->

<script>
pxInit.push(function() {
	load_summernote();
});
</script>


<script type="text/javascript">
pxInit.unshift(function() {
  $('#px-nav').pxNav();
  $('#px-footer').pxFooter();
});

for (var i = 0, len = pxInit.length; i < len; i++) {
  pxInit[i].call(null);
}
</script>

<div id="loading">
    <h3 class="m-y-1 font-weight-normal"><?php echo __('Loading ...');?>
	<br />
	<img src="<?php echo DESKUSS_MEDIA_URL.'/images/loading.gif'; ?>" width="120" /><br />
	</h3>
	
</div>
<?php
if (($lang = Internationalization::getCurrentLanguage()) && $lang != 'en_US') { ?>
    <script type="text/javascript" src="ajax.php/i18n/<?php
        echo $lang; ?>/js"></script>
<?php } ?>
<script type="text/javascript">
    getConfig().resolve(<?php
        include INCLUDE_DIR . 'ajax.config.php';
        $api = new ConfigAjaxAPI();
        print $api->client(false);
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
// REQUIRED FOR TINYMCE + WORDPRESS EDITOR
wp_print_footer_scripts();
?>
</body>
</html>
