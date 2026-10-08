<?php

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

//TODO: Make it DB based!
require_once INCLUDE_DIR.'class.signal.php';

class Cron {

    static function MailFetcher() {
        require_once(INCLUDE_DIR.'class.mailfetch.php');
        MailFetcher::run(); //Fetch mail..frequency is limited by email account setting.
    }

    static function TicketMonitor() {
        require_once(INCLUDE_DIR.'class.ticket.php');
        Ticket::checkOverdue(); //Make stale tickets overdue
        // Cleanup any expired locks
        require_once(INCLUDE_DIR.'class.lock.php');
        Lock::cleanup();

        // Auto-close inactive tickets (throttled to once per hour).
        self::AutoClose();
    }

    /**
     * Auto-close tickets that have been inactive past the configured
     * threshold. Honors the auto_close_* config keys. Throttled to one
     * run per hour via the `auto_close_last_run` config key.
     */
    static function AutoClose() {
        global $cfg;
        if (!$cfg || !$cfg->isAutoCloseEnabled())
            return;

        // Throttle: run at most once per hour.
        $last = $cfg->getAutoCloseLastRun();
        if ($last && (time() - $last) < 3600)
            return;

        require_once(INCLUDE_DIR.'class.ticket.php');
        $cfg->setAutoCloseLastRun(time());
        Ticket::autoCloseInactive($cfg);
    }

    static function PurgeLogs() {
        global $dsk;
        // Once a day on a 5-minute cron
        if (rand(1,300) == 42)
            if($dsk) $dsk->purgeLogs();
    }

    static function PurgeDrafts() {
        require_once(INCLUDE_DIR.'class.draft.php');
        Draft::cleanup();
    }

    static function CleanOrphanedFiles() {
        require_once(INCLUDE_DIR.'class.file.php');
        AttachmentFile::deleteOrphans();
    }

    static function CleanExpiredSessions() {
        require_once(INCLUDE_DIR.'class.deskusssession.php');
        DbSessionBackend::cleanup();
    }

    static function MaybeOptimizeTables() {
        // Once a week on a 5-minute cron
        $chance = rand(1,2000);
        switch ($chance) {
        case 42:
            @db_query('OPTIMIZE TABLE '.LOCK_TABLE);
            break;
        case 242:
            @db_query('OPTIMIZE TABLE '.SYSLOG_TABLE);
            break;
        case 442:
            @db_query('OPTIMIZE TABLE '.DRAFT_TABLE);
            break;

        // Start optimizing core ticket tables when we have an archiving
        // system available
        case 142:
            #@db_query('OPTIMIZE TABLE '.TICKET_TABLE);
            break;
        case 542:
            #@db_query('OPTIMIZE TABLE '.FORM_ENTRY_TABLE);
            break;
        case 642:
            #@db_query('OPTIMIZE TABLE '.FORM_ANSWER_TABLE);
            break;
        case 342:
            #@db_query('OPTIMIZE TABLE '.FILE_TABLE);
            # XXX: Please do not add an OPTIMIZE for the file_chunk table!
            break;

        // Start optimizing user tables when we have a user directory
        // sporting deletes
        case 742:
            #@db_query('OPTIMIZE TABLE '.USER_TABLE);
            break;
        case 842:
            #@db_query('OPTIMIZE TABLE '.USER_EMAIL_TABLE);
            break;
        }
    }

    static function run(){ //called by outside cron NOT autocron
        global $dsk;
        if (!$dsk || $dsk->isUpgradePending())
            return;

        self::MailFetcher();
        self::TicketMonitor();
        self::PurgeLogs();
        self::CleanExpiredSessions();
        // Run file purging about once an hour
        //if (mt_rand(1, 9) == 4)
		if(date('i') >= 0 && date('i') <= 5){
            self::CleanOrphanedFiles();
		}
        self::PurgeDrafts();
        self::MaybeOptimizeTables();

        $data = array('autocron'=>false);
        Signal::send('cron', null, $data);
    }
}
?>
