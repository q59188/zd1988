<div class="modal-header">
  <h5 class="modal-title h6">{{translate('Seller Message')}}</h5>
  <button type="button" class="close" data-dismiss="modal">
  </button>
</div>
<div class="modal-body">
     @if (empty($data))
         
    @elseif ($seller_withdraw_request->w_type == 2)
    <div class="from-group row">
        <div class="col-lg-10">
            银行名称: {{$data->bank_name}}
        </div>
    </div>
    <div class="from-group row">
        <div class="col-lg-10">
            银行卡号: {{$data->bank_num}}
        </div>
    </div>
     <div class="from-group row">
        <div class="col-lg-10">
            姓名: {{$data->name}}
        </div>
    </div>
    @elseif ($seller_withdraw_request->w_type == 3)
     <div class="from-group row">
        <div class="col-lg-10">
            提币网络: {{$data->coin_type}}
        </div>
    </div>
    <div class="from-group row">
        <div class="col-lg-10">
            提币地址: {{$data->address}}
        </div>
    </div>
    @endif
    <div class="from-group row" style="margin-top:20px;">
        <div class="col-lg-2">
            <label>{{translate('Message')}}</label>
        </div>
        
        <div class="col-lg-10">
            <textarea name="meta_description" rows="8" class="form-control">{{ $seller_withdraw_request->message }}</textarea>
        </div>
    </div>
   
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-light" data-dismiss="modal">{{translate('Cancel')}}</button>
</div>
