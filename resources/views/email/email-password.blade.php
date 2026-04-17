<html>
<body>
<h5>Dear {{ $name }}</h5>
<p>Use this code to reset your password:</p>
<p><strong>{{ $code ?? $password }}</strong></p>
<small>This code expires soon; if you did not request a reset, you can ignore this email.</small>
</body>
</html>
