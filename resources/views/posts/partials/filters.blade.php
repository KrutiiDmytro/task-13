<div class="card">
    <div class="card-body">
        <h5 class="card-title">Фильтры</h5>

        <form action="{{ route('posts.index') }}" method="GET">
            {{-- Поиск по названию --}}
            <div class="mb-3">
                <label for="search_title" class="form-label">Поиск по названию</label>
                <input type="text"
                       id="search_title"
                       name="search_title"
                       class="form-control"
                       value="{{ request('search_title') }}">
            </div>

            {{-- Категория --}}
            <div class="mb-3">
                <label for="category_id" class="form-label">Категория</label>
                <select id="category_id" name="category_id" class="form-select">
                    <option value="">Все категории</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}"
                                @selected(request('category_id') == $category->id)>
                                {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Тег --}}
            <div class="mb-3">
                <label for="tag_id" class="form-label">Тег</label>
                <select id="tag_id" name="tag_id" class="form-select">
                    <option value="">Все теги</option>
                    @foreach($tags as $tag)
                        <option value="{{ $tag->id }}"
                                @selected(request('tag_id') == $tag->id)>
                                {{ $tag->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-primary w-100">Применить</button>
        </form>
    </div>
</div>
