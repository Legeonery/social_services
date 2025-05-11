<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

require_once app_path('Services/SmsRu/sms.ru.php');

class RegisteredUserController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('auth/Register');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email_or_phone' => 'required|string|max:255',
            'name' => 'nullable|string|max:255',
            'password' => ['nullable'],
        ]);

        $input = $data['email_or_phone'];

        if (!filter_var($input, FILTER_VALIDATE_EMAIL)) {
            if (!preg_match('/^\\+?[0-9]{10,15}$/', $input)) {
                return back()->withErrors(['email_or_phone' => 'Неверный номер телефона']);
            }

            $code = rand(100000, 999999);
            Cache::put("verify:{$input}", $code, now()->addMinutes(5));

            Cache::put("register:pending:{$input}", [
                'name' => $data['name'],
                'phone' => $input,
                'password' => Hash::make($data['password']),
            ], now()->addMinutes(10));

            $smsru = new \SMSRU(config('smsru.api_key'));

            $obj = new \stdClass();
            $obj->to = ltrim($input, '+');
            $obj->text = "Ваш код подтверждения: {$code}";
            $obj->from = config('smsru.from');
            if (config('smsru.test')) {
                $obj->test = 1;
            }

            $result = $smsru->send_one($obj);

            if ($result->status !== "OK") {
                \Log::error("Ошибка SMSRU: {$result->status_code} — {$result->status_text}");
                return back()->withErrors([
                    'email_or_phone' => 'Не удалось отправить SMS. Попробуйте позже.',
                ]);
            }

            return redirect()->route('verify.phone.form')->with('phone', $input);
        }

        $request->merge(['email' => $input]);
        $request->validate([
            'email' => 'required|email|unique:users,email',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $input,
            'password' => Hash::make($data['password']),
        ]);

        event(new Registered($user));
        Auth::login($user);

        return to_route('users');
    }

    public function check(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email_or_phone' => 'required|string|max:255',
        ]);

        $input = $data['email_or_phone'];

        if (!filter_var($input, FILTER_VALIDATE_EMAIL)) {
            if (!preg_match('/^\\+?[0-9]{10,15}$/', $input)) {
                return back()->withErrors(['email_or_phone' => 'Неверный номер телефона']);
            }

            $code = rand(100000, 999999);
            Cache::put("verify:{$input}", $code, now()->addMinutes(5));
            Cache::put("register:phone-only:{$input}", true, now()->addMinutes(10));

            $smsru = new \SMSRU(config('smsru.api_key'));
            $obj = (object) [
                'to' => ltrim($input, '+'),
                'text' => "Ваш код подтверждения: {$code}",
            ];
            if (config('smsru.test')) {
                $obj->test = 1;
            }

            $result = $smsru->send_one($obj);
            if ($result->status !== "OK") {
                return back()->withErrors(['email_or_phone' => 'Ошибка отправки SMS.']);
            }

            return redirect()->route('verify.phone.form')->with('phone', $input);
        }

        return redirect()->route('register.with.email')->with('email', $input);
    }

    public function showVerifyForm(Request $request): Response
    {
        return Inertia::render('auth/PhoneVerificationForm', [
            'phone' => session('phone'),
        ]);
    }

    public function verifyCode(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'phone' => ['required'],
            'code' => ['required', 'digits:6'],
        ]);

        $cachedCode = Cache::get("verify:{$data['phone']}");

        if (!$cachedCode || $cachedCode != $data['code']) {
            return back()->withErrors(['code' => 'Неверный или просроченный код']);
        }

        $cachedData = Cache::get("register:pending:{$data['phone']}");

        if (!$cachedData) {
            return back()->withErrors(['code' => 'Сессия подтверждения истекла. Зарегистрируйтесь заново.']);
        }

        $user = User::create([
            'name' => $cachedData['name'],
            'phone' => $cachedData['phone'],
            'password' => $cachedData['password'],
        ]);

        Cache::forget("verify:{$data['phone']}");
        Cache::forget("register:pending:{$data['phone']}");
        Auth::login($user);

        return redirect()->route('users');
    }
}
