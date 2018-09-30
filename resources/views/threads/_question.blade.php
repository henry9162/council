{{-- Editing the question --}}
<div class="card card-default" v-if="editing">
    <div class="card-header">
        <div class="level">
            <input type="text" class="form-control" v-model="form.title">

        </div>
    </div>

    <div class="card-body">
        <div class="form-group">
            <wysiwyg v-model="form.body" :value="form.body"></wysiwyg>
            {{--<textarea class="form-control" rows="10" v-model="form.body"></textarea>--}}
        </div>
    </div>

    <div class="card-footer">
        <div class="level">
            <button class="btn btn-xs level-item" @click="editing=true" v-show="! editing">Edit</button>
            <button class="btn btn-xs btn-primary level-item" @click="update">Update</button>
            <button class="btn btn-danger btn-xs level-item" @click="resetForm">Cancel</button>

            @can ('update', $thread)
                <form action="{{$thread->path()}}" method="POST" class="ml-a">
                    {{csrf_field()}}
                    {{method_field('DELETE')}}

                    <button type="submit" class="btn btn-link">Delete Thread</button>
                </form>
            @endcan
        </div>
    </div>
</div>


{{-- Viewing the question --}}
<div class="card card-default" v-else>
    <div class="card-header">
        <div class="level">
            <img src="{{ asset($thread->creator->avatar_path) }}" alt="{{ $thread->creator->name }}" width="50" height="50" class="mr-3">

            <span class="flex">
                <a href="{{route('profile', $thread->creator)}}">{{$thread->creator->name}}</a> posted: <span v-text="title"></span>
            </span>

        </div>
    </div>

    <div class="card-body" v-html="body"></div>

    <div class="card-footer" v-if="authorize('updateThread', thread)">
        <button class="btn btn-xs" @click="editing=true">Edit</button>
    </div>
</div>