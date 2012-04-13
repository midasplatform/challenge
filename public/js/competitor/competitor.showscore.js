var midas = midas || {};
midas.challenge = midas.challenge || {};
midas.challenge.competitor = midas.challenge.competitor || {};

$(document).ready(function() { 
    
    $("#scoreResults").dataTable(
      {
      //"sScrollY": "200px",
      //"bFilter": true,
      //"bPaginate": false,
      //"bSort": true,
      //"bInfo": true,
      "aaSorting": [ [3,'asc'], [1,'asc'] ]
      }
    );  

 } );
 
/* 
function onClickShowScores($challenge)
   {
   document.getElementById($challenge).style.display="table";
   } 
   */

