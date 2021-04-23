@extends('backends.templates.master')
@section('title', __('Danh sách sửa chữa & báo hỏng thiết bị'))
@section('content')
@php
   $status = get_statusEquipments();
@endphp
<div id="list-events" class="content-wrapper events">
   <section class="content">
      <div class="head container">
         <h1 class="title">{{ __('Danh sách sửa chữa & báo hỏng thiết bị') }}</h1>
      </div>
      <div class="main">
         <div class="row search-filter">
            <div class="col-md-2 filter">
               <ul class="nav-filter">
                  <li class="active"><a href="{{ route('eqrepair.index') }}">{{ __('Tất cả') }}</a></li>
               </ul>
            </div>
            <div class="col-md-10 search-form">
               <form action="{{ route('eqrepair.index') }}" method="GET">
                  <div class="row">
                     <div class="col-md-3">
                        <select class="form-control select2"  name="department_id">
                           <option value="" > Chọn khoa phòng </option>                  
                           @foreach ($departments as $department)
                              <option value="{{ $department->id }}" {{ $department_id ==  $department->id ? 'selected' : '' }} >{{ $department->title }}</option>
                           @endforeach 
                        </select>   
                     </div>
                     <div class="col-md-3">
                        <select  class="select2 form-control" name="device_id">
                           <option value=""> Chọn loại thiết bị </option>
                           @foreach ($devices as $device)
                              <option  value="{{$device->id}}" {{ $device_id ==  $device->id ? 'selected' : '' }}>{{$device->title}}</option>
                           @endforeach
                        </select>
                     </div>
                     <div class="col-md-3">
                        <select class="form-control select2"  name="status_id">
                           <option value="" > Chọn tình trạng </option>                  
                              <option value="was_broken"  {{ $status_id ==  'was_broken' ? 'selected' : '' }} >{{ __('Đang báo hỏng') }}</option>
                              <option value="corrected"  {{ $status_id ==  'corrected' ? 'selected' : '' }} >{{ __('Đang sửa chữa') }}</option>
                        </select>   
                     </div> 
                     <div class="col-md-3 s-key">
                        <input type="text" name="key" class="form-control s-key" placeholder="{{__('Nhập mã thiết bị , tên thiết bị , model , serial, hãng sản xuất ...')}}" value="{{$keyword}}">
                     </div>
                     <button type="submit" class="btn btn-primary"><i class="fa fa-search" aria-hidden="true"></i></button>
                  </div>
               </form>
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
                              <th>{{ __('Khoa') }}</th>
                              <th>{{ __('Mã thiết bị') }}</th>
                              <th>{{ __('Tên thiết bị') }}</th>
                              <th>{{ __('Trạng thái') }}</th>
                              <th>{{ __('Ngày lập kế hoạch') }}</th>
                              <th>{{ __('Ngày sửa') }}</th>
                              <th>{{ __('Ngày sửa xong') }}</th>
                              <th class="action">{{ __('Tác vụ') }}</th>
                           </tr>
                        </thead>
                        <tbody class="tbody">
                           @if(!$eqrepairs->isEmpty())
                           
                           @foreach($eqrepairs as $key => $equipment)
                           @php
                              $item = $equipment->schedule_repairs->sortByDesc('planning_date')->first();
                              $check = isset($item) ? $item->acceptance == 'accepted' || $item->acceptance == 'accepted' : 'false';
                           @endphp
                           <tr class="text-center">
                              <td>{{ ++$key}}</td>
                              <td>{{  isset($equipment->equipment_department) ? $equipment->equipment_department->title : '-' }}</td>
                              <td>{{ isset($equipment->code) ? $equipment->code : '-'  }}</td>
                              <td>{{ isset($equipment->title) ? $equipment->title: '-' }}</td>
                              <td>{{ isset($status[$equipment->status]) ? $status[$equipment->status] :'-' }}</td>
                              <td>{{ isset($item) && $item->planning_date != '' ? $item->planning_date : '-' }}</td>
                              <td>{{ isset($item) && $item->repair_date != '' ? $item->repair_date : '-' }}</td>
                              <td>{{ isset($item) && $item->completed_repair != '' ? $item->completed_repair : '-' }}</td>
                              <td class="group-action action text-nowrap">
                                 <a class="btn btn-primary btn-sm" href="{{ route('eqrepair.history',['equip_id'=>$equipment->id]) }}"><i class="fa fa-list-alt"></i></a> 
                                 @if($equipment->status != 'corrected' && $check)
                                    <a class="btn btn-danger btn-sm" href="{{ route('eqrepair.create',['equip_id'=>$equipment->id]) }}"><i class="fa fa-plus"></i></a>
                                 @endif
                                 @if(isset($item) && $check && $equipment->status == 'corrected')
                                    <a class="btn btn-success btn-sm btn-repair" href="{{ route('eqrepair.stateTransition', ['equip_id'=>$equipment->id]) }}"><i class="fas fa-wrench"></i></a>
                                 @endif
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
               @if($department_id=="" && $device_id=="" && $keyword=="")
                  {!! $eqrepairs->links() !!}
               @else
                  {!!  $eqrepairs->appends(['department_id'=>$department_id,'device_id'=>$device_id,'key'=>$keyword])->links() !!}     
               @endif
            </div>
         </div>
      </div>
   </section>
   <!-- /.content -->
</div>
@php
   $statusCorrected = get_statusCorrected();
@endphp
<div class="modal fade" id="modal_corrected" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
             <h4 class="modal-title" id="myModalLabel">{{ __('Bàn giao lại thiết bị')}}</h4>
         </div>
         <div class="modal-body">
         <form id="corrected_form"  action="" name="frmProducts" class="form-horizontal" method="POST" novalidate="">
               @csrf
               <div class="form-group">
                  <label class="control-label">{{ __('Tình trạng thiết bị') }} <small></small></label>
                  <select class="form-control select2"  name="status">
                        @foreach ($statusCorrected as $key => $items)
                           <option value="{{  $key }}">{{ $items }}</option>
                        @endforeach 
                  </select>  
               </div>
               <div class="modal-footer">
                  <button type="submit" class="btn btn-success">Lưu</button>
                  <button type="button" class="btn btn-warning" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">Hủy</span></button>
               </div>
         </form>
         </div>
     </div>
   </div>
</div>
<!-- Side Modal Top Right -->
@include('modals.modal_delete')
@include('modals.modal_deleteChoose')
@endsection