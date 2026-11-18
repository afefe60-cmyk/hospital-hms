<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class AuthController extends Controller
{
    public function showLogin(Request $request, Response $response): string
    {
        return $this->view('auth/login', [
            'pageTitle' => 'تسجيل الدخول',
        ], 'layouts/auth');
    }

    public function login(Request $request, Response $response): void
    {
        $validation = $this->validate($request->all(), [
            'username' => 'required',
            'password' => 'required',
        ]);

        if (!$validation['isValid']) {
            $this->redirect(url('/login'));
            return;
        }

        $username = (string)$request->input('username');
        $password = (string)$request->input('password');
        $ip = $request->ip();
        $userAgent = $request->userAgent();

        $result = auth()->attempt($username, $password, $ip, $userAgent);

        if (!$result['success']) {
            Session::flash('error', $result['message']);
            Session::flash('_old_input', ['username' => $username]);
            $this->redirect(url('/login'));
            return;
        }

        Session::flash('success', 'مرحباً بك! تم تسجيل الدخول بنجاح.');
        $this->redirect(url('/'));
    }

    public function logout(Request $request, Response $response): void
    {
        auth()->logout();
        Session::flash('info', 'تم تسجيل الخروج بنجاح.');
        $this->redirect(url('/login'));
    }

    public function profile(Request $request, Response $response): string
    {
        $user = auth()->user();
        $roles = (new \App\Models\User())->roles($user['id']);

        return $this->view('auth/profile', [
            'pageTitle' => 'الملف الشخصي',
            'user' => $user,
            'roles' => $roles,
        ]);
    }

    public function updatePassword(Request $request, Response $response): void
    {
        $validation = $this->validate($request->all(), [
            'old_password' => 'required',
            'new_password' => 'required|min:8',
            'confirm_password' => 'required',
        ]);

        if (!$validation['isValid']) {
            $this->redirect(url('/profile'));
            return;
        }

        $newPassword = (string)$request->input('new_password');
        $confirmPassword = (string)$request->input('confirm_password');

        if ($newPassword !== $confirmPassword) {
            Session::flash('error', 'كلمة المرور الجديدة غير متطابقة مع التأكيد.');
            $this->redirect(url('/profile'));
            return;
        }

        $result = auth()->changePassword(
            auth()->id(),
            (string)$request->input('old_password'),
            $newPassword
        );

        if (!$result['success']) {
            Session::flash('error', $result['message']);
        } else {
            Session::flash('success', $result['message']);
        }

        $this->redirect(url('/profile'));
    }
}
