@section('content')
<div class="row">
  <div class="col-md-5">
  @include('partials.alert')
  </div>
</div>
<div class="row">
  <div class="col-md-5">
    <form class="form-horizontal" method="POST" action="/settings">
      <fieldset>
        <legend>Settings</legend>
        <div class="form-group">
          <label for="content" class="col-lg-2 control-label">Default Accounts</label>
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
                <input type="checkbox" name="settings[]" value="{{ $n->id }}" {{ $checked }}>
                {{ $n->username }} ({{ $n->network }})
              </label>
            </div>
            @endforeach


          </div>
        </div>
        <div class="form-group">
          <div class="col-lg-10 col-lg-offset-2">
            <button type="submit" class="btn btn-primary">Update Settings</button>
          </div>
        </div>
      </fieldset>
    </form>
  </div>
</div>
@stop