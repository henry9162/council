<template>
    <li class="nav-item dropdown" v-if="notifications.length">
        <a href="#" style="color:blue;" class="nav-link dropdown-toggle" data-toggle="dropdown">
            <span class="glyphicon glyphicon-bell"></span>Notifications
        </a>

        <div class="dropdown-menu">
            <div v-for="notification in notifications">
                <a class="dropdown-item" :href="notification.data.link" v-text="notification.data.message" @click="markAsRead(notification)"></a>
            </div>
        </div>
    </li>
</template>

<script>
    export default{

        data(){
            return {
                notifications : false
            }
        },

        created(){
            axios.get("/profiles/" + window.App.user.name + "/notifications")
                .then(response => this.notifications = response.data);
        },

        methods: {
            markAsRead(notification){
                axios.delete('/profiles/' + window.App.user.name + '/notifications/' + notification.id);
            }
        }
    }
</script>