@extends('layouts.admin')

@section('title', 'Edit Article')

@push('styles')
<!-- Quill Editor CSS -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-pencil"></i> Edit Article</h2>
    <a href="{{ route('admin.articles.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to List
    </a>
</div>

<form action="{{ route('admin.articles.update', $article) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Article Content</h5>
                </div>
                <div class="card-body">
                    <!-- Title -->
                    <div class="mb-3">
                        <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control @error('title') is-invalid @enderror" 
                               id="title" 
                               name="title" 
                               value="{{ old('title', $article->title) }}" 
                               required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Excerpt -->
                    <div class="mb-3">
                        <label for="excerpt" class="form-label">Excerpt</label>
                        <textarea class="form-control @error('excerpt') is-invalid @enderror" 
                                  id="excerpt" 
                                  name="excerpt" 
                                  rows="3" 
                                  placeholder="Short description (optional)">{{ old('excerpt', $article->excerpt) }}</textarea>
                        @error('excerpt')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Max 500 characters</small>
                    </div>

                    <!-- Content Editor -->
                    <div class="mb-3">
                        <label for="content" class="form-label">Content <span class="text-danger">*</span></label>
                        <div id="editor" style="height: 400px;"></div>
                        <textarea name="content" id="content" class="d-none">{{ old('content', $article->content) }}</textarea>

                        @error('content')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Publish</h5>
                </div>
                <div class="card-body">
                    <!-- Publish Status -->
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="is_published" 
                                   name="is_published" 
                                   value="1"
                                   {{ old('is_published', $article->is_published) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_published">
                                Published
                            </label>
                        </div>
                        @if($article->published_at)
                            <small class="text-muted">
                                Published on {{ $article->published_at->format('d M Y, H:i') }}
                            </small>
                        @endif
                    </div>

                    <!-- Read Time -->
                    <div class="mb-3">
                        <label for="read_time" class="form-label">Read Time (minutes)</label>
                        <input type="number" 
                               class="form-control @error('read_time') is-invalid @enderror" 
                               id="read_time" 
                               name="read_time" 
                               value="{{ old('read_time', $article->read_time) }}" 
                               min="1">
                        @error('read_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Stats -->
                    <div class="mb-3">
                        <small class="text-muted">
                            <i class="bi bi-eye"></i> {{ $article->views }} views<br>
                            <i class="bi bi-clock"></i> Created {{ $article->created_at->diffForHumans() }}<br>
                            <i class="bi bi-pencil"></i> Updated {{ $article->updated_at->diffForHumans() }}
                        </small>
                    </div>

                    <hr>

                    <!-- Submit Buttons -->
                    <button type="submit" class="btn btn-primary w-100 mb-2">
                        <i class="bi bi-check-circle"></i> Update Article
                    </button>
                    <a href="{{ route('admin.articles.index') }}" class="btn btn-secondary w-100">
                        <i class="bi bi-x-circle"></i> Cancel
                    </a>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Category</h5>
                </div>
                <div class="card-body">
                    <select class="form-select @error('category_id') is-invalid @enderror" 
                            name="category_id" 
                            required>
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" 
                                    {{ old('category_id', $article->category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->icon }} {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Thumbnail</h5>
                </div>
                <div class="card-body">
                    <!-- Current Thumbnail -->
                    @if($article->thumbnail)
                        <div class="mb-3">
                            <label class="form-label">Current Thumbnail</label>
                            <div>
                                <img src="{{ Storage::url($article->thumbnail) }}" 
                                     alt="Current Thumbnail" 
                                     class="img-fluid rounded"
                                     id="currentThumbnail">
                            </div>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label for="thumbnail" class="form-label">
                            {{ $article->thumbnail ? 'Change Thumbnail' : 'Upload Thumbnail' }}
                        </label>
                        <input type="file" 
                               class="form-control @error('thumbnail') is-invalid @enderror" 
                               id="thumbnail" 
                               name="thumbnail" 
                               accept="image/*"
                               onchange="previewImage(event)">
                        @error('thumbnail')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Max 2MB (JPEG, PNG, WebP)</small>
                    </div>
                    
                    <!-- New Image Preview -->
                    <div id="imagePreview" class="d-none">
                        <label class="form-label">New Thumbnail Preview</label>
                        <img id="preview" src="" alt="Preview" class="img-fluid rounded">
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<!-- Quill Editor JS -->
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script>
    // Initialize Quill Editor
    var quill = new Quill('#editor', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'color': [] }, { 'background': [] }],
                ['link', 'image'],
                ['clean']
            ]
        }
    });

    // Sync Quill content to hidden textarea
    var form = document.querySelector('form');
    form.onsubmit = function(e) {
        var content = document.querySelector('#content');
        content.value = quill.root.innerHTML;
        
        // Validate content not empty
        var text = quill.getText().trim();
        if (text.length === 0) {
            e.preventDefault();
            alert('Content tidak boleh kosong!');
            // Scroll to editor
            document.getElementById('editor').scrollIntoView({ behavior: 'smooth' });
            return false;
        }
    };

    // Load old content if validation fails
    @if(old('content'))
        quill.root.innerHTML = {!! json_encode(old('content')) !!};
    @endif

    // Image Preview
    function previewImage(event) {
        var reader = new FileReader();
        reader.onload = function(){
            var output = document.getElementById('preview');
            var preview = document.getElementById('imagePreview');
            output.src = reader.result;
            preview.classList.remove('d-none');
        };
        reader.readAsDataURL(event.target.files[0]);
    }
</script>
@endpush
