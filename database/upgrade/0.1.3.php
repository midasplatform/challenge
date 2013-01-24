<?php

class Challenge_Upgrade_0_1_3 extends MIDASUpgrade
{
  public function preUpgrade()
    {
    }

  public function mysql()
    {
    $this->db->query("ALTER TABLE challenge_metrics add column score_per_label BOOLEAN NOT NULL");
    $this->db->query("update challenge_metrics set score_per_label = True");
    $this->db->query("update challenge_metrics set score_per_label = False where metric_exe_name = 'ValidateImageKappa'");
    }
    
  public function postUpgrade()
    {
    }
}
?>
