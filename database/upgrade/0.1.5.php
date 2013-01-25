<?php

class Challenge_Upgrade_0_1_5 extends MIDASUpgrade
{
  public function preUpgrade()
    {
    }

  public function mysql()
    {
    $this->db->query("ALTER TABLE challenge_metrics add column lowest_score_best BOOLEAN NOT NULL");
    $this->db->query("update challenge_metrics set lowest_score_best = False");
    $this->db->query("update challenge_metrics set lowest_score_best = True where metric_exe_name = 'ValidateImageAveDist'");
    $this->db->query("update challenge_metrics set lowest_score_best = True where metric_exe_name = 'ValidateImageHausdorffDist'");
    }
    
  public function postUpgrade()
    {
    }
}
?>
