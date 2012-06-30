var midas = midas || {};
midas.challenge = midas.challenge || {};
midas.challenge.competitor = midas.challenge.competitor || {};

midas.challenge.competitor.currentBrowser = false;

$(document).ready(function()  {
    //$('#midas_challenge_competitor_scoreResults_anchor').click(function() { return false; });
    // TODO this styling isn't working, do a better job with style
    // validate folder if it is supplied
    var challenge_id = $('#midas_challenge_competitor_challengeId').val();
    var results_type = $('#midas_challenge_competitor_resultsType').val();
    var submission_folder_id = $('#midas_challenge_competitor_selectedResultsFolderId').val();
    if(submission_folder_id !== null && submission_folder_id !== undefined & submission_folder_id !== '') {
        midas.challenge.competitor.validateResultsFolder(challenge_id, submission_folder_id, results_type);          
    }
    
    // event handler on results folder browse button
    $('#midas_challenge_competitor_browseResultsFolder').click(function() {
        midas.loadDialog("selectfolder_resultsfolder","/challenge/competitor/selectresultsfolder");
        midas.showDialog('Browse for Results Submission Folder');
        midas.challenge.competitor.currentBrowser = 'folderresults';
    });
    
    // TODO event handlers for challenge and dataset selection
});


function folderSelectionCallback(name, id)
  {
  var challengeId =  $('#midas_challenge_competitor_challengeId').val();
  if(midas.challenge.competitor.currentBrowser == 'folderresults')
    {
    $('#midas_challenge_competitor_selectedResultsFolder').html(name);
    $('#midas_challenge_competitor_selectedResultsFolderId').val(id);
    $('#validateResultsFolder_Pass').hide();
    $('#validateResultsFolder_Fail').hide();
    var resultsType = $('#midas_challenge_competitor_resultsType').val();
    if(challengeId != '' && id != '')
      {
      midas.challenge.competitor.validateResultsFolder(challengeId, id, resultsType);
      }
    return;
    }
}

midas.challenge.competitor.enableScoring = function() {
    $('#midas_challenge_competitor_scoreResults_anchor').removeClass('buttonScoreSubmissionDisabled').addClass('buttonScoreSubmission');
    $('#midas_challenge_competitor_scoreResults_anchor').click(function() { 
        var challenge_id = $('#midas_challenge_competitor_challengeId').val();
        var results_type = $('#midas_challenge_competitor_resultsType').val();
        var submission_folder_id = $('#midas_challenge_competitor_selectedResultsFolderId').val();
        ajaxWebApi.ajax({
            method: 'midas.challenge.competitor.score.results',  
            args: 'challengeId=' + challenge_id + '&resultsFolderId=' + submission_folder_id + '&resultsType=' + results_type,
            success: function(results) {
                window.location.replace($('.webroot').val() + '/challenge/competitor/showscore?resultsRunId=' + results.data.challenge_results_run_id); 
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                var validationInfo = '';
                validationInfo = '<br/> <b>Scoring</b> action has an error: ' + XMLHttpRequest.message + '<br/> </br>';
                $('div#midas_challenge_competitor_getScore_Info').html(validationInfo);  
            }
        })
    });
}


