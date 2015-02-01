var alertmodal_template = Handlebars.compile($('#alertmodal-template').html());
var current_event;

function viewPost(id){

  $.post(
    '/post/' + id,
    function(response){

      $('#post_id').val(id);
      $('#content').val(response.post.content);

      $('.network').prop('checked', false);

      var selected_network_count = response.selected_networks.length;
      for(var x = 0; x < selected_network_count; x++){
        $('.network[data-id=' + response.selected_networks[x] + ']').prop('checked', true);
      }

      $('#btn-schedule').data('posturl', '/post/update');
      if(response.post.published){
        $('#btn-schedule').prop('disabled', true);
      }

      $('#alertmodal-container').html('');
      $('#post_modal .modal-title').text('Update Post');
      $('#post_modal #btn-schedule').text('Update');
      $('#schedule-container').addClass('hid');
      $('#post_modal').modal('show');

    }
  );

}

function updatePost(callback){
  var id = $('#post_id').val();
  var content = $('#content').val();
  var post_networks = [];
  $('input[name="network[]"]:checked').each(function(){
    post_networks.push($(this).val());
  });
  var schedule = $('input[name="schedule"]:checked').val();
  var schedule_value = $('#schedule_value').val();

  var post_url = $('#btn-schedule').data('posturl');

  $.post(
    post_url,
    {
      'ajax': '1',
      'id': id,
      'content': content,
      'network': post_networks,
      'schedule': schedule,
      'schedule_value': schedule_value
    },
    function(response){
      callback(response);
      $('#alertmodal-container').html(alertmodal_template(response));
    }
  );
}