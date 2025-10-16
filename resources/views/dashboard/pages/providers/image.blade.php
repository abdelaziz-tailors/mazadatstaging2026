<img style="height:50px ; width: 50px" src="{{(Storage::disk('public')->exists($item->doctor_image)) ? Storage::disk('public')->url($item->doctor_image) : asset('images/stethoscope.png')}}">
