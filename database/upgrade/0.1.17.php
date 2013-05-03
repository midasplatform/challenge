<?php

class Challenge_Upgrade_0_1_17 extends MIDASUpgrade
{
  public function preUpgrade()
    {
    }

  public function mysql()
    {
    $this->db->query("ALTER TABLE challenge_metrics add column include_in_score BOOLEAN NOT NULL DEFAULT True");
    $query = "insert into challenge_metrics (metric_name, metric_display_name, metric_exe_name, score_per_label, lowest_score_best,";
    $query .= "reference_link, description, source_link, include_in_score) values ('Validate', 'validate', 'ValidateInputImage', '0', '0', '', 'Validator to print debugging info when comparing two images', 'https://github.com/InsightSoftwareConsortium/covalic/blob/master/Code/Applications/ValidateInputImage/ValidateInputImage.cxx', '0')";
    $this->db->query($query);
    }

  public function postUpgrade()
    {
    }
}
?>