@extends('backend.layouts.app')

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
	<div class="row align-items-center">
		<div class="col-md-6">
			<h1 class="h3">{{translate('All Auto Orders')}}</h1>
		</div>
		<div class="col-md-6 text-md-right">
			<a href="{{ route('new_smart_order.create') }}" class="btn btn-circle btn-info">
				<span>{{translate('Add Auto Order')}}</span>
			</a>
		</div>
	</div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0 h6">{{translate('All Auto Orders')}}</h5>
    </div>
    <div class="card-body">
        <table class="table aiz-table mb-0">
            <thead>
                <tr>
                    <th data-breakpoints="lg" width="10%">#</th>
                    <th>{{translate('Sellers')}}</th>
                    <th data-breakpoints="lg">{{ translate('Order Time') }}</th>
                    <th data-breakpoints="lg">{{ translate('Order Quantity') }}</th>
                    <th data-breakpoints="lg">{{ translate('Price Range') }}</th>
                    <th data-breakpoints="lg">{{ translate('Status') }}</th>
                    <th class="text-right footable-last-visible" style="display: table-cell;">选项</th>
                </tr>
            </thead>
            <tbody>
                @foreach($smart_orders as $vo)
                    @if(!empty($vo->seller))
                    <tr>
                        <td>{{$vo->id}}</td>
                        <td>{{$vo->seller->name}}</td>
                        <td>{{$vo->date}} {{$vo->time_range}}</td>
                        <td>{{$vo->quantity}}</td>
                        <td>{{$vo->min_price}} - {{$vo->max_price}}</td>
                        <td>
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input type="checkbox" onchange="change_status(this)" value="{{$vo->id}}" @if($vo->status) checked @endif>
                                <span></span>
                            </label>
                        </td>
                        <td class="text-right">
                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{route('new_smart_order.edit', encrypt($vo->id))}}"
                               title="{{ translate('Edit') }}"><i class="las la-edit"></i>
                            </a>
                            <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete"
                               data-href="{{route('new_smart_order.destroy', $vo->id)}}" title="{{ translate('Delete') }}">
                                <i class="las la-trash"></i>
                            </a>
                        </td>
                    </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
        <div class="aiz-pagination">
            {{ $smart_orders->appends(request()->input())->links() }}
        </div>
    </div>
</div>
@endsection

@section('modal')
    @include('modals.delete_modal')
@endsection

@section('script')
    <script>
        function change_status(el){
            var status = 0;
            if(el.checked){
                var status = 1;
            }
            $.post("{{route('new_smart_order.change_status')}}", {_token: '{{csrf_token()}}', id:el.value, status:status}, function(data){
                if(data == 1){
                    AIZ.plugins.notify('success', '{{translate('Edit Successfully')}}');
                } else{
                    AIZ.plugins.notify('danger', '{{translate('Something went wrong')}}');
                }
            });
        }
    </script>
@endsection
