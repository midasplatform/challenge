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
      $('.resultsLoading').hide();
      var processingComplete = results.data.processing_complete;
      var resultsRows = results.data.results_rows;
      var rrisInError = results.data.rris_in_error;
      var rrisComplete = results.data.rris_complete;
      midas.challenge.competitor.updateResultsTable(resultsRows, rrisInError, rrisComplete);
      if(processingComplete !== 'true') 
        { 
        var t = setTimeout(midas.challenge.competitor.updateResults, delayMillis);
        $('.resultsProcessing').show();
        }
      else
        {
        $('.resultsProcessing').hide();
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
        midas.loadDialog('errorDetails'+rriid, '/challenge/competitor/jobdetails?rriid='+rriid+'&error=true');
        midas.showDialog('Job details', false);
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
    $('th.midasChallengeMetricCol').unbind('click').click(function () {
        var id = $(this)[0].id;
        var metricId = (id.split('_'))[1];
        midas.loadDialog('metricDetails'+metricId, '/challenge/competitor/metricdetails?metricId='+metricId);
        midas.showDialog('Metric details', false);
    });
    
    
    $("div#challengeCompetitorScoreAbout a").qtip({
        content: {
            text: function(api) {
                var text = 'This page will display the scores for an individual submission by a competitor, for a specific stage of a challenge.<br/><br/>';
                text += 'An alert box will indicate whether the results sumitted for scoring are still processing or have completed processing. ';
                text += 'When processing completes, the submitting user will be alerted via email.<br/><br/>';
                text += 'The score table will list the input cases for this dataset that are matched between the truth items and the competitor submitted result items.  The name of the case ';
                text += 'is listed under the Subject column.<br/><br/>';
                text += 'Each of the other columns will be a metric+label.  The cells in that column will be the scores for that case for that metric+label.  You can click on the ';
                text += 'metric+label column headers for more information about the metric.  You can click on the individual table cells once the case has been scored for the given metric, this ';
                text += 'will show you the actual command line output of the metric executable, or error information if the executable run was in error.<br/><br/>';
                text += 'The top row will be an averaged value of the scores of all cases for each metric+label column.<br/><br/>';
                text += 'If any of the distance metrics return Infinity, this value will be represented as an arbitrarily large value, and the column average will be this same arbitrarily large value.'
                return text;
            }
        },
        style: {
	    classes: 'ui-tooltip-dark scoreAboutStyle'
	},				
        position: {
	    my: 'top left',
	    at: 'bottom left'
	},
	show: {
	    event: 'click'
	},
        hide: {
            event: 'click unfocus'
        }
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