
<template>
    <div v-if="signedIn">
        <div class="form-group">
            <div>
                <wysiwyg name="body" v-model="body" placeholder="Have something to say?" :shouldClear="completed"></wysiwyg>
                <!--<textarea name="body" id="body" class="form-control" placeholder="Have something to say?" rows="5" v-model="body"></textarea>-->
            </div><br>

            <button type="submit" class="btn btn-primary btn-md" @click="addReply">Post</button>
        </div>
    </div>

    <p class="text-center" v-else>Please <a href="/login">sign in</a> to participate in this discussion</p>
</template>

<script>
    import 'jquery.caret';
    import 'at.js';

    export default{
        props: ['endpoint'],

        computed: {
            signedIn(){
                return window.App.signedIn;
            }
        },

        data(){
            return{
                body: '',
                completed: false
            };
        },

        mounted(){

            $('#body').atwho({
                at: "@",
                delay: 750,
                callbacks: {
                    remoteFilter: function(query, callback){
                        $.getJSON("/api/users", {name: query}, function(usernames){
                            callback(usernames);
                        });
                    }
                }
            });
        },

        methods: {
            addReply(){
                axios.post(this.endpoint, {body: this.body})

                .catch(error => {
                    flash(error.response.data, 'danger');
                })

                .then(({data}) => {
                    body: '';

                    this.completed = true;

                    flash('Your reply has been posted');

                    this.$emit('created', data);
                });
            }
        }
    }
</script>