<?php

class Challenge_Upgrade_0_0_4 extends MIDASUpgrade
{
  public function preUpgrade()
    {
    }

  public function mysql()
    {
    $this->db->query("ALTER TABLE `challenge_results_run` DROP COLUMN `executable_name`");
    $this->db->query("ALTER TABLE `challenge_results_run` DROP COLUMN `params`");
    $this->db->query("ALTER TABLE `challenge_results_run` ADD COLUMN `results_type` text");
    }
 
  public function postUpgrade()
    {
    }
}
?>
