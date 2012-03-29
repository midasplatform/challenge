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
   * Create a closed challenge with given name and description, in the community,
   * will create three folders in the community, truth, training, and testing.
   * @param communityId the id of the community to associate with this challenge
   * @param challengeName
   * @param challengeDescription
   * @return id of the newly created challenge
   */
  public function adminCreateChallenge($args)
    {
    $this->_checkKeys(array('communityId', 'challengeName', 'challengeDescription'), $args);

    $componentLoader = new MIDAS_ComponentLoader();
    $authComponent = $componentLoader->loadComponent('Authentication', 'api');
    $userDao = $authComponent->getUser($args,
                                       Zend_Registry::get('userSession')->Dao);
    if(!$userDao)
      {
      throw new Zend_Exception('You must be logged in to create a challenge');
      }

    $communityId = $args['communityId'];
    $challengeName = $args['challengeName'];
    $challengeDescription = $args['challengeDescription'];

    // must be a moderator of the community
    $modelLoad = new MIDAS_ModelLoader();
    $communityModel = $modelLoad->loadModel('Community');
    $communityDao = $communityModel->load($communityId);

    $challengeModel = $modelLoad->loadModel('Challenge', 'challenge');
    $challengeDao = $challengeModel->createChallenge($userDao, $communityDao, $challengeName, $challengeDescription);
    // return the challengeId
    return $challengeDao->getKey();
    }

  /**
   * Open a challenge, requires challenge moderator status.
   * @param challengeId
   * @return true on success
   */
  public function adminOpenChallenge($args)
    {
    $this->_checkKeys(array('challengeId'), $args);

    $componentLoader = new MIDAS_ComponentLoader();
    $authComponent = $componentLoader->loadComponent('Authentication', 'api');
    $userDao = $authComponent->getUser($args,
                                       Zend_Registry::get('userSession')->Dao);
    if(!$userDao)
      {
      throw new Zend_Exception('You must be logged in to open a challenge');
      }

    $challengeId = $args['challengeId'];

    $modelLoad = new MIDAS_ModelLoader();
    $challengeModel = $modelLoad->loadModel('Challenge', 'challenge');
    $challengeModel->openChallenge($userDao, $challengeId);
    return true;
    }

  /**
   * Close a challenge, requires challenge moderator status.
   * @param challengeId
   * @return true on success
   */
  public function adminCloseChallenge($args)
    {
    $this->_checkKeys(array('challengeId'), $args);

    $componentLoader = new MIDAS_ComponentLoader();
    $authComponent = $componentLoader->loadComponent('Authentication', 'api');
    $userDao = $authComponent->getUser($args,
                                       Zend_Registry::get('userSession')->Dao);
    if(!$userDao)
      {
      throw new Zend_Exception('You must be logged in to open a challenge');
      }

    $challengeId = $args['challengeId'];

    $modelLoad = new MIDAS_ModelLoader();
    $challengeModel = $modelLoad->loadModel('Challenge', 'challenge');
    $challengeModel->closeChallenge($userDao, $challengeId);
    return true;
    }

  /**
   * Add a metric to a challenge
   * no implementation, stub
   * @param challengeId
   * @param metricId ??
   */
  public function adminAddMetric($args)
    {
    }


  /**
   * List the open challenges a user is a competitor for, based on which
   * communities the user is a member of and are associated with challenges.
   * @return an array of challenge ids as keys, with an array of
   * challenge name and description as the value for each key.
   */
  public function competitorListOpenChallenges($args)
    {
    $componentLoader = new MIDAS_ComponentLoader();
    $authComponent = $componentLoader->loadComponent('Authentication', 'api');
    $userDao = $authComponent->getUser($args,
                                       Zend_Registry::get('userSession')->Dao);
    if(!$userDao)
      {
      throw new Zend_Exception('You must be logged in to list available challenges');
      }

    $modelLoad = new MIDAS_ModelLoader();
    $challengeModel = $modelLoad->loadModel('Challenge', 'challenge');
    $availableChallenges = $challengeModel->findAvailableChallenges($userDao, MIDAS_CHALLENGE_STATUS_OPEN);
    return $availableChallenges;
    }



  /**
   * helper function to generate the names of expected results items based on
   * testing folder items.
   * @param type $testingItems
   * @return string
   */
  protected function getExpectedResultsItems($testingItems, $resultsItems = null)
    {
    $testingResults = array();
    foreach($testingItems as $item)
      {
      $name = $item->getName();
      $testingResults[$name] = "result_" . $name;
      }
    if($resultsItems === null)
      {
      return $testingResults;
      }
    else
      {
      $resultsItemNames = array();
      $resultsWithoutTesting = array();
      foreach($resultsItems as $item)
        {
        $resultsItemName = $item->getName();
        $resultsItemNames[$resultsItemName] = $resultsItemName;
        if(!in_array($resultsItemName, $testingResults))
          {
          $resultsWithoutTesting[] = $resultsItemName;
          }
        }
      $testingWithoutResults = array();
      $matchedTestingResults = array();
      foreach($testingResults as $testItem => $resultItem)
        {
        if(in_array($resultItem, $resultsItemNames))
          {
          $matchedTestingResults[$testItem] = $resultItem;
          }
        else
          {
          $testingWithoutResults[$testItem] = $resultItem;
          }
        }
      return array('resultsWithoutTesting' => $resultsWithoutTesting, 'testingWithoutResults' => $testingWithoutResults, 'matchedTestingResults' => $matchedTestingResults);
      }
    }



  /**
   * Display the testing data along with expected results filenames for
   * the given challenge.
   * @param challengeId the id of the challenge to display testing inputs for
   * @return a list of item names and expected result names for the challenge
   */
  public function competitorDisplayTestingInputs($args)
    {
    $this->_checkKeys(array('challengeId'), $args);

    $componentLoader = new MIDAS_ComponentLoader();
    $authComponent = $componentLoader->loadComponent('Authentication', 'api');
    $userDao = $authComponent->getUser($args,
                                       Zend_Registry::get('userSession')->Dao);
    if(!$userDao)
      {
      throw new Zend_Exception('You must be logged in to view a challenge');
      }

    $challengeId = $args['challengeId'];

    $modelLoad = new MIDAS_ModelLoader();
    $challengeModel = $modelLoad->loadModel('Challenge', 'challenge');
    $communityModel = $modelLoad->loadModel('Community');
    $groupModel = $modelLoad->loadModel('Group');
    $dashboardModel = $modelLoad->loadModel('Dashboard', 'validation');
    $folderModel = $modelLoad->loadModel('Folder');

    $challengeDao = $challengeModel->load($challengeId);
    if(!$challengeDao)
      {
      throw new Zend_Exception('You must enter a valid challenge.');
      }
    // TODO: any checking for properties of the challenge

    // check that the community is valid and the user is a member
    $communityDao = $challengeDao->getCommunity();
    if(!$communityDao)
      {
      throw new Zend_Exception('This challenge does not have a valid community');
      }
    $memberGroup = $communityDao->getMemberGroup();
    if(!$groupModel->userInGroup($userDao, $memberGroup))
      {
      throw new Zend_Exception('You must join this community to view the challenge');
      }

    // get all the items in the Testing folder
    $dashboardDao = $challengeDao->getDashboard();
    $testingFolderDao = $dashboardDao->getTesting();
    $testingItems = $folderModel->getItemsFiltered($testingFolderDao, $userDao, MIDAS_POLICY_READ);

    // create an expected result filename pairing
    $testingResults = $this->getExpectedResultsItems($testingItems);
    return $testingResults;
    }




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
  public function competitorValidateResultsFolder($args)
    {
    $this->_checkKeys(array('challengeId', 'folderId'), $args);
    $componentLoader = new MIDAS_ComponentLoader();
    $authComponent = $componentLoader->loadComponent('Authentication', 'api');
    $userDao = $authComponent->getUser($args,
                                       Zend_Registry::get('userSession')->Dao);
    if(!$userDao)
      {
      throw new Zend_Exception('You must be logged in to validate a results folder');
      }

    $challengeId = $args['challengeId'];
    $folderId = $args['folderId'];

    $modelLoad = new MIDAS_ModelLoader();
    $challengeModel = $modelLoad->loadModel('Challenge', 'challenge');
    $communityModel = $modelLoad->loadModel('Community');
    $groupModel = $modelLoad->loadModel('Group');
    $dashboardModel = $modelLoad->loadModel('Dashboard', 'validation');
    $folderModel = $modelLoad->loadModel('Folder');
    $folderpolicyuserModel = $modelLoad->loadModel('Folderpolicyuser');
    $folderpolicygroupModel = $modelLoad->loadModel('Folderpolicygroup');

    $challengeDao = $challengeModel->load($challengeId);
    if(!$challengeDao)
      {
      throw new Zend_Exception('You must enter a valid challenge.');
      }
    // TODO: any checking for properties of the challenge? open/closed or other

    // check that the community is valid and the user is a member
    $communityDao = $challengeDao->getCommunity();
    if(!$communityDao)
      {
      throw new Zend_Exception('This challenge does not have a valid community');
      }
    $memberGroup = $communityDao->getMemberGroup();
    if(!$groupModel->userInGroup($userDao, $memberGroup))
      {
      throw new Zend_Exception('You must join this community to submit results to the challenge');
      }

    // get the results folder
    $resultsFolder = $folderModel->load($folderId);

    // ensure user has ownership/admin
    $folderpolicyuserDao = $folderpolicyuserModel->getPolicy($userDao, $resultsFolder);
    if($folderpolicyuserDao->getPolicy() != MIDAS_POLICY_ADMIN)
      {
      throw new Zend_Exception('You must have admin rights to this folder to submit it as a results folder.');
      }

    // ensure that anonymous users cannot access the folder
    $anonymousgroupDao = $groupModel->load(MIDAS_GROUP_ANONYMOUS_KEY);
    $anonymousfolderpolicygroupDao = $folderpolicygroupModel->getPolicy($anonymousgroupDao, $resultsFolder);
    if($anonymousfolderpolicygroupDao)
      {
      throw new Zend_Exception('You must remove anonymous access to this results folder');
      }

    // ensure that community members cannot access the folder
    $membersfolderpolicygroupDao = $folderpolicygroupModel->getPolicy($memberGroup, $resultsFolder);
    if($membersfolderpolicygroupDao)
      {
      throw new Zend_Exception('You must remove challenge community members access to this results folder');
      }

    // add read access to challenge community moderators for the folder
    $moderatorGroup = $communityDao->getModeratorGroup();
    $moderatorReadPolicy = $folderpolicygroupModel->createPolicy($moderatorGroup, $resultsFolder, MIDAS_POLICY_READ);
    if(!$moderatorReadPolicy)
      {
      throw new Zend_Exception('Cannot add read access to challenge moderators to your results folder.');
      }

    // get all the items in the Testing folder
    $dashboardDao = $challengeDao->getDashboard();
    $testingFolderDao = $dashboardDao->getTesting();
    $testingItems = $folderModel->getItemsFiltered($testingFolderDao, $userDao, MIDAS_POLICY_READ);

    // get all items in Results folder
    $resultsItems = $folderModel->getItemsFiltered($resultsFolder, $userDao, MIDAS_POLICY_READ);

    // create a listing of paired item names, along with any mismatches
    $testingResults = $this->getExpectedResultsItems($testingItems, $resultsItems);
    return $testingResults;
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
   * @param resultsFolderId id of folder owned by the user and containing results
   * @param outputFolderId id of folder owned by the user, will be the parent
   * directory for a newly created directory that will contain any outputs
   * created by the scoring process
   * @return some notion of success or error, to be determined
   */
  public function competitorScoreResultsFolder($value)
    {
    $this->_checkKeys(array('challengeId', 'resultsFolderId', 'outputFolderId'), $value);

    $componentLoader = new MIDAS_ComponentLoader();
    $authComponent = $componentLoader->loadComponent('Authentication', 'api');
    $userDao = $authComponent->getUser($value,
                                       Zend_Registry::get('userSession')->Dao);
    if(!$userDao)
      {
      throw new Zend_Exception('You must be logged in to see the testing inputs');
      }

    // TODO: want to call addResult on the results folder

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

  /**
   * Get the results for a competitor for a challenge.
   * @param challengeId the id of the challenge to display testing inputs for
   * @return find the most recent (TBD???) set of results for a competitor for
   * the challenge, a set of rows with values
   * (testing item name and id,
   * results item name and id,
   * metric name,score,
   * output item name and id if one exists)
   */
  public function competitorListResults($value)
    {
    $this->_checkKeys(array('challengeId'), $value);
    // TODO implementation
    // TODO figure out what happens if no results or more than one set of results
    }

  /**
   * Get the dashboard of results for an entire challenge.
   * @param challengeId the id of the challenge to display testing inputs for
   * @return find the most recent (TBD???) set of results for each competitor for
   * the challenge, a set of rows, one row per competitor who has at least one
   * result folder scored
   * (anonymized id of competitor,
   * for every testing item a column with the competitors score on that item,
   * aggregated competitor score)
   */
  public function competitorListDashboard($value)
    {
    $this->_checkKeys(array('challengeId'), $value);
    // TODO implementation
    // TODO figure out what happens if no results or more than one set of results
    }



} // end class
