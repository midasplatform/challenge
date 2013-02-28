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
require_once BASE_PATH . '/modules/challenge/models/base/ResultsRunModelBase.php';


/** Challenge_ResultsRunModel */
class Challenge_ResultsRunModel extends Challenge_ResultsRunModelBase {

  /**
   * lists the set of challenges for a user, based on which communities
   * they are a member of, if a status is provided, will filter by status.
   * @param UserDao $userDao
   * @param type $status
   * @return type
   */
  function loadLatestResultsRun($userId, $challengeId, $resultsType)
    {
    $sql = $this->database->select()->order(array('challenge_results_run_id DESC'))->setIntegrityCheck(false);
    $sql->from(array('crr' => 'challenge_results_run'));
    $sql->join(array('bt' => 'batchmake_task'), 'crr.batchmake_task_id=bt.batchmake_task_id');
    $sql->where('crr.challenge_id=?', $challengeId);
    $sql->where('crr.results_type=?', $resultsType);
    $sql->where('bt.user_id=?', $userId);
    $rowset = $this->database->fetchAll($sql);
    
    foreach($rowset as $row)
      {
      // just get the first one, probably better ways of doing this
      $maxChallengeId = $row['challenge_results_run_id'];  
      break;
      }
    
    return $this->load($maxChallengeId);  
    }

    
  function getUsersLatestTestingResults($challengeId)
    {
    $sql = $this->database->select()->setIntegrityCheck(false);
    $sql->from(array('crr' => 'challenge_results_run'));
    $sql->join(array('bt' => 'batchmake_task'), 'crr.batchmake_task_id=bt.batchmake_task_id');
    $sql->where('crr.challenge_id=?', $challengeId);
    $sql->where('crr.results_type=?', MIDAS_CHALLENGE_TESTING);

    $rowset = $this->database->fetchAll($sql);
    $rows = array();
    foreach($rowset as $row)
      {
      $challengeResultsRunId = $row['challenge_results_run_id'];
      if(!array_key_exists($row['user_id'], $rows) || (int)$challengeResultsRunId > (int)$rows[$row['user_id']])
        {
        $rows[$row['user_id']] = $row['challenge_results_run_id'];
        }
      }
    return $rows;
    }

    
  public function getAllUsersResultsRuns($userId = false)
    {
    $sql = $this->database->select()->setIntegrityCheck(false);
    $sql->from(array('crr' => 'challenge_results_run'));
    $sql->join(array('cc' => 'challenge_competitor'), 'crr.challenge_competitor_id=cc.challenge_competitor_id');
    if($userId != false)
      { 
      $sql->where('cc.user_id=?', $userId);
      }
    $sql->order(array('challenge_results_run_id DESC'));

    $rowset = $this->database->fetchAll($sql);
    $rows = array();
    foreach($rowset as $row)
      {
      if(!array_key_exists($row['user_id'], $rows))
        {
        $rows[$row['user_id']] = array();  
        }
      $rows[$row['user_id']][] = $this->load($row['challenge_results_run_id']);  
      }
    return $rows;
    }

    
}
