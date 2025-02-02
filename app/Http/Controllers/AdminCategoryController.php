<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class AdminCategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('children')->whereNull('parent_id')->get();
        return view('admin/categories.index', compact('categories'));
    }

    // public function create()
    // {
    //     $categories = Category::whereNull('parent_id')->get(); // Get top-level categories
    //     return view('categories.create', compact('categories'));
    // }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        Category::create($request->all());
        return redirect()->route('admin.categories.index')->with('success', 'Category created successfully.');
    }

    public function edit(Category $category)
    {
        $categories = Category::whereNull('parent_id')->where('id', '!=', $category->id)->get(); // Exclude the category itself
        return view('admin..categories.category_edit', compact('category', 'categories'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        $category->update($request->all());
        return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully.');
    }

    // public function destroy(Category $category)
    // {
    //     $subCategory = Category::where('parent_id', $category->id)->count('id');
    //     if ($subCategory > 0) {
    //         return redirect()->route('categories.index')->with('success', 'You can not deleted this category.Please First Delete all Child Category!');
    //     }
    //     $product = Product::where('category_id', $category->id)->count('id');
    //     if ($product > 0) {
    //         return redirect()->route('categories.index')->with('success', 'You can not deleted this category.Please First Delete all Product under this Subcategory!');
    //     }
    //     $category->delete();
    //     return redirect()->route('categories.index')->with('success', 'Category deleted successfully.');
    // }
}
