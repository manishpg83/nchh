<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class PanelController extends Controller
{
    protected $agentpanelPermissions = array(
        'send-invitation',
        'bank-account',
        'commission-history',
        'my-referral'
    ); 

    public function switchPanel($panel) {

        Session::put('previous-panel', Session::get('panel'));
        Session::put('panel', $panel);

        return redirect()->route('account.agent.invite.user');
    }

    public function logoutSwitchPanel() {

        $previousPanel = Session::get('previous-panel');
        Session::put('panel', $previousPanel);
        Session::forget('previous-panel');

        return redirect()->route('account.agent.invite.user');
    }
}
