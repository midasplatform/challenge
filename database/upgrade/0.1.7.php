<?php

class Challenge_Upgrade_0_1_7 extends MIDASUpgrade
{
  public function preUpgrade()
    {
    }

  public function mysql()
    {
    $this->db->query("alter table challenge_results_run_item drop column output_item_id");
    }

  public function postUpgrade()
    {
    }
}
?>