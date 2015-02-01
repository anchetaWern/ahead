@section('content')
<div class="row">
  <div class="col-md-5">
  @include('partials.alert')
  </div>
</div>
<div class="row">
  <div class="col-md-5" id="newpost">
  @include('partials.post')
  </div>
</div>
@stop