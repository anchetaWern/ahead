@section('content')
<div class="row">
  <div class="col-md-5">
  @include('partials.alert')
  </div>
</div>
<div class="row">
  <div class="col-md-5">
    <form class="form-horizontal" method="POST" action="/post/create">
      <fieldset>
        <legend>Schedule New Post</legend>
        <div class="form-group">
          <label for="content" class="col-lg-2 control-label">Content</label>
          <div class="col-lg-10">
            <textarea name="content" id="content" cols="60" rows="5" class="form-control"></textarea>
          </div>
        </div>

        <div class="form-group">
          <label class="col-lg-2 control-label">Post to</label>
          <div class="col-lg-10">
            @foreach($networks as $n)
            <div class="checkbox">
              <label>
                <input type="checkbox" name="network[]" value="{{ $n->id }}">
                {{ $n->username }} ({{ $n->network }})
              </label>
            </div>
            @endforeach

          </div>
        </div>

        <div class="form-group">
          <div class="col-lg-10 col-lg-offset-2">
            <button type="submit" class="btn btn-primary">Schedule</button>
          </div>
        </div>
      </fieldset>
    </form>
  </div>
</div>
@stop