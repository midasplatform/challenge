<?php

class Challenge_Upgrade_0_0_9 extends MIDASUpgrade
{
  public function preUpgrade()
    {
    }

  public function mysql()
    {
    $this->db->query("ALTER TABLE `challenge_results_run_item` modify `result_value` double");
    }

  public function postUpgrade()
    {
    }
}
?>
