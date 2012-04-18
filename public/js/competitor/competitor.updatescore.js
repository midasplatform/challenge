var midas = midas || {};
midas.challenge = midas.challenge || {};
midas.challenge.competitor = midas.challenge.competitor || {};


var delayMillis = 10000;

var updateResults = function()
  {
  ajaxWebApi.ajax(
    {
    method: 'midas.challenge.competitor.list.results',  
    //args: 'challengeId=' + json.challengeId + '&resultsFolderId=' + json.resultsFolderId + '&outputFolderId=' + json.outputFolderId,
    args: 'challengeId=' + 1 + '&resultsFolderId=' + 549 + '&outputFolderId=' + 543,
    success: function(results) 
      {
      var processingComplete = results.data.processing_complete;
      var resultsRows = results.data.results_rows;
      updateResultsTable(resultsRows);
      if(processingComplete !== 'true') 
        { 
        $('div#midas_challenge_competitor_listScoreStatus').html("Calculation status: not complete.");
        var t = setTimeout(updateResults, delayMillis);
        }
      else
        {
        $('div#midas_challenge_competitor_listScoreStatus').html("Calculation status: complete.");  
        }
      },
      error: function() {}
    });
  }

$(document).ready(function() {
    // check for new results after a delay
    var t = setTimeout(updateResults, delayMillis);
});



// Fill the results table with any data
function updateResultsTable(jsonResults)  {
    $('table.scoreDisplay').hide();
    $('.resultsLoading').show();
    
    // remove existing rows
    $('table.scoreDisplay .resultsRow').remove();

    var i = 0;
    $.each(jsonResults, function(index, value) {
        
        i++;
        var stripeClass = i % 2 ? 'odd' : 'even';
        var html='';
        html+='<tr class="resultsRow '+stripeClass+'">';
        html+=' <td>'+value.test_item_name+'</td>';
        html+=' <td>'+value.output_item_name+'</td>';
        html+=' <td>'+value.metric_item_name+'</td>';
        html+=' <td>'+value.score+'</td>';
        html+=' <td>'+value.output_item_name+'</td>';
        html+='</tr>';
        $('table.scoreDisplay').append(html);
    });

    $('table.scoreDisplay').show();
    $('.resultsLoading').hide();
}