<reply :attibutes="{{$reply}}" inline-template v-cloak>
    <div id="reply-{{ $reply->id }}" class="card card-default">
        <div class="card-header">
            <div class="level">
                <h5 class="flex">
                    <a href="{{route('profile', $reply->creator)}}">{{$reply->creator->name}}</a> <small> said {{$reply->created_at->diffForHumans()}}...</small>
                </h5>
                <div>
                    @if (Auth::check())
                        <favorite :reply="{{ $reply  }}"></favorite>
                    @endif

                    {{--<form method="POST" action="/replies/{{$reply->id}}/favorites">
                        {{csrf_field()}}

                        <button type="submit" class="btn btn-link" {{$reply->isFavorite() ? 'disabled' : ''}}>
                            {{$reply->favorites_count}} {{str_plural('Favorite', $reply->favorites_count)}}
                        </button>
                    </form>--}}

                </div>
            </div>
        </div>
        <div class="card-body">
            <div v-if="editing">
                <div class="form-group">
                    <textarea class="form-control" v-model="body"></textarea>
                </div>
                <button class="btn  btn-sm btn-primary" @click="update">Update</button>
                <button class="btn  btn-sm btn-link" @click="editing=false">Cancel</button>
            </div>
            <div v-else v-text="body"></div>
        </div>

        @can ('update', $reply)
            <div class="card-footer level">
                <button class="btn btn-sm mr-1" @click="editing=true">Edit</button>
                <button class="btn btn-sm btn-danger mr-1" @click="destroy">Delete</button>

                {{--<form method="POST" action="/replies/{{$reply->id}}">
                    {{csrf_field()}}
                    {{ method_field('DELETE') }}

                    <button class="btn btn-danger btn-sm">Delete</button>
                </form>--}}

            </div>
        @endcan
    </div>
</reply>
