@extends('seller.layouts.app')

@section('panel_content')
    <div class="aiz-titlebar mt-2 mb-4">
      <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{ translate('Shop Verification')}}</h1>
        </div>
      </div>
    </div>
    <form action="{{ route('seller.shop.improve.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0 h6">{{ translate('Verification info')}}</h4>
            </div>
            <div class="card-body">

                <div class="row mb-3">
                    <div class="col-md-2">
                        <label>{{ translate('Shop Name')}} <span class="text-danger">*</span></label>
                    </div>
                    <div class="col-md-10">
                        <input type="text" class="form-control" placeholder="{{ translate('Shop Name')}}" value="{{$shop['name'] ?? ''}}" name="name" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-2">
                        <label>{{ translate('Address')}} <span class="text-danger">*</span></label>
                    </div>
                    <div class="col-md-10">
                        <input type="text" class="form-control" placeholder="{{ translate('Address')}}" value="{{$shop['address'] ?? ''}}" name="address" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-2">
                        <label>{{ translate('Certificates Type')}} <span class="text-danger">*</span></label>
                    </div>
                    <div class="col-md-10">
                        <select class="form-control" name="certtype">
                            <option value="idcard" @if(isset($shop['certtype']) && $shop['certtype']=='idcard')selected @endif> {{translate('id card')}}</option>
                            <option value="passport" @if(isset($shop['certtype']) && $shop['certtype']=='passport')selected @endif> {{translate('passport')}}</option>
                            <option value="driving license" @if(isset($shop['certtype']) && $shop['certtype']=='driving license')selected @endif> {{translate('driving license')}}</option>
                            <option value="social security card" @if(isset($shop['certtype']) && $shop['certtype']=='social security card')selected @endif> {{translate('Social Security Card')}}</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-2">
                        <label>{{ translate('Certificates Front')}} <span class="text-danger">*</span></label>
                    </div>
                    <div class="col-md-10">
                        <div class="input-group" data-toggle="aizuploader" data-type="image">
                            <div class="input-group-prepend">
                                <div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse')}}</div>
                            </div>
                            <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                            <input type="hidden" name="identity_card_front" value="{{$shop['identity_card_front']}}" class="selected-files">
                        </div>
                        <div class="file-preview box sm mt-2">
                            <img height="120" src="{{ uploaded_asset($shop['identity_card_front']) }}">
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-2">
                        <label class="col-from-label">{{ translate('Certificates Back')}} <span class="text-danger">*</span></label>
                    </div>
                    <div class="col-md-10">
                        <div class="input-group" data-toggle="aizuploader" data-type="image">
                            <div class="input-group-prepend">
                                <div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse')}}</div>
                            </div>
                            <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                            <input type="hidden" name="identity_card_back" value="{{$shop['identity_card_back']}}" class="selected-files">
                        </div>
                        <div class="file-preview box sm mt-2">
                            <img height="120" src="{{ uploaded_asset($shop['identity_card_back']) }}">
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-2">
                        <label class="col-from-label">{{ translate('Approve Status') }}</label>
                    </div>
                    <div class="col-md-10">
                        @if($shop['verification_status']==2)
                            <span class="form-control">{{ translate('Disapproved') }}</span>
                        @elseif($shop['verification_status']==0)
                            <span class="form-control">{{ translate('Under Review') }}</span>
                        @else
                            <span class="form-control">{{ translate('Shop Approved') }}</span>
                        @endif
                    </div>
                </div>

                @if($shop['verification_status']==2)
                    <div class="row mb-3">
                        <div class="col-md-2">
                            <label class="col-from-label">{{ translate('Approve Remark') }}</label>
                        </div>
                        <div class="col-md-10">
                            <span class="form-control">{{$shop['verification_note'] ?? ''}}</span>
                        </div>
                    </div>
                @endif

                @if($shop['verification_status'] == 2)
                <div class="text-right mt-4">
                    <button type="submit" class="btn btn-primary">{{ translate('Submit')}}</button>
                </div>
                @endif
            </div>
        </div>
    </form>
@endsection
