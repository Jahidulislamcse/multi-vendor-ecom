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
                        <button class="btn btn-primary btn-round ms-auto" data-bs-toggle="modal"
                            data-bs-target="#addRowModal">
                            <i class="fa fa-plus"></i>
                            Add New
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Modal -->
                    <div class="modal fade" id="addRowModal" tabindex="-1" aria-labelledby="exampleModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="exampleModalLabel">Add New</h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <!-- Parent Category -->
                                                <div class="form-group">
                                                    <label for="parent_id">Parent Category</label>
                                                    <select name="parent_id" id="parent_id" class="form-control">
                                                        <option value="">None</option>
                                                        @foreach ($categories as $parentCategory)
                                                        <option value="{{ $parentCategory->id }}" {{ old('parent_id') == $parentCategory->id ? 'selected' : '' }}>
                                                            {{ $parentCategory->name }}
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <!-- Category Name (Dynamic Label) -->
                                                <div class="form-group">
                                                    <label for="name" id="nameLabel">Category Name <span class="text-danger">*</span></label>
                                                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                                                        value="{{ old('name') }}" required>
                                                    @error('name')
                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <!-- Description -->
                                                <div class="form-group">
                                                    <label for="description">Description</label>
                                                    <textarea name="description" id="description" class="form-control">{{ old('description') }}</textarea>
                                                </div>

                                                <!-- Order (Dynamic Label & Placeholder) -->
                                                <div class="form-group">
                                                    <label for="order" id="orderLabel">Category Order</label>
                                                    <input type="number" name="order" class="form-control" id="orderInput" placeholder="Enter Category Order" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary">Save changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="" class="display table table-hover">
                            <thead>
                                <th>ID</th>
                                <th>Name</th>
                                <!-- <th>Parent Category</th> -->
                                <th>Actions</th>
                            </thead>
                            <tbody>
                                @foreach ($categories as $key => $category)
                                <tr class="table-primary">
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $category->name }}</td>
                                    <!-- <td>{{ $category->parent ? $category->parent->name : 'None' }}</td> -->
                                    <td>
                                        <a href="{{ route('admin.categories.edit', $category->id) }}"
                                            data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Edit"
                                            class="btn btn-warning btn-sm"> <i class="fa fa-edit"></i></a>
                                        <form action="{{ route('admin.categories.destroy', $category->id) }}"
                                            method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm"
                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                data-bs-title="Delete"
                                                onclick="return confirm('Are you sure you want to delete this category?')"><i
                                                    class="fa fa-times"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                @if ($category->children->count())
                                <tr class="table-warning">
                                    <td colspan="4">
                                        <strong>Tags:</strong>
                                    </td>
                                </tr>
                                @foreach ($category->children as $child)
                                <tr class="table-warning">
                                    <td></td>
                                    <td class="text-muted">— {{ $child->name }}</td>
                                    <!-- <td>{{ $child->parent ? $child->parent->name : 'None' }}</td> -->
                                    <td>
                                        <a href="{{ route('admin.categories.edit', $child->id) }}"
                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                            data-bs-title="Edit" class="btn btn-warning btn-sm"> <i
                                                class="fa fa-edit"></i></a>
                                        <form action="{{ route('admin.categories.destroy', $child->id) }}"
                                            method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm"
                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                data-bs-title="Delete"
                                                onclick="return confirm('Are you sure you want to delete this subcategory?')"><i
                                                    class="fa fa-times"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                                @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('parent_id').addEventListener('change', function() {
        let nameLabel = document.getElementById('nameLabel');
        let orderLabel = document.getElementById('orderLabel');
        let orderInput = document.getElementById('orderInput');

        if (this.value) {
            // If a parent category is selected, change labels and placeholder
            nameLabel.innerHTML = "Tag Name <span class='text-danger'>*</span>";
            orderLabel.innerHTML = "Tag Order";
            orderInput.placeholder = "Enter Tag Order";
        } else {
            // If "None" is selected, reset labels and placeholder
            nameLabel.innerHTML = "Category Name <span class='text-danger'>*</span>";
            orderLabel.innerHTML = "Category Order";
            orderInput.placeholder = "Enter Category Order";
        }
    });
</script>
@endsection