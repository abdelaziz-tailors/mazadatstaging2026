<img class="md-thumb" src="{{(Storage::disk('public')->exists($item->image)) ? Storage::disk('public')->url($item->image) : asset('images/stethoscope.png')}}">
