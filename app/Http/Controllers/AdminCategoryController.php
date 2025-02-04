<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;


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
        // Validate input fields
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories')->where(function ($query) use ($request) {
                    return $query->where('parent_id', $request->parent_id);
                }),
            ],
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'profile_img' => 'nullable|image|mimes:jpeg,JPG,jpg,png,gif,svg,webp,bmp|max:2048',
            'cover_img' => 'nullable|image|mimes:jpeg,JPG,jpg,png,gif,svg,webp,bmp|max:2048',
        ]);

        $data = $request->only(['name', 'description', 'parent_id']);

        if ($request->hasFile('profile_img')) {
            $image = $request->file('profile_img');

            $name_gen = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();

            $folder = 'category_images/';

            if (!file_exists(public_path('upload/' . $folder))) {
                mkdir(public_path('upload/' . $folder), 0777, true);
            }

            $manager = new \Intervention\Image\ImageManager();
            $img = $manager->make($image);
            $img->fit(1920, 1280);
            $img->encode('jpg', 80)->save(public_path('upload/' . $folder . $name_gen));

            $data['profile_img'] = 'upload/' . $folder . $name_gen;
        }

        if ($request->hasFile('cover_img')) {
            $image = $request->file('cover_img');

            $name_gen = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();

            $folder = 'category_images/';

            if (!file_exists(public_path('upload/' . $folder))) {
                mkdir(public_path('upload/' . $folder), 0777, true);
            }

            $manager = new \Intervention\Image\ImageManager();
            $img = $manager->make($image);
            $img->fit(1920, 1280);
            $img->encode('jpg', 80)->save(public_path('upload/' . $folder . $name_gen));

            $data['cover_img'] = 'upload/' . $folder . $name_gen;
        }

        // Save category to database
        Category::create($data);

        return redirect()->route('admin.categories.index')->with('success', 'Category created successfully.');
    }

    public function edit(Category $category)
    {
        $categories = Category::whereNull('parent_id')->where('id', '!=', $category->id)->get(); // Exclude the category itself
        return view('admin..categories.category_edit', compact('category', 'categories'));
    }

    public function update(Request $request, Category $category)
    {
        // Validate the incoming data
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories')->where(function ($query) use ($request, $category) {
                    return $query->where('parent_id', $request->parent_id)->where('id', '!=', $category->id);
                }),
            ],
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'profile_img' => 'nullable|image|mimes:jpeg,jpg,png,gif,svg,webp|max:2048',
            'cover_img' => 'nullable|image|mimes:jpeg,jpg,png,gif,svg,webp|max:2048',
        ]);

        // Update other fields
        $category->name = $request->name;
        $category->description = $request->description;
        $category->parent_id = $request->parent_id;

        // Handle profile image upload
        if ($request->hasFile('profile_img')) {
            // Delete old image if exists
            if ($category->profile_img && file_exists(public_path($category->profile_img))) {
                unlink(public_path($category->profile_img));
            }

            // Process and save new image
            $image = $request->file('profile_img');
            $profileImageName = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
            $folder = 'category_images/';

            if (!file_exists(public_path('upload/' . $folder))) {
                mkdir(public_path('upload/' . $folder), 0777, true);
            }

            $manager = new \Intervention\Image\ImageManager();
            $img = $manager->make($image);
            $img->fit(1920, 1280);
            $img->encode('jpg', 80)->save(public_path('upload/' . $folder . $profileImageName));

            $category->profile_img = 'upload/' . $folder . $profileImageName;
        }

        // Handle cover image upload
        if ($request->hasFile('cover_img')) {
            // Delete old image if exists
            if ($category->cover_img && file_exists(public_path($category->cover_img))) {
                unlink(public_path($category->cover_img));
            }

            // Process and save new image
            $image = $request->file('cover_img');
            $coverImageName = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
            $folder = 'category_images/';

            if (!file_exists(public_path('upload/' . $folder))) {
                mkdir(public_path('upload/' . $folder), 0777, true);
            }

            $manager = new \Intervention\Image\ImageManager();
            $img = $manager->make($image);
            $img->fit(1920, 1280);
            $img->encode('jpg', 80)->save(public_path('upload/' . $folder . $coverImageName));

            $category->cover_img = 'upload/' . $folder . $coverImageName;
        }

        // Save the updated category
        $category->save();

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
