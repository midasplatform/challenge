<?php

class Challenge_Upgrade_0_1_14 extends MIDASUpgrade
{
  public function preUpgrade()
    {
    }

  public function mysql()
    {
    $this->db->query("alter table challenge_metrics add column reference_link text");
    $this->db->query("alter table challenge_metrics add column description text");
    $this->db->query("alter table challenge_metrics add column source_link text");


    $metricInfo = array(
        'kapp' => array('reference_link' => 'http://en.wikipedia.org/wiki/Cohen%27s_kappa',
                        'description' => '',
                        'source_link' => 'https://github.com/InsightSoftwareConsortium/covalic/blob/master/Code/Applications/ValidateImageKappa/ValidateImageKappa.cxx'),
        'adb' => array('reference_link' => '',
                        'description' => 'The average distance between two surface boundaries.  Returns Inf when one of the segmentations is blank (all zeros), since distance is not defined in these scenarios.',
                        'source_link' => 'https://github.com/InsightSoftwareConsortium/covalic/blob/master/Code/Applications/ValidateImageAveDist/ValidateImageAveDist.cxx'),
        'hdb' => array('reference_link' => 'http://en.wikipedia.org/wiki/Hausdorff_distance',
                        'description' => 'The 95th percentile furthest distance between two surface boundaries.  Returns Inf when one of the segmentations is blank (all zeros), since distance is not defined in these scenarios.',
                        'source_link' => 'https://github.com/InsightSoftwareConsortium/covalic/blob/master/Code/Applications/ValidateImageHausdorffDist/ValidateImageHausdorffDist.cxx'),
        'sens' => array('reference_link' => 'http://en.wikipedia.org/wiki/Sensitivity_and_specificity#Sensitivity',
                        'description' => 'Sensitivity is the ratio of true positives to true positives plus false negatives.',
                        'source_link' => 'https://github.com/InsightSoftwareConsortium/covalic/blob/master/Code/Applications/ValidateImageSensitivity/ValidateImageSensitivity.cxx'),
        'spec' => array('reference_link' => 'http://en.wikipedia.org/wiki/Sensitivity_and_specificity#Specificity',
                        'description' => 'Specificity is the ratio of true negatives to true negatives plus false positives.',
                        'source_link' => 'https://github.com/InsightSoftwareConsortium/covalic/blob/master/Code/Applications/ValidateImageSpecificity/ValidateImageSpecificity.cxx'),
        'dice' => array('reference_link' => 'http://en.wikipedia.org/wiki/Dice%27s_coefficient',
                        'description' => 'The Dice Coefficient is a measure of statistical similarity between the labelled region in the ground truth and the labelled region in the tested result.',
                        'source_link' => 'https://github.com/InsightSoftwareConsortium/covalic/blob/master/Code/Applications/ValidateImageDice/ValidateImageDice.cxx'),
        'jacc' => array('reference_link' => 'http://en.wikipedia.org/wiki/Jaccard_index',
                        'description' => 'The Jaccard Index is a measure of statistical similarity between the labelled region in the ground truth and the labelled region in the tested result.',
                        'source_link' => 'https://github.com/InsightSoftwareConsortium/covalic/blob/master/Code/Applications/ValidateImageJaccard/ValidateImageJaccard.cxx'),
        'ppv' => array('reference_link' => 'http://en.wikipedia.org/wiki/Positive_predictive_value',
                        'description' => 'Positive Predictive Value is the ratio of true positives to true positives plus false positives.',
                        'source_link' => 'https://github.com/InsightSoftwareConsortium/covalic/blob/master/Code/Applications/ValidateImagePPV/ValidateImagePPV.cxx')
    );
    foreach($metricInfo as $metric => $metricData)
      {
      $ref = $metricData['reference_link'];
      $desc = $metricData['description'];
      $source = $metricData['source_link'];
      $query = "update challenge_metrics set reference_link='".$ref."', description='".$desc."', source_link='".$source."' where metric_display_name='".$metric."'";  
      $this->db->query($query);
      }

    }
    
  public function postUpgrade()
    {
    }
}
?>