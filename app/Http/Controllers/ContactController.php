<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Models\Contact;
use App\Models\Category;

class ContactController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('created_at', 'desc')->get();
        return view('contact.index', compact('categories'));
    }

    public function store(StoreContactRequest $request)
    {
        $validated = Contact::create($request->validated());
        $category = Category::find($validated->category_id);

        return view('contact.confirm', compact(['validated', 'category']));
    }

    public function thanks()
    {
        return view('contact.thanks');
    }
}
