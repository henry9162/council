@component('profiles.activities.activity')
    @slot('heading')
        <a href="{{ route('profile', $profileUser->name) }}">
            {{$profileUser->name}}
        </a>
        favorited
        <a href="{{ $activity->subject->favorited->path() }}">
            @if ($activity->subject->favorited->creator->id == auth()->id())
                his
            @else
                {{ $activity->subject->favorited->creator->name }}
            @endif
                reply
        </a> to <i>{{ $activity->subject->favorited->thread->title }}</i>
         {{--<a href="{{$activity->subject->thread->path()}}">{{$activity->subject->thread->title}}</a>--}}
    @endslot

    @slot('body')
        {{ $activity->subject->favorited->body}}
    @endslot
@endcomponent