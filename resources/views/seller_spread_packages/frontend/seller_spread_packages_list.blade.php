@extends('seller.layouts.app')

@section('panel_content')
<section class="py-8 bg-soft-primary">
    <div class="container">
        <div class="row">
            <div class="col-xl-8 mx-auto text-center">
                <h1 class="mb-0 fw-700">{{ translate('Premium Packages for Sellers') }}</h1>
            </div>
        </div>
    </div>
</section>

<section class="py-4 py-lg-5">
    <div class="container">
        <div class="row row-cols-xxl-4 row-cols-lg-3 row-cols-md-2 row-cols-1 gutters-10 justify-content-center">
            @foreach ($seller_spread_packages as $key => $seller_spread_package)
                <div class="col">
                    <div class="card overflow-hidden">
                        <div class="card-body">
                            <div class="text-center mb-4 mt-3">
                                <img class="mw-100 mx-auto mb-4" src="{{ uploaded_asset($seller_spread_package->logo) }}" height="100">
                                <h5 class="mb-3 h5 fw-600">{{$seller_spread_package->getTranslation('name')}}</h5>
                            </div>
                            <ul class="list-group list-group-raw fs-15 mb-5">
                                <li class="list-group-item py-2">
                                    <i class="las la-check text-success mr-2"></i>
                                    {{ $seller_spread_package->product_upload_limit }} {{translate('Product Spread Limit')}}
                                </li>
                                <li class="list-group-item py-2">
                                    <i class="las la-check text-success mr-2"></i>
                                    {{ $seller_spread_package->getTranslation('max_profit')}}
                                </li>
                            </ul>
                            <div class="mb-5 d-flex align-items-center justify-content-center">
                                @if ($seller_spread_package->amount == 0)
                                    <span class="display-4 fw-600 lh-1 mb-0">{{ translate('Free') }}</span>
                                @else
                                    <span class="display-4 fw-600 lh-1 mb-0">{{single_price($seller_spread_package->amount)}}</span>
                                @endif
                                <span class="text-secondary border-left ml-2 pl-2">{{$seller_spread_package->duration}}<br>{{translate('Days')}}</span>
                            </div>

                            <div class="text-center">
                                @if ($seller_spread_package->amount == 0)
                                    <button class="btn btn-primary fw-600" onclick="get_free_package({{ $seller_spread_package->id}})">{{ translate('Free Package')}}</button>
                                @else
                                    @if (addon_is_activated('offline_payment'))
                                        <button class="btn btn-primary fw-600" onclick="select_payment_type({{ $seller_spread_package->id}},'{{ translate('Pay with wallet')}}','{{single_price($seller_spread_package->amount)}}')">{{ translate('Purchase Package')}}</button>
                                    @else
                                        <button class="btn btn-primary fw-600" onclick="show_price_modal({{ $seller_spread_package->id}})">{{ translate('Purchase Package')}}</button>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

@endsection

@section('modal')


