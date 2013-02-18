var midas = midas || {};
midas.challenge = midas.challenge || {};
midas.challenge.competitor = midas.challenge.competitor || {};


var delayMillis = 10000;

midas.challenge.competitor.updateResults = function()
  {
  ajaxWebApi.ajax(
    {
    method: 'midas.challenge.competitor.list.results',  
    args: 'resultsRunId=' + json.resultsRunId,
    success: function(results) 
      {
      var processingComplete = results.data.processing_complete;
      var resultsRows = results.data.results_rows;
      var rrisInError = results.data.rris_in_error;
      var rrisComplete = results.data.rris_complete;
      midas.challenge.competitor.updateResultsTable(resultsRows, rrisInError, rrisComplete);
      if(processingComplete !== 'true') 
        { 
        var t = setTimeout(midas.challenge.competitor.updateResults, delayMillis);
        }
      else {
          $('.resultsLoading').hide();
          $('.resultsComplete').show();
        }  
      },
      error: function() {}
    });
  }



$(document).ready(function() 
  { 
    midas.challenge.competitor.setupJobDetailsDisplay();

    // start looping for updates
    var t = setTimeout(midas.challenge.competitor.updateResults, 0);
  });
 
midas.challenge.competitor.setupJobDetailsDisplay = function() {
    $('td.midasChallengeError').unbind('click').click(function () {
        if(!json.global.logged) {
            midas.showOrHideDynamicBar('login');
            midas.loadAjaxDynamicBar('login','/user/login');
            return;
        }
        var id = $(this)[0].id;
        var rriid = (id.split('_'))[3];
        midas.loadDialog('errorDetails'+rriid, '/challenge/competitor/jobdetails?rriid='+rriid);
        midas.showDialog('Error details', false);
    });
    $('td.midasChallengeComplete').unbind('click').click(function () {
        if(!json.global.logged) {
            midas.showOrHideDynamicBar('login');
            midas.loadAjaxDynamicBar('login','/user/login');
            return;
        }
        var id = $(this)[0].id;
        var rriid = (id.split('_'))[3];
        midas.loadDialog('jobDetails'+rriid, '/challenge/competitor/jobdetails?rriid='+rriid);
        midas.showDialog('Job details', false);
    });
} 
 
// Fill the results table with any data
midas.challenge.competitor.updateResultsTable = function(jsonResults, rrisInError, rrisComplete)  {
    $('#tablesorter_scores').hide();
    
    // remove existing rows
    $('#tablesorter_scores .resultsRow').remove();

    var i = 0;
    $.each(jsonResults, function(index, value) {
        
        i++;
        var stripeClass = i % 2 ? 'odd' : 'even';
        var html='';
        html+='<tr class="resultsRow '+stripeClass+'">';
        $.each(json.tableHeaders, function (col_index, column)  {
            if(value[column] === undefined) {
                var colVal = json.unknownStatus;
                html+=' <td>'+colVal+'</td>';
            }
            else if(value[column] === 'waiting') {
                var colVal = 'waiting';
                html+=' <td class="midasChallengeWaiting">'+colVal+'</td>';
            }
            else if(value[column] === 'queued') {
                var colVal = 'queued';
                html+=' <td class="midasChallengeQueued">'+colVal+'</td>';
            }
            else if(value[column] === 'running') {
                var colVal = 'running';
                html+=' <td class="midasChallengeRunning">'+colVal+'</td>';
            }
            else if(value[column] === 'error') {
                var metric = column;
                var truthItemName = value['Subject'];
                var colVal = 'error';
                if(truthItemName in rrisInError && metric in rrisInError[truthItemName]) {
                    var rriId = rrisInError[truthItemName][metric];
                    html+=' <td class="midasChallengeError" id="error_cell_rriid_'+rriId+'">'+colVal+'</td>';
                }
                else {
                    html+=' <td>'+colVal+'</td>';
                }
            }
            else {
                var metric = column;
                var truthItemName = value['Subject'];
                var colVal = value[metric];
                if(truthItemName in rrisComplete && metric in rrisComplete[truthItemName]) {
                    var rriId = rrisComplete[truthItemName][metric];
                    html+=' <td class="midasChallengeComplete" id="job_cell_rriid_'+rriId+'">'+colVal+'</td>';
                }
                else {
                    html+=' <td>'+colVal+'</td>';
                }
            }
        });
        html+='</tr>';
        $('#tablesorter_scores').append(html);
    });

    $('#tablesorter_scores').show();
    $('#tablesorter_scores').trigger('update');
    midas.challenge.competitor.setupJobDetailsDisplay();

}