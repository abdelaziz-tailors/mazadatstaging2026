
@php
    $auctionOwner = $live_video->partnerData ?? \App\Models\User\User::find($live_video->partner_id);
    $auctionOwnerName = $auctionOwner->name ?? '—';
    $sellerSource = old('seller_source', isset($data) && $data->seller_id ? 'from_list' : 'owner');

    $existingPieces = old('pieces');
    if ($existingPieces === null && isset($data) && $data->relationLoaded('pieces') && $data->pieces->isNotEmpty()) {
        $existingPieces = $data->pieces->map(function ($piece) {
            return [
                'age' => $piece->age,
                'weight' => $piece->weight,
                'identifier' => $piece->identifier,
                'baham_count' => $piece->baham_count,
            ];
        })->toArray();
    }
    $existingPieces = $existingPieces ?? [];
    $initialQuantity = (int) old('quantity', $data->quantity ?? 1);
@endphp

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

            <div class="col-12">
                <h5 class="mb-3">{{ TranslationHelper::translate('item_details') }}</h5>
            </div>

            <input type="hidden" name="video_id" value="{{ $id ?? $data->live_video_id }}">

            <div class="col-12 form-group">
                <label class="form-label">{{ TranslationHelper::translate('seller') }} <span class="text-danger">*</span></label>
                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="border rounded p-3 d-block mb-0 h-100 @if($sellerSource === 'owner') border-primary @endif" style="cursor:pointer;">
                            <input type="radio" name="seller_source" value="owner" class="form-check-input me-2" {{ $sellerSource === 'owner' ? 'checked' : '' }}>
                            <strong>{{ TranslationHelper::translate('auction_owner') }}</strong>
                            <div class="text-muted small mt-1">{{ $auctionOwnerName }}</div>
                        </label>
                    </div>
                    <div class="col-md-6">
                        <label class="border rounded p-3 d-block mb-0 h-100 @if($sellerSource === 'from_list') border-primary @endif" style="cursor:pointer;">
                            <input type="radio" name="seller_source" value="from_list" class="form-check-input me-2" {{ $sellerSource === 'from_list' ? 'checked' : '' }}>
                            <strong>{{ TranslationHelper::translate('seller_from_list') }}</strong>
                        </label>
                    </div>
                </div>
                <div class="mt-2 @if($sellerSource !== 'from_list') d-none @endif" id="seller-from-list-field">
                    <select class="form-control select @error('seller_id') is-invalid @enderror" id="seller_id" name="seller_id">
                        <option value="">{{ TranslationHelper::translate('select_seller') }}</option>
                        @forelse($sellers as $sellerUser)
                            <option value="{{ $sellerUser->id }}" {{ old('seller_id', isset($data) ? $data->seller_id : '') == $sellerUser->id ? 'selected' : '' }}>
                                {{ $sellerUser->name }}
                            </option>
                        @empty
                        @endforelse
                    </select>
                    @error('seller_id')
                        <span class="text-danger small d-block mt-1">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            @if ($live_video->partners_type == 'single')
                <input type="hidden" name="user_id" value="{{ $live_video->partner_id }}">
            @else
                <div class="col-lg-6 form-group">
                    <label class="form-label">{{ TranslationHelper::translate('Vendor') }}</label>
                    <select class="form-control select @error('user_id') is-invalid @enderror" id="user_id" name="user_id">
                        <option value="">{{ TranslationHelper::translate('Select Vendor') }}</option>
                        @forelse($providers as $provider)
                            <option value="{{ $provider->id }}" {{ old('user_id', isset($data) ? $data->user_id : '') == $provider->id ? 'selected' : '' }}>{{ $provider->name }}</option>
                        @empty
                        @endforelse
                    </select>
                    @error('user_id')
                        <span class="text-danger small d-block mt-1">{{ $message }}</span>
                    @enderror
                </div>
            @endif

            <div class="col-lg-6 form-group">
                {!! Form::label('title_ar', TranslationHelper::translate('_lineage_title_ar') . ' *', ['class'=>'form-label']) !!}
                {!! Form::text('title_ar', old('title_ar', $data->title_ar ?? null), ['class' => 'form-control'.($errors->has('title_ar') ? ' is-invalid' : ''), 'required', 'placeholder' => TranslationHelper::translate('_lineage_title_ar')]) !!}
                @error('title_ar')
                    <span class="text-danger small d-block mt-1">{{ $message }}</span>
                @enderror
            </div>

            <div class="col-lg-6 form-group">
                {!! Form::label('information_ar', TranslationHelper::translate('item_description'), ['class'=>'form-label']) !!}
                {!! Form::textArea('information_ar', old('information_ar', $data->information_ar ?? null), ['class' => 'form-control'.($errors->has('information_ar') ? ' is-invalid' : ''), 'placeholder' => TranslationHelper::translate('item_description'), 'rows' => 3]) !!}
                @error('information_ar')
                    <span class="text-danger small d-block mt-1">{{ $message }}</span>
                @enderror
            </div>

            <div class="col-lg-6 form-group">
                {!! Form::label('bidding', TranslationHelper::translate('bidding_price') . ' *', ['class'=>'form-label']) !!}
                {!! Form::number('bidding', old('bidding', $data->bidding ?? null), ['step'=>"0.01", 'min'=>'0', 'class' => 'form-control'.($errors->has('bidding') ? ' is-invalid' : ''), 'required', 'placeholder' => TranslationHelper::translate('bidding_price')]) !!}
                @error('bidding')
                    <span class="text-danger small d-block mt-1">{{ $message }}</span>
                @enderror
            </div>

            <div class="col-lg-4 form-group">
                <label class="form-label">{{ TranslationHelper::translate('quantity') }}</label>
                <input type="number" class="form-control @error('quantity') is-invalid @enderror" id="quantity" name="quantity" value="{{ old('quantity', $data->quantity ?? 1) }}" min="1">
                @error('quantity')
                    <span class="text-danger small d-block mt-1">{{ $message }}</span>
                @enderror
            </div>

            <div class="col-lg-4 form-group">
                <label class="form-label">{{ TranslationHelper::translate('piece_multiplier_number') }}</label>
                <input type="number" class="form-control @error('piece_multiplier_number') is-invalid @enderror" id="piece_multiplier_number" name="piece_multiplier_number" value="{{ old('piece_multiplier_number', $data->piece_multiplier_number ?? 5) }}" min="1">
                @error('piece_multiplier_number')
                    <span class="text-danger small d-block mt-1">{{ $message }}</span>
                @enderror
            </div>

            <div class="col-12" id="pieces-section">
                <div class="card border mt-2 mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <strong>{{ TranslationHelper::translate('piece_details') }}</strong>
                        <button type="button" class="btn btn-sm btn-outline-primary @if($initialQuantity <= 1) d-none @endif" id="apply-pieces-to-all">
                            {{ TranslationHelper::translate('apply_same_to_all') }}
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ TranslationHelper::translate('Age') }} <span class="text-danger">*</span></th>
                                        <th>{{ TranslationHelper::translate('weight') }}</th>
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
                @error('pieces.*.age')
                    <span class="text-danger small d-block mt-1">{{ $message }}</span>
                @enderror
            </div>

            <template id="piece-row-template">
                <tr>
                    <td class="piece-number align-middle"></td>
                    <td>
                        <select class="form-control piece-age" data-name="age" >
                            <option value="">{{ TranslationHelper::translate('select_age') }}</option>
                            @foreach($ages as $age)
                                <option value="{{ $age->name }}">{{ $age->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td><input type="number" step="0.01" class="form-control piece-weight" data-name="weight" placeholder="{{ TranslationHelper::translate('weight') }}"></td>
                    <td><input type="text" class="form-control piece-identifier" data-name="identifier" placeholder="{{ TranslationHelper::translate('identifier') }}"></td>
                    <td><input type="number" min="0" class="form-control piece-baham" data-name="baham_count" placeholder="0" value="0"></td>
                </tr>
            </template>

            <div class="col-12"><hr></div>
            <div class="col-12">
                <h5 class="mb-3">{{ TranslationHelper::translate('item_photos') }}</h5>
            </div>

            <div class="form-group col-lg-6">
                {!! Form::label('image', TranslationHelper::translate('item_photos'), ['class' => 'form-label']) !!}
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

            @if ($live_video->type == 'recorded')
            <div class="col-lg-6 form-group">
                {!! Form::label('video', TranslationHelper::translate('video') . (isset($data) ? '' : ' *'), ['class'=>'form-label']) !!}
                <input type="file" id="video" name="video" class="form-control @error('video') is-invalid @enderror" accept="video/mp4,video/avi,video/x-ms-wv,video/x-flv" />
                @error('video')
                    <span class="text-danger small d-block mt-1">{{ $message }}</span>
                @enderror
                @if (isset($data) && $data->video)
                <a href="{{ Storage::disk('public')->url($data->video) }}" target="_blank">
                    <embed src="{{ Storage::disk('public')->url($data->video) }}" style="width: 300px; height: 300px;" class="mt-2" />
                </a>
                @endif
            </div>
            @endif

        <button type="submit" name="action" value="save" class="btn btn-primary mt-3">
                {{ TranslationHelper::translate('save') }}
            </button>
            @if (!isset($data))
            <button type="submit" name="action" value="add_product" class="btn btn-outline-primary mt-3">
                {{ TranslationHelper::translate('add_item') }}
            </button>
            @endif
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sellerRadios = document.querySelectorAll('input[name="seller_source"]');
        const sellerField = document.getElementById('seller-from-list-field');
        const quantityInput = document.getElementById('quantity');
        const piecesRows = document.getElementById('pieces-rows');
        const rowTemplate = document.getElementById('piece-row-template');
        const applyAllBtn = document.getElementById('apply-pieces-to-all');
        const existingPieces = @json($existingPieces);

        sellerRadios.forEach(function (radio) {
            radio.addEventListener('change', function () {
                if (this.value === 'from_list') {
                    sellerField.classList.remove('d-none');
                    // select2 computes its width while the field is still
                    // display:none (init runs on page load in script.js),
                    // so it renders at 0px until re-initialized now that
                    // the field is actually visible.
                    if (window.jQuery && jQuery.fn.select2) {
                        jQuery('#seller_id').select2('destroy').select2({ minimumResultsForSearch: -1, width: '100%' });
                    }
                } else {
                    sellerField.classList.add('d-none');
                    document.getElementById('seller_id').value = '';
                }
            });
        });

        function renderPieceRows() {
            const quantity = Math.max(1, parseInt(quantityInput.value || '1', 10));
            piecesRows.innerHTML = '';

            if (applyAllBtn) {
                applyAllBtn.classList.toggle('d-none', quantity <= 1);
            }

            for (let i = 0; i < quantity; i++) {
                const row = rowTemplate.content.firstElementChild.cloneNode(true);
                row.querySelector('.piece-number').textContent = i + 1;

                const piece = existingPieces[i] || {};
                ['age', 'weight', 'identifier', 'baham_count'].forEach(function (field) {
                    const input = row.querySelector('[data-name="' + field + '"]');
                    if (!input) return;
                    input.name = 'pieces[' + i + '][' + field + ']';
                    if (piece[field] !== undefined && piece[field] !== null && piece[field] !== '') {
                        input.value = piece[field];
                    }
                });

                piecesRows.appendChild(row);
            }
        }

        if (applyAllBtn) {
            applyAllBtn.addEventListener('click', function () {
                const firstRow = piecesRows.querySelector('tr');
                if (!firstRow) return;

                const values = {};
                firstRow.querySelectorAll('[data-name]').forEach(function (input) {
                    values[input.dataset.name] = input.value;
                });

                piecesRows.querySelectorAll('tr').forEach(function (row, index) {
                    if (index === 0) return;
                    row.querySelectorAll('[data-name]').forEach(function (input) {
                        input.value = values[input.dataset.name] || '';
                    });
                });
            });
        }

        quantityInput.addEventListener('input', renderPieceRows);
        renderPieceRows();
    });
</script>
