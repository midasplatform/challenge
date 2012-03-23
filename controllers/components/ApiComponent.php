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

/** Component for api methods */
class Challenge_ApiComponent extends AppComponent
{

  /**
   * Helper function for verifying keys in an input array
   */
  private function _checkKeys($keys, $values)
    {
    foreach($keys as $key)
      {
      if(!array_key_exists($key, $values))
        {
        throw new Exception('Parameter '.$key.' must be set.', -1);
        }
      }
    }

    
  /**
   * Create a challenge with the given name and description, in the community.
   * Will create three folders in the community, truth, training, and testing.
   * @param communityId the id of the community to associate with this challenge
   * @param challengeName
   * @param challengeDescription
   * @return id of the newly created challenge
   */
  public function createChallenge($args)
    {
    $this->_checkKeys(array('communityId', 'challengeName', 'challengeDescription'), $args);

    $componentLoader = new MIDAS_ComponentLoader();
    $authComponent = $componentLoader->loadComponent('Authentication', 'api');
    $args['useSession'] = 'useSession';
    $userDao = $authComponent->getUser($args,
                                       Zend_Registry::get('userSession')->Dao);
    if(!$userDao)
      {
      throw new Exception('You must be logged in to see the testing inputs');
      }

    $communityId = $args['communityId'];
    $challengeName = $args['challengeName'];
    $challengeDescription = $args['challengeDescription'];
      
    // must be a moderator of the community
    $modelLoad = new MIDAS_ModelLoader();
    $communityModel = $modelLoad->loadModel('Community');
    $communityDao = $communityModel->load($communityId);

    if(!$communityDao)
      {
      throw new Exception('You must enter a valid community id');
      }
    // if the use can write to this community, then they are a moderator or admin
    if(!$communityModel->policyCheck($communityDao, $userDao, MIDAS_POLICY_WRITE))
      {
      throw new Exception('You must be a moderator of this community to create a challenge');
      }

    $challengeModel = $modelLoad->loadModel('Challenge', 'challenge');
    $challengeDao = $challengeModel->createChallenge($userDao, $communityDao, $challengeName, $challengeDescription);
    // return the challengeId
    return $challengeDao->getKey();
    }
          
          
          
          
          
  /**
   * Create a dashboard with the given name and description
   * @param challengeId the id of the challenge to display testing inputs for
   * @return a list of item names and expected result names for the challenge
   */
  public function displayTestingInputs($value)
    {
    $this->_checkKeys(array('challengeId'), $value);

    $componentLoader = new MIDAS_ComponentLoader();
    $authComponent = $componentLoader->loadComponent('Authentication', 'api');
    $userDao = $authComponent->getUser($value,
                                       Zend_Registry::get('userSession')->Dao);
    if(!$userDao)
      {
      throw new Exception('You must be logged in to see the testing inputs');
      }
      
    // get the challenge, check that the challenge is valid
    // get the community from the challenge, check that they are a member of the community
      
    // get the testing folder from the community
    // get the listing of items from the testing folder
    // create the pairings of items names with expected results names
    // return the items listing
    }
/*
    $modelLoad = new MIDAS_ModelLoader();
    $model = $modelLoad->loadModel('Dashboard', 'validation');
    $model->loadDaoClass('DashboardDao', 'validation');
    $dao = new Validation_DashboardDao();
    $dao->setName($value['name']);
    $dao->setDescription($value['description']);
    $model->save($dao);

    return array('dashboard_id' => $dao->getKey());
    }
*/

    
    // method for validating a training folder or the training folder?  on the 2nd pass
    // method to score a training folder? for the 2nd pass
    // 
    
    
  /**
   * Validate a competitor folder to be used as a results folder.
   * @param challengeId the id of the challenge to display testing inputs for
   * @param folderId the id of the folder owned by the user and containing results
   * @return a list of pairings b/w the testing folder of the community and
   * this user's result's folder
   */
  public function validateResultsFolder($value)
    {
    $this->_checkKeys(array('challengeId', 'folderId'), $value);

    $componentLoader = new MIDAS_ComponentLoader();
    $authComponent = $componentLoader->loadComponent('Authentication', 'api');
    $userDao = $authComponent->getUser($value,
                                       Zend_Registry::get('userSession')->Dao);
    if(!$userDao)
      {
      throw new Exception('You must be logged in to see the testing inputs');
      }
      
    // get the challenge, check that the challenge is valid
    // get the community from the challenge, check that they are a member of the community
      
    // get the folder, check their ownership permissions
    // check that the folder is private, if not it is an error
    // add the group permissions so that the folder is viewable by the contest moderator or admin, if not, error
    // TODO be sure that we announce on UI that running this will make this folder viewable to contest mod/admin
    // 
    // get the items in this folder
    // get the testing folder from the challenge community
    // calculate the matchups b/w this folder and those in testing (3 parts, same, left +, right+)
    // return the matchup listing
    // 
    // the intention here is to display "here is what would be run" here is what you have and we don,t and vice versa
    // 
    // return the items listing
    }
            
    
 /*   
    validate results folder:
1        check that it is private to the world, if not error
2        check that it is open to moderators of the community (can add this group perm here), if not error
1        calculate the files/images/items that would be scored--compare items in this folder with the testing 
1        return the set of pairs of files/images, do "outer join", so show ones with out pairings in each
   */     
     
    
    /*
    score results action:
            
                    
    score results folder:
1        generate the pairing jobs, for each pairing, setup a bm job
                
    for the bm jobs:
        run an execution, run the php with condor dag py, condor job py,
                bring back in the scalar values and 
    endfor;
        */
            
  /**
   * Score a competitor folder to be used as a results folder.
   * @param challengeId the id of the challenge to display testing inputs for
   * @param folderId the id of the folder owned by the user and containing results
   * @return some notion of success or error, to be determined
   */
  public function scoreResultsFolder($value)
    {
    $this->_checkKeys(array('challengeId', 'folderId'), $value);

    $componentLoader = new MIDAS_ComponentLoader();
    $authComponent = $componentLoader->loadComponent('Authentication', 'api');
    $userDao = $authComponent->getUser($value,
                                       Zend_Registry::get('userSession')->Dao);
    if(!$userDao)
      {
      throw new Exception('You must be logged in to see the testing inputs');
      }
      
//abs1    // get the challenge, check that the challenge is valid
//abs1    // get the community from the challenge, check that they are a member of the community
      
//abs2    // get the folder, check their ownership permissions
//abs2    // check that the folder is private, if not it is an error
    // add the group permissions so that the folder is viewable by the contest moderator or admin, if not, error
    // TODO be sure that we announce on UI that running this will make this folder viewable to contest mod/admin
    // 
//abs3    // get the items in this folder
//abs3    // get the testing folder from the challenge community
//abs3    // calculate the matchups b/w this folder and those in testing (3 parts, same, left +, right+)
    // 
    // based on the listing of matchups, create jobs, start the jobs running
    // return a notion of success
    }
      
    
  
} // end class
