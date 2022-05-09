var backgroundColors = [
    'rgba(255, 99, 132, 0.2)',
    'rgba(54, 162, 235, 0.2)',
    'rgba(255, 206, 86, 0.2)',
    'rgba(75, 192, 192, 0.2)',
    'rgba(153, 102, 255, 0.2)',
    'rgba(255, 159, 64, 0.2)'
];
var borderColors = [
    'rgba(255, 99, 132, 1)',
    'rgba(54, 162, 235, 1)',
    'rgba(255, 206, 86, 1)',
    'rgba(75, 192, 192, 1)',
    'rgba(153, 102, 255, 1)',
    'rgba(255, 159, 64, 1)'
];
const colorsCount = 5;

var transactionNotes = [];
var transactionObjs = [];
var campaignNames = [];
var transactionsCountForCampaign = [];
var myChart = null;

var toPlot = { };
var txnCount = 0;



function refreshPlot(before, after, limit = 100000, next ='string')
{
     //start spinner
     $('#spinner2').show();

    // var urlOld = "https://algoindexer.testnet.algoexplorerapi.io/v2/transactions?limit="+limit+"&asset-id=67967557&before-time=" + before + "&after-time=" + after;

	// url nuovo, rand lab
	//
	// eliminato parametro limit , che da questa API non viene gestito come voluto
	//
	var url = "https://indexer.testnet.algoexplorerapi.io/v2/assets/67967557/transactions?before-time=" + before + "&after-time=" + after;

    if (next != 'string')
    {
        url += '&next='+next;
    }

    console.log("Calling api @url: "+url);
    
    $.get(url).done(function (data) {

        data["transactions"].forEach((element) => {
            if (element.note != undefined)
            {
                try
                {
                    var obj = JSON.parse(atob(element['note']));
                    var name = obj['CampaignName'];
                }
                catch(e)
                {
                    var name = 'empty note' //transaction without a json note
                }

                if (name in toPlot) 
                    toPlot[name]++;
                else 
                    toPlot[name] = 1;

                txnCount++;
            }
        });


        //stop spinner
        $('#spinner2').hide();

        if (data['next-token'] != 'string' && data['next-token'] != undefined)
        {
            //alert('Missing data on the graph! '+data['next-token']);
            //next call with token
            refreshPlot(before, after, limit, data['next-token'])
        }
        else 
        {
            //plot chart
            plotChart(Object.keys(toPlot), Object.values(toPlot));
            console.log(toPlot);
            $('#totTxn').html(txnCount);
        }

        
    }).fail(function(jqXHR, textStatus, errorThrown) 
    {
        //handle error here
        alert('Error getting graph data!');
         
        //plot chart
        plotChart(Object.keys(toPlot), Object.values(toPlot));
        console.log(toPlot);
        $('#totTxn').html(txnCount);
    });
}

function plotChart(names, datas, divId = '#myChart') {
    const ctx = $(divId);
    var backColors = [];
    var bColors = [];
    for(let i = 0; i < datas.length; i++)
    {
        backColors[i] = backgroundColors[i%colorsCount];
        bColors[i] = borderColors[i%colorsCount];
    }
    myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: names,
            datasets: [{
                label: '# of transactions',
                data: datas,
                backgroundColor: backColors,
                borderColor : bColors,
                borderWidth : 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

}

function refreshPlotForPublisher()
{
    var url = './scriptsPHP/publisherGraph.php?uid=15';
    $.get(url).done(function (data) {
        if (data['success'])
            plotChart(data['metrics']['names'],data['metrics']['values'],'#myChart2');
    });
    
}





$(document).ready( function () 
{
    //initial graph dates
    var nowDateString = new Date().toISOString();
    var dateOffset = (1 * 60 * 60 * 1000) * 1; //1 hours 
    var startDate = new Date();
    startDate.setTime(startDate.getTime() - dateOffset);
    var startDateString = startDate.toISOString();
    
    //set initial dates in fields
    var fromString = startDateString.replace("T"," ");
    fromString = fromString.substring(0, fromString.lastIndexOf(":"));
    var toString = nowDateString.replace("T"," ");
    toString = toString.substring(0,toString.lastIndexOf(":"));

    // const p = toString.lastIndexOf(" ")
    // var toHourString = toString.split(" ")[1];

    //setup datepicker
    $('#fromField').datetimepicker({
        format:'Y-m-d H:i',
        theme:'dark',
    });

    $('#toField').datetimepicker({
        format:'Y-m-d H:i',
        theme:'dark',
        maxDate : toString//,
        //maxTime : toHourString
    });


      //form submit
      $( "#graphForm" ).submit(function( event ) {
        event.preventDefault();
         //disable button
         //$('#btnUpdate').prop("disabled",true);
       
        //get form fields
        var from = $('#fromField').val();    
        var to = $('#toField').val();    

        //convert date to iso string
        from = from.replace(" ", "T");
        from = from.concat(":00.000Z");
        to = to.replace(" ", "T");
        to = to.concat(":00.000Z");

       myChart.destroy();
       toPlot = { };
       txnCount = 0;
       $('#totTxn').html(txnCount);

        
       refreshPlot(to, from);
    });


    

    $('#fromField').val(fromString);
    $('#toField').val(toString); 


    refreshPlot(nowDateString, startDateString);
    refreshPlotForPublisher();


});
