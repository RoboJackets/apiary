<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\NotificationTemplate;

class NotificationTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $nt = NotificationTemplate::all();
        return response()->json(['status' => 'success', 'templates' => $nt]);
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

        return response()->json(['status' => 'success', 'template' => $nt]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $nt = NotificationTemplate::find($id);
        if ($nt) {
            return response()->json(['status' => 'success', 'template' => $nt]);
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
        if (!$nt) {
            return response()->json(['status' => 'error', 'error' => 'model_not_found'], 404);
        }

        $this->validate($request, [
            'name' => 'string',
            'subject' => 'string',
        ]);

        $nt->update($request->all());

        return response()->json(['status' => 'success', 'template' => $nt]);
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
