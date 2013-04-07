<?php

class Challenge_Upgrade_0_1_16 extends MIDASUpgrade
{
  public function preUpgrade()
    {
    }

  public function mysql()
    {
    $this->db->query("alter table challenge_challenge add column current_scoreboard_stage text");
    }

  public function postUpgrade()
    {
    }
}
?>