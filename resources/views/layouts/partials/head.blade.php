<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $pageTitle }}</title>
<meta name="description" content="{{ $pageDescription }}">
<meta name="robots" content="{{ $robots }}">
<meta name="theme-color" content="#174bfa">
<link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
<link rel="icon" type="image/png" href="{{ asset('images/brand/favicon-32.png') }}" sizes="32x32">
<link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
<link rel="stylesheet" href="{{ asset('css/swipewire.css') }}?v={{ filemtime(public_path('css/swipewire.css')) }}">
<meta property="og:site_name" content="Swipewire">
<meta property="og:title" content="{{ $pageTitle }}">
<meta property="og:description" content="{{ $pageDescription }}">
<meta property="og:image" content="{{ asset('images/brand/swipewire-mark.png') }}">
