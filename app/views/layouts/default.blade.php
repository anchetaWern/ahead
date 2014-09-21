<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<link rel="stylesheet" href="{{ asset('assets/lib/lumen/css/bootstrap.css') }}">
	<link rel="stylesheet" href="{{ asset('assets/css/default.css') }}">
	<title>{{ $title }}</title>
</head>
<body>

  <div class="navbar navbar-default navbar-static-top" role="navigation">
    <div class="container">
      <div class="navbar-header">
        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".navbar-collapse">
          <span class="sr-only">Toggle navigation</span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="#">{{ Config::get('app.title') }}</a>
      </div>
      <div class="navbar-collapse collapse">
        <ul class="nav navbar-nav">
          <li class="active"><a href="/">Home</a></li>
          <li><a href="/login">Login</a></li>
          <li><a href="/register">Register</a></li>
        </ul>
      </div>
    </div>
  </div>


  <div class="container">
   @yield('content')
  </div>


	<script src="{{ asset('assets/js/jquery.js') }}"></script>
	<script src="{{ asset('assets/lib/lumen/js/bootstrap.js') }}"></script>
</body>
</html>
