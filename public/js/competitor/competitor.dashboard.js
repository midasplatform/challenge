var midas = midas || {};
midas.challenge = midas.challenge || {};
midas.challenge.competitor = midas.challenge.competitor || {};

$(document).ready(function() 
  { 
    $("table.dashboardDisplay").dataTable(
      {
      "bPaginate": true,
      "bLengthChange": true,
      "bFilter": true,
      "bSort": true,
      "bInfo": true,
      "bAutoWidth": false,
      "aaSorting": [ [1,'desc'], [0,'asc'] ]
      }
    ); 
    
    $(".dashboardContent").hide();
      
    $(".dashboardHeading").click(function(){
    $(this).next(".dashboardContent").slideToggle(100);
     })
     
    .toggle(
      function() 
        {
        $(this).children("span").text("-");
        }, 
      function()
        {
        $(this).children("span").text("+");
        }
     );  

  });
 

