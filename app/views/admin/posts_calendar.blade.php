@section('content')
<div class="row">
  <div class="col-md-12">
    <h3>Posts</h3>
    <div id="posts-calendar"></div>
  </div>
</div>

<div class="modal fade" id="newpost_modal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Schedule New Post</h4>
      </div>
      <div class="modal-body">
        @include('partials.new_post')
      </div>
    </div>
  </div>
</div>
@include('partials.alertmodal')
@stop