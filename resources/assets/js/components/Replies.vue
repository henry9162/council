<template>
    <div>
        <div v-for="(reply, index) in items" :key="reply.id">
            <reply :reply="reply" @deleted="remove(index)"></reply>
        </div><br>

        <paginator :dataSet = "dataSet" @changed = "fetch"></paginator>

        <p class="alert alert-warning" v-if="$parent.locked">This thread is locked, no more replies are allowed.</p>

        <new-reply :endpoint="endpoint" @created = "add" v-else></new-reply>
    </div>
</template>


<script>
    import Reply from './Reply.vue';
    import NewReply from './NewReply.vue';
    import collection from '../mixins/collection';

    export default {

        components: { Reply, NewReply },

        mixins: [collection],

        data(){
            return {
                dataSet: false,
                endpoint: location.pathname + '/replies'
            }
        },

        created(){
            this.fetch();
        },

        methods: {
            fetch(page){
                axios.get(this.url(page))
                    .then(this.refresh);
            },

            url(page){
                if (!page){
                    let query = location.search.match(/page=(\d+)/);

                    page = query ? query[1] : 1;
                }

                return `${location.pathname}/replies?page=${page}`;
            },

            refresh({data}){
                this.dataSet = data;
                this.items = data.data;

                window.scrollTo(8, 0);
            }
        }

    }
</script>