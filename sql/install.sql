CREATE TABLE IF NOT EXISTS `@PREFIX@oyst_payment_notification` (
  `id_oyst_payment_notification` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_order` int(10) unsigned NOT NULL,
  `id_cart` int(10) unsigned NOT NULL,
  `payment_id` varchar(128) NOT NULL,
  `event_code` varchar(32) NOT NULL,
  `event_data` text NOT NULL,
  `date_event` datetime DEFAULT NULL,
  `date_add` datetime DEFAULT NULL,
  PRIMARY KEY (`id_oyst_payment_notification`)
) ENGINE=@ENGINE@ DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `@PREFIX@oyst_shipment` (
  `id_oyst_shipment` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_carrier` int(10) unsigned NOT NULL,
  `primary` tinyint(1) DEFAULT 0,
  `type` varchar(128) NOT NULL,
  `delay` int(10) unsigned NOT NULL,
  `zones` varchar(128) NOT NULL,
  `amount_leader` decimal(20,2) NOT NULL,
  `amount_follower` decimal(20,2) NOT NULL,
  `amount_currency` varchar(3) NOT NULL,
  `free_shipping` decimal(20,2) NULL,
  PRIMARY KEY (`id_oyst_shipment`)
) ENGINE=@ENGINE@ DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
