<div class="row">
    <div class="col-lg-12 form-group">
        {!! Form::label('name', TranslationHelper::translate('name'), ['class'=>'form-label']) !!}
        {!! Form::text('name', NULL, ['class' => 'form-control']) !!}
    </div>
		@php
		$i = 1;
		$permission_group = 1;
		@endphp
		@foreach ($permissions as $permission)
			@php $permission_group++; $has_checked_subs = false; @endphp
			<div class="col-12 col-sm-6 col-lg-3 mb-3">
				<div class="border border-1 rounded p-2 bg-light">
					<div class="bg-primary p-2 rounded text-white fw-bold">
						{{ TranslationHelper::translate(''.$permission['name']) }}
					</div>
					@foreach ($permission['permissions'] as $key => $value)
						@if(isset($role))
							@if (!str_contains(strtolower($key), 'view') && $role->hasPermissionTo($key))
								@php $has_checked_subs = true; @endphp
							@endif
						@endif
					@endforeach
					@foreach ($permission['permissions'] as $key => $value)
						@php
						$i++;
						@endphp
						<div class="form-check my-2 @if($has_checked_subs && str_starts_with(strtolower($key), 'view')) disabled-permission @endif ">
							<input class="form-check-input @if(str_starts_with(strtolower($key), 'view') && count($permission['permissions']) > 1) main-permission-group-{{ $permission_group }} @else sub-permission-group sub-permission-group-{{ $permission_group }} @endif "
							@if(!str_contains(strtolower($key), 'view') && count($permission['permissions']) > 1) data-group="{{ $permission_group }}" @endif
							id="permissionCheck{{ $i }}" type="checkbox"
							@if(isset($role)) @if($role->hasPermissionTo($key)) checked @endif @endif
							name="permission[]" value="{{ $key }}"  />
							<label class="form-check-label" for="permissionCheck{{ $i }}">{{ TranslationHelper::translate(''.$value) }}</label>
						</div>
					@endforeach
				</div>
			</div>
		@endforeach
</div>
<button type="submit" class="btn btn-primary" id="kt_submit">{{ TranslationHelper::translate('save') }}</button>
