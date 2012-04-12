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
   * Returns challenge(s) by a communityId
   * @param type $communityId
   * @return type
   */

  function getByCommunityId($communityId)
    {
    $rowset = $this->database->fetchAll($this->database->select()->where('community_id=?', $communityId));
    foreach($rowset as $row)
      {
      $challengeId = $row['challenge_id'];
      $status = $row['status'];
      $return[$challengeId] = $status;
      }
    return $return;
    }

  /**
   * lists the set of challenges for a user, based on which communities
   * they are a member of, if a status is provided, will filter by status.
   * @param UserDao $userDao
   * @param type $status
   * @return type
   */
  function findAvailableChallenges($userDao, $status = null)
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
    return $return;
    }

  function getUsersWithSubmittedResults($challengeId)
    {
    // TODO distinct not working
    $sql = $this->database->select('user_id')->distinct()->setIntegrityCheck(false);
    $sql->from(array('crr' => 'challenge_results_run'));
    $sql->join(array('bt' => 'batchmake_task'), 'crr.batchmake_task_id=bt.batchmake_task_id');
    $sql->where('crr.challenge_id=?', $challengeId);
    
    $rowset = $this->database->fetchAll($sql);
    $rows = array();
    foreach($rowset as $row)
      {
      // TODO distinct not working, so using set/hash to do it
      $rows[$row['user_id']] = $row['user_id'];  
      }
   //    select user_id from challenge_results_run, batchmake_task where batchmake_task.batchmake_task_id=challenge_results_run.batchmake_task_id group by user_id;
    return $rows;
    }
}
