@extends('layouts.app')

@section('content')
    <div class="container">
        <div>
            <ais-index
                app-id="{{ config('scout.algolia.id') }}"
                api-key="264db861f2ada4db6bbd0cd06fee77b0"
                index-name="threads"
                query = "{{ request('q') }}"
            >
                <div class="row">
                    <div class="col-md-8">
                        <ais-results>
                            <template slot-scope="{ result }">
                                <li>
                                    <a :href="result.path">
                                        <ais-highlight :result="result" attribute-name="title"></ais-highlight>
                                    </a>
                                </li>
                            </template>
                        </ais-results>
                    </div>

                    <div class="col-md-4">
                        <div class="card card-default" style="margin-bottom: 1em">
                            <div class="card-header">
                                <p>Search</p>
                            </div>

                            <div class="card-body">
                                <ais-search-box>
                                    <ais-input placeholder="find a thread..." :autofocus="true" class="form-control"></ais-input>
                                </ais-search-box>
                            </div>
                        </div>

                        <div class="card card-default" style="margin-bottom: 1em">
                            <div class="card-header">
                                <p>Filter by channel</p>
                            </div>

                            <div class="card-body">
                                <ais-refinement-list attribute-name="channel.name"></ais-refinement-list>
                            </div>
                        </div>

                        @if (count($trending))
                            <div class="card card-default">
                                <div class="card-header">
                                    <p>Trending Threads</p>
                                </div>

                                <div class="card-body">
                                    <ul class="list-group">
                                        @foreach ($trending as $thread)
                                            <li class="list-group-item">
                                                <a href="{{ url($thread->path) }}">{{ $thread->title }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </ais-index>
        </div>
    </div>
@endsection
