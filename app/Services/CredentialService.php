<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CredentialService extends AbstractService
{
    protected $model;
    /**
     * @var ActivityService
     */
    private $activityService;

    /**
     * CredentialService constructor.
     * @param User $model
     * @param ActivityService $activityService
     */
    public function __construct(User $model, ActivityService $activityService)
    {
        $this->model = $model;
        $this->activityService = $activityService;
    }

    public function blockUser(array $attributes)
    {
        $validData = $this->validate($attributes, ['action' => 'required', 'user_id' => 'required']);

        if ($validData->status == false) {
            return glue_errors($validData);
        }

        $user = $this->model->withTrashed()->where('id', $attributes['user_id'])->first();

        if ($user == null) {
            return user_not_found();
        }

        if ($attributes['action'] == 'block') {
            $this->revokeTokens($user);

            $user->status = 0;
            $user->save();

            $newUser = $this->model->find($attributes['user_id']);

            $this->activityService->addActivity(['company_id' => $user->company_id, 'branch_id' => $user->branch_id,
                'user_id' => auth_id(),
                'action' => 'Blocked [' . $user->name . ']']);

            return prepareResponse(true, $newUser);
        } else {
            $user->status = 1;
            $user->save();

            $user = $this->model->find($attributes['user_id']);

            $this->activityService->addActivity(['company_id' => $user->company_id, 'branch_id' => $user->branch_id,
                'user_id' => auth_id(),
                'action' => 'Restored [' . $user->name . ']']);

            return prepareResponse(true, $user);
        }

    }

    public function changePassword(array $attributes) {

        $validData = $this->validate($attributes, ['password' => 'required', 'user_id' => 'required']);

        if ($validData->status == false) {
            return glue_errors($validData);
        }

        $user = $this->findById($attributes['user_id']);

        if ($user == null) {
            return user_not_found();
        }

        $user->password = Hash::make($attributes['password']);
        $user->save();

        $this->activityService->addActivity(['company_id' => $user->company_id, 'branch_id' => $user->branch_id,
            'user_id' => auth_id(),
            'action' => 'Changed Password [' . $user->name . ']']);

        $this->revokeTokens($user);



        return prepareResponse(true, "Password Changed. Pleas Login Again");

    }

    public function changeBranch(array $attributes) {

        $validData = $this->validate($attributes, ['branch_id' => 'required', 'user_id' => 'required']);

        if ($validData->status == false) {
            return glue_errors($validData);
        }

        $user = $this->findById($attributes['user_id']);

        if ($user == null) {
            return user_not_found();
        }

        $user->branch_id = $attributes['branch_id'];
        $user->save();

        $this->activityService->addActivity(['company_id' => $user->company_id, 'branch_id' => $user->branch_id,
            'user_id' => auth_id(),
            'action' => 'Changed Branch of [' . $user->name . ']']);

        return prepareResponse(true, $this->getById($user->id, ['branch']));

    }

    public function revokeTokens(User $user)
    {
        $tokens = $user->tokens;
        foreach ($tokens as $token){
            $token->revoke();
        }
    }

    public function blockCompanyUsers($company_id)
    {
        $users = $this->model->where('company_id', $company_id)->get();

        foreach ($users as $user)
        {
            $user->status = 0;
            $user->deleted_at = now();
            $user->save();
            $this->revokeTokens($user);
        }

    }

    public function restoreCompanyUsers($company_id)
    {
        $users = $this->model->where('company_id', $company_id)->get();

        foreach ($users as $user)
        {
            $user->status = 1;
            $user->deleted_at = null;
            $user->save();
        }
    }

}
