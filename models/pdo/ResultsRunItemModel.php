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
    //select * from challenge_results_run_item, validation_scalarresult where challenge_results_run_id = 15 and validation_scalarresult.scalarresult_id = challenge_results_run_item.validation_scalarresult_id

  //  $userId = $userDao->getUserId();
  //  $challengeId = $challengeDao->getChallengeId();
    
//    $sql = $this->database->select('max(challenge_results_run_id)')->setIntegrityCheck(false);
//    $sql = $this->database->select('max(challenge_results_run_id) as challenge_results_run_id')->setIntegrityCheck(false);
    $sql = $this->database->select()->setIntegrityCheck(false);
    $sql->from(array('crri' => 'challenge_results_run_item'));
    $sql->join(array('vs' => 'validation_scalarresult'), 'vs.scalarresult_id = crri.validation_scalarresult_id');
    $sql->where('crri.challenge_results_run_id=?', $challengeResultsRunId);
    $rowset = $this->database->fetchAll($sql);
    
    //    $rowset = $this->database->fetchAll($this->database->select()->order(array('folder_id DESC')));
    //results_item_id | condor_dag_job_id | validation_scalarresult_id | output_item_id | scalarresult_id | folder_id | item_id | value |
    
    $returnVals = array();
    
    foreach($rowset as $row)
      {
      $returnRow = array();
      $returnRow['result_item_id'] = $row['results_item_id'];
      $returnRow['output_item_id'] = $row['output_item_id'];
      $returnRow['test_item_id'] = $row['test_item_id'];
      $returnRow['score'] = $row['value'];
      $returnVals[] = $returnRow;  
      }
    return $returnVals;  
    /*
    $membersGroupName = "Members";
    $sql = $this->database->select('max(challenge_results_run_id)')->setIntegrityCheck(false);
    $sql->from(array('cc' => 'challenge_challenge'));
    $sql->join(array('g' => 'group'), 'cc.community_id=g.community_id');
    $sql->join(array('u2g' => 'user2group'), 'g.group_id=u2g.group_id');
    $sql->join(array('vd' => 'validation_dashboard'), 'validation_dashboard_id=dashboard_id');
    $sql->where('g.name=?', $membersGroupName);
    $sql->where('u2g.user_id=?', $userId);
    if($status)
      {
      $sql->where('cc.status=?', $status);
      }

    $rowset = $this->database->fetchAll($sql);
    $return = array();
    foreach($rowset as $row)
      {
      $challengeId = $row['challenge_id'];
      $name = $row['name'];
      $description = $row['description'];
      $return[$challengeId] = array('name' => $name, 'description' => $description);
      }
    return $return;*/
    }


}
