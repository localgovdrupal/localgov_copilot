(function ($, Drupal, drupalSettings) {
  'use strict';


  // Logic for minimizing the chatbot.
  $(document).ready(() => {

    let chatStatus = 'false';
    if (drupalSettings.localgov_copilot.toggle_state == 'remember') {
      chatStatus = localStorage.getItem("copilot_livechat.open");
    }
    else if (drupalSettings.localgov_copilot.toggle_state == 'open') {
      chatStatus = 'true';
    }
    if (chatStatus == 'true') {
      $('#live-chat #copilot-livechat').show();
    }
    $('#live-chat button').click(function() {

      const chat_widget = $('#live-chat');

      chat_widget.toggleClass('expanded');
      const expanded = chat_widget.class('expanded');
      localStorage.setItem("copilot_livechat.open", expanded);
      $(this).attr("aria-expanded", expanded);
    });
  });


})(jQuery, Drupal, drupalSettings);


