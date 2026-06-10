<?php

namespace Webkul\Admin\Http\Controllers\User;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Core\Models\AuditLog;

class AccountController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function edit()
    {
        $user = auth()->guard('user')->user();

        return view('admin::user.account.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update()
    {
        $user = auth()->guard('user')->user();

        $this->validate(request(), [
            'name' => 'required',
            'email' => 'email|unique:users,email,'.$user->id,
            'password' => ['nullable', 'confirmed', Password::min(12)->mixedCase()->symbols()->numbers()->uncompromised()],
            'current_password' => 'required',
            'image.*' => 'nullable|mimes:bmp,jpeg,jpg,png,webp',
        ]);

        $data = request()->only([
            'name',
            'email',
            'password',
            'password_confirmation',
            'current_password',
            'image',
        ]);

        if (! Hash::check($data['current_password'], $user->password)) {
            session()->flash('warning', trans('admin::app.account.edit.invalid-password'));

            return redirect()->back();
        }

        if (isset($data['role_id']) || isset($data['view_permission'])) {
            session()->flash('warning', trans('admin::app.user.account.permission-denied'));

            return redirect()->back();
        }

        $isPasswordChanged = false;

        if (! $data['password']) {
            unset($data['password']);
        } else {
            $isPasswordChanged = true;

            $data['password'] = bcrypt($data['password']);
        }

        if (request()->hasFile('image')) {
            $data['image'] = current(request()->file('image'))->store('admins/'.$user->id);
        } else {
            if (! isset($data['image'])) {
                if (! empty($data['image'])) {
                    Storage::delete($user->image);
                }

                $data['image'] = null;
            } else {
                $data['image'] = $user->image;
            }
        }

        $user->update($data);

        if ($isPasswordChanged) {
            Event::dispatch('user.account.update-password', $user);

            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'password_change',
                'entity_type' => 'users',
                'entity_id' => $user->id,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'payload' => null,
            ]);
        }

        session()->flash('success', trans('admin::app.account.edit.update-success'));

        return back();
    }
}
