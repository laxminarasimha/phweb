<?php
global $warnbtn;
$warnbtn = dsk_optGET('warnbtn');

require_once(INCLUDE_DIR . 'class.filter.php');

class FilterAjaxAPI extends AjaxController {
    function getFilterActionForm($type) {
	global $warnbtn;
        if (!($A = FilterAction::lookupByType($type)))
            Http::response(404, 'No such filter action type');

        $form = $A->getConfigurationForm();
        ?>		
        <div style="position:relative">
            <div class="pull-right" style="position:absolute;top:2px;right:2px;">
                <a class="growl_popup" data-type="warning" data-message='You must click "<?php echo $warnbtn; ?>" to apply the new changes' href="#" title="<?php echo __('clear'); ?>" onclick="javascript:
        if (!confirm(__('You sure?')))
            return false;
        $(this).closest('div.col-md-12').fadeOut(400, function() { $(this).hide(); });
        return false;"><i class="fa fa-trash text-danger"></i></a>
            </div>
        <?php
        include STAFFINC_DIR . 'templates/dynamic-form-simple.tmpl.php';
        ?>
        </div>
        <?php
    }

}
