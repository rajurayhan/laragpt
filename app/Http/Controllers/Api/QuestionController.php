<?php

namespace App\Http\Controllers\Api;

use App\Models\Question;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

/**
 * @group Question Management
 * @authenticated
 *
 * APIs for managing question
 */
class QuestionController extends Controller
{

    /**
     * Display a listing of Question.
     *
     * @group Question Management
     * @queryParam page integer page number.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $question = Question::with(['serviceInfo'])->paginate(10);

        return response()->json([
            'data' => $question->items(),
            'total' => $question->total(),
            'current_page' => $question->currentPage(),
        ]);
    }

    /**
     * Store a new Question
     *
     * @group Question Management
     *
     * @bodyParam title string required.
     * @bodyParam serviceId int required.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string',
            'serviceId' => 'required|int',
        ]);

        $question = new Question;
        $question->title = $validatedData['title'];
        $question->serviceId = $validatedData['serviceId'];
        $question->save();
        $question->load('serviceInfo');


        $response = [
            'message' => 'Created Successfully ',
            'data' => $question,
        ];
        return response()->json($response, 201);
    }

    /**
     * Display the specified question.
     *
     * @group Question Management
     *
     * @urlParam question required The ID of the question to display. Example: 1
     *
     * @param  Question $question
     * @return \Illuminate\Http\JsonResponse
     */

    public function show($id)
    {
        $question = Question::with(['serviceInfo'])->findOrFail($id);
        $response = [
            'message' => 'View Successfully ',
            'data' => $question,
        ];
        return response()->json($response, 201);
    }

    /**
     * Update the specified question.
     *
     * @group Question Management
     *
     * @urlParam question required The ID of the question to update. Example: 1
     * @bodyParam title string required.
     * @bodyParam serviceId int required.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Question $question
     * @return \Illuminate\Http\JsonResponse
     */

    public function update($id, Request $request,)
    {
        $validatedData = $request->validate([
            'title' => 'string|max:255',
            'serviceId' => 'required|int',
        ]);
        $question = Question::findOrFail($id);

        $question->title = $request->title;
        $question->serviceId = $validatedData['serviceId'];
        $question->save();
        $question->load('serviceInfo');

        $response = [
            'message' => 'Update Successfully ',
            'data' => $question,
        ];
        return response()->json($response, 201);
    }

    /**
     * Remove the specified question from storage.
     *
     * @group Question Management
     *
     * @urlParam question required The ID of the question to delete. Example: 1
     * @param  Question $question
     * @return \Illuminate\Http\JsonResponse
     */

    public function destroy($id)
    {
        $question = Question::findOrFail($id);
        $question->delete();
        $response = [
            'message' => 'Deleted Successfully',
            'data' => []
        ];

        return response()->json($response, 204);
    }
}

