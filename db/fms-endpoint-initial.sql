-- SQL for initialising FMS-endpoint database
-- suitable for mySQL 

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- --------------------------------------------------------

--
-- Table structure for table `categories`
-- category_id is varchar just in case that's helpful for departments with
-- existing (legacy) classifcations for categories. Be wary of case-dependency
-- if they're not numbers!
-- "categories" are effectively what Open311 calls "services"

CREATE TABLE IF NOT EXISTS `categories` (
  `category_id` varchar(255) NOT NULL,
  `category_name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `group` varchar(255) NOT NULL,
  `keywords` text NOT NULL,
  `metadata` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  PRIMARY KEY (`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Dumping data for table `categories`
--

INSERT INTO `categories` VALUES('001', 'Pothole', 'Hole or crack in road surface.', 'Street defects', '', 'false', 'realtime');
INSERT INTO `categories` VALUES('002', 'Streetlight', 'Broken streetlight.', 'Street Defects', '', 'false', 'realtime');

-- --------------------------------------------------------

--
-- Table structure for table `category_attributes`
-- Attributes allow additional information to be provided for services (e.g., the depth of a pothole)
-- Not used in current FMS-endpoint

CREATE TABLE IF NOT EXISTS `category_attributes` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `category_id` varchar(255) NOT NULL,
  `attribute_id` varchar(255) NOT NULL,
  `variable` varchar(255) NOT NULL,
  `datatype` varchar(255) NOT NULL,
  `required` varchar(255) NOT NULL,
  `datatype_description` text NOT NULL,
  `order` int(10) NOT NULL,
  `description` text NOT NULL,
  `values` text,
  `hidden` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;


--
-- Dumping data for table `category_attributes`
-- empty table; example only:

-- INSERT INTO `category_attributes` VALUES('001', 'XX', 'true', 'text', 'true', '', 1, 'How deep is the hole?', '');

-- --------------------------------------------------------

--
-- Table structure for table `config_settings`
-- Config settings let the admin configure the FMS-endpoint in the browser (once the endpoint is running)

CREATE TABLE `config_settings` (
  `name` varchar(64) NOT NULL,
  `value` text NOT NULL,
  `desc` text,
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `config_settings`
--

INSERT INTO `config_settings` VALUES('organisation_name', 'Example Department', '<p>The name of the department/council/authority running this endpoint.</p>');
INSERT INTO `config_settings` VALUES('can_edit_categories', 'no', '<p>Can normal users change the Open311 Categories? Suggested values:</p>\n<ul>\n<li>no [default]</li>\n<li>yes</li>\n</ul>\n<p>The admin user can always change them.</p>');
INSERT INTO `config_settings` VALUES('redirect_root_page', '', '<p>Once your endpoint is up and running, you may prefer to automatically redirect it to the admin URL. Suggested values:</p> <ul><li style="padding-left:3em"> <i>(blank)</i> [default &mdash; no redirection: display the root page]</li><li>/admin </li><li> any URL</li></ul><p>Be sure to <a href="/">visit the root page</a> after changing this setting to check that it is working as you expected.</p>');
INSERT INTO `config_settings` VALUES('enable_open311_server', 'yes', '<p>Is the Open311 server currently running? Suggested values:</p>\n<ul>\n<li>no</li>\n<li>yes [default]</li>\n</ul>');
INSERT INTO `config_settings` VALUES('open311_use_external_id', 'always', '<p>Does the Open311 server demand that an external ID (such as FixMyStreet problem ID) is always provided? Suggested values:</p>\n<ul>\n<li>no</li>\n<li>optional</li>\n<li>always [default]</li>\n</ul>');
INSERT INTO `config_settings` VALUES('open311_use_external_name', 'external_id', '<p>The name of the external ID that must be sent if <strong>open311_use_external_id</strong> is set to <em>yes</em>. Defaults to <em>external_id</em> if left blank. For example, use as <em>attrib[external_id]</em> in incoming reports.</li>\n</ul>');
INSERT INTO `config_settings` VALUES('open311_use_api_keys', 'yes', '<p>Must all incoming report submissions provide a valid API key? Suggested values:</p>\n<ul>\n<li>no</li>\n<li>yes [default]</li>\n</ul><p>See <a href="/admin/api_keys">API keys config</a> to create or edit keys to use.');
INSERT INTO `config_settings` VALUES('external_id_col_name', 'External ID', '<p>Name of external ID column on tables, which usually contains links to the external source of the report. If your endpoint is only accepting reports from a single Open311 client, it may be clearer to your users to change the column title to show this (e.g., <em>FixMyStreet ID</em>).');
INSERT INTO `config_settings` VALUES('default_client', '0', '<p>Default client ID &ndash; normally the client (who is sending the problem report) is inferred from the API key used. If you\'re not using API keys (that is, <strong>open311_use_api_keys</strong> is set to <i>no</i>), you can assume <i>all</i> incoming reports are from this client by setting this to the integer value of the client ID (in the clients table). Note: API keys are probably a better way of doing this! If in doubt, set this to 0 (which does <i>not</i> force a default client).</p>');
INSERT INTO `config_settings` VALUES('external_id_is_global', 'no', '<p>Should external IDs be considered unique regardless of which client they came from? Suggested values:</p><ul><li>no [default] &ndash; duplicate IDs are OK provided they are from different clients</li><li>yes &ndash; external IDs must all be unique</li></ul><p>This setting is used to help determine if an incoming should be rejected because it\'s already been submitted.');
INSERT INTO `config_settings` VALUES('open311_simple_status_only', 'yes', '<p>Should outgoing status updates only ever be OPEN or CLOSED? Suggested values:</p><ul><li>no &ndash; status updates are sent to the client with the actual status name</li><li>yes [default] &ndash; statuses are changed to either OPEN or CLOSED when sent to the client</li></ul>');
INSERT INTO `config_settings` VALUES('announcement_html', '', '<p>Insert any HTML that you want to appear at the top of every page (when logged in).<br/>Note: avoid use of double quotation marks.</p>');
INSERT INTO `config_settings` VALUES('organisation_url', '', '<p>The URL to an external site to appear in the footer (for example, to the organisation\'s home page, or its reports page in FixMyStreet-based clients). Leave blank if you don\'t want a link to appear. See also <b>organisation_link_text</b>.</p>');
INSERT INTO `config_settings` VALUES('organisation_link_text', '', '<p>The link text to use for the link in the footer (see <b>organisation_url</b>). If you leave this blank, the organisation URL will be used instead.</p>');
INSERT INTO `config_settings` VALUES('open311_allow_update_posts', 'yes', '<p>Should incoming status updates (which are not core Open311 functionality) be accepted? Suggested values:</p><ul><li>no &ndash; incoming status updates are rejected</li><li>yes [default] &ndash; incoming POST requests (with a valid API key) to servicerequestupdates will be accepted</li></ul>');

-- --------------------------------------------------------

--
-- Table structure for table `groups`
-- from Ion Auth 2 (CodeIgniter user authentication)

CREATE TABLE `groups` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `description` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` VALUES(1, 'admin', 'Administrator');
INSERT INTO `groups` VALUES(2, 'members', 'General User');
INSERT INTO `groups` VALUES(3, 'open311', 'Open311 write access');

-- --------------------------------------------------------

--
-- Table structure for table `users`
-- from Ion Auth 2 (CodeIgniter user authentication)
--

CREATE TABLE `users` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `ip_address` int(10) unsigned NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(40) NOT NULL,
  `salt` varchar(40) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `activation_code` varchar(40) DEFAULT NULL,
  `forgotten_password_code` varchar(40) DEFAULT NULL,
  `remember_code` varchar(40) DEFAULT NULL,
  `created_on` int(11) unsigned NOT NULL,
  `last_login` int(11) unsigned DEFAULT NULL,
  `active` tinyint(1) unsigned DEFAULT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `company` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` VALUES(1, 2130706433, 'administrator', '59beecdf7fc966e2f17fd8f65a4a9aeb09d4a3d4', '9462e8eee0', 'admin@example.com', '', NULL, NULL, 1268889823, 1335371450, 1, 'Admin', 'istrator', 'ADMIN', '0');

-- --------------------------------------------------------

--
-- Table structure for table `users_groups`
-- from Ion Auth 2 (CodeIgniter user authentication)
--

CREATE TABLE `users_groups` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` mediumint(8) unsigned NOT NULL,
  `group_id` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `users_groups`
--

INSERT INTO `users_groups` VALUES(1, 1, 1);
INSERT INTO `users_groups` VALUES(2, 1, 2);
-- --------------------------------------------------------

--
-- Table structure for table `priorities`
-- These are the priorities of problem reports in FMS-endpoint
-- provided primarily as a useful sorting index.

CREATE TABLE `priorities` (
  `prio_value` int(11) NOT NULL,
  `prio_name` varchar(255) NOT NULL,
  PRIMARY KEY (`prio_value`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `priorities`
--

INSERT INTO `priorities` VALUES('-2', 'Very Low');
INSERT INTO `priorities` VALUES('-1', 'Low');
INSERT INTO `priorities` VALUES('0', 'Normal');
INSERT INTO `priorities` VALUES('1', 'High');
INSERT INTO `priorities` VALUES('2', 'Urgent');


-- --------------------------------------------------------

--
-- Table structure for table `statuses`
-- Status may be taken from FixMyStreet... by keeping them in 
-- synch, the future function of transmitting status changes here
-- to FMS are less likely to hit errors.

CREATE TABLE `statuses` (
  `status_id` int(11) NOT NULL,
  `status_name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `is_closed` tinyint(1) NOT NULL,
  PRIMARY KEY (`status_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `statuses`
-- Note these statuses correspond to FixMyStreet statuses
-- except "unknown" which is an error state

INSERT INTO `statuses` VALUES(0, 'unknown',  'unrecognised status', 0);

INSERT INTO `statuses` VALUES(1, 'new',    'report newly created', 0);
INSERT INTO `statuses` VALUES(2, 'open',   'awaiting action', 0);
INSERT INTO `statuses` VALUES(3, 'closed', 'no further action required', 1);

INSERT INTO `statuses` VALUES(4, 'investigating', 'investigating', 0);
INSERT INTO `statuses` VALUES(5, 'planned', 'work is scheduled', 0);
INSERT INTO `statuses` VALUES(6, 'in progress', 'work is in progress', 0);

INSERT INTO `statuses` VALUES(7, 'fixed', 'problem is fixed', 1);
INSERT INTO `statuses` VALUES(8, 'fixed - user', 'problem marked as fixed by public', 1);
INSERT INTO `statuses` VALUES(9, 'fixed - council', 'problem marked as fixed by dept/council', 1);



-- --------------------------------------------------------

--
-- Table structure for table `open311_clients`
-- Open311 clients are those to whom one or more API keys are allocated.
-- FMS-endpoint infers the client by inspecting the api_key on incoming
-- reports, and matching it in this table.
-- The client URL will be used to construct a link back to the client:
-- use %id% in the URL to indicate where the client's own ref/id for the
-- report should occur in the URL (the id itself is passed in as an 
-- attribute when the report is submitted: see config_settings)

CREATE TABLE `open311_clients` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `client_url` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO `open311_clients` VALUES('1', 'Example client', 'http://www.example.com/report/%id%', 'don\'t use -- delete when live');

--
-- Table structure for table `api_keys`
-- API keys are used to associate incoming requests with clients (in the 
-- open311_clients table). Many keys can be allocated to a single client.
-- If open311_use_api_keys (in config_settings) is not set, you don't need
-- to use API keys at all, but it's recommended so you can identify incoming
-- requests with their source client.

CREATE TABLE `api_keys` (
  `api_key` varchar(255) NOT NULL,
  `client_id` mediumint(8) NOT NULL,
  `notes` text,
   PRIMARY KEY (`api_key`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--

INSERT INTO `api_keys` VALUES('12345', 1, 'don\'t use -- delete when live');

-- --------------------------------------------------------

--
-- Table structure for table `request_updates`
--

CREATE TABLE `request_updates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report_id` int(11) NOT NULL,
  `status_id` int(11) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `update_desc` text,
  `old_status_id` int(11) DEFAULT NULL,
  `changed_by` int(11) NOT NULL,
  `is_outbound` TINYINT(1) UNSIGNED NOT NULL,
  `changed_by_name` VARCHAR(255),
  `media_url` VARCHAR(255),
  `remote_update_id` INTEGER,
  `source_client` INTEGER DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `report_id` (`report_id`,`status_id`,`updated_at`),
  KEY `changed_by` (`changed_by`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE IF NOT EXISTS `reports` (
  `report_id` int(255) NOT NULL AUTO_INCREMENT,
  `status` int(11) DEFAULT '1',
  `status_notes` text,
  `priority` int(11) DEFAULT '0',
  `category_id` varchar(255) DEFAULT NULL,
  `description` text,
  `agency_responsible` varchar(255) DEFAULT NULL,
  `service_notice` text,
  `token` varchar(255) DEFAULT NULL,
  `source_client` mediumint(8) DEFAULT NULL,
  `external_id` varchar(255) DEFAULT NULL,
  `requested_datetime` datetime DEFAULT NULL,
  `updated_datetime` datetime DEFAULT NULL,
  `expected_datetime` datetime DEFAULT NULL,
  `address` text,
  `address_id` varchar(255) DEFAULT NULL,
  `postal_code` varchar(255) DEFAULT NULL,
  `lat` double DEFAULT NULL,
  `long` double DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `device_id` varchar(255) DEFAULT NULL,
  `account_id` varchar(255) DEFAULT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `media_url` text,
  `engineer` varchar(255) DEFAULT NULL,
  `attribute` text,
  PRIMARY KEY (`report_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1000 ;

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` VALUES(1000, 1, NULL, 0, '001', 'Hole in the road', NULL, NULL, NULL, 1, '99', '2012-05-01 12:00:00', NULL, '2012-05-02 13:00:00', 'Intersection of 22nd St and San Bruna Ave', NULL, NULL, 37.756954, -122.40473, 'a_user@example.com', NULL, NULL, 'Anne', 'Example', NULL, 'http://farm3.static.flickr.com/2002/2212426634_5ed477a060.jpg', null, null);
