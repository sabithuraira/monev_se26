@extends('layouts.admin')

@section('breadcrumb')
<ul class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{url('/dashboard')}}"><i class="icon-home"></i></a></li>   
    <li class="breadcrumb-item"><a href="{{url('/menu/master')}}">Menu MASTER</a></li>  
    <li class="breadcrumb-item"><a href="{{url('/master_kako')}}">Kabupaten/Kota</a></li>              
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
                    <template v-for="(data, index) in field_info.columns" :key="'form'+data">
                        <div v-if="data.form_generate && data.form_generate==true" class="col-lg-6 col-md-12">
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
        url_name: 'master_kako',
        url_php_name: {!! json_encode(url('api/master_kako')) !!},
        datas: [],
        field_info: {!! json_encode($field_info) !!},
        form: {},
        id_data:  {!! json_encode($id) !!},
        
        list_komoditas: [],
        list_kab: [],
    },
    //created dynamic form object
    created(){
        var self = this;

        self.getMaster();

        // self.field_info.field_form.forEach(function(item, idx) {
        self.field_info.columns.forEach(function(item, idx) {
            if(item.form_store && item.form_store==true){
                self.$set(self.form, item.name, '')
            }
        });

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
                    window.location.href ="{{ url('master_kako') }}"
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

                }
            }).fail(function (msg) {
                console.log(JSON.stringify(msg));
            });
        },
        getMaster: function(url){
            var self = this;
        },
    }
});
</script>
@endsection
