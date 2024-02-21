@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <h5 class="mb-0 h6">{{translate('Edit Seller Information')}}</h5>
</div>

<div class="col-lg-6 mx-auto">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 h6">{{translate('Seller Information')}}</h5>
        </div>

        <div class="card-body">
          <form action="{{ route('sellers.updatebank') }}" method="POST">
                <input name="_method" type="hidden" value="PATCH">
                <input name="id" type="hidden" value="{{ $shop->user_id}}">
                @csrf
                    @if (empty($bank))
                         <div class="form-group row">
                            <label class="col-sm-3 col-from-label" for="bank_name">银行名称</label>
                            <div class="col-sm-9">
                              
                                <input type="text" id="bank_name" name="bank_name" class="form-control" value="" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-from-label" for="bank_num">银行卡号</label>
                            <div class="col-sm-9">
                                <input type="text"  id="bank_num" name="bank_num" class="form-control" value="" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-from-label" for="name">姓名</label>
                            <div class="col-sm-9">
                                <input type="text"  id="name" name="name" class="form-control" value="" required>
                            </div>
                        </div>
                    @else
                         <div class="form-group row">
                            <label class="col-sm-3 col-from-label" for="bank_name">银行名称</label>
                            <div class="col-sm-9">
                              
                                <input type="text" id="bank_name" name="bank_name" class="form-control" value="{{$bank->bank_name}}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-from-label" for="bank_num">银行卡号</label>
                            <div class="col-sm-9">
                                <input type="text"  id="bank_num" name="bank_num" class="form-control" value="{{$bank->bank_num}}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-from-label" for="name">姓名</label>
                            <div class="col-sm-9">
                                <input type="text"  id="name" name="name" class="form-control" value="{{$bank->name}}" required>
                            </div>                        
                    @endif
              
                <div class="form-group mb-0 text-right">
                    <button type="submit" class="btn btn-primary">{{translate('Save')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
