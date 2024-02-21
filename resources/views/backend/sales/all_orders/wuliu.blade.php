@extends('backend.layouts.app')

@section('content')
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h1 class="mb-0 h6">{{translate('General Settings')}}</h1>
                </div>
                <div class="card-body">
                    <form class="form-horizontal" action="{{ route('wuliu_orders') }}" method="POST"
                          enctype="multipart/form-data" id="wuliu-form">
                        @csrf
                        <div class="form-list">
                            <div class="form-group row">
                                <div class="col-sm-9">
                                    <div class="fx-1">
                                        <div class="">文字信息</div>
                                    </div>
                                    <div class="fx-1">
                                        <div class="">时间间隔(按起始时间开始 N分钟 之后显示)</div>
                                    </div>
                                </div>
                            </div>
                            @foreach ($data as $key => $value )
                            <div class="form-group row">
                                <div class="col-sm-9">
                                    <div class="fx-1">
                                        <input type="text" name="text[]" class="form-control form-control-text" value="{{$value->text}}">
                                    </div>
                                    <div class="fx-1">
                                        <input type="text" name="time[]" class="form-control form-control-time" value="{{$value->time}}">
                                        <input type="button" value="+" onclick="addinfo()" class="btn btn-primary btn-add">
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="text-right">
    						<button type="submit" class="btn btn-primary">{{ translate('Update') }}</button>
    					</div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <style>
        #wuliu-form .form-control-text {
            width: 100%;
            display: inline-block;
        }
        #wuliu-form .form-control-time {
            margin-left: 10px;
            width: 100px;
            display: inline-block;
        }
        #wuliu-form .row .col-sm-9 {
            display: flex;
        }
        #wuliu-form .row .col-sm-9 .fx-1 {
            flex: 1;
        }
        .ssss {
            width: 60px;
            height: 60px;
            background: #fd720f;
            display: inline-block;
        }
    </style>
    <script type="text/javascript">
        function addinfo() {
            var a = '<div class="form-group row">' +
                '<div class="col-sm-9">' +
                    '<div class="fx-1">' +
                        '<input type="text" name="text[]" class="form-control form-control-text" value="">' +
                    '</div>' +
                    '<div class="fx-1">' +
                        '<input type="text" name="time[]" class="form-control form-control-time" value="">' +
                        '<input type="button" value="+" onclick="addinfo()" class="btn btn-primary btn-add">' +
                    '</div>' +
                '</div>' +
            '</div>'
            $('.form-list').append(a)
        }
        $(function(){
            
        })
       
    </script>
@endsection
