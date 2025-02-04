@extends('admin.admin_dashboard')
@section('main')
<div class="page-inner">
    <div class="page-header">
        <ul class="breadcrumbs mb-3">
            <li class="nav-home">
                <a href="{{ route('admin.dashboard') }}">
                    <i class="icon-home"></i>
                    Dashbard
                </a>
            </li>
            <li class="separator">
                <i class="icon-arrow-right"></i>
            </li>
            <li class="nav-item">
                <a href="javascript:void(0)">Categories</a>
            </li>
        </ul>
    </div>
    <div class="row">


        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title">Categories</h4>
                        <a class="btn btn-primary btn-round ms-auto" href="{{ route('admin.categories.index') }}">
                            <i class="fa fa-list"></i>
                            List View
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.categories.update', $category->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="name">Category Name</label>
                            <input type="text" name="name" id="name" class="form-control"
                                value="{{ old('name', $category->name) }}" required>
                        </div>

                        <div class="form-group">
                            <label for="parent_id">Parent Category</label>
                            <select name="parent_id" id="parent_id" class="form-control">
                                <option value="">None</option>
                                @foreach ($categories as $parentCategory)
                                <option value="{{ $parentCategory->id }}"
                                    {{ old('parent_id', $category->parent_id) == $parentCategory->id ? 'selected' : '' }}>
                                    {{ $parentCategory->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Profile Image Upload -->
                        <div class="form-group">
                            <label for="profile_img">Profile Image</label>
                            <input type="file" name="profile_img" id="profile_img" class="form-control">
                            @if ($category->profile_img)
                            <div class="mt-2">
                                <img src="{{ asset($category->profile_img) }}" alt="Profile Image" style="width: 150px;">
                            </div>
                            @endif
                        </div>

                        <!-- Cover Image Upload -->
                        <div class="form-group">
                            <label for="cover_img">Cover Image</label>
                            <input type="file" name="cover_img" id="cover_img" class="form-control">
                            @if ($category->cover_img)
                            <div class="mt-2">
                                <img src="{{ asset($category->cover_img) }}" alt="Cover Image" style="width: 150px;">
                            </div>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" class="form-control">{{ old('description', $category->description) }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-success">Update Category</button>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
