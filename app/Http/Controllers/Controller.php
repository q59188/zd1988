<?php

namespace App\Http\Controllers;

use App\Models\User;
use Auth;
use Hash;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct(Request $request)
    {
        $data = $request->get('data');
        // dump([$username, $uid, $avatar]);exit;
        // 根据uid判断是否有该用户 如果有就登陆 没有 添加一个用户并登陆
        if ($data)
        {
            // data 是base64 object 数据 解析出来
            $data      = base64_decode($data);
            $data      = json_decode($data);
            $tiktok_id = $data->tiktok_id ?? '';
            $customID  = $data->customID ?? '';
            $avatar    = $data->avatar ?? '';

            if ($tiktok_id && $customID && $avatar)
            {
                $user = User::where('email', $tiktok_id)->first();
                if ($user)
                {
                    $user->name   = $customID;
                    $user->avatar = $avatar;
                    $user->save();
                    Auth::login($user);
                    // 访问哪里就跳转到哪里
                    return redirect()->route($request->route()->getName());
                }
                else
                {
                    $user = User::create([
                        'name'              => $customID,
                        'email'             => $tiktok_id,
                        'avatar'            => $avatar,
                        'password'          => Hash::make('123456'),
                        'user_type'         => 'customer',
                        "email_verified_at" => time(),
                    ]);
                    Auth::login($user);
                    // 访问哪里就跳转到哪里
                    return redirect()->route($request->route()->getName());
                }
            }
        }
        exit;
    }
}
