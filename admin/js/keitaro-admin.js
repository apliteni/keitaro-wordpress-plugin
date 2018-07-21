(function( $ ) {
    'use strict';

    function initMainTab() {
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
          var name = el.attr('name').replace(/keitaro_settings\[(.*?)\]/gi, '$1');
          if (json[name]) {
            el.val(json[name]);
          }
        });

        $('#keitaro-import-box').hide();
        $('#keitaro-import-button').hide();
        $('#keitaro-import-settings').show();
        $('#keitaro-import-success').show();
      });
    }

    function initPagesTab() {
      var specifyPagesSwitch = $('input[id*="[specify_pages]"]');

      specifyPagesSwitch.change(function(){
        togglePagesVisibility(specifyPagesSwitch.filter(':checked').val() === 'yes')
      });

      togglePagesVisibility(specifyPagesSwitch.filter(':checked').val()  === 'yes');

      $('select[name*="pages"]').change(function(){
        toggleCustomTokenVisibility($(this), this.value);
      });

      $('select[name*="pages"]').each(function(){
        toggleCustomTokenVisibility($(this), this.value);
      });
    }

    function toggleCustomTokenVisibility(el, value) {
      var field = el.siblings('input');
      if (value !== 'primary_campaign' && value !== '') {
        field.show().attr('disabled', false);
      } else {
        field.hide().attr('disabled', true);
      }
    }

    function togglePagesVisibility(state) {
      var pagesRow = $('.keitaro-pages').closest('tr');
      if (state) {
        pagesRow.removeClass('keitaro—disabled');
      } else {
        pagesRow.addClass('keitaro—disabled');
      }
    }


    $(document).ready(function() {
        initMainTab();
        initPagesTab();
    });

})( jQuery );
