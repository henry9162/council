@extends('layouts.app')

@section('content')
    <ais-index
            app-id="{{ config('scout.algolia.id') }}"
            api-key="264db861f2ada4db6bbd0cd06fee77b0"
            index-name="threads"
    >
        <ais-search-box></ais-search-box>

        <ais-refinement-list attribute-name="channel.name"></ais-refinement-list>

        <ais-results>
            <template slot-scope="{ result }">
                <p>
                    <a :href="result.path">
                        <ais-highlight :result="result" attribute-name="title"></ais-highlight>
                    </a>
                </p>
            </template>
        </ais-results>
    </ais-index>
@endsection