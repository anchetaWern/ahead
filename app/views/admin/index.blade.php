@section('content')
<div class="row">
  <div class="col-lg-12">
    <h1 class="page-header">
      Hi {{ Auth::user()->username }}!
    </h1>
  </div>
</div>
@stop