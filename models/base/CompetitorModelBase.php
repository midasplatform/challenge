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
include_once BASE_PATH . '/modules/challenge/constant/module.php';
/** CompetitorModel Base class */
abstract class Challenge_CompetitorModelBase extends Challenge_AppModel {



  /**
   * constructor
   */
  public function __construct()
    {
    parent::__construct();
    $this->_name = 'challenge_competitor';
    $this->_key = 'challenge_competitor_id';
    $this->_daoName = 'CompetitorDao';
    $this->_mainData = array(
      'challenge_competitor_id' => array('type' => MIDAS_DATA),
      'challenge_id' => array('type' => MIDAS_DATA),
      'user_id' => array('type' => MIDAS_DATA),
      'training_submission_folder_id' => array('type' => MIDAS_DATA),
      'training_output_folder_id' => array('type' => MIDAS_DATA),
      'testing_submission_folder_id' => array('type' => MIDAS_DATA),
      'testing_output_folder_id' => array('type' => MIDAS_DATA),
      'challenge' =>  array('type' => MIDAS_MANY_TO_ONE,
                        'module' => 'challenge',
                        'model' => 'Challenge',
                        'parent_column' => 'challenge_id',
                        'child_column' => 'challenge_id'),
      'training_submission_folder' =>  array('type' => MIDAS_MANY_TO_ONE,
                        'model' => 'Folder',
                        'parent_column' => 'training_submission_folder_id',
                        'child_column' => 'folder_id'),
      'training_output_folder' =>  array('type' => MIDAS_MANY_TO_ONE,
                        'model' => 'Folder',
                        'parent_column' => 'training_output_folder_id',
                        'child_column' => 'folder_id'),
      'testing_submission_folder' =>  array('type' => MIDAS_MANY_TO_ONE,
                        'model' => 'Folder',
                        'parent_column' => 'testing_submission_folder_id',
                        'child_column' => 'folder_id'),
      'testing_output_folder' =>  array('type' => MIDAS_MANY_TO_ONE,
                        'model' => 'Folder',
                        'parent_column' => 'testing_output_folder_id',
                        'child_column' => 'folder_id')
       );
    $this->initialize(); // required
    }

  public abstract function findChallengeCompetitor($userId, $challengeId);




}  // end class Challenge_CompetitorModelBase