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
  ]
});