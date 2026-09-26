@php
    $baseUrl = config('landing.hosted_url');
    $sitemapUrls = [route('home', absolute: false)];

    foreach (config('landing.docs') as $docsSection) {
        foreach ($docsSection['pages'] as $docsPage) {
            $sitemapUrls[] = route($docsPage['route'], absolute: false);
        }
    }
@endphp
{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach ($sitemapUrls as $sitemapPath)
    <url>
        <loc>{{ $baseUrl.$sitemapPath }}</loc>
    </url>
@endforeach
</urlset>
