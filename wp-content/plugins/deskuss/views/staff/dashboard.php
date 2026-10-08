<?php
$report = new OverviewReport($_POST['start'] ?? null, $_POST['period'] ?? null);
$plots = $report->getPlotData();

// State display labels
$state_labels = array(
    'created'     => __('Created'),
    'closed'      => __('Closed'),
    'reopened'    => __('Reopened'),
    'assigned'    => __('Assigned'),
    'overdue'     => __('Overdue'),
    'transferred' => __('Transferred'),
);

// Event icon mapping (FontAwesome)
$event_icons = array(
    'created'     => 'fa-plus-circle',
    'closed'      => 'fa-check-circle',
    'reopened'    => 'fa-undo',
    'assigned'    => 'fa-hand-right',
    'overdue'     => 'fa-exclamation-triangle',
    'transferred' => 'fa-exchange',
    'edited'      => 'fa-pencil',
    'collab'      => 'fa-user-plus',
    'resent'      => 'fa-envelope',
);

// Event color mapping (Bootstrap-ish)
$event_colors = array(
    'created'     => '#5cb85c',
    'closed'      => '#777',
    'reopened'    => '#f0ad4e',
    'assigned'    => '#5bc0de',
    'overdue'     => '#d9534f',
    'transferred' => '#9b59b6',
    'edited'      => '#999',
    'collab'      => '#34495e',
    'resent'      => '#16a085',
);
?>

<!-- Load Chart.js -->
<script type="text/javascript" src="<?php echo esc_url(DESKUSS_MEDIA_URL); ?>/admin/js/chartjs.min.js?035fd0a"></script>
<script type="text/javascript" src="<?php echo esc_url(DESKUSS_MEDIA_URL); ?>/admin/js/dashboard.inc.js?035fd0a"></script>

<link rel="stylesheet" type="text/css" href="<?php echo esc_url(DESKUSS_MEDIA_URL); ?>/admin/css/dashboard.css?035fd0a"/>

<form method="post" action="dashboard.php" id="dsk-dashboard-form">
<div id="basic_search">
    <div class="dsk-period-bar">
            <?php echo csrf_token(); ?>
            <div class="dsk-quick-range">
                <button type="button" class="dsk-range-btn" data-range="today"><?php echo __('Today');?></button>
                <button type="button" class="dsk-range-btn" data-range="week"><?php echo __('This Week');?></button>
                <button type="button" class="dsk-range-btn" data-range="month"><?php echo __('This Month');?></button>
                <button type="button" class="dsk-range-btn" data-range="lastmonth"><?php echo __('Last Month');?></button>
            </div>
            <div class="dsk-custom-range">
                <label>
                    <?php echo __('Custom'); ?>:
                    <input type="text" class="dp input-medium search-query"
                        name="start" id="dsk-start-date" placeholder="<?php echo __('Start date');?>"
                        value="<?php echo Format::htmlchars($report->getStartDate()); ?>" />
                </label>
                <label>
                    <?php echo __('period');?>:
                    <select name="period" id="dsk-period">
                        <option value="now" selected="selected"><?php echo __('Up to today');?></option>
                        <option value="+7 days"><?php echo __('One Week');?></option>
                        <option value="+14 days"><?php echo __('Two Weeks');?></option>
                        <option value="+1 month"><?php echo __('One Month');?></option>
                        <option value="+3 months"><?php echo __('One Quarter');?></option>
                    </select>
                </label>
                <button class="dsk-btn dsk-btn-primary" type="submit">
                    <i class="fa fa-refresh"></i> <?php echo __('Refresh');?>
                </button>
            </div>
            <i class="help-tip fa fa-question-circle" href="#report_timeframe"></i>
    </div>
</div>
</form>

<!-- KPI TILES -->
<div class="dsk-kpi-grid">
    <a class="dsk-kpi-tile dsk-kpi-open" href="tickets.php?status=open">
        <div class="dsk-kpi-icon"><i class="fa fa-folder-open"></i></div>
        <div class="dsk-kpi-body">
            <div class="dsk-kpi-number"><?php echo (int)$kpi['open']; ?></div>
            <div class="dsk-kpi-label"><?php echo __('Open Tickets');?></div>
        </div>
    </a>
    <a class="dsk-kpi-tile dsk-kpi-assigned" href="tickets.php?status=assigned">
        <div class="dsk-kpi-icon"><i class="fa fa-user"></i></div>
        <div class="dsk-kpi-body">
            <div class="dsk-kpi-number"><?php echo (int)$kpi['assigned']; ?></div>
            <div class="dsk-kpi-label"><?php echo __('Assigned to Me');?></div>
        </div>
    </a>
    <a class="dsk-kpi-tile dsk-kpi-overdue" href="tickets.php?status=overdue">
        <div class="dsk-kpi-icon"><i class="fa fa-exclamation-triangle"></i></div>
        <div class="dsk-kpi-body">
            <div class="dsk-kpi-number"><?php echo (int)$kpi['overdue']; ?></div>
            <div class="dsk-kpi-label"><?php echo __('Overdue');?></div>
        </div>
    </a>
    <a class="dsk-kpi-tile dsk-kpi-unanswered" href="tickets.php?status=unanswered">
        <div class="dsk-kpi-icon"><i class="fa fa-question-circle"></i></div>
        <div class="dsk-kpi-body">
            <div class="dsk-kpi-number"><?php echo (int)$kpi['unanswered']; ?></div>
            <div class="dsk-kpi-label"><?php echo __('Unanswered');?></div>
        </div>
    </a>
    <a class="dsk-kpi-tile dsk-kpi-new" href="tickets.php?status=open&a=search">
        <div class="dsk-kpi-icon"><i class="fa fa-plus"></i></div>
        <div class="dsk-kpi-body">
            <div class="dsk-kpi-number"><?php echo (int)$kpi['new_today']; ?></div>
            <div class="dsk-kpi-label"><?php echo __('New Today');?></div>
        </div>
    </a>
    <a class="dsk-kpi-tile dsk-kpi-closed" href="tickets.php?status=closed">
        <div class="dsk-kpi-icon"><i class="fa fa-check"></i></div>
        <div class="dsk-kpi-body">
            <div class="dsk-kpi-number"><?php echo (int)$kpi['closed_today']; ?></div>
            <div class="dsk-kpi-label"><?php echo __('Closed Today');?></div>
        </div>
    </a>
