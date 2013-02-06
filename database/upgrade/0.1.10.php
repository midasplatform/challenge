<?php

class Challenge_Upgrade_0_1_10 extends MIDASUpgrade
{
  public function preUpgrade()
    {
    }

  public function mysql()
    {
    $this->db->query("alter table challenge_results_run_item add column return_code int");
    }

  public function postUpgrade()
    {
    }
}
?>