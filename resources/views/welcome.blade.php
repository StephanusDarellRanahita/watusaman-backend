<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Laravel Vue Example</title>
</head>
<body>
    <div id="app">
        <welcome-component></welcome-component>
    </div>
    <script src="{{ mix('js/app.js') }}"></script>
</body>
</html>