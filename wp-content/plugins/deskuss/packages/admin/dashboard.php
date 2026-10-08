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

require('staff.inc.php');

require_once INCLUDE_DIR . 'class.report.php';
require_once INCLUDE_DIR . 'class.thread.php';

if (!empty($_POST['export'])) {
    $report = new OverviewReport($_POST['start'], $_POST['period']);
    switch (true) {
    case ($data = $report->getTabularData($_POST['export'])):
        $ts = strftime('%Y%m%d');
        $group = Format::slugify($_POST['export']);
        $delimiter = ',';
        if (class_exists('NumberFormatter')) {
            $nf = NumberFormatter::create(Internationalization::getCurrentLocale(),
                NumberFormatter::DECIMAL);
            $s = $nf->getSymbol(NumberFormatter::DECIMAL_SEPARATOR_SYMBOL);
            if ($s == ',')
                $delimiter = ';';
        }

        Http::download("stats-$group-$ts.csv", 'text/csv');
        $output = fopen('php://output', 'w');
        fputs($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($output, $data['columns'], $delimiter);
        foreach ($data['data'] as $row)
            fputcsv($output, $row, $delimiter);
        exit;
    }
}

$nav->setTabActive('dashboard');
$dsk->addExtraHeader('<meta name="tip-namespace" content="dashboard.dashboard" />',
    "$('#content').data('tipNamespace', 'dashboard.dashboard');");

// KPI snapshot (right-now counts)
$staff_stats = Ticket::getStaffStats($thisstaff);
$open_count     = (isset($staff_stats['open'])     && $staff_stats['open'])     ? (int) $staff_stats['open']     : 0;
$assigned_count = (isset($staff_stats['assigned']) && $staff_stats['assigned']) ? (int) $staff_stats['assigned'] : 0;
$overdue_count  = (isset($staff_stats['overdue'])  && $staff_stats['overdue'])  ? (int) $staff_stats['overdue']  : 0;
$answered_count = (isset($staff_stats['answered']) && $staff_stats['answered']) ? (int) $staff_stats['answered'] : 0;

$kpi = array(
    'open'     => $open_count,
    'assigned' => $assigned_count,
    'overdue'  => $overdue_count,
    'answered' => $answered_count,
);
$kpi['unanswered'] = max(0, $kpi['open'] - $kpi['answered']);

// "New today" / "Closed today" counts
$today_start = date('Y-m-d 00:00:00');
$today_end   = date('Y-m-d 23:59:59');

$res = db_query('SELECT COUNT(DISTINCT E.id) '
    .' FROM '.THREAD_EVENT_TABLE.' E '
    .' JOIN '.THREAD_TABLE.' T ON (T.id = E.thread_id AND T.object_type = "T") '
    .' WHERE E.state = "created" AND E.annulled = 0 '
    .' AND E.timestamp BETWEEN "'.$today_start.'" AND "'.$today_end.'"');
$kpi['new_today'] = ($res ? (int)db_result($res) : 0);

$res = db_query('SELECT COUNT(DISTINCT E.id) '
    .' FROM '.THREAD_EVENT_TABLE.' E '
    .' JOIN '.THREAD_TABLE.' T ON (T.id = E.thread_id AND T.object_type = "T") '
    .' WHERE E.state = "closed" AND E.annulled = 0 '
    .' AND E.timestamp BETWEEN "'.$today_start.'" AND "'.$today_end .'"');
$kpi['closed_today'] = ($res ? (int)db_result($res) : 0);

// Department distribution for doughnut chart
$dept_counts = array();
$dept_names = Dept::getDepartments();
foreach ($dept_names as $dept_id => $dept_name) {
    $dept_counts[] = array(
        'id'    => (int)$dept_id,
        'name'  => $dept_name,
        'count' => (int)Ticket::objects()
            ->filter(array('dept_id' => (int)$dept_id, 'status__state' => 'open'))
            ->count(),
    );
}
usort($dept_counts, function($a, $b) { return $b['count'] - $a['count']; });

// Recent activity feed (last 10 thread events)
$recent_events = array();
$res = db_query('SELECT E.id, E.thread_id, E.state, E.timestamp, E.username, '
    .' T.object_id AS ticket_id '
    .' FROM '.THREAD_EVENT_TABLE.' E '
    .' JOIN '.THREAD_TABLE.' T ON (T.id = E.thread_id) '
    .' WHERE T.object_type = "T" AND E.annulled = 0 '
    .' ORDER BY E.timestamp DESC LIMIT 10');
if ($res) {
    while ($row = db_fetch_array($res)) {
        $ticket = Ticket::lookup($row['ticket_id']);
        $recent_events[] = array(
            'id'        => (int)$row['id'],
            'state'     => $row['state'],
            'username'  => $row['username'],
            'timestamp' => $row['timestamp'],
            'ticket_id' => (int)$row['ticket_id'],
            'number'    => $ticket ? $ticket->getNumber() : '',
            'subject'   => $ticket ? $ticket->getSubject() : '',
        );
    }
}

require deskuss_load_page('dashboard.php');
?>
