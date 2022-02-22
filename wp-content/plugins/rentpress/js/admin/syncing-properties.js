var rp_currently_sync_is_on=0;
var rp_the_prop_codes=[];

var rp_loading_cube='<div class="rp-sk-folding-cube">' +
    '<div class="rp-sk-cube1 rp-sk-cube"></div>' + 
    '<div class="rp-sk-cube2 rp-sk-cube"></div>' + 
    '<div class="rp-sk-cube4 rp-sk-cube"></div>' + 
    '<div class="rp-sk-cube3 rp-sk-cube"></div>' + 
'</div>';

(function($) {
    $(document).ready(function() {
        $('#rp-sync-properties').on('click', function() {

            $('.rp-syncing-respond').show();
            $('.rp-sync-ctas').hide();

            function nextProp() {
                var prop_code=rp_the_prop_codes[rp_currently_sync_is_on];

                if (typeof prop_code != 'undefined') {
                    $('.rp-syncing-respond .saythis').html(
                        'Refreshing account properties. This may take a while...<br>'+
                        'Syncing Property '+
                        ((rp_currently_sync_is_on+1)+' of '+rp_the_prop_codes.length)
                    );

                    $.ajax({
                        url : rentPressOptions.ajax_url, 
                        type : 'post',
                        data : {
                            action : 'resync_single_property_by_prop_code',
                            prop_code : prop_code,
                            current_post_type : "properties",
                        },
                        success : function( response ) {
                            rp_currently_sync_is_on++;
                            nextProp();
                        },
                        error: function(response) {
                            rp_currently_sync_is_on++;
                            nextProp();  
                        }
                    }); 

                }
                else {

                    rp_currently_sync_is_on=0;
                    $('.rp-syncing-respond').fadeOut();
                    $('.rp-sync-ctas').show();

                }
            }    

            $('.rp-syncing-respond').html(
                '<div style="text-align:center;background:white;padding:20px;"><span class="saythis">Fetching List Of Properties!</span>'
                +rp_loading_cube
            );

            $.ajax({
                url: rentPressOptions.ajax_url,
                typeof: 'post',
                dataType: 'json',
                data : {
                    action : 'fetch_property_codes',
                },
                success: function( response ) {
                    if (typeof response.error == 'object') {

                    $('.rp-syncing-respond')
                        .html(
                            '<div style="text-align:center;background:white;padding:20px;color:red;font-weight:bold;">'+
                            response.error.message+
                            '</div>'
                        )
                        .delay(2000)
                        .fadeOut();
                    }
                    else {

                        rp_the_prop_codes=response.ResponseData;

                        nextProp();

                    }
                },
                error: function (response) {

                    $('.rp-syncing-respond').html(
                        '<div style="text-align:center;background:white;padding:20px;color: red;">'+
                        '<b>An Error Has Happened... The status of your request was '+ response.status +'.</b>'+
                        '</div>'
                    );

                }
            });
        });

        $('#rp-sync-property-from-editing-page').on('click', function() {

            $('#rp-resync-activity-container').show();
                        
            $('#rp-resync-activity-container').html(
                '<div style="text-align:center;background:white;padding:20px;">Resyncing property. This may take a while...'+
                rp_loading_cube
            );

            $.ajax({
                url : rentPressOptions.ajax_url, 
                type : 'post',
                data : {
                    // Calls to callback method in Plugin.php - refresh_account_properties_callback()
                    action : 'resync_single_property',
                    property_post_id : $(this).data('property-post-id'),
                    current_post_type : $(this).data('post-type')
                },
                success : function( response ) {
                    $('#rp-resync-activity-container')
                        .html(response)
                        .delay(2000)
                        .fadeOut();

                    location.reload();
                },
                erorr: function (response) {
                    $('#rp-resync-activity-container').html(
                        '<div style="text-align:center;background:white;padding:20px;color: red;">'+
                        '<b>An Error Has Happened... The status of your request was '+ response.status +'.</b>'+
                        '</div>'
                    );
                }
            }); 
        });        
    });
})(jQuery);
