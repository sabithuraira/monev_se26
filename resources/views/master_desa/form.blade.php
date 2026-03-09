@extends('layouts.admin')

@section('breadcrumb')
<ul class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{url('/dashboard')}}"><i class="icon-home"></i></a></li>  
    <li class="breadcrumb-item"><a href="{{url('/menu/master')}}">Menu MASTER</a></li>   
    <li class="breadcrumb-item"><a href="{{url('/master_desa')}}">Desa/Kelurahan</a></li>              
    <li class="breadcrumb-item">Perbarui Data</li>
</ul>
@endsection

@section('content')
<div class="container" id="app_vue">
    <div class="card">
        <div class="body">
            <div class="input-group mb-3">
                <h6 v-if="id_data==''">Tambah Data</h6>
                <h6 v-else>Perbaharui Data</h6>
            </div>

            <section class="form">
                <div class="row clearfix">
                    <!-- Kode Kabupaten Dropdown -->
                    <div class="col-lg-6 col-md-12">
                        <div class="form-group">
                            <label>Kode Kabupaten/Kota :</label>
                            <select class="form-control" v-model="form.id_kab" @change="loadMasterKec">
                                <option value="">Pilih Kabupaten/Kota</option>
                                <option v-for="kab in master_kako_list" :key="kab.id" :value="kab.kode_kab">
                                    @{{ kab.kode_kab }} - @{{ kab.nama_kab }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Kode Kecamatan Dropdown -->
                    <div class="col-lg-6 col-md-12">
                        <div class="form-group">
                            <label>Kode Kecamatan :</label>
                            <select class="form-control" v-model="form.id_kec" :disabled="!form.id_kab || master_kec_list.length === 0">
                                <option value="">Pilih Kecamatan</option>
                                <option v-for="kec in master_kec_list" :key="kec.id" :value="kec.kode_kec">
                                    @{{ kec.kode_kec }} - @{{ kec.nama_kec }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <template v-for="(data, index) in field_info.columns" :key="'form'+data">
                        <!-- Hide id_prov and id_kab, id_kec fields, they are handled by dropdowns -->
                        <div v-if="data.form_generate && data.form_generate==true && data.name != 'id_prov' && data.name != 'id_kab' && data.name != 'id_kec'" class="col-lg-6 col-md-12">
                            <div class="form-group">
                                <label>@{{ data.label }} :</label>
                                <input type="text" class="form-control" v-model="form[data.name]">
                            </div>
                        </div>
                    </template>
                </div>

                <div class="input-group mb-3">
                    <button type="button" class="btn btn-primary" @click="saveData">SAVE</button>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script type="text/javascript" src="{{ URL::asset('js/app.js') }}"></script>
<script>
    
var vm = new Vue({  
    el: "#app_vue",
    data:  {
        url_name: 'master_desa',
        url_php_name: {!! json_encode(url('api/master_desa')) !!},
        datas: [],
        field_info: {!! json_encode($field_info) !!},
        form: {},
        id_data:  {!! json_encode($id) !!},
        
        list_komoditas: [],
        list_kab: [],
        master_kako_list: [],
        master_kec_list: [],
    },
    //created dynamic form object
    created(){
        var self = this;

        self.getMaster();
        self.loadMasterKako();

        // self.field_info.field_form.forEach(function(item, idx) {
        self.field_info.columns.forEach(function(item, idx) {
            if(item.form_store && item.form_store==true){
                self.$set(self.form, item.name, '')
            }
        });
        
        // Automatically set id_prov to "16"
        if(self.form.id_prov === '' || !self.form.id_prov){
            self.form.id_prov = '16';
        }

        if(self.id_data!=''){
            self.setDatas();
        }

    },
    methods: {
        saveData(){
            var self = this;
            $('#wait_progres').modal('show');

            let submit_info = {
                url: self.url_php_name,
                method: 'post'
            };

            if(self.id_data!=''){
                submit_info = {
                    url: self.url_php_name + '/' + self.id_data,
                    method: 'patch'
                };
            }

            let formData = {};
            for (let key in self.form) {
                if (self.form.hasOwnProperty(key)) {
                    formData[key] = self.form[key];
                }
            }
            
            // Automatically set id_prov to "16" before saving
            formData.id_prov = '16';

            $.ajax({
                url : submit_info.url,
                method : submit_info.method, dataType: 'json',
                beforeSend: function (xhr) {
                    xhr.setRequestHeader('Authorization', "Bearer {{ Auth::user()->remember_token }}");
                },
                data: formData,
            }).done(function (data) {
                if(data.status=='error'){
                    let errArray = [];
                    for (let key in self.form) {
                        if (data.data.hasOwnProperty(key)) {
                            errArray = [...errArray, ...data.data[key]]
                        }
                    }
                    alert(errArray.join('\n'))
                }
                else{
                    window.location.href ="{{ url('master_desa') }}"
                }
                $('#wait_progres').modal('hide');
            }).fail(function (msg) {
                console.log(JSON.stringify(msg));
                $('#wait_progres').modal('hide');
            });
        },
        setDatas: function(){
            var self = this;
            $.ajax({
                url : self.url_php_name + '/' + self.id_data,
                method : 'get', dataType: 'json',
                beforeSend: function (xhr) {
                    xhr.setRequestHeader('Authorization', "Bearer {{ Auth::user()->remember_token }}");
                },
            }).done(function (data) {
                if(data.status=='success'){
                    
                    self.field_info.columns.forEach(function(item, idx) {
                        if(item.form_store && item.form_store==true){
                            self.form[item.name] = data.datas[item.name];
                        }
                    });
                    
                    // Ensure id_prov is "16" when editing
                    self.form.id_prov = '16';
                    
                    // Load kecamatan list when editing
                    if(self.form.id_kab){
                        self.loadMasterKec();
                    }

                }
            }).fail(function (msg) {
                console.log(JSON.stringify(msg));
            });
        },
        getMaster: function(url){
            var self = this;
        },
        loadMasterKako: function(){
            var self = this;
            $.ajax({
                url: "{{ url('api/master_kako') }}",
                method: 'get',
                dataType: 'json',
                beforeSend: function (xhr) {
                    xhr.setRequestHeader('Authorization', "Bearer {{ Auth::user()->remember_token }}");
                },
                data: {
                    per_page: 1000 // Get all records
                }
            }).done(function (data) {
                if (data.status === 'success' && data.datas && data.datas.data) {
                    self.master_kako_list = data.datas.data;
                }
            }).fail(function (msg) {
                console.log('Error loading master_kako:', JSON.stringify(msg));
            });
        },
        loadMasterKec: function(){
            var self = this;
            if(!self.form.id_kab){
                self.master_kec_list = [];
                self.form.id_kec = '';
                return;
            }
            
            $.ajax({
                url: "{{ url('api/master_kec') }}",
                method: 'get',
                dataType: 'json',
                beforeSend: function (xhr) {
                    xhr.setRequestHeader('Authorization', "Bearer {{ Auth::user()->remember_token }}");
                },
                data: {
                    kode_kab: self.form.id_kab,
                    per_page: 1000 // Get all records
                }
            }).done(function (data) {
                if (data.status === 'success' && data.datas && data.datas.data) {
                    self.master_kec_list = data.datas.data;
                } else {
                    self.master_kec_list = [];
                }
                // Reset kecamatan selection when kabupaten changes
                self.form.id_kec = '';
            }).fail(function (msg) {
                console.log('Error loading master_kec:', JSON.stringify(msg));
                self.master_kec_list = [];
                self.form.id_kec = '';
            });
        },
    }
});
</script>
@endsection


