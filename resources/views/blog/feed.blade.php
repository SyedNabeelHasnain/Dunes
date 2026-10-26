{!! '<' . '?xml version="1.0" encoding="UTF-8"?' . '>' !!}
@php
    $escapeCdata = static function ($text): string {
        if (is_array($text)) {
            $text = reset($text) ?: '';
        }
        return str_replace(']]>', ']]]]><![CDATA[>', (string) $text);
    };
@endphp
<rss version="2.0" 
     xmlns:content="http://purl.org/rss/1.0/modules/content/"
     xmlns:wfw="http://wellformedweb.org/CommentAPI/"
     xmlns:dc="http://purl.org/dc/elements/1.1/"
     xmlns:atom="http://www.w3.org/2005/Atom"
     xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
     xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
     xmlns:media="http://search.yahoo.com/mrss/">
  <channel>
    <title>{{ $channelTitle }}</title>
    <atom:link href="{{ $feedUrl }}" rel="self" type="application/rss+xml" />
    <link>{{ localized_route('blog.index') }}</link>
    <description>{{ $channelDescription }}</description>
    <lastBuildDate>{{ $lastBuildDate }}</lastBuildDate>
    <language>{{ app()->getLocale() ?: 'en' }}</language>
    <sy:updatePeriod>hourly</sy:updatePeriod>
    <sy:updateFrequency>1</sy:updateFrequency>
    <generator>Dunes Discovery Tourism CMS Engine</generator>
    <image>
      <url>{{ asset('images/logo.png') }}</url>
      <title>{{ $channelTitle }}</title>
      <link>{{ localized_route('blog.index') }}</link>
      <width>144</width>
      <height>40</height>
    </image>

    @foreach($posts as $post)
    @php
        $curLocale = app()->getLocale() ?: 'en';
        $postTitle = is_array($post->title) ? ($post->title[$curLocale] ?? reset($post->title) ?: '') : (string) ($post->title ?? '');
        $postAuthor = is_array($post->author_name) ? ($post->author_name[$curLocale] ?? reset($post->author_name) ?: '') : (string) ($post->author_name ?? '');
        $postContent = is_array($post->content) ? ($post->content[$curLocale] ?? reset($post->content) ?: '') : (string) ($post->content ?? '');
        $postExcerpt = is_array($post->excerpt) ? ($post->excerpt[$curLocale] ?? reset($post->excerpt) ?: '') : (string) ($post->excerpt ?? '');
        $cleanDesc = $postExcerpt ?: \Illuminate\Support\Str::limit(strip_tags($postContent), 240);
        $pubDate = ($post->published_at instanceof \Carbon\CarbonInterface)
            ? $post->published_at->toRfc2822String()
            : (is_string($post->published_at) && !empty($post->published_at) ? \Carbon\Carbon::parse($post->published_at)->toRfc2822String() : now()->toRfc2822String());
    @endphp
    <item>
      <title><![CDATA[{!! $escapeCdata($postTitle) !!}]]></title>
      <link>{{ localized_route('blog.show', $post->slug) }}</link>
      <pubDate>{{ $pubDate }}</pubDate>
      <dc:creator><![CDATA[{!! $escapeCdata($postAuthor ?: 'Dunes Discovery Editorial Team') !!}]]></dc:creator>
      @if($post->category)
      @php
          $catName = is_array($post->category->name) ? ($post->category->name[$curLocale] ?? reset($post->category->name) ?: '') : (string) ($post->category->name ?? '');
      @endphp
      <category><![CDATA[{!! $escapeCdata($catName) !!}]]></category>
      @endif
      @if($post->tags)
        @foreach($post->tags as $tag)
        @php
            $tagName = is_array($tag->name) ? (reset($tag->name) ?: '') : (string) ($tag->name ?? '');
        @endphp
        <category><![CDATA[{!! $escapeCdata($tagName) !!}]]></category>
        @endforeach
      @endif
      <guid isPermaLink="true">{{ localized_route('blog.show', $post->slug) }}</guid>
      <description><![CDATA[{!! $escapeCdata($cleanDesc) !!}]]></description>
      <content:encoded><![CDATA[{!! $escapeCdata($postContent) !!}]]></content:encoded>
      @if(!empty($post->featured_image))
      @php
          $imageUrl = $post->featured_image_url;
          $imageAlt = is_array($post->featured_image_alt) ? (reset($post->featured_image_alt) ?: '') : (string) ($post->featured_image_alt ?? '');
          $imgExt = strtolower(pathinfo((string) parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION));
          $imgMime = match ($imgExt) {
              'webp' => 'image/webp',
              'png' => 'image/png',
              'jpg', 'jpeg' => 'image/jpeg',
              'svg' => 'image/svg+xml',
              default => 'image/avif',
          };
      @endphp
      <media:content url="{{ $imageUrl }}" medium="image" type="{{ $imgMime }}">
        <media:title><![CDATA[{!! $escapeCdata($imageAlt ?: $postTitle) !!}]]></media:title>
      </media:content>
      <enclosure url="{{ $imageUrl }}" type="{{ $imgMime }}" length="0" />
      @endif
    </item>
    @endforeach
  </channel>
</rss>
