<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        $tags = Tag::all();

        return view('contact.index', compact(['categories', 'tags']));
    }

    public function confirm(StoreContactRequest $request)
    {
        $validated = $request->validated();

        $request->session()->put('contact_input', $validated);

        $category = Category::find($validated['category_id']);
        $tagIds = $validated['tag_ids'] ?? [];
        $tags = Tag::whereIn('id', $tagIds)->get();

        return view('contact.confirm', compact(['validated', 'category', 'tags']));
    }

    public function store(Request $request)
    {
        $contactData = $request->session()->get('contact_input');

        if (! $contactData) {
            return redirect('/');
        }

        $tagIds = $contactData['tag_ids'] ?? [];

        $contact = $contactData;
        unset($contact['tag_ids']);

        $newContact = Contact::create($contact);
        $newContact->tags()->attach($tagIds);

        $request->session()->forget('contact_input');

        return redirect('/thanks');
    }

    public function thanks()
    {
        return view('contact.thanks');
    }
}
