<?php

class Challenge_Upgrade_0_1_8 extends MIDASUpgrade
{
  public function preUpgrade()
    {
    }

  public function mysql()
    {
    $this->db->query("alter table challenge_results_run_item add column status text");
    }

  public function postUpgrade()
    {
    }
}
?>