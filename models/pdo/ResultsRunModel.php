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

  //abstract function loadLatestResultsRun($userDao, $challengeDao);
  /**
   * lists the set of challenges for a user, based on which communities
   * they are a member of, if a status is provided, will filter by status.
   * @param UserDao $userDao
   * @param type $status
   * @return type
   */
  function loadLatestResultsRun($userId, $challengeId, $resultsType)
    {
    
//    $sql = $this->database->select('max(challenge_results_run_id)')->setIntegrityCheck(false);
//    $sql = $this->database->select('max(challenge_results_run_id) as challenge_results_run_id')->setIntegrityCheck(false);
    $sql = $this->database->select()->order(array('challenge_results_run_id DESC'))->setIntegrityCheck(false);
    $sql->from(array('crr' => 'challenge_results_run'));
    $sql->join(array('bt' => 'batchmake_task'), 'crr.batchmake_task_id=bt.batchmake_task_id');
    $sql->where('crr.challenge_id=?', $challengeId);
    $sql->where('crr.results_type=?', $resultsType);
    $sql->where('bt.user_id=?', $userId);
    $rowset = $this->database->fetchAll($sql);
    
    //    $rowset = $this->database->fetchAll($this->database->select()->order(array('folder_id DESC')));
    
    foreach($rowset as $row)
      {
      // just get the first one, probably better ways of doing this
      $maxChallengeId = $row['challenge_results_run_id'];  
      break;
      }
    
    return $this->load($maxChallengeId);  
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
//select max(challenge_results_run_id) from challenge_results_run, batchmake_task where challenge_id = 6 and user_id = 2 and batchmake_task.batchmake_task_id = challenge_results_run.batchmake_task_id order by challenge_results_run_id;

    
  function getUsersLatestTestingResults($challengeId)
    {
    /*
    // TODO distinct not working
    $sql = $this->database->select('user_id')->distinct()->setIntegrityCheck(false);
    $sql->from(array('crr' => 'challenge_results_run'));
    $sql->join(array('bt' => 'batchmake_task'), 'crr.batchmake_task_id=bt.batchmake_task_id');
    $sql->where('crr.challenge_id=?', $challengeId);
    $sql->where('crr.results_type=?', $resultsType);

    $rowset = $this->database->fetchAll($sql);
    $rows = array();
    foreach($rowset as $row)
      {
      // TODO distinct not working, so using set/hash to do it
      $rows[$row['user_id']] = $row['user_id'];
      }
   //    select user_id from challenge_results_run, batchmake_task where batchmake_task.batchmake_task_id=challenge_results_run.batchmake_task_id group by user_id;
    return $rows;
    */
//    $query = "select max(challenge_results_run_id) as latest_results_run_id, user_id  from challenge_results_run, batchmake_task where batchmake_task.batchmake_task_id = challenge_results_run.batchmake_task_id and results_type='Testing' and challenge_id=".$challengeId." group by user_id";
    //$query = "select user_id  from challenge_results_run, batchmake_task where batchmake_task.batchmake_task_id = challenge_results_run.batchmake_task_id and results_type='Testing' and challenge_id=".$challengeId." group by user_id";
    //$sql = $this->database->select(array("user_id", "challenge_results_run_id"))->setIntegrityCheck(false);//array('user_id', new Zend_Db_Expr('MAX(challenge_results_run_id)')))->setIntegrityCheck(false);
    $sql = $this->database->select()->setIntegrityCheck(false);//array('user_id', new Zend_Db_Expr('MAX(challenge_results_run_id)')))->setIntegrityCheck(false);
    $sql->from(array('crr' => 'challenge_results_run'));//, 'latest_results_run_id' => new Zend_Db_Expr('MAX(challenge_results_run_id')));
    $sql->join(array('bt' => 'batchmake_task'), 'crr.batchmake_task_id=bt.batchmake_task_id');
    $sql->where('crr.challenge_id=?', $challengeId);
    $sql->where('crr.results_type=?', MIDAS_CHALLENGE_TESTING);
    //$sql->group("user_id");

    $sql_out = (string) $sql    ;
//
    
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

    
    
    
  public function getAllUsersResultsRuns($userId)
    {
    $sql = $this->database->select()->setIntegrityCheck(false);
    $sql->from(array('crr' => 'challenge_results_run'));
    $sql->join(array('cc' => 'challenge_competitor'), 'crr.challenge_competitor_id=cc.challenge_competitor_id');
    $sql->where('cc.user_id=?', $userId);

    $rowset = $this->database->fetchAll($sql);
    $rows = array();
    foreach($rowset as $row)
      {
      $rows[] = $this->load($row['challenge_results_run_id']);  
      }
    return $rows;
    }

    
}
