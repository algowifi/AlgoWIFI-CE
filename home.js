$(document).ready(function () {

    refreshMetrics();

});


function refreshMetrics()
{
    $.ajax({
        url: "./scriptsPHP/metrics.php"
    }).then(function(data) 
    {
        if (data['success'] == 1)
        {
            $('#metricsTitle').html("Metrics");
            
            if (data['metrics']['numAdmin'] != undefined)
            {
                $('#numAdmin').html(data['metrics']['numAdmin']);
            }
            if (data['metrics']['numPublisher'] != undefined)
            {
                $('#numPublisher').html(data['metrics']['numPublisher']);
            }
            if (data['metrics']['numHotspotter'] != undefined)
            {
                $('#numHotspotter').html(data['metrics']['numHotspotter']);
            }
            if (data['metrics']['numLocation'] != undefined)
            {
                $('#numLocation').html(data['metrics']['numLocation']);
            }
            if (data['metrics']['numTotUsers'] != undefined)
            {
                $('#numTotUsers').html(data['metrics']['numTotUsers']);
            }

            if (data['metrics']['numHotspots'] != undefined)
            {
                $('#numHotspots').html(data['metrics']['numHotspots']);
                $('#numHotspots2').html(data['metrics']['numHotspots']);
            }

            if (data['metrics']['numEnabledCampaigns'] != undefined)
            {
                $('#numEnabledCampaigns').html(data['metrics']['numEnabledCampaigns']);
            }
            if (data['metrics']['numDisabledCampaigns'] != undefined)
            {
                $('#numDisabledCampaigns').html(data['metrics']['numDisabledCampaigns']);
            }
            if (data['metrics']['numTotCampaigns'] != undefined)
            {
                $('#numTotCampaigns').html(data['metrics']['numTotCampaigns']);
            }

            if (data['metrics']['totViews'] != undefined)
            {
                $('#totViews').html(data['metrics']['totViews']);
            }
            if (data['metrics']['receivedMicroAWIFI'] != undefined)
            {
                $('#receivedAWIFI').html((data['metrics']['receivedMicroAWIFI']/10000).toFixed(4));
            }
            if (data['metrics']['spentMicroAWIFI'] != undefined)
            {
                $('#spentAWIFI').html((data['metrics']['spentMicroAWIFI']/10000).toFixed(4));
            }



        }
        else
        {
            $('#metricsTitle').html("Metrics failed to load");
        }
        //$('#nftInfoTextarea').val(JSON.stringify(data, null, 2));
        //$('#ownerAddress').val(data['owner']);
               
        $('#spinner').hide();
    });
}


