@extends('admin.layouts.admin')

@section('title', 'Edit Article')

@push('styles')
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/article-form.css') }}">
@endpush

@section('content')
<div class="page-header">
    <h2 class="page-title"><i class="bi bi-pencil"></i> Edit Article</h2>
    <a href="{{ route('admin.articles.index') }}" class="btn btn--secondary">
        <i class="bi bi-arrow-left"></i> Back to List
    </a>
</div>

<form action="{{ route('admin.articles.update', $article) }}" method="POST" enctype="multipart/form-data" id="articleForm">
    @csrf
    @method('PUT')
    
    <div class="content-grid">
        <div class="content-main">
            <div class="card">
                <div class="card__header">
                    <i class="bi bi-layout-text-window"></i> Article Content
                </div>
                
                <div class="form-group">
                    <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-input @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $article->title) }}" required>
                    @error('title') <span class="form-text-error">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label for="excerpt" class="form-label">Excerpt</label>
                    <textarea class="form-textarea @error('excerpt') is-invalid @enderror" id="excerpt" name="excerpt" rows="3">{{ old('excerpt', $article->excerpt) }}</textarea>
                    <span class="form-text-muted">Max 500 characters. This will appear on article cards.</span>
                    @error('excerpt') <span class="form-text-error">{{ $message }}</span> @enderror
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="content" class="form-label">Content <span class="text-danger">*</span></label>
                    <div id="quillEditor"></div>
                    <input type="hidden" name="content" id="contentInput" value="{{ old('content', $article->content) }}">
                    @error('content') <span class="form-text-error">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <div class="content-sidebar">
            
            <div class="card" style="margin-bottom: 1.5rem;">
                <div class="card__header">
                    <i class="bi bi-tags"></i> Category
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <select class="form-select @error('category_id') is-invalid @enderror" name="category_id" required>
                        <option value="">-- Select a Category --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $article->category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id') <span class="form-text-error">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="card" style="margin-bottom: 1.5rem;">
                <div class="card__header">
                    <i class="bi bi-image"></i> Thumbnail
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    
                    @if($article->thumbnail)
                        <div class="mb-3">
                            <span class="form-label" style="font-size: 0.8rem; color: var(--gray-500);">Current Thumbnail</span>
                            <img src="{{ Storage::url($article->thumbnail) }}" alt="Current Thumbnail" class="rounded-img" style="width: 100%; margin-bottom: 1rem; border: 1px solid var(--gray-200);">
                        </div>
                    @endif

                    <label for="thumbnail" class="form-label">
                        {{ $article->thumbnail ? 'Change Thumbnail' : 'Upload New Thumbnail' }}
                    </label>
                    <input type="file" class="form-input @error('thumbnail') is-invalid @enderror" id="thumbnail" name="thumbnail" accept="image/*" onchange="previewImage(event)" style="padding: 0.5rem; background: var(--gray-50); cursor: pointer;">
                    <span class="form-text-muted">Format: JPEG, PNG, WebP. Max 2MB.</span>
                    @error('thumbnail') <span class="form-text-error">{{ $message }}</span> @enderror
                    
                    <div id="imagePreview" class="image-preview-box d-none mt-3">
                        <span class="form-label" style="font-size: 0.8rem; color: var(--accent); position: absolute; background: rgba(255,255,255,0.8); padding: 2px 8px; border-radius: 4px; margin: 8px;">New Preview</span>
                        <img id="preview" src="" alt="Preview" style="width: 100%; max-height: 250px; object-fit: cover;">
                    </div>
                </div>
            </div>

            <div class="sticky-actions">
                <div class="card">
                    <div class="card__header">
                        <i class="bi bi-rocket-takeoff"></i> Publish & Update
                    </div>
                    
                    <div class="form-group">
                        <label class="form-switch" for="is_published">
                            <input type="checkbox" id="is_published" name="is_published" value="1" {{ old('is_published', $article->is_published) ? 'checked' : '' }}>
                            <span class="form-label" style="margin: 0; font-weight: 500; cursor: pointer;">Published</span>
                        </label>
                        @if($article->published_at)
                            <span class="form-text-muted mt-2" style="font-size: 0.75rem;">
                                <i class="bi bi-clock-history"></i> Published on {{ $article->published_at->format('d M Y, H:i') }}
                            </span>
                        @endif
                    </div>

                    <div class="form-group">
                        <label for="read_time" class="form-label">Read Time (minutes)</label>
                        <input type="number" class="form-input @error('read_time') is-invalid @enderror" id="read_time" name="read_time" value="{{ old('read_time', $article->read_time) }}" min="1">
                        @error('read_time') <span class="form-text-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <div style="background: var(--gray-50); padding: 0.85rem; border-radius: 8px; border: 1px solid var(--gray-200); font-size: 0.8rem; color: var(--gray-600);">
                            <div class="d-flex align-center justify-between mb-2">
                                <span><i class="bi bi-eye"></i> Views</span>
                                <strong>{{ $article->views }}</strong>
                            </div>
                            <div class="d-flex align-center justify-between mb-2">
                                <span><i class="bi bi-calendar-plus"></i> Created</span>
                                <strong>{{ $article->created_at->diffForHumans() }}</strong>
                            </div>
                            <div class="d-flex align-center justify-between">
                                <span><i class="bi bi-pencil-square"></i> Updated</span>
                                <strong>{{ $article->updated_at->diffForHumans() }}</strong>
                            </div>
                        </div>
                    </div>

                    <hr style="border: none; border-top: 1px dashed var(--gray-200); margin: 1.5rem 0;">

                    <div class="d-flex gap-2" style="flex-direction: column;">
                        <button type="submit" class="btn btn--primary btn--full" style="padding: 0.8rem;">
                            <i class="bi bi-check-circle"></i> Update Article
                        </button>
                        <a href="{{ route('admin.articles.index') }}" class="btn btn--secondary btn--full">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</form>
@endsection

@push('scripts')
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script>
    var quill = new Quill('#quillEditor', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'color': [] }, { 'background': [] }],
                ['link'],
                ['clean']
            ]
        }
    });

    @if(old('content'))
        quill.root.innerHTML = {!! json_encode(old('content')) !!};
    @else
        quill.root.innerHTML = {!! json_encode($article->content) !!};
    @endif

    document.getElementById('articleForm').addEventListener('submit', function(e) {
        var content = quill.root.innerHTML;
        var text = quill.getText().trim();
        
        document.getElementById('contentInput').value = content;
        
        if (text.length === 0) {
            e.preventDefault();
            // Validasi modern, menggantikan fungsi alert() bawaan
            var errorSpan = document.createElement('span');
            errorSpan.className = 'form-text-error';
            errorSpan.innerText = 'Article content cannot be empty!';
            if (!document.getElementById('contentError')) {
                errorSpan.id = 'contentError';
                document.getElementById('quillEditor').parentNode.appendChild(errorSpan);
            }
            document.querySelector('.ql-editor').focus();
            return false;
        }
    });

    function previewImage(event) {
        var reader = new FileReader();
        reader.onload = function(){
            var output = document.getElementById('preview');
            var previewBox = document.getElementById('imagePreview');
            output.src = reader.result;
            previewBox.classList.remove('d-none');
        };
        
        if(event.target.files[0]) {
            reader.readAsDataURL(event.target.files[0]);
        }
    }
</script>
@endpush