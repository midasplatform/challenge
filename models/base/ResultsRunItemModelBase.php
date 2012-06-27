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
/** ResultsRunItemModel Base class */
abstract class Challenge_ResultsRunItemModelBase extends Challenge_AppModel {



  /**
   * constructor
   */
  public function __construct()
    {
    parent::__construct();
    $this->_name = 'challenge_results_run_item';
    $this->_key = 'challenge_results_run_item_id';
    $this->_daoName = 'ResultsRunItemDao';

    $this->_mainData = array(
      'challenge_results_run_item_id' => array('type' => MIDAS_DATA),
      'challenge_results_run_id' => array('type' => MIDAS_DATA),
      'test_item_id' => array('type' => MIDAS_DATA),
      'results_item_id' => array('type' => MIDAS_DATA),
      'output_item_id' => array('type' => MIDAS_DATA),
      'condor_dag_job_id' => array('type' => MIDAS_DATA),
      'result_key' => array('type' => MIDAS_DATA),
      'result_value' => array('type' => MIDAS_DATA),
      'challenge_results_run' =>  array('type' => MIDAS_MANY_TO_ONE,
                        'module' => 'challenge',
                        'model' => 'ResultsRun',
                        'parent_column' => 'challenge_results_run_id',
                        'child_column' => 'challenge_results_run_id'),
      'test_item' =>  array('type' => MIDAS_MANY_TO_ONE,
                        'model' => 'Item',
                        'parent_column' => 'test_item_id',
                        'child_column' => 'item_id'),
      'results_item' =>  array('type' => MIDAS_MANY_TO_ONE,
                        'model' => 'Item',
                        'parent_column' => 'results_item_id',
                        'child_column' => 'item_id'),
      'output_item' =>  array('type' => MIDAS_MANY_TO_ONE,
                        'model' => 'Item',
                        'parent_column' => 'output_item_id',
                        'child_column' => 'item_id'));
    $this->initialize(); // required
    }



  /** Create a ResultsRunItem
   * @return ResultsRunItemDao */
  function createResultsItemRun($challengeResultsRunId, $testItemId, $resultsItemId, $outputItemId, $condorDagJobId, $resultKey, $resultValue)
    {
    $modelLoad = new MIDAS_ModelLoader();
    $this->loadDaoClass('ResultsRunItemDao', 'challenge');

    // create a new resultsrunitem
    $resultsrunItemDao = new Challenge_ResultsRunItemDao();
    $resultsrunItemDao->setChallengeResultsRunId($challengeResultsRunId);
    $resultsrunItemDao->setTestItemId($testItemId);
    $resultsrunItemDao->setResultsItemId($resultsItemId);
    $resultsrunItemDao->setOutputItemId($outputItemId);
    $resultsrunItemDao->setCondorDagJobId($condorDagJobId);
    $resultsrunItemDao->setResultKey($resultKey);
    $resultsrunItemDao->setResultValue($resultValue);

    $this->save($resultsrunItemDao);
    return $resultsrunItemDao;
    }

  abstract function loadResultsItemsValues($challengeResultsRunId);
  abstract function loadLatestResultsRunSummary($resultRunId);


}  // end class Challenge_ResultsRunItemModelBase