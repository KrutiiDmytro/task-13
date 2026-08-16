@php
    // Supplied by the view composer in AppServiceProvider.
    $navCategories = $navCategories ?? collect();
    $popularTags = $popularTags ?? collect();
    $activeCategory = request()->routeIs('public.category') ? request()->route('slug') : null;
    $activeTag = request()->routeIs('public.tag') ? request()->route('slug') : null;
@endphp

<aside class="sidebar">

    @if($navCategories->isNotEmpty())
        <section class="widget">
            <h2 class="widget__title">Categories</h2>
            <ul class="widget__list">
                @foreach($navCategories as $category)
                    <li>
                        <a class="widget__link {{ $activeCategory === $category->slug ? 'is-active' : '' }}"
                           href="{{ route('public.category', $category->slug) }}">
                            <span>{{ $category->name }}</span>
                            <span class="widget__count">{{ $category->posts_count }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    @if($popularTags->isNotEmpty())
        <section class="widget">
            <h2 class="widget__title">Popular tags</h2>
            <div class="tag-list">
                @foreach($popularTags as $tag)
                    <a class="tag-pill {{ $activeTag === $tag->slug ? 'is-active' : '' }}"
                       href="{{ route('public.tag', $tag->slug) }}">{{ $tag->name }}</a>
                @endforeach
            </div>
        </section>
    @endif

    <section class="widget">
        <h2 class="widget__title">About</h2>
        <p style="margin: 0; font-size: 0.9rem; color: var(--text-muted);">
            News, tips, tutorials and reviews for people who take their games seriously.
        </p>
    </section>

</aside>
