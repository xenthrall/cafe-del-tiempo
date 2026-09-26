@php
    $brand = config('landing.name');
    $baseUrl = config('landing.hosted_url');
    $path = request()->getPathInfo();
    $canonicalUrl = $baseUrl.($path === '/' ? '/' : $path);
    $pageTitle = $path === '/' ? "{$brand} — {$title}" : "{$title} · {$brand}";
    $imageUrl = $baseUrl.'/images/og-image.png';
    $isIndexable = request()->getHost() === parse_url($baseUrl, PHP_URL_HOST);
@endphp

<title>{{ $pageTitle }}</title>
<meta name="description" content="{{ $description }}">
<meta name="robots" content="{{ $isIndexable ? 'index, follow, max-image-preview:large' : 'noindex, nofollow' }}">
<link rel="canonical" href="{{ $canonicalUrl }}">

<meta name="author" content="{{ config('landing.author.name') }}">
<meta name="application-name" content="{{ $brand }}">
<meta name="theme-color" content="#faf8f5" media="(prefers-color-scheme: light)">
<meta name="theme-color" content="#0e0c0a" media="(prefers-color-scheme: dark)">

<link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
<link rel="apple-touch-icon" href="{{ asset('images/icon-light.png') }}">
<link rel="sitemap" type="application/xml" href="{{ $baseUrl }}/sitemap.xml">

{{-- Open Graph --}}
<meta property="og:type" content="{{ $type }}">
<meta property="og:site_name" content="{{ $brand }}">
<meta property="og:title" content="{{ $pageTitle }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:url" content="{{ $canonicalUrl }}">
<meta property="og:image" content="{{ $imageUrl }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="{{ $brand }}: bóveda digital y finanzas personales">

{{-- Twitter / X --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $pageTitle }}">
<meta name="twitter:description" content="{{ $description }}">
<meta name="twitter:image" content="{{ $imageUrl }}">

@foreach ($schema as $schemaItem)
    <script type="application/ld+json">{!! json_encode(['@context' => 'https://schema.org'] + $schemaItem, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endforeach
