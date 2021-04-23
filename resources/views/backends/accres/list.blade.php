@extends('backends.templates.master')
@section('title', __('Kiểm định thiết bị'))
@section('content')
@php 
$data_link = [];
$regularInspection = get_RegularInspection();
$statusEquipments = get_statusEquipments();
if($keyword != '') $data_link['keyword'] = $keyword;
if($inspections_key != '') $data_link['inspections_key'] = $inspections_key;
@endphp
<div id="list-events" class="content-wrapper events">
   <section class="content">
      <div class="head container">
         <h1 class="title">{{ __('Danh sách thiết bị cần kiểm định') }}</h1>
         <a href="{{ route('accre.index',$data_link)  }}" class="btnprn btn float-right"> <i class="fas fa-print"></i> Xuất bản </a>
      </div>
      <div class="main">
         <div class="row search-filter">
            <div class="col-md-6 filter">
               <ul class="nav-filter">
                  <li class="active"><a href="{{ route('accre.index') }}">{{ __('Tất cả') }}</a></li>
               </ul>
            </div>
            <div class="col-md-6 search-form">
               <form action="{{ route('accre.index') }}" method="GET">
                  <div class="row">
                     <div class="col-md-8 s-key">
                        <input type="text" name="key" class="form-control s-key" placeholder="{{__('Nhập từ khóa')}}" value="{{$keyword}}">
                     </div>
                     <div class="col-md-4">
                        <select class="form-control select2"  name="inspection">
                                 <option value="" > Chọn thời gian định thiết bị </option>                  
                                 @foreach ($regularInspection as $key => $items)
                                    <option value="{{  $key }}"  {{ $inspections_key ==  $key ? 'selected' : '' }} >{{ $items }}</option>
                                 @endforeach 
                        </select>   
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
                           <tr>
                              <th>{{ __('Mã thiết bị') }}
                              </th>
                              <th>{{ __('Tên thiết bị') }}
                              </th>
                              <th>{{ __('Model') }}
                              </th>
                              <th>{{ __('Seria') }}
                              </th>
                              <th>{{ __('Tình trạng') }}
                              </th>
                              <th> {{ __('Khoa / phòng') }}
                              </th>
                              <th> {{ __('Thời gian lần cuối') }}
                              </th>
                              <th> {{ __('Thời gian tiếp theo') }}
                              </th>
                              <th class="group-action action">Thao tác</th>
                           </tr>
                        </thead>
                        <tbody class="tbody">
                        @if(!$equipments->isEmpty())
                           @foreach($equipments as $key => $equipment)
                        <tr>
                           <td>{{ $equipment->code }}</td>
                           <td>{{ $equipment->title }}</td>
                           <td>{{ $equipment->model }}</td>
                           <td>{{ $equipment->serial }}</td>
                           <td>{{ isset($statusEquipments[$equipment->status]) ? $statusEquipments[$equipment->status] :'' }}</td>     
                           <td>{{ isset($equipment->equipment_department->title) ? $equipment->equipment_department->title :'' }}</td>         
                           <td>@if($equipment->first_inspection && $equipment->regular_inspection)
                              @php 
                                 $time1 = strtotime($equipment->first_inspection);
                                 $time2 =  strtotime(date('Y-m-d'));
                                 $i = $equipment->regular_inspection;
                                 while (strtotime("+".$i."months", $time1) <= $time2) {
                                    $i = $i + $equipment->regular_inspection;
                                 }
                                 $abc =  date("Y-m-d", strtotime("+".$i."months", $time1));
                                 $a = $equipment->regular_inspection;
                                 echo date("Y-m-d", strtotime("-".$a."months", strtotime($abc)))
                              @endphp
                           @endif
                            </td>
                           <td>
                           @if($equipment->first_inspection && $equipment->regular_inspection)
                              @php 
                                 $time1 = strtotime($equipment->first_inspection);
                                 $time2 =  strtotime(date('Y-m-d'));
                                 $i = $equipment->regular_inspection;
                                 while (strtotime("+".$i."months", $time1) <= $time2) {
                                    $i = $i + $equipment->regular_inspection;
                                 }
                                 echo date("Y-m-d", strtotime("+".$i."months", $time1));
                              @endphp
                           @endif
                           </td>
                           <td class="group-action  text-center">
                              <a title="Hồ sơ thiết bị" href="{{ route('equipment.show' , $equipment->id )}}">
                                    <i class="fa fa-eye"></i>
                              </a> 
                              <a href="{{ route('accre.edit',$equipment->id) }}" title="{{ __('Lịch sử kiểm định') }}"><i class="fas fa-tools"></i></a>
                              <a class="ml-1 mr-1 accre-modal" data-title="{{ $equipment->title }}" data-href="{{ route('accre.post',$equipment->id) }}" title="{{ __('Tạo lịch sử kiểm định') }}"><i class="fas fa-plus-square"></i></a>
                           </td> 
                        </tr>
                           @endforeach
                           @else
                           <tr>
                              <td colspan="15">{{ __('No items!') }}</td>
                           </tr>
                           @endif
                        </tbody>
                     </table>
                  </div>
               </form>
               <div class="p-3">
                  {{ $equipments->appends($data_link)->links() }}
               </div>
            </div>
         </div>
      </div>
   </section>
   <!-- /.content -->
</div>
<!-- Side Modal Top Right -->


<div class="modal fade" id="modal_accre_show" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
           <div class="modal-content">
             <div class="modal-header">
               <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
               <h4 class="modal-title" id="myModalLabel"> Thông tin kiểm định </h4>
            </div>
            <div class="modal-body">
            <form id="accre_show_form"  action="" name="frmProducts"  class="form-horizontal" method="POST" novalidate="">
                  @csrf
                  <div class="form-group">
                     <label class="control-label">{{ __('Tên thiết bị') }} <small></small></label>
                     <input id="accre_title" type="text"  value="" class="form-control" disabled>
                     <div class="help-block with-errors"></div>
                  </div>
                  <div class="form-group">
                     <label class="control-label">{{ __('Thời gian kiểm định') }} <small></small></label>
                     <input type="date" name="time" value="{{ date('Y-m-d') }}" class="form-control" data-error="{{ __('Vui lòng chọn thời gian kiểm định')}}" required>
                     <div class="help-block with-errors"></div>
                  </div>
                  <div class="form-group">
                     <label class="control-label">{{ __('Đơn vị thực hiện') }} <small></small></label>
                     <input type="text" name="provider" value="{{ Request::old('provider') }}" class="form-control" data-error="{{ __('Vui lòng nhập đơn vị thực hiện')}}" required>
                     <div class="help-block with-errors"></div>
                  </div>
                  <div class="form-group">
                     <label class="control-label">{{ __('Nội dung kiểm định') }} <small></small></label>
                     <textarea name="content" class="editor form-control" data-error="{{ __('Vui lòng nhập nội dung kiểm định')}}" required>{{ Request::old('content') }}</textarea>
                     <div class="help-block with-errors"></div>
                  </div>
                  <div class="modal-footer">
                     <button type="submit" class="btn btn-success" onclick="return confirm('Bạn có chắc chắn thêm lịch kiểm định thiết bị này?')" value="add">Lưu</button>
                     <button type="button" class="btn btn-warning" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">Hủy</span></button>
                  </div>
            </form>
            </div>
        </div>
      </div>
  </div>
</div>



@include('modals.modal_delete')
@include('modals.modal_deleteChoose')
@endsection