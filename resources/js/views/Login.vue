<template>
    <div>
        <div class="card m-auto shadow ">
            <div class="card-body">
                    <h1 class="title">Login</h1>
                    <form autocomplete="off" @submit.prevent="login" method="post">
                        <div class="field">
                            <div class="control">
                                <input type="email" class="form-control" placeholder="E-mail" v-model="username" v-validate="'required|email'" :class="{'is-invalid': errors.has('Email') && submitted }" name="username" 
                                data-vv-name="Email" />
                                <span v-show="errors.has('Email')" class="invalid-feedback">{{ errors.first('Email') }}</span>
                            </div>
                        </div>
                        <div class="mt-2">
                            <div class="control">
                                <input type="password" class="form-control" placeholder="Contraseña" v-model="password" name="password" v-validate="'required'" :class="{'is-invalid': errors.has('Contraseña') && submitted }" data-vv-name="Contraseña" />
                                <span v-show="errors.has('Contraseña')" class="invalid-feedback">{{ errors.first('Contraseña') }}</span>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary mt-2" :disabled="loading?true:false">
                            <i class="fa fa-sign-in-alt"></i>
                            <span v-if="loading" >Iniciando sesión...</span>
                            <span v-else >Iniciar sesión</span>
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
                submitted:false,
                username: null,
                password: null,
                error: null,
                loading:false
            };
        },
        created(){
            if(this.$store.getters.loggedIn){
                this.$router.push({ name: "transactions" });
            }
        },
        methods: {
            login() {
                this.loading = true;
                this.submitted = true;
                this.$validator.validateAll().then((result) => {
                    if (result){          
                        this.$store
                            .dispatch("retrieveToken", {
                                username: this.username,
                                password: this.password
                            })
                            .then(response => {
                                this.loading = false;
                                this.$router.push({ name: "transactions" });
                            })
                            .catch(error => {
                                this.loading = false;
                                this.error = error.response.data;
                            });
                    }else{
                        this.loading = false;
                    }
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