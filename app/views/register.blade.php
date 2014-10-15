@section('content')
<div class="row">
  <div class="col-md-5">
  @include('partials.alert')
  </div>
</div>
<div class="row">
  <div class="col-md-5">
    <form class="form-horizontal" method="POST" action="/register">
      <fieldset>
        <legend>Sign Up</legend>
        <div class="form-group">
          <label for="username" class="col-lg-2 control-label">Name</label>
          <div class="col-lg-10">
            <input type="text" class="form-control" id="username" name="username" value="{{ Input::old('username') }}">
          </div>
        </div>
        <div class="form-group">
          <label for="email" class="col-lg-2 control-label">Email</label>
          <div class="col-lg-10">
            <input type="email" class="form-control" id="email" name="email" value="{{ Input::old('email') }}">
          </div>
        </div>
        <div class="form-group">
          <label for="password" class="col-lg-2 control-label">Password</label>
          <div class="col-lg-10">
            <input type="password" class="form-control" id="password" name="password">
          </div>
        </div>
        <div class="form-group">
          <div class="col-lg-10 col-lg-offset-2">
            <button type="submit" class="btn btn-primary">Sign Up</button>
          </div>
        </div>
      </fieldset>
    </form>
  </div>
  <div class="col-md-5">
    <ul id="social-login">
      <li>
        <a href="/twitter/redirect" class="btn btn-info btn-block"><i class="fa fa-twitter"></i> Signup with Twitter</a>
      </li>
      <li>
        <a href="/fb/redirect" class="btn btn-primary btn-block"><i class="fa fa-facebook"></i> Signup with Facebook</a>
      </li>
      <li>
        <a href="/linkedin/redirect" class="btn btn-default btn-block"><i class="fa fa-linkedin"></i> Signup with LinkedIn</a>
      </li>
    </ul>
  </div>
</div>
@stop