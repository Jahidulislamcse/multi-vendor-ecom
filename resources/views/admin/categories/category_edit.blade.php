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
                    <form action="{{ route('admin.categories.update', $category->id) }}" method="POST">
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