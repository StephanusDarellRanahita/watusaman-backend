<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email</title>
</head>
<body>
    <div style="background-color: #bcbcbc; padding: 20px; ">
        <h1 style="text-decoration: underline; text-decoration-color: #C31F16;">Villa<span style="color: #C31F16;">W</span>atusaman</h1>
        <h2>Untuk : {{ $data['title'] }}</h2>
        <h2>{{ $data['name'] }}, </h2>
        <p>{{ $data['message'] }}</p>
        <p>OTP akan direset setiap 1 menit setelah digenerate.</p>
    </div>
</body>
</html>