<template>
    <div class="container">
        <div class="card">
            <div class="card-header d-flex  flex-column flex-lg-row justify-content-between align-items-center">
                <h4 class="m-0">
                    Usuarios
                </h4>
                <button class="btn btn-dark" @click="addUser">
                    <i class="fas fa-user-plus"></i>
                    Agregar Usuario
                </button>
            </div>
            <div class="card-body table-responsive">
                <template v-if="data.data.length > 0">
                    <table  class="table table-sm table-striped  table-bordered">
                        <thead>
                        <th v-for="title in titles" :colspan="title.cols">
                            {{ title.label }}
                        </th>
                        </thead>
                        <tbody>
                        <tr v-for="(user,i) in data.data">
                            <td v-text="user.name"></td>
                            <td v-text="user.email"></td>
                            <td>
                                {{ user.status==1?"Activo":"Inactivo" }}
                            </td>

                            <td class="p-0 m-0" style="width:3%">
                                <button class="btn btn-link text-dark btn-tooltip" title="Editar" @click="editUser(i)"
                                        :disabled="user.status==2">
                                    <i class="fa fa-pencil-alt"></i>
                                </button>
                            </td>
                            <td class="p-0 m-0" style="width:3%">
                                <button class="btn btn-link text-dark btn-tooltip" title="Cambiar Clave" @click="editPass(i)" >
                                    <i class="fa fa-lock"></i>
                                </button>
                            </td>
                            <td class="p-0 m-0" style="width:3%">
                                <button class="btn btn-link text-dark btn-tooltip" :data-title="user.status==1?'Inactivar':'Activar'" @click="confirmToggleStatus(i)" >
                                    <i :class="user.status==1?'fa fa-ban':'fa fa-check'"></i>
                                </button>
                            </td>
                            <td class="p-0 m-0" style="width:3%">
                                <button class="btn btn-link text-dark btn-tooltip" title="Eliminar" @click="confirmDelete(i)" :disabled="user.status==2">
                                    <i class="fa fa-trash-alt"></i>
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
                        No se han encontrado usuarios.
                    </span>
                </template>
            </div>
        </div>
        <!-- Modal -->
        <div class="modal fade" id="modalUsers" tabindex="-1" role="dialog" aria-labelledby="modalUsersTitle" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalUsersTitle"></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="#" autocomplete="OFF">
                            <div v-if="user.modal_type!=3" class="form-group">
                                <b>Nombre:</b>
                                <input type="text" class="form-control " id="nombre" placeholder="Nombre" name="nombre" v-model="user.name" v-validate="'required'" :class="{'is-invalid': errors.has('nombre') && submitted }">
                                <span v-show="errors.has('nombre')" class="invalid-feedback">{{ errors.first('nombre') }}</span>

                            </div>
                            <div v-if="user.modal_type!=3" class="form-group">
                                <b>Email:</b>
                                <input type="text" class="form-control" id="email" placeholder="Email" name="email" v-model="user.email" v-validate="'required|email'" :class="{'is-invalid': errors.has('email') && submitted }"  autocomplete="off">
                                <span v-show="errors.has('email')" class="invalid-feedback">{{ errors.first('email') }}</span>
                            </div>
                            <div v-if="user.modal_type!=2"  class="form-group">
                                <b>Contraseña:</b>
                                <input type="password" class="form-control" id="password" placeholder="Contraseña" name="password" v-model="user.password" v-validate="'required'" :class="{'is-invalid': errors.has('password') && submitted }"  autocomplete="off">
                                <span v-show="errors.has('password')" class="invalid-feedback">{{ errors.first('password') }}</span>
                            </div>
                            <div v-if="user.modal_type!=2" class="form-group">
                                <b>Repetir Contraseña:</b>
                                <input type="password" class="form-control" id="password_confirm" placeholder="Repetir Contraseña" name="password_confirm" v-model="user.password_confirm" v-validate="'required'" :class="{'is-invalid': errors.has('password_confirm') && submitted }"  autocomplete="off">
                                <span v-show="errors.has('password_confirm')" class="invalid-feedback">{{ errors.first('password_confirm') }}</span>
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
    export default {
        name: "Users",
        data(){
            return{
                loading:true,
                submitted:false,
                titles:[
                    {label:"Nombre",cols:1},
                    {label:"E-mail",cols:1},
                    {label:"Estado",cols:1},
                    {label:"Acciones",cols:4}
                ],
                data: {
                    data:[]
                },
                user:{
                    id:0
                }
            }
        },
        mounted(){
            this.getResults(1);
        },
        methods:{
            getResults(page=1){
                this.data.current_page=page;
                axios.get('/api/users?page='+page,{
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
                this.user = {
                    id:0
                };
            },
            addUser(){
                this.clearData();
                $("#modalUsersTitle").text("Añadir Usuario");
                this.user.modal_type = 1;
                this.openModal(1);
            },
            editPass(i){
                this.user = this.data.data[i];
                $("#modalUsersTitle").text("Editar Contraseña");
                this.user.modal_type = 3;
                this.openModal()
            },
            editUser(i){
                this.user = this.data.data[i];
                $("#modalUsersTitle").text("Editar Usuario");
                this.user.modal_type = 2;
                this.openModal()
            },
            openModal(){
                this.submitted = false;
                $("#modalUsers").modal("show");
            },
            sendData(){
                this.submitted = true;
                this.$validator.validateAll().then((result) => {
                    if (result){
                        let $self = this;
                        if($self.user.password != $self.user.password_confirm){
                            createToastr("warning","Las contraseñas no coinciden.");
                            return false;
                        }
                        axios.post('/api/users',$self.user,{
                            'headers': { 'Authorization': 'Bearer '+localStorage.getItem("access_token") }
                        }).then(response => {
                            if(response.data.success){
                                if($self.user.id < 1){
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
            },
            confirmDelete(i){
                Swal.fire({
                    title: 'Se eliminara el usuario "' + this.data.data[i].name + '"',
                    text: "Deseas continuar?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'Si, borrar',
                    cancelButtonColor: '#d33',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true,
                    showLoaderOnConfirm: true ,
                    preConfirm: () => {
                        this.deleteClient(i);
                    }
                });
            },
            deleteClient(i){
                let $self = this;
                axios.delete('/api/users/'+$self.data.data[i].id,
                    {'headers': { 'Authorization': 'Bearer '+localStorage.getItem("access_token") }}
                )
                    .then(response => {
                        if(response.data.success){
                            $self.getResults();
                            createToastr("success",response.data.message);
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
            },
            confirmToggleStatus(i){
                let status = this.data.data[i].status;
                let title = status==1?"Inactivar el usuario ":"Activar el usuario ";
                    title +=this.data.data[i].name+" ?";
                let button = "Si, "+(status==1?"Inactivar":"Activar");
                Swal.fire({
                    title: title,
                    text: "Deseas continuar?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: button,
                    cancelButtonColor: '#d33',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true,
                    showLoaderOnConfirm: true ,
                    preConfirm: () => {
                        this.toggleStatus(i);
                    }
                });
            },
            toggleStatus(i){
                let $self = this;
                axios.put('/api/users/'+$self.data.data[i].id,{},
                    {'headers': { 'Authorization': 'Bearer '+localStorage.getItem("access_token") }}
                )
                    .then(response => {
                        if(response.data.success){
                            $self.getResults();
                            createToastr("success",response.data.message);
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
        }
    }
</script>

<style scoped>
    .form-group {
        margin-bottom: .5rem;
    }
</style>