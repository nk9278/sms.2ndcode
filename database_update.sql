CREATE TABLE IF NOT EXISTS `otp_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `website_id` int(11) NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `otp` varchar(10) NOT NULL,
  `status` enum('sent','verified','failed') NOT NULL DEFAULT 'sent',
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `website_id` (`website_id`),
  CONSTRAINT `fk_otp_website` FOREIGN KEY (`website_id`) REFERENCES `websites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