</div>

<!-- Quick actions -->
<div class="dsk-quick-actions">
    <a class="dsk-btn dsk-btn-success" href="tickets.php?a=open">
        <i class="fa fa-plus-circle"></i> <?php echo __('New Ticket');?>
    </a>
    <a class="dsk-btn dsk-btn-info" href="tickets.php?status=open">
        <i class="fa fa-list"></i> <?php echo __('Open Queue');?>
    </a>
    <a class="dsk-btn dsk-btn-warning" href="tickets.php?status=assigned">
        <i class="fa fa-user"></i> <?php echo __('My Tickets');?>
    </a>
    <a class="dsk-btn dsk-btn-danger" href="tickets.php?status=overdue">
        <i class="fa fa-exclamation-triangle"></i> <?php echo __('Overdue');?>
    </a>
</div>

<div class="dsk-clear"></div>

<!-- Charts row -->
<div class="dsk-dashboard-row">
    <div class="dsk-panel dsk-col-8">
        <div class="dsk-panel-header">
            <h3><?php echo __('Ticket Activity');?>&nbsp;<i class="help-tip fa fa-question-circle" href="#ticket_activity"></i></h3>
        </div>
        <div class="dsk-panel-body">
            <div class="dsk-chart-wrapper">
                <canvas id="dsk-activity-chart" height="110"></canvas>
            </div>
        </div>
    </div>
    <div class="dsk-panel dsk-col-4">
        <div class="dsk-panel-header">
            <h3><?php echo __('By Department');?></h3>
        </div>
        <div class="dsk-panel-body">
            <div class="dsk-chart-wrapper">
                <canvas id="dsk-dept-chart" height="160"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent activity + Statistics -->
