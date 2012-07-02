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
      midas.challenge.competitor.updateResultsTable(resultsRows);
      if(processingComplete !== 'true') 
        { 
        var t = setTimeout(midas.challenge.competitor.updateResults, delayMillis);
        }
      },
      error: function() {}
    });
  }



$(document).ready(function() 
  { 
    
    // Set up sortable table
    $('#tablesorter_scores').tablesorter({
        sortList: [[0,0]] 
    });

    // start looping for updates
    var t = setTimeout(midas.challenge.competitor.updateResults, delayMillis);
  });
 
 
 
// Fill the results table with any data
midas.challenge.competitor.updateResultsTable = function(jsonResults)  {
    $('#tablesorter_scores').hide();
    $('.resultsLoading').show();
    
    // remove existing rows
    $('#tablesorter_scores .resultsRow').remove();

    var i = 0;
    var columns = [
        'Subject', 
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
        'Specificity(A_2, B_2)'];
    $.each(jsonResults, function(index, value) {
        
        i++;
        var stripeClass = i % 2 ? 'odd' : 'even';
        var html='';
        html+='<tr class="resultsRow '+stripeClass+'">';
        $.each(columns, function (col_index, column)  {
            html+=' <td>'+value[column]+'</td>';
        });
        html+='</tr>';
        $('#tablesorter_scores').append(html);
    });

    $('#tablesorter_scores').show();
    $('.resultsLoading').hide();
    $('#tablesorter_scores').trigger('update');
}