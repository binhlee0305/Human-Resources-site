@extends('layouts.app')
@section('content')
@php
    use App\Constants\DataConstant;
@endphp
<div class="page-wrap">
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3>@lang('language.Employee Information')</h3>
                        </div>
                        <div class="card-body">
                            <form class="forms-sample">
                                <div class="form-group row">
                                    <label for="exampleInputUsername2" class="col-sm-3 col-form-label">@lang('language.Employee ID')</label>
                                    <label class="col-sm-8 col-form-label">
                                        {{$employee->id}}
                                    </label>
                                </div>
                                <div class="form-group row">
                                    <label for="exampleInputUsername2" class="col-sm-3 col-form-label">@lang('language.Employee Name')</label>
                                    <div class="col-sm-9">
                                        <lable>{{$employee->name}}</lable>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="exampleInputUsername2" class="col-sm-3 col-form-label">@lang('language.Gender')</label>
                                    <div class="col-sm-9">
                                        <lable>{{$employee->gender}}</lable>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="exampleInputUsername2" class="col-sm-3 col-form-label">@lang('language.Join Date')</label>
                                    <div class="col-sm-9">
                                        <lable>{{$employee->join_date->format('m-d-Y')}}</lable>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="exampleInputUsername2" class="col-sm-3 col-form-label">@lang('language.Level')</label>
                                    <div class="col-sm-9">
                                        <lable>{{$level->level}}</lable>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="exampleInputUsername2" class="col-sm-3 col-form-label">@lang('language.Role')</label>
                                    <div class="col-sm-9">
                                        @if ($employee->privillege=="1")
                                        <lable>Admin</lable>
                                        @elseif ($employee->privillege=="2")
                                        <lable>PM</lable>
                                        @elseif ($employee->privillege=="3")
                                        <lable>Dev</lable>
                                        @else
                                        <lable>@lang('language.Other')</lable>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="exampleInputUsername2" class="col-sm-3 col-form-label">@lang('language.Status')</label>
                                    <div class="col-sm-9">
                                        <lable>{{$employee->status}}</lable>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="exampleInputUsername2" class="col-sm-3 col-form-label">@lang('language.Type')</label>
                                    <div class="col-sm-9">
                                        <lable>{{$type->type}}</lable>
                                    </div>
                                </div>

                                @if($user->privillege != DataConstant::DEVELOPER)
                                <button type="button" class="btn btn-primary mr-2" data-target="#exampleModalLong"
                                    data-toggle="modal">@lang('language.Edit')</button>
                                @endif
                            </form>

                            <div class="modal fade" id="exampleModalLong" tabindex="-1" role="dialog"
                                aria-labelledby="exampleModalLongLabel" aria-hidden="true" style="display: none;">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="exampleModalLongLabel">@lang('language.Edit Employeee')</h5>
                                            <button type="button" class="close" data-dismiss="modal"
                                                aria-label="Close"><span aria-hidden="true">×</span></button>
                                        </div>
                                        <div class="modal-body">
                                            <form class="forms-sample" method="post">
                                                <input type="hidden" name="_token" value="{{csrf_token()}}">
                                                <div class="row">
                                                    <div class="col-md-9">
                                                        <div class="form-group">
                                                            <label for="employee-name">@lang('language.Employee Name')</label>
                                                            <input type="text" class="form-control" id="employee-name"
                                                                name="employee-name" placeholder="Employee Name"
                                                                value="{{$employee->name}}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <label for="employee-id">@lang('language.Employee ID')</label>
                                                            <input type="text" class="form-control" id="employee-id"
                                                                placeholder="Employee ID" value="{{$employee->id}}"
                                                                disabled>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                        <label for="status">@lang('language.Status')</label>
                                                        <select class="form-control list" id="status" name="status">
                                                            <option value="{{$employee->status}}" selected="selected">
                                                                {{$employee->status}}
                                                                </option>
                                                                @if ($employee->status!=="Active")
                                                                <option value = "Active">Active</option>
                                                                @endif
                                                                @if ($employee->status!=="Pending")
                                                                <option value = "Pending">Pending</option>
                                                                @endif
                                                                @if ($employee->status!=="Disable")
                                                                <option value = "Disable">Disable</option>
                                                                @endif
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <label for="level">@lang('language.Level'):</label>
                                                            <select id="level" name="level" class="form-control list">
                                                                <option value="{{$level->id}}" selected="selected">
                                                                    {{$level->level}}
                                                                </option>
                                                                @foreach($listLevel as $d)
                                                                @if ($d->level!==$level->level)
                                                                <option value="{{$d->id}}">{{$d->level}}</option>
                                                                @endif
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <label for="gender">@lang('language.Gender')</label>
                                                            <select class="form-control list" id="gender" name="gender">
                                                                <option value="{{$employee->gender}}" selected="selected">
                                                                    {{$employee->gender}}
                                                                </option>
                                                                @if ($employee->gender!=="Male")
                                                                <option value = "Male">@lang('language.Male')</option>
                                                                @endif
                                                                @if ($employee->gender!=="Female")
                                                                <option value = "Female">@lang('language.Female')</option>
                                                                @endif
                                                                @if ($employee->gender!=="Other")
                                                                <option value = "Other">@lang('language.Other')</option>
                                                                @endif
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                        <label for="exampleSelectGender">@lang('language.Role')</label>
                                                            <select class="form-control" id="privillege" name="privillege">
                                                                @if ($employee->privillege=="1")
                                                                <option value="{{$employee->privillege}}" selected="selected">
                                                                    Admin
                                                                </option>
                                                                <option value="2">PM</option>
                                                                <option value="3">Dev</option>
                                                                <option value="0">@lang('language.Other')</option>
                                                                @endif
                                                                @if ($employee->privillege=="2")
                                                                <option value="{{$employee->privillege}}" selected="selected">
                                                                    PM
                                                                </option>
                                                                <option value="1">Admin</option>
                                                                <option value="3">Dev</option>
                                                                <option value="0">@lang('language.Other')</option>
                                                                @endif
                                                                @if ($employee->privillege=="3")
                                                                <option value="{{$employee->privillege}}" selected="selected">
                                                                    Dev
                                                                </option>
                                                                <option value="1">Admin</option>
                                                                <option value="2">PM</option>
                                                                <option value="0">@lang('language.Other')</option>
                                                                @endif
                                                                @if ($employee->privillege=="0")
                                                                <option value="{{$employee->privillege}}" selected="selected">
                                                                    Other
                                                                </option>
                                                                <option value="1">Admin</option>
                                                                <option value="2">PM</option>
                                                                <option value="3">Dev</option>
                                                                @endif
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="join-date">@lang('language.Join Date')</label>
                                                            <input type="date" class="form-control" id="join-date"
                                                                name="join-date"
                                                                value="{{$employee->join_date->format('Y-m-d')}}">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                        <label for="exampleSelectGender">@lang('language.Type')</label>
                                                            <select class="form-control" id="type" name="type">
                                                                <option value="{{$type->id}}" selected="selected">
                                                                    {{$type->type}}
                                                                </option>
                                                                @foreach($listType as $lt)
                                                                    @if ($lt->type!==$type->type)
                                                                        <option value="{{$lt->id}}">{{$lt->type}}</option>
                                                                    @endif
                                                                @endforeach
                                                            </select>                                                            
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-dismiss="modal">@lang('language.Close')</button>
                                                    <button type="submit" class="btn btn-primary">@lang('language.Submit')</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3>@lang('language.Employee Effort')</h3>
                        </div>
                        <form action="" method="" class="search-effort-employee">
                            @csrf
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <br>
                            <div class="container">
                                <div class="row">
                                    <div class="container-fluid">
                                        <div class="form-group row">
                                            <label for="date" class="col-sm-2 col-form-label">Start Date</label>
                                            <div class="col-sm-3">
                                                <input type="date" name="fromDate" id="fromDate" class="form-control input-sm" required/>
                                            </div>
                                            <label for="date" class="col-sm-2 col-form-label">End Date</label>
                                            <div class="col-sm-3">
                                                <input type="date" name="toDate" id="toDate" class="form-control input-sm" required/>
                                            </div>
                                            <div class="col-sm-2">
                                                <button type="submit" data-id="{{$employee->id}}" class="btn btn-confirmreset" name="search" title="Search"><i class="fas fa-search"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <div id="loading">
                            <img src="{{asset('assets/img/loading.gif')}}" alt="Loading..."/>
                        </div>
                        <div class="card-body">
                            <div id="spreadsheet" class="table" style="display: block; margin-top:-20px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endsection