<?php

class Challenge_Upgrade_0_0_8 extends MIDASUpgrade
{
  public function preUpgrade()
    {
    }

  public function mysql()
    {
    $this->db->query("ALTER TABLE `challenge_results_run` ADD COLUMN `date` timestamp NOT NULL");
    }

  public function postUpgrade()
    {
    }
}
?>
