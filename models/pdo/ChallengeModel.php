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
require_once BASE_PATH . '/modules/challenge/models/base/ChallengeModelBase.php';


/** Challenge_ChallengeModel */
class Challenge_ChallengeModel extends Challenge_ChallengeModelBase {

  /**
   * Returns challenge by a communityId
   * @param type $communityId
   * @return type
   */

  function getByCommunityId($communityId)
    {
    $rowset = $this->database->fetchAll($this->database->select()->where('community_id=?', $communityId));
    $return = array();
    foreach($rowset as $row)
      {
      $challengeId = $row['challenge_id'];
      $trainingStatus = $row['training_status'];
      $testingStatus = $row['testing_status'];
      $return[$challengeId] = array('training_status' => $trainingStatus, 'testing_status' => $testingStatus);
      }
    return $return;
    }

  /**
   * lists the set of challenges for a user, based on which communities
   * they are a member of, if a status is provided, will filter by status.
   * @param UserDao $userDao
   * @param $trainingStatus
   * @param $testingStatus
   * @return type
   */
  function findAvailableChallenges($userDao, $trainingStatus = null, $testingStatus = null)
    {
    if(!$userDao)
      {
      throw new Exception('You must be logged in to create a challenge');
      }
    if(!$userDao instanceof UserDao)
      {
      throw new Zend_Exception("userDao should be a valid instance.");
      }

    $userId = $userDao->getUserId();
    $membersGroupName = "Members";
    $sql = $this->database->select()->setIntegrityCheck(false);
    $sql->from(array('cc' => 'challenge_challenge'));
    $sql->join(array('g' => 'group'), 'cc.community_id=g.community_id');
    $sql->join(array('u2g' => 'user2group'), 'g.group_id=u2g.group_id');
    $sql->join(array('vd' => 'validation_dashboard'), 'validation_dashboard_id=dashboard_id');
    $sql->where('g.name=?', $membersGroupName);
    $sql->where('u2g.user_id=?', $userId);
    if($trainingStatus)
      {
      $sql->where('cc.training_status=?', $trainingStatus);
      }
    if($testingStatus)
      {
      $sql->where('cc.testing_status=?', $testingStatus);
      }

    $rowset = $this->database->fetchAll($sql);
    $return = array();
    foreach($rowset as $row)
      {
      $return[$row['challenge_id']] = array('name' => $row['name'],
                                    'description' => $row['description'],
                                    'id' => $row['challenge_id'],
                                    'training_status' => $row['training_status'],
                                    'testing_status' => $row['testing_status']);
      }
    return $return;
    }

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
    $sql = $this->database->select(array("user_id", "challenge_results_run_id"))->setIntegrityCheck(false);//array('user_id', new Zend_Db_Expr('MAX(challenge_results_run_id)')))->setIntegrityCheck(false);
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
      $rows[$row['user_id']] = $row['latest_results_run_id'];
      }
    return $rows;
    
    
    }
}
