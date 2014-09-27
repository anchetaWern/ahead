@section('content')
<div class="row">
  <div class="col-md-5">
  @include('partials.alert')
  </div>
</div>
<div class="row">
  <div class="col-md-12">
    <h3>Schedules</h3>
    @if(!empty($schedules))
    <table class="table table-hover">
      <thead>
        <tr>
          <th>Name</th>
          <th>Interval</th>
        </tr>
      </thead>
      <tbody>
      @foreach($schedules as $p)
        <tr>
          <td>{{ $p->schedule_name }}</td>
          <td>{{ $p->interval_name }}</td>
        </tr>
      @endforeach
      </tbody>
    </table>
    {{ $schedules->links() }}
    @else
    <div class="alert alert-info">
      You haven't created any schedules yet.
    </div>
    @endif
  </div>
</div>
@stop