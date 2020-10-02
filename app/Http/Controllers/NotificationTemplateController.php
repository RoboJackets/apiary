<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreNotificationTemplateRequest;
use App\Http\Requests\UpdateNotificationTemplateRequest;
use App\Http\Resources\NotificationTemplate as NotificationTemplateResource;
use App\NotificationTemplate;
use App\Traits\AuthorizeInclude;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationTemplateController extends Controller
{
    use AuthorizeInclude;

    public function __construct()
    {
        $this->middleware(['permission:manage-notification-templates']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $include = $request->input('include');
        $nt = NotificationTemplate::with($this->authorizeInclude(NotificationTemplate::class, $include))->get();

        return response()->json(['status' => 'success', 'templates' => NotificationTemplateResource::collection($nt)]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreNotificationTemplateRequest $request): JsonResponse
    {
        $nt = new NotificationTemplate();
        $nt->name = $request->input('name');
        $nt->from = $request->input('from');
        $nt->subject = $request->input('subject');
        $nt->body_markdown = $request->input('body_markdown');
        $nt->created_by = $request->user()->id;
        $nt->save();

        return response()->json(['status' => 'success', 'template' => new NotificationTemplateResource($nt)]);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id, Request $request): JsonResponse
    {
        $include = $request->input('include');
        $nt = NotificationTemplate::with($this->authorizeInclude(NotificationTemplate::class, $include))->find($id);
        if (null !== $nt) {
            return response()->json(['status' => 'success', 'template' => new NotificationTemplateResource($nt)]);
        }

        return response()->json(['status' => 'error', 'error' => 'model_not_found'], 404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateNotificationTemplateRequest $request, int $id): JsonResponse
    {
        $nt = NotificationTemplate::find($id);
        if (null === $nt) {
            return response()->json(['status' => 'error', 'error' => 'model_not_found'], 404);
        }

        $nt->update($request->all());

        return response()->json(['status' => 'success', 'template' => new NotificationTemplateResource($nt)]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $nt = NotificationTemplate::find($id);
        if (true === $nt->delete()) {
            return response()->json(['status' => 'success', 'message' => 'model_deleted']);
        }

        return response()->json(['status' => 'error', 'error' => 'model_not_found'], 404);
    }
}
