@extends('backends.templates.master')
@section('title',__('Thêm Thiết Bị'))
@section('content')
@php 
    $statusEquipments = get_statusEquipments();
    $get_statusRisk = get_statusRisk();
    $get_RegularInspection = get_RegularInspection();
    $array_value = array();
@endphp
<div class="content-wrapper">
    <section class="content">
        <div class="container">
            <div class="head">
                <a href="{{ route('equipment.index') }}" class="back-icon"><i class="fas fa-angle-left" aria-hidden="true"></i>{{ __('All') }}</a>
                <h1 class="title">{{ __('Thêm Thiết Bị') }}</h1>
            </div>
            <div class="main">
                @include('notices.index')
                <form action="{{ route('equipment.post') }}" class="dev-form" data-filter="{{ route('equiment.select') }}" method="POST" data-toggle="validator" role="form">
                    @csrf
                    <div class="row">
                        <div class="col-md-9">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label">{{ __('Tên thiết bị') }} <small>*</small></label>
                                            <input type="text" name="title" placeholder="Tên thiết bị ..." value="{{ Request::old('title') }}" class="form-control" data-error="{{ __('Vui lòng nhập tiêu đề hiển thị')}}" required>
                                            <div class="help-block with-errors"></div>
                                        </div>
                                    </div>
                                </div>
                     
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label">{{ __('Nhóm thiết bị') }} <small></small></label>
                                            <select class="form-control select2" id="eq_cates"  name="cate_id">
                                            <option value=""> Chọn nhóm thiết bị </option>
                                                @foreach ($cates as $cate)
                                                    <option value="{{ $cate->id }}">{{ $cate->title }}</option>
                                                @endforeach
                                            </select>
                                        <div class="help-block with-errors"></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group" id="equi_cat_device">
                                        <label class="control-label">{{ __('Loại thiết bị') }} <small></small></label>
                                        <select  class="select2 form-control" name="devices_id" >
                                            <option value=""> Chọn loại thiết bị </option>
                                            @foreach ($devices as $device)
                                                <option  value="{{$device->id}}">{{$device->title}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label">{{ __('Mức độ rủi ro') }} <small></small></label>
                                        <select class="form-control select2"  name="risk">
                                        <option value="">Chọn mức độ rủi ro</option>
                                            @foreach ($get_statusRisk as $key => $items)
                                                <option value="{{ $key }}">{{ $items }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>


                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                    <label class="control-label">{{ __('Đơn vị tính') }} <small> * </small></label>
                                        <select class="form-control select2"  name="unit_id">
                                        <option value="">Chọn đơn vị tính</option>
                                            @foreach ($units as $unit)
                                                <option value="{{ $unit->id }}">{{ $unit->title }}</option>
                                            @endforeach
                                        </select>
                                        <div class="help-block with-errors"></div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="control-label">{{ __('Số lượng') }} <small> * </small></label>
                                        <input type="number" min="0" name="amount"  value="{{ Request::old('amount') }}" class="form-control" data-error="{{ __('Vui lòng nhập số lượng')}}" required>
                                        <div class="help-block with-errors"></div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="control-label">{{ __('Giá nhập') }} <small></small></label>
                                        <input type="text" id="currency2"  name="import_price"  value="{{ Request::old('import_price') }}" data-inputmask="'alias': 'decimal', 'groupSeparator': ',', 'autoGroup': true,  'digitsOptional': false, 'prefix': ' đ ', 'digits': 0, 'placeholder': '0'" class="form-control" data-error="{{ __('Vui lòng nhập giá nhập')}}">
                                    </div>
                                </div>
                            </div>                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label">{{ __('Serial') }} <small>*</small></label>
                                        <input name="serial"  placeholder="Số serial ..." type="text" class="form-control" value="{{ Request::old('serial') }}" data-error="{{ __('Vui lòng nhập số serial')}}" required>
                                        <div class="help-block with-errors"></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label">{{ __('Model') }} <small>*</small></label>
                                        <input type="text" name="model" placeholder="Model ..." value="{{ Request::old('model') }}" class="form-control" data-error="{{ __('Vui lòng nhập model hiển thị')}}" required>
                                        <div class="help-block with-errors"></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label">{{ __('Nhà cung cấp') }} <small></small></label>
                                        <select class="form-control select2"  name="provider_id">
                                            <option value="">Chọn nhà cung cấp</option>
                                            @foreach ($providers as $provider)
                                                <option value="{{ $provider->id }}">{{ $provider->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="control-label">{{ __('Hãng sản xuất') }} <small> * </small></label>
                                            <input type="text" name="manufacturer" placeholder="Hãng sản xuất ..." value="{{ Request::old('manufacturer') }}" class="form-control" data-error="{{ __('Vui lòng nhập hãng sản xuất')}}" required>
                                        </div>
                                </div>
                                <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="control-label">{{ __('Xuất xứ') }} <small> * </small></label>
                                            <input type="text" name="origin" placeholder="Xuất xứ ..." value="{{ Request::old('origin') }}" class="form-control" data-error="{{ __('Vui lòng nhập xuất xứ')}}" required>
                                        </div>
                                </div>
                                          <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label">{{ __('Năm sản xuất') }}<small> * </small></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                        <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                                                </div>
                                                <input type="text" placeholder="yyyy" name="year_manufacture" value="{{ Request::old('year_manufacture') }}" class="form-control" data-inputmask-alias="datetime" data-inputmask-inputformat="yyyy" data-mask data-error="Vui lòng nhập năm sản xuất" required>
                                            </div>
                                            <div class="help-block with-errors"></div>
                                    </div>
                                </div>
                            </div>                         
                            <div class="row">           
                                <div class="col-md-3">
                                    <label class="control-label">{{ __('Kiểm định định kỳ') }} <small> * </small></label>
                                        <select class="form-control select2"  name="regular_inspection">
                                            <option value="">Chọn tháng</option>
                                            @foreach ($get_RegularInspection as $key => $value)
                                                <option value="{{ $key }}">{{ $value }}</option>
                                            @endforeach
                                        </select>
                                </div>    
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="control-label">{{ __('Ngày kiểm định lần đầu') }}<small> </small></label>
                                        <input name="first_inspection" type="date"  class="form-control"  value="{{ Request::old('first_inspection') }}" data-error="Vui lòng nhập ngày kiểm định lần đầu">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="control-label">{{ __('Ngày nhập kho') }}<small> </small></label>
                                        <input name="warehouse"  type="date" class="form-control" value="{{ Request::old('warehouse') }}" data-error="Vui lòng nhập ngày nhập kho">                                       
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="control-label">{{ __('Ngày hết hạn bảo hành') }}<small> </small></label>
                                        <input name="warranty_date"  type="date" class="form-control"  value="{{ Request::old('warranty_date') }}" data-error="Vui lòng nhập ngày hết hạn bảo hành">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">{{ __('Thông số kỹ thuật') }}<small> </small></label>
                                        <textarea name="specificat" class="form-control" rows="4" placeholder="Thông số kỹ thuật ..."  data-error="{{ __('Vui lòng nhập thông số kỹ thuật')}}">{{ Request::old('specificat') }}</textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">{{ __('Cấu hình kỹ thuật') }}<small> </small></label>
                                        <textarea name="configurat" class="form-control" rows="4" placeholder="Cấu hình kỹ thuật ..."  data-error="{{ __('Vui lòng nhập cấu hình kỹ thuật')}}">{{ Request::old('configurat') }}</textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="control-label">{{ __('Giá trị ban đầu') }}<small> </small></label>
                                        <div class="input-group">
                                            <input type="number" min="0" max="100" name="first_value" placeholder="0% ..." class="form-control" value="{{ Request::old('first_value') }}" data-error="{{ __('Vui lòng nhập giá trị ban đầu')}}">
                                            <div class="input-group-append">
                                                <span class="input-group-text"><i class="fas fa-percent"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="control-label">{{ __('Khấu hao hàng năm') }}<small> </small></label>
                                        <div class="input-group">
                                            <input type="number" min="0" max="100" name="depreciat" placeholder="0% ..." class="form-control" value="{{ Request::old('depreciat') }}" data-error="{{ __('Vui lòng nhập khấu hao hàng năm')}}">
                                            <div class="input-group-append">
                                                <span class="input-group-text"><i class="fas fa-percent"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">{{ __('Đơn vị bảo trì') }} <small></small></label>
                                        <select class="form-control select2"  name="maintenance_id">
                                            <option value="">Chọn đơn vị bảo trì</option>
                                            @foreach ($maintenances as $maintenance)
                                                <option value="{{ $maintenance->id }}">{{ $maintenance->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">{{ __('Đơn vị sửa chũa') }} <small></small></label>
                                        <select class="form-control select2"  name="repair_id">
                                            <option value="">Chọn đơn vị sửa chữa</option>
                                            @foreach ($repairs as $repair)
                                                <option value="{{ $repair->id }}">{{ $repair->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="control-label">{{ __('Khoa - Phòng Ban') }} <small></small></label>
                                            <select class="form-control select2" id="eq_department" name="department_id">
                                            <option value="">Chọn Khoa - Phòng Ban</option>
                                                @foreach ($departments as $department)
                                                    <option value="{{ $department->id }}">{{ $department->title }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                </div>
                                <div class="col-md-4">
                                     <div class="form-group">
                                        <label>{{ __('Năm sử dụng') }}</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                                            </div>
                                            <input type="text" placeholder="yyyy" name="year_use" value="{{ Request::old('year_use') }}" class="form-control" data-inputmask-alias="datetime" data-inputmask-inputformat="yyyy" data-mask>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group" id="officer_charge_id_device">
                                        <label class="control-label">{{ __('CB phòng VT phụ trách') }} <small></small></label>
                                            <select class="form-control select2"  name="officer_charge_id">
                                                <option value="">Chọn CB phòng VT phụ trách</option>
                                                @foreach ($users_vt as $user)
                                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                                @endforeach
                                            </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group" id="equipment_user_use_device">
                                        <label class="control-label">{{ __('CB sử dụng') }} <small></small></label>
                                            <select class="form-control select2"  name="equipment_user_use[]" multiple>
                                                @foreach ($users as $user)
                                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                                @endforeach
                                            </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group" id="officer_department_charge_id_device">
                                        <label class="control-label">{{ __('CB khoa phòng phụ trách') }} <small></small></label>
                                            <select class="form-control select2"  name="officer_department_charge_id">
                                                <option value="">Chọn CB khoa phòng phụ trách</option>
                                                @foreach ($users as $user)
                                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                                @endforeach
                                            </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group" id="equipment_user_training_device">
                                        <label class="control-label">{{ __('CB được đào tạo') }} <small></small></label>
                                            <select class="form-control select2"  name="equipment_user_training[]" multiple>
                                                @foreach ($users as $user)
                                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                                @endforeach
                                            </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="control-label">{{ __('Ghi chú') }} <small></small></label>
                                        <input type="text" name="note" placeholder="Ghi chú ..." value="{{ Request::old('note') }}" class="form-control" data-error="{{ __('Vui lòng nhập ghi chú')}}">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">{{ __('Dự án') }} <small></small></label>
                                        <select class="form-control select2"  name="bid_project_id">
                                                <option value="">Chọn dự án</option>
                                                @foreach ($projects as $project)
                                                    <option value="{{ $project->id }}">{{ $project->title }}</option>
                                                @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">{{ __('Quy trình sử dụng') }} <small></small></label>
                                        <input type="text" name="process" placeholder="Quy trình sử dụng ..." value="{{ Request::old('process') }}" class="form-control" data-error="{{ __('Vui lòng nhập quy trình sử dụng')}}">
                                    </div>
                                </div>
                            </div>
                            <div class="row">    
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="control-label">{{ __('Người nhập') }} <small></small></label>
                                        <select class="form-control select2" name="user_id">
                                            <option value="{{ Auth::user()->id }}">{{ Auth::user()->name }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="control-label">{{ __('Ngày nhập thông tin') }}<small> </small></label>
                                        <input name="first_information" type="date" class="form-control" value="{{ $cur_day }}" data-error="Vui lòng nhập ngày nhập thông tin">
                                    </div>
                                </div>
                            </div>                                                
                            @include('parts.attachment')
                            <div class="group-action">
                                <button type="submit" name="submit" class="btn btn-success">{{ __('Thêm') }}</button>
                                <a href="{{ route('equipment.index') }}" class="btn btn-secondary">{{ __('Trở về') }}</a>	
                            </div>
                        </div>
                        <div class="col-md-3">
                            <aside id="sb-image" class="card card-outline card-info">
                                <div class="card-header">
                                    <h3 class="card-title">{{ __('Ảnh đại diện') }}</h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse" data-toggle="tooltip" title="Collapse">
                                        <i class="fas fa-minus"></i></button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="frm-avatar" class="img-upload">
                                        <div class="image">
                                            <a href="{{ route('popupMediaAdmin') }}" class="library"><i class="fa fa-edit" aria-hidden="true"></i></a>
                                            {!! image('',230,230,__('Avatar')) !!}
                                            <input type="hidden" name="image" class="thumb-media" value="" />
                                        </div>
                                    </div>
                                </div>
                            </aside>
                        </div>
                    </div>
                </form>            
            </div>
        </div>
    </section>
</div>
@include('backends.media.library')
@include('backends.media.multi-library')
@endsection