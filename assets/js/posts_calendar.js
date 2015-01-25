var alertmodal_template = Handlebars.compile($('#alertmodal-template').html());

$('#posts-calendar').fullCalendar({
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

    $('#content').val('').text('');
    $('#alertmodal-container').html('');
    $('#schedule').attr('checked', true);
    $('#schedule_value').val(datetime);
    $('.datetimepicker').data('DateTimePicker').setDate(datetime);
    $('#newpost_modal').modal('show');
  }
});


$('#btn-schedule').click(function(e){
  e.preventDefault();
  if($(this).parents('.modal').length){

    var content = $('#content').val();
    var post_networks = [];
    $('input[name="network[]"]:checked').each(function(){
      post_networks.push($(this).val());
    });
    var schedule = $('input[name="schedule"]:checked').val();
    var schedule_value = $('#schedule_value').val();
    $.post(
      '/post/create',
      {
        'ajax': '1',
        'content': content,
        'network': post_networks,
        'schedule': schedule,
        'schedule_value': schedule_value
      },
      function(response){
        $('#alertmodal-container').html(alertmodal_template(response));
      }
    );
  }else{
    $('#form_newpost').submit();
  }

});