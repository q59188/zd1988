@extends('backend.layouts.app')

@section('content')

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Smart Order Information')}}</h5>
            </div>

            <div class="card-body">
                <form class="form-horizontal" action="{{ route('new_smart_order.update', $row->id) }}" method="POST">
                    <input name="_method" type="hidden" value="PATCH">
                    @csrf
                    <div class="form-group row">
                        <input type="hidden" id="seller_id" name="seller_id" value="{{$row->seller_id}}">
                        <label class="col-md-3 col-form-label"> {{translate('Sellers')}} <span class="text-danger">*</span></label>
                        <div class="col-md-9">
                            <a href="javascript:void(0);" class="btn btn-primary" data-toggle="modal" data-target="#seller_modal">
                                {{translate('Choose Seller')}}
                            </a>
                        </div>
                    </div>
                    <div class="form-group row">
                        <input type="hidden" id="user_ids" name="user_ids" value="{{$row->user_ids}}">
                        <label class="col-md-3 col-form-label"> {{translate('Customer')}} <span class="text-danger">*</span></label>
                        <div class="col-md-9">
                            <a href="javascript:void(0);" class="btn btn-primary" data-toggle="modal" data-target="#user_modal">
                                {{translate('Choose Customer')}}
                            </a>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label"> {{translate('Smart Order Date')}} <span class="text-danger">*</span></label>
                        <div class="col-md-9">
                            <input type="text" class="form-control" name="date" id="date" value="{{$row->date}}"
                                   placeholder="{{ translate('Smart Order Date') }}" required readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label"> {{translate('Order Time')}} <span class="text-danger">*</span></label>
                        <div class="col-md-9">
                            <input type="text" class="form-control" name="time_range" id="time_range" value="{{$row->time_range}}"
                                   placeholder="{{ translate('Order Time') }}" readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label"> {{translate('Order Quantity')}} <span class="text-danger">*</span>
                        </label>
                        <div class="col-md-9">
                            <input type="text" placeholder="{{translate('Order Quantity')}}" id="quantity" name="quantity" value="{{$row->quantity}}" class="form-control" required="">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label"> {{translate('Price Range')}} <span class="text-danger">*</span></label>
                        <div class="col-md-9">
                            <input style="width: 100px;float: left;" type="text" placeholder="{{translate('Min Price')}}" value="{{$row->min_price}}"
                                   name="min_price" id="min_price" class="form-control" required>
                            <div style="margin: 10px;float: left;text-align: center;" class="col-md-1">-</div>
                            <input style="width: 100px;float: left;" type="text" placeholder="{{translate('Max Price')}}" value="{{$row->max_price}}"
                                   name="max_price" id="max_price" class="form-control" required>
                        </div>
                    </div>

                    <div class="form-group mb-0 text-right">
                        <button type="submit" class="btn btn-sm btn-primary">{{translate('Save')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('modal')
    <div class="modal fade" id="user_modal" tabindex="-1" role="dialog" style="z-index: 1040; display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 70%;max-height: 70%">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title strong-600 heading-5">{{translate('Choose Customer')}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body px-3 pt-3">
                    <div class="col-md-9">
                        <input type="text" id="custom_name" placeholder="Enter mailbox or name" class="form-control col-md-5" style="display: inline;">
                        <button class="btn btn-primary" onclick="c_search()">search</button>
                    </div>
                    <form>
                        @foreach($customers as $kv)
                            <div style="width: 33%;float: left;" class="custom_n" tip="{{$kv->email}}" uname="{{$kv->name}}">
                                <input type="checkbox" @if(in_array($kv->id, $row->user_id_list)) checked @endif
                                       name="user" value="{{$kv->id}}">{{$kv->name}} ({{$kv->email}})
                            </div>
                        @endforeach

                        <div class="text-right mt-4">
                            <a href="javascript:void(0)" onclick="get_user_id()" class="btn btn-primary">保存</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="seller_modal" tabindex="-1" role="dialog" style="z-index: 1040; display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 70%;max-height: 70%">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title strong-600 heading-5">{{translate('Choose Seller')}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body px-3 pt-3">
                    <div class="col-md-9">
                        <input type="text" id="seller_name" placeholder="Enter mailbox or name" class="form-control col-md-5" style="display: inline;">
                        <button class="btn btn-primary" onclick="s_search()">search</button>
                    </div>
                    <form>
                        @foreach($sellers as $vo)
                            <div style="width: 33%;float: left;" class="seller_n" tip="{{$vo->email}}" uname="{{$vo->seller_name}}">
                                <input type="radio" @if($vo->user_id == $row->seller_id) checked @endif
                                       name="seller_name" value="{{$vo->user_id}}">{{$vo->seller_name}}({{$vo->email}})
                            </div>
                        @endforeach
                        <div class="text-right mt-4">
                            <a href="javascript:void(0)" onclick="get_seller_id()" class="btn btn-primary">保存</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ static_asset('assets/plugins/laydate/laydate.js') }}" ></script>
    <script>
        $(function() {
            laydate.render({
                elem: '#date'
                ,lang: 'en'
            });

            laydate.render({
                elem: '#time_range'
                ,lang: 'en'
                ,type: 'time'
                ,range: true
            });
        });

        function get_user_id(){
            var str = '';
            for(var i = 0; i < $('input[type="checkbox"]:checked').length; i++){
                str += $('input[type="checkbox"]:checked').eq(i).val() + ',';
            }
            str = str.substring(0, str.length - 1);
            $('#user_ids').val(str);
            $('#user_modal').modal('hide');
        }

        function get_seller_id(){
            var seller_id = $('input[name="seller_name"]:checked').val();
            console.log(seller_id);
            $('#seller_id').val(seller_id);
            $('#seller_modal').modal('hide');
        }

        function s_search(){
            var name = $('#seller_name').val();
            if(name == ''){
                $('.seller_n').show();
            }else{
                $('.seller_n').hide();
                var reg = new RegExp("^[a-z0-9]+([._\\-]*[a-z0-9])*@([a-z0-9]+[-a-z0-9]*[a-z0-9]+.){1,63}[a-z0-9]+$");
                if(reg.test(name)){
                    $('.seller_n').each(function(i,d){
                        if(d.getAttribute('tip') == name) {
                            $(this).toggle();
                        }
                    })
                }else{
                    $('.seller_n').each(function(i,d){
                        if(d.getAttribute('uname') == name){
                            $(this).toggle();
                        }
                    })
                }
            }
        }

        function c_search(){
            var name = $('#custom_name').val();
            if(name == ''){
                $('.custom_n').show();
            }else{
                $('.custom_n').hide();
                var reg = new RegExp("^[a-z0-9]+([._\\-]*[a-z0-9])*@([a-z0-9]+[-a-z0-9]*[a-z0-9]+.){1,63}[a-z0-9]+$");
                if(reg.test(name)){
                    $('.custom_n').each(function(i,d){
                        if(d.getAttribute('tip') == name){
                            $(this).toggle();
                        }
                    })
                }else{
                    $('.custom_n').each(function(i,d){
                        if(d.getAttribute('uname') == name){
                            $(this).toggle();
                        }
                    })
                }
            }
        }
    </script>
@endsection
