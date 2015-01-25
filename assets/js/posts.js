var postnetworks_template = Handlebars.compile($('#postnetworks-template').html());

$('.post').click(function(){
  var self = $(this);
  var id = self.data('id');
  if(id){
    $.post(
      '/post/networks',
      {
        'id': id
      },
      function(response){
        self.find('.postnetworks').html(postnetworks_template({'postnetworks' : response})).slideToggle();
        self.data('id', 0);
      }
    );
  }else{
    self.find('.postnetworks').slideToggle();
  }
});