(function ($, Drupal, drupalSettings) {
  'use strict';


  // Logic for minimizing the chatbot.
  $(document).ready(() => {

    let chatStatus = 'true';
    if (drupalSettings.localgov_copilot.toggle_state == 'remember') {
      chatStatus = localStorage.getItem("copilot_livechat.closed");
    }
    else if (drupalSettings.localgov_copilot.toggle_state == 'open') {
      chatStatus = 'false';
    }
    if (chatStatus == 'false') {
      $('#live-chat .chat').show();
    }
    $('#live-chat header').click(function() {
      $('.chat').toggle(function () {
        localStorage.setItem("copilot_livechat.closed", localStorage.getItem("copilot_livechat.closed") == 'true' ? 'false' : 'true');
        $(this).animate({
          display: 'block',
        }, 100);
      })
    });
  });


})(jQuery, Drupal, drupalSettings);


