<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ContentPageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContentPageController extends Controller
{
    public function __construct(
        private readonly ContentPageService $contentPages
    ) {}

    /**
     * Display the list of editable content pages.
     */
    public function index(): View
    {
        return view('admin.pages.index', [
            'pages' => $this->contentPages->pages(),
        ]);
    }

    /**
     * Display the page-editing form.
     */
    public function edit(string $page): View
    {
        abort_unless(
            $this->contentPages->exists($page),
            404
        );

        return view('admin.pages.edit', [
            'page' => $page,
            'pageLabel' => $this->contentPages->label($page),
            'content' => $this->contentPages->content($page),
        ]);
    }

    /**
     * Validate and save page content.
     */
    public function update(
        Request $request,
        string $page
    ): RedirectResponse {
        abort_unless(
            $this->contentPages->exists($page),
            404
        );

        $validated = $request->validate([
            "page_{$page}_title" => [
                'required',
                'string',
                'max:120',
            ],

            "page_{$page}_subtitle" => [
                'nullable',
                'string',
                'max:255',
            ],

            "page_{$page}_body" => [
                'required',
                'string',
            ],

            "page_{$page}_email" => [
                'nullable',
                'email',
                'max:255',
            ],

            "page_{$page}_phone" => [
                'nullable',
                'string',
                'max:60',
            ],

            "page_{$page}_address" => [
                'nullable',
                'string',
                'max:255',
            ],

            "page_{$page}_cta_label" => [
                'nullable',
                'string',
                'max:80',
            ],

            "page_{$page}_cta_url" => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        $this->contentPages->save($validated);

        return redirect()
            ->route('admin.pages.edit', $page)
            ->with(
                'status',
                "{$this->contentPages->label($page)} page updated successfully."
            );
    }
}
