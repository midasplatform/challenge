<?php

class Challenge_Upgrade_0_0_3 extends MIDASUpgrade
{
  public function preUpgrade()
    {
    }

  public function mysql()
    {
    $this->db->query("ALTER TABLE `challenge_results_run_item` DROP COLUMN `validation_scalarresult_id`");
    $this->db->query("ALTER TABLE `challenge_results_run_item` ADD COLUMN `result_key` text NOT NULL");
    $this->db->query("ALTER TABLE `challenge_results_run_item` ADD COLUMN `result_value` double NOT NULL");
    }
 
  public function postUpgrade()
    {
    }
}
?>
