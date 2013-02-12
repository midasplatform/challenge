var midas = midas || {};
midas.challenge = midas.challenge || {};
midas.challenge.competitor = midas.challenge.competitor || {};

midas.challenge.competitor.currentBrowser = false;




$(document).ready(function()  {
    midas.challenge.competitor.disableScoring();
    // validate folder if it is supplied
    midas.challenge.competitor.updateUISelection();
    
    // event handler on results folder browse button
    $('#midas_challenge_competitor_browseResultsFolder').click(function() {
        midas.loadDialog("selectfolder_resultsfolder","/challenge/competitor/selectresultsfolder");
        midas.showDialog('Browse for Results Submission Folder');
        midas.challenge.competitor.currentBrowser = 'folderresults';
    });
    
    // event handler on challenge selection combo   
    $('#midas_challenge_competitor_challengeId').change(function() {
        midas.challenge.competitor.updateSelectedChallenge();
    });
    
    // event handler on resultsType selection combo   
    $('#midas_challenge_competitor_resultsType').change(function() {
        midas.challenge.competitor.updateUISelection();
    });
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
};




midas.challenge.competitor.disableScoring = function() {
    $('#midas_challenge_competitor_scoreResults_anchor').unbind('click');
    $('#midas_challenge_competitor_scoreResults_anchor').removeClass('buttonScoreSubmission').addClass('buttonScoreSubmissionDisabled');
};

midas.challenge.competitor.enableScoring = function() {
    $('#midas_challenge_competitor_scoreResults_anchor').removeClass('buttonScoreSubmissionDisabled').addClass('buttonScoreSubmission');
    $('#midas_challenge_competitor_scoreResults_anchor').unbind('click').click(function() { 
        var challenge_id = $('#midas_challenge_competitor_challengeId').val();
        var results_type = $('#midas_challenge_competitor_resultsType').val();
        var submission_folder_id = $('#midas_challenge_competitor_selectedResultsFolderId').val();
        $('#midas_challenge_competitor_scoreResults_anchor').removeClass('buttonScoreSubmission').addClass('buttonScoreSubmissionDisabled');
        $('#midas_challenge_competitor_scoreResults_anchor').text("Generating Batch Jobs");
        $('#generatingJobs').show();
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
};


midas.challenge.competitor.updateSelectedChallenge = function() {
    var challenge_id = $('#midas_challenge_competitor_challengeId').val();
    ajaxWebApi.ajax({
        method: 'midas.challenge.competitor.get.challenge.status',
        args: 'challengeId=' + challenge_id,
        success: function(results) {
            var stages = {'training_status' : 'Training', 'testing_status' : 'Testing'};
            $.each(stages, function(stage_status, option_value) {
                if(stage_status in results.data && results.data[stage_status] == 'open') {
                    if($("#midas_challenge_competitor_resultsType option[value='"+option_value+"']").length == 0) {
                        $('#midas_challenge_competitor_resultsType').append($("<option/>", { value: option_value, text: option_value }));
                    }
                }
                else {
                    if($("#midas_challenge_competitor_resultsType option[value='"+option_value+"']").length > 0) {
                        $("#midas_challenge_competitor_resultsType option[value='"+option_value+"']").remove();
                    }
                }
            });
            midas.challenge.competitor.updateUISelection();
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            console.log(testStatus, errorThrown);
        }
    });
};


midas.challenge.competitor.updateUISelection = function() {
    var challenge_id = $('#midas_challenge_competitor_challengeId').val();
    var results_type = $('#midas_challenge_competitor_resultsType').val();
    var submission_folder_id = $('#midas_challenge_competitor_selectedResultsFolderId').val();
    if(submission_folder_id !== null && submission_folder_id !== undefined & submission_folder_id !== '') {
        midas.challenge.competitor.validateResultsFolder(challenge_id, submission_folder_id, results_type);          
    }
};

midas.challenge.competitor.validateResultsFolder = function(challengeId, resultsFolderId, resultsType) {
    $('.validateResultsFolder').hide();   
    $('#midas_challenge_competitor_matchedItems_Info').hide();
  ajaxWebApi.ajax(
    {
    method: 'midas.challenge.competitor.validate.results',  
    args: 'challengeId=' + challengeId + '&resultsFolderId=' + resultsFolderId  + '&resultsType=' + resultsType,
    success: function(results) {
      var validationInfo = '';
      var matchedItemsInfo = '';
      
      if( $.isArray(results.data.truthWithoutResults) ) {
          //  it is either an empty array (initialarray), or an object collection
          if($.isArray(results.data.matchedTruthResults) &&
             results.data.matchedTruthResults.length === 0 &&
             results.data.truthWithoutResults.length === 0) {
              // there are no truths to match
              $('#validateResultsFolder_NoneMatched').show();
              midas.challenge.competitor.disableScoring();
          }
          else {
              $('#validateResultsFolder_AllMatched').show(); 
              $('#midas_challenge_competitor_matchedItems_Info').show();
              midas.challenge.competitor.enableScoring();
          }
      }
      else 
        {
        if($.isArray(results.data.matchedTruthResults)) {
            $('#validateResultsFolder_NoneMatched').show();
            midas.challenge.competitor.disableScoring();
        }
        else {
            $('#validateResultsFolder_SomeMatched').show();
            $('#midas_challenge_competitor_matchedItems_Info').show();
            midas.challenge.competitor.enableScoring();
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
      $('#validateResultsFolder_Error').show();
      var validationInfo = '';
      validationInfo = '<br/> <b>Reason: </b>' + XMLHttpRequest.message + '<br/> </br>';
      $('div#midas_challenge_competitor_validatedResultsFolder_Info').html(validationInfo);
      }  
    });
    return;
};