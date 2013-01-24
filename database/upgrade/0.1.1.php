<?php

class Challenge_Upgrade_0_1_1 extends MIDASUpgrade
{
  public function preUpgrade()
    {
    }

  public function mysql()
    {
    $this->db->query("insert into challenge_metrics (metric_name, metric_display_name, metric_exe_name) values ('Cohen\'s Kappa', 'kapp', 'ValidateImageKappa')");
    $this->db->query("insert into challenge_metrics (metric_name, metric_display_name, metric_exe_name) values ('Average Distance of Boundaries', 'adb', 'ValidateImageAveDist')");
    $this->db->query("insert into challenge_metrics (metric_name, metric_display_name, metric_exe_name) values ('Hausdorff Distance of Boundaries', 'hdb', 'ValidateImageHausdorffDist')");
    $this->db->query("insert into challenge_metrics (metric_name, metric_display_name, metric_exe_name) values ('Sensitivity', 'sens', 'ValidateImageSensitivity')");
    $this->db->query("insert into challenge_metrics (metric_name, metric_display_name, metric_exe_name) values ('Specificity', 'spec', 'ValidateImageSpecificity')");
    $this->db->query("insert into challenge_metrics (metric_name, metric_display_name, metric_exe_name) values ('Dice Overlap', 'dice', 'ValidateImageDice')");
    }
    
  public function postUpgrade()
    {
    }
}
?>