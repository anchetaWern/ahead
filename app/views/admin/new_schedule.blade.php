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
            <input type="text" id="name" name="name" class="form-control" value="{{ Input::old('name') }}">
          </div>
        </div>
        <div class="form-group">
          <label for="rule" class="col-lg-2 control-label">Rule</label>
          <div class="col-lg-10">
            @foreach($rules as $rule)
            <div class="radio">
              <label>
              <?php
              $checked = '';
              if($rule == Input::old('rule')){
                $checked = 'checked';
              }
              ?>
                <input type="radio" name="rule" id="{{ $rule }}" value="{{ $rule }}" {{ $checked }}>
                {{ $rule }}
              </label>
            </div>
            @endforeach
          </div>
        </div>

        <div class="form-group">
          <label for="period" class="col-lg-2 control-label">Period</label>
          <div class="col-lg-10">
            <input type="text" id="period" name="period" class="form-control" value="{{ Input::old('period') }}">
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