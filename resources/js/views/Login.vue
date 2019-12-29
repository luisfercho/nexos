<template>
    <div>
        <div class="card m-auto">
            <div class="card-body">
                    <h1 class="title">Login</h1>
                    <form autocomplete="off" @submit.prevent="login" method="post">
                        <div class="field">
                            <div class="control">
                                <input type="email" class="form-control" placeholder="user@example.com" v-model="username" />
                            </div>
                        </div>
                        <div class="mt-2">
                            <div class="control">
                                <input type="password" class="form-control" v-model="password" />
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary mt-2">
                            <i class="fa fa-sign-in-alt"></i>
                            Iniciar sessión
                        </button>
                    </form>
                    <div class="mt-2 alert alert-danger" v-if="error">
                        {{error}}
                    </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "Login",
        data() {
            return {
                username: null,
                password: null,
                error: null
            };
        },
        methods: {
            login() {
                this.$store
                    .dispatch("retrieveToken", {
                        username: this.username,
                        password: this.password
                    })
                    .then(response => {
                        this.$router.push({ name: "dashboard" });
                    })
                    .catch(error => {
                        this.error = error.response.data;
                    });
            }
        }
    }
</script>

<style scoped>
    .card{
        max-width:500px;
    }
</style>