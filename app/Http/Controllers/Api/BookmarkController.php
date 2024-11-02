<?php

namespace App\Http\Controllers\Api;

use App\Models\Bookmark;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

/**
 * @group Bookmark Management
 * @authenticated
 *
 * APIs for managing bookmarks
 */
class BookmarkController extends Controller
{
    /**
     * Display a listing of Bookmarks.
     *
     * @group Bookmark Management
     * @queryParam page integer page number.
     * @queryParam conversationId integer Filter bookmarks by conversation ID.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $bookmarkQuery = Bookmark::query();

        if ($request->get('conversationId')) {
            $bookmarkQuery->where('conversationId', $request->get('conversationId'));
        }

        if ($request->has('page')) {
            $bookmarksPagination = $bookmarkQuery->paginate(10);
            return response()->json([
                'data' => $bookmarksPagination->items(),
                'total' => $bookmarksPagination->total(),
                'current_page' => $bookmarksPagination->currentPage(),
            ]);
        } else {
            $bookmarks = $bookmarkQuery->get();
            return response()->json([
                'data' => $bookmarks,
                'total' => $bookmarks->count(),
            ]);
        }
    }

    /**
     * Store a new Bookmark
     *
     * @group Bookmark Management
     *
     * @bodyParam title string required.
     * @bodyParam conversationId int required.
     * @bodyParam conversationDetailId int required.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string',
            'conversationId' => 'required|integer',
            'conversationDetailId' => 'required|integer',
        ]);

        $bookmark = new Bookmark;
        $bookmark->fill($validatedData);
        $bookmark->save();

        $response = [
            'message' => 'Created Successfully',
            'data' => $bookmark,
        ];
        return response()->json($response, 201);
    }

    /**
     * Display the specified bookmark.
     *
     * @group Bookmark Management
     *
     * @urlParam bookmark required The ID of the bookmark to display. Example: 1
     *
     * @param  int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $bookmark = Bookmark::findOrFail($id);
        $response = [
            'message' => 'View Successfully',
            'data' => $bookmark,
        ];
        return response()->json($response, 200);
    }

    /**
     * Update the specified bookmark.
     *
     * @group Bookmark Management
     *
     * @urlParam bookmark required The ID of the bookmark to update. Example: 1
     * @bodyParam title string.
     * @bodyParam conversationId int.
     * @bodyParam conversationDetailId int.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id, Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $bookmark = Bookmark::findOrFail($id);
        $bookmark->update($validatedData);

        $response = [
            'message' => 'Update Successfully',
            'data' => $bookmark,
        ];
        return response()->json($response, 200);
    }

    /**
     * Remove the specified bookmark from storage.
     *
     * @group Bookmark Management
     *
     * @urlParam bookmark required The ID of the bookmark to delete. Example: 1
     * @param  int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $bookmark = Bookmark::findOrFail($id);
        $bookmark->delete();
        $response = [
            'message' => 'Deleted Successfully',
            'data' => []
        ];

        return response()->json($response, 204);
    }
}
