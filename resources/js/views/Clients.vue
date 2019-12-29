<template>
    <div class="container">
        <div class="card">
            <div class="card-header d-flex flex-row justify-content-between align-items-center">
                <h4 class="m-0">
                    Clientes
                </h4>
                <button class="btn btn-dark" @click="addClient">
                    <i class="fas fa-user-plus"></i>
                    Agregar Cliente
                </button>
            </div>
            <div class="card-body">
                <template v-if="data.data.length > 0">
                    <table  class="table table-sm table-striped  table-bordered">
                        <thead>
                        <th v-for="title in titles" :colspan="title.cols">
                            {{ title.label }}
                        </th>
                        </thead>
                        <tbody>
                        <tr v-for="(client,i) in data.data">
                            <td v-text="client.name+' '+client.last_name"></td>
                            <td v-text="client.document"></td>
                            <td v-text="client.address"></td>
                            <td v-text="client.cellphone"></td>
                            <td v-text="client.email"></td>

                            <td class="p-0 m-0" style="width:3%">
                                <button class="btn btn-link text-dark btn-tooltip" title="Editar" @click="editClient(i)">
                                    <i class="fa fa-pencil-alt"></i>
                                </button>
                            </td>
                            <td class="p-0 m-0" style="width:3%">
                                <button class="btn btn-link text-dark btn-tooltip" title="Eliminar">
                                    <i class="fa fa-times"></i>
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
                        No se han encontrado clientes.
                    </span>
                </template>
            </div>
        </div>
        <!-- Modal -->
        <div class="modal fade" id="modalClients" tabindex="-1" role="dialog" aria-labelledby="modalClientsTitle" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalClientsTitle"></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="#">
                            <div class="form-group">
                                <b>Nombre:</b>
                                <input type="text" class="form-control " id="nombre" placeholder="Nombre" name="nombre" v-model="client.name" v-validate="'required'" :class="{'is-invalid': errors.has('nombre') && submitted }">
                                <span v-show="errors.has('nombre')" class="invalid-feedback">{{ errors.first('nombre') }}</span>

                            </div>
                            <div class="form-group">
                            <b>Apellidos:</b>
                            <input type="text" class="form-control " id="apellidos" placeholder="Apellidos" name="apellidos" v-model="client.last_name" v-validate="'required'" :class="{'is-invalid': errors.has('apellidos') && submitted }">
                            <span v-show="errors.has('nombre')" class="invalid-feedback">{{ errors.first('apellidos') }}</span>

                        </div>
                            <div class="form-group">
                                <b>Documento:</b>
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                        <select name="tipo_documento" id="tipo_documento" v-model="client.document_type" class="form-control">
                                            <option value="1" selected>C.C</option>
                                            <option value="2">C.T</option>
                                            <option value="3">P.te</option>
                                            <option value="4">Otro</option>
                                        </select>
                                    </div>
                                    <input type="text" class="form-control " id="documento" placeholder="Documento" name="documento" v-model="client.document" v-validate="'required'" :class="{'is-invalid': errors.has('documento') && submitted }" minlength="4">
                                    <span v-show="errors.has('documento')" class="invalid-feedback">
                                        {{ errors.first('documento') }}
                                    </span>
                                </div>

                            </div>
                            <div class="form-group">
                                <b>Email:</b>
                                <input type="text" class="form-control" id="email" placeholder="Email" name="email" v-model="client.email" v-validate="'required|email'" :class="{'is-invalid': errors.has('email') && submitted }"  autocomplete="off">
                                <span v-show="errors.has('email')" class="invalid-feedback">{{ errors.first('email') }}</span>
                            </div>
                            <div class="form-group">
                                <b>Tel&eacute;fono:</b>
                                <input type="number" class="form-control" id="telefono" placeholder="Teléfono" name="telefono" v-model="client.cellphone" v-validate="'required'" :class="{'is-invalid': errors.has('telefono') && submitted }"  autocomplete="off">
                                <span v-show="errors.has('email')" class="invalid-feedback">{{ errors.first('telefono') }}</span>
                            </div>
                            <div class="form-group">
                                <b>Dirección:</b>
                                <input type="text" class="form-control" id="address" placeholder="Dirección" name="direccion" v-model="client.address" v-validate="'required'" :class="{'is-invalid': errors.has('direccion') && submitted }"  autocomplete="off">
                                <span v-show="errors.has('email')" class="invalid-feedback">{{ errors.first('direccion') }}</span>
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
    export default {
        name: "Clients",
        data(){
            return{
                loading:true,
                submitted:false,
                titles:[
                    {label:"Nombre",cols:1},
                    {label:"Documento",cols:1},
                    {label:"Dirección",cols:1},
                    {label:"Teléfono",cols:1},
                    {label:"E-mail",cols:1},
                    {label:"Acciones",cols:2}
                ],
                data: {
                    data:[]
                },
                client:{
                    document_type:1
                }
            }
        },
        mounted(){
            this.getResults(1);
        },
        methods:{
            getResults(page=1){
                this.data.current_page=page;
                axios.get('/api/clients?page='+page,{
                    'headers': { 'Authorization': 'Bearer '+localStorage.getItem("access_token") }
                })
                    .then(response => {
                        this.loading = false;
                        this.data = response.data;
                    })
                    .catch(err => {
                        this.loading = false;
                        let dataError = err.response;
                        let message;
                        if(dataError.status == 401){
                            message = "Acceso denegado";
                        }else{
                            message = err.response.data.message;
                        }
                        createToastr("warning",message);
                    })
            },
            clearData(){
                this.client = {
                    id:0,
                    document_type:1
                };
            },
            addClient(){
                this.clearData();
                $("#modalClientsTitle").text("Añadir Cliente");
                this.openModal(1);
            },
            editClient(i){
                this.client = this.data.data[i];
                $("#modalClientsTitle").text("Editar Cliente");
                this.openModal(2,i)
            },
            openModal(type,i=false){
                this.submitted = false;
                $("#modalClients").modal("show");
            },
            sendData(){
                this.submitted = true;
                this.$validator.validateAll().then((result) => {
                    if (result){
                        let $self = this;
                        axios.post('/api/clients',$self.client,{
                            'headers': { 'Authorization': 'Bearer '+localStorage.getItem("access_token") }
                        }).then(response => {
                            if(response.data.success){
                                if($self.client.id < 1){
                                    $self.clearData();
                                }
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
            }
        }
    }
</script>

<style scoped>
    .form-group {
        margin-bottom: .5rem;
    }
</style>