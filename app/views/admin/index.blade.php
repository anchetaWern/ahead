@section('content')
<div class="row">
  <div class="col-md-6">
  @include('partials.alert')
  </div>
</div>
<div class="row">
  <div class="col-lg-12">
    <h1 class="page-header">
      Hi {{ Auth::user()->username }}!
    </h1>
  </div>
</div>
<div class="row">
  <div class="col-lg-12">
    <a href="javascript:(function(){
      var request = new XMLHttpRequest();
      request.open('POST', 'http://ec2-54-68-251-216.us-west-2.compute.amazonaws.com/api/post', true);

      var content = document.title + ' ' + window.location.href;
      var form_data = new FormData();
      form_data.append('queue', true);
      form_data.append('api_key', '{{ $api_key }}');
      form_data.append('content', content);
      request.send(form_data);

      request.onload = function(){
        if(request.status >= 200 && request.status < 400){
          var response_data = JSON.parse(request.responseText);
          alert(response_data.text);
        }
      };

      request.onerror = function() {
        alert('Something went wrong while trying to post. Please try again');
      };
    })();" class="btn btn-lg btn-primary">Ahead Bookmarklet</a>
  </div>
</div>
@stop