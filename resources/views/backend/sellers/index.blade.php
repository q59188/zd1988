@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{translate('All Sellers')}}</h1>
        </div>
        <div class="col text-right">
            <a href="{{ route('shops.create') }}" class="btn btn-circle btn-info">
                <span>{{translate('Add Virtual Seller')}}</span>
            </a>
        </div>
    </div>
</div>

<div class="card">
    <form class="" id="sort_sellers" action="" method="GET">
        <div class="card-header row gutters-5">
            {{--<div class="col">
                <h5 class="mb-md-0 h6">{{ translate('Sellers') }}</h5>
            </div>--}}

            <div class="dropdown mb-2 mb-md-0">
                <button class="btn border dropdown-toggle" type="button" data-toggle="dropdown">
                    {{translate('Bulk Action')}}
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item" href="#" onclick="bulk_delete()">{{translate('Delete selection')}}</a>
                </div>
            </div>

            @php
                $salesmans = \App\Models\User::where('user_type', 'salesman')->orderBy('created_at', 'desc')->get();
            @endphp

            <div class="col-md-2 ml-auto">
                <div class="form-group mb-0">
                    <input type="text" class="aiz-date-range form-control"  name="date" placeholder="{{ translate('Filter by date') }}" data-format="Y-MM-DD" data-separator=" to " data-advanced-range="true" autocomplete="on">
                </div>
            </div>

            <div class="col-md-2 ml-auto">
                <select class="form-control aiz-selectpicker" name="is_virtual_user" id="is_virtual_user" onchange="sort_sellers()">
                    <option value="">{{translate('All')}}</option>
                    <option value="1"  @isset($is_virtual_user) @if($is_virtual_user == '1') selected @endif @endisset>{{translate('Virtual Account')}}</option>
                    <option value="0"  @isset($is_virtual_user) @if($is_virtual_user == '0') selected @endif @endisset>{{translate('General Account')}}</option>
                </select>
            </div>

            <div class="col-md-2 ml-auto">
                <select name="user_id" class="form-control aiz-selectpicker pos-customer" data-live-search="true" onchange="sort_sellers()">
                    <option value="">{{translate('All Ssalesman')}}</option>

                </select>
            </div>

            <div class="col-md-2 ml-auto">
                <select class="form-control aiz-selectpicker" name="approved_status" id="approved_status" onchange="sort_sellers()">
                    <option value="">{{translate('Filter by Approval')}}</option>
                    <option value="1"  @if($approved && $approved==1) selected @endif>{{translate('Shop Approved')}}</option>
                    <option value="0"  @if($approved && $approved==0) selected @endif>{{translate('Under Review')}}</option>
                    <option value="2"  @if($approved && $approved==2) selected @endif>{{translate('Disapproved')}}</option>
                </select>
            </div>
            <div class="col-md-2">
                <div class="form-group mb-0">
                  <input type="text" class="form-control" id="search" name="search"@isset($sort_search) value="{{ $sort_search }}" @endisset placeholder="{{ translate('Type name or email & Enter') }}">
                </div>
            </div>
            <button type="submit" class="btn btn-success btn-styled">{{ translate('Search') }}</button>
        </div>

        <div class="card-body">
            <table class="table aiz-table mb-0">
                <thead>
                <tr>
                    <th>
                        <div class="form-group">
                            <div class="aiz-checkbox-inline">
                                <label class="aiz-checkbox">
                                    <input type="checkbox" class="check-all">
                                    <span class="aiz-square-check"></span>
                                </label>
                            </div>
                        </div>
                    </th>
                    <th>{{translate('Name')}}</th>
                    <th data-breakpoints="lg">{{translate('Phone')}}</th>
                    <th data-breakpoints="lg">{{translate('Email Address')}}</th>
                    <th data-breakpoints="lg">{{translate('Approval')}}</th>
                    <th data-breakpoints="lg">{{ translate('Num. of Products') }}</th>
                    <th data-breakpoints="lg">{{ translate('Pending Balance') }}</th>

                    <th data-breakpoints="lg">{{ translate('Wallet Money') }}</th>
                    <th data-breakpoints="lg">{{ translate('Guarantee Money') }}</th>
                    <th data-breakpoints="lg" style="width:10%;">{{ translate('Views') }}</th>
                    <th data-breakpoints="lg">注册时间</th>
                    <th data-breakpoints="lg">{{ translate('Comment Permission') }}</th>
                    <th data-breakpoints="lg">{{ translate('Home Display') }}</th>
                    <th data-breakpoints="lg">{{ translate('Total recharge') }}</th>
                    <th data-breakpoints="lg">{{ translate('Total withdrawal amount') }}</th>
                    <th data-breakpoints="lg">{{ translate('Recharge difference') }}</th>
                    <th data-breakpoints="lg">{{ translate('Salesman') }}</th>
                    <th width="10%">{{translate('Options')}}</th>
                </tr>
                </thead>
                <tbody>
                @php
                    $total_recharge = '0.00';
                    $total_withdraw_money = '0.00';
                    $total_difference = '0.00';
                @endphp
                @foreach($shops as $key => $shop)
                    <tr>
                        <td>
                            <div class="form-group">
                                <div class="aiz-checkbox-inline">
                                    <label class="aiz-checkbox">
                                        <input type="checkbox" class="check-one" name="id[]" value="{{$shop->id}}">
                                        <span class="aiz-square-check"></span>
                                    </label>
                                </div>
                            </div>
                        </td>
                        <td>@if($shop->user->banned == 1) <i class="fa fa-ban text-danger" aria-hidden="true"></i> @endif {{$shop->name}} @if($shop->user->is_virtual == 1) (<font color="red">{{translate('Virtual')}}</font>) @endif</td>
                        <td>{{$shop->user->phone}}</td>
                        <td>{{$shop->user->email}}</td>
                        <td>
                            @if ($shop->verification_status == 0)
                                {{ translate('Under Review') }}
                            @elseif ($shop->verification_status == 2)
                                {{ translate('Disapproved') }}
                            @else
                                {{ translate('Shop Approved') }}
                            @endif
                        </td>
                        <td>{{ $shop->user->products->count() }}</td>
                        <td>
                            @if ($shop->admin_to_pay >= 0)
                                {{ single_price($shop->admin_to_pay) }}
                            @else
                                {{ single_price(abs($shop->admin_to_pay)) }} ({{ translate('Due to Admin') }})
                            @endif
                        </td>
                        <td  >
                            {{single_price($shop->user->balance)}}
                        </td>

                         <td  >
                            {{single_price($shop->bzj_money)}}
                        </td>

                          <td  >
                          {{translate('base num')}}：{{$shop->view_base_num}}
                            <br>
                           {{translate('inc num')}}：{{$shop->view_inc_num}}
                        </td>
                        <td>
                            {{$shop->created_at}}
                        </td>

                        <td>
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input onchange="update_comment_permission(this)" value="{{ $shop->id }}" type="checkbox" <?php if($shop->comment_permission == 1) echo "checked";?> >
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td>
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input onchange="update_home_display(this)" value="{{ $shop->id }}" type="checkbox" <?php if($shop->home_display == 1) echo "checked";?> >
                                <span class="slider round"></span>
                            </label>
                        </td>

                        @php
                            $wallets = $shop->user->wallets;
                            $recharge = 0;
                            foreach ($wallets as $wallet) {
                                if ($wallet->approval==1) $recharge += $wallet->amount;
                            }
                            $withdraws = $shop->user->seller_withdraw_requests;
                            $withdraw_money = '0.00';
                            foreach ($withdraws as $withdraw) {
                                if ($withdraw->status==1) $withdraw_money += $withdraw->amount;
                            }
                            $difference = $recharge - $withdraw_money;
                            $total_recharge += $recharge;
                            $total_withdraw_money += $withdraw_money;
                            $total_difference += $difference;
                        @endphp
                        {{--Total recharge--}}
                        <td>{{single_price($recharge)}}</td>
                        <td>{{single_price($withdraw_money)}}</td>
                        <td>{{single_price($difference)}}</td>
                        <td>
                            @php
                                $uid = $shop->user->pid;
                                if( $uid == '')
                                {
                                   echo '---';
                                }
                                else
                                {
                                  $r =  \App\Models\User::where('id',$uid)->first() ;
                                 echo $r ? $r['name'] : '';

                                }
                            @endphp
                        </td>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn btn-sm btn-circle btn-soft-primary btn-icon dropdown-toggle no-arrow" data-toggle="dropdown" href="javascript:void(0);" role="button" aria-haspopup="false" aria-expanded="false">
                                    <i class="las la-ellipsis-v"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right dropdown-menu-xs">
                                    @if($shop->verification_status == 0)
                                        <a href="#" onclick="set_approve('{{$shop->id}}', 1);"  class="dropdown-item">
                                            {{translate('Audit')}}
                                        </a>
                                    @endif
                                    <a href="#" onclick="show_seller_profile('{{$shop->id}}');"  class="dropdown-item">
                                        {{translate('Profile')}}
                                    </a>
                                    <a href="{{route('sellers.login', encrypt($shop->id))}}" class="dropdown-item">
                                        {{translate('Log in as this Seller')}}
                                    </a>
                                    <a href="#" onclick="show_seller_payment_modal('{{$shop->id}}');" class="dropdown-item">
                                        {{translate('Go to Payment')}}
                                    </a>
                                    <a href="{{route('sellers.payment_history', encrypt($shop->user_id))}}" class="dropdown-item">
                                        {{translate('Payment History')}}
                                    </a>
                                    <a href="{{route('sellers.edit', encrypt($shop->id))}}" class="dropdown-item">
                                        {{translate('Edit')}}
                                    </a>
                                    @if($shop->user->banned != 1)
                                        <a href="#" onclick="confirm_ban('{{route('sellers.ban', $shop->id)}}');" class="dropdown-item">
                                        {{translate('Ban this seller')}}
                                        <i class="fa fa-ban text-danger" aria-hidden="true"></i>
                                        </a>
                                    @else
                                        <a href="#" onclick="confirm_unban('{{route('sellers.ban', $shop->id)}}');" class="dropdown-item">
                                        {{translate('Unban this seller')}}
                                        <i class="fa fa-check text-success" aria-hidden="true"></i>
                                        </a>
                                    @endif
                                    <a href="#" class="dropdown-item confirm-delete" data-href="{{route('sellers.destroy', $shop->id)}}" class="">
                                        {{translate('Delete')}}
                                    </a>
                                    <span onclick="show_chat_modal({{$shop->user->id}})" class="dropdown-item" style="cursor:pointer;">
                                        {{translate('Message Seller')}}
                                    </span>
                                    {{--<span onclick="show_bzj({{$shop->id}},{{$shop->bzj_money}})" class="dropdown-item" style="cursor:pointer;">
                                        {{translate('Guarantee Money')}}
                                    </span>--}}
                                    <span onclick="show_seller_guarantee_money_modal({{$shop->id}})" class="dropdown-item" style="cursor:pointer;">
                                        {{translate('Guarantee Money')}}
                                    </span>
                                     <span onclick="show_view({{$shop->id}},{{$shop->view_inc_num}},{{$shop->view_base_num}})" class="dropdown-item" style="cursor:pointer;">
                                        {{translate('Views')}}
                                    </span>
                                     <a href="{{route('sellers.editbank', encrypt($shop->id))}}" class="dropdown-item">
                                       修改银行卡
                                    </a>

                                    <span onclick="show_package({{$shop->id}},{{$shop->seller_package_id}})" class="dropdown-item" style="cursor:pointer;">
                                        {{translate('Set Package')}}
                                    </span>
                                       <span onclick="set_pid({{$shop->id}},{{$shop->user->pid}})" class="dropdown-item" style="cursor:pointer;">
                                        {{translate('Set Salesman')}}
                                    </span>



                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
                @if(count($shops))
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>{{single_price($total_recharge)}}</td>
                        <td>{{single_price($total_withdraw_money)}}</td>
                        <td>{{single_price($total_difference)}}</td>
                        <td></td>
                        <td></td>
                    </tr>
                @endif
                </tbody>
            </table>
            <div class="aiz-pagination">
              {{ $shops->appends(request()->input())->links() }}
            </div>
        </div>
    </form>
