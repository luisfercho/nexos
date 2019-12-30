<template>
    <div>
        <main-app></main-app>
        <section class="content-app">
            <router-view :key="$route.fullPath"></router-view>
        </section>
    </div>
</template>

<script>
    export default {
        name: "App"
    }
</script>

<style scoped>
    .content-app{
        padding-top: 50px;
    }
</style>