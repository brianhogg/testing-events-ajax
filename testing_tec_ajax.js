(function($){
  $('#ecs-testing-events').click(() => {
    $.ajax({
      url: testing_fetch_events_object.ajaxurl,
      type: 'POST',
      data: {
        'action': 'testing_calendar_events'
      },
      success: function(data) {
        try {
          console.log($.parseJSON(data.posts));
        } catch (e) {
          console.log(e);
        }
      }
    });
  });
})(jQuery);