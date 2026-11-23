<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Controllers\Web;

use App\Service\FarmerAccountService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

/**
 * Public page where a farmer asks for their account to be deleted without opening the app (Google Play requires a web
 * link). Ownership is proven with a code sent to the account's email. Never log an email address or a code here.
 */
class AccountDeletionController extends Controller
{
    public function __construct(private readonly FarmerAccountService $accounts)
    {
    }

    public function show(): View
    {
        return view('account-deletion', ['step' => 'request']);
    }

    /** Step 1: email a code. The answer is the same whether or not the address has an account. */
    public function request(Request $request): View
    {
        $data = $request->validate(['email' => ['required', 'email:rfc', 'max:254']]);

        if (!$this->accounts->mailEnabled()) {
            return view('account-deletion', ['step' => 'request', 'unavailable' => true]);
        }

        $this->accounts->sendDeletion($data['email']);

        return view('account-deletion', ['step' => 'confirm', 'email' => $data['email']]);
    }

    /** Step 2: delete the account when the code is right. */
    public function confirm(Request $request): View
    {
        $data = $request->validate([
            'email' => ['required', 'email:rfc', 'max:254'],
            'code' => ['required', 'string', 'max:32'],
        ]);

        if (!$this->accounts->deleteWithCode(email: $data['email'], code: $data['code'])) {
            return view('account-deletion', [
                'step' => 'confirm',
                'email' => $data['email'],
                'error' => 'That code is wrong or has expired. Check it, or ask for a new one.',
            ]);
        }

        return view('account-deletion', ['step' => 'done']);
    }
}
