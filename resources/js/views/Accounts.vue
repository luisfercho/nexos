<template>
    <div class="container">
        <div class="card">
            <div class="card-header d-flex flex-row justify-content-between align-items-center">
                <h4 class="m-0">
                    Cuentas {{ !loading&&client_id>0?" del cliente "+client.name:"" }}
                </h4>
                <button class="btn btn-dark" @click="addAccount">
                    <i class="fas fa-hand-holding-usd"></i>
                    Agregar Cuenta
                </button>
            </div>
            <div class="card-body table-responsive">
                <template v-if="data.data.length > 0">
                    <table  class="table table-sm table-striped  table-bordered">
                        <thead>
                            <th v-for="title in titles" :colspan="title.cols" v-show="!(title.checkShow && hideClient)">
                                {{ title.label }}
                            </th>
                        </thead>
                        <tbody>
                            <tr v-for="(account,i) in data.data" :class="account.status==1?'':'bg-danger text-white'">
                                <td v-show="!hideClient">{{ account.client_name+" "+account.client_last_name }}</td>
                                <td>{{ account.type }}</td>
                                <td>{{ account.number }}</td>
                                <td>${{ account.balance | numFormat('0,0[.]00')  }}</td>
                                <td>{{ account.status==1?"Habilitada":"Inhabilitada" }}</td>
                                <td>
                                    <span v-if="account.active==1" class="badge badge-info">Si</span>
                                    <span v-else class="badge badge-warning">No</span>
                                </td>
                                <td class="p-0 m-0" style="width:2%">
                                    <router-link class="btn btn-link text-dark btn-tooltip" :to="{name:'transactions',params:{account_id:account.id}}" title="Transacciones">
                                        <i class="fas fa-exchange-alt"></i>
                                    </router-link>
                                </td>
                                <td class="p-0 m-0" style="width:2%">
                                    <button class="btn btn-link text-dark btn-tooltip" title="Inactivar" @click="confirmInactivate(i)" :disabled="account.status==2">
                                        <i class="fa fa-ban"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <pagination
                            :data="data"
                            :align="'center'"
                            :show-disabled="true"
                            @pagination-change-page="getResults"
                    ></pagination>
                </template>
                <template v-else>
                    <span v-if="loading" class="d-block mb-0 alert alert-info w-100">
                        Cargando datos...
                    </span>
                    <span v-else class="d-block mb-0 alert alert-warning w-100">
                        No se han encontrado cuentas{{ !loading&&client_id>0?" del cliente "+client.name+".":"."  }}
                    </span>
                </template>
            </div>
        </div>
        <!-- Modal -->
        <div class="modal fade" id="modalAccounts" tabindex="-1" role="dialog" aria-labelledby="modalAccountsTitle" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalAccountsTitle"></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="#">
                            <div class="form-group">
                                <b>Cliente:</b>
                                <template v-if="typeof this.$route.params.client_id != 'undefined'">
                                    <input type="text" class="form-control " id="nombre" placeholder="Nombre" name="nombre" v-model="client.name" v-validate="'required'" :class="{'is-invalid': errors.has('nombre') && submitted }" readonly >
                                    <span v-show="errors.has('nombre')" class="invalid-feedback">{{ errors.first('nombre') }}</span>
                                </template>
                                <template v-else>
                                    <cool-select
                                            :scrollItemsLim="10"
                                            v-model="client"
                                            :items="itemsCustomer"
                                            :loading="loadingCustomer"
                                            item-text="name"
                                            @search="onSearchCustomer"
                                            :error-message="errorMessageCustomer"
                                            :successful="!!(!errorMessageCustomer && client)">
                                        <template slot="no-data">
                                            {{ noDataCustomer?"No se encontro el cliente.":"Ingrese el nombre del cliente"}}
                                        </template>
                                        <template #item="{ item }">
                                            <div class="item">
                                                <div>
                                                    <span class="item-name"> {{ item.name }} </span>
                                                </div>
                                            </div>
                                        </template>
                                    </cool-select>
                                </template>
                            </div>
                            <div class="form-group">
                                <b>Tipo:</b>
                                <input type="text" class="form-control " id="typo" placeholder="Tipo" name="tipo" v-model="account.type" v-validate="'required'" :class="{'is-invalid': errors.has('tipo') && submitted }" readonly >
                                <span v-show="errors.has('tipo')" class="invalid-feedback">{{ errors.first('tipo') }}</span>
                            </div>
                            <div class="form-group">
                                <b>Abrir con:</b>
                                <input type="number" class="form-control " id="saldo" placeholder="Abrir con" name="saldo" v-model="account.balance" :class="{'is-invalid': errors.has('saldo') && submitted }" v-validate="'min:0|decimal:2'"  min="0">
                                <span v-show="errors.has('saldo')" class="invalid-feedback">{{ errors.first('saldo') }}</span>
                            </div>
                            <div>
                                <div class="alert alert-info">
                                    Para activar esta cuenta el saldo debe ser de minimo $100.000
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fa fa-times"></i>
                            Cerrar
                        </button>
                        <button type="button" class="btn btn-danger" @click="sendData">
                            <i class="fa fa-save"></i>
                            Guardar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import Swal from 'sweetalert2'
    import { CoolSelect } from "vue-cool-select";
    export default {
        name: "Accounts",
        components: {
            CoolSelect
        },
        data(){
            return {
                loading:true,
                submitted:false,
                hideClient:false,
                client_id:0,
                account:{
                    id:0,
                    type:"Cuenta de ahorros",
                    balance:""
                },
                client:{
                    name:""
                },
                titles:[
                    {label:"Cliente",cols:1,checkShow:true},
                    {label:"Tipo",cols:1,checkShow:false},
                    {label:"Numero",cols:1,checkShow:false},
                    {label:"Saldo",cols:1,checkShow:false},
                    {label:"Estado",cols:1,checkShow:false},
                    {label:"Activada",cols:1,checkShow:false},
                    {label:"Acciones",cols:3,checkShow:false}
                ],
                data:{
                    data:[]
                },

                loadingCustomer:false,
                selectedCustomer:{},
                timeoutCustomerId: null,
                noDataCustomer: false,
                itemsCustomer:[],
                errorMessageCustomer: null
            }
        },
        mounted(){
            if(typeof this.$route.params.client_id != "undefined"){
                this.client_id = parseInt(this.$route.params.client_id);
                if(isNaN(this.client_id) ){
                    createToastr("warning","Ha ocurrido un error al traer los datos del cliente")
                }else{
                    this.hideClient = true;
                }
            }
            this.getResults();
        },
        methods:{
            getResults(page=1){
                this.loading = true;
                axios.get('/api/accounts/'+this.client_id+"?page="+page,{
                   'headers': { 'Authorization': 'Bearer '+localStorage.getItem("access_token") }
                })
                    .then(res=>{
                        this.loading = false;
                        this.data    = res.data.accounts;
                        this.client  = res.data.client;
                    })
                    .catch(err=>{
                        this.loading = false;
                        let dataError = err.response;
                        let message;
                        if(dataError.status == 401){
                            message = "Acceso denegado";
                        }else{
                            message = dataError.data.message;
                        }
                        createToastr("warning",message);
                    });
            },
            clearData(){
                this.account = {
                    id:0,
                    type:"Cuenta de ahorros",
                    balance:""
                };
            },
            addAccount(){
                this.clearData();
                $("#modalAccountsTitle").text("Añadir Cuenta");
                this.openModal(1);
            },
            openModal(){
                this.submitted = false;
                $("#modalAccounts").modal("show");
            },
            sendData(){
                this.submitted = true;
                this.$validator.validateAll().then((result) => {
                    if (result) {
                        let $self = this;
                        if($self.client == null){
                            createToastr("warning","Debes ingresar el cliente");
                            return false;
                        }
                        axios.post('/api/accounts',{
                            id:$self.client.id,
                            name:$self.client.name,
                            balance:$self.account.balance
                        },{
                            'headers': { 'Authorization': 'Bearer '+localStorage.getItem("access_token") }
                        }).then(response => {
                            if(response.data.success){
                                this.clearData();
                                $self.getResults();
                                createToastr("siccess",response.data.message);
                            }else{
                                createToastr("warning",response.data.message);
                            }
                        })
                            .catch(err => {
                                this.loading = false;
                                let dataError = err.response;
                                let message;
                                if(dataError.status == 401){
                                    message = "Acceso denegado";
                                }else{
                                    message = dataError.data.message;
                                }
                                createToastr("warning",message);
                            })
                    }
                });
            },
            confirmInactivate(i){
                if(this.data.data[i].status === 1){
                    Swal.fire({
                        title: 'Se inhabilitara la cuenta "' + this.data.data[i].number + '"',
                        text: "Deseas continuar? esta acción no se puede deshacer.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'Si, inhabilitar',
                        cancelButtonColor: '#d33',
                        cancelButtonText: 'Cancelar',
                        reverseButtons: true,
                        showLoaderOnConfirm: true ,
                        preConfirm: () => {
                            this.inactivateAccount(i);
                        }
                    });
                }
            },
            inactivateAccount(i){
                let $self = this;
                axios.delete('/api/accounts/'+$self.data.data[i].id,
                    {'headers': { 'Authorization': 'Bearer '+localStorage.getItem("access_token") }}
                ).then((response)=>{
                    console.log(response);
                    if(response.data.success){
                        this.data.data[i].status = 2;
                        createToastr("success",response.data.message);
                    }else{
                        createToastr("warning",response.data.message);
                    }
                })
                .catch((err)=>{
                    console.log(err);
                    let dataError = err.response;
                    let message;
                    if(dataError.status == 401){
                        message = "Acceso denegado";
                    }else{
                        message = dataError.data.message;
                    }
                    createToastr("warning",message);
                });
                $(".tooltip").remove();
            },

            async getDataAjax(search){
                let res = await axios.get(`/api/accounts/getCustomer?query=${search}`,{
                    'headers': { 'Authorization': 'Bearer '+localStorage.getItem("access_token") }
                });
                console.log(res.data);
                return res.data;
            },

            async onSearchCustomer(search) {
                const lettersLimit = 2;

                this.noDataCustomer = false;
                if (search.length < lettersLimit) {
                    this.itemsCustomer = [];
                    this.loadingCustomer = false;
                    return;
                }
                this.loadingCustomer = true;

                clearTimeout(this.timeoutCustomerId);
                this.timeoutCustomerId = setTimeout(async () => {

                    this.itemsCustomer = await this.getDataAjax(search);
                    this.loadingCustomer = false;

                    if (!this.itemsCustomer.length) this.noDataCustomer = true;

                    console.log(this.itemsCustomer);
                }, 500);
            },
        }
    }
</script>

<style scoped>
    .badge{
        padding: 0.4rem 0.5rem;
        font-size: .8rem;
    }
</style>