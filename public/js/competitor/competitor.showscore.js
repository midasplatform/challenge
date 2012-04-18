var midas = midas || {};
midas.challenge = midas.challenge || {};
midas.challenge.competitor = midas.challenge.competitor || {};

$(document).ready(function() 
  { 
    
    $("table.scoreDisplay").dataTable(
      {
      "bPaginate": true,
      "bLengthChange": true,
      "bFilter": true,
      "bSort": true,
      "bInfo": true,
      "bAutoWidth": false,
      "aaSorting": [ [3,'desc'], [0,'asc'] ]
      }
    ); 
        
    $(".scoreContent").show();
    
    if(json.challengeId)
      {
      if(json.processingComplete !== 'true') 
        { 
        $('div#midas_challenge_competitor_listScoreStatus').html("Calculation status: not complete.");
        }
      else
        {
        $('div#midas_challenge_competitor_listScoreStatus').html("Calculation status: complete.");  
        }
      }
      
    $(".scoreHeading").click(function(){
    $(this).next(".scoreContent").slideToggle(100);
     })
     
    .toggle(
      function() 
        {
        $(this).children("span").text("+");
        }, 
      function()
        {
        $(this).children("span").text("-");
        }
     );      

  });
 
