<div class="row">
    <div class="col-12 d-flex">
        <div class="card flex-fill">
            <div class="card-body">
                <div class="row">
                    <div class="form-group col-lg-12">
                        {!! Form::label('title', TranslationHelper::translate('Title'), ['class'=>'form-label']) !!}<span style="color: red">*</span>
                        {!! Form::text('title', NULL, ['class' => 'form-control','readonly']) !!}
                    </div>


                    <div class="col-lg-12 form-group">
                        {!! Form::label('description', TranslationHelper::translate('Description'), ['class'=>'form-label']) !!}
                        {!! Form::textarea('description', NULL, ['class' => 'form-control', 'readonly']) !!}
                    </div>



                </div>
            </div>
        </div>
    </div>
</div>
