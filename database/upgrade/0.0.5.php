<?php

class Challenge_Upgrade_0_0_5 extends MIDASUpgrade
{
  public function preUpgrade()
    {
    }

  public function mysql()
    {
    $tableCreate =
    "CREATE TABLE IF NOT EXISTS `challenge_competitor` (" .
    "`challenge_competitor_id` bigint(20) NOT NULL AUTO_INCREMENT," .
    "`challenge_id` bigint(20) NOT NULL," .
    "`user_id` bigint(20) NOT NULL," .
    "`training_submission_folder_id` bigint(20) NOT NULL," .
    "`training_output_folder_id` bigint(20) NOT NULL," .
    "`testing_submission_folder_id` bigint(20) NOT NULL," .
    "`testing_output_folder_id` bigint(20) NOT NULL," .
    "PRIMARY KEY (`challenge_competitor_id`)" .
    ")   DEFAULT CHARSET=utf8";
    $this->db->query($tableCreate);
    
    
    $this->db->query("ALTER TABLE `challenge_challenge` ADD COLUMN `training_folder_stem` text");
    $this->db->query("ALTER TABLE `challenge_challenge` ADD COLUMN `testing_folder_stem` text");
    }
 
  public function postUpgrade()
    {
    }
}
?>
