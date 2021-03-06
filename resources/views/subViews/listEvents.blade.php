@if(count($events) < 1)
    <div class="alert alert-secondary" role="alert">
        No events to display!
    </div>
@elseif(count($events) > 0)
    <table class="table table-bordered table-hover" style="max-width: 90%">
        <thead>
        <th scope="col">Owner</th>
        <th scope="col">Title</th>
        <th scope="col">Description</th>
        <th scope="col">Start Date</th>
        <th scope="col">End Date</th>
        <th scope="col" width="200px;">Actions</th>
        </thead>
        <tbody>
        @foreach ($events as $event)
            <tr>
                <td>{{$event->user->name}}</td>
                <td>{{$event->title}}</td>
                <td>{{$event->description}}</td>
                <td>{{$event->start_date}}</td>
                <td>{{$event->end_date}}</td>
                <td style="display: flex">
                    <a href="{{route('events.edit', ['event' => $event])}}" class="btn btn-info btn-sm">Edit</a>
                    <a href="{{route('events.show', $event->id)}}" class="btn btn-info btn-sm">View</a>
                    <form method="post" action="{{route('events.destroy', $event->id)}}">
                        {{method_field('DELETE')}}
                        {{ csrf_field() }}
                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif
