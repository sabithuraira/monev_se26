@extends('layouts.admin')

@section('breadcrumb')
<ul class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{url('/dashboard')}}"><i class="icon-home"></i></a></li>     
    <li class="breadcrumb-item"><a href="{{url('/menu/master')}}">Menu MASTER</a></li>                
    <li class="breadcrumb-item">Kabupaten/Kota</li>
</ul>
@endsection

@section('content')
    <div class="container" id="app_vue">
      <div class="card">
        <div class="body">

            <div class="row mb-3">
                <div class="col-lg-12">
                    <a href="{{ url('master_kako/create') }}" class="btn btn-info">Tambah Data</a>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-lg-6">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" v-model="filter.keyword" placeholder="Search.." @keyup.enter="setDatas(url_name)">
                        <div class="input-group-append">
                            <button class="btn btn-info" type="button" @click="setDatas(url_name)"><i class="fa fa-search"></i></button>
                        </div>
                    </div>
                </div>
            </div>

            <section class="datas">
                <div class="table-responsive">
                    <table class="table table-bordered m-b-0"  style="min-width:100%">
                        <thead>
                            <tr class="text-center">
                                <th>No</th>
                                <template v-for="(data, index) in field_info.columns" :key="data">
                                    <th v-if="data.table_display && data.table_display==true">
                                        @{{ data.label }}   
                                    </th>
                                </template>
                                <th>Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="(data, index) in datas" :key="data.id">
                                <td>@{{ index+1 }}</td>     
                                <template v-for="(data_key, index_key) in field_info.columns" :key="data_key+data.id">
                                    <td v-if="data_key.table_display && data_key.table_display==true">
                                        @{{ data[data_key.name] }}   
                                    </td>    
                                </template>    
                                <td class="text-center">
                                    <a :href="urlEdit(data.encId)"><i class="icon-pencil"></i></a>
                                    &nbsp
                                    <a @click="deleteData(data.encId)"><i class="fa fa-trash text-danger"></i>&nbsp </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="row my-4">
                        <div class="col-lg-12">
                            <ul class="pagination float-right">
                                <li v-for="l in links" class="page-item"
                                    :class="[l.active ? 'active' : '', l.url ? '' : 'disabled']">
                                    <a class="page-link" @click="setDatas(l.url)"><span v-html="l.label"></span></a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </section>
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
        links: [],
        field_info: {!! json_encode($field_info) !!},
        filter: { keyword: '' },
    },
    created(){
        var self = this;
        self.getMaster();
    },
    methods: {
        urlEdit: function(dataId){
            return {!! json_encode(url('/master_kako')) !!} + `/${dataId}/edit`
        },
        getMaster: function(url){
            var self = this;

        },
        setDatas: function(url){
            var self = this;
            $('#wait_progres').modal('show');

            $.ajax({
                url : "{{ url('api/') }}" + `/${url}`,
                method : 'get', dataType: 'json',
                beforeSend: function (xhr) {
                    xhr.setRequestHeader('Authorization', "Bearer {{ Auth::user()->remember_token }}");
                },
                data:{ 
                    keyword: self.filter.keyword, 
                },
            }).done(function (data) {
                self.datas = data.datas.data;
                self.links = data.datas.links;
                $('#wait_progres').modal('hide');
            }).fail(function (msg) {
                console.log(JSON.stringify(msg));
                $('#wait_progres').modal('hide');
            });
        },
        deleteData: function (data_id) {
            if (confirm('Anda yakin ingin menghapus data ini?')) {
                var self = this;

                let submitUrl = self.url_php_name + `/${data_id}`
                $('#wait_progres').modal('show');
              
                $.ajax({
                    url :  submitUrl, method : 'delete', dataType: 'json',
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader('Authorization', "Bearer {{ Auth::user()->remember_token }}");
                    },
                }).done(function (data) {
                    $('#wait_progress').modal('hide');
                    self.setDatas(self.url_name);
                }).fail(function (msg) {
                    console.log(JSON.stringify(msg));
                    $('#wait_progres').modal('hide');
                });
            }
        },
    }
});

$(document).ready(function() {
    vm.setDatas(vm.url_name);
});
</script>
@endsection
