<?php

class Challenge_Upgrade_0_1_11 extends MIDASUpgrade
{
  public function preUpgrade()
    {
    }

  public function mysql()
    {
    $this->db->query("update challenge_results_run_item set result_key = 'dice 1' where result_key = 'Dice(A_1, B_1)'");
    $this->db->query("update challenge_results_run_item set result_key = 'dice 2' where result_key = 'Dice(A_2, B_2)'");
    $this->db->query("update challenge_results_run_item set result_key = 'adb 1' where result_key = 'AveDist(A_1, B_1)'");
    $this->db->query("update challenge_results_run_item set result_key = 'adb 2' where result_key = 'AveDist(A_2, B_2)'");
    $this->db->query("update challenge_results_run_item set result_key = 'hdb 1' where result_key = 'HausdorffDist(A_1, B_1)'");
    $this->db->query("update challenge_results_run_item set result_key = 'hdb 2' where result_key = 'HausdorffDist(A_2, B_2)'");
    $this->db->query("update challenge_results_run_item set result_key = 'sens 1' where result_key = 'Sensitivity(A_1, B_1)'");
    $this->db->query("update challenge_results_run_item set result_key = 'sens 2' where result_key = 'Sensitivity(A_2, B_2)'");
    $this->db->query("update challenge_results_run_item set result_key = 'spec 1' where result_key = 'Specificity(A_1, B_1)'");
    $this->db->query("update challenge_results_run_item set result_key = 'spec 2' where result_key = 'Specificity(A_2, B_2)'");
    $this->db->query("update challenge_results_run_item set result_key = 'kapp' where result_key = 'Kappa(A,B)'");
    }

  public function postUpgrade()
    {
    }
}
?>