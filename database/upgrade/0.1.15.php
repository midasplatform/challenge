<?php

class Challenge_Upgrade_0_1_15 extends MIDASUpgrade
{
  public function preUpgrade()
    {
    }

  public function mysql()
    {
    $this->db->query("alter table challenge_results_run add column submission_name text");
    $this->db->query("alter table challenge_results_run add column scoreboard_name text");
    $this->db->query("alter table challenge_challenge add column anonymize boolean");
    // default to not anonymous
    $this->db->query("update challenge_challenge set anonymize=0");
    }

  public function postUpgrade()
    {
    }
}
?>