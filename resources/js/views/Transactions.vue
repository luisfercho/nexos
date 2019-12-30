<template>
    <div class="container">
        <div class="card">
            <div class="card-header d-flex flex-row justify-content-between align-items-center">
                <h4 class="m-0">
                    Transacciones {{ !loading&&account_id>0?" de la cuenta "+account.number:"" }}
                </h4>
                <button class="btn btn-dark" @click="addTransaction">
                    <i class="fas fa-file-invoice-dollar"></i>
                    Generar transacción
                </button>
            </div>
            <div class="card-body table-responsive">
                <template v-if="data.data.length > 0">
                    <table  class="table table-sm table-striped  table-bordered">
                        <thead>
                            <th v-for="title in titles" :colspan="title.cols" v-show="!(title.checkShow && hideAcount)">
                                {{ title.label }}
                            </th>
                        </thead>
                        <tbody>
                            <tr v-for="(transaction,i) in data.data">
                                <td v-show="!hideAcount">{{ transaction.account_number }}</td>
                                <td>
                                    <span v-if="transaction.type==1">Retiro</span>
                                    <span v-else>Consignación</span>
                                </td>
                                <td>{{ transaction.date }}</td>
                                <td>${{ transaction.amount | numFormat('0,0[.]00') }}</td>
                                <td>{{ transaction.description }}</td>
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
                        No se han encontrado transacciones {{ !loading&&account_id>0?" de la cuenta "+account.number+".":"."  }}
                    </span>
                </template>
            </div>
        </div>
        <!-- Modal -->
        <div class="modal fade" id="modalTransactions" tabindex="-1" role="dialog" aria-labelledby="modalTransactionsTitle" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTransactionsTitle"></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="#">
                            <div class="form-group">
                                <b>Cajero:</b>
                            </div>
                            <div class="form-group">
                                <b>Numero de cuenta:</b>
                                <template v-if="typeof this.$route.params.account_id != 'undefined'">
                                    <input type="text" class="form-control " id="numero" placeholder="Numero de cuenta" name="nombre" v-model="account.number" v-validate="'required'" :class="{'is-invalid': errors.has('numero') && submitted }" readonly >
                                    <span v-show="errors.has('numero')" class="invalid-feedback">{{ errors.first('numero') }}</span>
                                </template>
                                <template v-else>
                                    <cool-select
                                            :scrollItemsLim="10"
                                            v-model="account"
                                            :items="itemsAccount"
                                            :loading="loadingAccount"
                                            item-text="name"
                                            @search="onSearchAccount"
                                            :error-message="errorMessageAccount"
                                            :successful="!!(!errorMessageAccount && account)">
                                        <template slot="no-data">
                                            {{ noDataAccount?"No se encontro el cliente.":"Ingrese el nombre del cliente"}}
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
                                <select name="tipo"  class="form-control " id="tipo"  v-model="transaction.type" v-validate="'required'" :class="{'is-invalid': errors.has('tipo') && submitted }">
                                    <option value="">-Tipo-</option>
                                    <option value="1">Retiro</option>
                                    <option value="2">Consignación</option>
                                </select>
                                <span v-show="errors.has('tipo')" class="invalid-feedback">{{ errors.first('tipo') }}</span>
                            </div>
                            <div class="form-group">
                                <b>Clave:</b>
                                <input type="password" class="form-control " id="clave" placeholder="Clave" name="clave" v-model="transaction.clave" :class="{'is-invalid': errors.has('clave') && submitted }" v-validate="'required'"  length="4">
                                <span v-show="errors.has('clave')" class="invalid-feedback">{{ errors.first('clave') }}</span>
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
        name: "Transactions",
        components: {
            CoolSelect
        },
        data(){
            return {
                loading:true,
                hideAcount:false,
                account_id:0,
                titles:[
                    {label:"Cuenta",cols:1,checkShow:true},
                    {label:"Tipo",cols:1,checkShow:false},
                    {label:"Fecha",cols:1,checkShow:false},
                    {label:"Valor",cols:1,checkShow:false},
                    {label:"Descripción",cols:1,checkShow:false}
                ],
                account:{
                    id:0,
                    number:''
                },
                transaction:{
                    type:""
                },
                data:{
                    data:{}
                },

                loadingAccount:false,
                timeoutCustomerId: null,
                noDataAccount: false,
                itemsAccount:[],
                errorMessageAccount: null
            }
        },
        mounted(){
            if(typeof this.$route.params.account_id != "undefined"){
                this.account_id = parseInt(this.$route.params.account_id);
                if(isNaN(this.account_id) ){
                    createToastr("warning","Ha ocurrido un error al traer los datos de la cuenta");
                }else{
                    this.hideAcount = true;
                }
            }
            this.getResults();
        },
        methods:{
            getResults(page=1){
                this.loading = true;
                axios.get('/api/transactions/'+this.account_id+"?page="+page,{
                    'headers': { 'Authorization': 'Bearer '+localStorage.getItem("access_token") }
                })
                    .then(res=>{
                        console.log(res.data);
                        this.loading = false;
                        this.data    = res.data.transactions;
                        this.account  = res.data.account;
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
                /*this.account = {
                    id:0,
                    name:""
                };*/
            },
            addTransaction(){
                this.clearData();
                $("#modalTransactionsTitle").text("Generar transacción");
                this.openModal(1);
            },
            openModal(){
                this.submitted = false;
                $("#modalTransactions").modal("show");
            },
            sendData(){

            },
            onSearchAccount(){

            }
        }
    }
</script>

<style scoped>

</style>