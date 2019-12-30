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
    </div>
</template>

<script>
    export default {
        name: "Transactions",
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
                    {label:"Acciones",cols:3,checkShow:false}
                ],
                account:{
                    id:0,
                    number:''
                },
                data:{
                    data:{}
                }
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
            addTransaction(){

            }
        }
    }
</script>

<style scoped>

</style>