</div>

@endsection

@section('modal')
	<!-- Delete Modal -->
	@include('modals.delete_modal')

    <div class="modal fade" id="chat_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-zoom product-modal" id="modal-size" role="document">
            <div class="modal-content position-relative">
                <div class="modal-header">
                    <h5 class="modal-title fw-600 h5">{{ translate('Any query about this seller') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('conversations.admin_store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="receiver_id" id="receiver_id" value="">
                    <div class="modal-body gry-bg px-3 pt-3">
                        {{--<div class="form-group">
                            <input type="text" class="form-control mb-3" name="title"
                                value="" placeholder="{{ translate('Title') }}"
                                required>
                        </div>--}}
                        <div class="form-group">
                            <textarea class="form-control" rows="8" name="title" required
                                placeholder="{{ translate('Title') }}"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-primary fw-600"
                            data-dismiss="modal">{{ translate('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary fw-600">{{ translate('Send') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

	<!-- Seller Profile Modal -->
	<div class="modal fade" id="profile_modal">
		<div class="modal-dialog">
			<div class="modal-content" id="profile-modal-content">

			</div>
		</div>
	</div>

	<!-- Seller Payment Modal -->
	<div class="modal fade" id="payment_modal">
	    <div class="modal-dialog">
	        <div class="modal-content" id="payment-modal-content">

	        </div>
	    </div>
	</div>

	<!-- Ban Seller Modal -->
	<div class="modal fade" id="confirm-ban">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title h6">{{translate('Confirmation')}}</h5>
					<button type="button" class="close" data-dismiss="modal">
					</button>
				</div>
				<div class="modal-body">
                    <p>{{translate('Do you really want to ban this seller?')}}</p>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-light" data-dismiss="modal">{{translate('Cancel')}}</button>
					<a class="btn btn-primary" id="confirmation">{{translate('Proceed!')}}</a>
				</div>
			</div>
		</div>
	</div>

	<!-- Unban Seller Modal -->
	<div class="modal fade" id="confirm-unban">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title h6">{{translate('Confirmation')}}</h5>
						<button type="button" class="close" data-dismiss="modal">
						</button>
					</div>
					<div class="modal-body">
							<p>{{translate('Do you really want to unban this seller?')}}</p>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-light" data-dismiss="modal">{{translate('Cancel')}}</button>
						<a class="btn btn-primary" id="confirmationunban">{{translate('Proceed!')}}</a>
					</div>
				</div>
			</div>
		</div>




    <!-- Guarantee Money Modal -->
    <div class="modal fade" id="guarantee_money">
        <div class="modal-dialog">
            <div class="modal-content" id="guarantee-money-content">

            </div>
        </div>
    </div>

    <div class="hidden" id="approve_form">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-2">
                    <label>{{ translate('Approve Status')}}</label>
                </div>
                <div class="col-md-10">
                    <select class="form-control" id="audit_status">
                        <option value="1">{{ translate('Shop Approved') }}</option>
                        <option value="2">{{ translate('Disapproved') }}</option>
                    </select>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-2">
                    <label>{{ translate('Approve Remark')}}</label>
                </div>
                <div class="col-md-10">
                    <textarea class="form-control" rows="5" id="audit_remark"></textarea>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')
    <script src="https://cdn.bootcdn.net/ajax/libs/layer/3.5.1/layer.min.js"></script>

    <script type="text/javascript">
        function show_seller_guarantee_money_modal(id){
            $.post('{{ route('sellers.guarantee_money_modal') }}',{_token:'{{ @csrf_token() }}', id:id}, function(data){
                $('#guarantee_money #guarantee-money-content').html(data);
                $('#guarantee_money').modal('show', {backdrop: 'static'});
            });
        }


        function show_bzj(shop_id, bzj) {
            layer.prompt( {
                title: "保证金金额", //提示框标题
                value: bzj //初始时的值，默认空字符
            }, function (value, index, elem)
            {
                $.post( '{{ route('sellers.setbzj') }}', {
                    _token: '{{ @csrf_token() }}',
                    shop_id: shop_id,
                    bzj: value
                }, function (data)
                {
                    layer.msg( data.msg, function ()
                    {
                        location.reload();
                    } );
                }, 'json' );
                layer.close( index );
            } );
        }
     function set_pid(shop_id,pid)
     {
          @php

         $Salesmans =  \App\Models\User::where('user_type','salesman')->get();
         @endphp
         var html = '';
          @foreach ($Salesmans as $key => $us)
            html +="<option ";
            if( pid == @php echo $us['id'];@endphp )
            {
                html += ' selected ';
            }

            html += " value='@php echo $us['id'];@endphp'> @php echo $us['name'];@endphp</option>";
          @endforeach

          var html2 = "<select class='form-control' name='userid' id='userid'> ";
        html = html2+html+"</select>";
          layer.open({

        type: 1,
        title:'设置推销员',
        skin:'layui-layer-rim',
        area:['450px', 'auto'],

        content: ' <div class="row" style="width: 420px;  margin-left:7px; margin-top:10px;">'
            +'<div class="col-sm-12">'
            +'<div class="input-group">'
           + html
            +'</div>'
            +'</div>'

              +'</div>'
        ,
        btn:['保存','取消'],
        btn1: function (index,layero) {
            var userid = $("#userid").val();
              $.post('{{ route('sellers.setpid') }}',{_token:'{{ @csrf_token() }}', shop_id:shop_id,pid:userid}, function(data){
                layer.msg(data.msg,function(){
                    location.reload();
                });

            },'json');
        },
        btn2:function (index,layero) {
             layer.close(index);
        }
          });
     }

     function show_package(shop_id,seller_package_id)
     {
         @php
         $seller_packages = \App\Models\SellerPackage::all();
         @endphp
         var html = '';
          @foreach ($seller_packages as $key => $seller_package)
            html +="<option ";
            if( seller_package_id == @php echo $seller_package['id'];@endphp )
            {
                html += ' selected ';
            }

            html += " value='@php echo $seller_package['id'];@endphp'> @php echo $seller_package['name'];@endphp</option>";
          @endforeach

          var html2 = "<select class='form-control' name='packageid' id='packageid'> ";
        html = html2+html+"</select>";
          layer.open({

        type: 1,
        title:'设置套餐',
        skin:'layui-layer-rim',
        area:['450px', 'auto'],

        content: ' <div class="row" style="width: 420px;  margin-left:7px; margin-top:10px;">'
            +'<div class="col-sm-12">'
            +'<div class="input-group">'
           + html
            +'</div>'
            +'</div>'

              +'</div>'
        ,
        btn:['保存','取消'],
        btn1: function (index,layero) {
            var packageid = $("#packageid").val();
              $.post('{{ route('sellers.setpackage') }}',{_token:'{{ @csrf_token() }}', shop_id:shop_id,packageid:packageid}, function(data){
                layer.msg(data.msg,function(){
                    location.reload();
                });

            },'json');
        },
        btn2:function (index,layero) {
             layer.close(index);
        }

    });



     }
     function show_view(shop_id,view_inc_num,view_base_num) {


          var content = ' <div class="row" style="width: 420px;  margin-left:7px; margin-top:10px;">'
            +'<div class="col-sm-12">'
            +'<div class="input-group">'
            +'<span class="input-group-addon"> 基础访问量：</span>'
            +'<input id="base_num" type="text" value="'+view_base_num+'" class="form-control" placeholder="基础访问量">'
            +'</div>'
            +'</div>'

               +'<div class="col-sm-12" style="margin-top:3px;">'
            +'<div class="input-group">'
            +'<span class="input-group-addon"> 每日递增量：</span>'
            +'<input id="inc_num" type="text" value="'+view_inc_num+'" class="form-control" placeholder="每日递增">'
            +'</div>'
            +'</div>'

              +'</div>';

            layer.open({

        type: 1,
        title:'访问量',
        skin:'layui-layer-rim',
        area:['450px', 'auto'],

        content: content,
        btn:['保存','取消'],
        btn1: function (index,layero) {
            var inc_num = $("#inc_num").val();
            var base_num = $("#base_num").val();

             $.post('{{ route('sellers.setviews') }}',{_token:'{{ @csrf_token() }}', shop_id:shop_id,inc_num:inc_num,base_num:base_num}, function(data){
                layer.msg(data.msg,function(){
                    location.reload();
                });
                },'json');

        },
        btn2:function (index,layero) {
             layer.close(index);
        }

    });







            return false;

            layer.prompt({

              title: "访问量", //提示框标题

              value: views, //初始时的值，默认空字符

            },function(value, index, elem){

              $.post('{{ route('sellers.setviews') }}',{_token:'{{ @csrf_token() }}', shop_id:shop_id,view_inc_num:view_inc_num}, function(data){
                layer.msg(data.msg,function(){
                    location.reload();
                });

            },'json');

              layer.close(index);

            });

        }

        function show_chat_modal(receiver_id) {
            $('#receiver_id').val(receiver_id);
            $('#chat_modal').modal('show');
        }

        $(document).on("change", ".check-all", function() {
            if(this.checked) {
                // Iterate each checkbox
                $('.check-one:checkbox').each(function() {
                    this.checked = true;
                });
            } else {
                $('.check-one:checkbox').each(function() {
                    this.checked = false;
                });
            }

        });

        function show_seller_payment_modal(id){
            $.post('{{ route('sellers.payment_modal') }}',{_token:'{{ @csrf_token() }}', id:id}, function(data){
                $('#payment_modal #payment-modal-content').html(data);
                $('#payment_modal').modal('show', {backdrop: 'static'});
                $('.demo-select2-placeholder').select2();
            });
        }

        function show_seller_profile(id){
            $.post('{{ route('sellers.profile_modal') }}',{_token:'{{ @csrf_token() }}', id:id}, function(data){
                $('#profile_modal #profile-modal-content').html(data);
                $('#profile_modal').modal('show', {backdrop: 'static'});
            });
        }

        function update_approved(el){
            if(el.checked){
                var status = 1;
            }
            else{
                var status = 0;
            }
            $.post('{{ route('sellers.approved') }}', {_token:'{{ csrf_token() }}', id:el.value, status:status}, function(data){
                if(data == 1){
                    AIZ.plugins.notify('success', '{{ translate('Approved sellers updated successfully') }}');
                }
                else{
                    AIZ.plugins.notify('danger', '{{ translate('Something went wrong') }}');
                }
            });
        }

        function update_comment_permission(el){
            if(el.checked){
                var status = 1;
            }
            else{
                var status = 0;
            }
            $.post('{{ route('sellers.comment_permission') }}', {_token:'{{ csrf_token() }}', id:el.value, status:status}, function(data){
                if(data == 1){
                    AIZ.plugins.notify('success', '{{ translate('Comment permission sellers updated successfully') }}');
                }
                else{
                    AIZ.plugins.notify('danger', '{{ translate('Something went wrong') }}');
                }
            });
        }

        function update_home_display(el){
            if(el.checked){
                var status = 1;
            }
            else{
                var status = 0;
            }
            $.post('{{ route('sellers.home_display') }}', {_token:'{{ csrf_token() }}', id:el.value, status:status}, function(data){
                if(data == 1){
                    AIZ.plugins.notify('success', '{{ translate('Home display sellers updated successfully') }}');
                }
                else{
                    AIZ.plugins.notify('danger', '{{ translate('Something went wrong') }}');
                }
            });
        }

        function sort_sellers(el){
            return false
            $('#sort_sellers').submit();
        }

        function confirm_ban(url)
        {
            $('#confirm-ban').modal('show', {backdrop: 'static'});
            document.getElementById('confirmation').setAttribute('href' , url);
        }

        function confirm_unban(url)
        {
            $('#confirm-unban').modal('show', {backdrop: 'static'});
            document.getElementById('confirmationunban').setAttribute('href' , url);
        }

        function bulk_delete() {
            var data = new FormData($('#sort_sellers')[0]);
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{route('bulk-seller-delete')}}",
                type: 'POST',
                data: data,
                cache: false,
                contentType: false,
                processData: false,
                success: function (response) {
                    if(response == 1) {
                        location.reload();
                    }
                }
            });
        }

        function set_approve(id) {

            $('#audit_status').val('1');
            $('#audit_remark').val('');
            layer.open({
                type: 1,
                title: '{{translate("Audit")}}',
                skin: 'layui-layer-rim',
                area: ['720px', 'auto'],
                content: $('#approve_form'),
                btn: ['{{translate("Save")}}', '{{translate("Cancel")}}'],
                btn1: function (index, layero) {
                    let status = $('#audit_status').val();
                    let remark = $('#audit_remark').val();
                    if (2 == status && remark.length < 1) {
                        layer.msg("{{translate('Audit remark required')}}");
                        return false;
                    }

                    let params = {_token: '{{ csrf_token() }}', id: id, status: status, remark: remark};
                    $.post('{{ route('sellers.approved') }}', params, function (res) {
                        if (res == 1) {
                            layer.close(index);
                            AIZ.plugins.notify('success', '{{ translate('Approved sellers updated successfully') }}');
                            window.location.reload();
                        } else {
                            AIZ.plugins.notify('danger', '{{ translate('Audit remark required') }}');
                        }
                    });
                },
                btn2: function (index, layero) {
                    layer.close(index);
                }
            });
        }


    </script>
@endsection
