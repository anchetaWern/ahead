$('#posts-calendar').fullCalendar({
  header: {
    left: 'prev,next today',
    center: 'title',
    right: 'month,agendaWeek,agendaDay'
  },
  slotDuration: '00:15:00',
  editable: true,
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

    var datetime = moment(start).format('MM-DD-YYYY HH:mm:ss');
    window.location.href = '/post/new/' + datetime;
  }
});