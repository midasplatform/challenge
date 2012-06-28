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
require_once BASE_PATH . '/modules/challenge/models/base/CompetitorModelBase.php';


/** Challenge_CompetitorModel */
class Challenge_CompetitorModel extends Challenge_CompetitorModelBase {

    
  public function findChallengeCompetitor($userId, $challengeId)
    {
    $sql = $this->database->select()->setIntegrityCheck(false);
    $sql->from(array('cc' => 'challenge_competitor'));
    $sql->where('cc.user_id=?', $userId);
    $sql->where('cc.challenge_id=?', $challengeId);

    $rowset = $this->database->fetchAll($sql);
    $return = array();
    foreach($rowset as $row)
      {
      $return[] = 
        array('challenge_id' => $row['challenge_id'],
              'user_id' => $row['user_id'],
              'training_submission_folder_id' => $row['training_submission_folder_id'],
              'training_output_folder_id' => $row['training_output_folder_id'],
              'testing_submission_folder_id' => $row['testing_submission_folder_id'],
              'testing_output_folder_id' => $row['testing_output_folder_id']);
      }
    return $return;
    }

    
}
