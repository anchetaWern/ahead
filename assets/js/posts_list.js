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

$('.edit-post').click(function(e){
  e.preventDefault();
  e.stopPropagation();
  viewPost($(this).data('id'));
});

$('#btn-schedule').click(function(e){
  e.preventDefault();
  var self = $(this);
  if(self.parents('.modal').length){

    updatePost(function(response){
      $('.post-content[data-id=' + response.post.id + ']').text(response.post.content);
    });

  }else{
    $('#form_newpost').submit();
  }
});