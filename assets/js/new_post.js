$('#btn-postnow').click(function(e){
  e.preventDefault();
  $('#post_now').val('1');
  $('#form_newpost').submit();
});