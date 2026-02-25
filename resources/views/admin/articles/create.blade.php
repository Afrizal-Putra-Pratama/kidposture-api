@extends('admin.layouts.admin')

@section('title', 'Create Article')

@push('styles')
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/article-form.css') }}">
@endpush

@section('content')
<div class="page-header">
    <h2 class="page-title"><i class="bi bi-plus-circle"></i> Create Article</h2>
    <a href="{{ route('admin.articles.index') }}" class="btn btn--secondary">
        <i class="bi bi-arrow-left"></i> Back to List
    </a>
</div>

<form action="{{ route('admin.articles.store') }}" method="POST" enctype="multipart/form-data" id="articleForm">
    @csrf
    
    <div class="content-grid">
        <div class="content-main">
            <div class="card">
                <div class="card__header">
                    <i class="bi bi-layout-text-window"></i> Article Content
                </div>
                
                <div class="form-group">
                    <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-input @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" placeholder="Enter an engaging title..." required autofocus>
                    @error('title') <span class="form-text-error">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label for="excerpt" class="form-label">Excerpt</label>
                    <textarea class="form-textarea @error('excerpt') is-invalid @enderror" id="excerpt" name="excerpt" rows="3" placeholder="Write a short, engaging description (optional)">{{ old('excerpt') }}</textarea>
                    <span class="form-text-muted">Max 500 characters. This will appear on article cards.</span>
                    @error('excerpt') <span class="form-text-error">{{ $message }}</span> @enderror
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="content" class="form-label">Content <span class="text-danger">*</span></label>
                    <div id="quillEditor"></div>
                    <input type="hidden" name="content" id="contentInput" value="{{ old('content') }}">
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
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
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
                    <input type="file" class="form-input @error('thumbnail') is-invalid @enderror" id="thumbnail" name="thumbnail" accept="image/*" onchange="previewImage(event)" style="padding: 0.5rem; background: var(--gray-50); cursor: pointer;">
                    <span class="form-text-muted">Format: JPEG, PNG, WebP. Max 2MB.</span>
                    @error('thumbnail') <span class="form-text-error">{{ $message }}</span> @enderror
                    
                    <div id="imagePreview" class="image-preview-box d-none">
                        <img id="preview" src="" alt="Preview" style="width: 100%; max-height: 250px; object-fit: cover;">
                    </div>
                </div>
            </div>

            <div class="sticky-actions">
                <div class="card">
                    <div class="card__header">
                        <i class="bi bi-rocket-takeoff"></i> Publish & Save
                    </div>
                    
                    <div class="form-group">
                        <label class="form-switch" for="is_published">
                            <input type="checkbox" id="is_published" name="is_published" value="1" {{ old('is_published') ? 'checked' : '' }}>
                            <span class="form-label" style="margin: 0; font-weight: 500; cursor: pointer;">Publish Immediately</span>
                        </label>
                    </div>

                    <div class="form-group">
                        <label for="read_time" class="form-label">Read Time (minutes)</label>
                        <input type="number" class="form-input @error('read_time') is-invalid @enderror" id="read_time" name="read_time" value="{{ old('read_time', 5) }}" min="1">
                        @error('read_time') <span class="form-text-error">{{ $message }}</span> @enderror
                    </div>

                    <hr style="border: none; border-top: 1px dashed var(--gray-200); margin: 1.5rem 0;">

                    <div class="d-flex gap-2" style="flex-direction: column;">
                        <button type="submit" class="btn btn--primary btn--full" style="padding: 0.8rem;">
                            <i class="bi bi-check-circle"></i> Save Article
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
        placeholder: 'Write your amazing article here...',
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
    @endif

    document.getElementById('articleForm').addEventListener('submit', function(e) {
        var content = quill.root.innerHTML;
        var text = quill.getText().trim();
        
        document.getElementById('contentInput').value = content;
        
        if (text.length === 0) {
            e.preventDefault();

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