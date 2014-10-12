@section('content')
<div class="row">
  <div class="col-md-5">
  @include('partials.alert')
  </div>
</div>
<div class="row">
  <div class="col-md-5">
    <form class="form-horizontal" method="POST" action="/posts">
      <fieldset>
        <legend>Edit Post</legend>

        <input type="hidden" name="post_id" id="post_id" value="{{ $post_id }}">

        <div class="form-group">
          <label for="content" class="col-lg-2 control-label">Content</label>
          <div class="col-lg-10">
            <textarea name="content" id="content" cols="60" rows="5" class="form-control" value="{{ $post->content }}">{{ $post->content }}</textarea>
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
              if(in_array($n->id, $selected_networks)){
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
          <div class="col-lg-10 col-lg-offset-2">
            <button type="submit" class="btn btn-primary">Update Post</button>
          </div>
        </div>
      </fieldset>
    </form>
  </div>
</div>
@stop