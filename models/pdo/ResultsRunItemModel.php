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
require_once BASE_PATH . '/modules/challenge/models/base/ResultsRunItemModelBase.php';


/** Challenge_ResultsRunItemModel */
class Challenge_ResultsRunItemModel extends Challenge_ResultsRunItemModelBase {


  function loadResultsItemsValues($challengeResultsRunId)
    {
    $sql = $this->database->select()->setIntegrityCheck(false);
    $sql->from(array('crri' => 'challenge_results_run_item'), array('test_item_id', 'result_key', 'result_value'));
    $sql->joinInner(array('i1' => 'item'), 'crri.test_item_id=i1.item_id', array('test_item_name' => 'i1.name'));
    $sql->where('crri.challenge_results_run_id=?', $challengeResultsRunId);
    $rowset = $this->database->fetchAll($sql);

    $returnVals = array();
    foreach($rowset as $row)
      {
      $returnRow = array();
      $returnRow['test_item_name'] = $row['test_item_name'];
      $returnRow['result_value'] = $row['result_value'];
      $returnRow['result_key'] = $row['result_key'];
      $returnVals[] = $returnRow;
      }
    return $returnVals;
    }

    
  function loadLatestResultsRunSummary($resultRunId) 
    {
    $sql = $this->database->select()->setIntegrityCheck(false);
    $sql->from(array("ccri" => "challenge_results_run_item"));
    $sql->where('challenge_results_run_id=?', $resultRunId);
    $sql->columns   (array("result_count" => "COUNT(*)", "metric_sum" => "sum(result_value)", "metric_average" => "avg(result_value)"));
    $sql->group('ccri.result_key');
    $sql_out = (string)$sql;  

    $results = $this->database->fetchAll($sql);
    $runResults = array();
    foreach($results as $row)
      {
      $runResults[$row['result_key']] = array(
          'result_count' => $row['result_count'],
          'metric_sum' => $row['metric_sum'],
          'metric_average' => $row['metric_average']);
      }
    return $runResults;     
    }
    
}
