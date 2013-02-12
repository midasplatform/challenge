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

    $authComponent = MidasLoader::loadComponent('Authentication', 'api');
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
    $numberScoredLabels = $args['numberScoredLabels'];
    $folderId = false;
    if(array_key_exists('folderId', $args))
      {
      $folderId = $args['folderId'];
      }

    // must be a moderator of the community
    $communityModel = MidasLoader::loadModel('Community');
    $communityDao = $communityModel->load($communityId);

    $challengeModel = MidasLoader::loadModel('Challenge', 'challenge');
    $challengeDao = $challengeModel->createChallenge($userDao, $communityDao, $challengeName, $challengeDescription, $numberScoredLabels, $trainingStatus, $testingStatus, $folderId);
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

    $authComponent = MidasLoader::loadComponent('Authentication', 'api');
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

    $challengeModel = MidasLoader::loadModel('Challenge', 'challenge');
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

    $authComponent = MidasLoader::loadComponent('Authentication', 'api');
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

    $challengeModel = MidasLoader::loadModel('Challenge', 'challenge');
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
    $authComponent = MidasLoader::loadComponent('Authentication', 'api');
    $userDao = $authComponent->getUser($args,
                                       Zend_Registry::get('userSession')->Dao);
    if(!$userDao)
      {
      throw new Zend_Exception('You must be logged in to list available challenges');
      }

    $challengeModel = MidasLoader::loadModel('Challenge', 'challenge');
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
    $challengeModel = MidasLoader::loadModel('Challenge', 'challenge');
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

    $authComponent = MidasLoader::loadComponent('Authentication', 'api');
    $userDao = $authComponent->getUser($args,
                                       Zend_Registry::get('userSession')->Dao);
    if(!$userDao)
      {
      throw new Zend_Exception('You must be logged in to view a challenge');
      }

    $challengeId = $args['challengeId'];

    $challengeModel = MidasLoader::loadModel('Challenge', 'challenge');
    list($challengeDao, $communityDao, $memberGroupDao) = $challengeModel->validateChallengeUser($userDao, $challengeId);
    return $challengeModel->getExpectedResultsItems($userDao, $challengeDao);
    }

    // method for validating a training folder or the training folder?  on the 2nd pass
    // method to score a training folder? for the 2nd pass
    //validateChallengeUserFolder

  public function adminAddResultsRunItem($args)
    {
    $this->_checkKeys(array('challenge_results_run_id', 'test_item_id', 'results_item_id', 'condor_job_id', 'result_key', 'result_value'), $args);

    $authComponent = MidasLoader::loadComponent('Authentication', 'api');
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

    $resultsRunItemModel = MidasLoader::loadModel('ResultsRunItem', 'challenge');
    $resultsRunItemDao = $resultsRunItemModel->createResultsItemRun($challengeResultsRunId, $testItemId, $resultsItemId, $outputItemId, $condorDagJobId, $resultKey, $resultValue);
    return $resultsRunItemDao;
    }

  public function adminUpdateResultsRunItem($args)
    {
    $this->_checkKeys(array('result_run_item_id'), $args);

    $authComponent = MidasLoader::loadComponent('Authentication', 'api');
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
    $resultsRunItemModel = MidasLoader::loadModel('ResultsRunItem', 'challenge');
    $resultsRunItem = $resultsRunItemModel->load($resultsRunItemId);
    if(!$resultsRunItem)
      {
      throw new Zend_Exception('Invalid results_run_item_id');
      }

    if(array_key_exists('result_value', $args))
      {
      $resultValue = $args['result_value'];
      if($resultValue == 'inf')
        {
        $resultValue = MIDAS_CHALLENGE_ARBITRARILY_LARGE_DOUBLE;  
        }
      $resultsRunItem->setResultValue($resultValue);
      }
    
    if(array_key_exists('condor_dag_job_id', $args)) { $resultsRunItem->setCondorDagJobId($args['condor_dag_job_id']);  }
    if(array_key_exists('result_key', $args)) { $resultsRunItem->setResultKey($args['result_key']);  }
    if(array_key_exists('return_code', $args)) { $resultsRunItem->setReturnCode($args['return_code']);  }
    if(array_key_exists('process_out', $args)) { $resultsRunItem->setProcessOut($args['process_out']);  }
    if(array_key_exists('status', $args)) { $resultsRunItem->setStatus($args['status']);  }
    
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
    $challengeModel = MidasLoader::loadModel('Challenge', 'challenge');

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
    $authComponent = MidasLoader::loadComponent('Authentication', 'api');
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
    $authComponent = MidasLoader::loadComponent('Authentication', 'api');
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
    $folderModel = MidasLoader::loadModel('Folder');
    $challengeModel = MidasLoader::loadModel('Challenge', 'challenge');

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
      
    // using the 1st user for this call, since that is an admin user
    // and we may have export hidden items
    $userModel = MidasLoader::loadModel('User');
    $adminDao =  $userModel->load('1');
    $truthItems = $folderModel->getItemsFiltered($truthFolder, $adminDao, MIDAS_POLICY_READ);
  
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

    $authComponent = MidasLoader::loadComponent('Authentication', 'api');
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
    $dashboardModel = MidasLoader::loadModel('Dashboard', 'validation');
    $challengeModel = MidasLoader::loadModel('Challenge', 'challenge');
    $folderModel = MidasLoader::loadModel('Folder');
    $resultsFolderDao = $folderModel->load($resultsFolderId);
    $challengeDao = $challengeModel->load($challengeId);
    $dashboardDao = $challengeDao->getDashboard();
    $dashboardModel->addResult($dashboardDao, $resultsFolderDao);



    $executeComponent = MidasLoader::loadComponent('Execute', 'batchmake');
    $kwbatchmakeComponent = MidasLoader::loadComponent('KWBatchmake', 'batchmake');

    // create a task
    $taskDao = $kwbatchmakeComponent->createTask($userDao);

    // create a resultsrun
    $resultsrunModel = MidasLoader::loadModel('ResultsRun', 'challenge');
    $resultsrunDao = $resultsrunModel->createResultsRun($userDao, $challengeId, $resultsType, $taskDao->getBatchmakeTaskId(), $resultsFolderId, $outputFolderId);

    $itemsForExport = $this->generateMatchedResultsItemIds($userDao, $matchedResults, $resultsType, $resultsFolderId, $challengeId);

    $userModel = MidasLoader::loadModel('User');
    $adminDao =  $userModel->load('1');
    $itemsPaths = $executeComponent->exportSingleBitstreamItemsToWorkDataDir($adminDao, $taskDao, $itemsForExport);

    // generate definitions of jobs
    $jobsConfig = $this->generateJobsConfig($matchedResults, $itemsPaths);
    
    $resultsRunItemModel = MidasLoader::loadModel('ResultsRunItem', 'challenge');
    $resultsRunItemModel->loadDaoClass('ResultsRunItemDao', 'challenge');
    
    
    // get the list of selected metrics for this challenge
    $selectedMetricModel = MidasLoader::loadModel('SelectedMetric', 'challenge');
    $selectedMetrics = $selectedMetricModel->findBy('challenge_id', $challengeId);
    
    $revised___resultRunItems_configs = array();    
    
    // for each job, that is a pairing of a result with a ground truth
    foreach($jobsConfig['cfg_jobInds'] as $jobInd)
      {
      $truthItemName = $jobsConfig['cfg_truthItems'][$jobInd];
      $resultItemName = $jobsConfig['cfg_resultItems'][$jobInd];
      $truthItemNameParts = explode('/',$truthItemName);
      $resultItemNameParts = explode('/',$resultItemName);
      $truthItemId = $truthItemNameParts[sizeof($truthItemNameParts)-2];
      $resultItemId = $resultItemNameParts[sizeof($resultItemNameParts)-2];

      // created resultrunitems for the selected metrics, for each label
      foreach($selectedMetrics as $selectedMetric)
        {
        $metric = $selectedMetric->getMetric();
        $metricExeName = $metric->getMetricExeName();
        $metricConfig = $metricExeName . "_resultRunItemIds";
        if($selectedMetric->getMetric()->getScorePerLabel())
          {
          $numLabels = $challengeDao->getNumberScoredLabels();  
          }
        else
          {
          $numLabels = 1;  
          }
        for($labelIter = 0; $labelIter < $numLabels; $labelIter++)
          {
          if(!array_key_exists($metricConfig, $revised___resultRunItems_configs))
            {
            $revised___resultRunItems_configs[$metricConfig] = array();  
            }
          $resultsrunItemDao = new Challenge_ResultsRunItemDao();
          $resultsrunItemDao->setChallengeResultsRunId($resultsrunDao->getKey());
          $resultsrunItemDao->setTestItemId($truthItemId);
          $resultsrunItemDao->setResultsItemId($resultItemId);
          if($metric->getScorePerLabel())
            {
            $resultsrunItemDao->setResultKey($metric->getMetricDisplayName() . " " . ($labelIter+1));
            }
          else
            {
            $resultsrunItemDao->setResultKey($metric->getMetricDisplayName());
            }
          $resultsrunItemDao->setResultValue(null); 
          $resultsrunItemDao->setChallengeSelectedMetricId($selectedMetric->getChallengeSelectedMetricId());
          // for even better status reporting we could let the condor_dag_post_script change these to queued
          $resultsrunItemDao->setStatus(MIDAS_CHALLENGE_RRI_STATUS_QUEUED);
          $resultsRunItemModel->save($resultsrunItemDao);
          if(!array_key_exists($jobInd, $revised___resultRunItems_configs[$metricConfig]))
            {
            $revised___resultRunItems_configs[$metricConfig][$jobInd] = $resultsrunItemDao->getKey(); 
            // create and append together as many rri as scored labels
            }
          else
            {  
            $revised___resultRunItems_configs[$metricConfig][$jobInd] .= '_' . $resultsrunItemDao->getKey();
            // create and append together as many rri as scored labels
            }
          }
        }
      }

    // set up each of the selected metrics along with their results run items     
    foreach($selectedMetrics as $selectedMetric)
      {
      $metricExeName = $selectedMetric->getMetric()->getMetricExeName();
      $metricConfig = $metricExeName . "_resultRunItemIds";
      $jobsConfig[$metricConfig] = $revised___resultRunItems_configs[$metricConfig];
      $metricSelected = $metricExeName . "_selected";
      $jobsConfig[$metricSelected] = "1";
      }
      
    // add placeholders as batchmake variables for non-selected metrics
    $challengeMetricModel = MidasLoader::loadModel('Metric', 'challenge');
    $allMetrics = $challengeMetricModel->fetchAll();
    foreach($allMetrics as $metric)
      {
      $metricExeName = $metric->getMetricExeName();
      $metricConfig = $metricExeName . "_resultRunItemIds";
      if(!array_key_exists($metricConfig, $jobsConfig))
        {
        $jobsConfig[$metricConfig] = '';
        $metricSelected = $metricExeName . "_selected";
        $jobsConfig[$metricSelected] = "0";  
        }
      }     
    

    $appTaskConfigProperties = array();
    $condorPostScriptPath = BASE_PATH . "/modules/challenge/library/challenge_condor_script.py";
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
      $userModel = MidasLoader::loadModel('User');
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
   * rris_in_error => list of ids of resultrunitems that were in error
   * rris_complete' => list of ids of resultrunitems that are complete
   */
  public function competitorListResults($args)
    {
    $this->_checkKeys(array('resultsRunId'), $args);
    
    $authComponent = MidasLoader::loadComponent('Authentication', 'api');
    $userDao = $authComponent->getUser($args,
                                       Zend_Registry::get('userSession')->Dao);
    if(!$userDao)
      {
      throw new Zend_Exception('You must be logged in to view results.');
      }

    $resultsRunId = $args['resultsRunId'];
    
    $challengeModel = MidasLoader::loadModel('Challenge', 'challenge');
    $itemModel = MidasLoader::loadModel('Item');
    $resultsrunModel = MidasLoader::loadModel('ResultsRun', 'challenge');
    $resultsRunItemModel = MidasLoader::loadModel('ResultsRunItem', 'challenge');

    $resultsRun = $resultsrunModel->load($resultsRunId);
    $challengeId = $resultsRun->getChallengeId();

    list($challengeDao, $communityDao, $memberGroupDao) = $challengeModel->validateChallengeUser($userDao, $challengeId);

    $resultsRunItems = $resultsRunItemModel->findBy('challenge_results_run_id',$resultsRun->getChallengeResultsRunId());
    // now that we have the results back, combine them based on the test_item_name
    $subjectScores = array();
    $metricSums = array();

    // assume the dataset has finished processing unless we encounter a missing value  
    $processingComplete = 'true';
    $rrisInError = array();
    $rrisComplete = array();
      
    foreach($resultsRunItems as $resultsRunItem)
      {
      // group the outputs by the test item
      $testItemName = $resultsRunItem->getTestItem()->getName();
      if(!array_key_exists($testItemName, $subjectScores))
        {
        $subjectScores[$testItemName] = array();  
        }
        $metricType = $resultsRunItem->getResultKey();
        if(!array_key_exists($metricType, $metricSums))
          {
          $metricSums[$metricType] = array('count' => 0, 'sum' => 0);  
          }
        // now deal with the result of this testitem based on its status
        if($resultsRunItem->getStatus() == MIDAS_CHALLENGE_RRI_STATUS_COMPLETE)
          {
          $metricScore = $resultsRunItem->getResultValue();
          $subjectScores[$testItemName][$metricType] = $metricScore;
          $metricSum = $metricSums[$metricType];
          $metricSum['count'] = $metricSum['count'] + 1;
          $metricSum['sum'] = $metricSum['sum'] + $metricScore;
          $metricSums[$metricType] = $metricSum;
          $rrisComplete[$testItemName][$metricType] = $resultsRunItem->getKey();
          }
        else
          {
          if($resultsRunItem->getStatus() == MIDAS_CHALLENGE_RRI_STATUS_QUEUED ||
             $resultsRunItem->getStatus() == MIDAS_CHALLENGE_RRI_STATUS_RUNNING ||
             $resultsRunItem->getStatus() == MIDAS_CHALLENGE_RRI_STATUS_UNKNOWN)
            {
            $subjectScores[$testItemName][$metricType] = $resultsRunItem->getStatus();
            $processingComplete = 'false';
            }
          elseif($resultsRunItem->getStatus() == MIDAS_CHALLENGE_RRI_STATUS_ERROR)
            {
            $subjectScores[$testItemName][$metricType] = $resultsRunItem->getStatus();
            $rrisInError[$testItemName][$metricType] = $resultsRunItem->getKey(); 
            }
          elseif($resultsRunItem->getStatus() == MIDAS_CHALLENGE_RRI_STATUS_STOPPED)
            {
            $subjectScores[$testItemName][$metricType] = $resultsRunItem->getStatus();
            }
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
    $metrics = array_keys($metricSums);
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
          $resultRow[$metric] = $scores[$metric];
          }
        }
      $resultRows[] = $resultRow;
      }
      
    $responseData = array('results_rows' => $resultRows, 'rris_in_error' => $rrisInError, 'processing_complete' => $processingComplete,
        'rris_complete' => $rrisComplete);
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

    $challengeId = $args['challengeId'];
    
    $challengeModel = MidasLoader::loadModel('Challenge', 'challenge');
    $resultsrunModel = MidasLoader::loadModel('ResultsRun', 'challenge');
    $resultsrunitemModel = MidasLoader::loadModel('ResultsRunItem', 'challenge');

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
      // THis will actually happen
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
      
    // get the number of items in the challenge's testing folder's truth folder
    // get all the items in the correct subfolder's Truth subfolder
    $dashboardDao = $challengeDao->getDashboard();
    $subfolder = $dashboardDao->getTesting();
    $folderModel = MidasLoader::loadModel('Folder');
    $truthFolder = $folderModel->getFolderExists(MIDAS_CHALLENGE_TRUTH, $subfolder);
    if(!$truthFolder)
      {
      throw new Zend_Exception('Cannot find truth folder under folderId['.$subfolder->getFolderId().']');
      }  
    $expectedResultCount = sizeof($truthFolder->getItems());  
      
    // now we can combine the ranks for each user for each metric with the metric scores
    // then average the ranks for an overal rank
    $usersToAverageRank = array();
    $usersAsterisks = array();
    $asterisk = false;
    foreach($metricResultsByUser as $user => $metricResults)
      {
      $rankSum = 0;
      $rankCount = 0;
      foreach($metricResults as $metric => $scores)
        {
        $rank = $userRanksByMetric[$user][$metric];
        // if they haven't submitted all the results, add an '*' to rank
        if($metricResultsByUser[$user][$metric]['result_count'] != $expectedResultCount)
          {
          $asterisk = true;
          $metricResultsByUser[$user][$metric]['rank'] = $rank . '*';
          }
        else
          {
          $metricResultsByUser[$user][$metric]['rank'] = $rank;
          }
        $rankSum = $rankSum + $rank;
        $rankCount = $rankCount + 1;
        }
      if($rankCount !== 0)
        {
        $rankAvg = $rankSum/$rankCount;
        $metricResultsByUser[$user]['Average Rank'] = array();
        $metricResultsByUser[$user]['Average Rank']['metric_average'] = round($rankAvg,3);
        $usersToAverageRank[$user] =  round($rankAvg,3);
        $usersAsterisks[$user] = $asterisk;
        }
      else
        {
        // this user doesn't have any results, create placeholders
        foreach($metrics as $metric)
          {
          // todo, should this be waiting or missing or what?
          // a constant either way
          $metricResultsByUser[$user][$metric]['metric_average'] = 'X';
          $metricResultsByUser[$user][$metric]['rank'] = 'X';
          }
          $metricResultsByUser[$user]['Average Rank'] = array();
          $metricResultsByUser[$user]['Average Rank']['metric_average'] = 'X';
          $metricResultsByUser[$userId]['Average Rank']['rank'] = 'X';
        }
      }
      
      
    // now sort by average rank to get overall rank  
    asort($usersToAverageRank, SORT_NUMERIC);
    $usersToOverallRank = array();
    $rank = 1;
    $rankCount = 1;
    $rankedUserSize = sizeof($usersToAverageRank);
    $rankedUsers = array_keys($usersToAverageRank);
    foreach($rankedUsers as $ind => $userId)
      {
      $avgRank = $usersToAverageRank[$userId];
      if(!array_key_exists($userId, $usersToOverallRank))
        {
        $usersToOverallRank[$userId] = $rank;
        }
      if($ind+1 < sizeof($usersToAverageRank))
        {
        // only need to look ahead for ties if there are more users  
        $currentAvgRank = $avgRank;
        $nextUser = $rankedUsers[$ind+1];
        $nextAvgRank = $usersToAverageRank[$nextUser];
        if($currentAvgRank === $nextAvgRank)
          {
          // a tie, should have the same rank
          // don't increment the rank, but do keep track of how many at this rank
          $rankCount = $rankCount + 1;
          $usersToOverallRank[$nextUser] = $rank;
          }
        else
          {
          // increment the rank by however many at this rank, reset rankCount
          $rank = $rank + $rankCount;
          $rankCount = 1;
          }
        }
      }
      
    foreach($usersToOverallRank as $userId => $overallRank)
      {
      if(array_key_exists($userId, $usersAsterisks) && $usersAsterisks[$userId])
        {
        $overallRank .= '*';
        }
      $metricResultsByUser[$userId]['Average Rank']['rank'] = $overallRank;
      }
      
    $returnVal = array('competitor_scores' => $metricResultsByUser);
    return $returnVal;
    }



} // end class
