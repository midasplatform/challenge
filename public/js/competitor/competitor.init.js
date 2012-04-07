var midas = midas || {};
midas.challenge = midas.challenge || {};
midas.challenge.competitor = midas.challenge.competitor || {};

var currentBrowser = false;

$(document).ready(function()
{
  // Initialize Smart Wizard
  $('#wizard').smartWizard(
  {
  // Properties
    keyNavigation: true, // Enable/Disable key navigation(left and right keys are used if enabled)
    enableAllSteps: false,  // Enable/Disable all steps on first load
    transitionEffect: 'fade', // Effect on navigation, none/fade/slide/slideleft
    contentURL:null, // specifying content url enables ajax content loading
    contentCache:false, // cache step contents, if false content is fetched always from ajax url
    cycleSteps: false, // cycle step navigation
    enableFinishButton: false, // makes finish button enabled always
    errorSteps:[],    // array of step numbers to highlighting as error steps
    labelNext:'Next', // label for Next button
    labelPrevious:'Previous', // label for Previous button
    labelFinish:'Get score',  // label for Finish button
    // Events
    onLeaveStep: onLeaveStepCallback, // triggers when leaving a step
    onShowStep: onShowStepCallback,  // triggers when showing a step
    onFinish: onFinishCallback  // triggers when Finish button is clicked
  });

  $('#uploadContentBlock').load(json.global.webroot+'/upload/simpleupload');
  $('#wizard').show();

  if($('#selectedExecutableId').val() != '')
    {
    executableValid = true;
    isExecutableMeta = true;
    }
});

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
     
    ajaxWebApi.ajax(
    {
      method: 'midas.challenge.competitor.score.results.folder',  
      args: 'challengeId=' + challengeId + '&resultsFolderId=' + resultsFolderId + '&outputFolderId=' + outputFolderId,
      success: function(results) 
      {
          createNotice("TO DO: score result dashboard!", 4000); 
      }
    })
  }
  else
   {
     createNotice("There are some errors.", 4000, 'error');
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
      createNotice("Please select the Midas folder where the results files are uploaded", 4000, 'error');
      isStepValid = false;
    }
    else if($('#midas_challenge_competitor_validatedResultsFolder').html() == '')
    {
      createNotice("Please verify your selected folder to check if there exist missing items!", 4000, 'error');
      isStepValid = false;
    }
    else if($('#midas_challenge_competitor_validatedResultsFolder').html() == 'Failed')
    {
      createNotice("The selected results folder doesn't contain all the required items. Please select another folder or fix the current one!", 4000, 'error');
      isStepValid = false;
    }
  }

  if(stepnumber == 3)
  {
    if($('#midas_challenge_competitor_selectedOutputFolderId').val() == '')
    {
      createNotice("Please select the Midas folder where output files will be stored", 4000, 'error');
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
  
  if(step_num == 2)
  {
    $('#midas_challenge_competitor_browseResultsFolder').click(function(){
      loadDialog("selectfolder_resultsfolder","/challenge/competitor/selectresultsfolder");
      showDialog('Browse Results Folder');
      currentBrowser = 'folderresults';
    });
    
    $('#midas_challenge_competitor_checkResultsFolder').click(function(){
      var challengeId =  $('#midas_challenge_competitor_selectedChallengeId').val();
      var folderId = $('#midas_challenge_competitor_selectedResultsFolderId').val();

      ajaxWebApi.ajax(
      {
        method: 'midas.challenge.competitor.validate.results.folder',  
        args: 'challengeId=' + challengeId + '&folderId=' + folderId ,
        success: function(results) {
    
          if( results.data.testingWithoutResults.length < 1 )
          {
            createNotice("The selected results folder is valid!", 4000);
            $('#midas_challenge_competitor_validatedResultsFolder').html('Passed');  
          }
          else 
          {
            createNotice("Sorry, the selected results folder is not valid! ", 4000, 'error');
            $('#midas_challenge_competitor_validatedResultsFolder').html('Failed');
            var validationInfo = '<br/> <b>The mismatched items: </b> <br/> </br>';
            validationInfo += '<table id="validationInfo" border="1" width="75%" cellpadding="1" cellspacing="0">';
            validationInfo += '<tr> <th align="center">What are required by the challenge</th> <th align="center">What are in your results folder</th></tr> <tr>';
            for (var idx in results.data.testingWithoutResults)
            {
              validationInfo += '<td align="center"> <span>' + results.data.testingWithoutResults[idx]+ '</span> </td>';
              validationInfo += '<td align="center"><img src="' + json.global.webroot + '/core/public/images/icons/nok.png"> </td>'; 
            }
            validationInfo +='</tr>';
            for (var idx in results.data.resultsWithoutTesting)
              {
                validationInfo += '<td align="center"><img src="' + json.global.webroot + '/core/public/images/icons/nok.png"> </td>';
                validationInfo += '<td align="center"> <span>' + results.data.resultsWithoutTesting[idx] + '</span> </td>';
              }
            validationInfo += '</tr> </table>';
            $('div#midas_challenge_competitor_validatedResultsFolder_Info').html(validationInfo);
          }
        }
     })
    });  
  }
  
  if(step_num == 3)
  {
    $('#midas_challenge_competitor_browseOutputFolder').click(function(){
      loadDialog("selectfolder_outputfolder","/challenge/competitor/selectoutputfolder");
      showDialog('Browse Output Folder');
      currentBrowser = 'folderoutput';
    });
  } 

}

function folderSelectionCallback(name, id)
{
  if(currentBrowser == 'folderresults')
  {
    $('#midas_challenge_competitor_selectedResultsFolder').html(name);
    $('#midas_challenge_competitor_selectedResultsFolderId').val(id);
    return;
  }
  if(currentBrowser == 'folderoutput')
  {
    $('#midas_challenge_competitor_selectedOutputFolder').html(name);
    $('#midas_challenge_competitor_selectedOutputFolderId').val(id);
    return;
  }
}
  