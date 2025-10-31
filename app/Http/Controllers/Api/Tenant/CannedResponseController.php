<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Tenant\CannedResponse;
use Illuminate\Http\Request;

class CannedResponseController extends Controller
{
    /**
     * Get list of canned responses available to the user.
     */
    public function index(Request $request)
    {
        $query = CannedResponse::availableFor(auth()->id());

        // Apply filters
        if ($request->category) {
            $query->category($request->category);
        }

        if ($request->platform) {
            $query->platform($request->platform);
        }

        if ($request->search) {
            $query->search($request->search);
        }

        if ($request->type === 'shared') {
            $query->shared();
        } elseif ($request->type === 'personal') {
            $query->personal(auth()->id());
        }

        $responses = $query->orderBy('category')
            ->orderByDesc('usage_count')
            ->get();

        return ApiResponse::success(data: $responses);
    }

    /**
     * Create a new canned response.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'shortcut' => 'nullable|string|max:50|unique:canned_responses,shortcut',
            'content' => 'required|string',
            'category' => 'nullable|string|max:100',
            'is_shared' => 'nullable|boolean',
            'platforms' => 'nullable|array',
            'platforms.*' => 'string|in:whatsapp,facebook,instagram,email,sms',
        ]);

        $response = CannedResponse::create([
            'title' => $validated['title'],
            'shortcut' => $validated['shortcut'] ?? null,
            'content' => $validated['content'],
            'category' => $validated['category'] ?? null,
            'user_id' => $validated['is_shared'] ?? false ? null : auth()->id(),
            'is_shared' => $validated['is_shared'] ?? false,
            'platforms' => $validated['platforms'] ?? null,
        ]);

        return ApiResponse::success(
            message: 'Canned response created successfully',
            data: $response
        );
    }

    /**
     * Update a canned response.
     */
    public function update(Request $request, $id)
    {
        $response = CannedResponse::findOrFail($id);

        // Check if user owns this response or it's shared
        if ($response->user_id && $response->user_id !== auth()->id()) {
            return ApiResponse::forbidden('You do not have permission to edit this response');
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'shortcut' => 'nullable|string|max:50|unique:canned_responses,shortcut,' . $id,
            'content' => 'sometimes|required|string',
            'category' => 'nullable|string|max:100',
            'is_shared' => 'nullable|boolean',
            'platforms' => 'nullable|array',
        ]);

        $response->update($validated);

        return ApiResponse::success(
            message: 'Canned response updated successfully',
            data: $response
        );
    }

    /**
     * Delete a canned response.
     */
    public function destroy($id)
    {
        $response = CannedResponse::findOrFail($id);

        // Check if user owns this response
        if ($response->user_id && $response->user_id !== auth()->id()) {
            return ApiResponse::forbidden('You do not have permission to delete this response');
        }

        $response->delete();

        return ApiResponse::success(message: 'Canned response deleted successfully');
    }

    /**
     * Use a canned response (get content with variables).
     */
    public function use(Request $request, $id)
    {
        $response = CannedResponse::findOrFail($id);

        $variables = $request->input('variables', []);
        $content = $response->getContentWithVariables($variables);

        // Record usage
        $response->recordUsage();

        return ApiResponse::success(data: [
            'id' => $response->id,
            'title' => $response->title,
            'content' => $content,
        ]);
    }

    /**
     * Get canned response by shortcut.
     */
    public function getByShortcut(Request $request)
    {
        $shortcut = $request->input('shortcut');

        if (!$shortcut) {
            return ApiResponse::badRequest('Shortcut is required');
        }

        $response = CannedResponse::availableFor(auth()->id())
            ->where('shortcut', $shortcut)
            ->first();

        if (!$response) {
            return ApiResponse::notFound('Canned response not found');
        }

        return ApiResponse::success(data: $response);
    }

    /**
     * Get most used canned responses.
     */
    public function mostUsed(Request $request)
    {
        $limit = $request->input('limit', 10);

        $responses = CannedResponse::availableFor(auth()->id())
            ->orderByDesc('usage_count')
            ->limit($limit)
            ->get();

        return ApiResponse::success(data: $responses);
    }

    /**
     * Get canned response categories.
     */
    public function categories()
    {
        $categories = CannedResponse::availableFor(auth()->id())
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category');

        return ApiResponse::success(data: $categories);
    }
}

