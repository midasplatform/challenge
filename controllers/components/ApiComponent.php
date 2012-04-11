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
   * List all the challenges a user is a competitor for, based on which
   * communities the user is a member of and are associated with challenges.
   * @param status
   * @return an array of challenge ids as keys, with an array of
   * challenge name and description as the value for each key.
   */
  public function competitorListChallenges($args)
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
    $availableChallenges = $challengeModel->findAvailableChallenges($userDao, isset($args['status'])?$args['status']:null);
    return $availableChallenges;
    }
    
 
  /**
   * Check if a community has one or more challenge(s)
   * @param communityId
   * @return an array of challenge ids as keys, with challenge status as the value for each key.
   */
  public function checkCommunity($args)
    {
    $this->_checkKeys(array('communityId'), $args);
    
    $componentLoader = new MIDAS_ComponentLoader();
    $authComponent = $componentLoader->loadComponent('Authentication', 'api');
    $userDao = $authComponent->getUser($args,
                                       Zend_Registry::get('userSession')->Dao);
    if(!$userDao)
      {
      throw new Zend_Exception('You must be logged in to list available challenges');
      }
    
    $communityId = $args['communityId'];  
      
    $modelLoad = new MIDAS_ModelLoader();
    $challengeModel = $modelLoad->loadModel('Challenge', 'challenge');
    $hasChallenges = $challengeModel->getByCommunityId($communityId);
    return $hasChallenges;
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
    list($challengeDao, $communityDao, $memberGroupDao) = $challengeModel->validateChallengeUser($userDao, $challengeId);
    return $challengeModel->getExpectedResultsItems($userDao, $challengeDao);
    }

    // method for validating a training folder or the training folder?  on the 2nd pass
    // method to score a training folder? for the 2nd pass
    //validateChallengeUserFolder

  public function competitorAddResultsRunItem($args)
    {
    $this->_checkKeys(array('challenge_results_run_id', 'test_item_id', 'results_item_id', 'output_item_id', 'condor_job_id', 'scalarresult_id'), $args);

    $componentLoader = new MIDAS_ComponentLoader();
    $authComponent = $componentLoader->loadComponent('Authentication', 'api');
    $userDao = $authComponent->getUser($args,
                                       Zend_Registry::get('userSession')->Dao);
    if(!$userDao)
      {
      throw new Zend_Exception('You must be logged in to add a results run item');
      }

    $challengeResultsRunId = $args['challenge_results_run_id'];
    $testItemId = $args['test_item_id'];
    $resultsItemId = $args['results_item_id'];
    $outputItemId = $args['output_item_id'];
    $condorDagJobId = $args['condor_job_id'];
    $scalarResultId = $args['scalarresult_id'];

    $modelLoad = new MIDAS_ModelLoader();
    $resultsRunItemModel = $modelLoad->loadModel('ResultsRunItem', 'challenge');
    $resultsRunItemDao = $resultsRunItemModel->createResultsItemRun($challengeResultsRunId, $testItemId, $resultsItemId, $outputItemId, $condorDagJobId, $scalarResultId);
    return $resultsRunItemDao;
    }
    
    
    
  /**
   * helper function to ensure that the user is part of the challenge, the
   * challenge is valid, and the user has proper permissions set on both
   * folders involved in the challenge.
   * @param type $userDao
   * @param type $challengeId
   * @param type $resultsFolderId, optional
   * @param type $outputFolderId, optional
   * @return 3 lists of results by comparing testing with results folders, assuming a resultsFolderId is passed in
   */
  protected function validateCompetitorResults($userDao, $challengeId, $resultsFolderId = null, $outputFolderId = null)
    {
    $modelLoad = new MIDAS_ModelLoader();
    $challengeModel = $modelLoad->loadModel('Challenge', 'challenge');

    list($challengeDao, $communityDao, $memberGroupDao) = $challengeModel->validateChallengeUser($userDao, $challengeId);

    
    if($outputFolderId !== null)
      {
      $outputFolder = $challengeModel->validateChallengeUserFolder($userDao, $communityDao, $outputFolderId); 
      }

    if($resultsFolderId !== null)
      {
      $resultsFolder = $challengeModel->validateChallengeUserFolder($userDao, $communityDao, $resultsFolderId); 
      return $challengeModel->getExpectedResultsItems($userDao, $challengeDao, $resultsFolderId);
      }
    
    }


  /**
   * Validate a competitor folder to be used as a results folder.
   * @param challengeId the id of the challenge to display testing inputs for
   * @param resultsFolderId the id of the folder owned by the user and containing results
   * @return a list of pairings b/w the testing folder of the community and
   * this user's result's folder
   */
  public function competitorValidateResults($args)
    {
    $this->_checkKeys(array('challengeId', 'resultsFolderId'), $args);
    $componentLoader = new MIDAS_ComponentLoader();
    $authComponent = $componentLoader->loadComponent('Authentication', 'api');
    $userDao = $authComponent->getUser($args,
                                       Zend_Registry::get('userSession')->Dao);
    if(!$userDao)
      {
      throw new Zend_Exception('You must be logged in to validate a results folder');
      }

    $challengeId = $args['challengeId'];
    $resultsFolderId = $args['resultsFolderId'];

    // TODO better exception handling/return value
    return $this->validateCompetitorResults($userDao, $challengeId, $resultsFolderId, null);
    }


  /**
   * Validate a competitor folder to be used as an output folder.
   * @param challengeId the id of the challenge to display testing inputs for
   * @param outputFolderId the id of the folder owned by the user for adding output
   * @return valid
   */
  public function competitorValidateOutput($args)
    {
    $this->_checkKeys(array('challengeId', 'outputFolderId'), $args);
    $componentLoader = new MIDAS_ComponentLoader();
    $authComponent = $componentLoader->loadComponent('Authentication', 'api');
    $userDao = $authComponent->getUser($args,
                                       Zend_Registry::get('userSession')->Dao);
    if(!$userDao)
      {
      throw new Zend_Exception('You must be logged in to validate a results folder');
      }

    $challengeId = $args['challengeId'];
    $outputFolderId = $args['outputFolderId'];

    $this->validateCompetitorResults($userDao, $challengeId, null, $outputFolderId);
    // TODO better exception handling/return value
    return array("valid" => "true");
    }
    
    
    
  protected function generateMatchedResultsItemIds($userDao, $matchedResults, $resultsFolderId, $challengeId)
    {
    $modelLoad = new MIDAS_ModelLoader();
    $folderModel = $modelLoad->loadModel('Folder');
    $challengeModel = $modelLoad->loadModel('Challenge', 'challenge');
    
    $itemsForExport = array();
    
    // get the list of matched results items
    $resultsFolder = $folderModel->load($resultsFolderId);
    if(!$resultsFolder)
      {
      throw new Zend_Exception("The results folder is invalid");  
      }
    $resultsItems = $folderModel->getItemsFiltered($resultsFolder, $userDao, MIDAS_POLICY_READ);
    foreach($resultsItems as $item)
      {
      $resultsItemName = $item->getName();
      if(in_array($resultsItemName, $matchedResults))
        {
        $itemsForExport[$resultsItemName] = $item->getItemId();
        }
      }
      
    // get the list of matched testing items
    $challengeDao = $challengeModel->load($challengeId);
    $dashboardDao = $challengeDao->getDashboard();
    $testingFolderDao = $dashboardDao->getTesting();
    $testingItems = $folderModel->getItemsFiltered($testingFolderDao, $userDao, MIDAS_POLICY_READ);
    foreach($testingItems as $item)
      {
      $testingItemName = $item->getName();
      if(array_key_exists($testingItemName, $matchedResults))
        {
        $itemsForExport[$testingItemName] = $item->getItemId();
        }
      }
    
    return $itemsForExport;
    }
    
  protected function generateJobsConfig($matchedResults, $itemsPaths)
    {
    $jobConfigParams = array();
    // loop through matched results and items paths
    // generate a job for each matched results:
    // key test, value result, with name mapping to path in itemspaths
    // then add one of each of these values to the relevant config value
    //
    // a bit like a transpose
    $jobConfigParams['cfg_jobInds'] = array();
    $jobConfigParams['cfg_testItems'] = array();
    $jobConfigParams['cfg_resultItems'] = array();
    $jobInd = 0;
    foreach($matchedResults as $testName => $resultName)
      {
      $jobConfigParams['cfg_jobInds'][] = $jobInd++;
      $jobConfigParams['cfg_testItems'][] = $itemsPaths[$testName];
      $jobConfigParams['cfg_resultItems'][] = $itemsPaths[$resultName];
      }
      
    return $jobConfigParams;
    }
    
    
    
  /**
   * Score a competitor folder to be used as a results folder.
   * @param challengeId the id of the challenge to display testing inputs for
   * @param resultsFolderId id of folder owned by the user and containing results
   * @param outputFolderId id of folder writable by the user, will be the parent
   * directory for a newly created directory that will contain any outputs
   * created by the scoring process
   * @return some notion of success or error, to be determined
   */
  public function competitorScoreResults($args)
    {
    $this->_checkKeys(array('challengeId', 'resultsFolderId', 'outputFolderId'), $args);

    $componentLoader = new MIDAS_ComponentLoader();
    $authComponent = $componentLoader->loadComponent('Authentication', 'api');
    $userDao = $authComponent->getUser($args,
                                       Zend_Registry::get('userSession')->Dao);
    if(!$userDao)
      {
      throw new Zend_Exception('You must be logged in to see the testing inputs');
      }

    $challengeId = $args['challengeId'];
    $resultsFolderId = $args['resultsFolderId'];
    $outputFolderId = $args['outputFolderId'];

    $allResults = $this->validateCompetitorResults($userDao, $challengeId, $resultsFolderId, $outputFolderId);
    $matchedResults = $allResults['matchedTestingResults'];

    set_time_limit(0);
    
    // add the results folder to the dashboard
    $modelLoad = new MIDAS_ModelLoader();
    $dashboardModel = $modelLoad->loadModel('Dashboard', 'validation');
    $challengeModel = $modelLoad->loadModel('Challenge', 'challenge');
    $folderModel = $modelLoad->loadModel('Folder');
    $resultsFolderDao = $folderModel->load($resultsFolderId);
    $challengeDao = $challengeModel->load($challengeId);
    $dashboardDao = $challengeDao->getDashboard();
    $dashboardModel->addResult($dashboardDao, $resultsFolderDao);

    
    
    $executeComponent = $componentLoader->loadComponent('Execute', 'batchmake');
    $kwbatchmakeComponent = $componentLoader->loadComponent('KWBatchmake', 'batchmake');
   
    // create a task
    $taskDao = $kwbatchmakeComponent->createTask($userDao);
    
    
    // TODO what is exe and params?
    $executableName = "TODO";
    $params = "TODO";
    
    
    // create a resultsrun
    $resultsrunModel = $modelLoad->loadModel('ResultsRun', 'challenge');
    $resultsrunDao = $resultsrunModel->createResultsRun($userDao, $challengeId, $executableName, $params, $taskDao->getBatchmakeTaskId(), $resultsFolderId, $outputFolderId);
    
    
    
    $itemsForExport = $this->generateMatchedResultsItemIds($userDao, $matchedResults, $resultsFolderId, $challengeId);
    $itemsPaths = $executeComponent->exportSingleBitstreamItemsToWorkDataDir($userDao, $taskDao, $itemsForExport);
    
    // generate definitions of jobs
    $jobsConfig = $this->generateJobsConfig($matchedResults, $itemsPaths);
    
    $appTaskConfigProperties = array();
    $condorPostScriptPath = BASE_PATH . "/modules/challenge/library/challenge_condor_postscript.py";
    $condorDagPostScriptPath = BASE_PATH . "/modules/challenge/library/challenge_condor_dag_postscript.py";

    
    $configScriptStem = "challenge";
    
    // add the challenge id and results run id
    $jobsConfig['cfg_challengeID'] = $challengeId;
    $jobsConfig['cfg_resultsFolderID'] = $resultsFolderId;
    $jobsConfig['cfg_resultsrunID'] = $resultsrunDao->getChallengeResultsRunId();
    $jobsConfig['cfg_outputFolderID'] = $resultsrunDao->getOutputFolderId();
    $jobsConfig['cfg_dashboardID'] = $dashboardDao->getDashboardId();
    
    $executeComponent->generateBatchmakeConfig($taskDao, $jobsConfig, $condorPostScriptPath, $condorDagPostScriptPath, $configScriptStem);
    
    // export the connection params
    // TODO: right thing to do here?
    // we need to add certain values back as the admin, so export a config
    // file as admin, get user id 1 for this
    
    if($userDao->isAdmin())
      {
      $executeComponent->generatePythonConfigParams($taskDao, $userDao, "user");    
      $executeComponent->generatePythonConfigParams($taskDao, $userDao, "admin");    
      }
    else 
      {
      $userModel = $modelLoad->loadModel('User');
      $adminUserDao = $userModel->load(1);
      $executeComponent->generatePythonConfigParams($taskDao, $userDao, "user");    
      $executeComponent->generatePythonConfigParams($taskDao, $adminUserDao, "admin");    
      }
      
      
    // export the batchmake scripts
    $bmScript = "challenge.bms";
    $kwbatchmakeComponent->preparePipelineScripts($taskDao->getWorkDir(), $bmScript);
    $kwbatchmakeComponent->preparePipelineBmms($taskDao->getWorkDir(), array($bmScript));

    // generate and run the condor dag
    $kwbatchmakeComponent->compileBatchMakeScript($taskDao->getWorkDir(), $bmScript);
    $dagScript = $kwbatchmakeComponent->generateCondorDag($taskDao->getWorkDir(), $bmScript);
    $kwbatchmakeComponent->condorSubmitDag($taskDao->getWorkDir(), $dagScript);

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
