@extends('seller.layouts.app')

@section('panel_content')
    <div class="row gutters-10 justify-content-center">
        @php
            $count = DB::table('orders')->where('seller_id', Auth::user()->id)
                ->count();
            $grand_total = DB::table('orders')->where('seller_id', Auth::user()->id)
                ->sum('orders.grand_total');
            $product_storehouse_total = DB::table('orders')->where('seller_id', Auth::user()->id)
                ->sum('orders.product_storehouse_total');
            $total_turnover = "$".sprintf('%.2f',$grand_total);
            $total_profit = "$".sprintf('%.2f',($grand_total - $product_storehouse_total));
            $today_summary = (array)DB::table('orders')->where('seller_id', Auth::user()->id)
                ->where('date', ">=", strtotime(date('Y-m-d')))
                ->selectRaw("sum('grand_total') as ta, sum('product_storehouse_total') as tc")->first();
            $today_profit = "$".sprintf('%.2f',($today_summary['ta'] - $today_summary['tc']));;
        @endphp
        <div class="col-md-3 mx-auto mb-3">
            <div class="bg-grad-1 text-white rounded-lg overflow-hidden">
                  <span class="size-30px rounded-circle mx-auto bg-soft-primary d-flex align-items-center justify-content-center mt-3">
                      <i class="las la-upload la-2x" style="color: #007bff"></i>
                  </span>
                <div class="px-3 pt-3 pb-3">
                    <div class="h4 fw-700 text-center">{{ $count }}</div>
                    <div class="opacity-50 text-center">{{  translate('Total Orders') }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mx-auto mb-3">
            <div class="bg-grad-1 text-white rounded-lg overflow-hidden">
                  <span class="size-30px rounded-circle mx-auto bg-soft-primary d-flex align-items-center justify-content-center mt-3">
                      <i class="las la-upload la-2x" style="color: #007bff"></i>
                  </span>
                <div class="px-3 pt-3 pb-3">
                    <div class="h4 fw-700 text-center">{{ $total_turnover }}</div>
                    <div class="opacity-50 text-center">{{  translate('Total Turnover') }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mx-auto mb-3">
            <div class="bg-grad-1 text-white rounded-lg overflow-hidden">
                  <span class="size-30px rounded-circle mx-auto bg-soft-primary d-flex align-items-center justify-content-center mt-3">
                      <i class="las la-upload la-2x" style="color: #007bff"></i>
                  </span>
                <div class="px-3 pt-3 pb-3">
                    <div class="h4 fw-700 text-center">{{ $today_profit }}</div>
                    <div class="opacity-50 text-center">{{  translate('Today Profit') }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mx-auto mb-3">
            <div class="bg-grad-1 text-white rounded-lg overflow-hidden">
                  <span class="size-30px rounded-circle mx-auto bg-soft-primary d-flex align-items-center justify-content-center mt-3">
                      <i class="las la-upload la-2x" style="color: #007bff"></i>
                  </span>
                <div class="px-3 pt-3 pb-3">
                    <div class="h4 fw-700 text-center">{{ $total_profit }}</div>
                    <div class="opacity-50 text-center">{{  translate('Total Profit') }}</div>
                </div>
            </div>
        </div>

    </div>

    <div class="card">
        <form id="sort_orders" action="" method="GET">
          <div class="card-header row gutters-5">
            <div class="col text-center text-md-left">
              <h5 class="mb-md-0 h6">{{ translate('Orders') }}</h5>
            </div>
              <div class="col-md-3 ml-auto">
                  <select class="form-control aiz-selectpicker" data-placeholder="{{ translate('Filter by Payment Status')}}" name="payment_status" onchange="sort_orders()">
                      <option value="">{{ translate('Filter by Payment Status')}}</option>
                      <option value="paid" @isset($payment_status) @if($payment_status == 'paid') selected @endif @endisset>{{ translate('Paid')}}</option>
                      <option value="unpaid" @isset($payment_status) @if($payment_status == 'unpaid') selected @endif @endisset>{{ translate('Un-Paid')}}</option>
                  </select>
              </div>

              <div class="col-md-3 ml-auto">
                <select class="form-control aiz-selectpicker" data-placeholder="{{ translate('Filter by Payment Status')}}" name="delivery_status" onchange="sort_orders()">
                    <option value="">{{ translate('Filter by Deliver Status')}}</option>
                    <option value="pending" @isset($delivery_status) @if($delivery_status == 'pending') selected @endif @endisset>{{ translate('Pending')}}</option>
                    <option value="confirmed" @isset($delivery_status) @if($delivery_status == 'confirmed') selected @endif @endisset>{{ translate('Confirmed')}}</option>
                    <option value="on_delivery" @isset($delivery_status) @if($delivery_status == 'on_delivery') selected @endif @endisset>{{ translate('On delivery')}}</option>
                    <option value="delivered" @isset($delivery_status) @if($delivery_status == 'delivered') selected @endif @endisset>{{ translate('Delivered')}}</option>
                </select>
              </div>
              <div class="col-md-3">
                <div class="from-group mb-0">
                    <input type="text" class="form-control" id="search" name="search" @isset($sort_search) value="{{ $sort_search }}" @endisset placeholder="{{ translate('Type Order code & hit Enter') }}">
                </div>
              </div>
          </div>
        </form>

        @if (count($orders) > 0)
            <div class="card-body p-3">
                <table class="table aiz-table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ translate('Order Code')}}</th>
                            <th data-breakpoints="lg">{{ translate('Num. of Products')}}</th>
                            <th data-breakpoints="lg">{{ translate('Customer')}}</th>
                            <th data-breakpoints="md">{{ translate('Amount')}}</th>
                            <th data-breakpoints="md">{{ translate('Profit')}}</th>
                            <th data-breakpoints="md">{{ translate('Pick Up Status') }}</th>
                            <th data-breakpoints="lg">{{ translate('Delivery Status')}}</th>
                            <th>{{ translate('Payment Status')}}</th>
                            <th class="text-right">{{ translate('Options')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $key => $order_id)
                            @php
                                $order = \App\Models\Order::find($order_id->id);
                            @endphp
                            @if($order != null)
                                <tr>
                                    <td>
                                        {{ $key+1 }}
                                    </td>
                                    <td>
                                        <a href="#{{ $order->code }}" onclick="show_order_details({{ $order->id }})">{{ $order->code }}</a>
                                    </td>
                                    <td>
                                        {{ count($order->orderDetails->where('seller_id', Auth::user()->id)) }}
                                    </td>
                                    <td>
                                        @if ($order->user_id != null)
                                            {{ optional($order->user)->name }}
                                        @else
                                            {{ translate('Guest') }} ({{ $order->guest_id }})
                                        @endif
                                    </td>
                                    <td>
                                        {{ single_price($order->grand_total) }}
                                    </td>
                                    <td>
                                        @if ($order->product_storehouse_total > 0)
                                            {{ single_price($order->grand_total - $order->product_storehouse_total) }}
                                        @else
                                            {{ translate('None') }}
                                        @endif
                                    </td>
                                    <td>
                                        @if ($order->product_storehouse_status)
                                            <span class="badge badge-inline badge-success">{{translate('Picked Up')}}</span>
                                        @else
                                            @if ($order->product_storehouse_total)
                                                <span class="badge badge-inline badge-danger">{{translate('Unpicked Up')}}</span>
                                            @endif
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $status = $order->delivery_status;
                                        @endphp
                                        {{ translate(ucfirst(str_replace('_', ' ', $status))) }}
                                    </td>
                                    <td>
                                        @if ($order->payment_status == 'paid')
                                            <span class="badge badge-inline badge-success">{{ translate('Paid')}}</span>
                                        @else
                                            <span class="badge badge-inline badge-danger">{{ translate('Unpaid')}}</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        @if (isset($can_manage) && 0 == $can_manage && $order->product_storehouse_total > 0)
                                            @if (!$order->product_storehouse_status)
                                                <a class="btn btn-primary btn-sm btn-soft-info btn-circle" onclick="paymentForStoreHouse('{{encrypt($order->id)}}', '{{single_price($order->product_storehouse_total)}}')">{{ translate('Payment For Storehouse') }}</a>
                                            @else
                                                <a class="btn btn-primary btn-xs btn-soft-info" onclick="javascript:;">{{ translate('Picked up') }}</a>
                                            @endif
                                        @endif

                                        <a href="{{ route('seller.orders.show', encrypt($order->id)) }}" class="btn btn-soft-info btn-icon btn-circle btn-sm" title="{{ translate('Order Details') }}">
                                            <i class="las la-eye"></i>
                                        </a>
                                        <a href="{{ route('seller.invoice.download', $order->id) }}" class="btn btn-soft-warning btn-icon btn-circle btn-sm" title="{{ translate('Download Invoice') }}">
                                            <i class="las la-download"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
                <div class="aiz-pagination">
                    {{ $orders->links() }}
              	</div>
            </div>
        @endif
    </div>
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
                    <input type="hidden" name="order_id" id="order_id" value="0">
                    <div class="row">
                        <div class="col-12">
                            <h5 class="text-center">{{ translate('Pay with wallet')}} <span id="order_price">0</span></h5>
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
    <script src="{{ static_asset('assets/js/md5.min.js') }}" ></script>
@endsection

@section('script')
    <script type="text/javascript">
        function sort_orders(el){
            $('#sort_orders').submit();
        }

        function paymentForStoreHouse(id, price) {
            var tpwd = '{{ $tpwd }}';
            if (!tpwd) {
                location.href="/seller/transaction";
                return false;
            } else {
                $('#order_id').val(id);
                $('#order_price').html(price);
                $('#payment_for_storehouse_modal').modal('show');
            }
        }

        // 付款
        $('#payment_button').on('click', function () {
            var tpwd = '{{ $tpwd }}';
            var pwd = $("#tpwd").val();
            if (md5(pwd) != tpwd) {
                AIZ.plugins.notify('danger', '{{ translate('password error') }}');
                return;
            }

            $.post('{{ route('seller.orders.payment_for_storehouse_product') }}', {
                _token: '{{ @csrf_token() }}',
                order_id: $('#order_id').val(),
            }, function (data) {
                console.log(data)
                if (data.success == 1) {
                    $('#order_details').modal('hide');
                    AIZ.plugins.notify('success', '{{ translate('Order status has been updated') }}');
                    location.reload().setTimeOut(500);
                } else {
                    AIZ.plugins.notify('danger', data.message ? data.message : '{{ translate('Something went wrong') }}');
                }
            });
        })
    </script>
@endsection
