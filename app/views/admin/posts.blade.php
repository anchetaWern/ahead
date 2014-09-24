@section('content')
<div class="row">
  <div class="col-md-5">
  @include('partials.alert')
  </div>
</div>
<div class="row">
  <div class="col-md-12">
    <h3>Posts</h3>
    @if($post_count > 0)
    <table class="table table-hover">
      <thead>
        <tr>
          <th>Content</th>
          <th>Time</th>
          <th>Published</th>
        </tr>
      </thead>
      <tbody>
      @foreach($posts as $p)
        <tr>
          <td>{{ str_limit($p->content, 50, '...') }}</td>
          <td>{{ Carbon::createFromTimeStamp(strtotime($p->date_time))->diffForHumans() }}</td>
          <td>
          <?php
          $published = 'nope';
          if($p->published){
            $published = 'yep';
          }
          ?>
          {{ $published }}
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>
    {{ $posts->links() }}
    @else
    <div class="alert alert-info">
      You haven't scheduled any posts yet.
    </div>
    @endif
  </div>
</div>
@stop