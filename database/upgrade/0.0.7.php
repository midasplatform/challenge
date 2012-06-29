<?php

class Challenge_Upgrade_0_0_7 extends MIDASUpgrade
{
  public function preUpgrade()
    {
    }

  public function mysql()
    {
    $this->db->query("ALTER TABLE `challenge_results_run` ADD COLUMN `challenge_competitor_id` bigint(20) NOT NULL");
    }

  public function postUpgrade()
    {
    }
}
?>
