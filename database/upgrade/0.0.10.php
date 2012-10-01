<?php

class Challenge_Upgrade_0_0_10 extends MIDASUpgrade
{
  public function preUpgrade()
    {
    }

  public function mysql()
    {
    $this->db->query("ALTER TABLE `challenge_results_run_item` add `return_code` int(10)");
    $this->db->query("ALTER TABLE `challenge_results_run_item` add `process_out` text");
    $this->db->query("ALTER TABLE `challenge_results_run_item` add `process_err` text");
    $this->db->query("ALTER TABLE `challenge_results_run_item` add `process_log` text");
    $this->db->query("ALTER TABLE `challenge_results_run_item` add `status` text");
    }

  public function postUpgrade()
    {
    }
}