<?php

class Challenge_Upgrade_0_0_2 extends MIDASUpgrade
{
  public function preUpgrade()
    {
    }

  public function mysql()
    {
    $this->db->query("ALTER TABLE `challenge_challenge` ADD COLUMN `root_folder_id` bigint(20) NOT NULL");
    }
    
  public function postUpgrade()
    {
    }
}
?>
