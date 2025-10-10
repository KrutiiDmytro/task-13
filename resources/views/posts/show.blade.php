@extends('layouts.app')

@section('title', $post->title)

@section('content')
<div class="container py-4">
	<div class="row justify-content-center">
		<div class="col-lg-9">
			{{-- керування постом (тільки власник/адмін) --}}
			<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
				<a href="{{ route('posts.index') }}" class="btn btn-outline-secondary">← Назад к постам</a>

				@auth
					@if(auth()->id() === $post->user_id || (auth()->user()->is_admin ?? false))
						<div class="d-flex flex-wrap gap-2">
							<a href="{{ route('posts.edit', $post) }}" class="btn btn-warning">
								<i class="fas fa-edit me-1"></i> Редактировать
							</a>
							<form action="{{ route('posts.destroy', $post) }}" method="POST" class="d-inline">
								@csrf
								@method('DELETE')
								<button type="submit" class="btn btn-danger" onclick="return confirm('Удалить пост?')">
									<i class="fas fa-trash me-1"></i> Удалить
								</button>
							</form>
						</div>
					@endif
				@endauth
			</div>

			<article class="card shadow-sm">
				<div class="card-body">
					<h1 class="h3 card-title mb-2">{{ $post->title }}</h1>

					{{-- мета --}}
					<div class="text-muted small mb-3">
						{{ optional($post->date)->format('d.m.Y') ?? $post->created_at->format('d.m.Y H:i') }}
						| Автор: {{ $post->user->name ?? $post->author_name ?? 'Аноним' }}
						@if($post->category)
							| Категория:
							<a href="{{ route('posts.index', ['category' => $post->category->id]) }}">
								{{ $post->category->name }}
							</a>
						@endif
					</div>

					{{-- контент --}}
					<div class="card-text mb-3">
						{!! nl2br(e($post->content)) !!}
					</div>

					{{-- теги (клікабельні для фільтрації) --}}
					@if($post->tags->count())
						<div class="mt-2">
							<span class="text-muted small me-2">Теги:</span>
							@foreach($post->tags as $tag)
								<a href="{{ route('posts.index', ['tag' => $tag->id]) }}"
								   class="badge bg-secondary text-decoration-none me-1">#{{ $tag->name }}</a>
							@endforeach
						</div>
					@endif
				</div>

				{{-- изображение внизу карточки --}}
			<x-post-image :post="$post" 
						class="card-img-bottom" 
						:showPlaceholder="false" />
			</article>

			{{-- коментарі --}}
			<div class="mt-5">
				<h3>Комментарии ({{ $post->comments->count() }})</h3>

				@if($post->comments->count())
					@foreach($post->comments as $comment)
						<div class="card mb-3">
							<div class="card-body">
								<h6 class="card-subtitle mb-2 text-muted">{{ $comment->author_name ?? 'Аноним' }}</h6>
								<p class="card-text mb-1">{{ $comment->content }}</p>
								<small class="text-muted">{{ $comment->created_at->format('d.m.Y H:i') }}</small>
							</div>
						</div>
					@endforeach
				@else
					<p class="text-muted">Пока нет комментариев.</p>
				@endif

				{{-- Форма добавления комментария --}}
				<div class="card mt-4">
					<div class="card-header">
						<h5 class="mb-0">Добавить комментарий</h5>
					</div>
					<div class="card-body">
						<form action="{{ route('comments.store') }}" method="POST">
							@csrf
							<input type="hidden" name="post_id" value="{{ $post->id }}">

							<div class="mb-3">
								<label for="author" class="form-label">Ваше имя <span class="text-danger">*</span></label>
								<input type="text" 
									   class="form-control @error('author') is-invalid @enderror" 
									   id="author" 
									   name="author" 
									   value="{{ old('author', auth()->user()->name ?? '') }}" 
									   required>
								@error('author')
									<div class="invalid-feedback">{{ $message }}</div>
								@enderror
							</div>

							<div class="mb-3">
								<label for="content" class="form-label">Комментарий <span class="text-danger">*</span></label>
								<textarea class="form-control @error('content') is-invalid @enderror" 
										  id="content" 
										  name="content" 
										  rows="4" 
										  required>{{ old('content') }}</textarea>
								@error('content')
									<div class="invalid-feedback">{{ $message }}</div>
								@enderror
							</div>

							<button type="submit" class="btn btn-primary">
								<i class="fas fa-paper-plane me-1"></i> Отправить комментарий
							</button>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection