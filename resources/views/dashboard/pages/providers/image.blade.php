<img class="md-thumb" src="{{(Storage::disk('public')->exists($item->doctor_image)) ? Storage::disk('public')->url($item->doctor_image) : asset('images/stethoscope.png')}}">
