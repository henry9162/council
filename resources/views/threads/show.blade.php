@extends('layouts.app')

@section('header')
    <link rel="stylesheet" href="/css/vendor/jquery.atwho.css">
@endsection

@section('content')
    <thread-view :thread = "{{ $thread }}" inline-template>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8" v-cloak>

                    @include('threads._question')
                    <br>
                    <replies @added="repliesCount++" @removed="repliesCount--"></replies>
                    {{--@foreach($replies as $reply)
                        @include('threads.reply')
                    @endforeach--}}

                    <br>

                    {{--{{$replies->links()}}--}}
                 {{--   @if (Auth()->check())
                        <form method="POST" action="{{ $thread->path() . '/replies' }}">
                            {{ csrf_field()}}
                            <div class="form-group">
                                <div>
                                    <textarea name="body" id="body" class="form-control" placeholder="Have something to say?" rows="5"></textarea>
                                </div><br>

                                <button type="submit" class="btn btn-primary btn-md">Post</button>
                            </div>
                        </form>
                    @else
                        <p class="text-center">Please <a href="{{route('login')}}">sign in</a> to participate in this discussion</p>
                    @endif--}}
                </div>
                <div class="col-md-4">
                    <div class="card card-default">
                        <div class="card-body">
                            <p>
                                This thread was published <strong><i>{{ $thread->created_at->diffForHumans() }}</i></strong> by
                                <a href="{{ route('profile', $thread->creator) }}">{{  $thread->creator->name }}</a>, and currently have <strong><span v-text="repliesCount"></span> comments</strong>.
                            </p>

                            <p>
                                <subscribe-button :active="{{ json_encode($thread->isSubscribedTo) }}" v-if="signedIn"></subscribe-button>
                                {{--json_encode gives us the string version of the boolean gotten from $thread->isSubscribedTo--}}

                                <button class="btn btn-default" v-if="authorize('isAdmin')" @click="toggleLock" v-text="locked ? 'Unlock' : 'Lock'">Lock</button>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </thread-view>
@endsection
