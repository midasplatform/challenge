<?php

class Challenge_Upgrade_0_1_6 extends MIDASUpgrade
{
  public function preUpgrade()
    {
    }

  public function mysql()
    {
    $this->db->query("insert into challenge_metrics (metric_name, metric_display_name, metric_exe_name, score_per_label, lowest_score_best) values ('Jaccard', 'jacc', 'ValidateImageJaccard', 1, 0)");
    $this->db->query("insert into challenge_metrics (metric_name, metric_display_name, metric_exe_name, score_per_label, lowest_score_best) values ('Positive Predictive Value', 'ppv', 'ValidateImagePPV', 1, 0)");
    }
    
  public function postUpgrade()
    {
    }
}
?>