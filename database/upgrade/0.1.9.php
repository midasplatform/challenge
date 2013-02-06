<?php

class Challenge_Upgrade_0_1_9 extends MIDASUpgrade
{
  public function preUpgrade()
    {
    }

  public function mysql()
    {
    $this->db->query("alter table challenge_results_run_item add column process_out text");
    }

  public function postUpgrade()
    {
    }
}
?>