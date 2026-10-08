{!! '<' . '?xml version="1.0" encoding="UTF-8"?' . '>' !!}
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
    <link>{{ $channelLink }}</link>
    <description>{{ $channelDescription }}</description>
    <lastBuildDate>{{ $lastBuildDate }}</lastBuildDate>
    <language>{{ $channelLanguage }}</language>
    <sy:updatePeriod>hourly</sy:updatePeriod>
    <sy:updateFrequency>1</sy:updateFrequency>
    <generator>Dunes Discovery Tourism CMS Engine</generator>
    <image>
      <url>{{ asset('images/logo.png') }}</url>
      <title>{{ $channelTitle }}</title>
      <link>{{ $channelLink }}</link>
      <width>144</width>
      <height>40</height>
    </image>

    @foreach($posts as $post)
    <item>
      <title><![CDATA[{{ $post->title }}]]></title>
      <link>{{ url('/blog/' . $post->slug) }}</link>
      <pubDate>{{ $post->published_at ? $post->published_at->toRfc2822String() : now()->toRfc2822String() }}</pubDate>
      <dc:creator><![CDATA[{{ $post->author_name ?: 'Dunes Discovery Editorial Team' }}]]></dc:creator>
      @if($post->category)
      <category><![CDATA[{{ $post->category->name }}]]></category>
      @endif
      @if($post->tags)
        @foreach($post->tags as $tag)
        <category><![CDATA[{{ $tag->name }}]]></category>
        @endforeach
      @endif
      <guid isPermaLink="true">{{ url('/blog/' . $post->slug) }}</guid>
      <description><![CDATA[{{ $post->excerpt ?: Str::limit(strip_tags($post->content), 240) }}]]></description>
      <content:encoded><![CDATA[{!! $post->content !!}]]></content:encoded>
      @if(!empty($post->featured_image))
      @php
          $imagePath = 'images/blog/' . preg_replace('/\.(jpg|jpeg|png|webp)$/i', '.avif', $post->featured_image);
          $imageUrl = asset($imagePath);
      @endphp
      <media:content url="{{ $imageUrl }}" medium="image">
        <media:title><![CDATA[{{ $post->featured_image_alt ?: $post->title }}]]></media:title>
      </media:content>
      <enclosure url="{{ $imageUrl }}" type="image/avif" length="0" />
      @endif
    </item>
    @endforeach
  </channel>
</rss>
