<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\mUser;
use App\Models\mUserNotification;
use App\Models\test;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AccountManagement extends Controller
{

    public function user()
    {
        $user = Auth::user();
        return response()->json($user, 200);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('user-access');
            return response()->json(['message' => 'success', 'token' => $token->plainTextToken, 'type_account' => Auth::user()->role, 'user' => $user], 200);
        }
        return response()->json(['message' => 'failed', 'token' => null], 200);
    }

    public function register(Request $request)
    {
        $credentials = $request->validate([
            'nama' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'alamat' => 'required',
            'nohp' => 'required',
        ]);

        $user = mUser::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'nama' => $request->nama,
            'alamat' => $request->alamat,
            'nohp' => $request->nohp,
        ]);

        if (!is_null($user)) {
            return response()->json(['message' => 'success', 'data' => $user], 200);
        }
        return response()->json(['message' => 'failed', 'data' => ''], 400);
    }

    public function logout()
    {
        $user = Auth::user();
        $datauser = mUser::find(Auth::id());
        $datauser->fcm_token = null;
        $datauser->save();
        if ($user->tokens()->delete()) {
            return response()->json(['message' => 'success'], 200);
        }
        return response()->json(['message' => 'failed'], 400);
    }

    public function edit(Request $request)
    {
        $user = mUser::find(Auth::id());
        if (!empty($user)) {
            $user->nama = $request->nama;
            $user->alamat = $request->alamat;
            $user->nohp = $request->nohp;
            $user->save();
            return response()->json(['message' => 'success', 'data' => $user], 200);
        } else {
            return response()->json(['message' => 'failed', 'data' => ''], 400);
        }
    }

    public function updateImage(Request $request)
    {
        $name = $request->file('image')->hashName();

        $path = $request->file('image')->store('public/images/profile');

        $user = mUser::find(Auth::id());
        if (!empty($user)) {
            $user->foto = $name;
            $user->save();
            return response()->json(['message' => 'success', 'data' => $user], 200);
        } else {
            return response()->json(['message' => 'failed', 'data' => ''], 400);
        }
    }

    public function editPassword(Request $request)
    {
        $user = mUser::find(Auth::id());
        if (!empty($user)) {
            if (Hash::check($request->old_password, $user->password)) {
                $user->password = Hash::make($request->password);
                $user->save();
                return response()->json(['message' => 'success', 'data' => $user], 200);
            } else {
                return response()->json(['message' => 'not found', 'data' => ""], 404);
            }
        } else {
            return response()->json(['message' => 'failed', 'data' => ''], 400);
        }
    }

    public function currentNotification(Request $request)
    {
        $user = mUser::find(Auth::id());
        if (!empty($user)) {
            $data = mUserNotification::where('user_id', Auth::id())->where('status', 0)->get();
            return response()->json(['errors' => 'false', 'message' => "success", 'data' => $data], 200);
        } else {
            return response()->json(['message' => 'failed', 'data' => ''], 400);
        }
    }

    public function archivedNotification(Request $request)
    {
        $user = mUser::find(Auth::id());
        if (!empty($user)) {
            $data = mUserNotification::where('user_id', Auth::id())->where('status', 1)->get();
            return response()->json(['errors' => 'false', 'message' => "success", 'data' => $data], 200);
        } else {
            return response()->json(['errors' => 'true', 'message' => 'not found', 'data' => ''], 400);
        }
    }

    public function notificationUpdate(Request $request)
    {
        $user = mUser::find(Auth::id());
        if (!empty($user)) {
            $data = mUserNotification::where('id', $request->id)->where('user_id', Auth::id())->first();
            if ($request->action) {
                if (!empty($data)) {
                    $data->status = 1;
                    $data->save();
                    return response()->json(['errors' => 'false', 'message' => 'success', 'data' => $data], 200);
                } else {
                    return response()->json(['errors' => 'true', 'message' => 'not found', 'data' => ''], 400);
                }
            } else {
                if (!empty($data)) {
                    $data->status = 3;
                    $data->save();
                    return response()->json(['errors' => 'false', 'message' => 'success', 'data' => $data], 200);
                } else {
                    return response()->json(['errors' => 'true', 'message' => 'not found', 'data' => ''], 400);
                }
            }
        } else {
            return response()->json(['errors' => 'true', 'message' => 'not found', 'data' => ''], 400);
        }
    }

    public function sendNotificationTest()
    {
        $recipients = mUser::where('fcm_token','!=','')->pluck('fcm_token')->toArray();

        fcm()
            ->to($recipients)
            ->priority('high')
            ->timeToLive(0)
            ->notification([
                'title' => 'Test Notification from server',
                'body' => 'This is notification test from server',
            ])
            ->send();
    }

    public function sendNotification(Request $request){
        $recipients = mUser::whereIn('user_id',$request->input('user_id'))->pluck('fcm_token')->toArray();

        foreach ($request->input('user_id') as $item){
            mUserNotification::create([
                'user_id'=>$item,
                'title'=>$request->title,
                'body'=>$request->body,
                'notification_by'=>$request->notification_by,
                'status'=>0,
                'type'=>$request->notification_type,
                'click_action'=>$request->click_action,
            ]);
        }

        fcm()
            ->to($recipients)
            ->priority('high')
            ->timeToLive(0)
            ->notification([
                'title' => $request->title,
                'body' => $request->body,
            ])
            ->send();
    }

    public function receiveFCMToken(Request $request)
    {
        $user = mUser::find(Auth::id());
        if (!empty($user)) {
            $user->fcm_token = $request->fcm_token;
            $user->save();
            return response()->json(['errors' => 'false', 'message' => 'success', 'data' => $user], 200);
        } else {
            return response()->json(['errors' => 'true', 'message' => 'not found', 'data' => ''], 400);
        }
    }

    public function notificationHandler(Request $request){
        $data = new test();
        $data->test(json_encode($request->all()));
        $data->save();
        return response()->json('',200);
    }
}
