<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexContactRequest;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use App\Http\Requests\ExportContactRequest;

class AdminController extends Controller
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

        $contacts = $query->orderBy('created_at', 'desc')->paginate(7);
        $categories = Category::all();
        $tags = Tag::all();

        return view('admin.index', compact(['contacts', 'categories', 'tags']));
    }

    public function show(Contact $contact)
    {
        Contact::with(['category', 'tags'])->find($contact);

        return view('admin.show', compact('contact'));
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();

        return redirect('/admin');
    }

    public function downloadCsv(ExportContactRequest $request)
    {
        $query = Contact::query()->latest();

        $query->with('category');

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

        $fileName = 'contacts_' . now()->format('YmdHis') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $stream = fopen('php://output', 'w');

            fwrite($stream, "\xEF\xBB\xBF");

            fputcsv($stream, ['ID', '氏名', '性別', 'メール', '電話', '住所', '建物', 'カテゴリ', '内容', '作成日時']);

            foreach ($query->lazy() as $contact) {
                $fullName = "{$contact->first_name}{$contact->last_name}";

                $genderText = match ((string) $contact->gender) {
                    '0' => '不明',
                    '1' => '男性',
                    '2' => '女性',
                    '3' => 'その他',
                    default => '不明'
                };

                $categoryName = $contact->category ? $contact->category->content : '';

                fputcsv($stream, [
                    $contact->id,
                    $fullName,
                    $genderText,
                    $contact->email,
                    $contact->tel,
                    $contact->address,
                    $contact->building,
                    $categoryName,
                    $contact->detail,
                    $contact->tag_ids,
                    $contact->created_at->format('Y-m-d'),

                ]);
            }
            fclose($stream);
        }, $fileName, [
            'Content-Type' => 'text/csv'
        ]);
    }
}