<!-- Payment For Storehouse Modal -->
    <div class="modal fade" id="payment_for_storehouse_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">{{ translate('Payment For Storehouse') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    
                    
                    
                 
											<option value="paypal">{{translate('Paypal')}}</option>
				 
                    <div class="row">
                        <div class="col-12">
                            <h5 class="text-center" id="ceshi111">{{ translate('Pay with wallet')}}

                            <!--@php

                            $user = \App\Models\User::find(Auth::user()->id);
                            @endphp
                            -->
                            {{single_price($user->balance)}}</h5>
                        </div>
                    </div>
                    <div class="row">

                                <div class="col-md-9">
                                    <input type="password" lang="en" class="form-control mb-3" id="tpwd" name="tpwd"
                                   placeholder="{{ translate('Transaction password') }}" max=6 required>
                                </div>
                    </div>
                    <div class="form-group text-right">
                        <button type="button" class="btn btn-sm btn-light transition-3d-hover mr-3" data-dismiss="modal">{{translate('Cancel')}}</button>
                        <button id="payment_button" type="button" class="btn btn-sm btn-primary transition-3d-hover mr-1">{{translate('Payment')}}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>


  <!-- Select Payment Type Modal -->
  <div class="modal fade" id="select_payment_type_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content">
              <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalLabel">{{ translate('Select Payment Type') }}</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                  <input type="hidden" id="package_id" name="package_id" value="">
                  <div class="row">
                      <div class="col-md-3">
                          <label>{{ translate('Payment Type')}}</label>
                      </div>
                      <div class="col-md-9">
                          <div class="mb-3">
                              <select class="form-control aiz-selectpicker" onchange="payment_type(this.value)"
                                      data-minimum-results-for-search="Infinity">
                                  <option value="">{{ translate('Select One')}}</option>
                                  <option value="cash">{{ translate('Wallet')}}</option>
                                  <option value="online">{{ translate('Online payment')}}</option>
                                  <option value="offline">{{ translate('Offline payment')}}</option>

                              </select>
                          </div>
                      </div>

                  </div>
                  <div class="form-group text-right">
                      <button type="button" class="btn btn-sm btn-primary transition-3d-hover mr-1" id="select_type_cancel" data-dismiss="modal">{{translate('Cancel')}}</button>
                  </div>
              </div>
          </div>
      </div>
  </div>

  <!-- Online payment Modal-->
  <div class="modal fade" id="price_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content">
              <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalLabel">{{ translate('Purchase Your Package') }}</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
              </div>
              <form class="" id="package_payment_form" action="{{ route('seller_spread_packages.purchase') }}" method="post">
                  @csrf
                  <input type="hidden" name="seller_spread_package_id" value="">
                  <div class="modal-body">
                      <div class="row">
                          <div class="col-md-2">
                              <label>{{translate('Payment Method')}}</label>
                          </div>
                          <div class="col-md-10">
								<div class="mb-3">
									<select class="form-control aiz-selectpicker" data-live-search="true" name="payment_option">
										@if (get_setting('paypal_payment') == 1)
											<option value="paypal">{{translate('Paypal')}}</option>
										@endif
										@if (get_setting('stripe_payment') == 1)
											<option value="stripe">{{translate('Stripe')}}</option>
										@endif
										@if (get_setting('mercadopago_payment') == 1)
                                            <option value="mercadopago">{{ translate('Mercadopago')}}</option>
                                            <option value="paypal">{{ translate('Paypal')}}</option>
                                        @endif
                                        @if(get_setting('toyyibpay_payment') == 1)
                                            <option value="toyyibpay">{{ translate('ToyyibPay')}}</option>
                                        @endif
                                        @if(get_setting('sslcommerz_payment') == 1)
                                            <option value="sslcommerz">{{ translate('sslcommerz')}}</option>
                                        @endif
                                        @if(get_setting('instamojo_payment') == 1)
                                            <option value="instamojo">{{ translate('Instamojo')}}</option>
                                        @endif
                                        @if(get_setting('razorpay') == 1)
                                            <option value="razorpay">{{ translate('RazorPay')}}</option>
                                        @endif
                                        @if(get_setting('paystack') == 1)
                                            <option value="paystack">{{ translate('PayStack')}}</option>
                                        @endif
                                        @if(get_setting('payhere') == 1)
                                            <option value="payhere">{{ translate('Payhere')}}</option>
                                        @endif
                                        @if(get_setting('ngenius') == 1)
                                            <option value="ngenius">{{ translate('Ngenius')}}</option>
                                        @endif
                                        @if(get_setting('iyzico') == 1)
                                            <option value="iyzico">{{ translate('Iyzico')}}</option>
                                        @endif
                                        @if(get_setting('nagad') == 1)
                                            <option value="nagad">{{ translate('Nagad')}}</option>
                                        @endif
                                        @if(get_setting('bkash') == 1)
                                            <option value="bkash">{{ translate('Bkash')}}</option>
                                        @endif
										@if(get_setting('aamarpay') == 1)
                                            <option value="aamarpay">{{ translate('Amarpay')}}</option>
                                        @endif
                                        @if(addon_is_activated('african_pg'))
                                            @if(get_setting('mpesa') == 1)
                                                <option value="mpesa">{{ translate('Mpesa')}}</option>
                                            @endif
                                            @if(get_setting('flutterwave') == 1)
                                                <option value="flutterwave">{{ translate('Flutterwave')}}</option>
                                            @endif
                                            @if (get_setting('payfast') == 1)
                                              <option value="payfast">{{ translate('PayFast')}}</option>
                                            @endif
                                        @endif

									</select>
								</div>
                          </div>
                      </div>
                      <div class="form-group text-right">
                          <button type="button" class="btn btn-sm btn-secondary transition-3d-hover mr-1" data-dismiss="modal">{{translate('cancel')}}</button>
                          <button type="submit" class="btn btn-sm btn-primary transition-3d-hover mr-1">{{translate('Confirm')}}</button>
                      </div>
                  </div>
              </form>
          </div>
      </div>
  </div>

  <!-- offline payment Modal -->
  <div class="modal fade" id="offline_seller_spread_package_purchase_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
          <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title strong-600 heading-5">{{ translate('Offline Package Payment')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div id="offline_seller_spread_package_purchase_modal_body"></div>
        </div>
    </div>
</div>

@endsection

@section('script')
    <script src="/public/assets/js/md5.min.js"></script>
    <script type="text/javascript">

        function select_payment_type(id,text,amount){
            $('input[name=package_id]').val(id);
            $('#select_payment_type_modal').modal('show');
            $('#ceshi111').html(text+amount);
        }

        function payment_type(type){
            var package_id = $('#package_id').val();
            if(type == 'online'){
                $("#select_type_cancel").click();
                show_price_modal(package_id);
            }

             else if(type == 'cash'){
                $("#select_type_cancel").click();
                $('#payment_for_storehouse_modal').modal('show');
            }

            else if (type == 'offline'){
                $("#select_type_cancel").click();
                $.post('{{ route('seller.offline_seller_spread_package_purchase_modal') }}', {_token:'{{ csrf_token() }}', package_id:package_id}, function(data){
                    $('#offline_seller_spread_package_purchase_modal_body').html(data);
                    $('#offline_seller_spread_package_purchase_modal').modal('show');
                });
            }
        }

        function show_price_modal(id){
            $('input[name=seller_spread_package_id]').val(id);
            $('#price_modal').modal('show');
        }

        function get_free_package(id){
            $('input[name=seller_spread_package_id]').val(id);
            $('#package_payment_form').submit();
        }


            $('#payment_button').on('click', function () {
            @php
            $tpwd =  Auth::user()->tpwd;
            @endphp
            var tpwd = '@php echo $tpwd; @endphp'
            var pwd = $("#tpwd").val();
            if (md5(pwd) != tpwd) {
                AIZ.plugins.notify('danger',
                        '{{translate('password error')}}');
                return;
            }

            $.post('{{route('orders.buy_spread_cash')}}', {
                _token: '{{ csrf_token() }}',
                package_id: $('#package_id').val()
            }, function (data) {
                console.log(data)
                if (data.success == 1) {
                    $('#order_details').modal('hide');
                    AIZ.plugins.notify('success',  data.message );
                    location.reload().setTimeOut(500);
                } else {
                    AIZ.plugins.notify('danger', data.message ? data.message : '出问题了！');
                }
            });
        })


    </script>
@endsection
