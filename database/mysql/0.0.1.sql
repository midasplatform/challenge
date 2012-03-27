CREATE TABLE IF NOT EXISTS `challenge_challenge` (
  `challenge_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `validation_dashboard_id` bigint(20) NOT NULL,
  `community_id` bigint(20) NOT NULL,
  `status` text NOT NULL,
  PRIMARY KEY (`challenge_id`)
)   DEFAULT CHARSET=utf8;
