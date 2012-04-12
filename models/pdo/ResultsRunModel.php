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
  function loadLatestResultsRun($userId, $challengeId)
    {
    
//    $sql = $this->database->select('max(challenge_results_run_id)')->setIntegrityCheck(false);
//    $sql = $this->database->select('max(challenge_results_run_id) as challenge_results_run_id')->setIntegrityCheck(false);
    $sql = $this->database->select()->order(array('challenge_results_run_id DESC'))->setIntegrityCheck(false);
    $sql->from(array('crr' => 'challenge_results_run'));
    $sql->join(array('bt' => 'batchmake_task'), 'crr.batchmake_task_id=bt.batchmake_task_id');
    $sql->where('crr.challenge_id=?', $challengeId);
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


}
