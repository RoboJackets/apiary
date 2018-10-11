<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\NotificationTemplate;
use App\Traits\AuthorizeInclude;
use App\Http\Resources\NotificationTemplate as NotificationTemplateResource;

class NotificationTemplateController extends Controller
{
    use AuthorizeInclude;

    public function __construct()
    {
        $this->middleware(['permission:send-notifications']);
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $include = $request->input('include');
        $nt = NotificationTemplate::with($this->authorizeInclude(NotificationTemplate::class, $include))->get();
        return response()->json(['status' => 'success', 'templates' => NotificationTemplateResource::collection($nt)]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'subject' => 'required|string',
            'body_markdown' => 'required',
        ]);

        $nt = new NotificationTemplate();
        $nt->name = $request->input('name');
        $nt->subject = $request->input('subject');
        $nt->body_markdown = $request->input('body_markdown');
        $nt->created_by = $request->user()->id;
        $nt->save();

        return response()->json(['status' => 'success', 'template' => new NotificationTemplateResource($nt)]);
    }

    /**
     * Display the specified resource.
     *
     * @param int  $id
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request)
    {
        $include = $request->input('include');
        $nt = NotificationTemplate::with($this->authorizeInclude(NotificationTemplate::class, $include))->find($id);
        if ($nt) {
            return response()->json(['status' => 'success', 'template' => new NotificationTemplateResource($nt)]);
        } else {
            return response()->json(['status' => 'error', 'error' => 'model_not_found'], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $nt = NotificationTemplate::find($id);
        if (! $nt) {
            return response()->json(['status' => 'error', 'error' => 'model_not_found'], 404);
        }

        $this->validate($request, [
            'name' => 'string',
            'subject' => 'string',
        ]);

        $nt->update($request->all());

        return response()->json(['status' => 'success', 'template' => new NotificationTemplateResource($nt)]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $nt = NotificationTemplate::find($id);
        if ($nt) {
            $nt->delete();

            return response()->json(['status' => 'success', 'message' => 'model_deleted']);
        } else {
            return response()->json(['status' => 'error', 'error' => 'model_not_found'], 404);
        }
    }
}
