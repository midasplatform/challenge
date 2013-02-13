<?php

class Challenge_Upgrade_0_1_12 extends MIDASUpgrade
{
  public function preUpgrade()
    {
    }

  public function mysql()
    {
    $previousMetrics = array('adb', 'kapp', 'hdb', 'spec', 'sens', 'dice');
    foreach($previousMetrics as $metric)
      {
      $query = "update challenge_results_run_item, challenge_results_run, challenge_metrics, challenge_selected_metrics set challenge_results_run_item.challenge_selected_metric_id = challenge_selected_metrics.challenge_selected_metric_id where challenge_results_run_item.challenge_results_run_id = challenge_results_run.challenge_results_run_id and challenge_selected_metrics.challenge_metric_id = challenge_metrics.challenge_metric_id and metric_display_name ='".$metric."' and  result_key like '".$metric."%' and challenge_selected_metrics.challenge_id = challenge_results_run.challenge_id";
      $this->db->query($query);
      }
    $this->db->query("update challenge_results_run_item set status='complete' where result_value is not null");
    }

  public function postUpgrade()
    {
    }
}
?>