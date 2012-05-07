CREATE TABLE IF NOT EXISTS `challenge_challenge` (
  `challenge_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `validation_dashboard_id` bigint(20) NOT NULL,
  `community_id` bigint(20) NOT NULL,
  `status` text NOT NULL,
  PRIMARY KEY (`challenge_id`)
)   DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `challenge_results_run` (
  `challenge_results_run_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `executable_name` text NOT NULL,
  `params` text NOT NULL,
  `challenge_id` bigint(20) NOT NULL,
  `batchmake_task_id` bigint(20) NOT NULL,
  `results_folder_id` bigint(20) NOT NULL,
  `output_folder_id` bigint(20) NOT NULL,
  PRIMARY KEY (`challenge_results_run_id`)
)   DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `challenge_results_run_item` (
  `challenge_results_run_item_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `challenge_results_run_id` bigint(20) NOT NULL,
  `test_item_id` bigint(20) NOT NULL,
  `output_item_id` bigint(20) NOT NULL,
  `results_item_id` bigint(20) NOT NULL,
  `condor_dag_job_id` bigint(20) NOT NULL,
  `validation_scalarresult_id` bigint(20) NOT NULL,
  PRIMARY KEY (`challenge_results_run_item_id`)
)   DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `challenge_enabled_community` (
  `challenge_enabled_community_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `community_id` bigint(20) NOT NULL,
  PRIMARY KEY (`challenge_enabled_community_id`)
)   DEFAULT CHARSET=utf8;

            