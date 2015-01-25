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
    <div id="posts">
      @foreach($posts as $p)
        <div class="col-md-5 col-centered post published-{{ $p->published }}" data-id="{{ $p->id }}">
          <div class="post-time">
          {{ Carbon::createFromTimeStamp(strtotime($p->date_time))->diffForHumans() }}
          </div>
          <div class="post-content">
          {{ $p->content }}
          </div>
          <div class="postnetworks"></div>
          <div>
            <a href="/posts/{{ $p->id }}/edit">edit</a>
          </div>
        </div>
      @endforeach
      <div class="col-md-5 col-centered">
      {{ $posts->links() }}
      </div>
    </div>

    @else
    <div class="alert alert-info">
      You haven't scheduled any posts yet.
    </div>
    @endif
  </div>
</div>
@include('partials.postnetworks')
@stop