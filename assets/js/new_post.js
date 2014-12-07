$('#btn-postnow').click(function(e){
  e.preventDefault();
  $('#post_now').val('1');
  $('#form_newpost').submit();
});

$('.datetimepicker').datetimepicker({
icons: {
    time: "fa fa-clock-o",
    date: "fa fa-calendar",
    up: "fa fa-arrow-up",
    down: "fa fa-arrow-down"
}
});