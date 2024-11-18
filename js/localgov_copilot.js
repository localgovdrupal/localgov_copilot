(function ($, Drupal, drupalSettings) {
  'use strict';


  // Logic for minimizing the chatbot.
  $(document).ready(() => {

    const chat_widget = $('#live-chat');

    const iframe = $('<iframe>');
    iframe.attr('src', drupalSettings.localgov_copilot.frame_url);

    let chatStatus = 'false';
    if (drupalSettings.localgov_copilot.toggle_state == 'remember') {
      chatStatus = localStorage.getItem("copilot_livechat.open");
    }
    else if (drupalSettings.localgov_copilot.toggle_state == 'open') {
      chatStatus = 'true';
    }
    if (chatStatus === 'true') {
      chat_widget.addClass('expanded');
      $("button", chat_widget).attr('aria-expanded', 'true');
      $(".chat", chat_widget).append(iframe);
    }

    $("button", chat_widget).click(function() {
      chat_widget.toggleClass('expanded');
      const expanded = chat_widget.hasClass('expanded') ? "true" : "false";

      if (expanded === "true" && !$("iframe", chat_widget).length) {
        $(".chat", chat_widget).append(iframe);
      }

      $(this).attr("aria-expanded", expanded);
      localStorage.setItem("copilot_livechat.open", expanded);
    });
  });


})(jQuery, Drupal, drupalSettings);


