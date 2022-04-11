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




var nowDateString = new Date().toISOString();
var dateOffset = (24 * 60 * 60 * 1000) * 7; //7 days
var startDate = new Date();
startDate.setTime(startDate.getTime() - dateOffset);
var startDateString = startDate.toISOString();

var url = "https://algoindexer.testnet.algoexplorerapi.io/v2/transactions?limit=100000&asset-id=67967557&before-time=" + nowDateString + "&after-time=" + startDateString;
$.get(url).done(function (data) {
    //alert(data["current-round"]);
    data["transactions"].forEach((element) => {
        if (element.note != undefined)
            transactionNotes.push(atob(element['note']))
    });

    //read transaction note as json and build objects array
    transactionNotes.forEach((element) => {
        var obj = JSON.parse(element);
        transactionObjs.push(obj);
        let name = obj['CampaignName'];
        if (!campaignNames.includes(name)) {
            campaignNames.push(obj['CampaignName'])
        }
    });

    //count transactions for campaigns
    campaignNames.forEach((name) => {
        let transactionsForThisName = transactionObjs.filter(function (item) { return item["CampaignName"] === name; });
        transactionsCountForCampaign.push(transactionsForThisName.length);
    });

    plotChart(campaignNames, transactionsCountForCampaign);
});

function plotChart(names, datas) {
    const ctx = $('#myChart');
    var backColors = [];
    var bColors = [];
    for(let i = 0; i < datas.length; i++)
    {
        backColors[i] = backgroundColors[i%colorsCount];
        bColors[i] = borderColors[i%colorsCount];
    }
    const myChart = new Chart(ctx, {
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