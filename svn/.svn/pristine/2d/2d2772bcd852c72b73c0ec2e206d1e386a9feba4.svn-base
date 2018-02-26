(function( $ ) {
    'use strict';
    $(document).ready(function() {
        $('#keitaro-import-settings').click(function() {
            $('#keitaro-import-settings').hide();
            $('#keitaro-import-box').show();
            $('#keitaro-import-button').show();
            $('#keitaro-import-success').hide();
            return false;
        });

        $('#keitaro-import-button').click(function(){
            try {
                var json = JSON.parse($('#keitaro-import-box').val());
            } catch (e) {
                alert(e.message);
                return;
			}

            if (!json || !json.tracker_url) {
                alert('Incorrect settings!');
                return;
            }
            $('#keitaro-settings-form input').each(function(){
                var el = $(this);
                var name = el.attr('name').replace(/keitaro_settings\[(.*?)\]/si, '$1')
                if (json[name]) {
                    el.val(json[name]);
                }
            });
            console.log(json);

            $('#keitaro-import-box').hide();
            $('#keitaro-import-button').hide();
            $('#keitaro-import-settings').show();
            $('#keitaro-import-success').show();
        });
    });

})( jQuery );
