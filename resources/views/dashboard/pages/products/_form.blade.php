
<div class="col-12 d-flex">
    <div class="card flex-fill">
        <div class='card-body'>

        <div class="row">

            @if ($errors->any())
                <div class="col-12">
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
                        @if ($live_video->partners_type=='single')
                        <div class="col-lg-12 form-group">
                            <label class="form-label">{{ TranslationHelper::translate('Vendor') }}</label>

                            <select class="form-control" id="user_id" disabled name="">
                                <option value="">{{ TranslationHelper::translate('Select Vendor') }}</option>
                                @forelse($providers as $provider)
                                    <option @if($provider->id ==$live_video->partner_id ?? 0) selected  @endif value="{{ $provider->id }}">{{ $provider->name }}</option>

                                @empty
                                @endforelse
                            </select>
                            <input type="hidden" name="user_id" value="{{ $live_video->partner_id }}">
                        </div>



                        @else


                            <div class="col-lg-12 form-group">
                                <label class="form-label">{{ TranslationHelper::translate('Vendor') }}</label>
                                <select class="form-control @error('user_id') is-invalid @enderror" id="user_id" name="user_id">
                                    <option value="">{{ TranslationHelper::translate('Select Vendor') }}</option>
                                    @forelse($providers as $provider)
                                        <option @if (isset($data)) @if($provider->id ==$data->user_id ?? 0) selected @endif @endif value="{{ $provider->id }}">{{ $provider->name }}</option>

                                    @empty
                                    @endforelse
                                </select>
                                @error('user_id')
                                    <span class="text-danger small d-block mt-1">{{ $message }}</span>
                                @enderror
                            </div>
                        @endif

                            <div class="col-lg-12 form-group">
                                <label class="form-label" for="seller_id">{{ TranslationHelper::translate('seller') }}</label>
                                <select class="form-control @error('seller_id') is-invalid @enderror" id="seller_id" name="seller_id">
                                    <option value="">{{ TranslationHelper::translate('select_seller') }}</option>
                                    @forelse($sellers as $sellerUser)
                                        <option value="{{ $sellerUser->id }}" >
                                            {{ $sellerUser->name }}
                                        </option>
                                    @empty
                                    @endforelse
                                </select>
                                @error('seller_id')
                                    <span class="text-danger small d-block mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-lg-12 form-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="show_partner_name" name="show_partner_name" value="1" @if(isset($data) && $data->show_partner_name) checked @endif>
                                    <label class="form-check-label" for="show_partner_name">
                                        {{ TranslationHelper::translate('show_partner_name') }}
                                    </label>
                                </div>
                            </div>

                                <input type="hidden" name="video_id" value="{{$id ?? $data->live_video_id }}">

                            <div class="col-lg-4 form-group">
                                <label class="form-label">{{ TranslationHelper::translate('quantity') }}</label>
                                <input type="number" class="form-control @error('quantity') is-invalid @enderror" id="quantity" name="quantity" value="{{ old('quantity', $data->quantity ?? 0) }}" min="0">
                                @error('quantity')
                                    <span class="text-danger small d-block mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-12" id="single-piece-fields">
                                <div class="row">
                                    <div class="col-lg-4 form-group">
                                        <label class="form-label">{{ TranslationHelper::translate('Age') }} <span class="text-danger">*</span></label>
                                        <select class="form-control @error('age') is-invalid @enderror" id="age_id" name="age">
                                        <option value="">{{ TranslationHelper::translate('Select Age') }}</option>
                                        @forelse($ages as $age)
                                            <option @if (old('age', $data->age ?? '') == $age->name) selected @endif value="{{ $age->name }}">{{ $age->name }}</option>
                                        @empty
                                        @endforelse
                                        </select>
                                        @error('age')
                                            <span class="text-danger small d-block mt-1">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="col-lg-4 form-group">
                                        <label class="form-label">{{ TranslationHelper::translate('piece_multiplier_number') }}</label>
                                        <input type="text" class="form-control @error('piece_multiplier_number') is-invalid @enderror" id="piece_multiplier_number" name="piece_multiplier_number" value="{{ old('piece_multiplier_number', $data->piece_multiplier_number ?? '') }}" placeholder="">
                                        @error('piece_multiplier_number')
                                            <span class="text-danger small d-block mt-1">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-lg-4 form-group">
                                        <label class="form-label">{{ TranslationHelper::translate('identifier') }}</label>
                                        <input type="text" class="form-control @error('identifier') is-invalid @enderror" id="identifier" name="identifier" value="{{ old('identifier', $data->identifier ?? '') }}" placeholder="">
                                        @error('identifier')
                                            <span class="text-danger small d-block mt-1">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-lg-4 form-group">
                                        <label class="form-label">{{ TranslationHelper::translate('baham_count') }}</label>
                                        <input type="text" class="form-control @error('baham_count') is-invalid @enderror" id="baham_count" name="baham_count" value="{{ old('baham_count', $data->baham_count ?? '') }}" placeholder="">
                                        @error('baham_count')
                                            <span class="text-danger small d-block mt-1">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            @php
                                $existingPieces = old('pieces');
                                if ($existingPieces === null && isset($data)) {
                                    if ($data->relationLoaded('pieces') && $data->pieces->isNotEmpty()) {
                                        $existingPieces = $data->pieces->map(function ($piece) {
                                            return [
                                                'age' => $piece->age,
                                                'weight' => $piece->weight,
                                                'piece_multiplier_number' => $piece->piece_multiplier_number,
                                                'identifier' => $piece->identifier,
                                                'baham_count' => $piece->baham_count,
                                            ];
                                        })->toArray();
                                    } elseif ((int) ($data->quantity ?? 0) > 1) {
                                        $existingPieces = array_fill(0, (int) $data->quantity, [
                                            'age' => $data->age,
                                            'weight' => $data->weight,
                                            'piece_multiplier_number' => $data->piece_multiplier_number,
                                            'identifier' => $data->identifier,
                                            'baham_count' => $data->baham_count,
                                        ]);
                                    }
                                }
                                $existingPieces = $existingPieces ?? [];
                            @endphp

                            <div class="col-12 d-none" id="pieces-section">
                                <div class="card border mt-2 mb-3">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <strong>{{ TranslationHelper::translate('piece_details') }}</strong>
                                        <button type="button" class="btn btn-sm btn-outline-primary" id="apply-pieces-to-all">
                                            {{ TranslationHelper::translate('apply_same_to_all') }}
                                        </button>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-bordered mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>{{ TranslationHelper::translate('Age') }}</th>
                                                        <th>{{ TranslationHelper::translate('weight') }}</th>
                                                        <th>{{ TranslationHelper::translate('piece_multiplier_number') }}</th>
                                                        <th>{{ TranslationHelper::translate('identifier') }}</th>
                                                        <th>{{ TranslationHelper::translate('baham_count') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="pieces-rows"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                @error('pieces')
                                    <span class="text-danger small d-block mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            <template id="piece-row-template">
                                <tr>
                                    <td class="piece-number align-middle"></td>
                                    <td>
                                        <select class="form-control piece-age" data-name="age">
                                            <option value="">{{ TranslationHelper::translate('Select Age') }}</option>
                                            @foreach($ages as $age)
                                                <option value="{{ $age->name }}">{{ $age->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="number" step="0.01" class="form-control piece-weight" data-name="weight"></td>
                                    <td><input type="text" class="form-control piece-multiplier" data-name="piece_multiplier_number"></td>
                                    <td><input type="text" class="form-control piece-identifier" data-name="identifier"></td>
                                    <td><input type="text" class="form-control piece-baham" data-name="baham_count"></td>
                                </tr>
                            </template>

                            <script>
                                document.addEventListener('DOMContentLoaded', function () {
                                    const quantityInput = document.getElementById('quantity');
                                    const singleFields = document.getElementById('single-piece-fields');
                                    const piecesSection = document.getElementById('pieces-section');
                                    const piecesRows = document.getElementById('pieces-rows');
                                    const rowTemplate = document.getElementById('piece-row-template');
                                    const applyAllBtn = document.getElementById('apply-pieces-to-all');
                                    const existingPieces = @json($existingPieces);

                                    function renderPieceRows() {
                                        const quantity = parseInt(quantityInput.value || '0', 10);
                                        piecesRows.innerHTML = '';

                                        if (quantity <= 1) {
                                            singleFields.classList.remove('d-none');
                                            piecesSection.classList.add('d-none');
                                            return;
                                        }

                                        singleFields.classList.add('d-none');
                                        piecesSection.classList.remove('d-none');

                                        for (let i = 0; i < quantity; i++) {
                                            const row = rowTemplate.content.firstElementChild.cloneNode(true);
                                            row.querySelector('.piece-number').textContent = i + 1;

                                            const piece = existingPieces[i] || {};
                                            const fields = ['age', 'weight', 'piece_multiplier_number', 'identifier', 'baham_count'];

                                            fields.forEach(function (field) {
                                                const input = row.querySelector('[data-name="' + field + '"]');
                                                if (!input) {
                                                    return;
                                                }

                                                input.name = 'pieces[' + i + '][' + field + ']';
                                                if (piece[field] !== undefined && piece[field] !== null) {
                                                    input.value = piece[field];
                                                }
                                            });

                                            piecesRows.appendChild(row);
                                        }
                                    }

                                    applyAllBtn.addEventListener('click', function () {
                                        const firstRow = piecesRows.querySelector('tr');
                                        if (!firstRow) {
                                            return;
                                        }

                                        const values = {};
                                        firstRow.querySelectorAll('[data-name]').forEach(function (input) {
                                            values[input.dataset.name] = input.value;
                                        });

                                        piecesRows.querySelectorAll('tr').forEach(function (row, index) {
                                            if (index === 0) {
                                                return;
                                            }

                                            row.querySelectorAll('[data-name]').forEach(function (input) {
                                                input.value = values[input.dataset.name] || '';
                                            });
                                        });
                                    });

                                    quantityInput.addEventListener('input', renderPieceRows);
                                    renderPieceRows();
                                });
                            </script>

                            <div class="col-lg-6 form-group">
                                {!! Form::label('title', TranslationHelper::translate('lineage title') . ' *', ['class'=>'form-label']) !!}
                                {!! Form::text('title', old('title', $data->title ?? null), ['class' => 'form-control'.($errors->has('title') ? ' is-invalid' : ''), 'required']) !!}
                                @error('title')
                                    <span class="text-danger small d-block mt-1">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-lg-6 form-group">
                                {!! Form::label('title_ar', TranslationHelper::translate(' lineage title ar') . ' *', ['class'=>'form-label']) !!}
                                {!! Form::text('title_ar', old('title_ar', $data->title_ar ?? null), ['class' => 'form-control'.($errors->has('title_ar') ? ' is-invalid' : ''), 'required']) !!}
                                @error('title_ar')
                                    <span class="text-danger small d-block mt-1">{{ $message }}</span>
                                @enderror
                            </div>
                            {{-- <div class="col-lg-4 form-group">
                                {!! Form::label('start_price', TranslationHelper::translate('start price') . ' *', ['class'=>'form-label']) !!}
                                {!! Form::number('start_price', old('start_price', $data->start_price ?? null), ['step'=>"0.01", 'min'=>'0', 'class' => 'form-control'.($errors->has('start_price') ? ' is-invalid' : ''), 'required']) !!}
                                @error('start_price')
                                    <span class="text-danger small d-block mt-1">{{ $message }}</span>
                                @enderror
                            </div> --}}
                            <div class="col-lg-4 form-group">
                                {!! Form::label('bidding', TranslationHelper::translate('bidding Price') . ' *', ['class'=>'form-label']) !!}
                                {!! Form::number('bidding', old('bidding', $data->bidding ?? null), ['step'=>"0.01", 'min'=>'0', 'class' => 'form-control'.($errors->has('bidding') ? ' is-invalid' : ''), 'required']) !!}
                                @error('bidding')
                                    <span class="text-danger small d-block mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-lg-6 form-group">
                                {!! Form::label('address', TranslationHelper::translate('address'), ['class'=>'form-label']) !!}
                                {!! Form::textArea('address', old('address', $data->address ?? null), ['class' => 'form-control'.($errors->has('address') ? ' is-invalid' : '')]) !!}
                                @error('address')
                                    <span class="text-danger small d-block mt-1">{{ $message }}</span>
                                @enderror
                            </div>



                            <div class="col-lg-6 form-group">
                                {!! Form::label('health_certificate', TranslationHelper::translate('health certificate'), ['class'=>'form-label']) !!}
                                <input type="file" id="health_certificate" name="health_certificate" class="form-control @error('health_certificate') is-invalid @enderror" />
                                @error('health_certificate')
                                    <span class="text-danger small d-block mt-1">{{ $message }}</span>
                                @enderror
                                @if (isset($data) && $data->health_certificate)
                                <a href="{{ Storage::disk('public')->url($data->health_certificate) }}" target="_blank">
                                <img src="{{ Storage::disk('public')->url($data->health_certificate) }}" style="width: 100px; height: 100px;" alt="{{ $data->name }}" class="avatar-img rounded-circle img-fluid mt-2" />
                                </a>
                                @endif

                            </div>


                            <div class="col-lg-6 form-group">
                                {!! Form::label('information', TranslationHelper::translate('information'), ['class'=>'form-label']) !!}
                                {!! Form::textArea('information', old('information', $data->information ?? null), ['class' => 'form-control'.($errors->has('information') ? ' is-invalid' : '')]) !!}
                                @error('information')
                                    <span class="text-danger small d-block mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-lg-6 form-group">
                                {!! Form::label('information_ar', TranslationHelper::translate('information ar'), ['class'=>'form-label']) !!}
                                {!! Form::textArea('information_ar', old('information_ar', $data->information_ar ?? null), ['class' => 'form-control'.($errors->has('information_ar') ? ' is-invalid' : '')]) !!}
                                @error('information_ar')
                                    <span class="text-danger small d-block mt-1">{{ $message }}</span>
                                @enderror
                            </div>


                            <div class="col-lg-6 form-group">
                                {!! Form::label('terms', TranslationHelper::translate('terms'), ['class'=>'form-label']) !!}
                                {!! Form::textArea('terms', old('terms', $data->terms ?? null), ['class' => 'form-control'.($errors->has('terms') ? ' is-invalid' : '')]) !!}
                                @error('terms')
                                    <span class="text-danger small d-block mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-lg-6 form-group">
                                {!! Form::label('terms_ar', TranslationHelper::translate('terms ar'), ['class'=>'form-label']) !!}
                                {!! Form::textArea('terms_ar', old('terms_ar', $data->terms_ar ?? null), ['class' => 'form-control'.($errors->has('terms_ar') ? ' is-invalid' : '')]) !!}
                                @error('terms_ar')
                                    <span class="text-danger small d-block mt-1">{{ $message }}</span>
                                @enderror
                            </div>



                            @if ($live_video->type == 'recorded')
                            <div class="col-lg-6 form-group">
                                {!! Form::label('video', TranslationHelper::translate('video') . (isset($data) ? '' : ' *'), ['class'=>'form-label']) !!}
                                <input type="file" id="video" name="video" class="form-control @error('video') is-invalid @enderror" accept="video/mp4,video/avi,video/x-ms-wmv,video/x-flv" />
                                @error('video')
                                    <span class="text-danger small d-block mt-1">{{ $message }}</span>
                                @enderror
                                @if (isset($data) && $data->video)
                                <a href="{{ Storage::disk('public')->url($data->video) }}" target="_blank">
                                <embed src="{{ Storage::disk('public')->url($data->video) }}" style="width: 300px; height: 300px;" alt="{{ $data->name }}" class="mt-2" />
                                </a>
                                @endif
                            </div>

                            @endif



                        <div class="form-group col-lg-6">
                                {!! Form::label('image', TranslationHelper::translate('PNG Images'), ['class' => 'form-label']) !!}
                                <input type="file" multiple id="image_png" name="image[]" class="form-control @error('image') is-invalid @enderror @error('image.*') is-invalid @enderror" accept="image/*" />
                                @error('image')
                                    <span class="text-danger small d-block mt-1">{{ $message }}</span>
                                @enderror
                                @error('image.*')
                                    <span class="text-danger small d-block mt-1">{{ $message }}</span>
                                @enderror

                            @if (isset($data))
                            <div class="row mt-2">

                                @forelse(json_decode($data->image) as $feature)
                                    @if (Storage::disk('public')->exists($feature))

                                        <div class="form-group col-lg-2">
                                            <a href="{{ Storage::disk('public')->url($feature) }}" target="_blank">
                                                <img src="{{ Storage::disk('public')->url($feature) }}" class="img-fluid" />
                                            </a>
                                        </div>
                                    @endif


                                @empty

                                @endforelse

                            </div>
                            @endif



                        </div>
        <button type="submit" name="action" value="save" class="btn btn-primary">
                {{ TranslationHelper::translate('save') }}
            </button>
            @if (!isset($data))

            <button type="submit" name="action" value="add_product" class="btn btn-secondary">
                {{ TranslationHelper::translate('add Product') }}
            </button>
            @endif
            </div>
        </div>
    </div>
</div>
