@extends('backends.templates.master')
@section('title', __('Danh sách lịch sửa chữa thiết bị'))
@section('content')
@php
   $acceptance = acceptanceRepair();
@endphp
<div id="list-events" class="content-wrapper events">
   <section class="content">
      <div class="head container">
         <h1 class="title">{{ __('Danh sách lịch sửa chữa thiết bị ') }}{{ $equipment->title}}</h1>
      </div>
      <div class="main">
         <div class="row search-filter">
            <div class="col-md-2 filter">
               <ul class="nav-filter">
                  <li class="active"><a href="{{ route('eqrepair.index') }}">{{ __('Tất cả') }}</a></li>
               </ul>
            </div>
            <div class="col-md-10">
               <div class="list-equip row">
                  <div class="col-md-4">
                     <p>{{ __('Tên: ')}}{{$equipment->title}}</p>
                     <p>{{ __('Model: ')}}{{$equipment->model}}</p>
                     <p>{{ __('Serial: ')}}{{$equipment->serial}}</p>
                  </div>
                  <div class="col-md-4">
                     <p>{{ __('Khoa: ')}}{{isset($equipment->equipment_department) ? $equipment->equipment_department->title : ''}}</p>
                     <p>{{ __('Ngày nhập: ')}}{{$equipment->warehouse}}</p>
                     <p>{{ __('Ngày hết hạn bảo hành: ')}}{{$equipment->warranty_date}}</p>
                  </div>
                  <div class="col-md-4">
                     <p>{{ __('Ngày kiểm định đầu tiên: ')}}{{$equipment->first_inspection}}</p>
                     @if(!$repairs->isEmpty())
                     <p>{{ __('Tình trạng sửa chữa: ')}}{{ $acceptance[$equipment->schedule_repairs->sortByDesc('planning_date')->first()->acceptance]}}</p>
                     @endif
                  </div>
               </div>
            </div>
         </div>
         <div class="card">
            <div class="card-body p-0">
               @include('notices.index')
               <form class="dev-form" action="" name="listEvent" method="POST">
                  @csrf
                  <div class="table-responsive">
                     <table class="table table-striped table-bordered" role="table">
                        <thead class="thead">
                           <tr class="text-center">
                              <th>{{ __('STT') }}</th>
                              <th>{{ __('Mã sửa chữa') }}</th>
                              <th>{{ __('Ngày báo hỏng') }}</th>
                              <th>{{ __('Ngày lập kế hoạch') }}</th>
                              <th>{{ __('Ngày sửa') }}</th>
                              <th>{{ __('Ngày sửa xong') }}</th>
                              <th>{{ __('Tình trạng') }}</th>
                              <th>{{ __('Chi phí') }}</th>
                              <th class="action">{{ __('Tác vụ') }}</th>
                           </tr>
                        </thead>
                        <tbody class="tbody">
                           @if(!$repairs->isEmpty())
                           @foreach($repairs as $key => $repair)
                           <tr class="text-center">
                              <td>{{ ++$key}}</td>
                              <td>{{ $repair->code }}</td>
                              <td>{{ $repair->date_failure }}</td>
                              <td>{{ $repair->planning_date }}</td>
                              <td>{{ $repair->repair_date }}</td>
                              <td>{{ $repair->completed_repair }}</td>
                              <td>{{ $acceptance[$repair->acceptance] }}</td>
                              <td>{{ $repair->actual_costs }}</td>
                              <td>
                                 <a class="btn btn-info btn-sm" href="{{ route('eqrepair.edit',['equip_id'=>$equipment->id, 'repair_id'=>$repair->id]) }}"><i class="fas fa-edit"></i></a> 
                                 <a class="btn btn-danger btn-sm" href="{{ route('eqrepair.delete',['equip_id'=>$equipment->id, 'repair_id'=>$repair->id]) }}" data-toggle="modal" data-target="#sideModal" data-direct="modal-top-right"><i class="fas fa-trash"></i></a>
                              </td>
                           </tr>
                           @endforeach
                           @else
                           <tr>
                              <td colspan="8">{{ __('No items!') }}</td>
                           </tr>
                           @endif
                        </tbody>
                     </table>
                  </div>
               </form>
               <div class="p-3 mt-2">{{ $repairs->links() }}</div>
            </div>
         </div>
      </div>
   </section>
   <!-- /.content -->
</div>
<!-- Side Modal Top Right -->
@include('modals.modal_delete')
@include('modals.modal_deleteChoose')
@endsection