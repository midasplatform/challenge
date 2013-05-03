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
    $sql->from(array("ccri" => "challenge_results_run_item"), array('result_key', 'result_value', 'challenge_selected_metric_id'));
    $sql->where('challenge_results_run_id=?', $resultRunId);
    $sql->where('status=?', MIDAS_CHALLENGE_RR_STATUS_COMPLETE);
    $runResults = array();
    
    $challengeSelectedMetricModel = MidasLoader::loadModel("SelectedMetric", "challenge");
    $selectedMetrics = array();
        
    $results = $this->database->fetchAll($sql);
    foreach($results as $row)
      {
      // skip any metrics that shouldn't be scored
      $challengeSelectedMetricId = $row['challenge_selected_metric_id'];
      if(!array_key_exists($challengeSelectedMetricId, $selectedMetrics))
        {
        $selectedMetrics[$challengeSelectedMetricId] = $challengeSelectedMetricModel->load($challengeSelectedMetricId);
        }
      if(!empty($selectedMetrics[$challengeSelectedMetricId]) && 
         !$selectedMetrics[$challengeSelectedMetricId]->getMetric()->getIncludeInScore())
        {
        continue;  
        }
      
      $resultKey = $row['result_key'];
      if(!array_key_exists($resultKey, $runResults))
        {
        $runResults[$resultKey] = array('result_count' => 0, 'metric_sum' => 0, 'metric_average' => 0);  
        }
      $runResults[$resultKey]['result_count'] += 1;
      $resultValue = $row['result_value'];
      // if a value is infinity, then the column average is infinity
      if(!is_infinite($runResults[$resultKey]['metric_sum']))
        {
        if($resultValue == MIDAS_CHALLENGE_ARBITRARILY_LARGE_DOUBLE)
          {
          $runResults[$resultKey]['metric_sum'] = INF;  
          $runResults[$resultKey]['metric_average'] = INF;  
          }
        else
          {
          // TODO: worry about overflow?
          $runResults[$resultKey]['metric_sum'] += $resultValue;  
          }
        }
      }
      
    foreach($runResults as $resultKey => $resultsSummary)
      {
      if(!is_infinite($resultsSummary['metric_sum']))
        {
        $sum = (float)$resultsSummary['metric_sum'];
        $count = (float)$resultsSummary['result_count'];
        $average = $sum / $count;
        $runResults[$resultKey]['metric_average'] = $average; 
        }
      }
        
    return $runResults;        
    }
    
}
