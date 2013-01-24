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
/** SelctedMetric Model Base class */
abstract class Challenge_SelectedMetricModelBase extends Challenge_AppModel {

  /**
   * constructor
   */
  public function __construct()
    {
    parent::__construct();
    $this->_name = 'challenge_selected_metrics';
    $this->_key = 'challenge_selected_metric_id';
    $this->_daoName = 'SelectedMetricDao';

    $this->_mainData = array(
      'challenge_selected_metric_id' => array('type' => MIDAS_DATA),
      'challenge_id' => array('type' => MIDAS_DATA),
      'challenge_metric_id' => array('type' => MIDAS_DATA),
      'challenge' =>  array('type' => MIDAS_MANY_TO_ONE,
                        'module' => 'challenge',
                        'model' => 'Challenge',
                        'parent_column' => 'challenge_id',
                        'child_column' => 'challenge_id'),
      'metric' =>  array('type' => MIDAS_MANY_TO_ONE,
                        'module' => 'challenge',
                        'model' => 'Metric',
                        'parent_column' => 'challenge_metric_id',
                        'child_column' => 'challenge_metric_id'));
       $this->initialize(); // required
    }





}  // end class Challenge_SelectedMetricModelBase
