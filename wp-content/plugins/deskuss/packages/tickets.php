<?php


// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
//////////////////////////////////////////////////////////////
//===========================================================
// SOFTACULOUS PROJECT
//===========================================================
// Inspired by the DESIRE to be the BEST OF ALL
// ----------------------------------------------------------
// Started by: Pulkit and Brijesh
// ----------------------------------------------------------
// Please Read the Terms of use at http://deskuss.com
// ----------------------------------------------------------
//===========================================================
// (c)Softaculous Ltd.
//===========================================================
//////////////////////////////////////////////////////////////

require('secure.inc.php');
if(!is_object($thisclient) || !$thisclient->getId()) die('Access denied'); //Double check again.

if ($thisclient->isGuest())
    $_REQUEST['id'] = $thisclient->getTicketId();

require_once(INCLUDE_DIR.'class.ticket.php');
require_once(INCLUDE_DIR.'class.json.php');
$ticket=null;
if($_REQUEST['id']) {
    if (!($ticket = Ticket::lookup($_REQUEST['id']))) {
        $errors['err']=__('Unknown or invalid ticket ID.');
    } elseif(!$ticket->checkUserAccess($thisclient)) {
        $errors['err']=__('Unknown or invalid ticket ID.'); //Using generic message on purpose!
        $ticket=null;
    }
}

if (!$ticket && $thisclient->isGuest())
    Http::redirect('view.php');

$tform = TicketForm::objects()->one()->getForm();
$messageField = $tform->getField('message');
$attachments = $messageField->getWidget()->getAttachments();

//Process post...depends on $ticket object above.
if ($_POST && is_object($ticket) && $ticket->getId()) {
    $errors=array();
    switch(strtolower($_POST['a'])){
    case 'edit':
        if(!$ticket->checkUserAccess($thisclient) //double check perm again!
                || $thisclient->getId() != $ticket->getUserId())
            $errors['err']=__('Access Denied. Possibly invalid ticket ID');
        else {
            $forms=DynamicFormEntry::forTicket($ticket->getId());
            $changes = array();
            foreach ($forms as $form) {
                $form->filterFields(function($f) { return !$f->isStorable(); });
                $form->setSource($_POST);
                if (!$form->isValid())
                    $errors = array_merge($errors, $form->errors());
            }
        }
        if (!$errors) {
            foreach ($forms as $f) {
                $changes += $f->getChanges();
                $f->save();
            }
            if ($changes){
				
				$do_log = apply_filters('log_event_form_fields_changed', true);
				
				if($do_log){
					$ticket->logEvent('edited', array('fields' => $changes));
				}
			}
            $_REQUEST['a'] = null; //Clear edit action - going back to view.
        }
        break;
    case 'reply':
        if(!$ticket->checkUserAccess($thisclient)) //double check perm again!
            $errors['err']=__('Access Denied. Possibly invalid ticket ID');

        $_POST['message'] = ThreadEntryBody::clean($_POST['message']);
        if (!$_POST['message'])
            $errors['message'] = __('Message required');

        if(!$errors) {
            //Everything checked out...do the magic.
            $vars = array(
                    'userId' => $thisclient->getId(),
                    'poster' => (string) $thisclient->getName(),
                    'message' => $_POST['message']
                    );
            $vars['cannedattachments'] = $attachments->getClean();
            if (isset($_POST['draft_id']))
                $vars['draft_id'] = $_POST['draft_id'];

            if(($msgid=$ticket->postMessage($vars, 'Web'))) {
                $msg=__('Message Posted Successfully');
                // Cleanup drafts for the ticket. If not closed, only clean
                // for this staff. Else clean all drafts for the ticket.
                Draft::deleteForNamespace('ticket.client.' . $ticket->getId());
                // Drop attachments
                $attachments->reset();
                $attachments->getForm()->setSource(array());
            } else {
                $errors['err'] = sprintf('%s %s',
                    __('Unable to post the message.'),
                    __('Correct any errors below and try again.'));
            }

        } elseif(!$errors['err']) {
            $errors['err'] = __('Correct any errors below and try again.');
        }
        break;
    default:
        $errors['err']=__('Unknown action');
    }
}

$nav->setActiveNav('tickets');
if($ticket && $ticket->checkUserAccess($thisclient)) {
    if (isset($_REQUEST['a'])) {
		if($_REQUEST['a'] == 'edit' && $ticket->hasClientEditableFields()){
			$inc = 'edit.php';
			if (!$forms) $forms=DynamicFormEntry::forTicket($ticket->getId());
			// Auto add new fields to the entries
			foreach ($forms as $f) {
				$f->filterFields(function($f) { return !$f->isStorable(); });
				$f->addMissingFields();
			}
		}elseif($_REQUEST['a'] == 'close'){
			
			$inc='view.php';
			
			if($thisclient->getId() == $ticket->getUserId()){
			
				if($ticket->getStatusID() == 3){
					$tfail = __('Ticket is already closed');
				}else{
					$ret = $ticket->setStatusId(3);
					if(!empty($ret)){
						$tmsg = __('Ticket has been closed successfully');
						apply_filters('post_ticket_closed', $ticket->getId());
					}else{
						$tfail = __('Failed to close the ticket');
					}
				}
				
			}else{
				$tfail = __('You do not have permissions to close this ticket');
			}
		}
    }
    else
        $inc='view.php';
} elseif($thisclient->getNumTickets($thisclient->canSeeOrgTickets())) {
    $inc='tickets.php';
} else {
    $nav->setActiveNav('new');
    $inc='open.php';
}
require deskuss_get_header();
require deskuss_load_view($inc);
print $tform->getMedia();
require deskuss_get_footer();
