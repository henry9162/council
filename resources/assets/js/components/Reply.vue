<template>
    <div :id="'reply-'+id" class="card" :class = "isBest ? 'border-success' : 'card-default'">
        <div class="card-header">
            <div class="level">
                <h5 class="flex">
                    <a :href="'/profiles/' + reply.creator.name" v-text="reply.creator.name"></a> <small> said <span v-text="ago"></span></small>
                </h5>

                <div v-if="signedIn">
                    <favorite :reply="reply"></favorite>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div v-if="editing">
                <form @submit="update">
                    <div class="form-group">
                        <wysiwyg v-model="body"></wysiwyg>
                        <!--<textarea class="form-control" v-model="body" required></textarea>-->
                    </div>
                    <button class="btn  btn-sm btn-primary">Update</button>
                    <button class="btn  btn-sm btn-link" @click="editing=false" type="button">Cancel</button>
                </form>
            </div>
            <div v-else v-html="body"></div>

            <hr>

            <div class="card-footer level" v-if="authorize('updateReply', reply) || authorize('updateThread', reply.thread)">
                <div v-if="authorize('updateReply', reply)">
                    <button class="btn btn-sm mr-1" @click="editing=true">Edit</button>
                    <button class="btn btn-sm btn-danger mr-1" @click="destroy">Delete</button>
                </div>
                <button class="btn btn-sm btn-default ml-a" @click="markBestReply" v-show="! isBest" v-if="authorize('updateThread', reply.thread)">Best Reply?</button>
            </div>
        </div>
    </div>
</template>

<script>
    import Favorite from './Favorite.vue';
    import moment from 'moment';

    export default{
        props: ['reply'],

        components: { Favorite },

        computed: {
            ago(){
                return moment(this.reply.created_at).fromNow() + '...';
            }
        },

        data(){
            return {
                editing: false,
                id: this.reply.id,
                body: this.reply.body,
                isBest: this.reply.isBest
            };
        },

        created(){
            window.events.$on('best-reply-selected', id => {
                //check if the new best reply id equals my id, if yes then return true
                this.isBest = (id === this.id);
            });
        },

        methods: {
            update(){
                axios.patch('/replies/' + this.id, {
                    body: this.body
                })

                .catch(error => {
                    flash(error.response.data, 'danger');
                });

                this.editing = false;

                flash('Updated!');
            },

            destroy(){
                axios.delete('/replies/' + this.id);

                this.$emit('deleted', this.id);
            },

            markBestReply(){

                axios.post('/replies/' + this.id + '/best');

                window.events.$emit('best-reply-selected', this.id);
            }
        }
    }
</script>