<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\Clap\CreateClap;
use App\Http\Resources\ClapResource;
use App\Models\Clap;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ClapController extends Controller
{
    public function store(CreateClap $request)
    {
        $user = $request->user();
        $clap = Clap::firstOrNew([
            'user_id' => $user->id,
            'article_id' => $request->get('article_id'),
        ]);
        if ($clap->count !== null) {
            $clap->update([
                'count' => $clap->count + 1,
            ]);
        } else {
            $clap->count = 1;
            $clap->save();
        }
        return new ClapResource($clap);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Clap $clap)
    {
        $clap->delete();
        return response()->json(null, 204);
    }
}