midas.challenge.competitor.validateResultsFolder = function(challengeId, resultsFolderId, resultsType)
  {  
  ajaxWebApi.ajax(
    {
    method: 'midas.challenge.competitor.validate.results',  
    args: 'challengeId=' + challengeId + '&resultsFolderId=' + resultsFolderId  + '&resultsType=' + resultsType,
    success: function(results) {
      var validationInfo = '';
      var matchedItemsInfo = '';
      if( $.isArray(results.data.truthWithoutResults) ) //  it is either an empty array (initialarray), or an object collection
        {
        //midas.createNotice("The selected results folder is valid!", 4000);  
        $('#validateResultsFolder_AllMatched').show();   
        midas.challenge.competitor.enableScoring();

        }
      else 
        {
        //midas.createNotice("Sorry, the selected results folder is not valid! ", 4000, 'error');
        if($.isArray(results.data.matchedTruthResults)) {
            $('#validateResultsFolder_NoneMatched').show();
        }
        else {
            $('#validateResultsFolder_SomeMatched').show();
            midas.challenge.competitor.enableScoring();
    //        $('#midas_challenge_competitor_scoreResults_anchor').removeClass('buttonScoreSubmissionDisabled').addClass('buttonScoreSubmission');
    //$('#midas_challenge_competitor_scoreResults_anchor').click(function() { return false; });


        }
        validationInfo = '<br/> <b>Mismatched items: </b> <br/> </br>';
        validationInfo += '<table id="validationInfo" class="validation">';
        validationInfo += '<tr> <th>What is required by the challenge</th> <th>What is in your results folder</th></tr>';
        for (var idx in results.data.truthWithoutResults)
          {
          validationInfo += '<tr> <td> <span>' + results.data.truthWithoutResults[idx]+ '</span> </td>';
          validationInfo += '<td><img src="' + json.global.webroot + '/core/public/images/icons/nok.png"> </td> </tr>'; 
          }
        for (var idx in results.data.resultsWithoutTruth)
          {
          validationInfo += '<tr> <td><img src="' + json.global.webroot + '/core/public/images/icons/nok.png"> </td>';
          validationInfo += '<td> <span>' + results.data.resultsWithoutTruth[idx] + '</span> </td> </tr>';
          }
        validationInfo += '</table>';
        }
      $('div#midas_challenge_competitor_validatedResultsFolder_Info').html(validationInfo);
      
      if( !$.isArray(results.data.matchedTruthResults) ) // it is either an empty array (initialarray), or an object collectionn
        {
        matchedItemsInfo = '<br/> <b>Matched items: </b> <br/> </br>';
        matchedItemsInfo += '<table id="matchedItemsInfo" class="validation">';
        matchedItemsInfo += '<tr> <th>What is required by the challenge</th> <th>If it is in your results folder</th></tr>';
        for (var idx in results.data.matchedTruthResults)
          {
          matchedItemsInfo += '<tr> <td> <span>' + results.data.matchedTruthResults[idx]+ '</span> </td>';
          matchedItemsInfo += '<td><img src="' + json.global.webroot + '/core/public/images/icons/ok.png"> </td> </tr>'; 
          }
        matchedItemsInfo += '</table>';
        }
        $('div#midas_challenge_competitor_matchedItems_Info').html(matchedItemsInfo);
      },
    error: function(XMLHttpRequest, textStatus, errorThrown){
      $('#validateResultsFolder_Fail').show();
      var validationInfo = '';
      validationInfo = '<br/> <b>Reason: </b>' + XMLHttpRequest.message + '<br/> </br>';
      $('div#midas_challenge_competitor_validatedResultsFolder_Info').html(validationInfo);
      }  
    });
    return;
  }
