<!-- resources/views/app.blade.php -->
<!DOCTYPE html>
<html lang="en" class="dark">   <!-- ← class="dark" is required -->
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Restaurant POS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-pos-bg text-pos-text">
    <div id="app"></div>
</body>
</html>