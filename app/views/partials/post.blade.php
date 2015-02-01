@if(count($networks) > 0)
<form class="form-horizontal" id="form_newpost" method="POST" action="/post/create">
  <fieldset>
    <legend>Schedule New Post</legend>
    <div id="alertmodal-container"></div>
    <input type="hidden" name="post_id" id="post_id">
    <input type="hidden" name="post_now" id="post_now" value="0">
    <div class="form-group">
      <label for="content" class="col-lg-2 control-label">Content</label>
      <div class="col-lg-10">
        <textarea name="content" id="content" cols="60" rows="5" class="form-control" value="{{ Input::old('content') }}">{{ Input::old('content') }}</textarea>
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
          if(in_array($n->id, Input::old('network', $default_networks))){
            $checked = 'checked';
          }
          ?>
            <input type="checkbox" name="network[]" class="network" data-id="{{ $n->id }}" value="{{ $n->id }}" {{ $checked }}>
            {{ $n->username }} ({{ $n->network }})
          </label>
        </div>
        @endforeach

      </div>
    </div>

    <div class="form-group" id="schedule-container">
      <label for="schedule" class="col-lg-2 control-label">Schedule</label>
      <div class="col-lg-10">
        <div class="radio">
          <label>
            <input type="radio" name="schedule" id="schedule" value="custom" {{ $custom_schedule_checked }}>
            custom
          </label>
        </div>
        <input type="text" name="schedule_value" id="schedule_value" class="form-control datetimepicker" value="{{ Input::old('schedule_value') }}">
        @foreach($schedules as $s)
        <div class="radio">
          <label>
          <?php
          $schedule_checked = '';
          if($s->id == Input::old('schedule', $default_schedule)){
            $schedule_checked = 'checked';
          }
          ?>
            <input type="radio" name="schedule" class="schedule" id="schedule_{{ $s->id }}" value="{{ $s->id }}" {{ $schedule_checked }}>
            {{ $s->name }}
          </label>
        </div>
        @endforeach
      </div>
    </div>

    <div class="form-group">
      <div class="col-lg-10 col-lg-offset-2">

        <div class="btn-group" id="btn-group-newpost">
          <button type="submit" class="btn btn-primary">Schedule</button>
          <button type="submit" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
            <span class="caret"></span>
            <span class="sr-only">Toggle Dropdown</span>
          </button>
          <ul class="dropdown-menu" role="menu">
            <li><a href="#" id="btn-postnow">Post Now</a></li>
          </ul>
        </div>

        <button type="submit" id="btn-schedule" class="btn btn-primary"></button>

      </div>
    </div>
  </fieldset>
</form>
@else
<div class="alert alert-info">
  Connect at least <a href="/networks">one network</a> to your account first.
</div>
@endif