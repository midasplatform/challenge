var midas = midas || {};
midas.challenge = midas.challenge || {};
midas.challenge.competitor = midas.challenge.competitor || {};

midas.challenge.competitor.dashboardSetup = function () {

    $('th.midasChallengeMetricCol').unbind('click').click(function () {
        var id = $(this)[0].id;
        var metricId = (id.split('_'))[1];
        midas.loadDialog('metricDetails'+metricId, '/challenge/competitor/metricdetails?metricId='+metricId);
        midas.showDialog('Metric details', false);
    });
       
    $("div#challengeCompetitorScoreboardAbout a").qtip({
        content: {
            text: function(api) {
                var text = 'This page will display combined rankings of competitors for the stages of a challenge.<br/><br/>';
                text += 'For any user that has submitted results for a given stage, we find the average value ';
                text += 'for all metric+label combinations.  This is the top score reported in the table ';
                text += 'cell for each user for a metric+label.  The bottom score reported in the table ';
                text += 'cell for each user is the ranking of that average metric score among all the ';
                text += 'competitors, for that same metric+label.<br/><br/>The last column in the table holds an averaged ranking value ';
                text += 'for that competitor, across all of the metric+label columns, reported as the top score. ';
                text += "This is used to find the competitor's overall ranking, which is the bottom score in the last column. ";
                text += "The competitors are reported in descending order of best overall ranking, with the best ranking being 1.  If a competitor has an '*' next to their ranking, ";
                text += 'they did not submit results for each of the truth cases in this stage.<br/><br/>';
                text += 'Click on the metric+label column headers for more information about the metric.<br/><br/>';                
                text += 'Competitor IDs are anonymized.  If you are logged in as a submitting competitor, your ID will display as your email and your email link will take you to your score results page ';
                text += 'used to calculate these values.<br/><br/>';
                text += 'If you are a moderator of the challenge, then each of the competitor IDs will link to the score results page used for that competitor to calculate these values.';
                return text;
            }
        },
        style: {
	    classes: 'ui-tooltip-dark dashboardAboutStyle'
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

$(document).ready(function() 
  { 
    
    //$(".dashboardContent").show();
    
    midas.challenge.competitor.dashboardSetup();  
  });
 

