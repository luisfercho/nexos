<template>
    <nav class="navbar navbar-expand-lg  navbar-dark bg-dark" v-if="this.$store.getters.loggedIn">
        <a class="navbar-brand" href="#">
            Nexos
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <router-link class="nav-link" :to="{name:'dashboard'}">
                        <i class="fa fa-home"></i>
                        Inicio
                    </router-link>
                </li>
                <li class="nav-item">
                    <router-link class="nav-link" :to="{name:'clients'}">
                        <i class="fa fa-users"></i>
                        Clientes
                    </router-link>
                </li>
                <li class="nav-item">
                    <router-link class="nav-link" :to="{name:'accounts'}">
                        <i class="fa fa-dollar-sign"></i>
                        Cuentas
                    </router-link>
                </li>
                <li class="nav-item">
                    <router-link class="nav-link" :to="{name:'transactions'}">
                        <i class="fa fa-exchange-alt"></i>
                        Transacciones
                    </router-link>
                </li>
            </ul>
            <ul class="navbar-nav my-2 my-lg-0">
                <li class="nav-item">
                    <router-link class="nav-link" :to="{name:'logout'}">
                        <i class="fa fa-sign-out-alt"></i>
                        Cerrar session
                    </router-link>
                </li>
            </ul>
        </div>
    </nav>
</template>

<script>
    export default {
        name: "app"
    }
</script>

<style scoped>

</style>