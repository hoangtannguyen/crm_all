@extends('backends.templates.master')
@section('title', __('Danh sách yêu cầu hỗ trợ'))
@section('content')
@php
   $statuss = get_statusRequest();
@endphp
<div id="list-events" class="content-wrapper events">
   <section class="content">
      <div class="head container">
         <h1 class="title">{{ __('Danh sách yêu cầu hỗ trợ') }}</h1>
      </div>
      <div class="main">
         <div class="row search-filter">
            <div class="col-md-8 filter">
               <ul class="nav-filter">
                  <li class="active"><a href="{{ route('request.index') }}">{{ __('Tất cả') }}</a></li>
                  <li class=""><a class="btn btn-success" style="color: #fff;" href="{{ route('request.create') }}">{{ _('Thêm mới') }}</a></li>
               </ul>
            </div>
            <div class="col-md-4 search-form">        
               <form action="{{ route('request.index') }}" method="GET">
                  <div class="row">
                     <div class="col-md-12">
                        <select class="form-control select2"  name="status">
                           <option value=""> Trạng thái </option>                  
                           @foreach ($statuss as $key => $items)
                              <option value="{{  $key }}"  {{ $status ==  $key ? 'selected' : '' }} >{{ $items }}</option>
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
                     <table class="table table-striped projects" role="table">
                        <thead class="thead">
                           <tr class="text-center">
                              <th>{{ __('STT') }}</th>
                              <th>{{ __('Họ tên') }}</th>
                              <th>{{ __('Phòng/Khoa') }}</th>
                              <th>{{ __('Ghi chú') }}</th>
                              <th>{{ __('Ảnh') }}</th>
                              <th>{{ __('File liên quan') }}</th>
                              <th>{{ __('Trạng thái') }}</th>
                              <th>{{ __('Tác vụ') }}</th>
                           </tr>
                        </thead>
                        <tbody class="tbody">
                           @if(!$list_request->isEmpty())
                              @foreach($list_request as $key => $request)
                                 <tr class="text-center">
                                    <td>{{ ++$key}}</td>
                                    <td>{{ isset($request->user) ? $request->user->name : ''}}</td>
                                    <td>{{ isset($request->department) ? $request->department->title : ''}}</td>
                                    <td>{{ $request->note }}</td>
                                    <td> 
                                       @foreach($request->attachments as $number => $value)
                                          <a href="{{ url('/public/uploads').'/'.$value->path }}">{{ $value->path }}{{($number!=count($request->attachments)-1) ? ',' : '' }}</a>
                                       @endforeach
                                    </td>
                                    <td>
                                       @foreach($request->files as $abc => $file)
                                          <a href="{{ url('/public/uploads').'/'.$file->path }}">{{ $file->path }}{{($abc!=count($request->files)-1) ? ',' : '' }}</a>
                                       @endforeach
                                    </td>
                                    <td>{{ $statuss[$request->status] }}</td>
                                    <td class="group-action action text-nowrap">
                                      <a class="btn btn-info btn-sm" href="{{ route('request.edit' , $request->id )}}"><i class="fas fa-pencil-alt"></i>{{__('Sửa')}}</a>
                                       <a class="btn btn-danger btn-sm" href="{{ route('request.delete',$request->id ) }}" data-toggle="modal" data-target="#sideModal" data-direct="modal-top-right"><i class="fas fa-trash"></i>{{__('Xóa')}}</a>
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