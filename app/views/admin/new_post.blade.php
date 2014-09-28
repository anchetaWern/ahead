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
              <?php
              $checked = '';
              if(in_array($n->id, $default_networks)){
                $checked = 'checked';
              }
              ?>
                <input type="checkbox" name="network[]" value="{{ $n->id }}" {{ $checked }}>
                {{ $n->username }} ({{ $n->network }})
              </label>
            </div>
            @endforeach

          </div>
        </div>

        <div class="form-group">
          <label for="schedule" class="col-lg-2 conntrol-label">Schedule</label>
          <div class="col-lg-10">
            @foreach($schedules as $s)
            <div class="radio">
              <label>
              <?php
              $schedule_checked = '';
              if($s->id == $default_schedule){
                $schedule_checked = 'checked';
              }
              ?>
                <input type="radio" name="schedule" id="schedule_{{ $s->id }}" value="{{ $s->id }}" {{ $schedule_checked }}>
                {{ $s->name }}
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