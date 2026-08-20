<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\V1\IndexContactRequest;
use App\Http\Requests\API\V1\StoreContactRequest;
use App\Http\Requests\API\V1\UpdateContactRequest;
use App\Http\Resources\ContactResource;
use App\Models\Contact;


class ContactController extends Controller
{
    public function index(IndexContactRequest $request)
    {
        $query = Contact::with(['category', 'tags']);

        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $query->where(function ($q) use ($keyword) {
                $q->where('first_name', 'like', "%{$keyword}%")
                    ->orWhere('last_name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('gender') && in_array($request->input('gender'), ['1', '2', '3'])) {
            $query->where('gender', $request->input('gender'));
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->input('date'));
        }

        $perPage = $request->input('per_page', 20);

        $contacts = $query->latest()->paginate($perPage);

        return ContactResource::collection($contacts);
    }

    public function store(StoreContactRequest $request)
    {
        $contactData = $request->validated();

        $tagIds = $contactData['tag_ids'] ?? [];

        $validated = $contactData;
        unset($validated['tag_ids']);

        $contact = Contact::create($validated);
        $contact->tags()->attach($tagIds);

        $contact->load(['category', 'tags']);

        return (new ContactResource($contact))
            ->additional(['message' => 'お問い合わせを作成しました'])
            ->response()
            ->setStatusCode(201);

    }

    public function show(Contact $contact): ContactResource
    {
        $contact->load(['category', 'tags']);

        return new ContactResource($contact);
    }

    public function update(UpdateContactRequest $request, Contact $contact)
    {
        $contactData = $request->validated();

        $tagIds = $contactData['tag_ids'] ?? [];

        $validated = $contactData;
        unset($validated['tag_ids']);

        $contact->update($validated);
        $contact->tags()->sync($tagIds);

        $contact->load(['category', 'tags']);

        return (new ContactResource($contact))
            ->additional(['message' => 'お問い合わせを更新しました']);
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();

        return response()->json(null, 204);
    }
}