<div class="dsk-dashboard-row">
    <div class="dsk-panel dsk-col-4">
        <div class="dsk-panel-header">
            <h3><i class="fa fa-clock-o"></i> <?php echo __('Recent Activity');?></h3>
        </div>
        <div class="dsk-panel-body">
            <?php if (empty($recent_events)) { ?>
                <p class="dsk-empty"><?php echo __('No recent activity.');?></p>
            <?php } else { ?>
                <ul class="dsk-activity-feed">
                <?php foreach ($recent_events as $ev) {
                    $icon  = isset($event_icons[$ev['state']]) ? $event_icons[$ev['state']] : 'fa-circle-o';
                    $color = isset($event_colors[$ev['state']]) ? $event_colors[$ev['state']] : '#999';
                    $label = isset($state_labels[$ev['state']]) ? $state_labels[$ev['state']] : ucfirst($ev['state']);
                    $subject = $ev['subject'] ?: '(no subject)';
                    $ts_display = Format::datetime($ev['timestamp']);
                ?>
                    <li class="dsk-activity-item">
                        <span class="dsk-activity-icon" style="background:<?php echo esc_attr($color); ?>">
                            <i class="fa <?php echo esc_attr($icon); ?>"></i>
                        </span>
                        <div class="dsk-activity-body">
                            <div class="dsk-activity-title">
                                <span class="dsk-activity-state"><?php echo Format::htmlchars($label); ?></span>
                                <a href="tickets.php?id=<?php echo (int)$ev['ticket_id']; ?>">
                                    #<?php echo Format::htmlchars($ev['number']); ?>
                                </a>
                            </div>
                            <div class="dsk-activity-subject"><?php echo Format::htmlchars($subject); ?></div>
                            <div class="dsk-activity-meta">
                                <?php echo Format::htmlchars($ev['username']); ?>
                                &middot; <?php echo $ts_display; ?>
                            </div>
                        </div>
                    </li>
                <?php } ?>
                </ul>
            <?php } ?>
        </div>
    </div>

    <div class="dsk-panel dsk-col-8">
        <div class="dsk-panel-header">
            <h3><?php echo __('Statistics'); ?>&nbsp;<i class="help-tip fa fa-question-circle" href="#statistics"></i></h3>
            <p class="dsk-panel-subtitle"><?php echo __('Statistics of tickets organized by department and agent.');?></p>
        </div>
        <div class="dsk-panel-body">
        <ul class="dsk-stat-tabs clean tabs">
        <?php
        $first = true;
        $groups = $report->enumTabularGroups();
        foreach ($groups as $g=>$desc) { ?>
            <li class="<?php echo $first ? 'active' : ''; ?>"><a href="#<?php echo Format::slugify($g); ?>"
                ><?php echo Format::htmlchars($desc); ?></a></li>
        <?php
            $first = false;
        } ?>
        </ul>

        <?php
        $first = true;
        foreach ($groups as $g=>$desc) {
            $data = $report->getTabularData($g); ?>
            <div class="tab_content <?php echo (!$first) ? 'hidden' : ''; ?>" id="<?php echo Format::slugify($g); ?>">
            <table class="dsk-stats-table"><tbody><tr>
        <?php
            foreach ($data['columns'] as $j=>$c) { ?>
                <th <?php if ($j === 0) echo 'class="dsk-col-name"'; ?>><?php echo Format::htmlchars($c); ?></th>
        <?php
            } ?>
            </tr></tbody>
            <tbody>
        <?php
            foreach ($data['data'] as $i=>$row) {
                echo '<tr>';
                foreach ($row as $j=>$td) {
                    if ($j === 0) { ?>
                        <th class="dsk-col-name"><?php echo Format::htmlchars($td); ?></th>
            <?php   }
                    else { ?>
                        <td><?php echo Format::htmlchars($td); ?></td>
            <?php       }
                }
                echo '</tr>';
            }
            $first = false; ?>
            </tbody></table>
            <div class="dsk-export-row"><button type="submit" class="dsk-btn dsk-btn-link" name="export"
                value="<?php echo Format::htmlchars($g); ?>" form="dsk-dashboard-form">
                <i class="fa fa-download"></i> <?php echo __('Export'); ?>
            </button></div>
            </div>
        <?php
        }
        ?>
        </div>
    </div>
</div>

<!-- Hidden form for CSV export -->
<form id="dsk-export-form" method="post" action="dashboard.php" style="display:none;">
    <?php echo csrf_token(); ?>
    <input type="hidden" name="start" value="<?php echo Format::htmlchars($report->getStartDate()); ?>" />
    <input type="hidden" name="period" value="<?php echo esc_attr(Format::htmlchars($_POST['period'] ?? 'now')); ?>" />
    <input type="hidden" name="export" value="" id="dsk-export-input" />
</form>

<script>
(function($){
    $(function(){
        var plots = <?php echo JsonDataEncoder::encode($plots); ?>;
        var stateLabels = <?php echo JsonDataEncoder::encode($state_labels); ?>;
        var eventColors = <?php echo JsonDataEncoder::encode($event_colors); ?>;
        $.dskDrawActivityChart('dsk-activity-chart', plots, stateLabels, eventColors);

        var deptData = <?php echo JsonDataEncoder::encode($dept_counts); ?>;
        $.dskDrawDeptChart('dsk-dept-chart', deptData);

        $('.dsk-range-btn').on('click', function(){
            var range = $(this).data('range');
            var d = new Date();
            var fmt = function(y,m,day){ return (y+'-'+(m<10?'0':'')+m+'-'+(day<10?'0':'')+day); };
            if (range === 'today') {
                $('#dsk-start-date').val(fmt(d.getFullYear(), d.getMonth()+1, d.getDate()));
            } else if (range === 'week') {
                var day = d.getDay() || 7;
                d.setDate(d.getDate() - day + 1);
                $('#dsk-start-date').val(fmt(d.getFullYear(), d.getMonth()+1, d.getDate()));
            } else if (range === 'month') {
                $('#dsk-start-date').val(fmt(d.getFullYear(), d.getMonth()+1, 1));
            } else if (range === 'lastmonth') {
                d.setMonth(d.getMonth() - 1);
                $('#dsk-start-date').val(fmt(d.getFullYear(), d.getMonth()+1, 1));
            }
            $('#dsk-period').val('now');
            $('#dsk-dashboard-form').submit();
        });

        $(document).on('click', 'ul.dsk-stat-tabs > li > a', function(e){
            e.preventDefault();
            var $this = $(this);
            var $ul = $this.closest('ul');
            var $container = $ul.parent();
            var $tab = $($this.attr('href'), $container);
            if (!$tab.length) return;
            $ul.children('li.active').removeClass('active');
            $this.closest('li').addClass('active');
            $container.children('.tab_content').hide();
            $tab.fadeIn('fast');
        });

        if (window.location.hash) {
            var $match = $('ul.dsk-stat-tabs a[href="' + window.location.hash + '"]');
            if ($match.length) $match.trigger('click');
        }
    });
})(jQuery);
</script>
