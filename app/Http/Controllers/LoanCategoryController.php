<?php

namespace App\Http\Controllers;

use App\Models\LoanCategory;
use Illuminate\Http\Request;

class LoanCategoryController extends Controller
{
    public function index()
    {
        $categories = LoanCategory::paginate(10);

        return view('pages.category.index', ['categories' => $categories]);

    }

    public function create()
    {
        return view('pages.category.create');
    }

    public function edit($id)
    {
        $category = LoanCategory::find($id);

        if ($category == null) return redirect()->back()->with('error_message', 'Category Not Found.');

        return view('pages.category.edit', ['category' => $category]);
    }

    public function update($id, Request $request)
    {
        $request->validate(['name' => 'required', 'company_id' => 'required']);

        LoanCategory::where('id', $id)->update($request->except(['_token', '_method']));

        return redirect(route('settings.loan-categories'))->with('success_message', 'Category Update');

    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required', 'company_id' => 'required']);

        LoanCategory::create($request->toArray());

        return redirect()->back()->with('success_message', 'Category Added');
    }
}
