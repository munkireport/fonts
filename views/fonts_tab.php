<div id="fonts-tab"></div>
<h2 data-i18n="fonts.clienttab"></h2>

<div id="fonts-msg" data-i18n="listing.loading" class="col-lg-12 text-center"></div>

<script>
$(document).on('appReady', function(){
   $.getJSON(appUrl + '/module/fonts/get_data/' + serialNumber, function(data){

        // Check if we have data
        if( data.length == 0 ){
            $('#fonts-msg').text(i18n.t('no_data'));
            $('#fonts-cnt').text('')
        } else {
            // Hide loading message
            $('#fonts-msg').text('');
            // Set count of fonts
            $('#fonts-cnt').text(data.length);
            var skipThese = ['id','serial_number','type_name'];
            $.each(data, function(i,d){

                // Generate rows from data
                var rows = ''
                for (var prop in d){
                    // Skip skipThese
                    if(skipThese.indexOf(prop) == -1){
                        if((prop == 'enabled' || prop == 'copy_protected' || prop == 'duplicate' || prop == 'embeddable' || prop == 'type_enabled' || prop == 'outline' || prop == 'valid') && d[prop] == 1){
                           rows = rows + '<tr><th>'+i18n.t('fonts.'+prop)+'</th><td>'+i18n.t('yes')+'</td></tr>';
                        }
                        else if((prop == 'enabled' || prop == 'copy_protected' || prop == 'duplicate' || prop == 'embeddable' || prop == 'type_enabled' || prop == 'outline' || prop == 'valid') && d[prop] == 0){

                           rows = rows + '<tr><th>'+i18n.t('fonts.'+prop)+'</th><td>'+i18n.t('no')+'</td></tr>';
                        }
                        else if(d[prop] == ""){
                           // Blank out empty rows
                        }
                        else {
                            rows = rows + '<tr><th>'+i18n.t('fonts.'+prop)+'</th><td>'+d[prop]+'</td></tr>';
                        }
                    }
                }
                $('#fonts-tab')
                    .append($('<h4>')
                        .append($('<i>')
                            .addClass('fa fa-font'))
                        .append(' '+d.type_name))
                    .append($('<div style="max-width:650px;">')
                        .addClass('table-responsive')
                        .append($('<table>')
                            .addClass('table table-striped table-condensed')
                            .append($('<tbody>')
                                .append(rows))))
            })
        }
   });
});
</script>
