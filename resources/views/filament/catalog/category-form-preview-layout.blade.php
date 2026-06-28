<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Category — UI Preview | Agricart ERP</title>
    @vite(['resources/css/filament/admin/theme.css', 'resources/js/filament/admin/category-form.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.9/dist/cdn.min.js"></script>
    <style>
        html, body {
            margin: 0;
            min-height: 100%;
        }

        .fi-body {
            min-height: 100vh;
        }
    </style>
</head>
<body class="fi-body">
    @include('filament.catalog.category-form-modal')
</body>
</html>
