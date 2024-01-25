-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `su_admins2domains`
--

CREATE TABLE IF NOT EXISTS `su_admins2domains` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `domain_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`,`domain_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `su_blacklist`
--

CREATE TABLE IF NOT EXISTS `su_blacklist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `short_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `domain_id` int(11) NOT NULL,
  `url_code` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `created_by` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `domain_id` (`domain_id`,`url_code`),
  UNIQUE KEY `short_url` (`short_url`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `su_notfound`
--

CREATE TABLE IF NOT EXISTS `su_notfound` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `short_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `mail_sent_date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mail_sent_date` (`mail_sent_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `su_shorturls`
--

CREATE TABLE IF NOT EXISTS `su_shorturls` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `short_url` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `target_url` varchar(2000) COLLATE utf8_unicode_ci NOT NULL,
  `url_code` varchar(2000) COLLATE utf8_unicode_ci NOT NULL,
  `domain_id` int(11) NOT NULL DEFAULT 0,
  `status` int(11) NOT NULL DEFAULT 1,
  `visits` int(11) NOT NULL DEFAULT 0,
  `qr_code_settings` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `sent_reminder1` tinyint(4) NOT NULL DEFAULT 0,
  `sent_reminder2` tinyint(4) NOT NULL DEFAULT 0,
  `sent_expiration_notification` tinyint(4) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `expiry_date` date DEFAULT NULL,
  `updated_by` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_shorturl` (`short_url`),
  KEY `expiry_date` (`expiry_date`),
  KEY `sent_reminder1` (`sent_reminder1`),
  KEY `sent_reminder2` (`sent_reminder2`),
  KEY `sent_expiration_notification` (`sent_expiration_notification`),
  KEY `short_url` (`short_url`,`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `su_shorturls2users`
--

CREATE TABLE IF NOT EXISTS `su_shorturls2users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `shorturl_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `su_shorturls_history`
--

CREATE TABLE IF NOT EXISTS `su_shorturls_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shorturl_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `short_url` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `target_url` varchar(2000) COLLATE utf8_unicode_ci NOT NULL,
  `url_code` varchar(2000) COLLATE utf8_unicode_ci NOT NULL,
  `domain_id` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 1,
  `visits` int(11) NOT NULL DEFAULT 0,
  `qr_code_settings` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `sent_reminder1` tinyint(4) NOT NULL DEFAULT 0,
  `sent_reminder2` tinyint(4) NOT NULL DEFAULT 0,
  `sent_expiration_notification` tinyint(4) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `expiry_date` date NOT NULL,
  `updated_by` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `su_id` (`shorturl_id`),
  KEY `updated_at` (`updated_at`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `su_users`
--

CREATE TABLE IF NOT EXISTS `su_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `firstname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `notifynotfound` tinyint(1) NOT NULL DEFAULT 0,
  `admin` tinyint(1) NOT NULL DEFAULT 0,
  `superadmin` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `notification_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `notifynotfound` (`notifynotfound`),
  KEY `admin` (`admin`),
  KEY `superadmin` (`superadmin`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `su_visits`
--

CREATE TABLE IF NOT EXISTS `su_visits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shorturl_id` int(11) NOT NULL,
  `visit_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `urldate` (`shorturl_id`,`visit_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
COMMIT;
