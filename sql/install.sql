CREATE TABLE IF NOT EXISTS `@PREFIX@oyst_payment_notification` (
  `id_oyst_payment_notification` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_order` int(10) unsigned NOT NULL,
  `id_cart` int(10) unsigned NOT NULL,
  `payment_id` varchar(128) NOT NULL,
  `event_code` varchar(32) NOT NULL,
  `event_data` text NOT NULL,
  `status` varchar(255) DEFAULT NULL,
  `date_event` datetime DEFAULT NULL,
  `date_add` datetime DEFAULT NULL,
  `date_upd` datetime DEFAULT NULL,
  PRIMARY KEY (`id_oyst_payment_notification`)
) ENGINE=@ENGINE@ DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
