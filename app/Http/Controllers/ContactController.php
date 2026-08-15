<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Models\Contact;
use App\Models\Category;
use App\Models\Tag;

class ContactController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        $tags = Tag::all();
        return view('contact.index', compact(['categories', 'tags']));
    }
    public function store(StoreContactRequest $request)
    {
        $contactData = $request->validated();

        $tagIds = $contactData['tag_ids'] ?? [];

        $contact = $contactData;
        unset($contact['tag_ids']);

        $validated = Contact::create($contact);
        $validated->tags()->attach($tagIds);

        $category = Category::find($validated->category_id);
        $tags = Tag::whereIn('id', $tagIds)->get();

        return view('contact.confirm', compact(['validated', 'category', 'tags']));
    }

    public function thanks()
    {
        return view('contact.thanks');
    }
}
