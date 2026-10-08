<?php
global $thisstaff, $ticket;
// Map states to actions
$actions= array(
        'closed' => array(
            'icon'  => 'fa fa-check-circle-o',
            'action' => 'close',
            'href' => 'tickets.php'
            ),
        'pending' => array(
            'icon'  => 'fa fa-clock-o',
            'action' => 'pending'
            ),
        'open' => array(
            'icon'  => 'fa fa-undo',
            'action' => 'reopen'
            ),
        );

$states = array('open');
if ($thisstaff->getRole($ticket ? $ticket->getDeptId() : null)->hasPerm(TicketModel::PERM_CLOSE)
        && (!$ticket || !$ticket->getMissingRequiredFields()))
    $states = array_merge($states, array('pending', 'closed'));

$statusId = $ticket ? $ticket->getStatusId() : 0;
$nextStatuses = array();
foreach (TicketStatusList::getStatuses(
            array('states' => $states)) as $status) {
    if (!isset($actions[$status->getState()])
            || $statusId == $status->getId())
        continue;
    $nextStatuses[] = $status;
}

if (!$nextStatuses)
    return;
?>
<div class="btn-group">
	<button class="action-button btn btn-outline dropdown-toggle" data-toggle="dropdown" title="<?php echo __('Change Status'); ?>"> <i class="fa fa-caret-down pull-right"></i><i class="fa fa-flag"></i>
		<a class="tickets-action" href="#statuses"></a>
	</button>
    <ul class="dropdown-menu">
<?php foreach ($nextStatuses as $status) { ?>
        <li>
            <a class="no-pjax <?php
                echo $ticket? 'ticket-action' : 'tickets-action'; ?>"
                href="<?php
                    echo sprintf('#%s/status/%s/%d',
                            $ticket ? ('tickets/'.$ticket->getId()) : 'tickets',
                            $actions[$status->getState()]['action'],
                            $status->getId()); ?>"
                <?php
                if (isset($actions[$status->getState()]['href']))
                    echo sprintf('data-redirect="%s"',
                            $actions[$status->getState()]['href']);

                ?>
                ><i class="<?php
                        echo $actions[$status->getState()]['icon'] ?: 'fa fa-tag';
                    ?>"></i> <?php
                        echo __($status->getName()); ?></a>
        </li>
    <?php
    } ?>
    </ul>
</div>
