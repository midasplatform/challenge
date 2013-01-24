<?php

class Challenge_Upgrade_0_1_4 extends MIDASUpgrade
{
  public function preUpgrade()
    {
    }

  public function mysql()
    {
    $this->db->query("ALTER TABLE challenge_challenge add column number_scored_labels int NOT NULL");
    }
    
  public function postUpgrade()
    {
    }
}
?>
