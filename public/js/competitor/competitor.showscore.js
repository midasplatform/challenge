var midas = midas || {};
midas.challenge = midas.challenge || {};
midas.challenge.competitor = midas.challenge.competitor || {};

midas.challenge.competitor.showscoreSetup = function () {
  if(json.challengeId) {
      var aniSpeed = 2000;   
      if(json.processingComplete !== 'true') { 
        $('div#midas_challenge_competitor_listScoreStatus').html("Calculation status: not complete. It will be automatically updated shortly.");
        $('div#midas_challenge_competitor_listScoreStatus').animate( { color: 'red' }, aniSpeed);
        }
      else {
        $('div#midas_challenge_competitor_listScoreStatus').html("Calculation status: complete."); 
        $('div#midas_challenge_competitor_listScoreStatus').animate( { color: 'green' }, aniSpeed);
        }  
      }
   
        
}




$(document).ready(function() 
  { 
    

    //midas.challenge.competitor.showscoreSetup();
    
    
    
    $('#scorelistingtable').tablesorter({
        sortList: [[2,0]] 
    });
    
    
    // Set up sortable table
    $('#tablesorter_scores').tablesorter({
        sortList: [[0,0]] 
    });


  });
 
