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
    $sql = $this->database->select(array("user_id", "challenge_results_run_id"))->setIntegrityCheck(false);
    $sql->from(array('crr' => 'challenge_results_run'));
    $sql->join(array('bt' => 'batchmake_task'), 'crr.batchmake_task_id=bt.batchmake_task_id');
    $sql->where('crr.challenge_id=?', $challengeId);
    $sql->where('crr.results_type=?', MIDAS_CHALLENGE_TESTING);

    $sql_out = (string) $sql    ;
    
    $rowset = $this->database->fetchAll($sql);
    $rows = array();
    foreach($rowset as $row)
      {
      $rows[$row['user_id']] = $row['latest_results_run_id'];
      }
    return $rows;
    
    
    }
}
