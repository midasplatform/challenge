<?php

class Challenge_Upgrade_0_1_0 extends MIDASUpgrade
{
  public function preUpgrade()
    {
    }

  public function mysql()
    {
    $challengeMetricsTable =
      "CREATE TABLE IF NOT EXISTS challenge_metrics (".
      "challenge_metric_id bigint(20) NOT NULL AUTO_INCREMENT,".
      "metric_name text NOT NULL,".
      "metric_display_name text NOT NULL,".
      "metric_exe_name text NOT NULL,".
      "PRIMARY KEY (challenge_metric_id)".
      ") DEFAULT CHARSET=utf8";
    echo $challengeMetricsTable;
    $this->db->query($challengeMetricsTable);           

    $challengeSelectedMetricsTable =
      "CREATE TABLE IF NOT EXISTS challenge_selected_metrics (".
      "challenge_selected_metric_id bigint(20) NOT NULL AUTO_INCREMENT,".
      "challenge_id bigint(20) NOT NULL,".
      "challenge_metric_id bigint(20) NOT NULL,".
      "label_value text NOT NULL,".
      "result_key text NOT NULL,".
      "PRIMARY KEY (challenge_selected_metric_id)".
      ")   DEFAULT CHARSET=utf8";
    $this->db->query($challengeSelectedMetricsTable);      
      
    $this->db->query("ALTER TABLE challenge_results_run_item add column challenge_selected_metric_id bigint(20)");
    }
    
  public function postUpgrade()
    {
    }
}
?>
