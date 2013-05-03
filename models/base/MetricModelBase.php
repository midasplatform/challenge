<?php
/*=========================================================================
MIDAS Server
Copyright (c) Kitware SAS. 20 rue de la Villette. All rights reserved.
69328 Lyon, FRANCE.

See Copyright.txt for details.
This software is distributed WITHOUT ANY WARRANTY; without even
the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
PURPOSE.  See the above copyright notices for more information.
=========================================================================*/
include_once BASE_PATH . '/modules/challenge/constant/module.php';
/** Metric Model Base class */
abstract class Challenge_MetricModelBase extends Challenge_AppModel {



  /**
   * constructor
   */
  public function __construct()
    {
    parent::__construct();
    $this->_name = 'challenge_metrics';
    $this->_key = 'challenge_metric_id';
    $this->_daoName = 'MetricDao';

    $this->_mainData = array(
      'challenge_metric_id' => array('type' => MIDAS_DATA),
      'metric_name' => array('type' => MIDAS_DATA),
      'metric_display_name' => array('type' => MIDAS_DATA),
      'score_per_label' => array('type' => MIDAS_DATA),
      'lowest_score_best' => array('type' => MIDAS_DATA),
      'include_in_score' => array('type' => MIDAS_DATA),
      'metric_exe_name' => array('type' => MIDAS_DATA),
      'reference_link' => array('type' => MIDAS_DATA),
      'description' => array('type' => MIDAS_DATA),
      'source_link' => array('type' => MIDAS_DATA));
    $this->initialize(); // required
    }

    
  abstract function fetchAll();

}  // end class Challenge_MetricModelBase
