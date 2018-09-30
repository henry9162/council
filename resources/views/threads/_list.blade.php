@forelse ($threads as $thread)
    <div class="card card-default" style="margin-bottom: 2em;">
        <div class="card-header">
            <div class="level">
                <div class="flex">
                    <h4>
                        <a href="{{$thread->path()}}">
                            @if (auth()->check() && $thread->hasUpdateFor(auth()->user()))
                                <strong>
                                    {{$thread->title}}
                                </strong>
                            @else
                                {{$thread->title}}
                            @endif

                        </a>
                    </h4>

                    <h6>Posted By: <a href="{{ route('profile', $thread->creator) }}">{{ $thread->creator->name }}</a></h6>
                </div>

                <a href="{{$thread->path()}}">{{$thread->replies_count}} {{str_plural('reply', $thread->replies_count)}}</a>
            </div>
        </div>
        <div class="card-body">
            <div class="body">{!! $thread->body !!}</div>
        </div>

        <div class="card-footer">
            {{-- {{ $thread->visits()->count() }} visits /// we using database instead of redis, so we just refrence the column from the database   --}}
            {{ $thread->visits }} Visits
        </div>
    </div>
@empty
    <p>There are no relevant result at this time</p>
@endforelse