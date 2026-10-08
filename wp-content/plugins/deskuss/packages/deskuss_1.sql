-- phpMyAdmin SQL Dump
-- version 4.4.11
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 13, 2018 at 10:36 AM
-- Server version: 5.6.25-log
-- PHP Version: 5.6.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `deskuss_1`
--

-- --------------------------------------------------------

--
-- Table structure for table `[[dbprefix]]dk_api_key`
--

CREATE TABLE IF NOT EXISTS `[[dbprefix]]dk_api_key` (
  `id` int(10) unsigned NOT NULL,
  `isactive` tinyint(1) NOT NULL DEFAULT '1',
  `ipaddr` varchar(64) NOT NULL,
  `apikey` varchar(255) NOT NULL,
  `can_create_tickets` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `can_exec_cron` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `notes` text,
  `updated` datetime NOT NULL,
  `created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `[[dbprefix]]dk_attachment`
--

CREATE TABLE IF NOT EXISTS `[[dbprefix]]dk_attachment` (
  `id` int(10) unsigned NOT NULL,
  `object_id` int(11) unsigned NOT NULL,
  `type` char(1) NOT NULL,
  `file_id` int(11) unsigned NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `inline` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `lang` varchar(16) DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `[[dbprefix]]dk_attachment`
--

INSERT INTO `[[dbprefix]]dk_attachment` VALUES
(1, 1, 'C', 2, NULL, 0, NULL),
(2, 8, 'T', 1, NULL, 1, NULL),
(3, 9, 'T', 1, NULL, 1, NULL),
(4, 10, 'T', 1, NULL, 1, NULL),
(5, 11, 'T', 1, NULL, 1, NULL),
(6, 12, 'T', 1, NULL, 1, NULL),
(7, 13, 'T', 1, NULL, 1, NULL),
(8, 14, 'T', 1, NULL, 1, NULL),
(9, 16, 'T', 1, NULL, 1, NULL),
(10, 17, 'T', 1, NULL, 1, NULL),
(11, 18, 'T', 1, NULL, 1, NULL),
(12, 19, 'T', 1, NULL, 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `[[dbprefix]]dk_canned_response`
--

CREATE TABLE IF NOT EXISTS `[[dbprefix]]dk_canned_response` (
  `canned_id` int(10) unsigned NOT NULL,
  `dept_id` int(10) unsigned NOT NULL DEFAULT '0',
  `isenabled` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `title` varchar(255) NOT NULL DEFAULT '',
  `response` text NOT NULL,
  `lang` varchar(16) NOT NULL DEFAULT 'en_US',
  `notes` text,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `[[dbprefix]]dk_canned_response`
--

INSERT INTO `[[dbprefix]]dk_canned_response` VALUES
(1, 0, 1, 'What is Deskuss ?', 'Deskuss is a customer support and ticketing system. Deskuss offers you a simple yet feature rich Tickets, Tasks, Knowledgebase, SLA, Departments, Ticket Filters, Internal Notes from your colleagues, Live User tracking, Canned Replies, Email Piping, Multiple Email configuration, etc.', 'en_US', NULL, '[[regtime]]', '[[regtime]]'),
(2, 0, 1, 'Sample (with variables)', 'Hi %{ticket.name.first},\n<br>\n<br>\nYour ticket #%{ticket.number} created on %{ticket.create_date} is in\n%{ticket.dept.name} department.', 'en_US', NULL, '[[regtime]]', '[[regtime]]');

-- --------------------------------------------------------

--
-- Table structure for table `[[dbprefix]]dk_config`
--

CREATE TABLE IF NOT EXISTS `[[dbprefix]]dk_config` (
  `id` int(11) unsigned NOT NULL,
  `namespace` varchar(64) NOT NULL,
  `key` varchar(64) NOT NULL,
  `value` text NOT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB AUTO_INCREMENT=89 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `[[dbprefix]]dk_config`
--

INSERT INTO `[[dbprefix]]dk_config` VALUES
(1, 'core', 'admin_email', '[[admin_email]]', '[[regtime]]'),
(2, 'core', 'helpdesk_url', 'https://[[helpdesk_full]]/', '[[regtime]]'),
(3, 'core', 'helpdesk_title', 'Deskuss - Support Ticket System', '[[regtime]]'),
(4, 'core', 'schema_signature', '98ad7d550c26ac44340350912296e673', '[[regtime]]'),
(5, 'core', 'time_format', 'hh:mm a', '[[regtime]]'),
(6, 'core', 'date_format', 'dd MMM y', '[[regtime]]'),
(7, 'core', 'datetime_format', 'dd MMM y h:mm a', '[[regtime]]'),
(8, 'core', 'daydatetime_format', 'EEE, MMM d y h:mm a', '[[regtime]]'),
(9, 'core', 'default_priority_id', '2', '[[regtime]]'),
(10, 'core', 'enable_daylight_saving', '', '[[regtime]]'),
(11, 'core', 'reply_separator', '-- reply above this line --', '[[regtime]]'),
(12, 'core', 'isonline', '1', '[[regtime]]'),
(13, 'core', 'staff_ip_binding', '', '[[regtime]]'),
(14, 'core', 'staff_max_logins', '4', '[[regtime]]'),
(15, 'core', 'staff_login_timeout', '2', '[[regtime]]'),
(16, 'core', 'staff_session_timeout', '30', '[[regtime]]'),
(17, 'core', 'passwd_reset_period', '', '[[regtime]]'),
(18, 'core', 'client_max_logins', '4', '[[regtime]]'),
(19, 'core', 'client_login_timeout', '2', '[[regtime]]'),
(20, 'core', 'client_session_timeout', '30', '[[regtime]]'),
(21, 'core', 'max_page_size', '25', '[[regtime]]'),
(22, 'core', 'max_open_tickets', '', '[[regtime]]'),
(23, 'core', 'autolock_minutes', '3', '[[regtime]]'),
(24, 'core', 'default_smtp_id', '', '[[regtime]]'),
(25, 'core', 'use_email_priority', '', '[[regtime]]'),
(26, 'core', 'enable_kb', '', '[[regtime]]'),
(27, 'core', 'enable_premade', '1', '[[regtime]]'),
(28, 'core', 'enable_captcha', '', '[[regtime]]'),
(29, 'core', 'enable_auto_cron', '', '[[regtime]]'),
(30, 'core', 'enable_mail_polling', '', '[[regtime]]'),
(31, 'core', 'send_sys_errors', '1', '[[regtime]]'),
(32, 'core', 'send_sql_errors', '1', '[[regtime]]'),
(33, 'core', 'send_login_errors', '1', '[[regtime]]'),
(34, 'core', 'save_email_headers', '1', '[[regtime]]'),
(35, 'core', 'strip_quoted_reply', '1', '[[regtime]]'),
(36, 'core', 'ticket_autoresponder', '', '[[regtime]]'),
(37, 'core', 'message_autoresponder', '', '[[regtime]]'),
(38, 'core', 'ticket_notice_active', '1', '[[regtime]]'),
(39, 'core', 'ticket_alert_active', '1', '[[regtime]]'),
(40, 'core', 'ticket_alert_admin', '1', '[[regtime]]'),
(41, 'core', 'ticket_alert_dept_manager', '1', '[[regtime]]'),
(42, 'core', 'ticket_alert_dept_members', '', '[[regtime]]'),
(43, 'core', 'message_alert_active', '1', '[[regtime]]'),
(44, 'core', 'message_alert_laststaff', '1', '[[regtime]]'),
(45, 'core', 'message_alert_assigned', '1', '[[regtime]]'),
(46, 'core', 'message_alert_dept_manager', '', '[[regtime]]'),
(47, 'core', 'note_alert_active', '', '[[regtime]]'),
(48, 'core', 'note_alert_laststaff', '1', '[[regtime]]'),
(49, 'core', 'note_alert_assigned', '1', '[[regtime]]'),
(50, 'core', 'note_alert_dept_manager', '', '[[regtime]]'),
(51, 'core', 'transfer_alert_active', '', '[[regtime]]'),
(52, 'core', 'transfer_alert_assigned', '', '[[regtime]]'),
(53, 'core', 'transfer_alert_dept_manager', '1', '[[regtime]]'),
(54, 'core', 'transfer_alert_dept_members', '', '[[regtime]]'),
(55, 'core', 'overdue_alert_active', '1', '[[regtime]]'),
(56, 'core', 'overdue_alert_assigned', '1', '[[regtime]]'),
(57, 'core', 'overdue_alert_dept_manager', '1', '[[regtime]]'),
(58, 'core', 'overdue_alert_dept_members', '', '[[regtime]]'),
(59, 'core', 'assigned_alert_active', '1', '[[regtime]]'),
(60, 'core', 'assigned_alert_staff', '1', '[[regtime]]'),
(61, 'core', 'assigned_alert_team_lead', '', '[[regtime]]'),
(62, 'core', 'assigned_alert_team_members', '', '[[regtime]]'),
(63, 'core', 'auto_claim_tickets', '1', '[[regtime]]'),
(64, 'core', 'show_related_tickets', '1', '[[regtime]]'),
(65, 'core', 'show_assigned_tickets', '1', '[[regtime]]'),
(66, 'core', 'show_answered_tickets', '', '[[regtime]]'),
(67, 'core', 'hide_staff_name', '', '[[regtime]]'),
(68, 'core', 'overlimit_notice_active', '', '[[regtime]]'),
(69, 'core', 'email_attachments', '1', '[[regtime]]'),
(70, 'core', 'ticket_number_format', '######', '[[regtime]]'),
(71, 'core', 'ticket_sequence_id', '', '[[regtime]]'),
(74, 'core', 'log_level', '2', '[[regtime]]'),
(75, 'core', 'log_graceperiod', '12', '[[regtime]]'),
(76, 'core', 'client_registration', 'public', '[[regtime]]'),
(77, 'core', 'max_file_size', '8388608', '[[regtime]]'),
(78, 'core', 'landing_page_id', '1', '[[regtime]]'),
(79, 'core', 'thank-you_page_id', '2', '[[regtime]]'),
(80, 'core', 'offline_page_id', '3', '[[regtime]]'),
(81, 'core', 'system_language', 'en_US', '[[regtime]]'),
(82, 'mysqlsearch', 'reindex', '1', '[[regtime]]'),
(83, 'core', 'default_email_id', '1', '[[regtime]]'),
(84, 'core', 'alert_email_id', '2', '[[regtime]]'),
(85, 'core', 'default_dept_id', '1', '[[regtime]]'),
(86, 'core', 'default_sla_id', '1', '[[regtime]]'),
(87, 'core', 'default_template_id', '1', '[[regtime]]'),
(88, 'core', 'default_timezone', 'Asia/Kolkata', '[[regtime]]');

-- --------------------------------------------------------

--
-- Table structure for table `[[dbprefix]]dk_content`
--

CREATE TABLE IF NOT EXISTS `[[dbprefix]]dk_content` (
  `id` int(10) unsigned NOT NULL,
  `isactive` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `type` varchar(32) NOT NULL DEFAULT 'other',
  `name` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `notes` text,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `[[dbprefix]]dk_content`
--

INSERT INTO `[[dbprefix]]dk_content` VALUES
(1, 1, 'landing', 'Landing', '<h1>Welcome to the Support Center</h1> <p> In order to streamline support requests and better serve you, we utilize a support ticket system. Every support request is assigned a unique ticket number which you can use to track the progress and responses online. For your reference we provide complete archives and history of all your support requests. A valid email address is required to submit a ticket. </p>', 'The Landing Page refers to the content of the Customer Portal''s initial view. The template modifies the content seen above the two links <strong>Open a New Ticket</strong> and <strong>Check Ticket Status</strong>.', '[[regtime]]', '[[regtime]]'),
(2, 1, 'thank-you', 'Thank You', '<div>%{ticket.name},\n<br>\n<br>\nThank you for contacting us.\n<br>\n<br>\nA support ticket request has been created and a representative will be\ngetting back to you shortly.</p>\n<br>\n<br>\nSupport Team\n</div>', 'This template defines the content displayed on the Thank You page after a\nClient submits a new ticket in the Client Portal.', '[[regtime]]', '[[regtime]]'),
(3, 1, 'offline', 'Offline', '<div><h1>\n<span style="font-size: medium">Support Ticket System Offline</span>\n</h1>\n<p>Thank you for your interest in contacting us.</p>\n<p>Our helpdesk is offline at the moment, please check back at a later\ntime.</p>\n</div>', 'The Offline Page appears in the Customer Portal when the Help Desk is offline.', '[[regtime]]', '[[regtime]]'),
(4, 1, 'registration-staff', 'Welcome to Deskuss', '<h3><strong>Hi %{recipient.name.first},</strong></h3> <div> We''ve created an account for you at our help desk at %{url}.<br /> <br /> Please follow the link below to confirm your account and gain access to your tickets.<br /> <br /> <a href="%{link}">%{link}</a><br /> <br /> <em style="font-size: small">Regards,<br /> %{company.name}</em> </div>', 'This template defines the initial email (optional) sent to Agents when an account is created on their behalf.', '[[regtime]]', '[[regtime]]'),
(5, 1, 'pwreset-staff', 'Deskuss Staff Password Reset', '<h3><strong>Hi %{staff.name.first},</strong></h3> <div> A password reset request has been submitted on your behalf for the helpdesk at %{url}.<br /> <br /> If you feel that this has been done in error, delete and disregard this email. Your account is still secure and no one has been given access to it. It is not locked and your password has not been reset. Someone could have mistakenly entered your email address.<br /> <br /> Follow the link below to login to the help desk and change your password.<br /> <br /> <a href="%{link}">%{link}</a><br /> <br /> <em style="font-size: small">Regards,</em> <br /> <img src="cid:b56944cb4722cc5cda9d1e23a3ea7fbc" alt="Powered by Deskuss" width="126" height="19" style="width: 126px" /> </div>', 'This template defines the email sent to Staff who select the <strong>Forgot My Password</strong> link on the Staff Control Panel Log In page.', '[[regtime]]', '[[regtime]]'),
(6, 1, 'banner-staff', 'Authentication Required', '', 'This is the initial message and banner shown on the Staff Log In page. The first input field refers to the red-formatted text that appears at the top. The latter textarea is for the banner content which should serve as a disclaimer.', '[[regtime]]', '[[regtime]]'),
(7, 1, 'registration-client', 'Welcome to %{company.name}', '<h3><strong>Hi %{recipient.name.first},</strong></h3> <div> We''ve created an account for you at our help desk at %{url}.<br /> <br /> Please follow the link below to confirm your account and gain access to your tickets.<br /> <br /> <a href="%{link}">%{link}</a><br /> <br /> <em style="font-size: small">Regards, <br /> %{company.name}</em> </div>', 'This template defines the email sent to Clients when their account has been created in the Client Portal or by an Agent on their behalf. This email serves as an email address verification. Please use %{link} somewhere in the body.', '[[regtime]]', '[[regtime]]'),
(8, 1, 'pwreset-client', '%{company.name} Help Desk Access', '<h3><strong>Hi %{user.name.first},</strong></h3> <div> A password reset request has been submitted on your behalf for the helpdesk at %{url}.<br /> <br /> If you feel that this has been done in error, delete and disregard this email. Your account is still secure and no one has been given access to it. It is not locked and your password has not been reset. Someone could have mistakenly entered your email address.<br /> <br /> Follow the link below to login to the help desk and change your password.<br /> <br /> <a href="%{link}">%{link}</a><br /> <br /> <em style="font-size: small">Regards, <br /> %{company.name}</em> </div>', 'This template defines the email sent to Clients who select the <strong>Forgot My Password</strong> link on the Client Log In page.', '[[regtime]]', '[[regtime]]'),
(9, 1, 'banner-client', 'Sign in to %{company.name}', 'To better serve you, we encourage our Clients to register for an account.', 'This composes the header on the Client Log In page. It can be useful to inform your Clients about your log in and registration policies.', '[[regtime]]', '[[regtime]]'),
(10, 1, 'registration-confirm', 'Account registration', '<div><strong>Thanks for registering for an account.</strong><br/> <br /> We''ve just sent you an email to the address you entered. Please follow the link in the email to confirm your account and gain access to your tickets. </div>', 'This templates defines the page shown to Clients after completing the registration form. The template should mention that the system is sending them an email confirmation link and what is the next step in the registration process.', '[[regtime]]', '[[regtime]]'),
(11, 1, 'registration-thanks', 'Account Confirmed!', '<div> <strong>Thanks for registering for an account.</strong><br /> <br /> You''ve confirmed your email address and successfully activated your account. You may proceed to open a new ticket or manage existing tickets.<br /> <br /> <em>Your friendly support center</em><br /> %{company.name} </div>', 'This template defines the content displayed after Clients successfully register by confirming their account. This page should inform the user that registration is complete and that the Client can now submit a ticket or access existing tickets.', '[[regtime]]', '[[regtime]]'),
(12, 1, 'access-link', 'Ticket [#%{ticket.number}] Access Link', '<h3><strong>Hi %{recipient.name.first},</strong></h3> <div> An access link request for ticket #%{ticket.number} has been submitted on your behalf for the helpdesk at %{url}.<br /> <br /> Follow the link below to check the status of the ticket #%{ticket.number}.<br /> <br /> <a href="%{recipient.ticket_link}">%{recipient.ticket_link}</a><br /> <br /> If you <strong>did not</strong> make the request, please delete and disregard this email. Your account is still secure and no one has been given access to the ticket. Someone could have mistakenly entered your email address.<br /> <br /> --<br /> %{company.name} </div>', 'This template defines the notification for Clients that an access link was sent to their email. The ticket number and email address trigger the access link.', '[[regtime]]', '[[regtime]]');

-- --------------------------------------------------------

--
-- Table structure for table `[[dbprefix]]dk_department`
--

CREATE TABLE IF NOT EXISTS `[[dbprefix]]dk_department` (
  `id` int(11) unsigned NOT NULL,
  `pid` int(11) unsigned DEFAULT NULL,
  `tpl_id` int(10) unsigned NOT NULL DEFAULT '0',
  `sla_id` int(10) unsigned NOT NULL DEFAULT '0',
  `email_id` int(10) unsigned NOT NULL DEFAULT '0',
  `autoresp_email_id` int(10) unsigned NOT NULL DEFAULT '0',
  `manager_id` int(10) unsigned NOT NULL DEFAULT '0',
  `priority_id` int(10) unsigned NOT NULL DEFAULT '0',
  `status_id` int(10) unsigned NOT NULL DEFAULT '0',
  `staff_id` int(10) unsigned NOT NULL DEFAULT '0',
  `team_id` int(10) unsigned NOT NULL DEFAULT '0',
  `page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `noautoresp` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `flags` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(128) NOT NULL DEFAULT '',
  `signature` text NOT NULL,
  `ispublic` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `group_membership` tinyint(1) NOT NULL DEFAULT '0',
  `ticket_auto_response` tinyint(1) NOT NULL DEFAULT '1',
  `message_auto_response` tinyint(1) NOT NULL DEFAULT '0',
  `path` varchar(128) NOT NULL DEFAULT '/',
  `updated` datetime NOT NULL,
  `created` datetime NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `[[dbprefix]]dk_department`
--

INSERT INTO `[[dbprefix]]dk_department` VALUES
(1, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 'Support', 'Support Department', 1, 1, 1, 1, '/1/', '[[regtime]]', '[[regtime]]'),
(3, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 'Maintenance', 'Maintenance Department', 1, 0, 1, 1, '/3/', '[[regtime]]', '[[regtime]]');

-- --------------------------------------------------------

--
-- Table structure for table `[[dbprefix]]dk_dept_form`
--

CREATE TABLE IF NOT EXISTS `[[dbprefix]]dk_dept_form` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `dept_id` int unsigned NOT NULL DEFAULT '0',
  `form_id` int unsigned NOT NULL DEFAULT '0',
  `sort` int unsigned NOT NULL DEFAULT '1',
  `extra` text,
  PRIMARY KEY (`id`),
  KEY `dept_id` (`dept_id`),
  KEY `form_id` (`form_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `[[dbprefix]]dk_dept_form`
--

INSERT INTO `[[dbprefix]]dk_dept_form` VALUES
(1, 1, 2, 1, NULL),
(2, 3, 2, 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `[[dbprefix]]dk_draft`
--

CREATE TABLE IF NOT EXISTS `[[dbprefix]]dk_draft` (
  `id` int(11) unsigned NOT NULL,
  `staff_id` int(11) unsigned NOT NULL,
  `namespace` varchar(32) NOT NULL DEFAULT '',
  `body` text NOT NULL,
  `extra` text,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `[[dbprefix]]dk_email`
--

CREATE TABLE IF NOT EXISTS `[[dbprefix]]dk_email` (
  `email_id` int(11) unsigned NOT NULL,
  `noautoresp` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `priority_id` tinyint(3) unsigned NOT NULL DEFAULT '2',
  `dept_id` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `topic_id` int(11) unsigned NOT NULL DEFAULT '0',
  `email` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `userid` varchar(255) NOT NULL,
  `userpass` varchar(255) CHARACTER SET ascii NOT NULL,
  `mail_active` tinyint(1) NOT NULL DEFAULT '0',
  `mail_host` varchar(255) NOT NULL,
  `mail_protocol` enum('POP','IMAP') NOT NULL DEFAULT 'POP',
  `mail_encryption` enum('NONE','SSL') NOT NULL,
  `mail_port` int(6) DEFAULT NULL,
  `mail_fetchfreq` tinyint(3) NOT NULL DEFAULT '5',
  `mail_fetchmax` SMALLINT(4) NOT NULL DEFAULT '30',
  `mail_archivefolder` varchar(255) DEFAULT NULL,
  `mail_delete` tinyint(1) NOT NULL DEFAULT '0',
  `mail_errors` tinyint(3) NOT NULL DEFAULT '0',
  `mail_lasterror` datetime DEFAULT NULL,
  `mail_lastfetch` datetime DEFAULT NULL,
  `smtp_active` tinyint(1) DEFAULT '0',
  `smtp_host` varchar(255) NOT NULL,
  `smtp_port` int(6) DEFAULT NULL,
  `smtp_secure` tinyint(1) NOT NULL DEFAULT '1',
  `smtp_auth` tinyint(1) NOT NULL DEFAULT '1',
  `smtp_spoofing` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `notes` text,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `[[dbprefix]]dk_email`
--

INSERT INTO `[[dbprefix]]dk_email` VALUES
(1, 0, 2, 1, 0, 'system@company.com', 'Support', '', '', 0, '', 'POP', 'NONE', NULL, 5, 30, NULL, 0, 0, NULL, NULL, 0, '', NULL, 1, 1, 0, NULL, '[[regtime]]', '[[regtime]]'),
(2, 0, 2, 1, 0, 'alerts@company.com', 'Alerts', '', '', 0, '', 'POP', 'NONE', NULL, 5, 30, NULL, 0, 0, NULL, NULL, 0, '', NULL, 1, 1, 0, NULL, '[[regtime]]', '[[regtime]]'),
(3, 0, 2, 1, 0, 'noreply@company.com', '', '', '', 0, '', 'POP', 'NONE', NULL, 5, 30, NULL, 0, 0, NULL, NULL, 0, '', NULL, 1, 1, 0, NULL, '[[regtime]]', '[[regtime]]');

-- --------------------------------------------------------

--
-- Table structure for table `[[dbprefix]]dk_email_account`
--

CREATE TABLE IF NOT EXISTS `[[dbprefix]]dk_email_account` (
  `id` int(11) unsigned NOT NULL,
  `name` varchar(128) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `protocol` varchar(64) NOT NULL DEFAULT '',
  `host` varchar(128) NOT NULL DEFAULT '',
  `port` int(11) NOT NULL,
  `username` varchar(128) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `options` varchar(512) DEFAULT NULL,
  `errors` int(11) unsigned DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `lastconnect` timestamp NULL DEFAULT NULL,
  `lasterror` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `[[dbprefix]]dk_email_template`
--

CREATE TABLE IF NOT EXISTS `[[dbprefix]]dk_email_template` (
  `id` int(11) unsigned NOT NULL,
  `tpl_id` int(11) unsigned NOT NULL,
  `code_name` varchar(32) NOT NULL,
  `subject` varchar(255) NOT NULL DEFAULT '',
  `body` text NOT NULL,
  `notes` text,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `[[dbprefix]]dk_email_template`
--

INSERT INTO `[[dbprefix]]dk_email_template` VALUES
(1, 1, 'ticket.autoresp', 'Support Ticket Opened [#%{ticket.number}]', ' <h3><strong>Dear %{recipient.name.first},</strong></h3> <p> A request for support has been created and assigned #%{ticket.number}. A representative will follow-up with you as soon as possible. You can <a href="%%7Brecipient.ticket_link%7D">view this ticket''s progress online</a>. </p> <br /><div style="color:rgb(127, 127, 127)"> Your %{company.name} Team, <br /> %{signature} </div> <hr /> <div style="color:rgb(127, 127, 127);font-size:small"><em>If you wish to provide additional comments or information regarding the issue, please reply to this email or <a href="%%7Brecipient.ticket_link%7D"><span style="color:rgb(84, 141, 212)">login to your account</span></a> for a complete archive of your support requests.</em></div> ', NULL, '[[regtime]]', '[[regtime]]'),
(2, 1, 'ticket.autoreply', 'Re: %{ticket.subject} [#%{ticket.number}]', ' <h3><strong>Dear %{recipient.name.first},</strong></h3> A request for support has been created and assigned ticket <a href="%%7Brecipient.ticket_link%7D">#%{ticket.number}</a> with the following automatic reply <br /><br /> Topic: <strong>%{ticket.dept.name}</strong> <br /> Subject: <strong>%{ticket.subject}</strong> <br /><br /> %{response} <br /><br /><div style="color:rgb(127, 127, 127)">Your %{company.name} Team,<br /> %{signature}</div> <hr /> <div style="color:rgb(127, 127, 127);font-size:small"><em>We hope this response has sufficiently answered your questions. If you wish to provide additional comments or information, please reply to this email or <a href="%%7Brecipient.ticket_link%7D"><span style="color:rgb(84, 141, 212)">login to your account</span></a> for a complete archive of your support requests.</em></div> ', NULL, '[[regtime]]', '[[regtime]]'),
(3, 1, 'message.autoresp', 'Message Confirmation', ' <h3><strong>Dear %{recipient.name.first},</strong></h3> Your reply to support request <a href="%%7Brecipient.ticket_link%7D">#%{ticket.number}</a> has been noted <br /><br /><div style="color:rgb(127, 127, 127)"> Your %{company.name} Team,<br /> %{signature} </div> <hr /> <div style="color:rgb(127, 127, 127);font-size:small;text-align:center"> <em>You can view the support request progress <a href="%%7Brecipient.ticket_link%7D">online here</a></em> </div> ', NULL, '[[regtime]]', '[[regtime]]'),
(4, 1, 'ticket.notice', '%{ticket.subject} [#%{ticket.number}]', ' <h3><strong>Dear %{recipient.name.first},</strong></h3> Our customer care team has created a ticket, <a href="%%7Brecipient.ticket_link%7D">#%{ticket.number}</a> on your behalf, with the following details and summary: <br /><br /> Topic: <strong>%{ticket.dept.name}</strong> <br /> Subject: <strong>%{ticket.subject}</strong> <br /><br /> %{message} <br /><br /> If need be, a representative will follow-up with you as soon as possible. You can also <a href="%%7Brecipient.ticket_link%7D">view this ticket''s progress online</a>. <br /><br /><div style="color:rgb(127, 127, 127)"> Your %{company.name} Team,<br /> %{signature}</div> <hr /> <div style="color:rgb(127, 127, 127);font-size:small"><em>If you wish to provide additional comments or information regarding the issue, please reply to this email or <a href="%%7Brecipient.ticket_link%7D"><span style="color:rgb(84, 141, 212)">login to your account</span></a> for a complete archive of your support requests.</em></div> ', NULL, '[[regtime]]', '[[regtime]]'),
(5, 1, 'ticket.overlimit', 'Open Tickets Limit Reached', ' <h3><strong>Dear %{ticket.name.first},</strong></h3> You have reached the maximum number of open tickets allowed. To be able to open another ticket, one of your pending tickets must be closed. To update or add comments to an open ticket simply <a href="%%7Burl%7D/tickets.php?e=%%7Bticket.email%7D">login to our helpdesk</a>. <br /><br /> Thank you,<br /> Support Ticket System', NULL, '[[regtime]]', '[[regtime]]'),
(6, 1, 'ticket.reply', 'Re: %{ticket.subject} [#%{ticket.number}]', ' <h3><strong>Dear %{recipient.name},</strong></h3> %{response} <br /><br /><div style="color:rgb(127, 127, 127)"> Your %{company.name} Team,<br /> %{signature} </div> <hr /> <div style="color:rgb(127, 127, 127);font-size:small;text-align:center"><em>We hope this response has sufficiently answered your questions. If not, please do not send another email. Instead, reply to this email or <a href="%%7Brecipient.ticket_link%7D" style="color:rgb(84, 141, 212)">login to your account</a> for a complete archive of all your support requests and responses.</em></div> ', NULL, '[[regtime]]', '[[regtime]]'),
(7, 1, 'ticket.activity.notice', 'Re: %{ticket.subject} [#%{ticket.number}]', ' <h3><strong>Dear %{recipient.name.first},</strong></h3> <div> <em>%{poster.name}</em> just logged a message to a ticket in which you participate. </div> <br /> %{message} <br /><br /><hr /> <div style="color:rgb(127, 127, 127);font-size:small;text-align:center"> <em>You''re getting this email because you are a collaborator on ticket <a href="%%7Brecipient.ticket_link%7D" style="color:rgb(84, 141, 212)">#%{ticket.number}</a>. To participate, simply reply to this email or <a href="%%7Brecipient.ticket_link%7D" style="color:rgb(84, 141, 212)">click here</a> for a complete archive of the ticket thread.</em> </div> ', NULL, '[[regtime]]', '[[regtime]]'),
(8, 1, 'ticket.alert', 'New Ticket Alert', ' <h2>Hi %{recipient.name},</h2> New ticket #%{ticket.number} created <br /><br /><table><tbody> <tr> <td> <strong>From</strong>: </td> <td> %{ticket.name} </td> </tr> <tr> <td> <strong>Department</strong>: </td> <td> %{ticket.dept.name} </td> </tr> </tbody></table> <br /> %{message} <br /><br /><hr /> <div>To view or respond to the ticket, please <a href="%%7Bticket.staff_link%7D">login</a> to the support ticket system</div> <em style="font-size:small">Regards,</em> <br /><a href="http://deskuss.com/"><img width="126" height="19" style="width:126px" alt="Powered By Deskuss" src="cid:b56944cb4722cc5cda9d1e23a3ea7fbc" /></a> ', NULL, '[[regtime]]', '[[regtime]]'),
(9, 1, 'message.alert', 'New Message Alert', ' <h3><strong>Hi %{recipient.name},</strong></h3> New message appended to ticket <a href="%%7Bticket.staff_link%7D">#%{ticket.number}</a> <br /><br /><table><tbody> <tr> <td> <strong>From</strong>: </td> <td> %{ticket.name} </td> </tr> <tr> <td> <strong>Department</strong>: </td> <td> %{ticket.dept.name} </td> </tr> </tbody></table> <br /> %{message} <br /><br /><hr /> <div>To view or respond to the ticket, please <a href="%%7Bticket.staff_link%7D"><span style="color:rgb(84, 141, 212)">login</span></a> to the support ticket system</div> <em style="color:rgb(127,127,127);font-size:small">Regards,</em><br /><img src="cid:b56944cb4722cc5cda9d1e23a3ea7fbc" alt="Powered by Deskuss" width="126" height="19" style="width:126px" /> ', NULL, '[[regtime]]', '[[regtime]]'),
(10, 1, 'note.alert', 'New Internal Activity Alert', ' <h3><strong>Hi %{recipient.name},</strong></h3> An agent has logged activity on ticket <a href="%%7Bticket.staff_link%7D">#%{ticket.number}</a> <br /><br /><table><tbody> <tr> <td> <strong>From</strong>: </td> <td> %{note.poster} </td> </tr> <tr> <td> <strong>Title</strong>: </td> <td> %{note.title} </td> </tr> </tbody></table> <br /> %{note.message} <br /><br /><hr /> To view/respond to the ticket, please <a href="%%7Bticket.staff_link%7D">login</a> to the support ticket system <br /><br /><em style="font-size:small">Regards,</em> <br /><img src="cid:b56944cb4722cc5cda9d1e23a3ea7fbc" alt="Powered by Deskuss" width="126" height="19" style="width:126px" /> ', NULL, '[[regtime]]', '[[regtime]]'),
(11, 1, 'assigned.alert', 'Ticket Assigned to you', ' <h3><strong>Hi %{assignee.name.first},</strong></h3> Ticket <a href="%%7Bticket.staff_link%7D">#%{ticket.number}</a> has been assigned to you by %{assigner.name.short} <br /><br /><table><tbody> <tr> <td> <strong>From</strong>: </td> <td> %{ticket.name} </td> </tr> <tr> <td> <strong>Subject</strong>: </td> <td> %{ticket.subject} </td> </tr> </tbody></table> <br /> %{comments} <br /><br /><hr /> <div>To view/respond to the ticket, please <a href="%%7Bticket.staff_link%7D"><span style="color:rgb(84, 141, 212)">login</span></a> to the support ticket system</div> <em style="font-size:small">Regards,</em> <br /><img src="cid:b56944cb4722cc5cda9d1e23a3ea7fbc" alt="Powered by Deskuss" width="126" height="19" style="width:126px" /> ', NULL, '[[regtime]]', '[[regtime]]'),
(12, 1, 'transfer.alert', 'Ticket #%{ticket.number} transfer - %{ticket.dept.name}', ' <h3>Hi %{recipient.name},</h3> Ticket <a href="%%7Bticket.staff_link%7D">#%{ticket.number}</a> has been transferred to the %{ticket.dept.name} department by <strong>%{staff.name.short}</strong> <br /><br /><blockquote> %{comments} </blockquote> <hr /> <div>To view or respond to the ticket, please <a href="%%7Bticket.staff_link%7D">login</a> to the support ticket system. </div> <em style="font-size:small">Regards,</em> <br /><a href="http://deskuss.com/"><img width="126" height="19" alt="Powered By Deskuss" style="width:126px" src="cid:b56944cb4722cc5cda9d1e23a3ea7fbc" /></a> ', NULL, '[[regtime]]', '[[regtime]]'),
(13, 1, 'ticket.overdue', 'Stale Ticket Alert', ' <h3> <strong>Hi %{recipient.name}</strong>,</h3> A ticket, <a href="%%7Bticket.staff_link%7D">#%{ticket.number}</a> is seriously overdue. <br /><br /> We should all work hard to guarantee that all tickets are being addressed in a timely manner. <br /><br /> Signed,<br /> %{ticket.dept.manager.name} <hr /> <div>To view or respond to the ticket, please <a href="%%7Bticket.staff_link%7D"><span style="color:rgb(84, 141, 212)">login</span></a> to the support ticket system. You''re receiving this notice because the ticket is assigned directly to you or to a team or department of which you''re a member.</div> <em style="font-size:small">Your friendly <span style="font-size:smaller">(although with limited patience)</span> Customer Support System</em><br /><img src="cid:b56944cb4722cc5cda9d1e23a3ea7fbc" height="19" alt="Powered by Deskuss" width="126" style="width:126px" /> ', NULL, '[[regtime]]', '[[regtime]]');

-- --------------------------------------------------------

--
-- Table structure for table `[[dbprefix]]dk_email_template_group`
--

CREATE TABLE IF NOT EXISTS `[[dbprefix]]dk_email_template_group` (
  `tpl_id` int(11) NOT NULL,
  `isactive` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `name` varchar(32) NOT NULL DEFAULT '',
  `lang` varchar(16) NOT NULL DEFAULT 'en_US',
  `notes` text,
  `created` datetime NOT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `[[dbprefix]]dk_email_template_group`
--

INSERT INTO `[[dbprefix]]dk_email_template_group` VALUES
(1, 1, 'Deskuss Default Template (HTML)', 'en_US', 'Default Deskuss templates', '[[regtime]]', '[[regtime]]');

-- --------------------------------------------------------

--
-- Table structure for table `[[dbprefix]]dk_faq`
--

CREATE TABLE IF NOT EXISTS `[[dbprefix]]dk_faq` (
  `faq_id` int(10) unsigned NOT NULL,
  `category_id` int(10) unsigned NOT NULL DEFAULT '0',
  `ispublished` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `question` varchar(255) NOT NULL,
  `answer` text NOT NULL,
  `keywords` tinytext,
  `notes` text,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `[[dbprefix]]dk_faq_category`
--

CREATE TABLE IF NOT EXISTS `[[dbprefix]]dk_faq_category` (
  `category_id` int(10) unsigned NOT NULL,
  `ispublic` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `name` varchar(125) DEFAULT NULL,
  `description` text NOT NULL,
  `notes` tinytext NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `[[dbprefix]]dk_file`
--

CREATE TABLE IF NOT EXISTS `[[dbprefix]]dk_file` (
  `id` int(11) NOT NULL,
  `ft` char(1) NOT NULL DEFAULT 'T',
  `bk` char(1) NOT NULL DEFAULT 'D',
  `type` varchar(255) CHARACTER SET ascii NOT NULL DEFAULT '',
  `size` bigint(20) unsigned NOT NULL DEFAULT '0',
  `key` varchar(86) CHARACTER SET ascii NOT NULL,
  `signature` varchar(86) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `attrs` varchar(255) DEFAULT NULL,
  `created` datetime NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `[[dbprefix]]dk_file`
--

INSERT INTO `[[dbprefix]]dk_file` VALUES
(1, 'T', 'D', 'image/png', 9452, 'b56944cb4722cc5cda9d1e23a3ea7fbc', 'gjMyblHhAxCQvzLfPBW3EjMUY1AmQQmz', 'powered-by-deskuss.png', NULL, '[[regtime]]'),
(2, 'T', 'D', 'text/plain', 24, '40AmGMWtx86n3ccfeGGNagoRoTDtol7o', 'MWtx86n3ccfeGGNafaacpitTxmJ4h3Ls', 'Deskuss.txt', NULL, '[[regtime]]');

-- --------------------------------------------------------

--
-- Table structure for table `[[dbprefix]]dk_file_chunk`
--

CREATE TABLE IF NOT EXISTS `[[dbprefix]]dk_file_chunk` (
  `file_id` int(11) NOT NULL,
  `chunk_id` int(11) NOT NULL,
  `filedata` longblob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `[[dbprefix]]dk_file_chunk`
--

INSERT INTO `[[dbprefix]]dk_file_chunk` VALUES
(1, 0, '‰PNG\r\n\Z\n\0\0\0\rIHDR\0\0\0Ú\0\0\0(\0\0\0˜GäÉ\0\0\nCiCCPICC profile\0\0xÚSwX“÷>ß÷eVBØð±—l\0"#¬ÈY¢’\0a„@Å…ˆ\nVœHUÄ‚Õ\nHˆâ (¸gAŠˆZ‹U\\8îÜ§µ}zïííû×û¼çœçüÎyÏ€&‘æ¢j\09R…<:ØOHÄÉ½€Hà æËÂgÅ\0\0ðyx~t°?ü¯o\0\0pÕ.$ÇáÿƒºP&W\0 ‘\0à"çR\0È.TÈ\0È\0°S³d\n\0”\0\0ly|B"\0ª\r\0ìôI>\0Ø©“Ü\0Ø¢©\0\0™(G$@»\0`UR,ÀÂ\0 ¬@".À®€Y¶2G€½\0vŽX@`\0€™B,Ì\0 8\0CÍ L 0Ò¿à©_p…¸H\0ÀË•Í—KÒ3¸•Ð\Zwòðàâ!âÂl±Ba)f	ä"œ—›#HçLÎ\0\0\ZùÑÁþ8?çæäáæfçlïôÅ¢þkðo">!ñßþ¼Œ\0NÏïÚ_ååÖpÇ°u¿k©[\0ÚV\0hßù]3Û	 Z\nÐzù‹y8ü@ž¡PÈ<\ní%b¡½0ã‹>ÿ3áoà‹~öü@þÛzð\0qš@™­À£ƒýqanv®RŽçËB1n÷ç#þÇ…ýŽ)Ñâ4±\\,ŠñX‰¸P"MÇy¹R‘D!É•âé2ñ–ý	“w\r\0¬†OÀN¶µËlÀ~î‹XÒv\0@~ó-Œ\Z‘\0g42y÷\0\0“¿ù@+\0Í—¤ã\0\0¼è\\¨”LÆ\0\0D *°AÁ¬ÀœÁ¼ÀaD@$À<Bä€\n¡–ATÀ:Øµ°\Z šá´Á18\rçà\\ëp`žÂ¼†	AÈa!:ˆbŽØ"Î™Ž"aH4’€¤ éˆQ"ÅÈr¤©Bj‘]H#ò-r9\\@úÛÈ 2ŠüŠ¼G1”²QÔu@¹¨\ZŠÆ sÑt4]€–¢kÑ\Z´=€¶¢§ÑKèut\0}ŠŽc€Ñ1fŒÙa\\Œ‡E`‰X\Z&ÇcåX5V5cX7vÀžaï$‹€ì^„Âl‚GXLXC¨%ì#´ºW	ƒ„1Â''"“¨O´%zùÄxb:±XF¬&î!!ž%^''_“H$É’äN\n!%2IIkHÛH-¤S¤>ÒiœL&ëmÉÞä²€¬ —‘·O’ûÉÃä·:ÅˆâL	¢$R¤”J5e?å¥Ÿ2B™ ªQÍ©žÔªˆ:ŸZIm vP/S‡©4uš%Í›CË¤-£ÕÐšigi÷h/étº	ÝƒE—Ð—Òkèéçéƒôw\r†\rƒÇHb(k{§·/™L¦Ó—™ÈT0×2™g˜˜oUX*ö*|‘Ê•:•V•~•çªTUsU?ÕyªT«U«^V}¦FU³Pã©	Ô«Õ©U»©6®ÎRwRPÏQ_£¾_ý‚úc\r²†…F †H£Tc·Æ!Æ2eñXBÖrVë,k˜Mb[²ùìLvûv/{LSCsªf¬f‘fæqÍÆ±àð9ÙœJÎ!Î\rÎ{--?-±Öj­f­~­7ÚzÚ¾ÚbírííëÚïup@,õ:m:÷u	º6ºQº…ºÛuÏê>Ócëyé	õÊõéÝÑGõmô£õêïÖïÑ7046l18cðÌcèk˜i¸Ñð„á¨Ëhº‘Äh£ÑI£''¸&î‡gã5x>f¬ob¬4ÞeÜk<abi2Û¤Ä¤Åä¾)Í”kšfºÑ´ÓtÌÌÈ,Ü¬Ø¬ÉìŽ9Õœkža¾Ù¼Ûü…¥EœÅJ‹6‹Ç–Ú–|Ë–M–÷¬˜V>VyVõV×¬IÖ\\ë,ëmÖWlPW››:›Ë¶¨­›­Äv›mßâ)Ò)õSnÚ1ìüì\nìšìí9öaö%ömöÏÌÖ;t;|rtuÌvlp¼ë¤á4Ã©Ä©ÃéWgg¡só5¦KË—v—Sm§Š§nŸzË•å\ZîºÒµÓõ£›»›Ü­ÙmÔÝÌ=Å}«ûM.›É]Ã=ïAôð÷XâqÌã§›§Âóç/^v^Y^û½O³œ&žÖ0mÈÛÄ[à½Ë{`:>=eúÎé>Æ>ŸzŸ‡¾¦¾"ß=¾#~Ö~™~üžû;úËýø¿áyòñN`Áå½\Z³k™¥5»/>B	\rYr“oÀòùc3Üg,šÑÊZú0Ì&LÖŽ†Ïß~o¦ùLéÌ¶ˆàGlˆ¸i™ù})*2ª.êQ´Stqt÷,Ö¬äYûg½Žñ©Œ¹;Ûj¶rvg¬jlRlcì›¸€¸ª¸x‡øEñ—t$	í‰äÄØÄ=‰ãsçlš3œäšT–tc®åÜ¢¹æéÎËžw<Y5Y|8…˜—²?åƒ BP/Oå§nMò„›…OE¾¢¢Q±·¸J<’æV•ö8Ý;}Cúh†OFuÆ3	OR+y‘’¹#óMVDÖÞ¬ÏÙqÙ-9”œ”œ£R\ri–´+×0·(·Of++“\räyæmÊ“‡Ê÷ä#ùsóÛl…LÑ£´R®PL/¨+x[[x¸H½HZÔ3ßfþêù#‚|½°P¸°³Ø¸xYñà"¿E»#‹Sw.1]RºdxiðÒ}ËhË²–ýPâXRUòjyÜòŽRƒÒ¥¥C+‚W4•©”ÉËn®ôZ¹ca•dUïj—Õ[V*•_¬p¬¨®ø°F¸æâWN_Õ|õymÚÚÞJ·ÊíëHë¤ën¬÷Y¿¯J½jAÕÐ†ð\r­ñå_mJÞt¡zjõŽÍ´ÍÊÍ5a5í[Ì¶¬Ûò¡6£öz]ËVý­«·¾Ù&ÚÖ¿Ýw{óƒ;Þï”ì¼µ+xWk½E}õnÒî‚Ý\Zbº¿æ~Ý¸GwOÅž{¥{öEïëjtolÜ¯¿¿²	mR6H:på›€oÚ›íšwµpZ*ÂAåÁ''ß¦|{ãPè¡ÎÃÜÃÍß™·õëHy+Ò:¿u¬-£m =¡½ïèŒ£^G¾·ÿ~ï1ãcuÇ5Wž (=ñùä‚“ã§d§žN?=Ô™Üy÷Lü™k]Q]½gCÏž?tîL·_÷ÉóÞç]ð¼pô"÷bÛ%·K­=®=G~pýáH¯[oëe÷ËíW<®tôMë;ÑïÓújÀÕs×ø×.]Ÿy½ïÆì·n&Ý¸%ºõøvöíw\nîLÜ]zx¯ü¾Úýêúê´þ±eÀmàø`À`ÏÃYï	‡žþ”ÿÓ‡áÒGÌGÕ#F#\r\Z½òdÎ“á§²§ÏÊ~Vÿyës«çßýâûKÏXüØðù‹Ï¿®y©órï«©¯:Ç#Ç¼Îy=ñ¦ü­ÎÛ}ï¸ïºßÇ½™(ü@þPóÑúcÇ§ÐO÷>ç|þü/÷„óû€9%\0\0\0tEXtSoftware\0Adobe ImageReadyqÉe<\0\0(iTXtXML:com.adobe.xmp\0\0\0\0\0<?xpacket begin="ï»¿" id="W5M0MpCehiHzreSzNTczkc9d"?> <x:xmpmeta xmlns:x="adobe:ns:meta/" x:xmptk="Adobe XMP Core 5.6-c014 79.156797, 2014/08/20-09:53:02        "> <rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"> <rdf:Description rdf:about="" xmlns:xmp="http://ns.adobe.com/xap/1.0/" xmlns:xmpMM="http://ns.adobe.com/xap/1.0/mm/" xmlns:stRef="http://ns.adobe.com/xap/1.0/sType/ResourceRef#" xmp:CreatorTool="Adobe Photoshop CC 2014 (Macintosh)" xmpMM:InstanceID="xmp.iid:6E2C95DEA67311E4BDCDDF91FAF94DA5" xmpMM:DocumentID="xmp.did:6E2C95DFA67311E4BDCDDF91FAF94DA5"> <xmpMM:DerivedFrom stRef:instanceID="xmp.iid:CFA74E4FA67111E4BDCDDF91FAF94DA5" stRef:documentID="xmp.did:CFA74E50A67111E4BDCDDF91FAF94DA5"/> </rdf:Description> </rdf:RDF> </x:xmpmeta> <?xpacket end="r"?>‹þöÊ\0\0IDATxÚì]	œSÕÕ?/{2Édf€aq]67ÐÏ­(*¨-\nöó³¶.õ+ÖÖ…º nµJÁ­öS‹R´Õ:VDT¤,eÑ2¨l‚ ¬‚ì‹3ÌÂL’—÷Ý›üosæN’ÉPqÌùý$“—÷î»÷üÏùŸsï»1†NY96¤ÚtÒØîïS±/QÄý]k~K¡“…îz›Ðí>ƒ%4ß¤Ò5ºú­<²Ù,²ÍclmYóÎÊ’„ž''ôÇB¯hô·£BóLZ¸ÞM?›¤°\0]s™GÖ>¾×âZ(4W¨]h\r"Ò¾&F4™]þ¶?JKD$úF>Yd-}QŠZY eå»)­„ž*t€ÐÓ„¶êà$»‰\r	=(t•ÐéBg	=Á¹íø_‚´¢Ñ”Q\0mÆVí+³SvaŠD›WÇgöýŽB¯ú¿B»eøÞB¯ºLèH¡Ò›#tó»BÇ	ýmFW’\0;tÈ _ŽÙì@–ÍÑš¿x„Þ.t!¿[Î!#à\\¡÷ã|ÉäWˆ’:÷Ø\rG³ I³·9é‰Ò*Ê6ËÈ­ùJk¡S…þAèqGÀN¼\09¤›EBïM¹~-4?í™Õ~ã I‹}Ô&·yåeYêØüå¡o\níu„Ï{.r»½Bk¸Öiv?Š—úLG·´Ñ”µjÈ-+ß‘Qæ•£\02%>ä|™Êï(^Í›’ß\nêXèÆr³æTÒÏRÇæ-’*öÿ–®-ãÑR¡Ë…V±¿ËBIË:GÊTÌ#þÉ5iþ\ZE"Fó”lD;æ\\_äp¾ýsjÊdñ‘“õB"t‡ÐB)ô2äwßÄïÏŠ»÷`”æ­ôÐ¤•^š¶ÑMnA!æ<¬YË>†¼ž¥’í§ö\n«ktõMæJw%ù»œ<.ÿÝÂÃB¿U\\GñBI?ç¦“]\0ÌoÆÐôå^ýô¥ºeF&.÷‘×a‘Ça5ï±Íš÷±#Ò£o>¤=L^«Ñþ]FŽžÚßJ…N\0ƒG¹ùã…¾V‡\Zú¢!q#!Å–ÛéÀ6=Xê§9›\\T2Èï²¨{«™cÑæ³,ÐŽ)zak³l´ú@1õÏß{¡„^£ýMF”¡_bÕæ(7ý9¡£­‘+''J«Ö{hñ—nê¡[\\ôhiµóGc41''''±Mëû1¶Ž³Ï>;káÇL›¿ŒÞÜÝ‹ú·Ø$Ü|Æ~ð,Š—Ý•”xß ¶lÚâ(4W.Ó\ZAjÕˆÂrâãsrÉWµÉMÐW”rÝ"zµhž«>²í;™§Y”çªŠE,0ÞŒ\\¾\\1ïbïº¯å"á''„¾‘ÁxËüêuÐLYÌÈ£x9ß)´ù^[\0›PôXVç‚NXá¥Í»T("Y¶‰u‹ßWevŠÝˆRUØC55AòØC™ÐGY6?‘½ÿ@è«Ú1k„VS|‚9ÈIîXÊè£Ä*rDÊ"€öL¡ƒ„ÎŽ=*@4é«=NÊwZj‰9¾%pÉöÊ%aíñ~ÐyBe–•˜øíµ´¢¢\r•ììKÃ;HTëoè+ƒ)¾QÉ_Å¸t¡Ôë‰cIìUžÈ±B"”VÚªÈVg>LÊvW¦Sâ1›|òX»¶îpÑ°Éy´í Zx£rR­Å''·# °)ýÅWšHª[ ÔöÊº±	Ý(#ò=B2ŠûC¡Û²@ËJL,H$ËÝµ(ñŸN)2š©°''¿›ä˜®\ZµÔ/*Ïð''rZŸKDÜûV((ßÕg	Œ²Å›{€ÆP“]aYÇ ¹jßŠ=B³\n"4~n€VnwR÷¶‘XŽFñUÿrQsÎJdäÜ\Zz¾ô7"ày¡7e©cVŽŠ¸ªéý½Ýèäàê%”BþT`Ë…SòOÐ;]NNy±XÞz7â¶s´Ñ}óôä‡~j/¨ß¬\rnª´·ÒFÃz×ÐÈ*ˆÄf–Û‰æ´Œ5)×ÜEžè¡EK|ôÖj·4Èù4\rI.òÂ–”x¶ÍÛÄ.ŒjÀ6ˆ¨GJŽG_÷†Ó˜‘Zz1`NxãPŠã<8¦^þÈ$6“vÔhUYõ\nìG¶äójrÞ¬/^ïDŽ¥‹¤aÝSš£ÛšImÂ·Lœ•\Zû^ fZË†A36ºck å{,\Z9 RÜ±@WµQ% °Zädó·×ãWmö†ož–''rKŠÇJöŠöU0Ã7YÞ§D~&*¯ØòÑÿ•‡1~ÿé…&=PH:	ïÇeÖ°È~¹|Ôd4ÅŸ:æÒ‡âë\nÛã˜{ŽÔÅ£TÇ¹+h²,ó‹ˆÖ9wwªÍzäüX\0¯¿ýÒå-‡K€ÌN/RÛðÍÏÌÌ\rÝ3-H…h+Ÿapymv+ÒÊgÉ§¥7·òE#ó¶:éê’*¹ò€ÛêZ;xù×ÎwÉ*‰¯Kq¨8WàQ€Ì€È¶<ð{Xž&Ò£”xPTRÏ;PÄ‘ ü#œ—›š²%Â·''íÈH/¼d–¢&Añy©ø?™• ²åLjÈ‡6á="²9¡t9\Z_¨»2I$Ûõ\0éKí,Ú-"Ùh²žùg.Ýþv°°8ß¼Éa‹=¨Ùž]MV*D-z²c0:òz7yßÉ£GÏ¯(/y=¿|Ò§^[¹Óº¶caäq>ÑºQj‹ÐÇ)¾bD—¯ÙkyƒŸJ@ãýÖc˜å¤K–·¡ £leh\rK„ÑÅ)¨ãAxnývF8\r“J¶Bww*%Gl^Í–Ì‹*ùDûLVÜþ†ª —)QÓxÀæ1×NTñ7oæÑ.ß|Îe§S¢Vì>^„ÑÈüèB/‘\ZŽÒ½ÇÍqSEÞöÅ>;-Þî41út¯ö¡¦iDÅç“8U”}''Ñ"ôOBÏz–#ñœÍÅÞw¢ø–v8;¹„ìIºF>È:Ô¹³ã°€âO4Dåå³r—‚ÖáŒÆSÝmò.¦øF!%¦8äŠ›I¬‚y"úº“†§‹á„eÛ*HàNEÃf£t5ÅçK#Á®L’›BâçÅEÁ3ãÙ6P• Ëý$ŠV72öã;ÜèP"1úÃ“È¹ ùàòÄ¡Ú¹ƒÔ\\y¨Õ ä(ªrÿ"ªW²>•ƒªÝzúSñ}‹ê|?AŸÈ6N£øŠyÎ‹p9H«’\0¢­ŠÄ6Â1‹6Ê§PÔZ}iÍ"§²R7Å%’ÞOÞèÄûíÑ‹kCU!j/@61à²z	Ê''ó‰[„~¥åw×£ú7V\0q_®+ö}ê˜o^e4"6¶‰Ž½Â¨ôs„þ¶ô!Å×\\râÊkžJN­Tò—$@“Q÷Nª¿®SÉù ¥éæÌ$yZ(_õ:ë{éÀäW,”¤\r·/ƒ¹u¹\ZsÖx†çÑ°qhèØæ‚ç«Ð.ŸÞ}	žŠ‹ìg(¾b[\ZÖsðL7ãüRF¡ä+¯%¡(ÁßGÃ8"ð4¤rê)Ú5VÂ‚6IÙFõ\r:ï XR>9±ÚQ;G)Ú´ïï¦øÀ^–ÓLÈ°RÆ#‹ÌÓ.cà/Â õÀ5/dÔIzf9¹,ç›æ}Og+>{8¾²¾xqï*êîÀk¹‚^ßZ`5¢Êr¹uÀÜuúºÊ ¾è"’IÍE[uT‹\\µ\nÑqŒÈßfåº-éÏµâÍº/I4%8´{0Æ7"W+k «¼T…ŒGa_édC:¶ö¨2ÙÆá¸ßv\0ÝYìóµÈ-»£ß{À¶ú0ÇŸNì6VýñÂàû!1ýšyˆš\0@LF™yè@:ö:xæ:¬/+EŸÃ®u.^f=0	²WÈV"b…¦KÐ5¬3G í„ht¸#À+Áõ>?÷àÇ½=‚ö˜0Ž}0žÎt É¼×´u\rþVˆ¶Ê9¬)ŒftÑ¼~;¼žWŸ9XtÈtÆŠ#)\n6Exý9r¢óá8¸¼ë_K€Ì¥gè‘—æDûŠœì¬š¿+	È¸¼\nìáZ­ôþušï-@ßGðÝLÊò©¢ÝM\ZÈÂ`Òñ^Nñ\r‚^F¥È«	ÇÝÈÞÏ` #¸ÎbLåFDÚ~”xÖŽ@7Ÿ„Ó:ÉÛþ!lò1Àl\Z—•''þ9@p)‹báPbNæUP®óq¬â¬÷ñþt€è8’"–''ª\r\\&!Âª²·ã&OGG×‚bÞnf`]Ê0çèN=m?‘ÏkõÂëÛXïF$?µ±2übP\n±ÁØ•p¶0˜ë{õEAoh¿Œ|®êdùYë·E,ÇqjQe˜0×íT`ÒËK|4bf.äDÉf‹õ‡ŒÚo³èžNÔDøÃªÒý=l,uÙ1¹\\£¤•vßU‹‹¼îP°šwÀB®G_û)1§\0»NŸo©°¡–¥CØçÄÆç&ÒûØç—Í<Pq~ÿ6ª®²cmZ˜žÀæb>Æ	”\\£U†ñ£ÿ‚'' øyø>!Ü¶F¾äG´,ÍGä²ÊMOÉf ÊuQçìÀÕàýnxª©ˆŠªHðrL<Ü4Š?f/å qR¦ƒêJÊ¿É¤%e"£‰È2Žs!Ö§Œ!¨¯''ó~Ëëp\r#Jeå¹+R=uËúm£^J¾‚U’|¨²ÊFïop“C¼öú(h_oI3™Ì]puB4û+®q.¢ÖC—¶I[ÙIM›gTm(‹üFò‡Fœg/"êX–·®Aÿð]™¯\03#°¨)IÎ5ã­rØ¬ÀcÓ¨}ÊªãGÚû…@©@Q¾|\0ºÂE(ˆN‘žåw”ØÌ¥þuDÎK\0–þhÔ*äYªC¥.ƒ1ÛàUrX4<žy¢ÅÌ;²Èù_B¿@‡D1p~mxÞVËÞ¯è»g´5Zeò+xÐãàH¶Ì''ÃP\rDé¾¬Ÿë€lw­ŸúöPÍÌdùxì>]h÷ü­B‹°›ÕG¼¾ÔG¯­ðR·Âˆ|ÈÒ`ýœéQìêvð¢ÛÓ ÀB¿†C,Eî¶ˆROøg*µ÷/6òû9 pª8!Ä­Œæì”¯ q"BZ45xH2Å¢-G: ™I83%©¾Õ$ñ‚|™‹aYÛ\0<Ü´¤Z½ÈW³›Ø0ì&ªÑæÏaTnÍàU¹ØÃ¢ÓZtš¦epª\ZØÔi#E¿™ŒF¨6Îýé*Ûí\\çóo¾­&H''	=Ðc6¹m"Ä¶6¨WéÆÜJVQSq*ê²¨b§“¦,óQ› ©žd¶±{Î4Òð{r²±Êu!Æ²è×F¡n£¦-èµ’Ì–7a®íL­¸‘ƒê7i@ã{PžLé–®%$Â(Ðô''qû°c¶€Ãw`ž”`@ùˆ5	´eöÀ°Ôã\Z—²Èò9ÀfÕ£¡š—V%õ®¯¢“M(–Ã«úqÎaðúês¾ß÷Q€è¨w^Ë“wÒ\nRZ0Ç²‹EÍOàtn`Å‡Åè—¸eY=Þm.ù÷’''2_ªýCN`Æ®Š(!–kÇûMÐÄû4u›º%¼4)±H¦[øÐ/ª;õý}\\Œþ8›*äXüŒÔ=Mwh\r\Zt\nÑ\\''"Ü\nÆ„ìZ~M3µ-9Ù8ÚR8‰¤g†_@uWNÀæ4º²ê“šÏ¹›5b5ËóT^RcSVh§bà>b\0#äØ5:¡,ûwÌcT§¨Pma×ù¢ú¼''¦ þE	e0ÃXÔŽBI‹Fx\\y¾•ÀUìõ"­8¡rŽãõ[ü„EÞr:!5ˆdIA–Çhí''Z4Wt°¶¶¯gÎ4é†²övä<ÇQý]ˆ-ôÿ\\âØA(5`m,¯	çšŒÂ‰’V¨\ZúXd¯Ñ*¦çPbŽõ<¦B%+û#c.V* éíPŽ”˜,VÕ™y(:Œ\0°F#"|Fu7†™Áªl+\0(¿–´ïÅ€ôbóŸ²‚Ì`xÚ—PRý7Õ*)ù.^U(è\\ŽÎœŠ÷;†¶è„«PÐ™\0ƒy—\ZÀ™êÇñèü\0ˆjËì8™§ý\ZÇÐó“ÞÝyÙd…Qæe©Ÿ®Zó	ÆEOÀ·ýÛhÂu)ˆÐÐîµ´h»3¶!›[SUÄ''¨á_Œˆqü¢õÇ(.ý”RO¯BÑbòºW›Ñ¶hï¯Ô‹G\rÈN«ÖÀ)žÎîé|V¦FühÿÁ&D]îôêE4 ky6hwâõ =ßàÄ?D©½''›³¹•uüb-áTsû˜G•²”y­yˆ¬`8—¢ßÇ<…\na+VÂ\rh÷2	óxš«1×Ñ–MA¼y—)lÞk$¸ü³¬H‘Ãy™7üýt\r¢•jËHô/6-ÓJÀu&<}"¢QÄ•n@{³jðK\Zµ.`-N±BÚ†éšÓ«hµM/^•‚\\ß€1vSÃ/GJP”!½6#¢½£9¶:Cbý±öð€æDÄRÝ_Ì9ø†C\r‹þSU]SE4;¼òÐBªo¯"¢(ùø÷Px)''ŒåÌµpÊ²Ô	ÞÓœ{ˆPï4	þå0,@:ÑL•\\jÕ_ðùgù08‚0îc‹"!Üë,x77®ñ\Z\nðdû"~hàƒç“çºùÞ~P•9I¸ý,FSçÔ;km@F2?\n%û5z|:ú¸5hÐDíÛCØtGÂTET“å}-þËqzç{\n¥ûiIî³Q¿3þ#æÌdtþ-Æ!ÙDWDÒ#w#e&°š\ZñÀQNDûw•€Ý”jíq²¢Ô{p¤£k›§£+Ü{gZÇ«rÐ¥l«Á"ülŽm$lÛp$I?€1»`<ÉøË:$“êìBiæb–B“c]šÎÝ€k8q°vC”d’W“èxUÞ''¹yž¿À¡¬ ðQ’é.ˆ=o…3µV…˜ %–þ|Fõ×=ŠVÊ+­ž$‹E''^9Oõ7KíÏLõƒè½²Z…äÈ«³IÎ4xø‡a°ÏÂA•á>ÎÄgÐ·³³¾\0`_c:\ZÁ˜\rF´(Dä~@3Ñ·3)±¾S:¸ß _?ðµ€“\\¨M)è´î8Ö~x/ÆïÁÆF!ÕPnép/Ø½åØ%h[pÝ‹Xes,Ú¾Ä‘¢\nieLÓ˜ÉD÷JKKrÖu”A˜)JÀw"öd´±þò¥x^¶ÌâWÈû\npÓÉæ$¹Æ\0D÷(¢-‹]]¢Ó:†hk¹çi„üd3þ¿ªñ+ðî;ØßUnþ4\nKÉ¶P(PÇhýë×\n;^»Æ²”,]|ž„X¾Ë«±Tw	VžÆÞv\0¼uª¿à¾žbS(ÁÕÙ–Z?=g`x’ÚÑ¡5ÀMY9\Z’ƒ\\Sl3¨e:Y	jæBn©v¡J5—sûzÒSÐÆ.''ÔÒ%›jhÌœ€\0Z=_ð\Z¢ó¥˜öh`oe[šâºË)ñ#ñ2§i°ìŸMÉ7×™ŽbV÷õ«„N@tòSý_]ŠëýÿwfHuÍ™He¦ãmÐéÿÛHk ZµDÛ½øî''(˜ƒŠ¹NT»ç|_;ç"´ëVJLÉ”i¾ñòË/÷BhŒ ‘³¸8â"©Æ/á!wÂûšìÀëÊF5åüvT!+QÁ­/A“FOÒ˜Òê4›CŸ\Zp0jC•\nJÌ''6¸£Qš~Ô7vD3/ØB5¨ªÕ@ÛZ pÉãËþ_€\0³à¯˜s]Jý\0\0\0\0IEND®B`‚'),
(2, 0, 'Canned Attachments Rock!');

-- --------------------------------------------------------

--
-- Table structure for table `[[dbprefix]]dk_filter`
--

CREATE TABLE IF NOT EXISTS `[[dbprefix]]dk_filter` (
  `id` int(11) unsigned NOT NULL,
  `execorder` int(10) unsigned NOT NULL DEFAULT '99',
  `isactive` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `status` int(11) unsigned NOT NULL DEFAULT '0',
  `match_all_rules` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `stop_onmatch` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `target` enum('Any','Web','Email','API') NOT NULL DEFAULT 'Any',
  `email_id` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(32) NOT NULL DEFAULT '',
  `notes` text,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `[[dbprefix]]dk_filter`
--

INSERT INTO `[[dbprefix]]dk_filter` VALUES
(1, 99, 1, 0, 0, 0, 'Email', 0, 'SYSTEM BAN LIST', 'Internal list for email banning. Do not remove', '[[regtime]]', '[[regtime]]');

-- --------------------------------------------------------

--
-- Table structure for table `[[dbprefix]]dk_filter_action`
--

CREATE TABLE IF NOT EXISTS `[[dbprefix]]dk_filter_action` (
  `id` int(11) unsigned NOT NULL,
  `filter_id` int(10) unsigned NOT NULL,
  `sort` int(10) unsigned NOT NULL DEFAULT '0',
  `type` varchar(24) NOT NULL,
  `configuration` text,
  `updated` datetime NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `[[dbprefix]]dk_filter_action`
--

INSERT INTO `[[dbprefix]]dk_filter_action` VALUES
(1, 1, 1, 'reject', '[]', '[[regtime]]');

-- --------------------------------------------------------

--
-- Table structure for table `[[dbprefix]]dk_filter_rule`
--

CREATE TABLE IF NOT EXISTS `[[dbprefix]]dk_filter_rule` (
  `id` int(11) unsigned NOT NULL,
  `filter_id` int(10) unsigned NOT NULL DEFAULT '0',
  `what` varchar(32) NOT NULL,
  `how` enum('equal','not_equal','contains','dn_contain','starts','ends','match','not_match') NOT NULL,
  `val` varchar(255) NOT NULL,
  `isactive` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `notes` tinytext NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `[[dbprefix]]dk_filter_rule`
--

INSERT INTO `[[dbprefix]]dk_filter_rule` VALUES
(1, 1, 'email', 'equal', 'test@example.com', 1, '', '[[regtime]]', '[[regtime]]');

-- --------------------------------------------------------

--
-- Table structure for table `[[dbprefix]]dk_form`
--

CREATE TABLE IF NOT EXISTS `[[dbprefix]]dk_form` (
  `id` int(11) unsigned NOT NULL,
  `pid` int(10) unsigned DEFAULT NULL,
  `type` varchar(8) NOT NULL DEFAULT 'G',
  `flags` int(10) unsigned NOT NULL DEFAULT '1',
  `title` varchar(255) NOT NULL,
  `instructions` varchar(512) DEFAULT NULL,
  `name` varchar(64) NOT NULL DEFAULT '',
  `notes` text,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `[[dbprefix]]dk_form`
--

INSERT INTO `[[dbprefix]]dk_form` VALUES
(1, NULL, 'U', 1, 'Contact Information', NULL, '', NULL, '[[regtime]]', '[[regtime]]'),
(2, NULL, 'T', 1, 'Ticket Details', 'Please Describe Your Issue', '', 'This form will be attached to every ticket, regardless of its source.\nYou can add any fields to this form and they will be available to all\ntickets, and will be searchable with advanced search and filterable.', '[[regtime]]', '[[regtime]]'),
(3, NULL, 'C', 1, 'Company Information', 'Details available in email templates', '', NULL, '[[regtime]]', '[[regtime]]'),
(4, NULL, 'O', 1, 'Organization Information', 'Details on user organization', '', NULL, '[[regtime]]', '[[regtime]]'),
(6, NULL, 'L1', 1, 'Ticket Status Properties', 'Properties that can be set on a ticket status.', '', NULL, '[[regtime]]', '[[regtime]]');

-- --------------------------------------------------------

--
-- Table structure for table `[[dbprefix]]dk_form_entry`
--

CREATE TABLE IF NOT EXISTS `[[dbprefix]]dk_form_entry` (
  `id` int(11) unsigned NOT NULL,
  `form_id` int(11) unsigned NOT NULL,
  `object_id` int(11) unsigned DEFAULT NULL,
  `object_type` char(1) NOT NULL DEFAULT 'T',
  `sort` int(11) unsigned NOT NULL DEFAULT '1',
  `extra` text,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `[[dbprefix]]dk_form_entry`
--

INSERT INTO `[[dbprefix]]dk_form_entry` VALUES
(1, 4, 1, 'O', 1, NULL, '[[regtime]]', '[[regtime]]'),
(2, 3, NULL, 'C', 1, NULL, '[[regtime]]', '[[regtime]]'),
(3, 1, 1, 'U', 1, NULL, '[[regtime]]', '[[regtime]]'),
(4, 2, 1, 'T', 0, '{"disable":[]}', '[[regtime]]', '[[regtime]]');

-- --------------------------------------------------------

--
-- Table structure for table `[[dbprefix]]dk_form_entry_values`
--

CREATE TABLE IF NOT EXISTS `[[dbprefix]]dk_form_entry_values` (
  `entry_id` int(11) unsigned NOT NULL,
  `field_id` int(11) unsigned NOT NULL,
  `value` text,
  `value_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `[[dbprefix]]dk_form_entry_values`
--

INSERT INTO `[[dbprefix]]dk_form_entry_values` VALUES
(1, 28, '420 Desoto Street\nAlexandria, LA 71301', NULL),
(1, 29, '3182903674', NULL),
(1, 30, 'http://deskuss.com', NULL),
(1, 31, 'Not only do we develop the software, we also use it to manage support for Deskuss. Let us help you quickly implement and leverage the full potential of Deskuss''s features and functionality. Contact us for professional support or visit our website for documentation and community support.', NULL),
(2, 23, 'Deskuss - Support Ticket System', NULL),
(2, 24, NULL, NULL),
(2, 25, NULL, NULL),
(2, 26, NULL, NULL),
(3, 3, NULL, NULL),
(3, 4, NULL, NULL),
(4, 20, 'Deskuss Installed!', NULL),
(4, 22, 'Normal', 2);

-- --------------------------------------------------------

--
-- Table structure for table `[[dbprefix]]dk_form_field`
--

CREATE TABLE IF NOT EXISTS `[[dbprefix]]dk_form_field` (
  `id` int(11) unsigned NOT NULL,
  `form_id` int(11) unsigned NOT NULL,
  `flags` int(10) unsigned DEFAULT '1',
  `type` varchar(255) NOT NULL DEFAULT 'text',
  `label` varchar(255) NOT NULL,
  `name` varchar(64) NOT NULL,
  `configuration` text,
  `sort` int(11) unsigned NOT NULL,
  `hint` varchar(512) DEFAULT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `[[dbprefix]]dk_form_field`
--

INSERT INTO `[[dbprefix]]dk_form_field` VALUES
(1, 1, 489379, 'text', 'Email Address', 'email', '{"size":40,"length":64,"validator":"email"}', 1, NULL, '[[regtime]]', '[[regtime]]'),
(2, 1, 489379, 'text', 'Full Name', 'name', '{"size":40,"length":64}', 2, NULL, '[[regtime]]', '[[regtime]]'),
(3, 1, 13057, 'phone', 'Phone Number', 'phone', NULL, 3, NULL, '[[regtime]]', '[[regtime]]'),
(4, 1, 12289, 'memo', 'Internal Notes', 'notes', '{"rows":4,"cols":40}', 4, NULL, '[[regtime]]', '[[regtime]]'),
(20, 2, 489249, 'text', 'Issue Summary', 'subject', '{"size":40,"length":50}', 1, NULL, '[[regtime]]', '[[regtime]]'),
(21, 2, 480547, 'thread', 'Issue Details', 'message', NULL, 2, 'Details on the reason(s) for opening the ticket.', '[[regtime]]', '[[regtime]]'),
(22, 2, 274609, 'priority', 'Priority Level', 'priority', NULL, 3, NULL, '[[regtime]]', '[[regtime]]'),
(23, 3, 291233, 'text', 'Company Name', 'name', '{"size":40,"length":64}', 1, NULL, '[[regtime]]', '[[regtime]]'),
(24, 3, 12545, 'text', 'Website', 'website', '{"size":40,"length":64}', 2, NULL, '[[regtime]]', '[[regtime]]'),
(25, 3, 12545, 'phone', 'Phone Number', 'phone', '{"ext":false}', 3, NULL, '[[regtime]]', '[[regtime]]'),
(26, 3, 12545, 'memo', 'Address', 'address', '{"rows":2,"cols":40,"html":false,"length":100}', 4, NULL, '[[regtime]]', '[[regtime]]'),
(27, 4, 489379, 'text', 'Name', 'name', '{"size":40,"length":64}', 1, NULL, '[[regtime]]', '[[regtime]]'),
(28, 4, 13057, 'memo', 'Address', 'address', '{"rows":2,"cols":40,"length":100,"html":false}', 2, NULL, '[[regtime]]', '[[regtime]]'),
(29, 4, 13057, 'phone', 'Phone', 'phone', NULL, 3, NULL, '[[regtime]]', '[[regtime]]'),
(30, 4, 13057, 'text', 'Website', 'website', '{"size":40,"length":0}', 4, NULL, '[[regtime]]', '[[regtime]]'),
(31, 4, 12289, 'memo', 'Internal Notes', 'notes', '{"rows":4,"cols":40}', 5, NULL, '[[regtime]]', '[[regtime]]'),
(34, 6, 487665, 'state', 'State', 'state', '{"prompt":"State of a ticket"}', 1, NULL, '[[regtime]]', '[[regtime]]'),
(35, 6, 471073, 'memo', 'Description', 'description', '{"rows":2,"cols":40,"html":false,"length":100}', 3, NULL, '[[regtime]]', '[[regtime]]');

-- --------------------------------------------------------

--
-- Table structure for table `[[dbprefix]]dk_group`
--

CREATE TABLE IF NOT EXISTS `[[dbprefix]]dk_group` (
  `id` int(10) unsigned NOT NULL,
  `role_id` int(11) unsigned NOT NULL,
  `flags` int(11) unsigned NOT NULL DEFAULT '1',
  `name` varchar(120) NOT NULL DEFAULT '',
  `notes` text,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `[[dbprefix]]dk_list`
--

CREATE TABLE IF NOT EXISTS `[[dbprefix]]dk_list` (
  `id` int(11) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `name_plural` varchar(255) DEFAULT NULL,
  `sort_mode` enum('Alpha','-Alpha','SortCol') NOT NULL DEFAULT 'Alpha',
  `masks` int(11) unsigned NOT NULL DEFAULT '0',
  `type` varchar(16) DEFAULT NULL,
  `configuration` text NOT NULL,
  `notes` text,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `[[dbprefix]]dk_list`
--

INSERT INTO `[[dbprefix]]dk_list` VALUES
(1, 'Ticket Status', 'Ticket Statuses', 'SortCol', 13, 'ticket-status', '{"handler":"TicketStatusList"}', 'Ticket statuses', '[[regtime]]', '[[regtime]]');

-- --------------------------------------------------------

--
-- Table structure for table `[[dbprefix]]dk_list_items`
--

CREATE TABLE IF NOT EXISTS `[[dbprefix]]dk_list_items` (
  `id` int(11) unsigned NOT NULL,
  `list_id` int(11) DEFAULT NULL,
  `status` int(11) unsigned NOT NULL DEFAULT '1',
  `value` varchar(255) NOT NULL,
  `extra` varchar(255) DEFAULT NULL,
  `sort` int(11) NOT NULL DEFAULT '1',
  `properties` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `[[dbprefix]]dk_lock`
--

CREATE TABLE IF NOT EXISTS `[[dbprefix]]dk_lock` (
  `lock_id` int(11) unsigned NOT NULL,
  `staff_id` int(10) unsigned NOT NULL DEFAULT '0',
  `expire` datetime DEFAULT NULL,
  `code` varchar(20) DEFAULT NULL,
  `created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `[[dbprefix]]dk_note`
--

CREATE TABLE IF NOT EXISTS `[[dbprefix]]dk_note` (
  `id` int(11) unsigned NOT NULL,
  `pid` int(11) unsigned DEFAULT NULL,
  `staff_id` int(11) unsigned NOT NULL DEFAULT '0',
  `ext_id` varchar(10) DEFAULT NULL,
  `body` text,
  `status` int(11) unsigned NOT NULL DEFAULT '0',
  `sort` int(11) unsigned NOT NULL DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `[[dbprefix]]dk_organization`
--

CREATE TABLE IF NOT EXISTS `[[dbprefix]]dk_organization` (
  `id` int(11) unsigned NOT NULL,
  `name` varchar(128) NOT NULL DEFAULT '',
  `manager` varchar(16) NOT NULL DEFAULT '',
  `status` int(11) unsigned NOT NULL DEFAULT '0',
  `domain` varchar(256) NOT NULL DEFAULT '',
  `extra` text,
  `created` timestamp NULL DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `[[dbprefix]]dk_organization`
--

INSERT INTO `[[dbprefix]]dk_organization` VALUES
(1, 'Deskuss', '', 8, '', NULL, '[[regtime]]', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `[[dbprefix]]dk_queue`
--

CREATE TABLE IF NOT EXISTS `[[dbprefix]]dk_queue` (
  `id` int(11) unsigned NOT NULL,
  `parent_id` int(11) unsigned NOT NULL DEFAULT '0',
  `flags` int(11) unsigned NOT NULL DEFAULT '0',
  `staff_id` int(11) unsigned NOT NULL DEFAULT '0',
  `sort` int(11) unsigned NOT NULL DEFAULT '0',
  `title` varchar(60) DEFAULT NULL,
  `config` text,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `[[dbprefix]]dk_role`
--

CREATE TABLE IF NOT EXISTS `[[dbprefix]]dk_role` (
  `id` int(11) unsigned NOT NULL,
  `flags` int(10) unsigned NOT NULL DEFAULT '1',
  `name` varchar(64) DEFAULT NULL,
  `permissions` text,
  `notes` text,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `[[dbprefix]]dk_role`
--

INSERT INTO `[[dbprefix]]dk_role` VALUES
(1, 1, 'All Access', '{"ticket.create":1,"ticket.edit":1,"ticket.assign":1,"ticket.transfer":1,"ticket.reply":1,"ticket.close":1,"ticket.delete":1,"thread.edit":1,"canned.manage":1}', 'Role with unlimited access', '[[regtime]]', '[[regtime]]'),
(2, 1, 'Expanded Access', '{"ticket.create":1,"ticket.edit":1,"ticket.assign":1,"ticket.transfer":1,"ticket.reply":1,"ticket.close":1,"canned.manage":1}', 'Role with expanded access', '[[regtime]]', '[[regtime]]'),
(3, 1, 'Limited Access', '{"ticket.create":1,"ticket.assign":1,"ticket.transfer":1}', 'Role with limited access', '[[regtime]]', '[[regtime]]'),
(4, 1, 'View only', NULL, 'Simple role with no permissions', '[[regtime]]', '[[regtime]]');

-- --------------------------------------------------------

--
-- Table structure for table `[[dbprefix]]dk_sequence`
--

CREATE TABLE IF NOT EXISTS `[[dbprefix]]dk_sequence` (
  `id` int(11) unsigned NOT NULL,
  `name` varchar(64) DEFAULT NULL,
  `flags` int(10) unsigned DEFAULT NULL,
  `next` bigint(20) unsigned NOT NULL DEFAULT '1',
  `increment` int(11) DEFAULT '1',
  `padding` char(1) DEFAULT '0',
  `updated` datetime NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `[[dbprefix]]dk_sequence`
--

INSERT INTO `[[dbprefix]]dk_sequence` VALUES
(1, 'General Tickets', 1, 1, 1, '0', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `[[dbprefix]]dk_session`
--

CREATE TABLE IF NOT EXISTS `[[dbprefix]]dk_session` (
  `session_id` varchar(255) CHARACTER SET ascii NOT NULL DEFAULT '',
  `session_data` blob,
  `session_expire` datetime DEFAULT NULL,
  `session_updated` datetime DEFAULT NULL,
  `user_id` varchar(16) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0' COMMENT 'Deskuss staff/client ID',
  `user_ip` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `user_agent` varchar(255) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `[[dbprefix]]dk_sla`
--

CREATE TABLE IF NOT EXISTS `[[dbprefix]]dk_sla` (
  `id` int(11) unsigned NOT NULL,
  `flags` int(10) unsigned NOT NULL DEFAULT '3',
  `grace_period` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(64) NOT NULL DEFAULT '',
  `notes` text,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `[[dbprefix]]dk_sla`
--

INSERT INTO `[[dbprefix]]dk_sla` VALUES
(1, 3, 48, 'Default SLA', NULL, '[[regtime]]', '[[regtime]]');

-- --------------------------------------------------------

--
-- Table structure for table `[[dbprefix]]dk_staff`
--

CREATE TABLE IF NOT EXISTS `[[dbprefix]]dk_staff` (
  `staff_id` int(11) unsigned NOT NULL,
  `dept_id` int(10) unsigned NOT NULL DEFAULT '0',
  `role_id` int(10) unsigned NOT NULL DEFAULT '0',
  `username` varchar(32) NOT NULL DEFAULT '',
  `firstname` varchar(32) DEFAULT NULL,
  `lastname` varchar(32) DEFAULT NULL,
  `passwd` varchar(128) DEFAULT NULL,
  `backend` varchar(32) DEFAULT NULL,
  `email` varchar(128) DEFAULT NULL,
  `phone` varchar(24) NOT NULL DEFAULT '',
  `phone_ext` varchar(6) DEFAULT NULL,
  `mobile` varchar(24) NOT NULL DEFAULT '',
  `signature` text NOT NULL,
  `lang` varchar(16) DEFAULT NULL,
  `timezone` varchar(64) DEFAULT NULL,
  `locale` varchar(16) DEFAULT NULL,
  `notes` text,
  `isactive` tinyint(1) NOT NULL DEFAULT '1',
  `isadmin` tinyint(1) NOT NULL DEFAULT '0',
  `isvisible` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `onvacation` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `assigned_only` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `show_assigned_tickets` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `change_passwd` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `max_page_size` int(11) unsigned NOT NULL DEFAULT '0',
  `auto_refresh_rate` int(10) unsigned NOT NULL DEFAULT '0',
  `default_signature_type` enum('none','mine','dept') NOT NULL DEFAULT 'none',
  `default_paper_size` enum('Letter','Legal','Ledger','A4','A3') NOT NULL DEFAULT 'Letter',
  `extra` text,
  `permissions` text,
  `created` datetime NOT NULL,
  `lastlogin` datetime DEFAULT NULL,
  `passwdreset` datetime DEFAULT NULL,
  `updated` datetime NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `[[dbprefix]]dk_staff_dept_access`
--

CREATE TABLE IF NOT EXISTS `[[dbprefix]]dk_staff_dept_access` (
  `staff_id` int(10) unsigned NOT NULL DEFAULT '0',
  `dept_id` int(10) unsigned NOT NULL DEFAULT '0',
  `role_id` int(10) unsigned NOT NULL DEFAULT '0',
  `flags` int(10) unsigned NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `[[dbprefix]]dk_syslog`
--

CREATE TABLE IF NOT EXISTS `[[dbprefix]]dk_syslog` (
  `log_id` int(11) unsigned NOT NULL,
  `log_type` enum('Debug','Warning','Error') NOT NULL,
  `title` varchar(255) NOT NULL,
  `log` text NOT NULL,
  `logger` varchar(64) NOT NULL,
  `ip_address` varchar(64) NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- --------------------------------------------------------

--
-- Table structure for table `[[dbprefix]]dk_team`
--

CREATE TABLE IF NOT EXISTS `[[dbprefix]]dk_team` (
  `team_id` int(10) unsigned NOT NULL,
  `lead_id` int(10) unsigned NOT NULL DEFAULT '0',
  `flags` int(10) unsigned NOT NULL DEFAULT '1',
  `name` varchar(125) NOT NULL DEFAULT '',
  `notes` text,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `[[dbprefix]]dk_team`
--

INSERT INTO `[[dbprefix]]dk_team` VALUES
(1, 0, 1, 'Level I Support', 'Tier 1 support, responsible for the initial iteraction with customers', '[[regtime]]', '[[regtime]]');

-- --------------------------------------------------------

--
-- Table structure for table `[[dbprefix]]dk_team_member`
--

CREATE TABLE IF NOT EXISTS `[[dbprefix]]dk_team_member` (
  `team_id` int(10) unsigned NOT NULL DEFAULT '0',
  `staff_id` int(10) unsigned NOT NULL,
  `flags` int(10) unsigned NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `[[dbprefix]]dk_thread`
--

CREATE TABLE IF NOT EXISTS `[[dbprefix]]dk_thread` (
  `id` int(11) unsigned NOT NULL,
  `object_id` int(11) unsigned NOT NULL,
  `object_type` char(1) NOT NULL,
  `extra` text,
  `lastresponse` datetime DEFAULT NULL,
  `lastmessage` datetime DEFAULT NULL,
  `created` datetime NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `[[dbprefix]]dk_thread`
--

INSERT INTO `[[dbprefix]]dk_thread` VALUES
(1, 1, 'T', NULL, NULL, '[[regtime]]', '[[regtime]]');

-- --------------------------------------------------------

--
-- Table structure for table `[[dbprefix]]dk_thread_collaborator`
--

CREATE TABLE IF NOT EXISTS `[[dbprefix]]dk_thread_collaborator` (
  `id` int(11) unsigned NOT NULL,
  `isactive` tinyint(1) NOT NULL DEFAULT '1',
  `thread_id` int(11) unsigned NOT NULL DEFAULT '0',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `role` char(1) NOT NULL DEFAULT 'M',
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `[[dbprefix]]dk_thread_entry`
--

CREATE TABLE IF NOT EXISTS `[[dbprefix]]dk_thread_entry` (
  `id` int(11) unsigned NOT NULL,
  `pid` int(11) unsigned NOT NULL DEFAULT '0',
  `thread_id` int(11) unsigned NOT NULL DEFAULT '0',
  `staff_id` int(11) unsigned NOT NULL DEFAULT '0',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `type` char(1) NOT NULL DEFAULT '',
  `flags` int(11) unsigned NOT NULL DEFAULT '0',
  `poster` varchar(128) NOT NULL DEFAULT '',
  `editor` int(10) unsigned DEFAULT NULL,
  `editor_type` char(1) DEFAULT NULL,
  `source` varchar(32) NOT NULL DEFAULT '',
  `title` varchar(255) DEFAULT NULL,
  `body` text NOT NULL,
  `format` varchar(16) NOT NULL DEFAULT 'html',
  `ip_address` varchar(64) NOT NULL DEFAULT '',
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `[[dbprefix]]dk_thread_entry`
--

INSERT INTO `[[dbprefix]]dk_thread_entry` VALUES
(1, 0, 1, 0, 1, 'M', 65, 'Deskuss Support', NULL, NULL, 'Web', 'Deskuss Installed!', ' <p> Thank you for choosing Deskuss. </p> <p> Please make sure you join the <a href="https://www.softaculous.com/board/">Deskuss forums</a> and our <a href="http://deskuss.com/blog/">mailing list</a> to stay up to date on the latest news, security alerts and updates. The Deskuss forums are also a great place to get assistance, guidance, tips, and help from other Deskuss users. In addition to the forums, the Deskuss wiki provides a useful collection of educational materials, documentation, and notes from the community. We welcome your contributions to the Deskuss community. </p> <p> With SupportSystem''s turnkey infrastructure, you get Deskuss at its best, leaving you free to focus on your customers without the burden of making sure the application is stable, maintained, and secure. </p> <p> Cheers, </p> <p> -<br /> Deskuss Team http://deskuss.com/ </p> <p> <strong>PS.</strong> Don''t just make customers happy, make happy customers! </p> ', 'html', '127.0.0.1', '[[regtime]]', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `[[dbprefix]]dk_thread_entry_email`
--

CREATE TABLE IF NOT EXISTS `[[dbprefix]]dk_thread_entry_email` (
  `id` int(11) unsigned NOT NULL,
  `thread_entry_id` int(11) unsigned NOT NULL,
  `mid` varchar(255) NOT NULL,
  `headers` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `[[dbprefix]]dk_thread_event`
--

CREATE TABLE IF NOT EXISTS `[[dbprefix]]dk_thread_event` (
  `id` int(10) unsigned NOT NULL,
  `thread_id` int(11) unsigned NOT NULL DEFAULT '0',
  `staff_id` int(11) unsigned NOT NULL,
  `team_id` int(11) unsigned NOT NULL,
  `dept_id` int(11) unsigned NOT NULL,
  `topic_id` int(11) unsigned NOT NULL,
  `state` enum('created','closed','reopened','assigned','transferred','overdue','edited','viewed','error','collab','resent') NOT NULL,
  `data` varchar(1024) DEFAULT NULL COMMENT 'Encoded differences',
  `username` varchar(128) NOT NULL DEFAULT 'SYSTEM',
  `uid` int(11) unsigned DEFAULT NULL,
  `uid_type` char(1) NOT NULL DEFAULT 'S',
  `annulled` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `timestamp` datetime NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `[[dbprefix]]dk_thread_event`
--

INSERT INTO `[[dbprefix]]dk_thread_event` VALUES
(1, 1, 0, 0, 1, 1, 'created', NULL, 'SYSTEM', 1, 'U', 0, '[[regtime]]');

-- --------------------------------------------------------

--
-- Table structure for table `[[dbprefix]]dk_ticket`
--

CREATE TABLE IF NOT EXISTS `[[dbprefix]]dk_ticket` (
  `ticket_id` int(11) unsigned NOT NULL,
  `number` varchar(20) DEFAULT NULL,
  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `user_email_id` int(11) unsigned NOT NULL DEFAULT '0',
  `status_id` int(10) unsigned NOT NULL DEFAULT '0',
  `dept_id` int(10) unsigned NOT NULL DEFAULT '0',
  `sla_id` int(10) unsigned NOT NULL DEFAULT '0',
  `topic_id` int(10) unsigned NOT NULL DEFAULT '0',
  `staff_id` int(10) unsigned NOT NULL DEFAULT '0',
  `team_id` int(10) unsigned NOT NULL DEFAULT '0',
  `email_id` int(11) unsigned NOT NULL DEFAULT '0',
  `lock_id` int(11) unsigned NOT NULL DEFAULT '0',
  `flags` int(10) unsigned NOT NULL DEFAULT '0',
  `ip_address` varchar(64) NOT NULL DEFAULT '',
  `source` enum('Web','Email','Phone','API','Other') NOT NULL DEFAULT 'Other',
  `source_extra` varchar(40) DEFAULT NULL,
  `isoverdue` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `isanswered` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `duedate` datetime DEFAULT NULL,
  `est_duedate` datetime DEFAULT NULL,
  `reopened` datetime DEFAULT NULL,
  `closed` datetime DEFAULT NULL,
  `lastupdate` datetime DEFAULT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `[[dbprefix]]dk_ticket`
--

INSERT INTO `[[dbprefix]]dk_ticket` VALUES
(1, '915696', 1, 0, 1, 1, 1, 1, 0, 0, 0, 0, 0, '127.0.0.1', 'Web', NULL, 0, 0, NULL, '[[regtime_nextweek]]', NULL, NULL, '[[regtime]]', '[[regtime]]', '[[regtime]]');

-- --------------------------------------------------------

--
-- Table structure for table `[[dbprefix]]dk_ticket_priority`
--

CREATE TABLE IF NOT EXISTS `[[dbprefix]]dk_ticket_priority` (
  `priority_id` tinyint(4) NOT NULL,
  `priority` varchar(60) NOT NULL DEFAULT '',
  `priority_desc` varchar(30) NOT NULL DEFAULT '',
  `priority_color` varchar(7) NOT NULL DEFAULT '',
  `priority_urgency` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ispublic` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `[[dbprefix]]dk_ticket_priority`
--

INSERT INTO `[[dbprefix]]dk_ticket_priority` VALUES
(1, 'low', 'Low', '#DDFFDD', 4, 1),
(2, 'normal', 'Normal', '#FFFFF0', 3, 1),
(3, 'high', 'High', '#FEE7E7', 2, 1),
(4, 'emergency', 'Emergency', '#FEE7E7', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `[[dbprefix]]dk_ticket_status`
--

CREATE TABLE IF NOT EXISTS `[[dbprefix]]dk_ticket_status` (
  `id` int(11) NOT NULL,
  `name` varchar(60) NOT NULL DEFAULT '',
  `state` varchar(16) DEFAULT NULL,
  `mode` int(11) unsigned NOT NULL DEFAULT '0',
  `flags` int(11) unsigned NOT NULL DEFAULT '0',
  `sort` int(11) unsigned NOT NULL DEFAULT '0',
  `properties` text NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `[[dbprefix]]dk_ticket_status`
--

INSERT INTO `[[dbprefix]]dk_ticket_status` VALUES
(1, 'Open', 'open', 3, 0, 1, '{"description":"Open tickets."}', '[[regtime]]', '0000-00-00 00:00:00'),
(2, 'Resolved', 'closed', 1, 0, 2, '{"allowreopen":true,"reopenstatus":0,"description":"Resolved tickets"}', '[[regtime]]', '0000-00-00 00:00:00'),
(3, 'Closed', 'closed', 3, 0, 3, '{"allowreopen":true,"reopenstatus":0,"description":"Closed tickets. Tickets will still be accessible on client and staff panels."}', '[[regtime]]', '0000-00-00 00:00:00'),
(4, 'Archived', 'archived', 3, 0, 4, '{"description":"Tickets only adminstratively available but no longer accessible on ticket queues and client panel."}', '[[regtime]]', '0000-00-00 00:00:00'),
(5, 'Deleted', 'deleted', 3, 0, 5, '{"description":"Tickets queued for deletion. Not accessible on ticket queues."}', '[[regtime]]', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `[[dbprefix]]dk_translation`
--

CREATE TABLE IF NOT EXISTS `[[dbprefix]]dk_translation` (
  `id` int(11) unsigned NOT NULL,
  `object_hash` char(16) CHARACTER SET ascii DEFAULT NULL,
  `type` enum('phrase','article','override') DEFAULT NULL,
  `flags` int(10) unsigned NOT NULL DEFAULT '0',
  `revision` int(11) unsigned DEFAULT NULL,
  `agent_id` int(10) unsigned NOT NULL DEFAULT '0',
  `lang` varchar(16) NOT NULL DEFAULT '',
  `text` mediumtext NOT NULL,
  `source_text` text,
  `updated` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `[[dbprefix]]dk_user`
--

CREATE TABLE IF NOT EXISTS `[[dbprefix]]dk_user` (
  `id` int(10) unsigned NOT NULL,
  `org_id` int(10) unsigned NOT NULL,
  `default_email_id` int(10) NOT NULL,
  `status` int(11) unsigned NOT NULL DEFAULT '0',
  `name` varchar(128) NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `[[dbprefix]]dk_user`
--

INSERT INTO `[[dbprefix]]dk_user` VALUES
(1, 1, 1, 0, 'Deskuss Support', '[[regtime]]', '[[regtime]]');

-- --------------------------------------------------------

--
-- Table structure for table `[[dbprefix]]dk_user_account`
--

CREATE TABLE IF NOT EXISTS `[[dbprefix]]dk_user_account` (
  `id` int(11) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `status` int(11) unsigned NOT NULL DEFAULT '0',
  `timezone` varchar(64) DEFAULT NULL,
  `lang` varchar(16) DEFAULT NULL,
  `username` varchar(64) DEFAULT NULL,
  `passwd` varchar(128) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL,
  `backend` varchar(32) DEFAULT NULL,
  `extra` text,
  `registered` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `[[dbprefix]]dk_user_email`
--

CREATE TABLE IF NOT EXISTS `[[dbprefix]]dk_user_email` (
  `id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `flags` int(10) unsigned NOT NULL DEFAULT '0',
  `address` varchar(128) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `[[dbprefix]]dk_user_email`
--

INSERT INTO `[[dbprefix]]dk_user_email` VALUES
(1, 1, 0, '[[admin_email]]');

-- --------------------------------------------------------

--
-- Table structure for table `[[dbprefix]]dk__search`
--

CREATE TABLE IF NOT EXISTS `[[dbprefix]]dk__search` (
  `object_type` varchar(8) NOT NULL,
  `object_id` int(11) unsigned NOT NULL,
  `title` text,
  `content` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Indexes for dumped tables
--

--
-- Indexes for table `[[dbprefix]]dk_api_key`
--
ALTER TABLE `[[dbprefix]]dk_api_key`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `apikey` (`apikey`),
  ADD KEY `ipaddr` (`ipaddr`);

--
-- Indexes for table `[[dbprefix]]dk_attachment`
--
ALTER TABLE `[[dbprefix]]dk_attachment`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `file-type` (`object_id`,`file_id`,`type`);

--
-- Indexes for table `[[dbprefix]]dk_canned_response`
--
ALTER TABLE `[[dbprefix]]dk_canned_response`
  ADD PRIMARY KEY (`canned_id`),
  ADD UNIQUE KEY `title` (`title`),
  ADD KEY `dept_id` (`dept_id`),
  ADD KEY `active` (`isenabled`);

--
-- Indexes for table `[[dbprefix]]dk_config`
--
ALTER TABLE `[[dbprefix]]dk_config`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `namespace` (`namespace`,`key`);

--
-- Indexes for table `[[dbprefix]]dk_content`
--
ALTER TABLE `[[dbprefix]]dk_content`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `[[dbprefix]]dk_department`
--
ALTER TABLE `[[dbprefix]]dk_department`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`,`pid`),
  ADD KEY `manager_id` (`manager_id`),
  ADD KEY `autoresp_email_id` (`autoresp_email_id`),
  ADD KEY `tpl_id` (`tpl_id`),
  ADD KEY `priority_id` (`priority_id`),
  ADD KEY `status_id` (`status_id`),
  ADD KEY `staff_id` (`staff_id`),
  ADD KEY `team_id` (`team_id`),
  ADD KEY `page_id` (`page_id`);

--
-- Indexes for table `[[dbprefix]]dk_draft`
--
ALTER TABLE `[[dbprefix]]dk_draft`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `[[dbprefix]]dk_email`
--
ALTER TABLE `[[dbprefix]]dk_email`
  ADD PRIMARY KEY (`email_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `priority_id` (`priority_id`),
  ADD KEY `dept_id` (`dept_id`);

--
-- Indexes for table `[[dbprefix]]dk_email_account`
--
ALTER TABLE `[[dbprefix]]dk_email_account`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `[[dbprefix]]dk_email_template`
--
ALTER TABLE `[[dbprefix]]dk_email_template`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `template_lookup` (`tpl_id`,`code_name`);

--
-- Indexes for table `[[dbprefix]]dk_email_template_group`
--
ALTER TABLE `[[dbprefix]]dk_email_template_group`
  ADD PRIMARY KEY (`tpl_id`);

--
-- Indexes for table `[[dbprefix]]dk_faq`
--
ALTER TABLE `[[dbprefix]]dk_faq`
  ADD PRIMARY KEY (`faq_id`),
  ADD UNIQUE KEY `question` (`question`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `ispublished` (`ispublished`);

--
-- Indexes for table `[[dbprefix]]dk_faq_category`
--
ALTER TABLE `[[dbprefix]]dk_faq_category`
  ADD PRIMARY KEY (`category_id`),
  ADD KEY `ispublic` (`ispublic`);

--
-- Indexes for table `[[dbprefix]]dk_file`
--
ALTER TABLE `[[dbprefix]]dk_file`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ft` (`ft`),
  ADD KEY `key` (`key`),
  ADD KEY `signature` (`signature`);

--
-- Indexes for table `[[dbprefix]]dk_file_chunk`
--
ALTER TABLE `[[dbprefix]]dk_file_chunk`
  ADD PRIMARY KEY (`file_id`,`chunk_id`);

--
-- Indexes for table `[[dbprefix]]dk_filter`
--
ALTER TABLE `[[dbprefix]]dk_filter`
  ADD PRIMARY KEY (`id`),
  ADD KEY `target` (`target`),
  ADD KEY `email_id` (`email_id`);

--
-- Indexes for table `[[dbprefix]]dk_filter_action`
--
ALTER TABLE `[[dbprefix]]dk_filter_action`
  ADD PRIMARY KEY (`id`),
  ADD KEY `filter_id` (`filter_id`);

--
-- Indexes for table `[[dbprefix]]dk_filter_rule`
--
ALTER TABLE `[[dbprefix]]dk_filter_rule`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `filter` (`filter_id`,`what`,`how`,`val`),
  ADD KEY `filter_id` (`filter_id`);

--
-- Indexes for table `[[dbprefix]]dk_form`
--
ALTER TABLE `[[dbprefix]]dk_form`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `[[dbprefix]]dk_form_entry`
--
ALTER TABLE `[[dbprefix]]dk_form_entry`
  ADD PRIMARY KEY (`id`),
  ADD KEY `entry_lookup` (`object_type`,`object_id`);

--
-- Indexes for table `[[dbprefix]]dk_form_entry_values`
--
ALTER TABLE `[[dbprefix]]dk_form_entry_values`
  ADD PRIMARY KEY (`entry_id`,`field_id`);

--
-- Indexes for table `[[dbprefix]]dk_form_field`
--
ALTER TABLE `[[dbprefix]]dk_form_field`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `[[dbprefix]]dk_group`
--
ALTER TABLE `[[dbprefix]]dk_group`
  ADD PRIMARY KEY (`id`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `[[dbprefix]]dk_list`
--
ALTER TABLE `[[dbprefix]]dk_list`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type` (`type`);

--
-- Indexes for table `[[dbprefix]]dk_list_items`
--
ALTER TABLE `[[dbprefix]]dk_list_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `list_item_lookup` (`list_id`);

--
-- Indexes for table `[[dbprefix]]dk_lock`
--
ALTER TABLE `[[dbprefix]]dk_lock`
  ADD PRIMARY KEY (`lock_id`),
  ADD KEY `staff_id` (`staff_id`);

--
-- Indexes for table `[[dbprefix]]dk_note`
--
ALTER TABLE `[[dbprefix]]dk_note`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ext_id` (`ext_id`);

--
-- Indexes for table `[[dbprefix]]dk_organization`
--
ALTER TABLE `[[dbprefix]]dk_organization`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `[[dbprefix]]dk_queue`
--
ALTER TABLE `[[dbprefix]]dk_queue`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `[[dbprefix]]dk_role`
--
ALTER TABLE `[[dbprefix]]dk_role`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `[[dbprefix]]dk_sequence`
--
ALTER TABLE `[[dbprefix]]dk_sequence`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `[[dbprefix]]dk_session`
--
ALTER TABLE `[[dbprefix]]dk_session`
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `updated` (`session_updated`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `[[dbprefix]]dk_sla`
--
ALTER TABLE `[[dbprefix]]dk_sla`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `[[dbprefix]]dk_staff`
--
ALTER TABLE `[[dbprefix]]dk_staff`
  ADD PRIMARY KEY (`staff_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `dept_id` (`dept_id`),
  ADD KEY `issuperuser` (`isadmin`);

--
-- Indexes for table `[[dbprefix]]dk_staff_dept_access`
--
ALTER TABLE `[[dbprefix]]dk_staff_dept_access`
  ADD PRIMARY KEY (`staff_id`,`dept_id`),
  ADD KEY `dept_id` (`dept_id`);

--
-- Indexes for table `[[dbprefix]]dk_syslog`
--
ALTER TABLE `[[dbprefix]]dk_syslog`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `log_type` (`log_type`);

--
-- Indexes for table `[[dbprefix]]dk_team`
--
ALTER TABLE `[[dbprefix]]dk_team`
  ADD PRIMARY KEY (`team_id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `lead_id` (`lead_id`);

--
-- Indexes for table `[[dbprefix]]dk_team_member`
--
ALTER TABLE `[[dbprefix]]dk_team_member`
  ADD PRIMARY KEY (`team_id`,`staff_id`);

--
-- Indexes for table `[[dbprefix]]dk_thread`
--
ALTER TABLE `[[dbprefix]]dk_thread`
  ADD PRIMARY KEY (`id`),
  ADD KEY `object_id` (`object_id`),
  ADD KEY `object_type` (`object_type`);

--
-- Indexes for table `[[dbprefix]]dk_thread_collaborator`
--
ALTER TABLE `[[dbprefix]]dk_thread_collaborator`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `collab` (`thread_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `[[dbprefix]]dk_thread_entry`
--
ALTER TABLE `[[dbprefix]]dk_thread_entry`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pid` (`pid`),
  ADD KEY `thread_id` (`thread_id`),
  ADD KEY `staff_id` (`staff_id`),
  ADD KEY `type` (`type`);

--
-- Indexes for table `[[dbprefix]]dk_thread_entry_email`
--
ALTER TABLE `[[dbprefix]]dk_thread_entry_email`
  ADD PRIMARY KEY (`id`),
  ADD KEY `thread_entry_id` (`thread_entry_id`),
  ADD KEY `mid` (`mid`);

--
-- Indexes for table `[[dbprefix]]dk_thread_event`
--
ALTER TABLE `[[dbprefix]]dk_thread_event`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_state` (`thread_id`,`state`,`timestamp`),
  ADD KEY `ticket_stats` (`timestamp`,`state`);

--
-- Indexes for table `[[dbprefix]]dk_ticket`
--
ALTER TABLE `[[dbprefix]]dk_ticket`
  ADD PRIMARY KEY (`ticket_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `dept_id` (`dept_id`),
  ADD KEY `staff_id` (`staff_id`),
  ADD KEY `team_id` (`team_id`),
  ADD KEY `status_id` (`status_id`),
  ADD KEY `created` (`created`),
  ADD KEY `closed` (`closed`),
  ADD KEY `duedate` (`duedate`),
  ADD KEY `topic_id` (`topic_id`),
  ADD KEY `sla_id` (`sla_id`);

--
-- Indexes for table `[[dbprefix]]dk_ticket_priority`
--
ALTER TABLE `[[dbprefix]]dk_ticket_priority`
  ADD PRIMARY KEY (`priority_id`),
  ADD UNIQUE KEY `priority` (`priority`),
  ADD KEY `priority_urgency` (`priority_urgency`),
  ADD KEY `ispublic` (`ispublic`);

--
-- Indexes for table `[[dbprefix]]dk_ticket_status`
--
ALTER TABLE `[[dbprefix]]dk_ticket_status`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `state` (`state`);

--
-- Indexes for table `[[dbprefix]]dk_translation`
--
ALTER TABLE `[[dbprefix]]dk_translation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type` (`type`,`lang`),
  ADD KEY `object_hash` (`object_hash`);

--
-- Indexes for table `[[dbprefix]]dk_user`
--
ALTER TABLE `[[dbprefix]]dk_user`
  ADD PRIMARY KEY (`id`),
  ADD KEY `org_id` (`org_id`);

--
-- Indexes for table `[[dbprefix]]dk_user_account`
--
ALTER TABLE `[[dbprefix]]dk_user_account`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `[[dbprefix]]dk_user_email`
--
ALTER TABLE `[[dbprefix]]dk_user_email`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `address` (`address`),
  ADD KEY `user_email_lookup` (`user_id`);

--
-- Indexes for table `[[dbprefix]]dk__search`
--
ALTER TABLE `[[dbprefix]]dk__search`
  ADD PRIMARY KEY (`object_type`,`object_id`),
  ADD FULLTEXT KEY `search` (`title`,`content`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `[[dbprefix]]dk_api_key`
--
ALTER TABLE `[[dbprefix]]dk_api_key`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `[[dbprefix]]dk_attachment`
--
ALTER TABLE `[[dbprefix]]dk_attachment`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=13;
--
-- AUTO_INCREMENT for table `[[dbprefix]]dk_canned_response`
--
ALTER TABLE `[[dbprefix]]dk_canned_response`
  MODIFY `canned_id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `[[dbprefix]]dk_config`
--
ALTER TABLE `[[dbprefix]]dk_config`
  MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=87;
--
-- AUTO_INCREMENT for table `[[dbprefix]]dk_content`
--
ALTER TABLE `[[dbprefix]]dk_content`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=13;
--
-- AUTO_INCREMENT for table `[[dbprefix]]dk_department`
--
ALTER TABLE `[[dbprefix]]dk_department`
  MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `[[dbprefix]]dk_draft`
--
ALTER TABLE `[[dbprefix]]dk_draft`
  MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `[[dbprefix]]dk_email`
--
ALTER TABLE `[[dbprefix]]dk_email`
  MODIFY `email_id` int(11) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `[[dbprefix]]dk_email_account`
--
ALTER TABLE `[[dbprefix]]dk_email_account`
  MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `[[dbprefix]]dk_email_template`
--
ALTER TABLE `[[dbprefix]]dk_email_template`
  MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=14;
--
-- AUTO_INCREMENT for table `[[dbprefix]]dk_email_template_group`
--
ALTER TABLE `[[dbprefix]]dk_email_template_group`
  MODIFY `tpl_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `[[dbprefix]]dk_faq`
--
ALTER TABLE `[[dbprefix]]dk_faq`
  MODIFY `faq_id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `[[dbprefix]]dk_faq_category`
--
ALTER TABLE `[[dbprefix]]dk_faq_category`
  MODIFY `category_id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `[[dbprefix]]dk_file`
--
ALTER TABLE `[[dbprefix]]dk_file`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `[[dbprefix]]dk_filter`
--
ALTER TABLE `[[dbprefix]]dk_filter`
  MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `[[dbprefix]]dk_filter_action`
--
ALTER TABLE `[[dbprefix]]dk_filter_action`
  MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `[[dbprefix]]dk_filter_rule`
--
ALTER TABLE `[[dbprefix]]dk_filter_rule`
  MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `[[dbprefix]]dk_form`
--
ALTER TABLE `[[dbprefix]]dk_form`
  MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table `[[dbprefix]]dk_form_entry`
--
ALTER TABLE `[[dbprefix]]dk_form_entry`
  MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `[[dbprefix]]dk_form_field`
--
ALTER TABLE `[[dbprefix]]dk_form_field`
  MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=34;
--
-- AUTO_INCREMENT for table `[[dbprefix]]dk_group`
--
ALTER TABLE `[[dbprefix]]dk_group`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `[[dbprefix]]dk_list`
--
ALTER TABLE `[[dbprefix]]dk_list`
  MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `[[dbprefix]]dk_list_items`
--
ALTER TABLE `[[dbprefix]]dk_list_items`
  MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `[[dbprefix]]dk_lock`
--
ALTER TABLE `[[dbprefix]]dk_lock`
  MODIFY `lock_id` int(11) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `[[dbprefix]]dk_note`
--
ALTER TABLE `[[dbprefix]]dk_note`
  MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `[[dbprefix]]dk_organization`
--
ALTER TABLE `[[dbprefix]]dk_organization`
  MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `[[dbprefix]]dk_queue`
--
ALTER TABLE `[[dbprefix]]dk_queue`
  MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `[[dbprefix]]dk_role`
--
ALTER TABLE `[[dbprefix]]dk_role`
  MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `[[dbprefix]]dk_sequence`
--
ALTER TABLE `[[dbprefix]]dk_sequence`
  MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `[[dbprefix]]dk_sla`
--
ALTER TABLE `[[dbprefix]]dk_sla`
  MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `[[dbprefix]]dk_staff`
--
ALTER TABLE `[[dbprefix]]dk_staff`
  MODIFY `staff_id` int(11) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `[[dbprefix]]dk_syslog`
--
ALTER TABLE `[[dbprefix]]dk_syslog`
  MODIFY `log_id` int(11) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `[[dbprefix]]dk_team`
--
ALTER TABLE `[[dbprefix]]dk_team`
  MODIFY `team_id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `[[dbprefix]]dk_thread`
--
ALTER TABLE `[[dbprefix]]dk_thread`
  MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `[[dbprefix]]dk_thread_collaborator`
--
ALTER TABLE `[[dbprefix]]dk_thread_collaborator`
  MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `[[dbprefix]]dk_thread_entry`
--
ALTER TABLE `[[dbprefix]]dk_thread_entry`
  MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `[[dbprefix]]dk_thread_entry_email`
--
ALTER TABLE `[[dbprefix]]dk_thread_entry_email`
  MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `[[dbprefix]]dk_thread_event`
--
ALTER TABLE `[[dbprefix]]dk_thread_event`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `[[dbprefix]]dk_ticket`
--
ALTER TABLE `[[dbprefix]]dk_ticket`
  MODIFY `ticket_id` int(11) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `[[dbprefix]]dk_ticket_priority`
--
ALTER TABLE `[[dbprefix]]dk_ticket_priority`
  MODIFY `priority_id` tinyint(4) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `[[dbprefix]]dk_ticket_status`
--
ALTER TABLE `[[dbprefix]]dk_ticket_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table `[[dbprefix]]dk_translation`
--
ALTER TABLE `[[dbprefix]]dk_translation`
  MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `[[dbprefix]]dk_user`
--
ALTER TABLE `[[dbprefix]]dk_user`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `[[dbprefix]]dk_user_account`
--
ALTER TABLE `[[dbprefix]]dk_user_account`
  MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `[[dbprefix]]dk_user_email`
--
ALTER TABLE `[[dbprefix]]dk_user_email`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
