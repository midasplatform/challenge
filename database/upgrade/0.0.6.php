<?php

class Challenge_Upgrade_0_0_6 extends MIDASUpgrade
{
  public function preUpgrade()
    {
    }

  public function mysql()
    {
    $this->db->query("ALTER TABLE `challenge_challenge` ADD COLUMN `testing_status` text");
    $this->db->query("ALTER TABLE `challenge_challenge` CHANGE `status` `training_status` text");
    }

  public function postUpgrade()
    {
    }
}
?>
