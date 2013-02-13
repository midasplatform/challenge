<?php

class Challenge_Upgrade_0_1_13 extends MIDASUpgrade
{
  public function preUpgrade()
    {
    }

  public function mysql()
    {
    $this->db->query("alter table challenge_results_run add column status text");
    }

  public function postUpgrade()
    {
    }
}
?>