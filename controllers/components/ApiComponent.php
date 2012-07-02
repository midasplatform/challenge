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
   * will create appropriate subfolders and enforce permissions, if permissions
   * on existing folders are not suitable for a challenge, this method will return
   * an error describing the problems and not create a challenge.
   * @param communityId the id of the community to associate with this challenge
   * @param challengeName
   * @param challengeDescription
   * @param folderId optional, the id of the folder in the community to use as a challenge
   * root, if the folder already exists
   * @param trainingStatus
   * @param testingStatus
   * @return id of the newly created challenge
   */
  public function adminCreateChallenge($args)
    {
    $this->_checkKeys(array('communityId', 'challengeName', 'challengeDescription', 'trainingStatus', 'testingStatus'), $args);

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
    $trainingStatus = $args['trainingStatus'];
    $testingStatus = $args['testingStatus'];
    $folderId = false;
    if(array_key_exists('folderId', $args))
      {
      $folderId = $args['folderId'];
      }

    // must be a moderator of the community
    $modelLoad = new MIDAS_ModelLoader();
    $communityModel = $modelLoad->loadModel('Community');
    $communityDao = $communityModel->load($communityId);

    $challengeModel = $modelLoad->loadModel('Challenge', 'challenge');
    $challengeDao = $challengeModel->createChallenge($userDao, $communityDao, $challengeName, $challengeDescription, $trainingStatus, $testingStatus, $folderId);
    // return the challengeId
    return $challengeDao->getKey();
    }

  /**
   * Open a challenge for training, requires challenge moderator status.
   * @param challengeId
   * @param resultsType
   * @return true on success
   */
  public function adminOpenChallenge($args)
    {
    $this->_checkKeys(array('challengeId', 'resultsType'), $args);

    $componentLoader = new MIDAS_ComponentLoader();
    $authComponent = $componentLoader->loadComponent('Authentication', 'api');
    $userDao = $authComponent->getUser($args,
                                       Zend_Registry::get('userSession')->Dao);
    if(!$userDao)
      {
      throw new Zend_Exception('You must be logged in to open a challenge');
      }

    $challengeId = $args['challengeId'];
    $resultsType = $args['resultsType'];
    if($resultsType !== MIDAS_CHALLENGE_TESTING && $resultsType !== MIDAS_CHALLENGE_TRAINING)
      {
      throw new Zend_Exception('resultsType should be one of ['.MIDAS_CHALLENGE_TESTING.'|'.MIDAS_CHALLENGE_TRAINING.']');
      }

    $modelLoad = new MIDAS_ModelLoader();
    $challengeModel = $modelLoad->loadModel('Challenge', 'challenge');
    $challengeModel->openChallenge($userDao, $challengeId, $resultsType);
    return true;
    }

  /**
   * Close a challenge for training, requires challenge moderator status.
   * @param challengeId
   * @param resultsType
   * @return true on success
   */
  public function adminCloseChallenge($args)
    {
    $this->_checkKeys(array('challengeId', 'resultsType'), $args);

    $componentLoader = new MIDAS_ComponentLoader();
    $authComponent = $componentLoader->loadComponent('Authentication', 'api');
    $userDao = $authComponent->getUser($args,
                                       Zend_Registry::get('userSession')->Dao);
    if(!$userDao)
      {
      throw new Zend_Exception('You must be logged in to open a challenge');
      }

    $challengeId = $args['challengeId'];
    $resultsType = $args['resultsType'];
    if($resultsType !== MIDAS_CHALLENGE_TESTING && $resultsType !== MIDAS_CHALLENGE_TRAINING)
      {
      throw new Zend_Exception('resultsType should be one of ['.MIDAS_CHALLENGE_TESTING.'|'.MIDAS_CHALLENGE_TRAINING.']');
      }

    $modelLoad = new MIDAS_ModelLoader();
    $challengeModel = $modelLoad->loadModel('Challenge', 'challenge');
    $challengeModel->closeChallenge($userDao, $challengeId, $resultsType);
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
   * @param trainingStatus
   * @param testingStatus
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
    $availableChallenges =
      $challengeModel->findAvailableChallenges($userDao,
                                               isset($args['trainingStatus']) ? $args['trainingStatus'] : null,
                                               isset($args['testingStatus']) ? $args['testingStatus'] : null);
    return $availableChallenges;
    }


  /**
   * Check if a community has a challenge
   * @param communityId
   * @return an array of challenge ids as keys,
   * with the value being an array of 
   * ('training_status' => $trainingStatus, 'testing_status' => $testingStatus)
   */
  public function anonymousGetChallenge($args)
    {
    $this->_checkKeys(array('communityId'), $args);
    $communityId = $args['communityId'];
    $modelLoad = new MIDAS_ModelLoader();
    $challengeModel = $modelLoad->loadModel('Challenge', 'challenge');
    $includedChallenge = $challengeModel->getByCommunityId($communityId);
    return $includedChallenge;
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

  public function adminAddResultsRunItem($args)
    {
    $this->_checkKeys(array('challenge_results_run_id', 'test_item_id', 'results_item_id', 'condor_job_id', 'result_key', 'result_value'), $args);

    $componentLoader = new MIDAS_ComponentLoader();
    $authComponent = $componentLoader->loadComponent('Authentication', 'api');
    $userDao = $authComponent->getUser($args,
                                       Zend_Registry::get('userSession')->Dao);
    if(!$userDao)
      {
      throw new Zend_Exception('You must be logged in to add a results run item');
      }
    if(!$userDao->getAdmin())
      {
      throw new Zend_Exception('You must be an admin to add a results run item');
      }
      

    $challengeResultsRunId = $args['challenge_results_run_id'];
    $testItemId = $args['test_item_id'];
    $resultsItemId = $args['results_item_id'];
    if(array_key_exists('output_item_id', $args))
      {
      $outputItemId = $args['output_item_id'];
      }
    else
      {
      $outputItemId = 0;
      }
    $condorDagJobId = $args['condor_job_id'];
    $resultKey = $args['result_key'];
    $resultValue = $args['result_value'];

    $modelLoad = new MIDAS_ModelLoader();
    $resultsRunItemModel = $modelLoad->loadModel('ResultsRunItem', 'challenge');
    $resultsRunItemDao = $resultsRunItemModel->createResultsItemRun($challengeResultsRunId, $testItemId, $resultsItemId, $outputItemId, $condorDagJobId, $resultKey, $resultValue);
    return $resultsRunItemDao;
    }

  public function adminUpdateResultsRunItem($args)
    {
    $this->_checkKeys(array('result_run_item_id', 'result_value'), $args);

    $componentLoader = new MIDAS_ComponentLoader();
    $authComponent = $componentLoader->loadComponent('Authentication', 'api');
    $userDao = $authComponent->getUser($args,
                                       Zend_Registry::get('userSession')->Dao);
    if(!$userDao)
      {
      throw new Zend_Exception('You must be logged in to add a results run item');
      }
    if(!$userDao->getAdmin())
      {
      throw new Zend_Exception('You must be an admin to add a results run item');
      }

    $resultsRunItemId = $args['result_run_item_id'];
    $resultValue = $args['result_value'];
      
    $modelLoad = new MIDAS_ModelLoader();
    $resultsRunItemModel = $modelLoad->loadModel('ResultsRunItem', 'challenge');

    
    $resultsRunItem = $resultsRunItemModel->load($resultsRunItemId);
    if(!$resultsRunItem)
      {
      throw new Zend_Exception('Invalid results_run_item_id');
      }
      
    $resultsRunItem->setResultValue($resultValue); 
    $resultsRunItemModel->save($resultsRunItem);
    return $resultsRunItem;
    }

    
  /**
   * helper function to ensure that the user is part of the challenge, the
   * challenge is valid, and the user has proper permissions set on both
   * folders involved in the challenge.
   * @param $userDao
   * @param $challengeId
   * @param $resultsType one of ['Testing'|'Training']
   * @param $resultsFolderId, optional
   * @param $outputFolderId, optional
   * @return 3 lists of results by comparing testing with results folders, assuming a resultsFolderId is passed in
   */
  protected function validateCompetitorResults($userDao, $challengeId, $resultsType, $resultsFolderId = null, $outputFolderId = null)
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
      return $challengeModel->getExpectedResultsItems($userDao, $challengeDao, $resultsType, $resultsFolderId);
      }

    }


  /**
   * Validate a competitor folder to be used as a results folder.
   * @param challengeId the id of the challenge to display testing inputs for
   * @param resultsType one of [Testing|Training]
   * @param resultsFolderId the id of the folder owned by the user and containing results
   * @return a list of pairings b/w the testing folder of the community and
   * this user's result's folder
   */
  public function competitorValidateResults($args)
    {
    $this->_checkKeys(array('challengeId', 'resultsType', 'resultsFolderId'), $args);
    $componentLoader = new MIDAS_ComponentLoader();
    $authComponent = $componentLoader->loadComponent('Authentication', 'api');
    $userDao = $authComponent->getUser($args,
                                       Zend_Registry::get('userSession')->Dao);
    if(!$userDao)
      {
      throw new Zend_Exception('You must be logged in to validate a results folder');
      }

    $challengeId = $args['challengeId'];
    $resultsType = $args['resultsType'];
    $resultsFolderId = $args['resultsFolderId'];

    if($resultsType !== MIDAS_CHALLENGE_TESTING && $resultsType !== MIDAS_CHALLENGE_TRAINING)
      {
      throw new Zend_Exception('resultsType should be one of ['.MIDAS_CHALLENGE_TESTING.'|'.MIDAS_CHALLENGE_TRAINING.']');
      }
    
    // TODO better exception handling/return value
    return $this->validateCompetitorResults($userDao, $challengeId, $resultsType, $resultsFolderId, null);
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



  protected function generateMatchedResultsItemIds($userDao, $matchedResults, $resultsType, $resultsFolderId, $challengeId)
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

    // get the list of matched truth items of the right folder
    $challengeDao = $challengeModel->load($challengeId);
    $dashboardDao = $challengeDao->getDashboard();
    if($resultsType === MIDAS_CHALLENGE_TRAINING)
      {
      $subfolder = $dashboardDao->getTraining();  
      }
    else
      {
      $subfolder = $dashboardDao->getTesting();  
      }
    $truthFolder = $folderModel->getFolderExists(MIDAS_CHALLENGE_TRUTH, $subfolder);
    if(!$truthFolder)
      {
      throw new Zend_Exception('Cannot find truth folder under folderId['.$subfolder->getFolderId().']');
      }
    $truthItems = $folderModel->getItemsFiltered($truthFolder, $userDao, MIDAS_POLICY_READ);
    foreach($truthItems as $item)
      {
      $truthItemName = $item->getName();
      if(array_key_exists($truthItemName, $matchedResults))
        {
        $itemsForExport[$truthItemName] = $item->getItemId();
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
    $jobConfigParams['cfg_truthItems'] = array();
    $jobConfigParams['cfg_resultItems'] = array();
    $jobInd = 0;
    foreach($matchedResults as $testName => $resultName)
      {
      $jobConfigParams['cfg_jobInds'][] = $jobInd++;
      $jobConfigParams['cfg_truthItems'][] = $itemsPaths[$testName];
      $jobConfigParams['cfg_resultItems'][] = $itemsPaths[$resultName];
      }

    return $jobConfigParams;
    }



  /**
   * Score a competitor folder to be used as a results folder.
   * @param challengeId the id of the challenge to display testing inputs for
   * @param resultsFolderId id of folder owned by the user and containing results
   * @param resultsType one of [Testing|Training]
   * @param outputFolderId, optional, id of folder writable by the user, will be the parent
   * directory for a newly created directory that will contain any outputs
   * created by the scoring process
   * @return some notion of success or error, to be determined
   */
  public function competitorScoreResults($args)
    {
    $this->_checkKeys(array('challengeId', 'resultsFolderId', 'resultsType'), $args);

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
    $resultsType = $args['resultsType'];
    if(array_key_exists('outputFolderId', $args))
      {
      $outputFolderId = $args['outputFolderId'];
      }
    else
      {
      $outputFolderId = null;
      }
    
    if($resultsType !== MIDAS_CHALLENGE_TESTING && $resultsType !== MIDAS_CHALLENGE_TRAINING)
      {
      throw new Zend_Exception('resultsType should be one of ['.MIDAS_CHALLENGE_TESTING.'|'.MIDAS_CHALLENGE_TRAINING.']');
      }

    $allResults = $this->validateCompetitorResults($userDao, $challengeId, $resultsType, $resultsFolderId, $outputFolderId);
    $matchedResults = $allResults['matchedTruthResults'];


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

    // create a resultsrun
    $resultsrunModel = $modelLoad->loadModel('ResultsRun', 'challenge');
    $resultsrunDao = $resultsrunModel->createResultsRun($userDao, $challengeId, $resultsType, $taskDao->getBatchmakeTaskId(), $resultsFolderId, $outputFolderId);

    $itemsForExport = $this->generateMatchedResultsItemIds($userDao, $matchedResults, $resultsType, $resultsFolderId, $challengeId);
    $itemsPaths = $executeComponent->exportSingleBitstreamItemsToWorkDataDir($userDao, $taskDao, $itemsForExport);

    // generate definitions of jobs
    $jobsConfig = $this->generateJobsConfig($matchedResults, $itemsPaths);
    
    $resultsRunItemModel = $modelLoad->loadModel('ResultsRunItem', 'challenge');
    $resultsRunItemModel->loadDaoClass('ResultsRunItemDao', 'challenge');
    
    $metrics = array(
        'cfg_avedist1' => 'AveDist(A_1, B_1)',
        'cfg_avedist2' => 'AveDist(A_2, B_2)',
        'cfg_dice1' => 'Dice(A_1, B_1)',
        'cfg_dice2' => 'Dice(A_2, B_2)',
        'cfg_hausdorff1' => 'HausdorffDist(A_1, B_1)',
        'cfg_hausdorff2' => 'HausdorffDist(A_2, B_2)',
        'cfg_kappa' => 'Kappa(A,B)',
        'cfg_sensitivity1' => 'Sensitivity(A_1, B_1)',
        'cfg_sensitivity2' => 'Sensitivity(A_2, B_2)',
        'cfg_specificity1' => 'Specificity(A_1, B_1)',
        'cfg_specificity2' => 'Specificity(A_2, B_2)');
    $resultRunItems_configs = array();
    
    
    foreach($jobsConfig['cfg_jobInds'] as $jobInd)
      {
      $truthItemName = $jobsConfig['cfg_truthItems'][$jobInd];
      $resultItemName = $jobsConfig['cfg_resultItems'][$jobInd];
      $truthItemNameParts = explode('/',$truthItemName);
      $resultItemNameParts = explode('/',$resultItemName);
      $truthItemId = $truthItemNameParts[sizeof($truthItemNameParts)-2];
      $resultItemId = $resultItemNameParts[sizeof($resultItemNameParts)-2];

      foreach($metrics as $metricConfig => $metric)
        {
        if(!array_key_exists($metricConfig, $resultRunItems_configs))
          {
          $resultRunItems_configs[$metricConfig] = array();  
          }
          $resultsrunItemDao = new Challenge_ResultsRunItemDao();
          $resultsrunItemDao->setChallengeResultsRunId($resultsrunDao->getKey());
          $resultsrunItemDao->setTestItemId($truthItemId);
          $resultsrunItemDao->setResultsItemId($resultItemId);
          $resultsrunItemDao->setResultKey($metric);
          $resultsrunItemDao->setResultValue(null); 
          $resultsRunItemModel->save($resultsrunItemDao);
          $resultRunItems_configs[$metricConfig][$jobInd] = $resultsrunItemDao->getKey(); 
        }
      }
    foreach($metrics as $metricConfig => $metric)
      {
      $jobsConfig[$metricConfig] = $resultRunItems_configs[$metricConfig];
      }
    

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
    return array("challenge_results_run_id" => $resultsrunDao->getKey());
    }

  /**
   * Get the results for a competitor for a challenge.
   * @param resultsRunId the id of the results run
   * @return find the most recent (TBD???) set of results for a competitor for
   * the challenge.
   *
   * return value is an array:
   * processing_complete => true/false
   * results_rows => array of rows having keys:
   * test_item_name
   * test_item_id
   * result_item_name
   * result_item_id
   * output_item_name
   * output_item_id
   * metric_item_name
   * metric_item_id
   * score
   */
  public function competitorListResults($args)
    {
    // TODO be smarter about joins, see dashboard method
    $this->_checkKeys(array('resultsRunId'), $args);
    // TODO implementation
    // TODO figure out what happens if no results or more than one set of results

    $componentLoader = new MIDAS_ComponentLoader();
    $authComponent = $componentLoader->loadComponent('Authentication', 'api');
    $userDao = $authComponent->getUser($args,
                                       Zend_Registry::get('userSession')->Dao);
    if(!$userDao)
      {
      throw new Zend_Exception('You must be logged in to view results.');
      }

    $resultsRunId = $args['resultsRunId'];
    

    $modelLoad = new MIDAS_ModelLoader();
    $challengeModel = $modelLoad->loadModel('Challenge', 'challenge');
    $itemModel = $modelLoad->loadModel('Item');
    $resultsrunModel = $modelLoad->loadModel('ResultsRun', 'challenge');
    $resultsRunItemModel = $modelLoad->loadModel('ResultsRunItem', 'challenge');

    $resultsRun = $resultsrunModel->load($resultsRunId);
    $challengeId = $resultsRun->getChallengeId();

    
    list($challengeDao, $communityDao, $memberGroupDao) = $challengeModel->validateChallengeUser($userDao, $challengeId);

    $resultsRunItemsValues = $resultsRunItemModel->loadResultsItemsValues($resultsRun->getChallengeResultsRunId());

//TODO something like this instead
//select challenge_results_run_id, count(*), result_key, sum(result_value), avg(result_value) from challenge_results_run_item group by challenge_results_run_id, result_key;
    // now that we have the results back, combine them based on the test_item_name
    $subjectScores = array();
    $metrics = array(
        'AveDist(A_1, B_1)',
        'AveDist(A_2, B_2)',
        'Dice(A_1, B_1)',
        'Dice(A_2, B_2)',
        'HausdorffDist(A_1, B_1)',
        'HausdorffDist(A_2, B_2)',
        'Kappa(A,B)',
        'Sensitivity(A_1, B_1)',
        'Sensitivity(A_2, B_2)',
        'Specificity(A_1, B_1)',
        'Specificity(A_2, B_2)');
    $metricSums = array();
    foreach($metrics as $metric)
      {
      $metricSums[$metric] = array('count' => 0, 'sum' => 0);  
      }

    // assume we are finished unless we encounter a missing value  
    $processingComplete = 'true';  
      
    foreach($resultsRunItemsValues as $resultsRunItemsValue)
      {
      $testItemName = $resultsRunItemsValue['test_item_name'];
      if(!array_key_exists($testItemName, $subjectScores))
        {
        $subjectScores[$testItemName] = array();  
        }
        $metricType = $resultsRunItemsValue['result_key'];
        $metricScore = $resultsRunItemsValue['result_value'];
        if($metricScore === null)
          {
          $subjectScores[$testItemName][$metricType] = MIDAS_CHALLENGE_WAITING;
          $processingComplete = 'false';
          }
        else
          {
          $subjectScores[$testItemName][$metricType] = $metricScore;
          $metricSum = $metricSums[$metricType];
          $metricSum['count'] = $metricSum['count'] + 1;
          $metricSum['sum'] = $metricSum['sum'] + $metricScore;
          $metricSums[$metricType] = $metricSum;
          }
      }
    
    
    // now compute an average for each metric type
    $subjectScores['averages'] = array();
    foreach($metricSums as $metricType => $totals)
      {
      if($totals['count'] == 0)
        {
        $subjectScores['averages'][$metricType] = MIDAS_CHALLENGE_WAITING;
        }
      else
        {
        $subjectScores['averages'][$metricType] = $totals['sum'] / $totals['count'];  
        }
      }
    
    $resultRows = array();
    // reverse the array to get averages first
    foreach(array_reverse($subjectScores) as $subject => $scores)
      {
      $resultRow = array();
      $pos = strpos($subject, '_truth.mha');
      if($pos > -1)
        {
        $subject = substr($subject, 0, $pos);  
        }
      $resultRow['Subject'] = $subject;
      foreach($metrics as $metric)
        {
        if(array_key_exists($metric, $scores) && is_numeric($scores[$metric]))
          {
          $resultRow[$metric] = round($scores[$metric], 3);
          }
        else
          {
          $resultRow[$metric] = MIDAS_CHALLENGE_WAITING;
          }
        }
      $resultRows[] = $resultRow;
      }
      
      
    /*$returnRows = array();
    foreach($resultsRunItemsValues as $resultsRunItemsValue)
      {
      $test_item_id = $resultsRunItemsValue['test_item_id'];
      $output_item_id = $resultsRunItemsValue['output_item_id'];
      $result_item_id = $resultsRunItemsValue['result_item_id'];
      $testItem = $itemModel->load($test_item_id);
      $outputItem = $itemModel->load($output_item_id);
      $resultItem = $itemModel->load($result_item_id);
      $resultsRunItemsValue['result_item_name'] = $resultItem->getName();
      $resultsRunItemsValue['output_item_name'] = $outputItem->getName();
      $resultsRunItemsValue['test_item_name'] = $testItem->getName();
      $returnRows[] = $resultsRunItemsValue;
      }
*/

    $responseData = array('results_rows' => $resultRows, 'processing_complete' => $processingComplete);
/*
    // TODO this is fake data, uncomment if no condor setup
    $rows = array();
    $row1 = array('test_item_name' => 'test1', 'test_item_id' => '1',
                  'result_item_name' => 'result1', 'result_item_id' => '2',
                  'output_item_name' => 'output1', 'output_item_id' => '8',
                  'metric_item_name' => 'metric1', 'metric_item_id' => '3',
                  'score' => '0.77');
    $row2 = array('test_item_name' => 'test2', 'test_item_id' => '4',
                  'result_item_name' => 'result2', 'result_item_id' => '5',
                  'output_item_name' => 'output2', 'output_item_id' => '9',
                  'metric_item_name' => 'metric1', 'metric_item_id' => '3',
                  'score' => '0.65');
    $row3 = array('test_item_name' => 'test3', 'test_item_id' => '6',
                  'result_item_name' => 'result3', 'result_item_id' => '7',
                  'output_item_name' => 'output3', 'output_item_id' => '10',
                  'metric_item_name' => 'metric1', 'metric_item_id' => '3',
                  'score' => '0.84');

    // some randomization to pretend like processing is happening
    $processingComplete = 'false';
    $randVal = rand(1,3);
    if($randVal === 1)
      {
      $rows[] = $row1;
      }
    else if($randVal === 2)
      {
      $rows[] = $row1;
      $rows[] = $row2;
      }
    else if($randVal === 3)
      {
      $rows[] = $row1;
      $rows[] = $row2;
      $rows[] = $row3;
      $processingComplete = 'true';
      }
    $responseData = array('results_rows' => $rows, 'processing_complete' => $processingComplete);
*/
    return $responseData;
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
   *
   * FOR NOW, probably will change
   *
   * returns value is an array, with each row having:
   * competitor_id : a randomized id
   * test_items: an array with keys being the test item id and values of score
   *
   */
  public function anonymousListDashboard($args)
    {
    $this->_checkKeys(array('challengeId'), $args);
    // TODO implementation
    // TODO figure out what happens if no results or more than one set of results

    $componentLoader = new MIDAS_ComponentLoader();
//    $authComponent = $componentLoader->loadComponent('Authentication', 'api');
//    $userDao = $authComponent->getUser($args,
//                                       Zend_Registry::get('userSession')->Dao);
//    if(!$userDao)
//      {
//      throw new Zend_Exception('You must be logged in to view results.');
//      }

    $challengeId = $args['challengeId'];
    
    $modelLoad = new MIDAS_ModelLoader();
    $challengeModel = $modelLoad->loadModel('Challenge', 'challenge');
    $resultsrunModel = $modelLoad->loadModel('ResultsRun', 'challenge');
    $resultsrunitemModel = $modelLoad->loadModel('ResultsRunItem', 'challenge');

    $challengeDao = $challengeModel->load($challengeId);
    if(!$challengeDao)
      {
      throw new Zend_Exception('You must enter a valid challenge.');
      }
    
    $usersTestingResults = $resultsrunModel->getUsersLatestTestingResults($challengeId);

    $resultsPerCompetitor = array();
    $usersToScoreByMetric = array();
    $metrics = array(
        'AveDist(A_1, B_1)',
        'AveDist(A_2, B_2)',
        'Dice(A_1, B_1)',
        'Dice(A_2, B_2)',
        'HausdorffDist(A_1, B_1)',
        'HausdorffDist(A_2, B_2)',
        'Kappa(A,B)',
        'Sensitivity(A_1, B_1)',
        'Sensitivity(A_2, B_2)',
        'Specificity(A_1, B_1)',
        'Specificity(A_2, B_2)');
    if(!isset($usersTestingResults) || sizeof($usersTestingResults) !== 0)
      {
      //  for each user, for this latest results run, get all averaged score values
      //  
      //  get the latest resultsRun, and all items
      $userRanksByMetric = array();
      $metricResultsByUser = array();
      foreach($usersTestingResults as $userId => $latestTestingResultRunId)
        {
        $userRanksByMetric[$userId] = array();
        $resultsByMetric = $resultsrunitemModel->loadLatestResultsRunSummary($latestTestingResultRunId);  
        $metricResultsByUser[$userId] = $resultsByMetric;
        foreach($metrics as $metric)
          {
          if(!array_key_exists($metric, $usersToScoreByMetric))
            {
            $usersToScoreByMetric[$metric] = array();  
            }
          if(array_key_exists($metric, $resultsByMetric))
            {
            $usersToScoreByMetric[$metric][$userId] = $resultsByMetric[$metric]['metric_average'];
            }
          }
        }
      }
    else
      {
      // TODO now what?
      }

    // now for each metric, sort the results
    foreach($metrics as $metric)
      {
      if(array_key_exists($metric, $usersToScoreByMetric))
        {
        asort($usersToScoreByMetric[$metric], SORT_NUMERIC);
        // now assign ranks to each user for this metric
        $sortedUsers = array_keys($usersToScoreByMetric[$metric]);
        $rank = 1;
        $rankCount = 1;
        $sortedUserSize = sizeof($sortedUsers);
        foreach($sortedUsers as $ind => $user)
          {
          $userRanksByMetric[$user][$metric] = $rank;
          if($ind+1 < sizeof($sortedUsers))
            {
            //TODO how much to round for ranking  
            // only need to look ahead for ties if there are more users  
            $currentScore = round($usersToScoreByMetric[$metric][$user], 3);
            $nextUser = $sortedUsers[$ind+1];
            $nextScore = round($usersToScoreByMetric[$metric][$nextUser], 3);
            if($currentScore === $nextScore)
              {
              // a tie, should have the same rank
              // don't increment the rank, but do keep track of how many at this rank
              $rankCount = $rankCount + 1;
              }
            else
              {
              // increment the rank by however many at this rank, reset rankCount
              $rank = $rank + $rankCount;
              $rankCount = 1;
              }
            }
          }
        }
      }
      
    // now we can combine the ranks for each user for each metric with the metric scores
    // then average the ranks for an overal rank
    foreach($metricResultsByUser as $user => $metricResults)
      {
      $rankSum = 0;
      $rankCount = 0;
      foreach($metricResults as $metric => $scores)
        {
        $rank = $userRanksByMetric[$user][$metric];
        $metricResultsByUser[$user][$metric]['rank'] = $rank;
        $rankSum = $rankSum + $rank;
        $rankCount = $rankCount + 1;
        }
      if($rankCount !== 0)
        {
        $rankAvg = $rankSum/$rankCount;  
        }
      else
        {
        $rankAvg = 0;  
        }
      $metricResultsByUser[$user]['Average Rank'] = round($rankAvg,3);
      }
      
    $returnVal = array('competitor_scores' => $metricResultsByUser);
      
    // now construct an array for each metric of user id to score
    
    
    
    
    
    
    
// can we assume that there is one Testing run per user?
    //select challenge_results_run_id, count(*), result_key, sum(result_value), avg(result_value) from challenge_results_run_item group by challenge_results_run_id, result_key;

    
//    select max(challenge_results_run_id), user_id  from challenge_results_run, batchmake_task where batchmake_task.batchmake_task_id = challenge_results_run.batchmake_task_id and results_type='Testing' and challenge_id=42 group by user_id;
    
    
    
    
    
    
    
/*
    $folderModel = $modelLoad->loadModel('Folder');
    $userModel = $modelLoad->loadModel('User');
    $resultsRunItemModel = $modelLoad->loadModel('ResultsRunItem', 'challenge');
    $dashboardModel = $modelLoad->loadModel('Dashboard', 'validation');



    // check that the community is valid and the user is a member
    $communityDao = $challengeDao->getCommunity();
    if(!$communityDao)
      {
      throw new Zend_Exception('This challenge does not have a valid community');
      }


    //list($challengeDao, $communityDao, $memberGroupDao) = $challengeModel->validateChallengeUser($userDao, $challengeId);
// TODO check this for when no users
    

    $resultsPerCompetitor = array();
    if(!isset($competitorIds) || sizeof($competitorIds) !== 0)
      {
      //  for each user, get the latest resultsRun, and all items
      foreach($competitorIds as $competitorId)
        {
        $resultsRun = $resultsrunModel->loadLatestResultsRun($competitorId, $challengeId);
        $resultsRunItemsValues = $resultsRunItemModel->loadResultsItemsValues($resultsRun->getChallengeResultsRunId());
        $competitorResults = array();
        foreach($resultsRunItemsValues as $resultsRunItemsValue)
          {
          $competitorResults[$resultsRunItemsValue['test_item_id']] = array('name'=> $resultsRunItemsValue['test_item_name'], 'score'=>$resultsRunItemsValue['score']);
          }
        $resultsPerCompetitor[$competitorId] = $competitorResults;
        }
      }
    else
      {
      $competitorId = 1;
      }



    // need a list of all result item ids for the challenge
    $testingFolder = $challengeDao->getDashboard()->getTesting();
    // the results should be public, but there is no user login required for this method
    // so use user id of the latest competitor
    $userDao = $userModel->load($competitorId);
    $testingItems = $folderModel->getItemsFiltered($testingFolder, $userDao, MIDAS_POLICY_READ);

    $testItemIds = array();
    foreach($testingItems as $testingItem)
      {
      $testItems[$testingItem->getItemId()] = $testingItem->getName();
      }

    // get all the users with results for this challenge


    $returnVal = array('test_items' => $testItems, 'competitor_scores' => $resultsPerCompetitor);

   */
    /*
    // TODO this is fake data, uncomment if no condor setup
    $testItems = array("294" => "test1.mha","295" => "test2.mha");

    $resultsPerCompetitor = array('1' => array("294" => array("name" => "test1.mha", "score" => "0.666667"),
                               "295" => array("name" => "test2.mha", "score" => "0.563667")),
                  '2' => array("294" => array("name" => "test1.mha", "score" => "0.8764"),
                               "295" => array("name" => "test2.mha", "score" => "0.67864")));
    $returnVal = array('test_items' => $testItems, 'competitor_scores' => $resultsPerCompetitor);
*/

    return $returnVal;
    }



} // end class
