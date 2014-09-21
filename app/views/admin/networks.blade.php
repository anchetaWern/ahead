@section('content')
<div class="row">
  <div class="col-md-5">
  @include('partials.alert')
  </div>
</div>
<div class="row">
  <div class="col-md-5">
    <h3>Networks</h3>
    <ul id="networks">
      <li>
        <a href="/twitter/redirect" class="btn btn-info">
          <i class="fa fa-twitter"></i>
          Connect Twitter
        </a>
      </li>
      <li>
        <a href="/fb/redirect" class="btn btn-primary">
          <i class="fa fa-facebook"></i>
          Connect Facebook
        </a>
      </li>
      <li>
        <a href="/linkedin/redirect" class="btn btn-success">
          <i class="fa fa-linkedin"></i>
          Connect LinkedIn
        </a>
      </li>
    </ul>
  </div>

  <div class="col-md-7">
    @if($network_count > 0)
    <table class="table">
      <thead>
        <tr>
          <th>Type</th>
          <th>Username</th>
        </tr>
      </thead>
      <tbody>
      @foreach($networks as $n)
        <tr>
          <td>{{ $n->network }}</td>
          <td>{{ $n->username }}</td>
        </tr>
      @endforeach
      </tbody>
    </table>
    @else
    <div class="alert alert-info">
      You haven't connected any networks yet
    </div>
    @endif
  </div>
</div>
@stop