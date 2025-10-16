@extends('dashboard.layouts.app')

@section('title') {{ TranslationHelper::translate('edit_role') }} @endsection

@section('content')
<div class="page-header">
    <div class="row">
        <div class="col">
            <h3 class="page-title">{{ TranslationHelper::translate('edit_role') }}</h3>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('admin.dashboard.index')}}">{{ TranslationHelper::translate('dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{route('admin.roles.index')}}">{{ TranslationHelper::translate('roles') }}</a></li>
                <li class="breadcrumb-item active">{{ TranslationHelper::translate('edit_role') }}</li>
            </ul>
        </div>
    </div>
</div>
<!--begin::Form-->
<div class="card">
    <div class="card-body">
        {!! Form::model($role, ['route' => ['admin.roles.update', $role->id], 'method' => 'PUT', 'files' => false, 'id' => 'kt_form_1', 'data-toggle' => 'validator']) !!}
            @include('dashboard.pages.roles._form')
        {!! Form::close() !!}
    </div>
</div>
@endsection


@section('scripts_lib')
<script>
        // Permissions
        $('body').on('change', '.sub-permission-group', function() {
        var group = $(this).attr('data-group');
        var has_checked = false;
        var permissions = $(".sub-permission-group-"+group);
        for (var i = 0; i < permissions.length; i++) {
            if ($(permissions[i]).is(':checked')) {
                has_checked = true;
            }
        }
        if (has_checked) {
            $('.main-permission-group-'+group).prop('checked', true);
            $('.main-permission-group-'+group).parent().addClass('disabled-permission');
        }
        else {
            $('.main-permission-group-'+group).parent().removeClass('disabled-permission');
        }
    });

    $('body').on('click', '.disabled-permission input', function(event) {
        event.preventDefault();
    });
</script>
@endsection
