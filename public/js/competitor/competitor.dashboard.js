var midas = midas || {};
midas.challenge = midas.challenge || {};
midas.challenge.competitor = midas.challenge.competitor || {};

midas.challenge.competitor.dashboardSetup = function (){
  
  $("div.dashboardHeading").qtip({
          content: 'Click to show or hide scores for this challenge!',
          show: 'mouseover',
          hide: 'mouseout',
          position: {
                target: 'mouse',
                my: 'bottom left',
                viewport: $(window), // Keep the qtip on-screen at all times
                effect: true // Disable positioning animation
             }
         }).click(function(){
             $(this).next(".dashboardContent").slideToggle(100);
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
}

$(document).ready(function() 
  { console.log("blara");
    /*$("table.dashboardDisplay").dataTable(
      {
      "bPaginate": true,
      "bLengthChange": true,
      "bFilter": true,
      "bSort": true,
      "bInfo": true,
      "bAutoWidth": false,
      "aaSorting": [ [1,'desc'], [0,'asc'] ]
      }
    );*/
 $("#challenge_dashboard").tablesorter({});
    
    $(".dashboardContent").show();
    
    midas.challenge.competitor.dashboardSetup();  
  });
 

