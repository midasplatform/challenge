var midas = midas || {};
midas.challenge = midas.challenge || {};
midas.challenge.competitor = midas.challenge.competitor || {};


var delayMillis = 10000;

midas.challenge.competitor.updateResults = function()
  {
  ajaxWebApi.ajax(
    {
    method: 'midas.challenge.competitor.list.results',  
    args: 'challengeId=' + json.challengeId,
    success: function(results) 
      {
      var processingComplete = results.data.processing_complete;
      var resultsRows = results.data.results_rows;
      midas.challenge.competitor.updateResultsTable(resultsRows);
      var aniSpeed = 2000;
      if(processingComplete !== 'true') 
        { 
        $('div#midas_challenge_competitor_listScoreStatus').html("Calculation status: not complete. It will be automatically updated shortly.");
        $('div#midas_challenge_competitor_listScoreStatus').animate( { color: 'red' }, aniSpeed);
        var t = setTimeout(midas.challenge.competitor.updateResults, delayMillis);
        }
      else
        {
        $('div#midas_challenge_competitor_listScoreStatus').html("Calculation status: complete.");
        $('div#midas_challenge_competitor_listScoreStatus').animate( { color: 'green' }, aniSpeed);
        }
      },
      error: function() {}
    });
  }

$(document).ready(function() {
    // check for new results after a delay
    var t = setTimeout(midas.challenge.competitor.updateResults, delayMillis);
});



// Fill the results table with any data
midas.challenge.competitor.updateResultsTable = function(jsonResults)  {
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