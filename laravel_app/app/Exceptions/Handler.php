<?php
namespace App\Exceptions;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Throwable;
class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];
    public function register(): void
    {
        $this->renderable(function (TokenMismatchException $e, $request) {
            return redirect()->route('login')
                ->with('error', 'セッションが切れました。再度ログインしてください。');
        });
        $this->reportable(function (Throwable $e) {
            //
        });
    }
}
