import Vue from 'vue'
import Vuex from 'vuex'
import axios from 'axios'
Vue.use(Vuex);

const store = new Vuex.Store({
    // estado de la aplicación
    state: {
        token: localStorage.getItem('access_token') || null,
        user_name: localStorage.getItem('user_name') || "",
        user_ref: localStorage.getItem('user_ref') || 0,
        user_cashier: localStorage.getItem('user_cashier') || 0
    },
    // funciones para ejecutar en la aplicación
    getters: {
        loggedIn(state) {
            return state.token !== null
        }
    },
    // modificadores de estado
    mutations: {
        retrieveToken(state, token) {
            state.token = token
        },
        destroyToken(state) {
            state.token = null;
            state.user_name = "";
            state.user_ref  = 0;
            state.user_cashier = 0;
        },
        setUserData(state,userData){
            state.user_name = userData.name;
            state.user_ref  = userData.id;
            state.user_cashier = 0;
        }
    },
    // acciones asincronas
    actions: {
        retrieveToken(context, credentials) {

            return new Promise((resolve, reject) => {
                axios.post('/api/login', {
                    username: credentials.username,
                    password: credentials.password,
                })
                    .then(response => {
                        //console.log(response)
                        const token = response.data.access_token;
                        localStorage.setItem('access_token', token);
                        context.commit('retrieveToken', token);
                        context.dispatch('setUserData');
                        resolve(response)
                    })
                    .catch(error => {
                        //console.log(error)
                        reject(error)
                    })
            })

        },
        destroyToken(context) {

            axios.defaults.headers.common['Authorization'] = 'Bearer ' + context.state.token

            if (context.getters.loggedIn){

                return new Promise((resolve, reject) => {
                    axios.post('/api/logout')
                        .then(response => {
                            //console.log(response)
                            localStorage.removeItem('access_token')
                            localStorage.removeItem('access_token')
                            localStorage.removeItem('user_name')
                            localStorage.removeItem('user_ref')
                            localStorage.removeItem('user_cashier')
                            context.commit('destroyToken')

                            resolve(response)
                        })
                        .catch(error => {
                            //console.log(error)
                            localStorage.removeItem('access_token')
                            localStorage.removeItem('user_name')
                            localStorage.removeItem('user_ref')
                            localStorage.removeItem('user_cashier')
                            context.commit('destroyToken')

                            reject(error)
                        })
                })

            }
        },
        setUserData(context){
            axios.defaults.headers.common['Authorization'] = 'Bearer ' + context.state.token

            if (context.getters.loggedIn){

                return new Promise((resolve, reject) => {
                    axios.get('/api/user')
                        .then(response => {
                            localStorage.setItem('user_name',response.data.name);
                            localStorage.setItem('user_ref',response.data.id);
                            localStorage.setItem('user_cashier',0);
                            context.commit('setUserData',{
                                id:response.data.id,
                                name:response.data.name
                            });
                            resolve(response)
                        })
                        .catch(error => {
                            reject(error)
                        })
                })

            }
        }
    }
});

export default store