
<script>
    import Replies from '../components/Replies.vue';
    import SubscribeButton from '../components/SubscribeButton.vue';

    export default{

        props: ['thread'],

        components: { Replies, SubscribeButton },

        data(){
            return{
                repliesCount: this.thread.replies_count,
                locked: this.thread.locked,
                editing: false,
                title: this.thread.title,
                body: this.thread.body,
                form: {
                    title: this.thread.title,
                    body: this.thread.body
                }
            };
        },

        /*created(){
            this.resetForm();
        },*/

        methods: {
            toggleLock(){
                let url = `/locked-threads/${this.thread.slug}`;
                axios[this.locked ? 'delete' : 'post'](url); // We use slug here instead of id bcos its been change by getRouteKey to slug in the thread model

                this.locked = ! this.locked;
            },

            update(){
                let url = `/thread/${this.thread.channel.slug}/${this.thread.slug}`;

                axios.patch(url, this.form).then(() => {
                    this.editing = false;

                    this.title = this.form.title;
                    this.body = this.form.body;

                    flash('your thread has been updated.')
                });
            },

            resetForm(){
                this.form = {
                    title: this.thread.title,
                    body: this.thread.body
                };

                this.editing = false;
            }
        }
    }
</script>