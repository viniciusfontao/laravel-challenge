@extends('layouts.app')

@section('content')
    <h1>Details</h1>

    <ul>
        <li>Owner: {{$event->user->name}}</li>
        <li>Title: {{$event->title}}</li>
        <li>Description: {{$event->description}}</li>
        <li>Start Date: {{$event->start_date}}</li>
        <li>End Date: {{$event->end_date}}</li>
        <li>
            Participants:
            @forelse($event->participants as $participant)
                {{$participant->name}}
            @empty
               No participants yet!
            @endforelse
        </li>
    </ul>
@stop
