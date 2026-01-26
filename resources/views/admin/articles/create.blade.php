@extends('admin.layouts.admin')


@section('title', 'Create Article')

@push('styles')
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-plus-circle"></i> Create Article</h2>
    <a href="{{ route('admin.articles.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to List
    </a>
</div>

<form action="{{ route('admin.articles.store') }}" method="POST" enctype="multipart/form-data" id="articleForm">
    @csrf
    
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
                               value="{{ old('title') }}" 
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
                                  placeholder="Short description (optional)">{{ old('excerpt') }}</textarea>
                        @error('excerpt')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Max 500 characters</small>
                    </div>

                    <!-- Content Editor -->
                    <div class="mb-3">
                        <label for="content" class="form-label">Content <span class="text-danger">*</span></label>
                        <div id="quillEditor" style="height: 400px;"></div>
                        <input type="hidden" name="content" id="contentInput" value="{{ old('content') }}">
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
                                   {{ old('is_published') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_published">
                                Publish immediately
                            </label>
                        </div>
                    </div>

                    <!-- Read Time -->
                    <div class="mb-3">
                        <label for="read_time" class="form-label">Read Time (minutes)</label>
                        <input type="number" 
                               class="form-control @error('read_time') is-invalid @enderror" 
                               id="read_time" 
                               name="read_time" 
                               value="{{ old('read_time', 5) }}" 
                               min="1">
                        @error('read_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr>

                    <!-- Submit Buttons -->
                    <button type="submit" class="btn btn-primary w-100 mb-2">
                        <i class="bi bi-check-circle"></i> Create Article
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
                                    {{ old('category_id') == $category->id ? 'selected' : '' }}>
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
                    <div class="mb-3">
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
                    
                    <!-- Image Preview -->
                    <div id="imagePreview" class="d-none">
                        <img id="preview" src="" alt="Preview" class="img-fluid rounded">
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
// Initialize Quill
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

// Load old content if exists
@if(old('content'))
    quill.root.innerHTML = {!! json_encode(old('content')) !!};
@endif

// Handle form submit
document.getElementById('articleForm').addEventListener('submit', function(e) {
    // Get content from Quill
    var content = quill.root.innerHTML;
    var text = quill.getText().trim();
    
    // Set to hidden input
    document.getElementById('contentInput').value = content;
    
    // Validate
    if (text.length === 0) {
        e.preventDefault();
        alert('Content tidak boleh kosong!');
        return false;
    }
    
    console.log('Submitting content:', content.substring(0, 100) + '...');
});

// Image preview
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