/*
function onLeaveStepCallback(obj)
  {
  var step_num= obj.attr('rel'); // get the current step number
  return validateSteps(step_num); // return false to stay on step and true to continue navigation
  }

function onFinishCallback()
  {
  if(validateAllSteps())
    {       
    var challengeId = $('#midas_challenge_competitor_selectedChallengeId').val();
    var resultsFolderId = $('#midas_challenge_competitor_selectedResultsFolderId').val();
    var outputFolderId = $('#midas_challenge_competitor_selectedOutputFolderId').val();
    var resultsType = $('#midas_challenge_competitor_resultsType').val();
 
    ajaxWebApi.ajax(
      {
      method: 'midas.challenge.competitor.score.results',  
      args: 'challengeId=' + challengeId + '&resultsFolderId=' + resultsFolderId + '&outputFolderId=' + outputFolderId  + '&resultsType=' + resultsType,
      success: function() 
        {
        window.location.replace($('.webroot').val() + '/challenge/competitor/showscore?challengeId=' + challengeId +'&resultsType=' + resultsType); 
        },
      error: function(XMLHttpRequest, textStatus, errorThrown)
        {
        var validationInfo = '';
        validationInfo = '<br/> Oops, <b>Get Score</b> action has an error: ' + XMLHttpRequest.message + '<br/> </br>';
        $('div#midas_challenge_competitor_getScore_Info').html(validationInfo);  
        }
      })
    }
  else
    {
   // midas.createNotice("There are some errors.", 4000, 'error');
    }
  }

function validateSteps(stepnumber)
  {
  var isStepValid = true;

  if(stepnumber == 1)
    {    
    if($('#midas_challenge_competitor_selectedChallengeId').val() == '')
      {
      createNotive("Please select a challenge to participate in", 4000, 'error');
      isStepValid = false;
      }
    }

  if(stepnumber == 2)
    {
    if($('#midas_challenge_competitor_selectedResultsFolderId').val() == '')
      {
      midas.createNotice("Please select the Midas folder where the results files are uploaded", 4000, 'error');
      isStepValid = false;
      }
    else if($('#midas_challenge_competitor_validatedResultsFolder').html() == 'Failed')
      {
      midas.createNotice("The selected results folder doesn't contain all the required items. Please select another folder or fix the current one!", 5000, 'error');
      isStepValid = false;
      }
    }

  if(stepnumber == 3)
    {
    if($('#midas_challenge_competitor_selectedOutputFolderId').val() == '')
      {
      midas.createNotice("Please select the Midas folder where output files will be stored", 4000, 'error');
      isStepValid = false;
      }
    else if($('#midas_challenge_competitor_validatedOutputFolder').html() == 'Failed')
      {
      midas.createNotice("The selected output folder is not valid. Please select another folder or fix the current one!", 5000, 'error');
      isStepValid = false;
      }
    }
    
  if(isStepValid)
    {
    $('#wizard').smartWizard('setError',{stepnum:stepnumber,iserror:false});
    }
  else
    {
    $('#wizard').smartWizard('setError',{stepnum:stepnumber,iserror:true});
    }
  
  return isStepValid;
  }

function validateAllSteps()
  {
  return validateSteps(1) && validateSteps(2) && validateSteps(3) && validateSteps(4);
  }

function onShowStepCallback(obj)
  {
  var step_num = obj.attr('rel'); // get the current step number
  
  if(step_num == 1) {
      var challenge_id = $('#midas_challenge_competitor_challengeId').val();
      var results_type = $('#midas_challenge_competitor_resultsType').val();
      var submission_folder_id = $('#midas_challenge_competitor_selectedResultsFolderId').val();
      _validateResultsFolder(challenge_id, submission_folder_id, results_type)
  }
  
  
  
  if(step_num == 2)
    {
    $('#midas_challenge_competitor_browseResultsFolder').click(function(){
      midas.loadDialog("selectfolder_resultsfolder","/challenge/competitor/selectresultsfolder");
      midas.showDialog('Browse Results Folder');
      currentBrowser = 'folderresults';
      });
    }
  
  if(step_num == 3)
    {
    $('#midas_challenge_competitor_browseOutputFolder').click(function(){
      midas.loadDialog("selectfolder_outputfolder","/challenge/competitor/selectoutputfolder");
      midas.showDialog('Browse Output Folder');
      currentBrowser = 'folderoutput';
      });
    } 
  }

function _validateResultsFolder(challengeId, resultsFolderId, resultsType)
  {  
  ajaxWebApi.ajax(
    {
    method: 'midas.challenge.competitor.validate.results',  
    args: 'challengeId=' + challengeId + '&resultsFolderId=' + resultsFolderId  + '&resultsType=' + resultsType,
    success: function(results) {
      var validationInfo = '';
      var matchedItemsInfo = '';
      if( $.isArray(results.data.truthWithoutResults) ) //  it is either an empty array (initialarray), or an object collection
        {
        midas.createNotice("The selected results folder is valid!", 4000);  
        $('#validateResultsFolder_Pass').show();   
        }
      else 
        {
        midas.createNotice("Sorry, the selected results folder is not valid! ", 4000, 'error');
        $('#validateResultsFolder_Fail').show();
        validationInfo = '<br/> <b>Mismatched items: </b> <br/> </br>';
        validationInfo += '<table id="validationInfo" class="validation">';
        validationInfo += '<tr> <th>What is required by the challenge</th> <th>What is in your results folder</th></tr>';
        for (var idx in results.data.truthWithoutResults)
          {
          validationInfo += '<tr> <td> <span>' + results.data.truthWithoutResults[idx]+ '</span> </td>';
          validationInfo += '<td><img src="' + json.global.webroot + '/core/public/images/icons/nok.png"> </td> </tr>'; 
          }
        for (var idx in results.data.resultsWithoutTruth)
          {
          validationInfo += '<tr> <td><img src="' + json.global.webroot + '/core/public/images/icons/nok.png"> </td>';
          validationInfo += '<td> <span>' + results.data.resultsWithoutTruth[idx] + '</span> </td> </tr>';
          }
        validationInfo += '</table>';
        }
      $('div#midas_challenge_competitor_validatedResultsFolder_Info').html(validationInfo);
      
      if( !$.isArray(results.data.matchedTruthResults) ) // it is either an empty array (initialarray), or an object collectionn
        {
        matchedItemsInfo = '<br/> <b>Matched items: </b> <br/> </br>';
        matchedItemsInfo += '<table id="matchedItemsInfo" class="validation">';
        matchedItemsInfo += '<tr> <th>What is required by the challenge</th> <th>If it is in your results folder</th></tr>';
        for (var idx in results.data.matchedTruthResults)
          {
          matchedItemsInfo += '<tr> <td> <span>' + results.data.matchedTruthResults[idx]+ '</span> </td>';
          matchedItemsInfo += '<td><img src="' + json.global.webroot + '/core/public/images/icons/ok.png"> </td> </tr>'; 
          }
        matchedItemsInfo += '</table>';
        }
        $('div#midas_challenge_competitor_matchedItems_Info').html(matchedItemsInfo);
      },
    error: function(XMLHttpRequest, textStatus, errorThrown){
      $('#validateResultsFolder_Fail').show();
      var validationInfo = '';
      validationInfo = '<br/> <b>Reason: </b>' + XMLHttpRequest.message + '<br/> </br>';
      $('div#midas_challenge_competitor_validatedResultsFolder_Info').html(validationInfo);
      }  
    });
    return;
  }
  
function _validateOutputFolder(challengeId, outputFolderId)
  {  
  ajaxWebApi.ajax(
    {
    method: 'midas.challenge.competitor.validate.output',  
    args: 'challengeId=' + challengeId + '&outputFolderId=' + outputFolderId ,
    success: function(results) {     
      if( results.data.valid === "true" )
        {
        midas.createNotice("The selected output folder is valid!", 4000);
        $('#validateOutputFolder_Pass').show();  
        }
      else 
        {
        midas.createNotice("Sorry, the selected output folder is not valid! ", 4000, 'error');
        $('#validateOutputFolder_Fail').show();
        }
      },
    error: function(XMLHttpRequest, textStatus, errorThrown){
      $('#validateOutputFolder_Fail').show();
      var validationInfo = '';
      validationInfo = '<br/> <b>Reason: </b>' + XMLHttpRequest.message + '<br/> </br>';
      $('div#midas_challenge_competitor_validatedOutputFolder_Info').html(validationInfo);
      }      
      
    });
    return;
  }  
  
function folderSelectionCallback(name, id)
  {
  var challengeId =  $('#midas_challenge_competitor_selectedChallengeId').val();
  if(currentBrowser == 'folderresults')
    {
    $('#midas_challenge_competitor_selectedResultsFolder').html(name);
    $('#midas_challenge_competitor_selectedResultsFolderId').val(id);
    $('#validateResultsFolder_Pass').hide();
    $('#validateResultsFolder_Fail').hide();
    var resultsType = $('#midas_challenge_competitor_resultsType').val();
    if(challengeId != '' && id != '')
      {
      _validateResultsFolder(challengeId, id, resultsType);
      }
    return;
    }
  if(currentBrowser == 'folderoutput')
    {
    $('#midas_challenge_competitor_selectedOutputFolder').html(name);
    $('#midas_challenge_competitor_selectedOutputFolderId').val(id);
    $('#validateOutputFolder_Pass').hide();
    $('#validateOutputFolder_Fail').hide();
    if(challengeId != '' && id != '')
      {
      _validateOutputFolder(challengeId, id);
      }
    return;
    }
}
  
*/