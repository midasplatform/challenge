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

/** EnabledCommunityModel Base class */
abstract class Challenge_EnabledCommunityModelBase extends Challenge_AppModel {


  /**
   * constructor
   */
  public function __construct()
    {
    parent::__construct();
    $this->_name = 'challenge_enabled_community';
    $this->_key = 'challenge_enabled_community_id';
    $this->_daoName = 'EnabledCommunityDao';

    $this->_mainData = array(
      'challenge_enabled_community_id' => array('type' => MIDAS_DATA),
      'community_id' => array('type' => MIDAS_DATA),
      'community' =>  array('type' => MIDAS_ONE_TO_ONE,
                        'model' => 'Community',
                        'parent_column' => 'community_id',
                        'child_column' => 'community_id')

       );
    $this->initialize(); // required
    }

  /** get by communiyId */
  abstract function getByCommunityId($communityId);

  /** get all */
  abstract function getAll();

}  // end class Challenge_EnabledCommunityModelBase