<?php

namespace App\Http\Controllers;

use App\Models\AdminChange;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'Admin') {
            return response()->json(['error' => 'Only admins can create categories'], 403);
        }

        $request->validate([
            'name' => 'required|string|unique:category,name|min:3|max:255',
        ]);

        $categoryName = ucwords(strtolower(trim($request->input('name'))));

        try {
            $category = new Category([
                'name' => $categoryName,
                'attribute_list' => [],
            ]);

            $category->save();

            AdminChange::create([
                'description' => 'Created new category ' . $categoryName,
                'admin' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Category '{$categoryName}' created successfully.",
                'id' => $category->id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while creating the category. Please try again.',
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, int $categoryId)
    {
        $user = $request->user();

        if ($categoryId === 1) {
            return back()->with('error', "You are not allowed to edit the category Other.");
        }

        if ($user->role !== 'Admin') {
            return back()->with('error', 'Only admins can access this area');
        }

        $categories = Category::all();
        $categoriesId = $categories->pluck('id')->toArray();

        if (!in_array($categoryId, $categoriesId)) {
            return redirect('/');
            // TODO: Mandar erro
        }

        $category = $categories->firstWhere('id', $categoryId);

        return view("adminManagement.editCategory", compact('user', 'category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $categoryId)
    {
        $user = $request->user();

        if ($categoryId === 1) {
            return back()->with('error', "You are not allowed to edit the category Other.");
        }

        if ($user->role !== 'Admin') {
            return back()->with('error', 'Only admins can access this area');
        }

        $categories = Category::all();
        $categoriesId = $categories->pluck('id')->toArray();

        if (!in_array($categoryId, $categoriesId)) {
            return redirect('/')->with('error', 'Category not found');
        }

        $category = $categories->firstWhere('id', $categoryId);

        $attributes = $request->input('attributes');

        $convertedAttributes = $this->convertAttributes($attributes);

        $category->attribute_list = $convertedAttributes;
        $category->save();

        AdminChange::create([
            'description' => 'Edited category ' . $category->name,
            'admin' => $user->id,
        ]);

        // return view("adminManagement.categories", compact('user', 'categories'));
        return redirect()->route('management.show', ['section' => 'categories'])->with('success', 'Category updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $categoryId)
    {
        $user = request()->user();

        if ($user->role !== 'Admin') {
            return back()->with('error', 'Only admins can perform this action');
        }

        $category = Category::find($categoryId);

        if (!$category) {
            return back()->with('error', 'Category not found');
        }

        if ($categoryId === 1) {
            return back()->with('error', "You are not allowed to delete the category 'Other'.");
        }

        // Auction::where('category_id', $categoryId)->update(['category_id' => 1]);

        $category->delete();

        AdminChange::create([
            'description' => 'Deleted category ' . $category->name,
            'admin' => $user->id,
        ]);


        return response()->json(['success' => true, 'message' => 'Category deleted successfully']);
    }

    private function convertAttributes(?array $attributes = [])
    {
        $converted = [];

        if ($attributes) {
            foreach ($attributes as $key => $attribute) {

                $formattedName = strtolower(str_replace(' ', '_', trim($attribute["name"])));

                $convertedAttribute = [
                    'name' => $formattedName,
                    'type' => $attribute["type"],
                ];

                if (isset($attribute["options"])) {
                    $convertedAttribute['options'] = array_values($attribute["options"]);
                } else if ($attribute["type"] == 'enum') {
                    $convertedAttribute['type'] = 'string';
                }

                $converted[] = $convertedAttribute;
            }
        }

        return $converted;
    }
}
