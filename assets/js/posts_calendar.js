var calendar = $('#posts-calendar').fullCalendar({
  header: {
    left: 'prev,next today',
    center: 'title',
    right: 'month,agendaWeek,agendaDay'
  },
  slotDuration: '00:15:00',
  editable: false,
  eventLimit: true,
  defaultView: 'agendaWeek',
  selectable: true,
  eventSources: [
    {
      url: '/posts/calendar',
      type: 'POST'
    }
  ],
  select: function(start, end, jsEvent, view){

    var datetime = moment(start).format('MM/DD/YYYY HH:mm A');

    $('#post_id').val('');
    $('#content').val('').text('');
    $('#alertmodal-container').html('');
    $('#schedule').attr('checked', true);
    $('#schedule_value').val(datetime);
    $('.datetimepicker').data('DateTimePicker').setDate(datetime);
    $('#btn-schedule').data('posturl', '/post/create').prop('disabled', false);

    $('#post_modal .modal-title').text('Schedule New Post');
    $('#post_modal #btn-schedule').text('Schedule');
    $('#schedule-container').removeClass('hid');
    $('#post_modal').modal('show');

  },
  eventClick: function(calEvent, jsEvent, view){

    current_event = calEvent;
    viewPost(calEvent.id);
  }
});


$('#btn-schedule').click(function(e){
  e.preventDefault();
  var self = $(this);
  if(self.parents('.modal').length){

    updatePost(function(response){
      current_event.title = response.post.title;
      calendar.fullCalendar('updateEvent', current_event);
    });

  }else{
    $('#form_newpost').submit();
  }
});