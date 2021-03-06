<?php
namespace App\Http\Controllers;

use Auth;
use App\Tag;
use App\User;
use App\ImportJob;
use Carbon\Carbon;
use App\Invitation;
use Illuminate\Http\Request;
use App\Helpers\RandomHelper;
use App\Jobs\SendNewUserAlert;
use App\Jobs\ExportAccountAsSQL;
use App\Jobs\AddContactFromVCard;
use App\Jobs\SendInvitationEmail;
use App\Http\Requests\ImportsRequest;
use App\Http\Requests\SettingsRequest;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\InvitationRequest;

class SettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('settings.index');
    }

    /**
     * Save user settings.
     *
     * @param SettingsRequest $request
     * @return \Illuminate\Http\Response
     */
    public function save(SettingsRequest $request)
    {
        $request->user()->update(
            $request->only([
                'email',
                'timezone',
                'locale',
                'currency_id',
                'name_order',
            ]) + [
                'fluid_container' => $request->get('layout'),
            ]
        );
        return redirect('settings')
            ->with('status', trans('settings.settings_success'));
    }

    /**
     * Delete user account.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        $user = $request->user();
        $account = $user->account;
        if ($account) {
            $account->reminders->each->forceDelete();
            $account->kids->each->forceDelete();
            $account->notes->each->forceDelete();
            $account->significantOthers->each->forceDelete();
            $account->tasks->each->forceDelete();
            $account->activities->each->forceDelete();
            $account->debts->each->forceDelete();
            $account->events->each->forceDelete();
            $account->contacts->each->forceDelete();
            $account->invitations->each->forceDelete();
            $account->importjobs->each->forceDelete();
            $account->importjobreports->each->forceDelete();
            $account->forceDelete();
        }
        auth()->logout();
        $user->forceDelete();
        return redirect('/');
    }

    /**
     * Reset user account.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function reset(Request $request)
    {
        $user = $request->user();
        $account = $user->account;
        if ($account) {
            $account->reminders->each->forceDelete();
            $account->kids->each->forceDelete();
            $account->notes->each->forceDelete();
            $account->significantOthers->each->forceDelete();
            $account->tasks->each->forceDelete();
            $account->activities->each->forceDelete();
            $account->debts->each->forceDelete();
            $account->events->each->forceDelete();
            $account->contacts->each->forceDelete();
            $account->invitations->each->forceDelete();
            $account->importjobs->each->forceDelete();
            $account->importjobreports->each->forceDelete();
        }
        return redirect('/settings')
            ->with('status', trans('settings.reset_success'));
    }

    /**
     * Display the export view.
     *
     * @return \Illuminate\Http\Response
     */
    public function export()
    {
        return view('settings.export');
    }

    /**
     * Exports the data of the account in SQL format.
     *
     * @return \Illuminate\Http\Response
     */
    public function exportToSql()
    {
        $path = $this->dispatchNow(new ExportAccountAsSQL());
        return response()
            ->download(Storage::disk('public')->getDriver()->getAdapter()->getPathPrefix() . $path, 'monica.sql')
            ->deleteFileAfterSend(true);
    }

    /**
     * Display the import view.
     *
     * @return \Illuminate\Http\Response
     */
    public function import()
    {
        if (auth()->user()->company->importjobs->count() == 0) {
            return view('settings.imports.blank');
        }
        return view('settings.imports.index');
    }

    /**
     * Display the Import people's view.
     *
     * @return \Illuminate\Http\Response
     */
    public function upload()
    {
        return view('settings.imports.upload');
    }

    public function storeImport(ImportsRequest $request)
    {
        $filename = $request->file('vcard')->store('imports', 'public');
        $importJob = auth()->user()->company->importjobs()->create([
            'user_id' => auth()->user()->id,
            'type' => 'vcard',
            'filename' => $filename,
        ]);
        dispatch(new AddContactFromVCard($importJob));
        return redirect()->route('settings.import');
    }

    /**
     * Display the import report view.
     *
     * @return \Illuminate\Http\Response
     */
    public function report($importJobId)
    {
        $importJob = ImportJob::findOrFail($importJobId);
        if ($importJob->company_id != auth()->user()->company->id) {
            return redirect()->route('settings.index');
        }
        return view('settings.imports.report', compact('importJob'));
    }

    /**
     * Display the users view.
     *
     * @return \Illuminate\Http\Response
     */
    public function users()
    {
        $users = auth()->user()->company->users;
        if ($users->count() == 1 && auth()->user()->company->invitations()->count() == 0) {
            return view('settings.users.blank');
        }
        return view('settings.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function addUser()
    {
        if (config('monica.requires_subscription') && !auth()->user()->company->isSubscribed()) {
            return redirect('/settings/subscriptions');
        }
        return view('settings.users.add');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param InvitationRequest $request
     * @return \Illuminate\Http\Response
     */
    public function inviteUser(InvitationRequest $request)
    {
        // Make sure the confirmation to invite has not been bypassed
        if (!$request->get('confirmation')) {
            return redirect()->back()->withErrors(trans('settings.users_error_please_confirm'))->withInput();
        }
        // Is the email address already taken?
        $users = User::where('email', $request->only(['email']))->count();
        if ($users > 0) {
            return redirect()->back()->withErrors(trans('settings.users_error_email_already_taken'))->withInput();
        }
        // Has this user been invited already?
        $invitations = Invitation::where('email', $request->only(['email']))->count();
        if ($invitations > 0) {
            return redirect()->back()->withErrors(trans('settings.users_error_already_invited'))->withInput();
        }
        $invitation = auth()->user()->company->invitations()->create(
            $request->only([
                'email',
            ])
            + [
                'invited_by_user_id' => auth()->user()->id,
                'company_id' => auth()->user()->company_id,
                'invitation_key' => RandomHelper::generateString(100),
            ]
        );
        dispatch(new SendInvitationEmail($invitation));
        auth()->user()->company->update([
            'number_of_invitations_sent' => auth()->user()->company->number_of_invitations_sent + 1,
        ]);
        return redirect('settings/users')
            ->with('status', trans('settings.settings_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Invitation $invitation
     * @return \Illuminate\Http\Response
     */
    public function destroyInvitation(Invitation $invitation)
    {
        $invitation->delete();
        return redirect('/settings/users')
            ->with('success', trans('settings.users_invitation_deleted_confirmation_message'));
    }

    /**
     * Display the specified resource.
     *
     * @param string $key
     * @return \Illuminate\Http\Response
     */
    public function acceptInvitation($key)
    {
        if (Auth::check()) {
            return redirect('/');
        }
        $invitation = Invitation::where('invitation_key', $key)
            ->firstOrFail();
        return view('settings.users.accept', compact('key'));
    }

    /**
     * Store the specified resource.
     *
     * @param Request $request
     * @param string $key
     * @return \Illuminate\Http\Response
     */
    public function storeAcceptedInvitation(Request $request, $key)
    {
        $invitation = Invitation::where('invitation_key', $key)
            ->firstOrFail();
        // as a security measure, make sure that the new user provides the email
        // of the person who has invited him/her.
        if ($request->input('email_security') != $invitation->invitedBy->email) {
            return redirect()->back()->withErrors(trans('settings.users_error_email_not_similar'))->withInput();
        }
        $user = new User;
        $user->first_name = $request->input('first_name');
        $user->last_name = $request->input('last_name');
        $user->email = $request->input('email');
        $user->password = bcrypt($request->input('password'));
        $user->timezone = config('app.timezone');
        $user->created_at = Carbon::now();
        $user->company_id = $invitation->company_id;
        $user->save();
        $invitation->delete();
        // send me an alert
        dispatch(new SendNewUserAlert($user));
        if (Auth::attempt(['email' => $user->email, 'password' => $request->input('password')])) {
            return redirect('dashboard');
        }
    }

    /**
     * Delete additional user account.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function deleteAdditionalUser(Request $request, $userID)
    {
        $user = User::find($userID);
        if ($user->company_id != auth()->user()->company_id) {
            return redirect('/');
        }
        // make sure you don't delete yourself from this screen
        if ($user->id == auth()->user()->id) {
            return redirect('/');
        }
        $user = User::find($userID);
        $user->delete();
        return redirect('/settings/users')
            ->with('success', trans('settings.users_list_delete_success'));
    }

    /**
     * Display the list of tags for this account.
     */
    public function tags()
    {
        return view('settings.tags');
    }

    public function deleteTag(Request $request, $tagId)
    {
        $tag = Tag::findOrFail($tagId);
        if ($tag->company_id != auth()->user()->company_id) {
            return redirect('/');
        }
        $tag->contacts()->detach();
        $tag->delete();
        return redirect('/settings/tags')
            ->with('success', trans('settings.tags_list_delete_success'));
    }
}
