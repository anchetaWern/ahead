@section('content')
<div class="row">
  <div class="col-md-5">
  @include('partials.alert')
  </div>
</div>
<div class="row">
  <div class="col-md-5">
    <form class="form-horizontal" method="POST" action="/schedules/create">
      <fieldset>
        <legend>New Schedule</legend>
        <div class="form-group">
          <label for="name" class="col-lg-2 control-label">Name</label>
          <div class="col-lg-10">
            <input type="text" id="name" name="name" class="form-control">
          </div>
        </div>

        <div class="form-group">
          <label for="interval" class="col-lg-2 control-label">Interval</label>
          <div class="col-lg-10">
            <select name="interval" id="interval" class="form-control">
            @foreach($intervals as $s)
              <option value="{{ $s->id }}">{{ $s->name }}</option>
            @endforeach
            </select>
          </div>
        </div>

        <div class="form-group">
          <div class="col-lg-10 col-lg-offset-2">
            <button type="submit" class="btn btn-primary">Create Schedule</button>
          </div>
        </div>
      </fieldset>
    </form>
  </div>
</div>
@stop