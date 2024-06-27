<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email</title>
</head>
<body>
    <div style="background-color: #bcbcbc; padding: 20px;">
        <h2>Untuk : {{ $data['title'] }}</h2>
        <h2>{{ $data['name'] }}, </h2>
        <p>{{ $data['message'] }}</p>
    </div>
</body>
</html>